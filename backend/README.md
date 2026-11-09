# Backend Absensi Digital PMR (Laravel 12 & Supabase PostgreSQL)

Backend REST API untuk sistem Absensi Digital Palang Merah Remaja (PMR) Wira menggunakan **Laravel 12**, **Laravel Sanctum**, dan **Supabase PostgreSQL**.

---

## 🛠️ Prasyarat
- PHP 8.2 atau lebih baru
- Composer 2.x
- Ekstensi PHP yang aktif: `pdo_pgsql`, `pgsql`, `openssl`, `mbstring`, `fileinfo`, `curl`
- Akun project di [Supabase](https://supabase.com)

---

## 🚀 Panduan Instalasi & Menjalankan di Visual Studio Code

### 1. Masuk ke direktori backend
```bash
cd backend
```

### 2. Pasang dependensi PHP melalui Composer
```bash
composer install
```

### 3. Konfigurasi Environment (`.env`)
Salin file `.env.example` ke `.env`:
```bash
cp .env.example .env
```
Generate kunci aplikasi:
```bash
php artisan key:generate
```

### 4. Hubungkan ke Supabase PostgreSQL
Buka dashboard Supabase Anda -> **Project Settings** -> **Database**.
Isi variabel berikut pada file `.env`:

```env
DB_CONNECTION=pgsql
DB_HOST=aws-0-ap-southeast-1.pooler.supabase.com # atau host langsung Anda
DB_PORT=5432 # atau 6543 untuk Supabase connection pooler
DB_DATABASE=postgres
DB_USERNAME=postgres.YOUR_PROJECT_REF
DB_PASSWORD=YOUR_SUPABASE_DB_PASSWORD
DB_SSLMODE=require

# Supabase Storage untuk Paraf / Tanda Tangan Digital
SUPABASE_URL=https://YOUR_PROJECT_REF.supabase.co
SUPABASE_SERVICE_ROLE_KEY=YOUR_SUPABASE_SERVICE_ROLE_KEY
SUPABASE_STORAGE_BUCKET=pmr-signatures
```

### 5. Buat Bucket di Supabase Storage
1. Buka **Storage** pada Dashboard Supabase.
2. Buat bucket baru dengan nama: `pmr-signatures`.
3. Set bucket sebagai **Private** (non-publik).
4. Berkas tanda tangan akan disimpan secara terorganisasi di folder `signatures/{YYYY-MM}/` dan diakses melalui **Signed URL** berbatas waktu demi privasi dan integritas data.

### 6. Jalankan Database Migration & Seeder
```bash
php artisan migrate --seed
```
*Seeder ini otomatis membuat:*
- Akun admin default:
  - **Username:** `sekretaris`
  - **Password:** `pmr123`
- Data awal anggota PMR (Kelas 10 & 11 berbagai jurusan dan divisi).

### 7. Jalankan Server Development Laravel
```bash
php artisan serve --port=8000
```
Server REST API akan aktif di `http://localhost:8000`.

---

## 📡 Dokumentasi Endpoint REST API

### 1. Autentikasi Admin
| Metode | Endpoint | Keterangan | Akses |
|---|---|---|---|
| `POST` | `/api/login` | Login admin dengan username & password, menerbitkan token Sanctum | Publik |
| `POST` | `/api/logout` | Cabut token dan akhiri sesi | Admin (`auth:sanctum`) |
| `GET` | `/api/user` | Dapatkan data admin yang sedang terotentikasi | Admin (`auth:sanctum`) |

### 2. Anggota PMR
| Metode | Endpoint | Keterangan | Akses |
|---|---|---|---|
| `GET` | `/api/anggota` | Daftar seluruh anggota dengan filter (search, angkatan, kelas, divisi) | Admin (`auth:sanctum`) |
| `GET` | `/api/anggota/search?q={nama}` | Autocomplete pencarian nama anggota (max 5-20) | Publik |
| `POST` | `/api/anggota` | Tambah anggota baru | Admin (`auth:sanctum`) |
| `GET` | `/api/anggota/{id}` | Detail anggota | Admin (`auth:sanctum`) |
| `PUT` | `/api/anggota/{id}` | Perbarui data anggota | Admin (`auth:sanctum`) |
| `DELETE` | `/api/anggota/{id}` | Hapus anggota (riwayat absensi tetap utuh) | Admin (`auth:sanctum`) |

### 3. Presensi & Tanda Tangan
| Metode | Endpoint | Keterangan | Akses |
|---|---|---|---|
| `GET` | `/api/absensi/check-today?anggota_id={id}` | Cek apakah anggota telah absen hari ini (WITA) | Publik |
| `POST` | `/api/absensi` | Simpan absensi baru (unggah paraf ke Supabase Storage) | Publik |
| `GET` | `/api/absensi` | Tampilkan seluruh catatan absensi dengan filter & pagination | Admin (`auth:sanctum`) |
| `GET` | `/api/absensi/{id}` | Detail arsip absensi | Admin (`auth:sanctum`) |
| `PUT` | `/api/absensi/{id}` | Koreksi data absensi | Admin (`auth:sanctum`) |
| `DELETE` | `/api/absensi/{id}` | Hapus arsip absensi | Admin (`auth:sanctum`) |

### 4. Rekap & Pengaturan
| Metode | Endpoint | Keterangan | Akses |
|---|---|---|---|
| `GET` | `/api/rekap/statistik` | Statistik jumlah anggota, absensi harian & bulanan, per divisi | Admin (`auth:sanctum`) |
| `GET` | `/api/rekap/export` | Unduh rekapan format CSV / Excel kompatibel | Admin (`auth:sanctum`) |
| `GET` | `/api/admin/profile` | Profil akun admin | Admin (`auth:sanctum`) |
| `PUT` | `/api/admin/profile` | Ubah username & password admin | Admin (`auth:sanctum`) |
| `POST` | `/api/admin/change-password` | Ganti password admin | Admin (`auth:sanctum`) |
| `GET` | `/api/server-time` | Waktu server resmi dalam zona Asia/Makassar (WITA, UTC+8) | Publik |
