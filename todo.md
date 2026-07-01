# Slim API Starter - Areas for Improvement (To-Do)

Meskipun *starter* ini sudah sangat solid, masih ada beberapa fitur krusial yang umumnya dibutuhkan oleh aplikasi API modern berbasis *production*. Berikut adalah daftar rekomendasi peningkatannya:

## 1. Security & Authentication
- [x] **CORS Middleware**: Tambahkan middleware CORS standar. API hampir selalu dikonsumsi oleh client eksternal (React/Vue/Flutter), sehingga penanganan *Cross-Origin* sangat penting.
- [x] **JWT Authentication**: Integrasikan library JWT (JSON Web Token) sebagai middleware untuk memproteksi endpoint/routes tertentu.
- [x] **Rate Limiting (Throttling)**: Implementasikan pembatasan jumlah *request* (misal: 60 request/menit) per IP untuk melindungi API dari serangan *brute-force* atau *spam*.

## 2. Data Handling & Validation
- [ ] **Request Validation**: Integrasikan library validasi (seperti `rakit/validation` atau `respect/validation`). Saat ini belum ada cara standar untuk memvalidasi input *payload* JSON sebelum diproses oleh Action.
- [ ] **Resource Transformers / Pagination**: Gunakan *layer transformer* (seperti `league/fractal`) untuk melakukan serialisasi data dari Eloquent Model ke JSON yang terstruktur dan aman, serta menangani meta data *pagination*.

## 3. Error & Exception Handling
- [ ] **Custom Error Handler**: *Override* `ErrorHandler` bawaan Slim. Saat ini, error 404 (Not Found) atau 500 (Internal Server Error) masih menampilkan HTML/teks default dari Slim. Handler khusus perlu dibuat agar semua error sistem konsisten menggunakan format JSON dari `ResponseTrait` (`{"success": false, "message": "..."}`).

## 4. Testing & QA
- [ ] **Database Testing Trait**: Buat *helper trait* untuk PHPUnit yang akan melakukan migrasi dan *rollback* (atau membungkus dalam *DB Transaction*) sebelum dan sesudah menjalankan *test* agar *database state* tetap bersih.
- [ ] **CI/CD Pipeline**: Tambahkan file workflow GitHub Actions (`.github/workflows/main.yml`) untuk menjalankan `composer test` dan pengecekan kode otomatis (`composer style-check`) di setiap Pull Request.

## 5. API Documentation
- [ ] **Swagger/OpenAPI**: Integrasikan library seperti `zircote/swagger-php` agar dokumentasi API bisa di-generate secara otomatis berdasarkan *attributes/annotations* di class Action.

## 6. Architecture & Monitoring
- [ ] **Health Check Endpoint**: Tambahkan endpoint publik (misal: `/health` atau `/ping`) yang tidak diproteksi, bertugas untuk melakukan *ping* ke database dan memastikan semua servis internal berjalan dengan baik.

## 7. Additional Enhancements (Dari Review Lanjutan)
- [x] **Log Rotation**: Mengganti `StreamHandler` menjadi `RotatingFileHandler` agar file log (`storage/logs/app.log`) dibatasi dan dirotasi berdasarkan tanggal untuk mencegah pembengkakan memori. (Selesai diterapkan!)
- [ ] **Generator Command Database Seeder**: Membuat command `make:seeder` pada CLI terpadu kita untuk mempermudah pembuatan file *seeder* Phinx.
- [ ] **Pemisahan Environment (Testing vs Dev)**: Memperbarui `phpunit.xml` dan pengaturan bootstrap agar pengujian (*unit test*) menggunakan database atau environment terpisah sehingga tidak merusak data lokal.
- [ ] **Localization (i18n)**: Menambahkan dukungan multi-bahasa untuk pesan balasan (seperti error validasi) berdasarkan header `Accept-Language` yang dikirim dari *client*.
