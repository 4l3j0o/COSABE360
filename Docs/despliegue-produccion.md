# Despliegue a producción

## Variables obligatorias de `.env`

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://360.cosabe.edu.bo
SESSION_SECURE_COOKIE=true
SESSION_ENCRYPT=true
SESSION_SAME_SITE=lax
```

- `APP_DEBUG` debe quedar en `false`. Si por error queda en `true`, la aplicación lo fuerza a `false` en producción y registra un evento crítico.
- `APP_KEY` debe ser única del servidor.
- La base de datos debe usar un usuario dedicado, sin permisos de superusuario, y escuchar solo en localhost.

## Comandos de despliegue

```powershell
php composer.phar install --no-dev --optimize-autoloader
npm ci
npm run build
php artisan migrate --force
php artisan db:seed --class=PermissionSeeder --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan queue:restart
```

## Verificación posterior

- Abrir `/login` por HTTPS.
- Revisar que no aparezca depuración ante un error.
- Probar login con Google con una cuenta autorizada.
- Probar descarga de un archivo privado con un usuario sin permiso: debe dar 403.
- Confirmar que `storage/logs/security.log` recibe eventos.
- Verificar backup y restauración antes de abrir producción.
