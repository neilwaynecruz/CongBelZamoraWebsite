import fs from 'node:fs/promises';
import path from 'node:path';
import { chromium } from '@playwright/test';

const baseUrl =
    process.env.ADMIN_CONSOLE_VISUAL_BASE_URL ?? 'http://127.0.0.1:8018';
const email = process.env.ADMIN_CONSOLE_VISUAL_EMAIL;
const password = process.env.ADMIN_CONSOLE_VISUAL_PASSWORD;
const outputDir = path.resolve('storage/app/admin-console-visual-smoke');
const inquiryId = process.env.ADMIN_CONSOLE_VISUAL_INQUIRY_ID;
const approvalId = process.env.ADMIN_CONSOLE_VISUAL_APPROVAL_ID;
const executeWorkflow =
    process.env.ADMIN_CONSOLE_VISUAL_EXECUTE_WORKFLOW === '1';
const executeGovernance =
    process.env.ADMIN_CONSOLE_VISUAL_EXECUTE_GOVERNANCE === '1';

if (!email || !password) {
    throw new Error(
        'ADMIN_CONSOLE_VISUAL_EMAIL and ADMIN_CONSOLE_VISUAL_PASSWORD are required.',
    );
}

const viewports = [
    { name: '320', width: 320, height: 780 },
    { name: '390', width: 390, height: 844 },
    { name: '768', width: 768, height: 1024 },
    { name: '1024', width: 1024, height: 900 },
    { name: '1440', width: 1440, height: 1000 },
    { name: '1920', width: 1920, height: 1080 },
];

const pages = [
    { name: 'dashboard', url: '/admin-console' },
    { name: 'inquiries', url: '/admin-console/inquiries' },
    { name: 'content-approvals', url: '/admin-console/content-approvals' },
    { name: 'design-system', url: '/admin-console/design-system' },
];

if (inquiryId) {
    pages.splice(2, 0, {
        name: 'inquiry-detail',
        url: `/admin-console/inquiries/${inquiryId}`,
    });
}

if (approvalId) {
    pages.splice(pages.length - 1, 0, {
        name: 'approval-detail',
        url: `/admin-console/content-approvals/${approvalId}`,
    });
}

await fs.rm(outputDir, { recursive: true, force: true });
await fs.mkdir(outputDir, { recursive: true });

const browser = await chromium.launch({ headless: true });
const context = await browser.newContext({
    reducedMotion: 'reduce',
    colorScheme: 'dark',
});
const page = await context.newPage();
const consoleErrors = [];

page.on('console', (message) => {
    if (message.type() === 'error') {
        consoleErrors.push(`${page.url()}: ${message.text()}`);
    }
});

const assertNoHorizontalOverflow = async (label) => {
    const report = await page.evaluate(() => {
        const viewportWidth = document.documentElement.clientWidth;
        const bodyOverflow =
            Math.max(
                document.documentElement.scrollWidth,
                document.body.scrollWidth,
            ) - viewportWidth;
        const offenders = Array.from(document.querySelectorAll('body *'))
            .filter((element) => {
                const style = window.getComputedStyle(element);

                if (style.display === 'none' || style.visibility === 'hidden') {
                    return false;
                }

                if (
                    style.clipPath !== 'none' ||
                    (style.clip !== 'auto' && style.clip !== '') ||
                    element.classList.contains('sr-only') ||
                    element.closest('[data-sonner-toaster]')
                ) {
                    return false;
                }

                const tableHead = element.closest('thead');

                if (
                    tableHead &&
                    window.getComputedStyle(tableHead).position ===
                        'absolute' &&
                    tableHead.getBoundingClientRect().width <= 2
                ) {
                    return false;
                }

                const rect = element.getBoundingClientRect();

                return (
                    rect.width > 0 &&
                    (rect.right > viewportWidth + 1 || rect.left < -1)
                );
            })
            .slice(0, 8)
            .map((element) => ({
                tag: element.tagName.toLowerCase(),
                className:
                    typeof element.className === 'string'
                        ? element.className
                        : '',
                text: element.textContent?.trim().slice(0, 80) ?? '',
            }));

        return { bodyOverflow, offenders };
    });

    if (report.bodyOverflow > 1 || report.offenders.length > 0) {
        throw new Error(
            `${label} has horizontal overflow: ${JSON.stringify(report)}`,
        );
    }
};

for (const viewport of viewports) {
    await page.setViewportSize({
        width: viewport.width,
        height: viewport.height,
    });
    await page.goto(`${baseUrl}/admin-console/login`, {
        waitUntil: 'networkidle',
    });
    await page.screenshot({
        path: path.join(outputDir, `login-${viewport.name}.png`),
        fullPage: true,
    });
    await assertNoHorizontalOverflow(`login-${viewport.name}`);
}

await page.setViewportSize({ width: 1440, height: 1000 });
await page.goto(`${baseUrl}/admin-console/login`, { waitUntil: 'networkidle' });
await page.locator('input[name="email"]').fill(email);
await page.locator('input[name="password"]').fill(password);
await Promise.all([
    page.waitForURL((url) => url.pathname === '/admin-console', {
        timeout: 20_000,
    }),
    page.locator('button[type="submit"]').click(),
]);

