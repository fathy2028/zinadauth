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
echo Step 3: Checking .env files configuration...
if exist ".env" (
    echo Root .env file exists
) else (
    echo Creating root .env file...
    echo MYSQL_DATABASE=zinadauth > .env
    echo MYSQL_USER=zinadauth_user >> .env
    echo MYSQL_PASSWORD=zinadauth_password >> .env
    echo MYSQL_ROOT_PASSWORD=root_password >> .env
    echo DB_HOST=mysql >> .env
    echo DB_PORT=3306 >> .env
    echo DB_DATABASE=zinadauth >> .env
    echo DB_USERNAME=zinadauth_user >> .env
    echo DB_PASSWORD=zinadauth_password >> .env
)

if exist "backend\.env" (
    echo Backend .env file exists
) else (
    echo ERROR: Backend .env file missing! Please ensure backend/.env exists.
    pause
    exit /b 1
)

echo.
echo Step 4: Building and starting all services...
docker-compose up --build -d

echo.
echo Step 5: Waiting for MySQL to initialize (45 seconds)...
timeout /t 45 /nobreak >nul

echo.
echo Step 6: Checking container status...
docker-compose ps

echo.
echo Step 7: Generating application key if needed...
docker exec zinadauth_backend php artisan key:generate --force

echo.
echo Step 8: Generating JWT secret if needed...
docker exec zinadauth_backend php artisan jwt:secret --force

echo.
echo Step 9: Clearing Laravel configuration cache...
docker exec zinadauth_backend php artisan config:clear

echo.
echo Step 10: Clearing Laravel cache...
docker exec zinadauth_backend php artisan cache:clear

echo.
echo Step 11: Clearing route cache...
docker exec zinadauth_backend php artisan route:clear

echo.
echo Step 12: Testing database connection...
docker exec zinadauth_backend php artisan migrate:status

echo.
echo Step 13: Running migrations...
docker exec zinadauth_backend php artisan migrate --force

echo.
echo Step 14: Running database seeders (Roles and Permissions)...
docker exec zinadauth_backend php artisan db:seed --class=RolePermissionSeeder

echo.
echo ========================================
echo Database Fix Complete!
echo ========================================
echo.
echo Configuration Summary:
echo - Root .env:       MYSQL_* variables for Docker
echo - Backend .env:    DB_HOST=mysql, Laravel config
echo - Docker Compose: Uses environment variables
echo.
echo New Database Configuration:
echo - Database: zinadauth
echo - Username: zinadauth_user
echo - Password: zinadauth_password
echo - Host: mysql (Docker service)
echo - Port: 3306
echo.
echo Services available at:
echo - Frontend: http://localhost:4200
echo - Backend:  http://localhost:8000
echo.
echo Default test users created:
echo - Admin: admin@zinadauth.com (password123)
echo - Facilitator: facilitator@zinadauth.com (password123)
echo - Participant: participant@zinadauth.com (password123)
echo.
echo Roles and Permissions system is ready!
echo Run 'docker exec zinadauth_backend php artisan test:roles-permissions' to verify.
echo.

pause
