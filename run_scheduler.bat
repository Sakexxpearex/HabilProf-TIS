@echo off
set PROJECT_DIR=C:\Users\luckh\Downloads\HabilProf\HabilProf-TIS
set PHP_EXE=C:\Users\luckh\Downloads\php-8.4.14-nts-Win32-vs17-x64\php.exe
set LOG_FILE=%PROJECT_DIR%\scheduler_CRITICAL.log

REM 1. Navegar al directorio (fundamental)
cd %PROJECT_DIR%

echo. >> %LOG_FILE%
echo --- CRITICAL TEST INICIADO %date% %time% --- >> %LOG_FILE%

REM ⚠️ EJECUCIÓN CRÍTICA: Redirige TODAS las salidas/errores de PHP al log.
%PHP_EXE% -d display_errors=stderr artisan schedule:run >> %LOG_FILE% 2>&1

echo --- CRITICAL TEST FINALIZADO --- >> %LOG_FILE%