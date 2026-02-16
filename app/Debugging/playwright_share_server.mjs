import http from "node:http";
import { chromium } from "playwright";
import { URL } from "node:url";

/**
 * Lightweight Playwright HTTP server for share page HTML.
 *
 * Start with:
 *   node app/Debugging/playwright_share_server.mjs
 *
 * Then request:
 *   http://localhost:3031/share?url=https://chatgpt.com/share/...
 */
const port = Number.parseInt(process.env.PLAYWRIGHT_SHARE_PORT ?? "3031", 10);

const server = http.createServer(async (request, response) => {
    if (!request.url) {
        response.writeHead(400, { "Content-Type": "text/plain" });
        response.end("Missing request URL.");
        return;
    }

    const requestUrl = new URL(request.url, `http://localhost:${port}`);

    if (requestUrl.pathname !== "/share") {
        response.writeHead(404, { "Content-Type": "text/plain" });
        response.end("Not found.");
        return;
    }

    const shareUrl = requestUrl.searchParams.get("url");

    if (!shareUrl) {
        response.writeHead(400, { "Content-Type": "text/plain" });
        response.end("Missing url parameter.");
        return;
    }

    const browser = await chromium.launch({ headless: true });
    const page = await browser.newPage({
        userAgent: "Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/122 Safari/537.36",
    });

    try {
        await page.goto(shareUrl, { waitUntil: "networkidle", timeout: 60000 });
        await page.waitForTimeout(2000);
        const htmlContent = await page.content();

        response.writeHead(200, { "Content-Type": "text/html" });
        response.end(htmlContent);
    } catch (error) {
        response.writeHead(500, { "Content-Type": "text/plain" });
        response.end(`Playwright failed: ${error?.message ?? error}`);
    } finally {
        await browser.close();
    }
});

server.listen(port, () => {
    // eslint-disable-next-line no-console
    console.log(`Playwright share server listening on port ${port}`);
});
