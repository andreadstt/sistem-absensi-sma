# AI Feature Map
**Last verified:** 2026-07-11

Dokumen ini memetakan setiap fitur utama dalam aplikasi ke file-file relevan di source code. Gunakan ini sebagai titik awal untuk memahami di mana harus mencari dan memodifikasi kode. **Selalu verifikasi keberadaan file sebelum mengedit.**

---

## 1. Authentication & Authorization

- **Tujuan:** Mengelola login, logout, dan hak akses berbasis peran (Admin, Guru).
- **Lokasi File Kunci:**
    - **Rute & Middleware:** `routes/web.php`, `routes/auth.php`, `app/Http/Middleware/` (terutama `AdminMiddleware.php`, `GuruMiddleware.php`, `ForceChangePasswordMiddleware.php`).
    - **Controllers:** `app/Http/Controllers/Auth/` (Scaffold dari Laravel Breeze).
    - **Model:** `app/Models/User.php`.
    - **Views:** Halaman login dan register ada di `resources/js/Pages/Auth/`.
    - **Role Seeder:** `database/seeders/RolesSeeder.php` (definisi peran 'admin' dan 'guru').
- **Business Rules:**
    - Pengguna 'admin' diarahkan ke panel Filament (`/admin`).
    - Pengguna 'guru' diarahkan ke dashboard guru (`/guru/dashboard`).
    - Pengguna guru baru (dibuat dari proses approval) harus mengganti password saat login pertama, di-enforce oleh `ForceChangePasswordMiddleware`. **Catatan:** Middleware ini **hanya berlaku untuk role `guru`** — admin yang dibuat melalui fitur "Kelola Admin" (lihat Bagian 9) tidak terkena force-change-password.
- **Analisis Dampak:** Perubahan pada auth sangat berisiko. Mempengaruhi akses ke seluruh aplikasi. Edit file di `app/Http/Controllers/Auth/` dan middleware terkait dengan sangat hati-hati.
- **Task Mapping / Jika user berkata...**
    - *"Edit halaman login"*: Buka `resources/js/Pages/Auth/Login.vue` (frontend) dan `app/Http/Controllers/Auth/AuthenticatedSessionController.php` (backend).
    - *"Ubah hak akses guru"*: Cek `app/Http/Middleware/GuruMiddleware.php` dan grup rute 'guru' di `routes/web.php`.
    - *"Tambah role baru"*: Edit `database/seeders/RolesSeeder.php`, lalu buat middleware baru jika perlu.

---

## 2. Teacher Registration & Approval (Pendaftaran & Persetujuan Guru)

- **Tujuan:** Menyediakan alur lengkap mulai dari pendaftaran guru melalui form publik hingga persetujuan oleh admin, di mana guru menentukan password-nya sendiri saat mendaftar.
- **Alur Kerja:**
    1.  **Pendaftaran Publik**: Calon guru mengakses halaman publik di `/teacher/register`.
    2.  **Input Data**: Calon guru mengisi form pendaftaran, termasuk data pribadi dan password.
    3.  **Validasi & Penyimpanan**: Data divalidasi (termasuk konfirmasi password) dan disimpan di tabel `teacher_registrations` dengan status `pending`. Password disimpan dalam bentuk hash.
    4.  **Peninjauan Admin**: Admin melihat daftar pendaftar di menu "Persetujuan Guru" (`TeacherRegistrationResource`).
    5.  **Keputusan**: Admin menekan tombol "Approve" atau "Reject".
    6.  **Finalisasi**:
        -   **Approve**: Sistem membuat record `users` dan `teachers` menggunakan data dari `teacher_registrations`, termasuk password yang sudah di-hash. Email notifikasi dikirim **tanpa** password.
        -   **Reject**: Pendaftaran ditolak dan notifikasi email dikirim.
- **Lokasi File Kunci:**
    - **Form Publik (Frontend):** `resources/js/Pages/Auth/TeacherRegister.vue`.
    - **Form Publik (Backend & Validasi):** `app/Http/Requests/Auth/StoreTeacherRegistrationRequest.php` dan `app/Http/Controllers/Auth/TeacherRegistrationController.php` (method `create` dan `store`).
    - **Rute Publik:** `routes/web.php` (cari `TeacherRegistrationController`).
    - **Resource Admin (Logika Approval):** `app/Filament/Resources/TeacherRegistrationResource.php` (terutama `ApproveAction`).
    - **Model:** `app/Models/TeacherRegistration.php`, `app/Models/Teacher.php`, `app/Models/User.php`.
    - **Email Notifikasi:** `app/Mail/TeacherApproved.php`, `app/Mail/TeacherRejected.php`.
