@echo off
cd /d "%~dp0"

set "PHP_EXE=php"
set "PHP_INI=-d max_execution_time=120 -d memory_limit=256M -d upload_max_filesize=100M -d post_max_size=105M"

if exist "C:\tools\php85\php.exe" (
    set "PHP_EXE=C:\tools\php85\php.exe"
    if exist "%~dp0php-serve.ini" (
        set "PHP_INI=-c "%~dp0php-serve.ini""
    ) else (
        set "PHP_INI=-d extension_dir=C:/tools/php85/ext -d extension=gd -d max_execution_time=120 -d memory_limit=256M -d upload_max_filesize=100M -d post_max_size=105M"
    )
)

echo [1/2] Prechauffage du cache Symfony (evite le timeout a la 1ere visite)...
"%PHP_EXE%" %PHP_INI% -d max_execution_time=0 bin/console cache:warmup --no-optional-warmers
if errorlevel 1 (
    echo Echec du warmup. Tentative avec suppression du cache...
    rmdir /s /q var\cache\dev 2>nul
    "%PHP_EXE%" %PHP_INI% -d max_execution_time=0 bin/console cache:warmup --no-optional-warmers
)

echo.
echo [2/2] Demarrage du serveur sur http://127.0.0.1:8000
echo PHP : %PHP_EXE%
echo INI : %PHP_INI%
"%PHP_EXE%" %PHP_INI% -r "echo extension_loaded('gd') ? 'GD: OK' : 'GD: MANQUANT'; echo PHP_EOL;" 2>nul
echo Limites upload : upload_max_filesize=100M, post_max_size=105M
echo Appuyez sur Ctrl+C pour arreter.
echo.
"%PHP_EXE%" %PHP_INI% -S 127.0.0.1:8000 -t public
