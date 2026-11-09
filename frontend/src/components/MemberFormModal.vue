<script setup lang="ts">
import { reactive, watch } from 'vue';
import { X, Layers, GraduationCap, Building2 } from 'lucide-vue-next';
import {
  type Anggota,
  type Angkatan,
  type Divisi,
  DAFTAR_ANGKATAN,
  DAFTAR_KELAS_JURUSAN,
  DAFTAR_DIVISI,
} from '../types';

const props = defineProps<{
  isOpen: boolean;
  editingMember: Anggota | null;
}>();

const emit = defineEmits<{
  (e: 'close'): void;
  (e: 'save', payload: { nama: string; angkatan: Angkatan; kelas_jurusan: string; divisi: Divisi }): void;
}>();

const form = reactive({
  nama: '',
  angkatan: 'XI' as Angkatan,
  kelas_jurusan: 'PPLG 1',
  divisi: 'LITBANG' as Divisi,
});

watch(
  () => props.editingMember,
  (member) => {
    if (member) {
      form.nama = member.nama;
      form.angkatan = member.angkatan;
      form.kelas_jurusan = member.kelas_jurusan;
      form.divisi = member.divisi;
    } else {
      form.nama = '';
      form.angkatan = 'XI';
      form.kelas_jurusan = 'PPLG 1';
      form.divisi = 'LITBANG';
    }
  },
  { immediate: true }
);

function handleSubmit() {
  if (!form.nama.trim()) {
    alert('Nama lengkap wajib diisi');
    return;
  }
  emit('save', { ...form, nama: form.nama.trim() });
}
</script>

<template>
  <div
    v-if="isOpen"
    class="fixed inset-0 z-50 flex items-end sm:items-center justify-center bg-[#343630]/60 backdrop-blur-xs p-0 sm:p-4 animate-in fade-in duration-150"
  >
    <div class="bg-[#FFFDF8] rounded-t-3xl sm:rounded-3xl max-w-md w-full p-5 sm:p-6 shadow-2xl border border-[#EAE3D7] max-h-[92vh] overflow-y-auto animate-in slide-in-from-bottom duration-200">
      <div class="flex items-center justify-between pb-3 border-b border-[#EAE3D7]">
        <div>
          <span class="text-[10px] font-sans font-semibold tracking-wider uppercase text-[#77846B] block">
            Administrasi Keanggotaan
          </span>
          <h3 class="font-serif font-bold text-[#343630] text-lg sm:text-xl">
            {{ editingMember ? 'Edit Data Anggota' : 'Tambah Anggota Baru' }}
          </h3>
        </div>
        <button
          type="button"
          @click="emit('close')"
          class="w-10 h-10 flex items-center justify-center text-[#85847D] hover:text-[#343630] rounded-xl cursor-pointer"
          aria-label="Tutup form anggota"
        >
          <X class="w-5 h-5" />
        </button>
      </div>

      <form @submit.prevent="handleSubmit" class="space-y-4 mt-4 text-xs sm:text-sm">
        <!-- 1. Nama Lengkap -->
        <div>
          <label class="block font-medium text-[#343630] mb-1.5 font-sans">
            1. Nama Lengkap <span class="text-[#984B50]">*</span>
          </label>
          <input
            type="text"
            v-model="form.nama"
            placeholder="Contoh: Salma Rahmawati"
            required
            class="w-full px-3.5 py-2.5 min-h-[44px] bg-[#F8F6F0] border border-[#EAE3D7] rounded-xl text-base sm:text-sm text-[#343630] focus:ring-1 focus:ring-[#984B50] focus:outline-none transition-all placeholder:text-[#85847D]/60"
          />
        </div>

        <!-- 2. Angkatan -->
        <div>
          <label class="block font-medium text-[#343630] mb-1.5 flex items-center gap-1 font-sans">
            <Layers class="w-3.5 h-3.5 text-[#77846B]" />
            <span>2. Angkatan</span>
            <span class="text-[#984B50]">*</span>
          </label>
          <div class="grid grid-cols-2 gap-2">
            <button
              v-for="opt in DAFTAR_ANGKATAN"
              :key="opt.value"
              type="button"
              @click="form.angkatan = opt.value"
              :class="[
                'py-2.5 px-3 min-h-[44px] rounded-xl font-medium text-xs sm:text-sm border text-center transition-all cursor-pointer flex items-center justify-center',
                form.angkatan === opt.value
                  ? 'bg-[#984B50] text-[#FFFDF8] border-[#984B50] shadow-2xs'
                  : 'bg-[#F8F6F0] text-[#343630] border-[#EAE3D7] hover:bg-[#EAE3D7]/60'
              ]"
            >
              {{ opt.label }}
            </button>
          </div>
        </div>

        <!-- 3. Kelas / Jurusan -->
        <div>
          <label class="block font-medium text-[#343630] mb-1.5 flex items-center gap-1 font-sans">
            <GraduationCap class="w-3.5 h-3.5 text-[#77846B]" />
            <span>3. Kelas / Jurusan</span>
            <span class="text-[#984B50]">*</span>
          </label>
          <select
            v-model="form.kelas_jurusan"
            class="w-full px-3.5 py-2.5 min-h-[44px] bg-[#F8F6F0] border border-[#EAE3D7] rounded-xl text-base sm:text-sm text-[#343630] focus:ring-1 focus:ring-[#984B50] focus:outline-none"
          >
            <option v-for="k in DAFTAR_KELAS_JURUSAN" :key="k" :value="k">
              {{ form.angkatan }} {{ k }}
            </option>
          </select>
        </div>

        <!-- 4. Divisi PMR -->
        <div>
          <label class="block font-medium text-[#343630] mb-1.5 flex items-center gap-1 font-sans">
            <Building2 class="w-3.5 h-3.5 text-[#77846B]" />
            <span>4. Divisi PMR</span>
            <span class="text-[#984B50]">*</span>
          </label>
          <select
            v-model="form.divisi"
            class="w-full px-3.5 py-2.5 min-h-[44px] bg-[#F8F6F0] border border-[#EAE3D7] rounded-xl text-base sm:text-sm text-[#343630] focus:ring-1 focus:ring-[#984B50] focus:outline-none"
          >
            <option v-for="d in DAFTAR_DIVISI" :key="d" :value="d">
              Divisi {{ d }}
            </option>
          </select>
        </div>

        <!-- Buttons -->
        <div class="pt-3 flex gap-2.5">
          <button
            type="button"
            @click="emit('close')"
            class="flex-1 min-h-[44px] py-2.5 border border-[#EAE3D7] text-[#85847D] font-medium text-xs sm:text-sm rounded-xl hover:bg-[#F8F6F0] cursor-pointer"
          >
            Batal
          </button>
          <button
            type="submit"
            class="flex-1 min-h-[44px] py-2.5 bg-[#984B50] hover:bg-[#853f44] text-[#FFFDF8] font-medium text-xs sm:text-sm rounded-xl shadow-xs cursor-pointer"
          >
            {{ editingMember ? 'Simpan Perubahan' : 'Daftarkan Anggota' }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>