- **Business Rules:**
    - Calon guru **wajib** mengisi password saat pendaftaran.
    - Password disimpan di tabel `teacher_registrations`.
    - Proses approval di `TeacherRegistrationResource` akan gagal jika pendaftar tidak memiliki password (untuk menangani data lama).
    - Email `TeacherApproved` tidak lagi mengirimkan kredensial.
- **Task Mapping / Jika user berkata...**
    - *"Tambah field di form pendaftaran publik"*: Edit `resources/js/Pages/Auth/TeacherRegister.vue` (frontend), `app/Http/Requests/Auth/StoreTeacherRegistrationRequest.php` (validasi), dan `app/Http/Controllers/Auth/TeacherRegistrationController.php` (backend). Update juga migrasi `teacher_registrations` jika perlu.
    - *"Ubah logika saat admin menyetujui pendaftaran"*: Edit `ApproveAction` di dalam `app/Filament/Resources/TeacherRegistrationResource.php`.
    - *"Ubah email yang dikirim setelah approval"*: Edit `app/Mail/TeacherApproved.php` dan view email terkait.

---

## 3. Student Management (Manajemen Siswa)

- **Tujuan:** Admin melakukan CRUD (Create, Read, Update, Delete) untuk data siswa, termasuk impor massal dari Excel.
- **Lokasi File Kunci:**
    - **Filament Resource:** `app/Filament/Resources/StudentResource.php` (form, tabel, action CRUD).
    - **Logika Impor/Ekspor:** `app/Imports/StudentsImport.php` (untuk mengimpor) dan `app/Exports/StudentsTemplateExport.php` (untuk download template).
    - **Model:** `app/Models/Student.php`.
    - **Observer:** `app/Observers/StudentObserver.php`.
- **Business Rules:**
    - Admin dapat mengunduh template Excel kosong melalui sebuah `Action` di `StudentResource`.
    - Admin dapat mengunggah file Excel yang sudah diisi untuk membuat siswa secara massal menggunakan `ImportAction` yang memanggil `StudentsImport`.
- **Task Mapping / Jika user berkata...**
    - *"Edit form siswa"*: Buka `app/Filament/Resources/StudentResource.php`.
    - *"Ubah format import Excel"*: Buka `app/Imports/StudentsImport.php`.

---

## 4. Attendance (Absensi Siswa) - Portal Guru

- **Tujuan:** Guru mengambil absensi harian untuk kelas dan mata pelajaran yang mereka ajar.
- **Status Verifikasi:** **TERVERIFIKASI.** Alur ini sudah dicek ulang dan akurat.
- **Data Flow:**
    1.  Guru klik jadwal di `Dashboard.vue`, link mengarah ke `guru.absensi.show`.
    2.  `GET /guru/absensi/{classRoom}/{subject}/{date}` dilayani oleh `AbsensiController@show`.
    3.  Controller mengambil data siswa, mengecek apakah absensi sudah ada, lalu merender `Inertia::render('Guru/Absensi', ...)`.
    4.  Halaman `resources/js/Pages/Guru/Absensi.vue` ditampilkan.
    5.  Guru mengisi form dan klik "Simpan Absensi".
    6.  `POST /guru/absensi` (route `guru.absensi.store`) dikirim ke `AbsensiController@store`.
    7.  Controller memvalidasi dan menyimpan data ke tabel `attendances`.
- **Lokasi File Kunci:**
    - **Controller:** `app/Http/Controllers/Guru/AbsensiController.php`.
    - **Vue Page:** `resources/js/Pages/Guru/Absensi.vue`.
    - **Rute:** `routes/web.php` (cari `AbsensiController`).
    - **Model:** `app/Models/Attendance.php`.
- **Task Mapping / Jika user berkata...**
    - *"Edit tampilan form absensi"*: Buka `resources/js/Pages/Guru/Absensi.vue`.
    - *"Tambah validasi saat simpan absensi"*: Buka method `store()` di `app/Http/Controllers/Guru/AbsensiController.php`.

---

## 5. Wali Kelas (Homeroom Teacher) - Portal Guru

