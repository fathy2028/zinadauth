# Zinad Auth Docker Makefile
# Replaces fix-all-database-issues.bat with cross-platform Docker automation

.PHONY: setup clean build up down status logs test migrate seed fresh shell help

# Default target
.DEFAULT_GOAL := setup

# Colors for output
BLUE = \033[0;34m
GREEN = \033[0;32m
YELLOW = \033[1;33m
RED = \033[0;31m
NC = \033[0m

# Project variables
PROJECT_NAME = zinadauth
BACKEND_CONTAINER = zinadauth_backend
MYSQL_CONTAINER = zinadauth_mysql

## Main setup command - replaces fix-all-database-issues.bat
setup:
	@echo "$(BLUE)========================================"
	@echo "Fixing ALL Database Configuration Issues"
	@echo "========================================$(NC)"
	@echo ""
	@echo "$(BLUE)Step 1: Stopping all containers...$(NC)"
	@docker-compose down || true
	@echo ""
	@echo "$(BLUE)Step 2: Removing MySQL volume to reset database...$(NC)"
	@docker volume rm $(PROJECT_NAME)_mysql_data 2>/dev/null && echo "$(GREEN)MySQL volume removed successfully$(NC)" || echo "$(YELLOW)MySQL volume not found or already removed$(NC)"
	@echo ""
	@echo "$(BLUE)Step 3: Checking .env files configuration...$(NC)"
	@$(MAKE) check-env-files
	@echo ""
	@echo "$(BLUE)Step 4: Building and starting all services...$(NC)"
	@docker-compose up --build -d
	@echo ""
	@echo "$(BLUE)Step 5: Waiting for MySQL to initialize (45 seconds)...$(NC)"
	@timeout /t 45 /nobreak >nul
	@echo ""
	@echo "$(BLUE)Step 6: Checking container status...$(NC)"
	@docker-compose ps
	@echo ""
	@echo "$(BLUE)Step 7: Generating application key if needed...$(NC)"
	@docker exec $(BACKEND_CONTAINER) php artisan key:generate --force
	@echo ""
	@echo "$(BLUE)Step 8: Generating JWT secret if needed...$(NC)"
	@docker exec $(BACKEND_CONTAINER) php artisan jwt:secret --force
	@echo ""
	@echo "$(BLUE)Step 9: Running migrations and seeders...$(NC)"
	@docker exec $(BACKEND_CONTAINER) php artisan migrate --force
	@docker exec $(BACKEND_CONTAINER) php artisan db:seed --class=RolePermissionSeeder
	@echo ""
	@echo "$(BLUE)Step 10: Clearing Laravel configuration cache...$(NC)"
	@docker exec $(BACKEND_CONTAINER) php artisan config:clear
	@echo ""
	@echo "$(BLUE)Step 11: Clearing Laravel cache...$(NC)"
	@docker exec $(BACKEND_CONTAINER) php artisan cache:clear
	@echo ""
	@echo "$(BLUE)Step 12: Clearing route cache...$(NC)"
	@docker exec $(BACKEND_CONTAINER) php artisan route:clear
	@echo ""
	@echo "$(BLUE)Step 13: Testing database connection...$(NC)"
	@docker exec $(BACKEND_CONTAINER) php artisan migrate:status
	@echo ""
	@echo "$(GREEN)========================================"
	@echo "Database Fix Complete!"
	@echo "========================================$(NC)"
	@echo ""
	@echo "$(YELLOW)Configuration Summary:$(NC)"
	@echo "- Root .env:       MYSQL_* variables for Docker"
	@echo "- Backend .env:    DB_HOST=mysql, Laravel config"
	@echo "- Docker Compose: Uses environment variables"
	@echo ""
	@echo "$(YELLOW)New Database Configuration:$(NC)"
	@echo "- Database: zinadauth"
	@echo "- Username: zinadauth_user"
	@echo "- Password: zinadauth_password"
	@echo "- Host: mysql (Docker service)"
	@echo "- Port: 3306"
	@echo ""
	@echo "$(YELLOW)Services available at:$(NC)"
	@echo "- Frontend: http://localhost:4200"
	@echo "- Backend:  http://localhost:8000"
	@echo ""
	@echo "$(YELLOW)Default test users created:$(NC)"
	@echo "- Admin: admin@zinadauth.com (password123)"
	@echo "- Facilitator: facilitator@zinadauth.com (password123)"
	@echo "- Participant: participant@zinadauth.com (password123)"
	@echo ""
	@echo "$(YELLOW)Roles and Permissions system is ready!$(NC)"
	@echo "Run 'docker exec $(BACKEND_CONTAINER) php artisan test:roles-permissions' to verify."
	@echo ""

## Check and create environment files
check-env-files:
	@if exist .env (echo "Root .env file exists") else (echo "Creating root .env file..." && echo MYSQL_DATABASE=zinadauth > .env && echo MYSQL_USER=zinadauth_user >> .env && echo MYSQL_PASSWORD=zinadauth_password >> .env && echo MYSQL_ROOT_PASSWORD=root_password >> .env && echo DB_HOST=mysql >> .env && echo DB_PORT=3306 >> .env && echo DB_DATABASE=zinadauth >> .env && echo DB_USERNAME=zinadauth_user >> .env && echo DB_PASSWORD=zinadauth_password >> .env)
	@if exist backend\.env (echo "Backend .env file exists") else (echo "ERROR: Backend .env file missing! Please ensure backend/.env exists." && exit 1)

## Clean up all containers and volumes
clean:
	@echo "$(BLUE)Cleaning up Docker resources...$(NC)"
	@docker-compose down -v --remove-orphans || true
	@docker volume rm $(PROJECT_NAME)_mysql_data 2>/dev/null || true
	@echo "$(GREEN)Cleanup complete$(NC)"

## Build and start services
build:
	@echo "$(BLUE)Building and starting services...$(NC)"
	@docker-compose up --build -d

## Start services
up:
	@echo "$(BLUE)Starting services...$(NC)"
	@docker-compose up -d

## Stop services
down:
	@echo "$(BLUE)Stopping services...$(NC)"
	@docker-compose down

## Show container status
status:
	@echo "$(BLUE)Container Status:$(NC)"
	@docker-compose ps

## Show logs
logs:
	@echo "$(BLUE)Showing logs...$(NC)"
	@docker-compose logs -f

## Run tests
test:
	@echo "$(BLUE)Running tests...$(NC)"
	@docker exec $(BACKEND_CONTAINER) php artisan test

## Run database migrations
migrate:
	@echo "$(BLUE)Running database migrations...$(NC)"
	@docker exec $(BACKEND_CONTAINER) php artisan migrate --force

## Run database seeders
seed:
	@echo "$(BLUE)Running database seeders...$(NC)"
	@docker exec $(BACKEND_CONTAINER) php artisan db:seed --class=RolePermissionSeeder --force

## Fresh migration with seeding
fresh:
	@echo "$(BLUE)Fresh migration with seeding...$(NC)"
	@docker exec $(BACKEND_CONTAINER) php artisan migrate:fresh --force
	@docker exec $(BACKEND_CONTAINER) php artisan db:seed --class=RolePermissionSeeder --force

## Access backend shell
shell:
	@docker exec -it $(BACKEND_CONTAINER) bash

## Show help
help:
	@echo "$(BLUE)Zinad Auth Docker Commands$(NC)"
	@echo "=========================="
	@echo ""
	@echo "$(GREEN)setup$(NC)    - Complete setup (replaces fix-all-database-issues.bat)"
	@echo "$(GREEN)clean$(NC)    - Clean up containers and volumes"
	@echo "$(GREEN)build$(NC)    - Build and start services"
	@echo "$(GREEN)up$(NC)       - Start services"
	@echo "$(GREEN)down$(NC)     - Stop services"
	@echo "$(GREEN)status$(NC)   - Show container status"
	@echo "$(GREEN)logs$(NC)     - Show logs"
	@echo "$(GREEN)test$(NC)     - Run tests"
	@echo "$(GREEN)migrate$(NC)  - Run database migrations"
	@echo "$(GREEN)seed$(NC)     - Run database seeders"
	@echo "$(GREEN)fresh$(NC)    - Fresh migration with seeding"
	@echo "$(GREEN)shell$(NC)    - Access backend shell"
	@echo "$(GREEN)help$(NC)     - Show this help"
	@echo ""
	@echo "$(YELLOW)Quick start: make setup$(NC)"
	@echo "$(YELLOW)Database commands: make migrate, make seed, make fresh$(NC)"
