export type Angkatan = 'X' | 'XI';

export const DAFTAR_ANGKATAN: { value: Angkatan; label: string }[] = [
  { value: 'X', label: 'Kelas 10 (X)' },
  { value: 'XI', label: 'Kelas 11 (XI)' },
];

export const DAFTAR_KELAS_JURUSAN = [
  'PPLG 1',
  'PPLG 2',
  'DKV 1',
  'DKV 2',
  'ANIMASI',
  'TJKT 1',
  'TJKT 2',
  'TJKT 3',
] as const;

export type KelasJurusan = (typeof DAFTAR_KELAS_JURUSAN)[number];

export const DAFTAR_DIVISI = [
  'PRIMA',
  'LITBANG',
  'PDD',
  'KEBERSIHAN',
] as const;

export type Divisi = (typeof DAFTAR_DIVISI)[number];

export interface Anggota {
  id: number;
  nama: string;
  angkatan: Angkatan;
  kelas_jurusan: string;
  divisi: Divisi;
  created_at: string;
  updated_at?: string;
}

export interface Absensi {
  id: number;
  anggota_id: number;
  nama_snapshot: string;
  angkatan_snapshot: Angkatan;
  kelas_jurusan_snapshot: string;
  divisi_snapshot: Divisi;
  tanggal: string; // YYYY-MM-DD (Asia/Makassar)
  waktu: string;   // HH:mm:ss (Asia/Makassar)
  tanda_tangan: string; // Storage path or signed URL or data URL
  created_at: string;
  updated_at?: string;
}

export interface AdminUser {
  id: number;
  username: string;
  created_at: string;
}

export interface AdminProfile {
  id: number;
  user_id?: number;
  username: string;
  created_at?: string;
  updated_at?: string;
}

export interface AbsensiFilter {
  bulan?: string; // 1-12
  tahun?: string; // e.g. 2026
  angkatan?: string; // 'X' | 'XI'
  kelas_jurusan?: string;
  divisi?: string;
  search?: string;
  sort_by?: 'tanggal' | 'nama' | 'waktu';
  sort_order?: 'asc' | 'desc';
}

export interface RekapStatistik {
  total_anggota: number;
  total_absensi_hari_ini: number;
  total_absensi_bulan_ini: number;
  kehadiran_per_divisi: Record<string, number>;
  kehadiran_per_angkatan: Record<string, number>;
}

export interface ApiResponse<T = any> {
  success?: boolean;
  message?: string;
  data?: T;
  error?: string;
}
