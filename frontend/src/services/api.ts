import type {
  Anggota,
  Absensi,
  AbsensiFilter,
  RekapStatistik,
  AdminProfile
} from '../types';
import { getMakassarDateTime } from '../composables/useTimezone';

const API_BASE_URL = (import.meta.env.VITE_API_BASE_URL as string) || '/api';

function getAuthHeader(): Record<string, string> {
  const token = localStorage.getItem('pmr_admin_token');
  return token ? { Authorization: `Bearer ${token}` } : {};
}

// Initial seed data for fallback / preview demonstration
const INITIAL_ANGGOTA: Anggota[] = [
  { id: 1, nama: 'Salma Rahmawati', angkatan: 'XI', kelas_jurusan: 'PPLG 1', divisi: 'LITBANG', created_at: '2026-01-10T08:00:00Z' },
  { id: 2, nama: 'Ahmad Fauzan', angkatan: 'XI', kelas_jurusan: 'DKV 1', divisi: 'PDD', created_at: '2026-01-10T08:00:00Z' },
  { id: 3, nama: 'Nabila Putri Ayu', angkatan: 'XI', kelas_jurusan: 'PPLG 2', divisi: 'PRIMA', created_at: '2026-01-10T08:00:00Z' },
  { id: 4, nama: 'Bintang Pratama', angkatan: 'X', kelas_jurusan: 'TJKT 1', divisi: 'KEBERSIHAN', created_at: '2026-01-12T08:00:00Z' },
  { id: 5, nama: 'Dewi Lestari', angkatan: 'X', kelas_jurusan: 'ANIMASI', divisi: 'PDD', created_at: '2026-01-12T08:00:00Z' },
  { id: 6, nama: 'Rian Hidayat', angkatan: 'XI', kelas_jurusan: 'TJKT 2', divisi: 'PRIMA', created_at: '2026-01-15T08:00:00Z' },
  { id: 7, nama: 'Siti Nurhaliza', angkatan: 'X', kelas_jurusan: 'PPLG 1', divisi: 'LITBANG', created_at: '2026-01-15T08:00:00Z' },
  { id: 8, nama: 'Fajar Maulana', angkatan: 'XI', kelas_jurusan: 'DKV 2', divisi: 'PDD', created_at: '2026-01-18T08:00:00Z' },
];

function getLocalStore<T>(key: string, defaultValue: T): T {
  try {
    const raw = localStorage.getItem(`pmr_db_${key}`);
    return raw ? JSON.parse(raw) : defaultValue;
  } catch {
    return defaultValue;
  }
}

function setLocalStore<T>(key: string, value: T): void {
  try {
    localStorage.setItem(`pmr_db_${key}`, JSON.stringify(value));
  } catch {
    // Storage quota or disabled
  }
}