- **Tujuan:** Memberikan hak akses khusus kepada guru yang ditunjuk sebagai Wali Kelas untuk melihat rekap absensi dan data detail kelasnya.
- **Status Verifikasi:** **SUDAH DIIMPLEMENTASI.** Fitur ini lebih dari sekadar skema DB; ada fungsionalitas penuh di belakangnya.
- **Business Rules:**
    - Hak akses tidak menggunakan role `wali kelas`, melainkan pengecekan langsung: `if ($classRoom->head_teacher_id === $auth_teacher->id)`.
    - Middleware `HeadTeacherMiddleware` melindungi rute detail wali kelas.
    - Wali kelas mendapatkan menu "Wali Kelas" di sidebar-nya.
    - **Unik Wali Kelas:** Setiap guru hanya dapat menjadi wali kelas untuk **satu** kelas. Aturan ini diterapkan melalui *unique constraint* pada kolom `head_teacher_id` di tabel `class_rooms`. Kolom ini juga bersifat `nullable`, artinya guru yang tidak ditunjuk sebagai wali kelas tidak akan melanggar aturan ini.
- **Lokasi File Kunci:**
    - **Controller:** `app/Http/Controllers/Guru/WaliKelasController.php`.
    - **Middleware:** `app/Http/Middleware/HeadTeacherMiddleware.php`.
    - **Vue Pages:** `resources/js/Pages/Guru/WaliKelas/Index.vue` (daftar kelas yang diampu) dan `Show.vue` (dashboard detail kelas).
    - **Model Relationship:** Method `classRoomsAsHeadTeacher()` di `app/Models/Teacher.php`.
    - **Rute:** `routes/web.php` (cari `WaliKelasController`).
- **Fitur Utama:**
    - Melihat daftar siswa di kelasnya.
    - Melihat rekapitulasi absensi detail per siswa (total sakit, izin, alfa) untuk semua mata pelajaran.
    - Mengekspor rekap absensi kelas ke Excel menggunakan `AttendanceSummaryExport`.
- **Task Mapping / Jika user berkata...**
    - *"Edit halaman detail yang dilihat wali kelas"*: Buka `app/Http/Controllers/Guru/WaliKelasController.php` (untuk data) dan `resources/js/Pages/Guru/WaliKelas/Show.vue` (untuk tampilan).
    - *"Ubah cara wali kelas di-assign"*: Buka `app/Filament/Resources/ClassRoomResource.php`, cari field `head_teacher_id`.

---

## 6. Data Master & Penjadwalan - Portal Admin

- **Tujuan:** Admin mengelola data-data inti akademik seperti Kelas, Mata Pelajaran, Tahun Ajaran, Program, dan membuat Jadwal.
- **Lokasi File Kunci (Semua di dalam `app/Filament/Resources/`):**
    - `ClassRoomResource.php`: Manajemen Kelas. **Catatan:** Halaman daftar (index) standar telah diganti dengan halaman navigasi *drill-down* kustom untuk mempermudah penelusuran berdasarkan Tingkat dan Jurusan. Form CRUD (Create/Edit) tetap tidak berubah.
    - `ClassRoomResource/Pages/BrowseClassRooms.php`: Logika untuk halaman custom navigasi kelas.
    - `views/filament/resources/class-room-resource/pages/browse-class-rooms.blade.php`: Tampilan untuk halaman custom navigasi kelas.
    - `SubjectResource.php`: Manajemen Mata Pelajaran.
    - `AcademicYearResource.php`: Manajemen Tahun Ajaran.
    - `ProgramResource.php`: Manajemen Program/Jurusan.
    - `TeachingAssignmentResource.php`: Menghubungkan Guru, Kelas, dan Mata Pelajaran. Ini adalah "jembatan" sebelum jadwal dibuat.
    - `ScheduleResource.php`: Membuat jadwal pelajaran mingguan berdasarkan `TeachingAssignment`.
- **Business Rules:**
    - Alur kerja: Admin membuat `Schedule` secara langsung. Sistem secara otomatis membuat `TeachingAssignment` (penugasan) di belakang layar jika belum ada.
    - **Validasi Anti-Bentrok:** Sistem secara otomatis melakukan validasi untuk mencegah konflik saat menyimpan jadwal:
        - **Konflik Guru:** Jadwal tidak akan tersimpan jika guru yang sama sudah memiliki jadwal lain di hari dan rentang waktu yang tumpang-tindih.
        - **Konflik Ruang Kelas:** Jadwal tidak akan tersimpan jika ruang kelas yang sama sudah digunakan oleh jadwal lain di hari dan rentang waktu yang tumpang-tindih.
