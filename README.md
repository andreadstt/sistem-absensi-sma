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
# Copy file .env.example ke .env
cp .env.example .env

# Generate application key
php artisan key:generate
```

### 4: Konfigurasi db
edit file `.env`:
```env
DB_DATABASE=FINAL_SMAN10

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

npm run dev
```

```bash

php artisan serve
```
