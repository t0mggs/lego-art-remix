@echo off
echo ==========================================
echo    VISUBLOQ - TESTING CON NGROK
echo ==========================================
echo.
echo IMPORTANTE: Esto es SOLO para testing!
echo Para produccion necesitas hosting real.
echo.
echo 1. Asegurate que XAMPP este corriendo
echo 2. Ve a http://localhost/VisuBloq/app/
echo 3. Ejecuta este script para exponer al internet
echo.
pause

echo Iniciando ngrok...
echo La URL que te aparezca la usas en Shopify webhook
echo.

REM Cambiar la ruta segun donde tengas ngrok
if exist "C:\ngrok\ngrok.exe" (
    C:\ngrok\ngrok.exe http 80
) else (
    echo ERROR: No encuentro ngrok en C:\ngrok\
    echo Descargalo de: https://ngrok.com/download
    echo Y ponlo en C:\ngrok\
    pause
)