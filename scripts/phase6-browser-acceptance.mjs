import fs from 'node:fs/promises';

const base = process.env.PHASE6_BASE_URL ?? 'http://127.0.0.1:8022';
const cdp = process.env.PHASE6_CDP_URL ?? 'http://127.0.0.1:9226';
const screenshotDir =
    process.env.PHASE6_SCREENSHOT_DIR ??
    'C:/laragon/www/CongBellProject/storage/app/qa/phase6-browser';

const routes = [
    '/',
    '/about',
    '/programs',
    '/programs/gabay',
    '/programs/education/tulong-dunong',
    '/legislation',
    '/projects',
    '/news',
    '/contact',
    '/privacy',
    '/accessibility',
    '/disclaimer',
];

const sleep = (ms) => new Promise((resolve) => setTimeout(resolve, ms));

async function waitForHttp(url, timeoutMs = 20_000) {
    const startedAt = Date.now();
    let lastError;

    while (Date.now() - startedAt < timeoutMs) {
        try {
            const response = await fetch(url);

            if (response.ok) {
                return response;
            }

            lastError = new Error(`HTTP ${response.status}`);
        } catch (error) {
            lastError = error;
        }

        await sleep(500);
    }

    throw lastError ?? new Error(`Timed out waiting for ${url}`);
}

async function pageTargetWebSocket() {
    await waitForHttp(`${cdp}/json/version`);

    let targets = await fetch(`${cdp}/json/list`).then((response) =>
        response.json(),
    );
    let pageTarget = targets.find(
        (target) => target.type === 'page' && target.webSocketDebuggerUrl,
    );

    if (!pageTarget) {
        await fetch(`${cdp}/json/new?about:blank`, { method: 'PUT' });
        targets = await fetch(`${cdp}/json/list`).then((response) =>
            response.json(),
        );
        pageTarget = targets.find(
            (target) => target.type === 'page' && target.webSocketDebuggerUrl,
        );
    }

    if (!pageTarget) {
        throw new Error('No Chrome page target WebSocket is available.');
    }

    return pageTarget.webSocketDebuggerUrl;
}

class CdpClient {
    constructor(wsUrl) {
        this.ws = new WebSocket(wsUrl);
        this.id = 0;
        this.pending = new Map();
        this.consoleErrors = [];
        this.loadResolvers = [];

        this.ws.onmessage = (event) => {
            const message = JSON.parse(event.data);

            if (message.id && this.pending.has(message.id)) {
                const { resolve, reject } = this.pending.get(message.id);
                this.pending.delete(message.id);

                if (message.error) {
                    reject(new Error(JSON.stringify(message.error)));
                } else {
                    resolve(message.result ?? {});
                }

                return;
            }

            if (message.method === 'Runtime.exceptionThrown') {
                this.consoleErrors.push({
                    type: 'exception',
                    text:
                        message.params.exceptionDetails?.exception
                            ?.description ??
                        message.params.exceptionDetails?.text ??
                        'Runtime exception',
                    url: message.params.exceptionDetails?.url ?? '',
                });
            }

            if (
                message.method === 'Runtime.consoleAPICalled' &&
                message.params.type === 'error'
            ) {
                this.consoleErrors.push({
                    type: 'console.error',
                    text: (message.params.args ?? [])
                        .map((arg) => arg.value ?? arg.description ?? '')
                        .join(' '),
                    url: '',
                });
            }

            if (
                message.method === 'Log.entryAdded' &&
                message.params.entry?.level === 'error'
            ) {
                this.consoleErrors.push({
                    type: 'log.error',
                    text: message.params.entry.text,
                    url: message.params.entry.url ?? '',
                });
            }

            if (message.method === 'Page.loadEventFired') {
                this.loadResolvers.splice(0).forEach((resolve) => resolve());
            }
        };
    }

    async open() {
        await new Promise((resolve, reject) => {
            this.ws.onopen = resolve;
            this.ws.onerror = reject;
        });
    }

    send(method, params = {}) {
        const id = ++this.id;
        this.ws.send(JSON.stringify({ id, method, params }));

        return new Promise((resolve, reject) => {
            this.pending.set(id, { resolve, reject });
        });
    }

