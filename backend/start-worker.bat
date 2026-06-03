@echo off
echo Starting Laravel Queue Worker...
php artisan queue:work --tries=3 --backoff=10,30,60
pause
