# Slim 4 API Starter

Starter project menggunakan Slim Framework 4 dengan PHP 8.2, PHP-DI, Eloquent ORM, dan Docker.

## Struktur Folder

```text
.
├── app/                # Source code aplikasi utama
│   ├── Action/         # API Actions (ADR Pattern)
│   ├── Commands/       # CLI Commands (Symfony Console)
│   ├── Model/          # Eloquent Models
│   └── Traits/         # Reusable Traits (contoh: ResponseTrait)
├── config/             # Konfigurasi (Routes, Container, Settings, DB)
├── db/                 # Database Migrations & Seeds (Phinx)
├── public/             # Document root (Entry point index.php)
├── tests/              # Pengujian otomatis (PHPUnit)
├── slim                # Executable CLI tool (Symfony Console)
├── Dockerfile          # Konfigurasi image PHP 8.2-FPM
├── docker-compose.yml  # Orchestration App & Web Server (Nginx)
└── nginx.conf          # Konfigurasi Nginx
```

## Fitur & Teknologi Utama

- **PHP 8.2**
- **Slim Framework 4** & **Slim PSR-7**
- **PHP-DI 7** (Dependency Injection)
- **Eloquent ORM** (Database Management)
- **Phinx** (Database Migrations)
- **Symfony Console** (Custom CLI Generator)
- **Laravel Pint** (Code Styling & Formatting)
- **PHPUnit** (Testing)
- **Docker & Nginx**

## Cara Menjalankan

1. **Clone/Download** project ini.
2. Jalankan container dengan Docker Compose:
   ```bash
   docker compose up -d --build
   ```
3. Install dependencies menggunakan composer (di dalam container):
   ```bash
   docker exec -it slim-api-starter-app-1 composer install
   ```
4. Akses API di URL: `http://localhost:8080`

## CLI Tool (Slim API Starter)

Project ini memiliki built-in CLI (`./slim`) untuk membantu mempercepat proses development.
Semua command CLI bisa dieksekusi di dalam container:

```bash
docker exec -it slim-api-starter-app-1 php slim list
```

### Generator Commands
- **Membuat Action Baru**: 
  Akan men-generate class Action dengan format PascalCase dan otomatis menggunakan `ResponseTrait`.
  ```bash
  php slim make:action User/LoginAction
  ```
- **Membuat Model Baru**:
  Akan men-generate Eloquent Model dengan format yang sesuai.
  ```bash
  php slim make:model User
  ```

## Formatting & Code Styling

Project ini terintegrasi dengan **Laravel Pint** untuk menjaga kerapian kode (PSR-12/Laravel Style).

- **Mengecek Style Kode**: `composer style-check`
- **Memperbaiki Style Kode Secara Otomatis**: `composer style-fix`

## Testing

Semua file pengujian (tests) ditempatkan di direktori `tests/` dan dites menggunakan PHPUnit.

```bash
docker exec -it slim-api-starter-app-1 composer test
```

## Catatan Arsitektur

### Pola ADR (Action-Domain-Response)
Project ini menggunakan pola **ADR** sebagai alternatif dari MVC konvensional untuk endpoint API:
- Setiap class di dalam `app/Action` bertindak secara independen dengan **Single Responsibility**.
- Class diimplementasikan sebagai *invokable* (menggunakan magic method `__invoke()`) sehingga dapat di-routing secara dinamis oleh Slim.
- Output distandarisasi menggunakan `ResponseTrait` yang memusatkan logika pembentukan JSON (metode `$this->success()` dan `$this->error()`).

## Perintah Umum Container

- **Melihat Log App**: `docker compose logs -f app`
- **Masuk ke Container Shell**: `docker exec -it slim-api-starter-app-1 bash`
- **Update Composer**: `docker exec -it slim-api-starter-app-1 composer update`
- **Refresh Autoloader**: `docker exec -it slim-api-starter-app-1 composer dump-autoload`
