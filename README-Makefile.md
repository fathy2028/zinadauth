# Docker Makefile Documentation

This Makefile provides automated Docker setup and management commands for the ZinadAuth project. It replaces the previous `fix-all-database-issues.bat` file with cross-platform automation.

## Prerequisites

- **Docker** and **Docker Compose** installed
- **Make** utility installed (GNU Make 4.4.1 or later)
- **Git** (for version control)

### Installing Make on Windows

If you don't have make installed on Windows, you can install it using:

```bash
# Using Chocolatey
choco install make

# Using Scoop
scoop install make

# Using winget
winget install GnuWin32.Make
```

## Available Commands

### Main Commands

#### `make setup` (Default)
Runs the complete Docker setup process. This is the main command that replaces the old batch file.

**What it does:**
1. Stops all running containers
2. Removes MySQL volume to reset database
3. Checks and creates environment files
4. Builds all Docker services
5. Starts services in detached mode
6. Waits for MySQL initialization (45 seconds)
7. Generates Laravel application key
8. Generates JWT secret key
9. Runs database migrations and seeders
10. Clears Laravel caches (config, application, routes)
11. Tests database connection
12. Shows setup completion message with default user credentials

```bash
make setup
# or simply
make
```

### Service Management Commands

#### `make clean`
Stops all containers and removes MySQL volume for a fresh start.

```bash
make clean
```

#### `make build`
Builds all Docker services without starting them.

```bash
make build
```

#### `make up`
Starts all services in detached mode.

```bash
make up
```

#### `make down`
Stops all running containers.

```bash
make down
```

### Utility Commands

#### `make status`
Shows the status of all Docker containers.

```bash
make status
```

#### `make logs`
Shows logs from all services. Add service name to see specific logs.

```bash
make logs
# or for specific service
make logs SERVICE=backend
```

#### `make test`
Runs the application test suite.

```bash
make test
```

#### `make shell`
Opens a shell in the backend container for debugging.

```bash
make shell
```

#### `make help`
Displays all available commands with descriptions.

```bash
make help
```

## Environment Configuration

The Makefile automatically creates and manages environment files:

### Root `.env` file
Created automatically with MySQL configuration:
```env
MYSQL_DATABASE=zinadauth
MYSQL_USER=zinadauth_user
MYSQL_PASSWORD=zinadauth_password
MYSQL_ROOT_PASSWORD=root_password
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=zinadauth
DB_USERNAME=zinadauth_user
DB_PASSWORD=zinadauth_password
```

### Backend `.env` file
Must exist in `backend/.env` - the Makefile will check for its presence but won't create it automatically.

## Default User Accounts

After running `make setup`, the following default users are available:

- **Admin**: admin@zinadauth.com (password123)
- **Facilitator**: facilitator@zinadauth.com (password123)
- **Participant**: participant@zinadauth.com (password123)

## Container Configuration

The Makefile works with these Docker containers:

- **Backend**: `zinadauth-backend-1` (Laravel API)
- **Frontend**: `zinadauth-frontend-1` (Angular application)
- **MySQL**: `zinadauth-mysql-1` (Database)

## Troubleshooting

### Common Issues

1. **Make command not found**
   ```bash
   # Install make (see Prerequisites section)
   ```

2. **Permission denied errors**
   ```bash
   # On Linux/Mac, ensure Docker daemon is running
   sudo systemctl start docker
   ```

3. **Port conflicts**
   ```bash
   # Check if ports 3306, 8000, 4200 are available
   netstat -an | grep :8000
   ```

4. **MySQL initialization timeout**
   ```bash
   # Increase wait time in Makefile if needed
   # Or run: make clean && make setup
   ```

### Debugging Commands

```bash
# Check container status
make status

# View logs
make logs

# Access backend container
make shell

# Clean restart
make clean && make setup
```

## Development Workflow

### Initial Setup
```bash
# First time setup
git clone <repository>
cd zinadauth
make setup
```

### Daily Development
```bash
# Start services
make up

# View logs during development
make logs

# Run tests
make test

# Stop services when done
make down
```

### Reset Environment
```bash
# Complete reset (removes all data)
make clean
make setup
```

## API Testing

After setup, you can test the API:

**Base URL**: `http://localhost:8000/api`

**Login Endpoint**: `POST /auth/login`
```json
{
  "email": "admin@zinadauth.com",
  "password": "password123"
}
```

## Notes

- The Makefile is cross-platform compatible (Windows, Linux, macOS)
- All commands include colored output for better readability
- Error handling is built into each step
- The setup process is idempotent (safe to run multiple times)
- MySQL data persists between container restarts unless volume is removed