    waitForLoad(timeout = 15_000) {
        return new Promise((resolve) => {
            const timer = setTimeout(resolve, timeout);
            this.loadResolvers.push(() => {
                clearTimeout(timer);
                resolve();
            });
        });
    }

    async eval(expression, label = 'Runtime.evaluate') {
        const result = await this.send('Runtime.evaluate', {
            expression,
            awaitPromise: true,
            returnByValue: true,
        });

        if (result.exceptionDetails) {
            const description =
                result.exceptionDetails.exception?.description ??
                result.exceptionDetails.text ??
                'Evaluation failed';

            throw new Error(`${label}: ${description}`);
        }

        return result.result.value;
    }

    async navigate(path) {
        this.consoleErrors = [];
        const loadPromise = this.waitForLoad(4_000);
        await this.send('Page.navigate', { url: `${base}${path}` });
        await loadPromise;
        await sleep(500);
    }

    close() {
        this.ws.close();
    }
}

async function setViewport(client, viewport) {
    if (viewport === 'mobile') {
        await client.send('Emulation.setDeviceMetricsOverride', {
            width: 390,
            height: 844,
            deviceScaleFactor: 2,
            mobile: true,
        });
        await client.send('Emulation.setUserAgentOverride', {
            userAgent:
                'Mozilla/5.0 (iPhone; CPU iPhone OS 17_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.0 Mobile/15E148 Safari/604.1',
        });

        return;
    }

    await client.send('Emulation.setDeviceMetricsOverride', {
        width: 1366,
        height: 900,
        deviceScaleFactor: 1,
        mobile: false,
    });
    await client.send('Emulation.setUserAgentOverride', {
        userAgent:
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124 Safari/537.36',
    });
}

const pageAuditExpression = String.raw`(() => {
    const root = document.documentElement || document.body;
    const visible = (el) => {
        const rect = el.getBoundingClientRect();
        const style = getComputedStyle(el);

        return rect.width > 0 &&
            rect.height > 0 &&
            style.visibility !== 'hidden' &&
            style.display !== 'none';
    };

    const interactive = [...document.querySelectorAll('a[href],button,input,select,textarea,[role="button"],[tabindex]:not([tabindex="-1"])')]
        .filter(visible)
        .map((el) => {
            const rect = el.getBoundingClientRect();

            return {
                tag: el.tagName.toLowerCase(),
                text: (el.innerText || el.getAttribute('aria-label') || el.getAttribute('placeholder') || el.name || '').trim().slice(0, 80),
                width: Math.round(rect.width),
                height: Math.round(rect.height),
            };
        });

    return {
        path: location.pathname,
        title: document.title,
        h1: document.querySelector('h1')?.innerText.trim() || '',
        hasMain: Boolean(document.querySelector('main#main-content')),
        hasBreadcrumb: Boolean(document.querySelector('nav[aria-label="Breadcrumb"]')) || location.pathname === '/',
        hasInertia: Boolean(document.querySelector('#app[data-page], [data-page]')),
        horizontalOverflow: root ? root.scrollWidth > root.clientWidth + 1 : true,
        visibleInteractiveCount: interactive.length,
        smallTargets: interactive.filter((item) => item.width < 44 || item.height < 44),
        canonical: document.querySelector('link[rel="canonical"]')?.href || '',
        ogTitle: document.querySelector('meta[property="og:title"]')?.content || '',
        jsonLd: [...document.querySelectorAll('script[type="application/ld+json"]')].length,
    };
})()`;

async function waitForCondition(client, expression, label, timeoutMs = 5_000) {
    const startedAt = Date.now();
    let lastValue;

    while (Date.now() - startedAt < timeoutMs) {
        lastValue = await client.eval(expression, label);

        if (lastValue) {
            return lastValue;
        }

        await sleep(250);
    }

    return lastValue;
}

function addRouteFailures(failures, routeResult) {
    const { viewport, route, audit, errors } = routeResult;

    if (!audit.hasMain) {
failures.push(`${viewport} ${route}: missing main`);
}

    if (!audit.hasBreadcrumb) {
        failures.push(`${viewport} ${route}: missing breadcrumb`);
    }

    if (!audit.hasInertia) {
        failures.push(`${viewport} ${route}: missing Inertia root`);
    }

    if (audit.horizontalOverflow) {
        failures.push(`${viewport} ${route}: horizontal overflow`);
    }

    if (!audit.canonical || !audit.ogTitle || audit.jsonLd < 1) {
        failures.push(`${viewport} ${route}: incomplete metadata`);
    }

    if (errors.length > 0) {
        failures.push(
            `${viewport} ${route}: console errors ${JSON.stringify(errors)}`,
        );
    }
}