- **Analisis Dampak:** Fitur-fitur ini sangat saling terkait. Mengubah `TeachingAssignment` akan berdampak pada `Schedule` dan `Attendance`.
- **Task Mapping / Jika user berkata...**
    - *"Edit cara jadwal dibuat"*: Buka `app/Filament/Resources/ScheduleResource.php`.
    - *"Ganti wali kelas untuk 7A"*: Telusuri kelas dari menu `Kelas`, pilih Tingkat dan Jurusan yang sesuai, lalu klik kelas yang dituju untuk masuk ke halaman edit.
    - *"Ubah tampilan penelusuran kelas"*: Buka `app/Filament/Resources/ClassRoomResource/Pages/BrowseClassRooms.php` (logika) dan `resources/views/filament/resources/class-room-resource/pages/browse-class-rooms.blade.php` (tampilan).

---

## 7. Teacher Management (Manajemen Guru)

- **Tujuan:** Admin melakukan CRUD (Create, Read, Update, Delete) untuk data master guru.
- **Lokasi File Kunci:**
    - **Filament Resource:** `app/Filament/Resources/TeacherResource.php` (Logika utama untuk form, tabel, dan actions).
    - **Model:** `app/Models/Teacher.php`.
    - **Relasi Model:** `app/Models/User.php` (setiap guru terhubung ke satu user).
- **Business Rules:**
    - Fitur ini mengelola record `Teacher` yang sudah ada (dibuat dari modul "Persetujuan Guru").
    - Setiap `Teacher` harus terhubung ke sebuah `User`. Form-nya menggunakan `Select` yang me-load relasi untuk memilih user.
    - Tabel menampilkan informasi gabungan dari tabel `teachers` dan `users` (seperti NIP, nama, dan email).
- **Task Mapping / Jika user berkata...**
    - *"Edit form data guru"*: Buka method `form()` di `app/Filament/Resources/TeacherResource.php`.
    - *"Tambah kolom di tabel guru"*: Buka method `table()` di `app/Filament/Resources/TeacherResource.php`.
    - *"Tambah validasi NIP unik"*: Aturan `->unique()` sudah ada di field NIP pada method `form()`.

---

## 8. Dashboards (Admin & Guru)

- **Tujuan:** Menyediakan halaman utama yang relevan untuk Admin dan Guru setelah mereka login.
- **Lokasi File Kunci (Admin):**
    - **Konfigurasi:** `app/Providers/Filament/AdminPanelProvider.php` (mengatur halaman, widget, dan navigasi dashboard default).
    - **Widgets:** Folder `app/Filament/Widgets/` saat ini tidak ada, menandakan dashboard admin menggunakan widget default Filament atau widget yang didefinisikan langsung di provider.
- **Lokasi File Kunci (Guru):**
    - **Controller:** `app/Http/Controllers/Guru/DashboardController.php` (menyiapkan semua data yang dibutuhkan dashboard guru, seperti jadwal, rekap, dll.).
    - **Vue Page:** `resources/js/Pages/Guru/Dashboard.vue` (menampilkan data yang diterima dari controller).
    - **Rute:** `routes/web.php` (cari rute `guru.dashboard`).
- **Business Rules:**
    - **Admin:** Dashboard menampilkan ringkasan data dari berbagai `Resource`.
    - **Guru:** Dashboard adalah pusat navigasi utama. Ini menampilkan jadwal mengajar hari ini, ringkasan statistik, dan link ke fitur lain seperti absensi dan halaman wali kelas.
- **Task Mapping / Jika user berkata...**
    - *"Edit dashboard admin"*: Buka `app/Providers/Filament/AdminPanelProvider.php` untuk mengubah widget. Jika ingin membuat widget baru, gunakan `php artisan make:filament-widget`.
    - *"Edit dashboard guru"*: Buka `app/Http/Controllers/Guru/DashboardController.php` untuk mengubah data, dan `resources/js/Pages/Guru/Dashboard.vue` untuk mengubah tampilan.

---

## 9. Kelola Admin (Admin Management) - Portal Admin

