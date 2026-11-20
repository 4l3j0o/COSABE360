const { chromium } = require('playwright-core');
const fs = require('fs');
const path = require('path');

const OUT = path.join(__dirname, 'capturas');
const BASE = 'http://127.0.0.1:5177/';

const pantallas = [
  ['01-splash', 'splash', 350],
  ['02-login', 'login', 900],
  ['03-datos', 'profileSetup', 900],
  ['04-inicio', 'home', 1200],
  ['05-materias', 'subjects', 900],
  ['06-detalle-materia', 'subject', 900],
  ['07-notas', 'grades', 900],
  ['08-asistencia', 'attendance', 900],
  ['09-horario', 'schedule', 900],
  ['10-comunicados', 'announcements', 900],
  ['11-comunicado', 'announcement', 900],
  ['12-inscripcion', 'enrollment', 900],
  ['13-camara', 'camera', 1200],
  ['14-pagos', 'payments', 900],
  ['15-perfil', 'profile', 900],
];

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
  page.on('pageerror', (e) => console.log('[pageerror]', e.message.slice(0, 200)));

  for (const [archivo, screen, espera] of pantallas) {
    await page.goto(`${BASE}?screen=${screen}`, { waitUntil: 'domcontentloaded', timeout: 30000 });
    await page.waitForTimeout(espera);
    await page.screenshot({ path: path.join(OUT, archivo + '.png') });
    console.log('capturada:', archivo);
  }

  await browser.close();
})();