await fs.mkdir(screenshotDir, { recursive: true });
await waitForHttp(`${base}/`);

const client = new CdpClient(await pageTargetWebSocket());
await client.open();
await client.send('Page.enable');
await client.send('Runtime.enable');
await client.send('Log.enable');
await client.send('Network.enable');

const routeResults = [];

for (const viewport of ['desktop', 'mobile']) {
    await setViewport(client, viewport);

    for (const route of routes) {
        await client.navigate(route);
        const audit = await client.eval(
            pageAuditExpression,
            `audit ${viewport} ${route}`,
        );

        routeResults.push({
            viewport,
            route,
            audit,
            errors: [...client.consoleErrors],
        });

        const shouldCapture =
            (viewport === 'desktop' && ['/', '/contact'].includes(route)) ||
            (viewport === 'mobile' &&
                ['/', '/programs', '/contact'].includes(route));

        if (shouldCapture) {
            const name = `${viewport}-${
                route === '/' ? 'home' : route.slice(1).replaceAll('/', '-')
            }.png`;
            const screenshot = await client.send('Page.captureScreenshot', {
                format: 'png',
                captureBeyondViewport: false,
            });
            await fs.writeFile(
                `${screenshotDir}/${name}`,
                Buffer.from(screenshot.data, 'base64'),
            );
        }
    }
}

await setViewport(client, 'desktop');
await client.navigate('/');
await client.send('Input.dispatchKeyEvent', {
    type: 'keyDown',
    key: 'Tab',
    windowsVirtualKeyCode: 9,
    nativeVirtualKeyCode: 9,
});
await client.send('Input.dispatchKeyEvent', {
    type: 'keyUp',
    key: 'Tab',
    windowsVirtualKeyCode: 9,
    nativeVirtualKeyCode: 9,
});
await sleep(200);

const firstFocus = await client.eval(
    String.raw`(() => {
        const el = document.activeElement;
        const rect = el?.getBoundingClientRect();
        const style = el ? getComputedStyle(el) : null;

        return {
            tag: el?.tagName,
            text: (el?.innerText || el?.getAttribute('aria-label') || '').trim(),
            href: el?.getAttribute('href'),
            visible: Boolean(rect && rect.width > 0 && rect.height > 0),
            top: rect ? Math.round(rect.top) : null,
            outline: style?.outlineStyle || '',
            outlineWidth: style?.outlineWidth || '',
        };
    })()`,
    'first focus',
);

await client.send('Input.dispatchKeyEvent', {
    type: 'keyDown',
    key: 'Enter',
    windowsVirtualKeyCode: 13,
    nativeVirtualKeyCode: 13,
});
await client.send('Input.dispatchKeyEvent', {
    type: 'keyUp',
    key: 'Enter',
    windowsVirtualKeyCode: 13,
    nativeVirtualKeyCode: 13,
});
await sleep(300);

const skipResult = await client.eval(
    `(() => ({ hash: location.hash, activeId: document.activeElement?.id || '', activeTag: document.activeElement?.tagName || '' }))()`,
    'skip link',
);

await client.navigate('/');
await client.eval(
    `document.querySelector('[aria-controls="site-search"]')?.click()`,
    'open search',
);
await sleep(200);

const searchResult = await client.eval(
    String.raw`(() => {
        const region = document.querySelector('#site-search');
        const input = document.querySelector('#global-search');
        const rect = region?.getBoundingClientRect();

        return {
            expanded: document.querySelector('[aria-controls="site-search"]')?.getAttribute('aria-expanded'),
            visible: Boolean(region && rect.width > 0 && rect.height > 0 && !region.classList.contains('hidden')),
            hasInput: Boolean(input),
        };
    })()`,
    'search disclosure',
);

await setViewport(client, 'mobile');
await client.navigate('/');
await client.eval(
    `document.querySelector('[aria-controls="mobile-navigation"]')?.click()`,
    'open mobile nav',
);
await sleep(200);

