# Slim 4 API Starter

Starter project sederhana menggunakan Slim Framework 4 dengan PHP 8.2, PHP-DI, dan Docker.

## Struktur Folder

```text
.
├── app/                # Source code aplikasi
│   └── Action/         # API Actions (ADR Pattern)
├── config/             # Konfigurasi (Routes, Container, Settings)
├── public/             # Document root (Entry point index.php)
├── Dockerfile          # Konfigurasi PHP 8.2-FPM
├── docker-compose.yml  # Orchestration App & Web Server (Nginx)
└── nginx.conf          # Konfigurasi Nginx
```

## Teknologi yang Digunakan

- **PHP 8.2**
- **Slim Framework 4**
- **PHP-DI 7** (Dependency Injection)
- **Slim PSR-7** (Request/Response implementation)
- **Docker & Docker Compose**
- **Nginx** (Web Server)

## Cara Menjalankan

1. **Clone/Download** project ini.
2. Jalankan Docker Compose:
   ```bash
   docker compose up -d --build
   ```
3. Akses API di:
   - URL: `http://localhost:8080`
   - Output: `{"message":"Hello World!"}`

## Catatan Arsitektur

### Kenapa Folder `Action`?
Project ini menggunakan pola **Action-Domain-Response (ADR)** sebagai alternatif dari MVC tradisional. 
- Setiap class di dalam `app/Action` hanya memiliki satu tanggung jawab (**Single Responsibility**).
- Menggunakan method `__invoke()` sehingga class dapat langsung dipanggil sebagai *callable* oleh Slim.
- Memudahkan testing dan pengelolaan dependensi yang lebih spesifik per-endpoint.

### Autoloading
Namespace utama adalah `App` yang dipetakan ke folder `app/` menggunakan PSR-4 di `composer.json`.

## Perintah Umum

- **Melihat Log**: `docker compose logs -f`
- **Masuk ke Container**: `docker compose exec app bash`
- **Update Dependencies**: `docker compose exec app composer update`
- **Refresh Autoloader**: `docker compose exec app composer dump-autoload`
