@echo off
cd /d "%~dp0"

echo [1/2] Prechauffage du cache Symfony (evite le timeout a la 1ere visite)...
php -d max_execution_time=0 bin/console cache:warmup --no-optional-warmers
if errorlevel 1 (
    echo Echec du warmup. Tentative avec suppression du cache...
    rmdir /s /q var\cache\dev 2>nul
    php -d max_execution_time=0 bin/console cache:warmup --no-optional-warmers
)

echo.
echo [2/2] Demarrage du serveur sur http://127.0.0.1:8000
echo Appuyez sur Ctrl+C pour arreter.
echo.
php -d max_execution_time=120 -d memory_limit=256M -S 127.0.0.1:8000 -t public
