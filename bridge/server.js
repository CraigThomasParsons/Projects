const { chromium } = require('playwright');
const WebSocket = require('ws');
require('dotenv').config();

const wss = new WebSocket.Server({ port: 3001 });
let browser;
let context;
let page;

async function initBrowser() {
    console.log('[Bridge] Initializing browser with persistent context...');
    const userDataDir = './user_data';

    // Launch persistent context to save login state
    context = await chromium.launchPersistentContext(userDataDir, {
        headless: false,
        viewport: null // Uses actual window size
    });

    // Get the first page or create new one
    const pages = context.pages();
    page = pages.length > 0 ? pages[0] : await context.newPage();

    await page.goto('https://chatgpt.com/');
    console.log('[Bridge] Browser ready at chatgpt.com');
}

wss.on('connection', (ws) => {
    console.log('[Bridge] Client connected');

    ws.on('message', async (message) => {
        const data = JSON.parse(message);
        console.log('[Bridge] Received message:', data);

        if (data.type === 'send_message') {
            try {
                console.log('[Bridge] Attempting to send message to ChatGPT...');

                // Wait for the textarea to be available
                await page.waitForSelector('#prompt-textarea', { timeout: 5000 });

                // Clear and type
                await page.fill('#prompt-textarea', data.content);
                console.log('[Bridge] Text filled into textarea');

                // Press Enter to send
                await page.press('#prompt-textarea', 'Enter');
                console.log('[Bridge] Enter key pressed, waiting for response...');

                // Wait a moment for the response to start
                await page.waitForTimeout(1000);

                // This is a simplified streaming implementation.
                let lastContent = '';
                let pollCount = 0;
                const maxPolls = 150; // 30 seconds max

                const interval = setInterval(async () => {
                    pollCount++;

                    try {
                        // Try multiple selectors for assistant messages
                        const selectors = [
                            '[data-message-author-role="assistant"]',
                            '.agent-turn',
                            '[class*="agent"]'
                        ];

                        let assistantMessages = [];
                        for (const selector of selectors) {
                            assistantMessages = await page.$$(selector);
                            if (assistantMessages.length > 0) {
                                console.log(`[Bridge] Found ${assistantMessages.length} messages with selector: ${selector}`);
                                break;
                            }
                        }

                        if (assistantMessages.length > 0) {
                            const lastMessage = assistantMessages[assistantMessages.length - 1];
                            const content = await lastMessage.innerText();

                            if (content && content !== lastContent) {
                                const delta = content.substring(lastContent.length);
                                console.log('[Bridge] Streaming delta:', delta.substring(0, 50) + '...');
                                ws.send(JSON.stringify({
                                    type: 'delta',
                                    content: delta
                                }));
                                lastContent = content;
                            }

                            // Check if ChatGPT is done
                            const sendButton = await page.$('[data-testid="send-button"]');
                            const isVisible = sendButton ? await sendButton.isVisible() : false;

                            if (isVisible && content === lastContent && lastContent.length > 0) {
                                clearInterval(interval);
                                console.log('[Bridge] Response complete. Total length:', lastContent.length);
                                ws.send(JSON.stringify({ type: 'done', content: lastContent }));
                            }
                        }

                        if (pollCount >= maxPolls) {
                            clearInterval(interval);
                            console.log('[Bridge] Timeout waiting for response');
                            ws.send(JSON.stringify({ type: 'error', message: 'Timeout waiting for response' }));
                        }
                    } catch (err) {
                        console.error('[Bridge] Error in polling loop:', err);
                    }
                }, 200);

            } catch (error) {
                console.error('[Bridge] Error sending message:', error);
                ws.send(JSON.stringify({ type: 'error', message: error.message }));
            }
        }
    });

    ws.on('close', () => {
        console.log('[Bridge] Client disconnected');
    });
});

initBrowser().catch(err => {
    console.error('[Bridge] Failed to init browser:', err);
});
