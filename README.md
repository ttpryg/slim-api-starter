# Slim 4 API Starter

A robust starter project using Slim Framework 4 with PHP 8.2, PHP-DI, Eloquent ORM, and Docker.

## Folder Structure

```text
.
├── app/                # Main application source code
│   ├── Action/         # API Actions (ADR Pattern — invokable classes)
│   ├── Commands/       # CLI Commands (Symfony Console)
│   ├── Database/       # Migration & Seeder infrastructure
│   ├── Exception/      # Custom exception classes
│   ├── Handler/        # Custom error handler (JSON error responses)
│   ├── Middleware/      # PSR-15 Middleware (CORS, JWT, Rate Limit)
│   ├── Model/          # Eloquent Models
│   ├── Traits/         # Reusable Traits (ResponseTrait, TransformTrait)
│   ├── Transformer/    # Fractal resource transformers
│   └── Validation/     # Request validation wrapper (Rakit)
├── config/             # Configuration (Routes, Container, Settings, DB)
├── db/                 # Database Migrations & Seeds
├── public/             # Document root (Entry point index.php)
├── storage/            # Local storage (Logs, Caches, etc.)
│   ├── logs/           # Rotating application log files
│   └── rate-limit/     # Rate limiter cache files
├── tests/              # Automated testing (PHPUnit)
├── slim                # Executable CLI tool (Symfony Console)
├── Dockerfile          # PHP 8.2-FPM image configuration
├── docker-compose.yml  # Orchestration App & Web Server (Nginx)
└── nginx.conf          # Nginx Configuration
```

## Key Features & Technologies

- **PHP 8.2**
- **Slim Framework 4** & **Slim PSR-7**
- **PHP-DI 7** (Dependency Injection)
- **Eloquent ORM** (Database Management)
- **Illuminate Database** (Database Migrations & Schema Builder)
- **Symfony Console** (Custom CLI Generator)
- **Monolog** (File-based Error Logging)
- **Laravel Pint** (Code Styling & Formatting)
- **PHPUnit** (Testing)
- **Docker & Nginx**
- **Health Check Endpoint** (`GET /health`) — Database ping & storage writability check

## How to Run

1. **Clone/Download** this project.
2. Start the container with Docker Compose:
   ```bash
   docker compose up -d --build
   ```
3. Install dependencies using composer (inside the container):
   ```bash
   docker exec -it slim_app composer install
   ```
4. Access the API at URL: `http://localhost:8080`

## Application Logging

This starter comes pre-configured with **Monolog** (`RotatingFileHandler`) for error and debug logging.
- Any unhandled exceptions or internal Slim errors will be automatically logged to:
  `storage/logs/app-YYYY-MM-DD.log` (rotated daily, max 14 files retained)
- You can inject `Psr\Log\LoggerInterface` into your actions to log custom messages manually:
  ```php
  public function __construct(private \Psr\Log\LoggerInterface $logger) {}
  
  public function __invoke(...) {
      $this->logger->info("This is a custom log entry");
  }
  ```

## CLI Tool (Slim API Starter)

This project has a built-in CLI (`./slim`) to help accelerate the development process.
All CLI commands can be executed inside the container:

```bash
docker exec -it slim_app php slim list
```

### Generator Commands
- **Make a New Action**: 
  Generates an Action class in PascalCase format and automatically uses `ResponseTrait`.
  ```bash
  php slim make:action User/LoginAction
  ```
- **Make a New Model**:
  Generates an Eloquent Model with the appropriate format.
  ```bash
  php slim make:model User
  ```
- **Run Migrations**:
  Runs all pending database migrations.
  ```bash
  php slim migrate
  ```
- **Rollback Migrations**:
  Rolls back the last batch of migrations.
  ```bash
  php slim migrate:rollback
  php slim migrate:rollback 3   # rollback 3 batches
  ```
- **Make a New Migration**:
  Generates a new migration class using Illuminate Schema Builder.
  ```bash
  php slim make:migration CreateUsersTable
  ```
- **Run Seeders**:
  Runs all database seeders.
  ```bash
  php slim seed:run
  ```
- **Create a Seeder**:
  Generates a new database seeder class.
  ```bash
  php slim seed:create UsersTableSeeder
  ```

## Formatting & Code Styling

This project is integrated with **Laravel Pint** to maintain code neatness (PSR-12/Laravel Style).

- **Check Code Style**: `composer style-check`
- **Fix Code Style Automatically**: `composer style-fix`

## Testing

All test files are placed in the `tests/` directory and tested using PHPUnit.

```bash
docker exec -it slim_app composer test
```

## Architectural Notes

### ADR Pattern (Action-Domain-Response)
This project uses the **ADR** pattern as an alternative to conventional MVC for API endpoints:
- Each class in `app/Action` acts independently with **Single Responsibility**.
- Classes are implemented as *invokables* (using the `__invoke()` magic method) so they can be routed dynamically by Slim.
- Output is standardized using `ResponseTrait` which centralizes JSON payload creation (`$this->success()` and `$this->error()` methods).

## Common Container Commands

- **View App Logs (Docker)**: `docker compose logs -f app`
- **Enter Container Shell**: `docker exec -it slim_app bash`
- **Update Composer**: `docker exec -it slim_app composer update`
- **Refresh Autoloader**: `docker exec -it slim_app composer dump-autoload`
