# CSB 360 — App móvil del estudiante

Aplicación móvil (React Native + Expo) para estudiantes de la Unidad Educativa San Bernardo
(Tarija, Bolivia). Complementa el sistema web **CSB 360**: consulta de notas, asistencia,
horarios, comunicados, pagos e inscripción en línea.

## Estructura

```
App_360/
├── mobile/                 App Expo (React Native + TypeScript + Expo Router)
│   ├── src/app/            Rutas: splash, login, tabs y detalle de materia
│   ├── src/components/ui/  Componentes base (Card, Button, Chip, ProgressRing…)
│   ├── src/data/           Datos de ejemplo mientras no hay API
│   ├── src/lib/            Cliente HTTP y almacenamiento de sesión
│   ├── src/store/          Estado de sesión (Zustand)
│   └── src/theme/          Tokens de diseño (#1A8C1A, Outfit)
├── Docs/                   Entregables de la materia (Actividad 02 y 03) + PDFs
├── prototipo-figma/        Prototipo navegable exportado de Figma Make
└── scripts/                Generación de documentos y capturas
```

## Requisitos

- Node.js 20+
- Expo Go en el celular (misma red Wi-Fi) o emulador Android
- Para la API: Laravel 12 + MySQL (repositorio `360`)

## Correr la app

```bash
cd mobile
npm install
npx expo start --lan
```

Escaneá el QR con Expo Go o abrí `exp://<IP-de-la-PC>:8081`.

## Estado

- [x] Splash, login, onboarding y 5 pestañas (Inicio, Notas, Asistencia, Comunicados, Perfil)
- [x] Detalle de materia con criterios SE/SA/HA/AU
- [x] Logo institucional y tokens de diseño
- [x] Datos de ejemplo (mock)
- [ ] API v1 con Sanctum en el sistema Laravel (`/api/v1`)
- [ ] Inscripción en línea con cámara (FormData)
- [ ] Notificaciones push

## Documentos

- `Docs/Actividad-02-Grupo-7-Idea de Proyecto.docx|pdf`
- `Docs/Actividad-03-Grupo-7-Mis Pantallas.docx|pdf`
- `Docs/prompt-figma-csb360.md`

Generación: `python scripts/generar_documentos.py` (requiere `python-docx`).
