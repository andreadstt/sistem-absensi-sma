#  Sistem Absensi SMA - Portal Guru & Admin


## How To run


### Langkah 1: Clone Repo
```bash
git clone https://github.com/username/sistem-absensi-sma.git
cd sistem-absensi-sma
```

### 2: Install package
```bash
composer install
npm install
```

### 3: Setup env
```bash
# Copy file .env.example menjadi .env
cp .env.example .env

# Generate application key
php artisan key:generate
```

### 4: Konfigurasi db
edit file `.env`:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=FINAL_SMAN10
DB_USERNAME=root
DB_PASSWORD=
```

### 5: Migrate db & Seeding
```bash

php artisan migrate

# Seed roles & permissions
php artisan db:seed --class=RolesSeeder

# Seed data demo lengkap 1 semester
php artisan db:seed --class=FullSemesterDemoSeeder
```


```bash

# Atau jalankan development server (auto-reload)
npm run dev
```

```bash

php artisan serve
```

Aplikasi akan berjalan di: **http://127.0.0.1:8000**

## 👤 Akun Default (Setelah Seeding)

### Admin
- **URL:** http://127.0.0.1:8000/admin
- **Email:** admin@gmail.com
- **Password:** password

### Guru (Demo)
- **URL:** http://127.0.0.1:8000/guru/dashboard
- **Email:** andreadst@gmail.com
- **Password:** password

## 📖 Panduan Penggunaan

### Untuk Administrator

1. **Login ke Admin Panel**: Akses `/admin` dan login dengan akun admin
2. **Kelola Tahun Ajaran**: Buat tahun ajaran baru di menu "Tahun Ajaran"
3. **Kelola Kelas & Siswa**: 
   - Buat kelas di menu "Kelas"
   - Tambah siswa via "Import Excel" atau "Bulk Create"
4. **Kelola Guru**: Daftarkan guru di menu "Guru"
5. **Atur Jadwal**: 
   - Assign guru ke mata pelajaran & kelas di "Jadwal & Penugasan"
   - Buat jadwal mingguan di menu "Jadwal"

### Untuk Guru

1. **Login**: Akses `/guru/dashboard` atau klik "Login" di homepage
2. **Dashboard**: Dashboard menampilkan jadwal hari ini
   - Klik "Masuk" pada jadwal yang tersedia untuk masing2 guru untuk melakukan input absensi
3. **Kehadiran Saya**: Menampilkan presensi kehadiran guru menggunakan calendar view.
   - KLik arrow (kanan atau kiri) untuk mengganti view berdasarkan bulan.
4. **Ruang Wali Kelas**: Menampilkan detail halaman berisi kelas yang dipandu.
5. **Rekap Absensi**: Klik badge status di tabel untuk mengubah status absensi siswa



## 🔧 Troubleshooting

### Error: "Class not found"
```bash
composer dump-autoload
```

### Error saat npm run build
```bash
rm -rf node_modules package-lock.json
npm install
npm run build
```

### Database migration error
```bash
php artisan migrate:fresh
php artisan db:seed --class=RolesSeeder
```

### Port 8000 sudah digunakan
```bash
php artisan serve --port=8080
```
