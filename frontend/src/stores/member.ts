import { defineStore } from 'pinia';
import { ref } from 'vue';
import type { Anggota } from '../types';
import { api } from '../services/api';

export const useMemberStore = defineStore('member', () => {
  const members = ref<Anggota[]>([]);
  const loading = ref(false);
  const error = ref<string | null>(null);

  async function fetchMembers(filters?: { search?: string; angkatan?: string; kelas?: string; divisi?: string }) {
    loading.value = true;
    error.value = null;
    try {
      const data = await api.getAnggota(filters);
      members.value = data;
      return data;
    } catch (err: any) {
      error.value = err.message || 'Gagal memuat data anggota';
      throw err;
    } finally {
      loading.value = false;
    }
  }

  async function search(q: string) {
    return await api.searchAnggota(q);
  }

  async function addMember(data: { nama: string; angkatan: string; kelas_jurusan: string; divisi: string }) {
    loading.value = true;
    try {
      const result = await api.createAnggota(data);
      await fetchMembers();
      return result;
    } finally {
      loading.value = false;
    }
  }

  async function updateMember(id: number, data: { nama: string; angkatan: string; kelas_jurusan: string; divisi: string }) {
    loading.value = true;
    try {
      const result = await api.updateAnggota(id, data);
      await fetchMembers();
      return result;
    } finally {
      loading.value = false;
    }
  }

  async function deleteMember(id: number) {
    loading.value = true;
    try {
      const result = await api.deleteAnggota(id);
      await fetchMembers();
      return result;
    } finally {
      loading.value = false;
    }
  }

  return {
    members,
    loading,
    error,
    fetchMembers,
    search,
    addMember,
    updateMember,
    deleteMember,
  };
});
