# Absensi Digital PMR (Palang Merah Remaja)

Sistem Absensi Digital PMR Wira modern dengan arsitektur terpisah:
- **Frontend:** Vue.js 3, TypeScript, Vite, Pinia, Vue Router, Tailwind CSS
- **Backend:** Laravel 12, PHP 8.2+, Laravel Sanctum, Supabase PostgreSQL, Supabase Storage
- **Zona Waktu:** Asia/Makassar (WITA, UTC+8)

---

## 📁 Struktur Direktori Proyek

```
.
├── frontend/                     # Aplikasi Frontend Vue.js 3
│   ├── public/                   # Asset statis
│   ├── src/
│   │   ├── assets/               # Gambar botani & artwork
│   │   ├── components/           # Reusable Vue components (Navbar, SignaturePad, dll)
│   │   ├── composables/          # Composables (useTimezone, formatIndoDate)
│   │   ├── layouts/              # AdminLayout & drawer responsif
│   │   ├── router/index.ts       # Vue Router (Absensi, Login, Rekap, Anggota, Pengaturan)
│   │   ├── services/api.ts       # REST API service client dengan Sanctum auth
│   │   ├── stores/               # Pinia stores (auth, attendance, member)
│   │   ├── types/index.ts        # TypeScript interfaces & types
│   │   ├── views/                # Views (PortalAbsensi, AdminLogin, Rekap, Anggota, Pengaturan)
│   │   ├── App.vue               # Root component
│   │   └── main.ts               # Entry point
│   ├── package.json
│   ├── vite.config.ts
│   └── tsconfig.json
│
├── backend/                      # Backend REST API Laravel 12
│   ├── app/
│   │   ├── Http/
│   │   │   ├── Controllers/Api/  # AuthController, AnggotaController, AbsensiController, RekapController, AdminProfileController
│   │   │   ├── Requests/         # Form Request Validation (Login, Store, Update)
│   │   │   └── Resources/        # API Resources (AnggotaResource, AbsensiResource, AdminProfileResource)
│   │   ├── Models/               # User, Anggota, Absensi, AdminProfile
│   │   └── Services/             # SupabaseStorageService, TimezoneService
│   ├── bootstrap/app.php         # Laravel 12 app configuration & middleware
│   ├── config/                   # database (Supabase pgsql), cors, sanctum, filesystems
│   ├── database/
│   │   ├── migrations/           # anggota, absensi (unique harian), admin_profiles, users, tokens
│   │   └── seeders/              # DatabaseSeeder, AdminSeeder (sekretaris/pmr123), AnggotaSeeder
│   ├── routes/api.php            # REST API endpoints
│   ├── .env.example              # Konfigurasi Supabase PostgreSQL & Storage
│   ├── composer.json             # PHP 8.2+ & Laravel 12 dependencies
│   └── artisan
│
└── README.md
```

---

## ⚡ Panduan Menjalankan

### Menjalankan Frontend (Vue.js 3):
```bash
cd frontend
npm install
npm run dev
```
Buka browser di `http://localhost:3000`.

### Menjalankan Backend (Laravel 12):
```bash
cd backend
composer install
cp .env.example .env
php artisan key:generate
# Masukkan kredensial Supabase PostgreSQL & Storage Anda di file .env
php artisan migrate --seed
php artisan serve --port=8000
```
API akan aktif di `http://localhost:8000`.

---

## 🔐 Kredensial Default Admin
- **Username:** `sekretaris`
- **Password:** `pmr123`
*(Dapat diubah melalui menu Pengaturan Admin)*
