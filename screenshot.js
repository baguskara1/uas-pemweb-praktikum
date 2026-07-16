const puppeteer = require('puppeteer');

(async () => {
    const browser = await puppeteer.launch({ headless: 'new' });
    const page = await browser.newPage();
    await page.setViewport({ width: 1280, height: 800 });

    try {
        console.log("Navigating to Login...");
        await page.goto('http://localhost:8080/login.php', { waitUntil: 'networkidle2' });
        await page.screenshot({ path: 'assets/screenshots/login.png' });

        console.log("Logging in...");
        await page.type('input[name="username"]', 'admin');
        await page.type('input[name="password"]', 'admin123');
        await Promise.all([
            page.waitForNavigation({ waitUntil: 'networkidle2' }),
            page.click('button[type="submit"]')
        ]);

        console.log("Screenshot Dashboard...");
        await page.screenshot({ path: 'assets/screenshots/dashboard.png' });

        console.log("Screenshot POS Baru...");
        await page.goto('http://localhost:8080/pages/transaksi/baru.php', { waitUntil: 'networkidle2' });
        await page.screenshot({ path: 'assets/screenshots/pos.png' });

        console.log("Screenshot Data Pelanggan...");
        await page.goto('http://localhost:8080/pages/pelanggan/index.php', { waitUntil: 'networkidle2' });
        await page.screenshot({ path: 'assets/screenshots/pelanggan.png' });

        console.log("Screenshots captured successfully!");
    } catch (e) {
        console.error("Error capturing screenshots:", e);
    } finally {
        await browser.close();
    }
})();
