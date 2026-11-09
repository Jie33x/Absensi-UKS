# Frontend Absensi Digital PMR (Vue.js 3, TypeScript & Vite)

Frontend aplikasi sistem Absensi Digital Palang Merah Remaja (PMR) Wira menggunakan **Vue 3**, **Composition API**, `<script setup lang="ts">`, **Vue Router**, **Pinia**, dan **Tailwind CSS**.

---

## 🛠️ Prasyarat
- Node.js 18+ atau 20+
- npm atau pnpm atau bun

---

## 🚀 Panduan Menjalankan Frontend

### 1. Masuk ke direktori frontend
```bash
cd frontend
```

### 2. Pasang dependensi
```bash
npm install
```

### 3. Konfigurasi Environment (`.env`)
Buat file `.env` jika ingin mengarahkan API ke server Laravel lokal Anda:
```env
VITE_API_BASE_URL=http://localhost:8000/api
```

### 4. Jalankan Dev Server
```bash
npm run dev
```
Aplikasi akan aktif di `http://localhost:3000`.

### 5. Build untuk Produksi
```bash
npm run build
```

---

## 🧭 Struktur Rute (Vue Router)
Semua rute didefinisikan dalam `src/router/index.ts`:

- `/` -> Otomatis diarahkan ke `/absensi`
- `/absensi` -> **Portal Absensi Digital** (Pencarian nama, validasi tanggal harian WITA, tanda tangan digital, konfirmasi presensi)
- `/admin/login` -> **Halaman Masuk Admin** (Kredensial bawaan: `sekretaris` / `pmr123`)
- `/admin/rekap` -> **Rekap Absensi** (Filter periode bulan/tahun, filter divisi/kelas/nama, ekspor Excel & PDF, preview paraf digital)
- `/admin/anggota` -> **Data Anggota** (CRUD anggota, pemilihan angkatan 10/11, kelas & jurusan, divisi)
- `/admin/pengaturan` -> **Pengaturan Admin** (Ganti kata sandi, ubah username, monitoring zona waktu Asia/Makassar)
