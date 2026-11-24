@echo off
cd /d "%~dp0"
if not exist node_modules (
  echo Instalando dependencias de la API...
  call npm install
)
if not exist .env copy .env.example .env
echo.
echo Iniciando API local de CSB 360...
call npm start
pause