export const api = {
  // ==========================================
  // AUTH
  // ==========================================
  async login(credentials: { username: string; password: string }) {
    try {
      const res = await fetch(`${API_BASE_URL}/login`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(credentials),
      });
      if (res.ok) {
        return await res.json();
      }
      const err = await res.json().catch(() => ({}));
      if (res.status !== 404 && res.status !== 502) {
        throw new Error(err.message || err.error || 'Login gagal.');
      }
    } catch (e: any) {
      if (e.message && !e.message.includes('fetch')) {
        throw e;
      }
    }

    // Client fallback preview validation
    if (
      (credentials.username === 'sekretaris' && credentials.password === 'pmr123') ||
      (credentials.username === 'admin' && credentials.password === 'admin123')
    ) {
      const profile = getLocalStore<AdminProfile>('admin_profile', {
        id: 1,
        username: credentials.username,
      });
      const token = `sanctum_token_${Date.now()}_mock`;
      return {
        success: true,
        token,
        user: { id: profile.id, username: profile.username },
        admin: { id: profile.id, username: profile.username },
      };
    }
    throw new Error('Username atau kata sandi tidak valid.');
  },

  async logout() {
    try {
      await fetch(`${API_BASE_URL}/logout`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          ...getAuthHeader(),
        },
      });
    } catch {
      // Ignore network errors on logout
    }
    localStorage.removeItem('pmr_admin_token');
    localStorage.removeItem('pmr_admin_username');
  },

  async getUser() {
    try {
      const res = await fetch(`${API_BASE_URL}/user`, {
        headers: getAuthHeader(),
      });
      if (res.ok) {
        return await res.json();
      }
    } catch {
      // Fallback
    }
    const username = localStorage.getItem('pmr_admin_username') || 'sekretaris';
    return { id: 1, username };
  },

  // ==========================================
  // ANGGOTA
  // ==========================================
  async getAnggota(filters?: { search?: string; angkatan?: string; kelas?: string; divisi?: string }) {
    const params = new URLSearchParams();
    if (filters?.search) params.append('search', filters.search);
    if (filters?.angkatan) params.append('angkatan', filters.angkatan);
    if (filters?.kelas) params.append('kelas', filters.kelas);
    if (filters?.divisi) params.append('divisi', filters.divisi);

    try {
      const res = await fetch(`${API_BASE_URL}/anggota?${params.toString()}`, {
        headers: getAuthHeader(),
      });
      if (res.ok) {
        return await res.json();
      }
    } catch {
      // Fallback
    }

    let items = getLocalStore<Anggota[]>('anggota', INITIAL_ANGGOTA);
    if (filters?.search) {
      const q = filters.search.toLowerCase();
      items = items.filter(a => a.nama.toLowerCase().includes(q));
    }
    if (filters?.angkatan) {
      items = items.filter(a => a.angkatan === filters.angkatan);
    }
    if (filters?.kelas) {
      items = items.filter(a => a.kelas_jurusan === filters.kelas);
    }
    if (filters?.divisi) {
      items = items.filter(a => a.divisi === filters.divisi);
    }
    return items.sort((a, b) => a.nama.localeCompare(b.nama));
  },

  async searchAnggota(query: string): Promise<Anggota[]> {
    if (!query.trim()) return [];
    try {
      const res = await fetch(`${API_BASE_URL}/anggota/search?q=${encodeURIComponent(query.trim())}`);
      if (res.ok) {
        return await res.json();
      }
    } catch {
      // Fallback
    }

    const q = query.trim().toLowerCase();
    const items = getLocalStore<Anggota[]>('anggota', INITIAL_ANGGOTA);
    return items
      .filter(a => a.nama.toLowerCase().includes(q))
      .sort((a, b) => {
        const aStarts = a.nama.toLowerCase().startsWith(q) ? 0 : 1;
        const bStarts = b.nama.toLowerCase().startsWith(q) ? 0 : 1;
        return aStarts - bStarts || a.nama.localeCompare(b.nama);
      })
      .slice(0, 5);
  },

  async createAnggota(data: { nama: string; angkatan: string; kelas_jurusan: string; divisi: string }) {
    try {
      const res = await fetch(`${API_BASE_URL}/anggota`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          ...getAuthHeader(),
        },
        body: JSON.stringify(data),
      });
      if (res.ok) {
        return await res.json();
      }
      const err = await res.json().catch(() => ({}));
      if (res.status !== 404 && res.status !== 502) {
        throw new Error(err.message || err.error || 'Gagal menambahkan anggota.');
      }
    } catch (e: any) {
      if (e.message && !e.message.includes('fetch')) throw e;
    }

    const items = getLocalStore<Anggota[]>('anggota', INITIAL_ANGGOTA);
    const newId = items.length ? Math.max(...items.map(i => i.id)) + 1 : 1;
    const newAnggota: Anggota = {
      id: newId,
      nama: data.nama.trim(),
      angkatan: data.angkatan as any,
      kelas_jurusan: data.kelas_jurusan.trim(),
      divisi: data.divisi as any,
      created_at: new Date().toISOString(),
    };
    items.push(newAnggota);
    setLocalStore('anggota', items);
    return { success: true, message: 'Anggota baru berhasil ditambahkan.', anggota: newAnggota };
  },

  async updateAnggota(id: number, data: { nama: string; angkatan: string; kelas_jurusan: string; divisi: string }) {
    try {
      const res = await fetch(`${API_BASE_URL}/anggota/${id}`, {
        method: 'PUT',
        headers: {
          'Content-Type': 'application/json',
          ...getAuthHeader(),
        },
        body: JSON.stringify(data),
      });
      if (res.ok) {
        return await res.json();
      }
      const err = await res.json().catch(() => ({}));
      if (res.status !== 404 && res.status !== 502) {
        throw new Error(err.message || err.error || 'Gagal memperbarui data.');
      }
    } catch (e: any) {
      if (e.message && !e.message.includes('fetch')) throw e;
    }

    const items = getLocalStore<Anggota[]>('anggota', INITIAL_ANGGOTA);
    const index = items.findIndex(a => a.id === id);
    if (index === -1) throw new Error('Data anggota tidak ditemukan.');
    items[index] = {
      ...items[index],
      nama: data.nama.trim(),
      angkatan: data.angkatan as any,
      kelas_jurusan: data.kelas_jurusan.trim(),
      divisi: data.divisi as any,
      updated_at: new Date().toISOString(),
    };
    setLocalStore('anggota', items);
    return { success: true, message: 'Data anggota berhasil diperbarui.', anggota: items[index] };
  },

  async deleteAnggota(id: number) {
    try {
      const res = await fetch(`${API_BASE_URL}/anggota/${id}`, {
        method: 'DELETE',
        headers: getAuthHeader(),
      });
      if (res.ok) {
        return await res.json();
      }
      const err = await res.json().catch(() => ({}));
      if (res.status !== 404 && res.status !== 502) {
        throw new Error(err.message || err.error || 'Gagal menghapus anggota.');
      }
    } catch (e: any) {
      if (e.message && !e.message.includes('fetch')) throw e;
    }

    let items = getLocalStore<Anggota[]>('anggota', INITIAL_ANGGOTA);
    const target = items.find(a => a.id === id);
    items = items.filter(a => a.id !== id);
    setLocalStore('anggota', items);
    return {
      success: true,
      message: `Data anggota ${target?.nama || ''} berhasil dihapus. Riwayat absensi tetap tersimpan.`,
    };
  },

  // ==========================================
  // ABSENSI
  // ==========================================
  async checkToday(anggotaId: number) {
    try {
      const res = await fetch(`${API_BASE_URL}/absensi/check-today?anggota_id=${anggotaId}`);
      if (res.ok) {
        return await res.json();
      }
    } catch {
      // Fallback
    }

    const { tanggal } = getMakassarDateTime();
    const absensiList = getLocalStore<Absensi[]>('absensi', []);
    const match = absensiList.find(a => a.anggota_id === anggotaId && a.tanggal === tanggal);
    if (match) {
      return {
        alreadyAttended: true,
        waktu: match.waktu,
        tanggal: match.tanggal,
        message: `Anggota ini sudah melakukan absensi hari ini pada pukul ${match.waktu} WITA.`,
      };
    }
    return { alreadyAttended: false };
  },

  async submitAbsensi(payload: { anggota_id: number; tanda_tangan: string }) {
    try {
      const res = await fetch(`${API_BASE_URL}/absensi`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload),
      });
      if (res.ok) {
        return await res.json();
      }
      const err = await res.json().catch(() => ({}));
      if (res.status === 409) {
        return {
          error: err.error || 'Sudah melakukan absensi hari ini.',
          sudahAbsen: true,
          waktu: err.waktu,
        };
      }
      if (res.status !== 404 && res.status !== 502) {
        throw new Error(err.message || err.error || 'Gagal mengirim absensi.');
      }
    } catch (e: any) {
      if (e.message && !e.message.includes('fetch')) throw e;
    }

    // Local fallback
    const items = getLocalStore<Anggota[]>('anggota', INITIAL_ANGGOTA);
    const member = items.find(a => a.id === payload.anggota_id);
    if (!member) throw new Error('Data anggota tidak ditemukan dalam database.');

    const { tanggal, waktu } = getMakassarDateTime();
    const absensiList = getLocalStore<Absensi[]>('absensi', []);

    // Check duplicate
    const existing = absensiList.find(a => a.anggota_id === member.id && a.tanggal === tanggal);
    if (existing) {
      return {
        error: `Pemberitahuan: ${member.nama} sudah melakukan absensi hari ini pada pukul ${existing.waktu} WITA.`,
        sudahAbsen: true,
        waktu: existing.waktu,
      };
    }

    const newRecord: Absensi = {
      id: absensiList.length ? Math.max(...absensiList.map(a => a.id)) + 1 : 1,
      anggota_id: member.id,
      nama_snapshot: member.nama,
      angkatan_snapshot: member.angkatan,
      kelas_jurusan_snapshot: member.kelas_jurusan,
      divisi_snapshot: member.divisi,
      tanggal,
      waktu,
      tanda_tangan: payload.tanda_tangan,
      created_at: new Date().toISOString(),
    };
    absensiList.unshift(newRecord);
    setLocalStore('absensi', absensiList);

    return {
      success: true,
      message: 'Absensi berhasil dicatat!',
      absensi: {
        id: newRecord.id,
        nama: member.nama,
        angkatan: member.angkatan,
        kelas_jurusan: member.kelas_jurusan,
        divisi: member.divisi,
        tanggal,
        waktu,
      },
    };
  },

  async getAbsensi(filters: AbsensiFilter = {}): Promise<Absensi[]> {
    const params = new URLSearchParams();
    if (filters.bulan) params.append('bulan', filters.bulan);
    if (filters.tahun) params.append('tahun', filters.tahun);
    if (filters.angkatan) params.append('angkatan', filters.angkatan);
    if (filters.kelas_jurusan) params.append('kelas_jurusan', filters.kelas_jurusan);
    if (filters.divisi) params.append('divisi', filters.divisi);
    if (filters.search) params.append('search', filters.search);
    if (filters.sort_by) params.append('sort_by', filters.sort_by);
    if (filters.sort_order) params.append('sort_order', filters.sort_order);

    try {
      const res = await fetch(`${API_BASE_URL}/absensi?${params.toString()}`, {
        headers: getAuthHeader(),
      });
      if (res.ok) {
        return await res.json();
      }
    } catch {
      // Fallback
    }

    let list = getLocalStore<Absensi[]>('absensi', []);
    if (filters.bulan) {
      const b = filters.bulan.padStart(2, '0');
      list = list.filter(a => a.tanggal.split('-')[1] === b);
    }
    if (filters.tahun) {
      list = list.filter(a => a.tanggal.split('-')[0] === filters.tahun);
    }
    if (filters.angkatan) {
      list = list.filter(a => a.angkatan_snapshot === filters.angkatan);
    }
    if (filters.kelas_jurusan) {
      list = list.filter(a => a.kelas_jurusan_snapshot === filters.kelas_jurusan);
    }
    if (filters.divisi) {
      list = list.filter(a => a.divisi_snapshot === filters.divisi);
    }
    if (filters.search?.trim()) {
      const q = filters.search.trim().toLowerCase();
      list = list.filter(a => a.nama_snapshot.toLowerCase().includes(q));
    }

    return list.sort((a, b) => b.tanggal.localeCompare(a.tanggal) || b.waktu.localeCompare(a.waktu));
  },

  async updateAbsensi(id: number, payload: Partial<Absensi>) {
    try {
      const res = await fetch(`${API_BASE_URL}/absensi/${id}`, {
        method: 'PUT',
        headers: {
          'Content-Type': 'application/json',
          ...getAuthHeader(),
        },
        body: JSON.stringify(payload),
      });
      if (res.ok) {
        return await res.json();
      }
      const err = await res.json().catch(() => ({}));
      if (res.status !== 404 && res.status !== 502) {
        throw new Error(err.message || err.error || 'Gagal memperbarui absensi.');
      }
    } catch (e: any) {
      if (e.message && !e.message.includes('fetch')) throw e;
    }

    const list = getLocalStore<Absensi[]>('absensi', []);
    const idx = list.findIndex(a => a.id === id);
    if (idx === -1) throw new Error('Data absensi tidak ditemukan.');
    list[idx] = { ...list[idx], ...payload, updated_at: new Date().toISOString() };
    setLocalStore('absensi', list);
    return { success: true, message: 'Data absensi berhasil diperbarui.', absensi: list[idx] };
  },

  async deleteAbsensi(id: number) {
    try {
      const res = await fetch(`${API_BASE_URL}/absensi/${id}`, {
        method: 'DELETE',
        headers: getAuthHeader(),
      });
      if (res.ok) {
        return await res.json();
      }
      const err = await res.json().catch(() => ({}));
      if (res.status !== 404 && res.status !== 502) {
        throw new Error(err.message || err.error || 'Gagal menghapus absensi.');
      }
    } catch (e: any) {
      if (e.message && !e.message.includes('fetch')) throw e;
    }

    let list = getLocalStore<Absensi[]>('absensi', []);
    const target = list.find(a => a.id === id);
    list = list.filter(a => a.id !== id);
    setLocalStore('absensi', list);
    return { success: true, message: `Data absensi ${target?.nama_snapshot || ''} berhasil dihapus.` };
  },

  // ==========================================
  // REKAP & STATISTIK
  // ==========================================
  async getStatistik(): Promise<RekapStatistik> {
    try {
      const res = await fetch(`${API_BASE_URL}/rekap/statistik`, {
        headers: getAuthHeader(),
      });
      if (res.ok) {
        return await res.json();
      }
    } catch {
      // Fallback
    }

    const { tanggal } = getMakassarDateTime();
    const currMonth = tanggal.substring(0, 7);
    const anggota = getLocalStore<Anggota[]>('anggota', INITIAL_ANGGOTA);
    const absensi = getLocalStore<Absensi[]>('absensi', []);

    const divisiCount: Record<string, number> = {};
    const angkatanCount: Record<string, number> = {};

    absensi.forEach(a => {
      divisiCount[a.divisi_snapshot] = (divisiCount[a.divisi_snapshot] || 0) + 1;
      angkatanCount[a.angkatan_snapshot] = (angkatanCount[a.angkatan_snapshot] || 0) + 1;
    });

    return {
      total_anggota: anggota.length,
      total_absensi_hari_ini: absensi.filter(a => a.tanggal === tanggal).length,
      total_absensi_bulan_ini: absensi.filter(a => a.tanggal.startsWith(currMonth)).length,
      kehadiran_per_divisi: divisiCount,
      kehadiran_per_angkatan: angkatanCount,
    };
  },

  // ==========================================
  // PENGATURAN ADMIN
  // ==========================================
  async getAdminProfile() {
    try {
      const res = await fetch(`${API_BASE_URL}/admin/profile`, {
        headers: getAuthHeader(),
      });
      if (res.ok) {
        return await res.json();
      }
    } catch {
      // Fallback
    }
    const username = localStorage.getItem('pmr_admin_username') || 'sekretaris';
    return { id: 1, username };
  },

  async updateAdminProfile(payload: { current_password: string; new_username?: string; new_password?: string }) {
    try {
      const res = await fetch(`${API_BASE_URL}/admin/profile`, {
        method: 'PUT',
        headers: {
          'Content-Type': 'application/json',
          ...getAuthHeader(),
        },
        body: JSON.stringify(payload),
      });
      if (res.ok) {
        return await res.json();
      }
      const err = await res.json().catch(() => ({}));
      if (res.status !== 404 && res.status !== 502) {
        throw new Error(err.message || err.error || 'Gagal memperbarui profil.');
      }
    } catch (e: any) {
      if (e.message && !e.message.includes('fetch')) throw e;
    }

    if (payload.new_username) {
      localStorage.setItem('pmr_admin_username', payload.new_username);
    }
    return {
      success: true,
      message: 'Profil admin berhasil diperbarui.',
      token: `sanctum_token_${Date.now()}_updated`,
      admin: { id: 1, username: payload.new_username || 'sekretaris' },
    };
  },
};
