# 📋 CATATAN PROJECT - SISTEM ABSENSI SISWA SMAN 10

## 🎯 Ringkasan Project

**Nama Project**: Sistem Absensi Siswa Berbasis Website (SMAN 10 Kota Bogor)

**Tujuan**: Platform terpusat untuk manajemen absensi siswa dengan portal terpisah untuk admin dan guru.

**Status**: Production Ready dengan seeder data 1 semester lengkap (~720 siswa, 24 kelas)

---

## 🛠️ Tech Stack

| Komponen | Teknologi |
|----------|-----------|
| **Backend** | Laravel 12 |
| **Frontend** | Vue 3 + Inertia.js |
| **Admin Panel** | Filament v3 |
| **Styling** | Tailwind CSS + DaisyUI |
| **Database** | MySQL |
| **Export** | Laravel Excel (Maatwebsite) |
| **RBAC** | Spatie Laravel Permission |

---

## 📊 Data Model & Entity

### Core Entities (11 Total)

1. **User** → User system (admin/guru)
   - Relasi: 1:1 dengan Teacher

2. **Teacher** → Profil guru
   - Fields: user_id, nip, name, phone, default_password
   - Relasi: hasMany(TeachingAssignments), hasMany(Schedules)

3. **AcademicYear** → Tahun ajaran
   - Fields: name, start_year, end_year, start_date, end_date, is_active
   - Relasi: hasMany(ClassRooms)

4. **Program** → Program studi (IPA, IPS, Bahasa, dll)
   - Fields: code, name, short_name, description, min_grade_level, is_active

5. **ClassRoom** → Kelas
   - Fields: name, grade_level, academic_year_id, program_id, section
   - Relasi: hasMany(Students), hasMany(TeachingAssignments), hasMany(Schedules)
   - Observer: Auto-generate nama dari grade_level + program + section

6. **Student** → Siswa
   - Fields: nis, name, gender, class_room_id

7. **Subject** → Mata pelajaran
   - Fields: code, name

8. **TeachingAssignment** → Penugasan mengajar (junction table)
   - Fields: teacher_id, subject_id, class_room_id
   - **Constraint**: Max 3 subjects per teacher
   - Auto-created ketika Schedule dibuat

9. **Schedule** → Jadwal mingguan
   - Fields: class_room_id, subject_id, teacher_id, weekday (1-7), time_slot
   - Weekday: 1=Monday, 7=Sunday
   - Auto-creates TeachingAssignment on create/update

10. **Attendance** → Absensi (core business data)
    - Fields: date, class_room_id, subject_id, teacher_id, student_id, status, recorded_by
    - Status: HADIR | SAKIT | IZIN | ALFA
    - **Unique Constraint**: (date, class_room_id, subject_id, student_id)

11. **TeacherRegistration** → Pendaftaran guru (pending approval)
    - Fields: email, name, nip, phone, notes, status (pending/approved/rejected)

### Relasi Utama

```
User ──1:1──> Teacher
              ├── 1:N ──> TeachingAssignments
              └── 1:N ──> Schedules

AcademicYear ──1:N──> ClassRoom ──1:N──> Student
Program ──────1:N──> ClassRoom

Subject ◄──┐
           ├─ TeachingAssignments ◄─ Teacher (max 3 subjects)
           ├─ Schedules
           └─ Attendances

ClassRoom ◄─┬─ TeachingAssignments
            ├─ Schedules
            └─ Attendances

Attendance ──┬─> Student
             ├─> Teacher (recorder)
             ├─> User (recorded_by)
             └─> Subject
```

---

## 🔄 Business Flow

### 1️⃣ Phase Setup (Admin via Filament)

```
1. Buat AcademicYear (mark as active)
   ↓
2. Buat Programs (IPA, IPS, Bahasa)
   ↓
3. Buat ClassRooms (linked ke AcademicYear + Program)
   ↓
4. Input/Import Students ke ClassRooms
   ↓
5. Register/Create Teachers
   ↓
6. Create TeachingAssignments (teacher → subject → class)
   ↓
7. Create Schedules (timetable mingguan)
```

### 2️⃣ Phase Absensi (Guru via Portal)

```
Login di /guru/dashboard
   ↓
Dashboard menampilkan jadwal hari ini (filter by weekday)
   ↓
Klik "Take Attendance" → AbsensiController::show()
   ↓
Check if attendance sudah ada (jika ya → read-only)
   ↓
Select status setiap siswa (H/S/I/A)
   ↓
Submit → Store all records di Attendance table
   ↓
Bisa view/edit di KelasDetail
```