const mobileNavResult = await client.eval(
    String.raw`(() => {
        const nav = document.querySelector('#mobile-navigation');
        const rect = nav?.getBoundingClientRect();

        return {
            expanded: document.querySelector('[aria-controls="mobile-navigation"]')?.getAttribute('aria-expanded'),
            visible: Boolean(nav && rect.width > 0 && rect.height > 0 && !nav.classList.contains('hidden')),
            linkCount: nav ? nav.querySelectorAll('a[href]').length : 0,
        };
    })()`,
    'mobile nav',
);

await setViewport(client, 'desktop');
await client.navigate('/');
await client.eval(
    String.raw`(() => {
        const visible = (el) => {
            const rect = el.getBoundingClientRect();
            const style = getComputedStyle(el);

            return rect.width > 0 &&
                rect.height > 0 &&
                style.visibility !== 'hidden' &&
                style.display !== 'none';
        };
        const link = [...document.querySelectorAll('a[href]')].find((el) =>
            visible(el) && new URL(el.href).pathname === '/about'
        );

        link?.click();
    })()`,
    'about link',
);
await sleep(1_200);

const inertiaNavResult = await client.eval(
    `(() => ({ path: location.pathname, title: document.title, h1: document.querySelector('h1')?.innerText.trim() || '' }))()`,
    'inertia nav',
);
const inertiaNavErrors = [...client.consoleErrors];

const filterResults = [];

for (const route of ['/programs', '/legislation', '/projects', '/news']) {
    await client.navigate(route);
    await client.eval(
        String.raw`(() => {
            const visible = (el) => {
                const rect = el.getBoundingClientRect();
                const style = getComputedStyle(el);

                return rect.width > 0 &&
                    rect.height > 0 &&
                    style.visibility !== 'hidden' &&
                    style.display !== 'none';
            };
            const input = [...document.querySelectorAll('input[type="search"]')]
                .find((el) => visible(el) && el.id !== 'global-search');
            const setter = Object.getOwnPropertyDescriptor(HTMLInputElement.prototype, 'value').set;
            setter.call(input, 'zzzzzz-phase6-no-result');
            input.dispatchEvent(new Event('input', { bubbles: true }));
        })()`,
        `set filter ${route}`,
    );

    const state = await waitForCondition(
        client,
        String.raw`(() => {
            const text = document.body.innerText;

            return {
                path: location.pathname,
                hasNoResult: /No results|No legislative records|No project records|No updates|Walang nahanap/i.test(text),
                hasReset: [...document.querySelectorAll('button')].some((button) => /Reset/i.test(button.innerText)),
            };
        })()`,
        `wait filter ${route}`,
        5_000,
    );

    filterResults.push(state);
}

await client.navigate('/contact');
await client.eval(
    `document.querySelector('#inquiry-form button[type="submit"]')?.click()`,
    'empty contact submit',
);

const contactErrorResult = await waitForCondition(
    client,
    String.raw`(() => {
        const errors = [...document.querySelectorAll('[aria-invalid="true"], .text-status-danger')]
            .map((el) => el.innerText || el.getAttribute('name') || el.id)
            .filter(Boolean);

        return errors.length > 0
            ? { path: location.pathname, errors, text: document.body.innerText.slice(0, 2500) }
            : false;
    })()`,
    'contact validation errors',
    6_000,
);

await client.eval(
    String.raw`(() => {
        const set = (selector, value) => {
            const el = document.querySelector(selector);
            const prototype = el instanceof HTMLTextAreaElement
                ? HTMLTextAreaElement.prototype
                : el instanceof HTMLSelectElement
                    ? HTMLSelectElement.prototype
                    : HTMLInputElement.prototype;
            const setter = Object.getOwnPropertyDescriptor(prototype, 'value').set;

            setter.call(el, value);
            el.dispatchEvent(new Event('input', { bubbles: true }));
            el.dispatchEvent(new Event('change', { bubbles: true }));
        };

        set('#citizen-name', 'Browser QA Tester');
        document.querySelector('input[name="contact_preference"][value="email"]').click();
        set('#citizen-email', 'qa@example.com');
        set('#inquiry-category', 'general_inquiry');
        set('#message-body', 'Browser QA validation message for Phase 6 contact form and privacy-safe routing.');
        document.querySelector('input[name="privacy_consent"]').click();
    })()`,
    'fill contact form',
);
await sleep(200);
await client.eval(
    `document.querySelector('#inquiry-form button[type="submit"]')?.click()`,
    'valid contact submit',
);

