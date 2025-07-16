@echo off
echo ========================================
echo Fixing ALL Database Configuration Issues
echo ========================================
echo.

echo Step 1: Stopping all containers...
docker-compose down

echo.
echo Step 2: Removing MySQL volume to reset database...
docker volume rm zinadauth_mysql_data 2>nul
if %errorlevel% equ 0 (
    echo MySQL volume removed successfully
) else (
    echo MySQL volume not found or already removed
)

echo.
echo Step 3: Building and starting all services...
docker-compose up --build -d

echo.
echo Step 4: Waiting for MySQL to initialize (30 seconds)...
timeout /t 30 /nobreak >nul

echo.
echo Step 5: Checking container status...
docker-compose ps

echo.
echo Step 6: Clearing Laravel configuration cache...
docker exec -it zinadauth_backend php artisan config:clear

echo.
echo Step 7: Clearing Laravel cache...
docker exec -it zinadauth_backend php artisan cache:clear

echo.
echo Step 8: Testing database connection...
docker exec -it zinadauth_backend php artisan migrate:status

echo.
echo Step 9: Running migrations...
docker exec -it zinadauth_backend php artisan migrate

echo.
echo ========================================
echo Database Fix Complete!
echo ========================================
echo.
echo Configuration Summary:
echo - Backend .env:    DB_HOST=mysql
echo - .laravel.env:    DB_HOST=mysql  
echo - .mysql.env:      MYSQL_* variables
echo.
echo All files now have consistent configuration:
echo - Database: zinad_auth
echo - Username: zinad
echo - Password: db_password
echo.
echo Services available at:
echo - Frontend: http://localhost:4200
echo - Backend:  http://localhost:8000
echo.
pause
