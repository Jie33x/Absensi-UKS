import { defineStore } from 'pinia';
import { ref } from 'vue';
import type { Absensi, AbsensiFilter, RekapStatistik } from '../types';
import { api } from '../services/api';

export const useAttendanceStore = defineStore('attendance', () => {
  const records = ref<Absensi[]>([]);
  const loading = ref(false);
  const error = ref<string | null>(null);
  const statistik = ref<RekapStatistik | null>(null);

  async function fetchRecords(filters: AbsensiFilter = {}) {
    loading.value = true;
    error.value = null;
    try {
      const data = await api.getAbsensi(filters);
      records.value = data;
      return data;
    } catch (err: any) {
      error.value = err.message || 'Gagal memuat rekap absensi';
      throw err;
    } finally {
      loading.value = false;
    }
  }

  async function fetchStatistik() {
    try {
      const stats = await api.getStatistik();
      statistik.value = stats;
      return stats;
    } catch {
      return null;
    }
  }

  async function checkToday(anggotaId: number) {
    return await api.checkToday(anggotaId);
  }

  async function submitAttendance(payload: { anggota_id: number; tanda_tangan: string }) {
    return await api.submitAbsensi(payload);
  }

  async function updateRecord(id: number, payload: Partial<Absensi>) {
    const res = await api.updateAbsensi(id, payload);
    const index = records.value.findIndex(r => r.id === id);
    if (index !== -1 && res.absensi) {
      records.value[index] = res.absensi;
    }
    return res;
  }

  async function deleteRecord(id: number) {
    const res = await api.deleteAbsensi(id);
    records.value = records.value.filter(r => r.id !== id);
    return res;
  }

  return {
    records,
    loading,
    error,
    statistik,
    fetchRecords,
    fetchStatistik,
    checkToday,
    submitAttendance,
    updateRecord,
    deleteRecord,
  };
});