### 3️⃣ Phase Rekap (Admin via Filament atau Guru via Portal)

```
Admin: Lihat attendance summary di Filament
Guru: Lihat detail kelas + attendance history + export Excel
```

---

## 📍 Routes & Endpoints

### Public Routes
- `GET /` → Redirect berdasarkan role
- `GET /teacher/register` → Form registrasi guru (guest only)
- `POST /teacher/register` → Submit registrasi guru

### Admin Routes (Filament - `/admin`)
- Filament Resource-based CRUD untuk semua entities
- Protected by: `Authenticate` + `AdminMiddleware`

### Guru Routes (Inertia - `/guru`)
- `GET /guru/dashboard` → Dashboard + jadwal hari ini
- `GET /guru/kelas/{classRoom}` → Detail kelas + attendance history
- `GET /guru/kelas/{classRoom}/export` → Export attendance Excel
- `GET /guru/absensi/{classRoom}/{subject}/{date}` → Attendance form
- `POST /guru/absensi` → Store attendance records
- `POST /guru/attendance/update` → Update attendance status

Protected by: `Authenticate` + `GuruMiddleware`

---

## 👥 Fitur per Role

### Admin Panel (Filament v3)
✓ Manajemen Tahun Ajaran (create, edit, set active)  
✓ Manajemen Programs (IPA, IPS, Bahasa)  
✓ Manajemen Kelas (auto-naming dari grade_level + program)  
✓ Manajemen Siswa (create, edit, delete, bulk import Excel, bulk create 30)  
✓ Manajemen Guru (create, edit, default password manage)  
✓ Manajemen TeachingAssignments (assign guru ke subject + class, max 3 subject)  
✓ Manajemen Schedules (create timetable mingguan)  
✓ Monitoring Attendance (view summary, laporan harian/semester)  
✓ Approve Teacher Registration (dari TeacherRegistration table)  

### Guru Portal (Vue + Inertia)
✓ Login  
✓ Dashboard (jadwal hari ini, automatically filter by current weekday)  
✓ View Assigned Classes (dari TeachingAssignments)  
✓ Take Attendance (input status per siswa per subject per date)  
✓ View Attendance History (table with semua recorded sessions)  
✓ Edit Individual Attendance (update status existing record - ada ambiguity)  
✓ Export Attendance (per class to Excel)  
✓ View User Profile  

---

## 🔐 Security & Access Control

| Fitur | Guardian |
|-------|----------|
| Admin Panel | Filament AuthorizationProvider + AdminMiddleware |
| Guru Portal | GuruMiddleware (check hasRole('guru')) |
| Teacher Dashboard | Check if User.teacher exist (HasOne relation) |
| Class Authorization | Check TeachingAssignment (guru only teach assigned class) |
| Attendance Store | Check if attendance belum exist (prevent duplicate) |

---

## 📋 Business Rules & Constraints

| Rule | Enforcement | Risk |
|------|-------------|------|
| Max 3 subjects per teacher | TeachingAssignment model boot | Exception thrown if violate |
| One attendance per date/class/subject/student | Unique constraint in DB | Duplicate prevention |
| Attendance read-only after record | Frontend check `isReadOnly` | Logic baru ada updateAttendance endpoint |
| ClassRoom name auto-generated | ClassRoomObserver | Duplikat jika manual name tidak kosong |
| Active academic year filter | Filament query scope | Only show classes from active year |
| Teacher must have teacher relation | GuruMiddleware implicit | Error if teacher record missing |
| Student import unique NIS | StudentsImport validation | Skip if NIS duplicate |

---

## 📦 Fitur Pendukung

### Import & Export
- **Import Siswa**: Excel template → StudentsImport class
- **Export Absensi**: Per class to Excel (via KelasController::export)
- **Bulk Create**: Max 30 students sekaligus di admin panel

### Seeding Data
```bash
php artisan db:seed --class=RolesSeeder                    # Create admin + guru roles
php artisan db:seed --class=AcademicSystemSeeder           # Basic data structure
php artisan db:seed --class=FullSemesterDemoSeeder         # 1 semester: ~720 siswa, 24 kelas
```

### Observers & Auto-Sync
- **ClassRoomObserver**: Auto-generate class name
- **ScheduleObserver**: Auto-create TeachingAssignment

