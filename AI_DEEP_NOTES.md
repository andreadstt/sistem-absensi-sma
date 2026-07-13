# AI Deep Notes: Catatan Teknis, Operasional, dan Utang Kode
**Last verified:** 2026-07-07

Dokumen ini berisi catatan teknis mendalam, panduan operasional, dan analisis kode. Gunakan dokumen ini jika Anda perlu memahami "mengapa" di balik keputusan arsitektur, atau jika Anda ingin menjalankan proyek secara lokal.

---

## 1. Panduan Operasional Proyek

### 1.1. Cara Menjalankan Proyek Secara Lokal

**Prasyarat:**
- PHP 8.2+
- Composer
- Node.js & NPM
- Database MySQL (buat database kosong, misal dengan nama `final`)

**Langkah-langkah Setup (dari root proyek):**

1.  **Gunakan Skrip Setup Otomatis:**
    Proyek ini menyediakan skrip `setup` yang praktis di `composer.json`. Jalankan perintah ini untuk instalasi lengkap:
    ```bash
    composer setup
    ```
    Skrip ini akan melakukan:
    - `composer install`
    - Membuat file `.env` dari `.env.example`
    - `php artisan key:generate`
    - `php artisan migrate`
    - `npm install`
    - `npm run build`

2.  **Konfigurasi Environment (`.env`):**
    Pastikan file `.env` Anda memiliki koneksi database yang benar:
    ```ini
    DB_CONNECTION=mysql
    DB_HOST=127.0.0.1
    DB_PORT=3306
    DB_DATABASE=final
    DB_USERNAME=root
    DB_PASSWORD=
    ```
    Penting: `MAIL_MAILER` di-set ke `log` secara default, jadi email tidak akan dikirim tetapi akan ditulis ke file log Laravel.

3.  **Seed Database:**
    Jalankan seeder utama untuk membuat role (`admin`, `guru`) dan data sistem awal.
    ```bash
    php artisan db:seed
    ```
    - **User Admin Default:** Seeder `RolesSeeder` akan membuat user admin. Cek file tersebut untuk mengetahui kredensial login defaultnya.
    - **Seeder Demo:** Ada seeder `FullSemesterDemoSeeder.php` yang tidak dijalankan secara default. Gunakan jika Anda butuh data dummy yang lebih lengkap: `php artisan db:seed --class=FullSemesterDemoSeeder`.

4.  **Jalankan Server Development:**
    Proyek ini juga punya skrip `dev` yang akan menjalankan semua service yang dibutuhkan secara bersamaan (server PHP, Vite, antrian, log).
    ```bash
    composer dev
    ```
    Setelah menjalankan ini, aplikasi akan tersedia di alamat yang ditampilkan oleh `artisan serve` (biasanya `http://127.0.0.1:8000`).

### 1.2. Status & Cara Menjalankan Test

- **Framework:** Pest
- **Status Test Saat Ini:**
    - Cakupan tes masih sangat minim.
    - `tests/Feature/`: Terdapat tes dasar untuk `ProfileTest` dan `Auth`.
    - `tests/Unit/`: Hanya berisi tes placeholder, termasuk `FilamentTeacherAttendanceValidationTest.php` yang masih kosong.
- **Cara Menjalankan Test:**
    Gunakan skrip `test` dari `composer.json` atau jalankan `artisan` secara langsung.
    ```bash
    composer test
    # ATAU
    php artisan test
    ```

---

## 2. Analisis Kode & Saran (Berdasarkan Verifikasi 2026-07-07)

Bagian ini berisi analisis kode, potensi masalah, dan saran perbaikan.

### 2.1. Utang Teknis (Technical Debt) & Code Smells
- **Denormalisasi pada Tabel `schedules`:** Tabel `schedules` menyimpan `teaching_assignment_id`. Ini adalah denormalisasi yang disengaja, kemungkinan untuk performa. Namun, ini menciptakan risiko inkonsistensi jika `TeachingAssignment` diubah atau dihapus tanpa mengupdate `Schedule` yang terkait.
- **Logika Bisnis di Filament Resources:** Logika persetujuan guru (membuat User, Teacher, mengirim email) berada langsung di dalam `Action` pada `TeacherRegistrationResource.php`. Untuk logika yang lebih kompleks di masa depan, ini bisa membuat file Resource menjadi gemuk (fat).
- **Kurangnya Test:** Ini adalah utang teknis terbesar. Fitur-fitur krusial seperti absensi, penjadwalan, dan persetujuan guru tidak memiliki tes otomatis, membuat refactoring di masa depan berisiko.

