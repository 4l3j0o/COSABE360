const { chromium } = require('playwright-core');
const fs = require('fs');
const path = require('path');

const OUT = path.join(__dirname, 'capturas-dia2');
const BASE = 'http://127.0.0.1:8081';

async function shot(page, nombre) {
  await page.waitForTimeout(1200);
  await page.screenshot({ path: path.join(OUT, nombre + '.png') });
  console.log('OK', nombre, page.url());
}

(async () => {
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
  page.on('pageerror', (e) => console.log('[pageerror]', e.message.slice(0, 160)));

  await page.goto(BASE, { waitUntil: 'networkidle', timeout: 120000 });
  await page.waitForTimeout(4000);
  await shot(page, '01-login');

  await page.getByText('Ingresar a la demo').click({ force: true });
  await page.waitForTimeout(4500);
  await shot(page, '02-inicio');

  for (const [ruta, nombre] of [
    ['/pendientes', '03-pendientes'],
    ['/notas', '04-notas'],
    ['/feriados', '05-feriados'],
    ['/perfil', '06-perfil'],
    ['/materia/mat', '07-detalle-materia'],
    ['/pendiente/p1', '08-detalle-pendiente'],
    ['/pendiente/form', '09-formulario'],
  ]) {
    await page.goto(BASE + ruta, { waitUntil: 'domcontentloaded', timeout: 60000 });
    await page.waitForTimeout(ruta === '/feriados' ? 4500 : 2000);
    await shot(page, nombre);
  }

  // Errores de validación en el formulario
  const guardar = page.getByText('Crear pendiente', { exact: true });
  if (await guardar.count()) {
    await guardar.first().click({ force: true });
    await page.waitForTimeout(900);
    await shot(page, '10-formulario-errores');
  }

  await browser.close();
})();
