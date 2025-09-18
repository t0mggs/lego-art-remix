@echo off
echo ==========================================
echo     VISUBLOQ - SISTEMA COMPLETO
echo ==========================================
echo.
echo 1. Iniciando ngrok para exponer servidor...
echo 2. Tu URL se mostrara aqui
echo 3. Esa URL la usaras en GitHub Pages
echo.

REM Verificar que XAMPP este corriendo
echo Verificando XAMPP...
tasklist /FI "IMAGENAME eq httpd.exe" 2>NUL | find /I /N "httpd.exe">NUL
if "%ERRORLEVEL%"=="0" (
    echo ✅ Apache esta corriendo
) else (
    echo ❌ Apache NO esta corriendo - Inicia XAMPP primero
    pause
    exit
)

echo.
echo Iniciando ngrok...
echo IMPORTANTE: Copia la URL https que aparezca
echo.

C:\ngrok\ngrok.exe http 80