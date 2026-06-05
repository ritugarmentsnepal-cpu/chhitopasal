@echo off
echo ============================================
echo   Laravel Scheduler Worker
echo   (Required for Pathao auto-sync to work)
echo ============================================
echo.
echo Starting scheduler... Press Ctrl+C to stop.
echo.
cd /d "%~dp0"
php artisan schedule:work
