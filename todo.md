# Slim API Starter - Areas for Improvement (To-Do)

Meskipun *starter* ini sudah sangat solid, masih ada beberapa fitur krusial yang umumnya dibutuhkan oleh aplikasi API modern berbasis *production*. Berikut adalah daftar rekomendasi peningkatannya:

## 1. Security & Authentication
- [x] **CORS Middleware**: Tambahkan middleware CORS standar. API hampir selalu dikonsumsi oleh client eksternal (React/Vue/Flutter), sehingga penanganan *Cross-Origin* sangat penting.
- [x] **JWT Authentication**: Integrasikan library JWT (JSON Web Token) sebagai middleware untuk memproteksi endpoint/routes tertentu.
- [x] **Rate Limiting (Throttling)**: Implementasikan pembatasan jumlah *request* (misal: 60 request/menit) per IP untuk melindungi API dari serangan *brute-force* atau *spam*.

## 2. Data Handling & Validation
- [x] **Request Validation**: Integrasikan library validasi (`ttpryg/validation`). Menggunakan `Ttpryg\Validation\Validator` untuk memvalidasi input *payload* JSON maupun objek DTO sebelum diproses oleh Action.
- [x] **Resource Transformers / Pagination**: Gunakan *layer transformer* (seperti `league/fractal`) untuk melakukan serialisasi data dari Eloquent Model ke JSON yang terstruktur dan aman, serta menangani meta data *pagination*.

## 3. Error & Exception Handling
- [x] **Custom Error Handler**: *Override* `ErrorHandler` bawaan Slim. Saat ini, error 404 (Not Found) atau 500 (Internal Server Error) masih menampilkan HTML/teks default dari Slim. Handler khusus perlu dibuat agar semua error sistem konsisten menggunakan format JSON dari `ResponseTrait` (`{"success": false, "message": "..."}`).

## 4. Testing & QA
- [x] **Database Testing Trait**: Buat *helper trait* untuk PHPUnit yang akan melakukan migrasi dan *rollback* (atau membungkus dalam *DB Transaction*) sebelum dan sesudah menjalankan *test* agar *database state* tetap bersih.
- [x] **Pemisahan Environment (Testing vs Dev)**: Memperbarui `phpunit.xml` dan pengaturan bootstrap agar pengujian (*unit test*) menggunakan database atau environment terpisah (misal SQLite in-memory / `.env.testing`) sehingga tidak merusak data lokal.
- [x] **Static Analysis (PHPStan)**: Integrasikan `phpstan/phpstan` (level 6-8) ke dalam pipeline dev/test untuk mendeteksi type mismatch, dead code, dan null-safety sebelum runtime.
- [x] **CI/CD Pipeline**: Tambahkan file workflow GitHub Actions (`.github/workflows/main.yml`) untuk menjalankan syntax check, code style (`pint`), static analysis (`phpstan`), dan unit test (`phpunit`) secara otomatis di setiap Pull Request.

## 5. API Documentation
- [ ] **Swagger/OpenAPI**: Integrasikan library seperti `zircote/swagger-php` agar dokumentasi API bisa di-generate secara otomatis berdasarkan *attributes/annotations* di class Action dan sediakan viewer UI (Swagger UI / Scalar) di route `/docs`.

## 6. Architecture & Monitoring
- [x] **Health Check Endpoint**: Tambahkan endpoint publik (misal: `/health` atau `/ping`) yang tidak diproteksi, bertugas untuk melakukan *ping* ke database dan memastikan semua servis internal berjalan dengan baik.
- [x] **Request ID / Correlation ID Middleware**: Buat middleware untuk membaca/menghasilkan header `X-Request-Id` (UUID) yang diteruskan ke response header dan disisipkan ke context logger Monolog guna mempermudah *request tracing* di production.
- [x] **Form Request / Validation Middleware Pattern**: Sediakan abstraction Form Request / Validation Middleware terintegrasi dengan `ttpryg/validation` agar aksi/controller tidak perlu memanggil proses validasi secara prosedural di body controller.

## 7. Additional Enhancements (Dari Review Lanjutan)
- [x] **Log Rotation**: Mengganti `StreamHandler` menjadi `RotatingFileHandler` agar file log (`storage/logs/app.log`) dibatasi dan dirotasi berdasarkan tanggal untuk mencegah pembengkakan memori. (Selesai diterapkan!)
- [x] **Generator Command Database Seeder**: Membuat command `make:seeder` (alias `seed:create`) pada CLI terpadu kita untuk mempermudah pembuatan file *seeder*.
- [x] **CLI Generators Lengkap**: Tambahkan command `make:middleware`, `make:transformer`, `migrate:reset`, dan `migrate:refresh` pada CLI console (`./slim`).
- [x] **Localization (i18n)**: Menambahkan dukungan multi-bahasa untuk pesan balasan (seperti error validasi / messages) berdasarkan header `Accept-Language` yang dikirim dari *client*.

## 8. Enterprise & Scaling Roadmap
- [ ] **File Storage Abstraction (Flysystem)**: Integrasikan `league/flysystem` dengan multi-driver (`local` dan `s3`) untuk manajemen upload file dan routing file statis (`/storage/*`).
- [ ] **Asynchronous Jobs & Queue Worker**: Integrasikan antrean background jobs (Redis/Database) dan worker command CLI `php slim queue:work` untuk tugas asynchronous seperti email dan webhook.
- [ ] **Caching Layer (PSR-16)**: Sediakan abstraction cache (Redis / File) berbasis `SimpleCacheInterface` dengan cache helper method.
- [ ] **Model Factory Pattern**: Tambahkan model factory generator (`php slim make:factory`) dan integrasi FakerPHP untuk pembuatan dummy data pengujian dan seeding yang ekspresif.
