const { chromium } = require('playwright-core');
const path = require('path');
const OUT = path.join(__dirname, 'capturas-dia2');
(async () => {
  const browser = await chromium.launch({ channel: 'msedge', headless: true });
  const context = await browser.newContext({ viewport: { width: 390, height: 844 }, deviceScaleFactor: 2, isMobile: true, hasTouch: true, locale: 'es-BO' });
  await context.route('**/date.nager.at/**', (route) => route.abort());
  const page = await context.newPage();
  await page.goto('http://127.0.0.1:8081/feriados', { waitUntil: 'domcontentloaded', timeout: 60000 });
  await page.waitForTimeout(6000);
  await page.screenshot({ path: path.join(OUT, '11-feriados-error.png') });
  console.log('OK');
  await browser.close();
})();