const contactSuccessResult = await waitForCondition(
    client,
    String.raw`(() => {
        const hasConfirmation = /INQ-|reference|salamat|received|natanggap|Thank/i.test(document.body.innerText);

        return hasConfirmation
            ? { path: location.pathname, hasConfirmation, text: document.body.innerText.slice(0, 2500) }
            : false;
    })()`,
    'contact success',
    6_000,
);

const failures = [];

for (const routeResult of routeResults) {
    addRouteFailures(failures, routeResult);
}

if (
    !(
        firstFocus.href === '#main-content' &&
        firstFocus.visible &&
        firstFocus.outline !== 'none'
    )
) {
    failures.push(`Skip link focus failed: ${JSON.stringify(firstFocus)}`);
}

if (skipResult.hash !== '#main-content') {
    failures.push(`Skip link Enter failed: ${JSON.stringify(skipResult)}`);
}

if (
    !(
        searchResult.expanded === 'true' &&
        searchResult.visible &&
        searchResult.hasInput
    )
) {
    failures.push(`Search disclosure failed: ${JSON.stringify(searchResult)}`);
}

if (
    !(
        mobileNavResult.expanded === 'true' &&
        mobileNavResult.visible &&
        mobileNavResult.linkCount >= 7
    )
) {
    failures.push(`Mobile nav failed: ${JSON.stringify(mobileNavResult)}`);
}

if (
    !(
        inertiaNavResult.path === '/about' &&
        /About|Bel Zamora/i.test(inertiaNavResult.h1)
    )
) {
    failures.push(`Inertia navigation failed: ${JSON.stringify(inertiaNavResult)}`);
}

if (inertiaNavErrors.length > 0) {
    failures.push(
        `Inertia navigation console errors: ${JSON.stringify(inertiaNavErrors)}`,
    );
}

for (const filterResult of filterResults) {
    if (!(filterResult.hasNoResult && filterResult.hasReset)) {
        failures.push(`Filter no-result failed ${filterResult.path}`);
    }
}

if (!contactErrorResult || contactErrorResult.errors.length < 3) {
    failures.push('Contact validation errors were not rendered.');
}

if (!contactSuccessResult?.hasConfirmation) {
    failures.push('Contact success confirmation was not rendered.');
}

const report = {
    generatedAt: new Date().toISOString(),
    base,
    routesTested: routes,
    viewports: ['desktop 1366x900', 'mobile 390x844 DPR2'],
    screenshotDir,
    routeSummary: routeResults.map((result) => ({
        viewport: result.viewport,
        route: result.route,
        title: result.audit.title,
        h1: result.audit.h1,
        interactive: result.audit.visibleInteractiveCount,
        smallTargetCount: result.audit.smallTargets.length,
        overflow: result.audit.horizontalOverflow,
        canonical: result.audit.canonical,
        jsonLd: result.audit.jsonLd,
        errors: result.errors,
    })),
    keyboard: { firstFocus, skipResult },
    searchResult,
    mobileNavResult,
    inertiaNavResult,
    inertiaNavErrors,
    filterResults,
    contactErrorCount: contactErrorResult?.errors.length ?? 0,
    contactSuccess: {
        path: contactSuccessResult?.path ?? '',
        hasConfirmation: Boolean(contactSuccessResult?.hasConfirmation),
    },
    failures,
};

await fs.writeFile(
    `${screenshotDir}/phase6-browser-acceptance.json`,
    JSON.stringify(report, null, 2),
);

console.log(
    JSON.stringify(
        {
            generatedAt: report.generatedAt,
            routeChecks: report.routeSummary.length,
            screenshotDir,
            keyboard: report.keyboard,
            searchResult,
            mobileNavResult,
            inertiaNavResult,
            filterResults,
            contactErrorCount: report.contactErrorCount,
            contactSuccess: report.contactSuccess,
            failures,
        },
        null,
        2,
    ),
);

client.close();

if (failures.length > 0) {
    process.exit(1);
}
