# AI Guide: Panduan Kerja Proyek
**Last verified:** 2026-07-07

Dokumen ini adalah panduan utama untuk AI coding agent yang bekerja di proyek ini. Baca dan pahami aturan di bawah ini untuk memastikan kolaborasi yang efisien, aman, dan akurat.

---

## 1. Aturan Kerja Utama untuk AI

### 1.1. Verifikasi Sebelum Percaya
**ATURAN BARU:** Sebelum mempercayai path/file yang disebut di `AI_FEATURE_MAP.md` atau dokumen lainnya, **cek dulu apakah file itu masih ada**. Jika path tidak ada atau berbeda, anggap dokumentasi tersebut usang. Segera cari lokasi yang benar menggunakan tool pencarian (`grep`, `glob`), selesaikan tugas, lalu laporkan ke user bahwa dokumentasi perlu diupdate dengan temuanmu. **JANGAN berasumsi tanpa verifikasi.**

### 1.2. Prioritaskan Source Code
Source code adalah satu-satunya sumber kebenaran. Dokumentasi, termasuk file ini, bisa saja usang. Jika ada keraguan, selalu rujuk ke kode yang sebenarnya.

### 1.3. Minimalkan File yang Dibuka
Jangan membaca banyak file sekaligus. Gunakan tool pencarian untuk menemukan file yang paling relevan, baca satu atau dua file kunci untuk mendapatkan konteks, lalu lanjutkan pekerjaan. Membuka terlalu banyak file akan menghabiskan waktu dan token.

### 1.4. Checklist Sebelum & Sesudah Edit
- **Sebelum Edit:**
    - [ ] Apakah saya sudah membaca kode di sekitar area yang akan diubah?
    - [ ] Apakah saya sudah memahami style, konvensi, dan arsitektur lokal?
    - [ ] Apakah saya tahu cara memverifikasi perubahan saya (misalnya, dengan test)?
- **Sesudah Edit:**
    - [ ] Apakah perubahan saya sudah mengikuti konvensi yang ada?
    - [ ] Apakah saya perlu membuat atau memperbarui test?
    - [ ] Apakah perubahan ini berpotensi merusak bagian lain dari aplikasi? (Lihat `AI_FEATURE_MAP.md` untuk analisis dampak).

### 1.5. Rebuild Custom Filament Theme
**ATURAN PENTING:** Setelah mengubah class Tailwind di file Blade/Livewire manapun yang dipakai di dalam panel admin Filament, **WAJIB** jalankan `npm run build` sebelum menganggap perubahan styling selesai — custom Filament theme (`resources/css/filament/admin/theme.css`) tidak otomatis ter-recompile hanya dari `npm run dev` untuk perubahan di file Blade.

### 1.6. Perbandingan Tanggal Eloquent di PHP
**ATURAN PENTING:** Parameter format cast pada model Eloquent (seperti `'start_date' => 'date:Y-m-d'`) **HANYA** memengaruhi hasil serialisasi data saat dikonversi ke array/JSON (termasuk props yang dikirim Inertia ke Vue). Saat properti tersebut diakses langsung di PHP (misal `$event->start_date`), tipe datanya **tetap berupa instance Carbon**, bukan string.
Oleh karena itu, perbandingan tanggal di PHP:
*   ❌ **SALAH:** `$dateStr >= $event->start_date` (membandingkan string dengan instance Carbon, menghasilkan evaluasi yang tidak valid).
*   ✅ **BENAR (Format Eksplisit):** `$dateStr >= $event->start_date->format('Y-m-d')`.
*   ✅ **BENAR (Carbon ke Carbon):** `Carbon::parse($dateStr)->greaterThanOrEqualTo($event->start_date)`.
Selalu gunakan salah satu dari cara yang benar di atas saat membandingkan tanggal di backend Laravel.

### 1.7. Clear Cache Setelah Ubah Config (.env atau app.php)
**ATURAN PENTING:** Setelah mengubah `config/app.php` (misal timezone) atau `.env`, **WAJIB** jalankan perintah `php artisan config:clear` sebelum melakukan testing. Laravel menyimpan konfigurasi lama di cache (terutama saat `php artisan serve` sedang berjalan lama), yang dapat menyebabkan bug logika waktu atau environment tidak terbaca dengan benar meskipun file sudah diedit.

---

## 2. Gambaran Umum Proyek

### 2.1. Tujuan Proyek
Aplikasi ini adalah Sistem Informasi Akademik yang dirancang untuk mengelola data sekolah, dengan dua portal utama:
1.  **Portal Admin:** Panel administrasi lengkap untuk mengelola semua data master (siswa, guru, kelas, jadwal, dll).
2.  **Portal Guru:** Dashboard untuk guru, di mana mereka dapat melihat jadwal mengajar, mencatat kehadiran siswa, dan mengelola data kelas (jika mereka adalah wali kelas).

### 2.2. Arsitektur & Teknologi
Proyek ini dibangun dengan arsitektur "dual-portal" menggunakan tumpukan teknologi berikut:
- **Backend:** Laravel 12
- **Frontend (Portal Admin):** Filament 3.x
    - Ini adalah Admin Panel Builder yang berjalan di atas stack TALL (Tailwind CSS, Alpine.js, Laravel, Livewire). Sebagian besar logika CRUD admin berada di dalam "Filament Resources".
- **Frontend (Portal Guru):** Inertia.js + Vue.js 3
    - Ini adalah arsitektur monolitik modern (MPA) di mana backend Laravel merender komponen Vue secara langsung.
- **Styling:** Tailwind CSS & DaisyUI
- **Database:** MySQL
- **Auth:** Laravel Breeze (scaffolding), Spatie Laravel Permission (roles/permissions)
- **Tugas Asinkron:** Laravel Queues (dengan driver database)
- **Testing:** Pest

### 2.3. Struktur Folder Utama
- `app/Filament/`: Berisi semua `Resources` (untuk CRUD) dan `Pages` (halaman kustom) untuk Portal Admin. **Ini adalah pusat logika admin.**
- `app/Http/Controllers/Guru/`: Berisi semua `Controller` untuk Portal Guru.
- `resources/js/Pages/Guru/`: Berisi semua komponen halaman `Vue` untuk Portal Guru.
- `app/Models/`: Berisi semua model Eloquent.
- `routes/web.php`: Mendefinisikan rute untuk Portal Guru dan halaman web lainnya. Rute Portal Admin diatur oleh Filament secara otomatis.
- `database/seeders/`: Berisi seeder untuk data awal, termasuk role dan permission.
- `database/migrations/`: Skema database.
