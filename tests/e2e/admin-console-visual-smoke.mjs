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
const programId = process.env.ADMIN_CONSOLE_VISUAL_PROGRAM_ID;
const projectId = process.env.ADMIN_CONSOLE_VISUAL_PROJECT_ID;
const newsId = process.env.ADMIN_CONSOLE_VISUAL_NEWS_ID;
const legislationId = process.env.ADMIN_CONSOLE_VISUAL_LEGISLATION_ID;
const publicPageId = process.env.ADMIN_CONSOLE_VISUAL_PUBLIC_PAGE_ID;
const userId = process.env.ADMIN_CONSOLE_VISUAL_USER_ID;
const sourceRecordId = process.env.ADMIN_CONSOLE_VISUAL_SOURCE_RECORD_ID;
const referenceType =
    process.env.ADMIN_CONSOLE_VISUAL_REFERENCE_TYPE ?? 'program-categories';
const referenceRecordId = process.env.ADMIN_CONSOLE_VISUAL_REFERENCE_RECORD_ID;
const visualScope = process.env.ADMIN_CONSOLE_VISUAL_SCOPE;
const visualScopes = visualScope
    ?.split(',')
    .map((scope) => scope.trim())
    .filter(Boolean);
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

let pages = [
    { name: 'dashboard', url: '/admin-console' },
    { name: 'inquiries', url: '/admin-console/inquiries' },
    { name: 'programs', url: '/admin-console/programs' },
    { name: 'program-create', url: '/admin-console/programs/create' },
    { name: 'projects', url: '/admin-console/projects' },
    { name: 'project-create', url: '/admin-console/projects/create' },
    { name: 'news', url: '/admin-console/news' },
    { name: 'news-create', url: '/admin-console/news/create' },
    { name: 'legislation', url: '/admin-console/legislation' },
    { name: 'legislation-create', url: '/admin-console/legislation/create' },
    { name: 'public-pages', url: '/admin-console/public-pages' },
    { name: 'public-page-create', url: '/admin-console/public-pages/create' },
    { name: 'users', url: '/admin-console/users' },
    { name: 'user-create', url: '/admin-console/users/create' },
    { name: 'settings', url: '/admin-console/settings' },
    { name: 'audit', url: '/admin-console/audit' },
    { name: 'reports', url: '/admin-console/reports' },
    { name: 'source-records', url: '/admin-console/source-records' },
    {
        name: 'source-record-create',
        url: '/admin-console/source-records/create',
    },
    {
        name: 'reference-data',
        url: `/admin-console/reference-data/${referenceType}`,
    },
    {
        name: 'reference-data-create',
        url: `/admin-console/reference-data/${referenceType}/create`,
    },
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

if (programId) {
    pages.splice(3, 0, {
        name: 'program-detail',
        url: `/admin-console/programs/${programId}`,
    });
    pages.splice(4, 0, {
        name: 'program-edit',
        url: `/admin-console/programs/${programId}/edit`,
    });
}

if (projectId) {
    pages.splice(5, 0, {
        name: 'project-detail',
        url: `/admin-console/projects/${projectId}`,
    });
    pages.splice(6, 0, {
        name: 'project-edit',
        url: `/admin-console/projects/${projectId}/edit`,
    });
    pages.splice(7, 0, {
        name: 'project-evidence',
        url: `/admin-console/projects/${projectId}/evidence`,
    });
}

if (newsId) {
    pages.splice(7, 0, {
        name: 'news-detail',
        url: `/admin-console/news/${newsId}`,
    });
    pages.splice(8, 0, {
        name: 'news-edit',
        url: `/admin-console/news/${newsId}/edit`,
    });
    pages.splice(9, 0, {
        name: 'news-media',
        url: `/admin-console/news/${newsId}/media`,
    });
}

if (legislationId) {
    pages.splice(pages.length - 3, 0, {
        name: 'legislation-detail',
        url: `/admin-console/legislation/${legislationId}`,
    });
    pages.splice(pages.length - 3, 0, {
        name: 'legislation-edit',
        url: `/admin-console/legislation/${legislationId}/edit`,
    });
}

if (publicPageId) {
    pages.splice(pages.length - 3, 0, {
        name: 'public-page-detail',
        url: `/admin-console/public-pages/${publicPageId}`,
    });
    pages.splice(pages.length - 3, 0, {
        name: 'public-page-edit',
        url: `/admin-console/public-pages/${publicPageId}/edit`,
    });
}

if (userId) {
    pages.splice(pages.length - 3, 0, {
        name: 'user-detail',
        url: `/admin-console/users/${userId}`,
    });
    pages.splice(pages.length - 3, 0, {
        name: 'user-edit',
        url: `/admin-console/users/${userId}/edit`,
    });
}

if (sourceRecordId) {
    pages.splice(pages.length - 2, 0, {
        name: 'source-record-detail',
        url: `/admin-console/source-records/${sourceRecordId}`,
    });
    pages.splice(pages.length - 2, 0, {
        name: 'source-record-edit',
        url: `/admin-console/source-records/${sourceRecordId}/edit`,
    });
}

if (referenceRecordId) {
    pages.splice(pages.length - 2, 0, {
        name: 'reference-data-detail',
        url: `/admin-console/reference-data/${referenceType}/${referenceRecordId}`,
    });
    pages.splice(pages.length - 2, 0, {
        name: 'reference-data-edit',
        url: `/admin-console/reference-data/${referenceType}/${referenceRecordId}/edit`,
    });
}

if (visualScopes?.length) {
    pages = pages.filter((target) =>
        visualScopes.some((scope) => target.name.startsWith(scope)),
    );
}

if (!visualScope) {
    await fs.rm(outputDir, { recursive: true, force: true });
}

await fs.mkdir(outputDir, { recursive: true });

const browser = await chromium.launch({ headless: true });
const context = await browser.newContext({
    reducedMotion: 'reduce',
    colorScheme: 'dark',
});
const page = await context.newPage();
const consoleErrors = [];
const interactionEvidence = [];

page.on('console', (message) => {
    if (
        message.type() === 'error' ||
        (message.type() === 'warning' && message.text().includes('[Vue warn]'))
    ) {
        consoleErrors.push(`${page.url()}: ${message.text()}`);
    }
});

page.on('pageerror', (error) => {
    consoleErrors.push(`${page.url()}: ${error.message}`);
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

const assertAdminDocumentSurface = async (label) => {
    const surface = await page.evaluate(() => ({
        htmlClass: document.documentElement.classList.contains(
            'admin-console-document',
        ),
        bodyClass: document.body.classList.contains('admin-console-document'),
        htmlBackground: getComputedStyle(document.documentElement)
            .backgroundColor,
        bodyBackground: getComputedStyle(document.body).backgroundColor,
        overlayHostHeight:
            document
                .querySelector('#admin-overlay-root')
                ?.getBoundingClientRect().height ?? null,
    }));

    if (!surface.htmlClass || !surface.bodyClass) {
        throw new Error(
            `${label} did not apply the authenticated admin document surface.`,
        );
    }

    if (
        surface.htmlBackground === 'rgb(0, 0, 0)' ||
        surface.bodyBackground === 'rgb(0, 0, 0)'
    ) {
        throw new Error(`${label} exposed a black document background.`);
    }

    if (surface.overlayHostHeight !== 0) {
        throw new Error(
            `${label} overlay host added ${surface.overlayHostHeight}px of empty document height.`,
        );
    }
};

if (!visualScope) {
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

const exerciseCommandSearch = async () => {
    await page.setViewportSize({ width: 1440, height: 1000 });
    await page.goto(`${baseUrl}/admin-console`, { waitUntil: 'networkidle' });

    const trigger = page.getByRole('button', { name: 'Open command search' });

    if (!(await trigger.isVisible())) {
        interactionEvidence.push({
            workflow: 'command-search',
            status: 'missing',
            note: 'Open command search trigger was not visible on desktop dashboard.',
        });

        return;
    }

    await trigger.click();

    const dialog = page.getByRole('dialog', {
        name: 'Find work and move quickly',
    });
    await dialog.waitFor({ state: 'visible' });
    await page
        .getByRole('searchbox', { name: 'Search commands and records' })
        .fill('projects');
    await page.screenshot({
        path: path.join(outputDir, 'command-search-1440.png'),
        fullPage: true,
    });
    await assertNoHorizontalOverflow('command-search-1440');
    await page.keyboard.press('Escape');
    await dialog.waitFor({ state: 'hidden' });
    interactionEvidence.push({
        workflow: 'command-search',
        status: 'passed',
        note: 'Desktop command search opened, accepted keyboard input, closed with Escape, and produced no overflow.',
    });
};

const exerciseMobileNavigation = async () => {
    await page.setViewportSize({ width: 390, height: 844 });
    await page.goto(`${baseUrl}/admin-console`, { waitUntil: 'networkidle' });

    const trigger = page.getByRole('button', { name: 'Open navigation' });

    if (!(await trigger.isVisible())) {
        interactionEvidence.push({
            workflow: 'mobile-navigation',
            status: 'missing',
            note: 'Open navigation trigger was not visible on mobile dashboard.',
        });

        return;
    }

    await trigger.click();
    await page.getByLabel('Admin navigation').waitFor({ state: 'visible' });
    await page.screenshot({
        path: path.join(outputDir, 'mobile-navigation-390.png'),
        fullPage: true,
    });
    await assertNoHorizontalOverflow('mobile-navigation-390');
    await page.keyboard.press('Escape');
    interactionEvidence.push({
        workflow: 'mobile-navigation',
        status: 'passed',
        note: 'Mobile drawer navigation opened, was accessible by label, closed with Escape, and produced no overflow.',
    });
};

if (!visualScopes?.length || visualScopes.includes('dashboard')) {
    await exerciseCommandSearch();
    await exerciseMobileNavigation();
}

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
        await assertAdminDocumentSurface(`${target.name}-${viewport.name}`);
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
                name: 'Approve version',
            });

            if (await decisionTrigger.isVisible()) {
                await decisionTrigger.click();
                const dialog = page.getByRole('dialog', {
                    name: 'Approve this content version',
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

        if (
            [
                'program-detail',
                'project-detail',
                'news-detail',
                'legislation-detail',
                'public-page-detail',
            ].includes(target.name)
        ) {
            const lifecyclePanel = page.locator('.admin-lifecycle-panel');
            const archiveTrigger = lifecyclePanel
                .getByRole('button', { name: /^Archive / })
                .first();

            if (await archiveTrigger.isVisible()) {
                await archiveTrigger.click();
                const dialog = page.locator('.admin-action-dialog__panel');
                await dialog.waitFor({ state: 'visible' });

                const focusIsContained = await dialog.evaluate((element) =>
                    element.contains(document.activeElement),
                );

                if (!focusIsContained) {
                    throw new Error(
                        `lifecycle-dialog-${target.name}-${viewport.name} did not receive focus.`,
                    );
                }

                await page.screenshot({
                    path: path.join(
                        outputDir,
                        `lifecycle-dialog-${target.name}-${viewport.name}.png`,
                    ),
                });
                await assertNoHorizontalOverflow(
                    `lifecycle-dialog-${target.name}-${viewport.name}`,
                );
                await page.keyboard.press('Escape');
                await dialog.waitFor({ state: 'hidden' });

                if (
                    !(await archiveTrigger.evaluate(
                        (element) => document.activeElement === element,
                    ))
                ) {
                    throw new Error(
                        `lifecycle-dialog-${target.name}-${viewport.name} did not restore focus.`,
                    );
                }

                interactionEvidence.push({
                    workflow: `lifecycle-dialog-${target.name}-${viewport.name}`,
                    status: 'passed',
                    note: 'Archive confirmation opened, contained focus, closed with Escape, restored focus, and did not submit the action.',
                });
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
    await page.getByRole('button', { name: 'Approve version' }).click();

    const dialog = page.getByRole('dialog', {
        name: 'Approve this content version',
    });
    await dialog
        .locator('textarea')
        .first()
        .fill(
            'Playwright verified the governed approval decision, snapshot, audit trail, and custom console success state.',
        );
    await dialog.getByRole('button', { name: 'Approve version' }).click();
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

await fs.writeFile(
    path.join(
        outputDir,
        visualScope
            ? `admin-console-visual-smoke-report-${visualScope}.json`
            : 'admin-console-visual-smoke-report.json',
    ),
    JSON.stringify(
        {
            baseUrl,
            generatedAt: new Date().toISOString(),
            viewports,
            reflowEvidence: {
                cssViewportWidth: 320,
                equivalentPhysicalWidthAt200PercentZoom: 640,
                note: 'Every listed page is checked at a 320 CSS-pixel viewport, the WCAG reflow equivalent of a 640-pixel browser window at 200% zoom.',
            },
            pages,
            interactions: interactionEvidence,
            consoleErrors,
        },
        null,
        2,
    ),
);

console.log(`Admin console screenshots written to ${outputDir}`);