### 2.2. Potensi Bug
- **Inkonsistensi Data Jadwal:** Jika sebuah `TeachingAssignment` dihapus, record di `schedules` yang merujuk padanya bisa menjadi "yatim" (orphaned). Belum ditemukan adanya `Observer` atau *cascading delete* di level database yang menangani ini secara otomatis.
- **Masalah Timezone:** Penggunaan `date` dan `time` pada fitur absensi dan jadwal perlu diaudit untuk memastikan konsistensi penanganan timezone, meskipun untuk lingkup sekolah tunggal ini mungkin bukan masalah besar.

### 2.3. Saran Refactoring
- **Gunakan Service Class:** Untuk logika yang lebih dari sekadar CRUD sederhana, pertimbangkan untuk memindahkannya ke Service Class. Contoh: `TeacherRegistrationService` bisa menangani semua langkah saat menyetujui guru (membuat user, teacher, mengirim email). Ini akan membuat Filament Resource lebih bersih dan logikanya terpusat & mudah diuji.
    - **File untuk di-refactor:** `app/Filament/Resources/TeacherRegistrationResource.php` (Action `Approve`).
- **Gunakan Observer:** Manfaatkan Eloquent Observer untuk menangani *side-effect*. Contoh:
    - Buat `TeachingAssignmentObserver` untuk event `deleted`, yang akan menghapus `Schedule` terkait secara otomatis.
    - `AttendanceObserver` bisa digunakan untuk memicu rekapitulasi atau notifikasi jika seorang siswa mencapai jumlah absensi tertentu.

### 2.4. Saran Peningkatan Performa
- **Eager Loading (N+1 Problem):** Selalu gunakan `with()` saat mengambil data yang memiliki relasi, terutama di dalam loop atau di tampilan tabel Filament. Contoh: saat menampilkan nama guru di tabel jadwal, pastikan relasi `teacher` sudah di-load.
- **Indexing Database:** Verifikasi bahwa semua kolom *foreign key* (misalnya, `class_room_id`, `subject_id`, `teacher_id`, `student_id`) di tabel `attendances` dan `schedules` sudah memiliki *database index*.

### 2.5. Saran Peningkatan Maintainability
- **Tulis Unit & Feature Tests:** Ini adalah prioritas utama. Mulailah dengan menulis tes untuk alur paling kritis:
    1.  `AbsensiController@store`: Pastikan data absensi tersimpan dengan benar.
    2.  `WaliKelasController@show`: Pastikan data rekapitulasi akurat.
    3.  Aksi persetujuan di `TeacherRegistrationResource`.
- **Gunakan FormRequest:** Untuk endpoint di luar Filament (seperti di Portal Guru), gunakan class `FormRequest` khusus untuk validasi, alih-alih `request()->validate()`. Ini memisahkan tanggung jawab dan membuat controller lebih rapi.
    - **File untuk di-refactor:** `app/Http/Controllers/Guru/AbsensiController.php` (method `store`).

---

## 2.6. Isu yang Telah Diselesaikan (per 2026-07-07)
- **Konflik Jadwal (Bentrok):** Sebelumnya, tidak ada validasi untuk mencegah jadwal yang tumpang-tindih. Isu ini **telah diselesaikan** dengan menambahkan *custom validation rule* di `app/Filament/Resources/ScheduleResource.php`. Validasi ini sekarang menangani konflik guru dan konflik ruang kelas berdasarkan hari dan tumpang-tindih jam pelajaran (`time_slot`).


## Rencana Fitur Mendatang (Belum Dikerjakan)

### Kenaikan Kelas & Riwayat Siswa per Tahun Ajaran
**Status:** Ditunda, prioritas rendah untuk saat ini.
**Desain yang sudah disepakati:**
- Tabel baru `student_enrollments` (student_id, class_room_id, academic_year_id, unique per siswa+tahun ajaran)
- `students.class_room_id` tetap dipertahankan sebagai cache "kelas saat ini", disinkron otomatis
- Fitur baru "Kenaikan Kelas" (Filament Custom Page): auto-mapping Grade+1 & Jurusan tetap, bisa dikoreksi manual per siswa
- Siswa grade 12 dinonaktifkan (bukan pindah kelas), tidak perlu status "alumni" kompleks
- Kelas tujuan harus sudah dibuat manual oleh admin dulu lewat ClassRoomResource

### Kredensial Demo Terbaru
- **Administrator:** `a9296691@gmail.com` / `password`
- **Guru Demo:** `adiestoa@gmail.com` / `password`