- **Tujuan:** Admin mengelola akun user dengan role `admin` — membuat admin baru, mengedit data admin, dan menghapus admin (dengan safeguard).
- **Lokasi File Kunci:**
    - **Filament Resource:** `app/Filament/Resources/AdminResource.php` (form, tabel, query scope, delete safeguard).
    - **Pages:** `app/Filament/Resources/AdminResource/Pages/` (`ListAdmins.php`, `CreateAdmin.php`, `EditAdmin.php`).
    - **Model:** `app/Models/User.php` (di-scope ke `User::role('admin')` via Spatie).
- **Business Rules:**
    - Resource ini hanya menampilkan user yang memiliki role `admin` (override `getEloquentQuery()` dengan `->role('admin')`).
    - **Form Create:** Nama, Email, Password (wajib diisi, minimal 8 karakter). Admin baru langsung bisa login tanpa force-change-password (`must_change_password = false`).
    - **Form Edit:** Nama, Email, Password (opsional — kosongkan jika tidak ingin mengubah). Field password menggunakan `autocomplete='new-password'` untuk mencegah browser autofill.
    - **Safeguard Admin Terakhir:** Sistem **menolak penghapusan** jika admin yang akan dihapus adalah satu-satunya admin tersisa. Validasi ini diterapkan di tiga tempat: delete action di tabel, bulk delete action, dan delete action di halaman edit. Pesan error: *"Tidak dapat menghapus admin terakhir di sistem."*
    - Role `admin` di-assign otomatis via hook `afterCreate()` di `CreateAdmin.php`.
- **Navigasi:** Menu "Kelola Admin" berada di group "Manajemen User" (sejajar dengan "Pendaftaran Guru"), `$navigationSort = 2`.
- **Otorisasi:** Akses dikontrol oleh `AdminMiddleware` di level panel (semua resource di panel admin hanya bisa diakses oleh user dengan role `admin`). Tidak ada Policy khusus.
- **Task Mapping / Jika user berkata...**
    - *"Edit form data admin"*: Buka method `form()` di `app/Filament/Resources/AdminResource.php`.
    - *"Ubah aturan penghapusan admin"*: Cari `before()` hook di `DeleteAction` pada `AdminResource.php` (tabel) dan `EditAdmin.php` (halaman edit).
    - *"Tambah admin baru"*: Navigasi ke sidebar "Manajemen User" → "Kelola Admin" → tombol "Create".

---

## 10. Edit Profile Admin (Ganti Password) - Portal Admin

- **Tujuan:** Admin yang sedang login dapat mengganti nama, email, dan password miliknya sendiri melalui halaman profile bawaan Filament, dengan validasi password saat ini.
- **Lokasi File Kunci:**
    - **Custom Profile Page:** `app/Filament/Pages/Auth/EditProfile.php` (extends `Filament\Pages\Auth\EditProfile`).
    - **Registrasi di Panel:** `app/Providers/Filament/AdminPanelProvider.php` (baris `->profile(EditProfile::class)`).
- **Business Rules:**
    - Halaman profile diakses melalui menu user di pojok kanan atas panel admin.
    - Form berisi: Nama, Email, Password Saat Ini, Password Baru, Konfirmasi Password Baru.
    - **Validasi Current Password:** Field "Password Saat Ini" (`currentPassword`) **hanya muncul** saat admin mulai mengisi field "Password Baru" (menggunakan `->visible(fn (Get $get) => filled($get('password')))`). Validasi menggunakan `->currentPassword()` bawaan Filament/Laravel yang otomatis memverifikasi terhadap hash di database.
    - **Tidak ada force-change-password untuk admin.** Ini keputusan desain final — berbeda dengan guru yang menggunakan `ForceChangePasswordMiddleware`.
    - Fitur ini **hanya berlaku di panel admin** (`/admin`). Tidak memengaruhi alur login, profil, atau fungsionalitas apapun di Portal Guru.
- **Task Mapping / Jika user berkata...**
    - *"Ubah field di halaman profile admin"*: Override method yang sesuai (`getNameFormComponent()`, `getEmailFormComponent()`, dll.) di `app/Filament/Pages/Auth/EditProfile.php`.
    - *"Nonaktifkan halaman profile admin"*: Hapus `->profile(EditProfile::class)` dari `app/Providers/Filament/AdminPanelProvider.php`.
    - *"Tambah field baru di profile admin"*: Edit method `getForms()` di `app/Filament/Pages/Auth/EditProfile.php`, tambahkan komponen form baru ke array schema.
