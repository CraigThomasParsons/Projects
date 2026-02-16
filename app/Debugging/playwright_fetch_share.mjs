import { chromium } from "playwright";

/**
 * Fetch a ChatGPT share page using Playwright and print HTML to stdout.
 *
 * Usage:
 *   node app/Debugging/playwright_fetch_share.mjs <share-url>
 */
const shareUrl = process.argv[2];

if (!shareUrl) {
    console.error("Missing share URL.");
    process.exit(1);
}

const browser = await chromium.launch({ headless: true });
const page = await browser.newPage({
    userAgent: "Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/122 Safari/537.36",
});

try {
    await page.goto(shareUrl, { waitUntil: "networkidle", timeout: 60000 });
    await page.waitForTimeout(2000);
    const htmlContent = await page.content();
    process.stdout.write(htmlContent);
} catch (error) {
    console.error(`Playwright failed: ${error?.message ?? error}`);
    process.exit(1);
} finally {
    await browser.close();
}
