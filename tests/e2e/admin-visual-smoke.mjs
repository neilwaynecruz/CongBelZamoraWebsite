import fs from 'node:fs/promises';
import path from 'node:path';
import { chromium } from '@playwright/test';

const baseUrl = process.env.ADMIN_VISUAL_BASE_URL ?? 'http://127.0.0.1:8017';
const email = process.env.ADMIN_VISUAL_EMAIL ?? 'visual.qa.admin@example.test';
const password = process.env.ADMIN_VISUAL_PASSWORD ?? 'VisualQa123!';
const outputDir = path.resolve('storage/app/admin-visual-smoke');

const pages = [
    { name: 'dashboard', url: '/admin' },
    { name: 'public-pages', url: '/admin/public-pages' },
    { name: 'public-page-edit', url: '/admin/public-pages/1/edit' },
];

function luminance([r, g, b]) {
    const convert = (value) => {
        const channel = value / 255;

        return channel <= 0.03928
            ? channel / 12.92
            : ((channel + 0.055) / 1.055) ** 2.4;
    };

    return 0.2126 * convert(r) + 0.7152 * convert(g) + 0.0722 * convert(b);
}

function contrastRatio(foreground, background) {
    const lighter = Math.max(luminance(foreground), luminance(background));
    const darker = Math.min(luminance(foreground), luminance(background));

    return (lighter + 0.05) / (darker + 0.05);
}

async function collectContrastIssues(page) {
    const samples = await page.locator('body *').evaluateAll((elements) => {
        const parseColor = (value) => {
            const match = value.match(/rgba?\((\d+),\s*(\d+),\s*(\d+)(?:,\s*([\d.]+))?\)/);

            if (!match) {
                return null;
            }

            const alpha = match[4] === undefined ? 1 : Number(match[4]);

            return {
                rgb: [Number(match[1]), Number(match[2]), Number(match[3])],
                alpha,
            };
        };

        const findBackground = (element) => {
            let current = element;

            while (current) {
                const color = parseColor(window.getComputedStyle(current).backgroundColor);

                if (color && color.alpha > 0.05) {
                    return color.rgb;
                }

                current = current.parentElement;
            }

            return [255, 255, 255];
        };

        return elements
            .map((element) => {
                const rect = element.getBoundingClientRect();
                const text = (element.textContent || '').replace(/\s+/g, ' ').trim();

                if (!text || rect.width < 4 || rect.height < 4) {
                    return null;
                }

                const style = window.getComputedStyle(element);

                if (style.visibility === 'hidden' || style.display === 'none' || Number(style.opacity) < 0.05) {
                    return null;
                }

                const foreground = parseColor(style.color);

                if (!foreground || foreground.alpha < 0.5) {
                    return null;
                }

                return {
                    text: text.slice(0, 120),
                    tag: element.tagName.toLowerCase(),
                    className: String(element.className || '').slice(0, 160),
                    foreground: foreground.rgb,
                    background: findBackground(element),
                };
            })
            .filter(Boolean);
    });

    return samples
        .map((sample) => ({
            ...sample,
            contrast: contrastRatio(sample.foreground, sample.background),
        }))
        .filter((sample) => sample.contrast < 4.5)
        .slice(0, 40);
}

async function safeGoto(page, url) {
    try {
        return await page.goto(url, { waitUntil: 'domcontentloaded' });
    } catch (error) {
        if (String(error).includes('ERR_ABORTED')) {
            await page.waitForTimeout(750);

            return null;
        }

        throw error;
    }
}

async function login(page) {
    await safeGoto(page, `${baseUrl}/admin/login`);
    await page.locator('input[type="email"], input[name="email"]').first().fill(email);
    await page.locator('input[type="password"], input[name="password"]').first().fill(password);
    await page.getByRole('button', { name: /enter admin console|sign in|log in/i }).click();
    await page.waitForURL(/\/admin(?:$|[/?#])/, { timeout: 15000 });
    await page.waitForLoadState('domcontentloaded');
    await page.waitForTimeout(2500);
}

async function runForScheme(browser, scheme) {
    const context = await browser.newContext({
        colorScheme: scheme,
        viewport: { width: 1440, height: 950 },
    });

    await context.addInitScript((activeScheme) => {
        window.localStorage.setItem('theme', activeScheme);
        window.localStorage.setItem('appearance', activeScheme);
        window.localStorage.setItem('color-theme', activeScheme);
        window.localStorage.setItem('filament-theme-mode', activeScheme);
    }, scheme);

    const page = await context.newPage();
    const summary = [];

    await safeGoto(page, `${baseUrl}/admin/login`);
    await page.waitForTimeout(2500);
    await page.screenshot({
        path: path.join(outputDir, `${scheme}-login.png`),
        fullPage: true,
    });
    summary.push({
        scheme,
        page: 'login',
        issues: await collectContrastIssues(page),
    });

    await login(page);

    for (const target of pages) {
        const response = await safeGoto(page, `${baseUrl}${target.url}`);
        const status = response?.status() ?? 0;
        await page.waitForTimeout(2500);

        await page.screenshot({
            path: path.join(outputDir, `${scheme}-${target.name}.png`),
            fullPage: true,
        });

        summary.push({
            scheme,
            page: target.name,
            status,
            issues: await collectContrastIssues(page),
        });
    }

    await context.close();

    return summary;
}

await fs.mkdir(outputDir, { recursive: true });

const browser = await chromium.launch();
const results = [
    ...(await runForScheme(browser, 'light')),
    ...(await runForScheme(browser, 'dark')),
];

await browser.close();

await fs.writeFile(
    path.join(outputDir, 'summary.json'),
    JSON.stringify(results, null, 2),
);

const severe = results.flatMap((result) =>
    result.issues.map((issue) => ({
        scheme: result.scheme,
        page: result.page,
        status: result.status,
        contrast: Number(issue.contrast.toFixed(2)),
        text: issue.text,
        tag: issue.tag,
    })),
);

console.log(JSON.stringify({ outputDir, severe }, null, 2));
