const { chromium } = require('playwright-core');
const path = require('path');

const OUT = path.join(__dirname, 'capturas-dia3');
const BASE = 'http://127.0.0.1:8081';

(async () => {
  const fs = require('fs');
  fs.mkdirSync(OUT, { recursive: true });
  const browser = await chromium.launch({ channel: 'msedge', headless: true });
  const context = await browser.newContext({
    viewport: { width: 390, height: 844 },
    deviceScaleFactor: 2,
    isMobile: true,
    hasTouch: true,
    locale: 'es-BO',
  });
  const page = await context.newPage();
  page.on('console', (m) => {
    if (m.type() === 'error') console.log('[console]', m.text().slice(0, 300));
  });
  page.on('pageerror', (e) => console.log('[pageerror]', e.message.slice(0, 300)));
  page.on('requestfailed', (r) => console.log('[failed]', r.url(), r.failure()?.errorText));

  await page.goto(BASE, { waitUntil: 'networkidle', timeout: 180000 });
  await page.waitForTimeout(5000);
  await page.screenshot({ path: path.join(OUT, 'prueba-login.png') });
  console.log('login title:', await page.title());

  const demo = page.getByText('Entrar con el usuario de prueba');
  console.log('demo count:', await demo.count());
  if (await demo.count()) {
    await demo.first().click({ force: true });
    await page.waitForTimeout(5000);
    await page.screenshot({ path: path.join(OUT, 'prueba-inicio.png') });
    console.log('url after login:', page.url());
    await page.goto(BASE + '/pendientes', { waitUntil: 'domcontentloaded', timeout: 60000 });
    await page.waitForTimeout(4000);
    await page.screenshot({ path: path.join(OUT, 'prueba-pendientes.png') });
    console.log('body text:', (await page.locator('body').innerText()).slice(0, 500));
  }

  await browser.close();
})();