---

## 🚨 Area Kompleks & Potential Issues

### 1. Schedule ↔ TeachingAssignment Auto-Sync
**Issue**: Schedule boot method auto-creates/FirstOrCreate TeachingAssignment
- Jika logic salah bisa duplikasi
- Update schedule juga trigger FirstOrCreate

### 2. Attendance Edit vs Read-Only
**Ambiguity**: 
- Frontend set `isReadOnly = true` jika attendance ada
- Tapi ada `updateAttendance()` endpoint di backend
- User experience tidak clear apakah boleh edit atau tidak

### 3. Teacher Registration Approval
**Incomplete**: 
- TeacherRegistration table ada
- Belum jelas: bagaimana approval flow? Auto-create User + Teacher?

### 4. Multi-Subject Teacher Maximum Constraint
**Implementation**: Enforced di model, tidak ada UI validation
- Bisa cause error if user tidak aware

### 5. ClassRoom Name Collision
**Risk**: Observer hanya auto-generate jika name kosong
- Manual name + auto-name bisa crash

### 6. RBAC Inconsistency
**Risk**: Ad-hoc middleware + Filament AuthorizationProvider
- Permission logic bisa inconsistent

---

## 📊 Data Integrity

✓ Cascading deletes on foreign keys  
✓ Date casting (AcademicYear, Attendance)  
✓ Boolean casting (is_active flags)  
✓ NIS unique per student  
✓ Email unique per user + teacher_registrations  
✓ Teacher NIP unique  
✓ Attendance unique constraint  

---

## 🔧 Setup & Run

```bash
# Install dependencies
composer install
npm install

# Setup environment
cp .env.example .env
php artisan key:generate

# Database configuration in .env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=FINAL_SMAN10
DB_USERNAME=root
DB_PASSWORD=

# Migrate & seed
php artisan migrate
php artisan db:seed --class=RolesSeeder
php artisan db:seed --class=FullSemesterDemoSeeder

# Run servers
php artisan serve              # http://127.0.0.1:8000
npm run dev                    # Vite dev server

# Default Accounts (setelah seeding)
Admin: admin@gmail.com / password
Guru: andreadst@gmail.com / password
```

---

## 📝 File Structure

```
app/
├── Models/                    # 11 entities dengan relations
├── Http/
│   ├── Controllers/
│   │   ├── Guru/            # 3 guru controllers (Dashboard, Absensi, Kelas)
│   │   └── Auth/
│   ├── Middleware/          # GuruMiddleware, AdminMiddleware
│   └── Requests/
├── Filament/
│   ├── Resources/           # Resource-based CRUD (10+ resources)
│   └── Pages/
├── Observers/               # ClassRoomObserver, ProgramObserver
├── Imports/                 # StudentsImport (Excel)
└── Exports/                 # StudentsTemplateExport (Excel)

database/
├── migrations/              # 17 migration files
└── seeders/                 # 4 seeders

resources/
├── js/                      # Vue 3 components
├── css/                     # Tailwind + DaisyUI
└── views/                   # Blade templates

routes/
├── web.php                  # Main routes (guru portal + redirects)
└── auth.php                 # Auth routes (login, register, reset)

config/
├── permission.php           # Spatie permission config
├── excel.php               # Maatwebsite Excel config
└── [other configs]
```

---

## ✅ Checklist Pemahaman

- [x] Arsitektur monolithic Laravel + Inertia.js + Filament
- [x] 11 entities dengan relasi kompleks
- [x] 3 phase business flow (setup, absensi, rekap)
- [x] Admin panel vs Guru portal separation
- [x] RBAC dengan 2 roles (admin, guru)
- [x] Data integrity constraints
- [x] Auto-sync mechanisms (Schedule → TeachingAssignment)
- [x] Import/Export features
- [x] Potential issues identified
- [x] Security model understood

---

## 📌 Next Steps (Untuk Development)

1. **Klarifikasi** attendance edit flow (permanent read-only atau boleh edit?)
2. **Implement** teacher registration approval workflow
3. **Add** soft deletes untuk data histories
4. **Add** attendance archive strategy
5. **Enhance** RBAC consistency (centralize permission logic)
6. **Add** test coverage untuk business rules
7. **Document** API endpoints jika butuh REST integration

---

**Dokumen dibuat**: May 6, 2026  
**Version**: 1.0 - Initial System Understanding  
**Pembuat**: Onboarding Engineer

