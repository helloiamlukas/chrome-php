const puppeteer = require('puppeteer');

const options = JSON.parse(process.argv[2]);

const runChrome = async () => {
        let browser;
        let page;

        try {
            browser = await puppeteer.launch({
                ignoreHTTPSErrors: true,
                executablePath: options.path
            });

            page = await browser.newPage();

            if (options.userAgent)
                await page.setUserAgent(options.userAgent);

            if (options.viewport)
                await page.setViewport(options.viewport);

            if (options.headers)
                await page.setExtraHTTPHeaders(options.headers);

            if (options.blacklist && options.blacklist.length) {
                await page.setRequestInterception(true);
                page.on('request', req => {
                    const block = ['www.google-analytics.com', '/gtag/js', 'ga.js', 'analytics.js'];
                    if (block.find(regex => req.url().match(regex))) return req.abort();
                    req.continue();
                });
            }


            /*
                The following code is taken from
                https://github.com/intoli/intoli-article-materials/tree/master/articles/not-possible-to-block-chrome-headless
             */
            await page.evaluateOnNewDocument(() => {
                // Pass the Chrome Test.
                window.navigator.chrome = JSON.parse('{"app":{"isInstalled":false},"webstore":{"onInstallStageChanged":{},"onDownloadProgress":{}},"runtime":{"PlatformOs":{"MAC":"mac","WIN":"win","ANDROID":"android","CROS":"cros","LINUX":"linux","OPENBSD":"openbsd"},"PlatformArch":{"ARM":"arm","X86_32":"x86-32","X86_64":"x86-64"},"PlatformNaclArch":{"ARM":"arm","X86_32":"x86-32","X86_64":"x86-64"},"RequestUpdateCheckStatus":{"THROTTLED":"throttled","NO_UPDATE":"no_update","UPDATE_AVAILABLE":"update_available"},"OnInstalledReason":{"INSTALL":"install","UPDATE":"update","CHROME_UPDATE":"chrome_update","SHARED_MODULE_UPDATE":"shared_module_update"},"OnRestartRequiredReason":{"APP_UPDATE":"app_update","OS_UPDATE":"os_update","PERIODIC":"periodic"}}}');

                // Pass the Permissions Test.
                const originalQuery = window.navigator.permissions.query;
                window.navigator.permissions.query = (parameters) => (
                    parameters.name === 'notifications' ?
                        Promise.resolve({state: Notification.permission}) :
                        originalQuery(parameters)
                );

                // Pass the Webdriver Test.
                Object.defineProperty(navigator, 'webdriver', {
                    get: () => false,
                });

                // Pass the Plugins Length Test.
                // Overwrite the `plugins` property to use a custom getter.
                Object.defineProperty(navigator, 'plugins', {
                    // This just needs to have `length > 0` for the current test,
                    // but we could mock the plugins too if necessary.
                    get: () => [1, 2, 3, 4, 5],
                });

                // Pass the Languages Test.
                // Overwrite the `plugins` property to use a custom getter.
                Object.defineProperty(navigator, 'languages', {
                    get: () => ['en-US', 'en'],
                });
            });
            /* */

            const response = await page.goto(options.url, {});

            if (response.status() >= 400) {
                throw new Error('HTTP Response: ' + response.status());
            }

            const output = await page.evaluate(() => document.documentElement.outerHTML);

            console.log(output);

            await browser.close();
        }
        catch (exception) {
            if (browser) await browser.close();
            console.error(exception);
            process.exit(1);
        }
    }
;

runChrome();