for (const target of pages) {
    for (const viewport of viewports) {
        await page.setViewportSize({
            width: viewport.width,
            height: viewport.height,
        });
        await page.goto(`${baseUrl}${target.url}`, {
            waitUntil: 'networkidle',
        });

        if (page.url().includes('/admin-console/login')) {
            throw new Error(
                `Authentication was lost while opening ${target.url}.`,
            );
        }

        await page.screenshot({
            path: path.join(outputDir, `${target.name}-${viewport.name}.png`),
            fullPage: true,
        });
        await assertNoHorizontalOverflow(`${target.name}-${viewport.name}`);

        if (target.name === 'inquiry-detail') {
            const noteTrigger = page.getByRole('button', {
                name: 'Add internal note',
            });

            if (await noteTrigger.isVisible()) {
                await noteTrigger.click();
                const dialog = page.getByRole('dialog', {
                    name: 'Add an internal case note',
                });
                await dialog.waitFor({ state: 'visible' });

                const focusIsContained = await dialog.evaluate((element) =>
                    element.contains(document.activeElement),
                );

                if (!focusIsContained) {
                    throw new Error(
                        `inquiry-action-dialog-${viewport.name} did not receive focus.`,
                    );
                }

                await page.screenshot({
                    path: path.join(
                        outputDir,
                        `inquiry-action-dialog-${viewport.name}.png`,
                    ),
                });
                await assertNoHorizontalOverflow(
                    `inquiry-action-dialog-${viewport.name}`,
                );
                await page.keyboard.press('Escape');
                await dialog.waitFor({ state: 'hidden' });

                if (
                    !(await noteTrigger.evaluate(
                        (element) => document.activeElement === element,
                    ))
                ) {
                    throw new Error(
                        `inquiry-action-dialog-${viewport.name} did not restore focus.`,
                    );
                }
            }
        }

        if (target.name === 'approval-detail') {
            const decisionTrigger = page.getByRole('button', {
                name: 'Approve revision',
            });

            if (await decisionTrigger.isVisible()) {
                await decisionTrigger.click();
                const dialog = page.getByRole('dialog', {
                    name: 'Approve this content revision',
                });
                await dialog.waitFor({ state: 'visible' });

                const focusIsContained = await dialog.evaluate((element) =>
                    element.contains(document.activeElement),
                );

                if (!focusIsContained) {
                    throw new Error(
                        `governance-action-dialog-${viewport.name} did not receive focus.`,
                    );
                }

                await page.screenshot({
                    path: path.join(
                        outputDir,
                        `governance-action-dialog-${viewport.name}.png`,
                    ),
                });
                await assertNoHorizontalOverflow(
                    `governance-action-dialog-${viewport.name}`,
                );
                await page.keyboard.press('Escape');
                await dialog.waitFor({ state: 'hidden' });

                if (
                    !(await decisionTrigger.evaluate(
                        (element) => document.activeElement === element,
                    ))
                ) {
                    throw new Error(
                        `governance-action-dialog-${viewport.name} did not restore focus.`,
                    );
                }
            }
        }
    }
}

if (executeGovernance && approvalId) {
    await page.setViewportSize({ width: 1440, height: 1000 });
    await page.goto(
        `${baseUrl}/admin-console/content-approvals/${approvalId}`,
        {
            waitUntil: 'networkidle',
        },
    );
    await page.getByRole('button', { name: 'Approve revision' }).click();

    const dialog = page.getByRole('dialog', {
        name: 'Approve this content revision',
    });
    await dialog
        .locator('textarea')
        .first()
        .fill(
            'Playwright verified the governed approval decision, snapshot, audit trail, and custom console success state.',
        );
    await dialog.getByRole('button', { name: 'Approve revision' }).click();
    await dialog.waitFor({ state: 'hidden' });
    await page.getByText('Approved', { exact: true }).first().waitFor();
    await page
        .getByText('Revision approved and captured as an approved snapshot.', {
            exact: true,
        })
        .waitFor();
    await page.screenshot({
        path: path.join(outputDir, 'governance-workflow-success-1440.png'),
        fullPage: true,
    });
    await assertNoHorizontalOverflow('governance-workflow-success-1440');
}

if (executeWorkflow && inquiryId) {
    await page.setViewportSize({ width: 1440, height: 1000 });
    await page.goto(`${baseUrl}/admin-console/inquiries/${inquiryId}`, {
        waitUntil: 'networkidle',
    });
    await page.getByRole('button', { name: 'Add internal note' }).click();

    const dialog = page.getByRole('dialog', {
        name: 'Add an internal case note',
    });
    const note = `Playwright verified the custom inquiry workflow end to end at ${Date.now()}.`;
    await dialog.locator('textarea').fill(note);
    await dialog.getByRole('button', { name: 'Add note' }).click();
    await dialog.waitFor({ state: 'hidden' });
    await page.getByText(note, { exact: true }).waitFor();
    await page
        .getByText('Internal note added to the case timeline.', { exact: true })
        .waitFor();
    await page.screenshot({
        path: path.join(outputDir, 'inquiry-workflow-success-1440.png'),
        fullPage: true,
    });
    await assertNoHorizontalOverflow('inquiry-workflow-success-1440');
}

if (consoleErrors.length > 0) {
    throw new Error(
        `Browser console errors detected:\n${consoleErrors.join('\n')}`,
    );
}

await browser.close();

console.log(`Admin console screenshots written to ${outputDir}`);
