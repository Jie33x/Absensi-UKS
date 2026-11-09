<script setup lang="ts">
import { ref } from 'vue';
import { Calendar, Search, ChevronDown, ChevronUp, RotateCcw, X } from 'lucide-vue-next';
import { DAFTAR_KELAS_JURUSAN, DAFTAR_DIVISI } from '../types';

defineProps<{
  bulan: string;
  tahun: string;
  angkatan: string;
  kelas: string;
  divisi: string;
  search: string;
  activeChips: { id: string; label: string; onClear: () => void }[];
  bulanOptions: { value: string; label: string }[];
}>();

const emit = defineEmits<{
  (e: 'update:bulan', val: string): void;
  (e: 'update:tahun', val: string): void;
  (e: 'update:angkatan', val: string): void;
  (e: 'update:kelas', val: string): void;
  (e: 'update:divisi', val: string): void;
  (e: 'update:search', val: string): void;
  (e: 'reset'): void;
}>();

const isFilterLainnyaOpen = ref(false);
</script>

<template>
  <div class="bg-[#FFFDF8] rounded-2xl border border-[#EAE3D7] p-4 shadow-2xs space-y-3.5">
    <!-- Main Filters Row -->
    <div class="grid grid-cols-1 sm:grid-cols-12 gap-3 text-xs">
      <!-- Bulan -->
      <div class="sm:col-span-4 space-y-1">
        <label class="font-medium text-[#343630] font-sans flex items-center gap-1">
          <Calendar class="w-3.5 h-3.5 text-[#77846B]" />
          <span>Bulan</span>
        </label>
        <select
          :value="bulan"
          @change="emit('update:bulan', ($event.target as HTMLSelectElement).value)"
          class="w-full px-3 py-2 min-h-[42px] bg-[#F8F6F0] border border-[#EAE3D7] rounded-xl text-base sm:text-xs text-[#343630] focus:ring-1 focus:ring-[#984B50] focus:outline-none"
        >
          <option value="">Semua Bulan</option>
          <option v-for="b in bulanOptions" :key="b.value" :value="b.value">
            {{ b.label }}
          </option>
        </select>
      </div>

      <!-- Tahun -->
      <div class="sm:col-span-3 space-y-1">
        <label class="font-medium text-[#343630] font-sans">Tahun</label>
        <input
          type="number"
          :value="tahun"
          @input="emit('update:tahun', ($event.target as HTMLInputElement).value)"
          placeholder="Tahun..."
          class="w-full px-3 py-2 min-h-[42px] bg-[#F8F6F0] border border-[#EAE3D7] rounded-xl text-base sm:text-xs text-[#343630] focus:ring-1 focus:ring-[#984B50] focus:outline-none"
        />
      </div>

      <!-- Angkatan -->
      <div class="sm:col-span-5 space-y-1">
        <label class="font-medium text-[#343630] font-sans">Angkatan</label>
        <div class="grid grid-cols-3 gap-1.5">
          <button
            type="button"
            @click="emit('update:angkatan', '')"
            :class="[
              'py-2 px-2 min-h-[42px] rounded-xl font-medium text-[11px] border text-center transition-all cursor-pointer flex items-center justify-center',
              angkatan === ''
                ? 'bg-[#984B50] text-[#FFFDF8] border-[#984B50] shadow-2xs'
                : 'bg-[#F8F6F0] text-[#343630] border-[#EAE3D7] hover:bg-[#EAE3D7]/60'
            ]"
          >
            Semua
          </button>
          <button
            type="button"
            @click="emit('update:angkatan', 'XI')"
            :class="[
              'py-2 px-2 min-h-[42px] rounded-xl font-medium text-[11px] border text-center transition-all cursor-pointer flex items-center justify-center',
              angkatan === 'XI'
                ? 'bg-[#984B50] text-[#FFFDF8] border-[#984B50] shadow-2xs'
                : 'bg-[#F8F6F0] text-[#343630] border-[#EAE3D7] hover:bg-[#EAE3D7]/60'
            ]"
          >
            Kelas 11
          </button>
          <button
            type="button"
            @click="emit('update:angkatan', 'X')"
            :class="[
              'py-2 px-2 min-h-[42px] rounded-xl font-medium text-[11px] border text-center transition-all cursor-pointer flex items-center justify-center',
              angkatan === 'X'
                ? 'bg-[#984B50] text-[#FFFDF8] border-[#984B50] shadow-2xs'
                : 'bg-[#F8F6F0] text-[#343630] border-[#EAE3D7] hover:bg-[#EAE3D7]/60'
            ]"
          >
            Kelas 10
          </button>
        </div>
      </div>
    </div>

    <!-- Expandable Secondary Filters -->
    <div class="pt-1">
      <button
        type="button"
        @click="isFilterLainnyaOpen = !isFilterLainnyaOpen"
        class="inline-flex items-center gap-1.5 text-xs font-semibold text-[#77846B] hover:text-[#5e6954] cursor-pointer"
      >
        <ChevronUp v-if="isFilterLainnyaOpen" class="w-4 h-4" />
        <ChevronDown v-else class="w-4 h-4" />
        <span>{{ isFilterLainnyaOpen ? 'Sembunyikan Filter Lanjutan' : 'Filter Lanjutan (Kelas, Divisi, Cari Nama)' }}</span>
      </button>

      <div v-if="isFilterLainnyaOpen" class="mt-3 pt-3 border-t border-[#EAE3D7] grid grid-cols-1 sm:grid-cols-3 gap-3 text-xs">
        <!-- Cari Nama -->
        <div class="space-y-1">
          <label class="font-medium text-[#343630] font-sans">Cari Nama Anggota</label>
          <div class="relative">
            <Search class="w-3.5 h-3.5 absolute left-3 top-3 text-[#85847D]" />
            <input
              type="text"
              :value="search"
              @input="emit('update:search', ($event.target as HTMLInputElement).value)"
              placeholder="Ketik nama..."
              class="w-full pl-9 pr-3 py-2 min-h-[40px] bg-[#F8F6F0] border border-[#EAE3D7] rounded-xl text-base sm:text-xs text-[#343630] focus:ring-1 focus:ring-[#984B50] focus:outline-none"
            />
          </div>
        </div>

        <!-- Kelas -->
        <div class="space-y-1">
          <label class="font-medium text-[#343630] font-sans">Kelas / Jurusan</label>
          <select
            :value="kelas"
            @change="emit('update:kelas', ($event.target as HTMLSelectElement).value)"
            class="w-full px-3 py-2 min-h-[40px] bg-[#F8F6F0] border border-[#EAE3D7] rounded-xl text-base sm:text-xs text-[#343630] focus:ring-1 focus:ring-[#984B50] focus:outline-none"
          >
            <option value="">Semua Kelas</option>
            <option v-for="k in DAFTAR_KELAS_JURUSAN" :key="k" :value="k">
              {{ k }}
            </option>
          </select>
        </div>

        <!-- Divisi -->
        <div class="space-y-1">
          <label class="font-medium text-[#343630] font-sans">Divisi</label>
          <select
            :value="divisi"
            @change="emit('update:divisi', ($event.target as HTMLSelectElement).value)"
            class="w-full px-3 py-2 min-h-[40px] bg-[#F8F6F0] border border-[#EAE3D7] rounded-xl text-base sm:text-xs text-[#343630] focus:ring-1 focus:ring-[#984B50] focus:outline-none"
          >
            <option value="">Semua Divisi</option>
            <option v-for="d in DAFTAR_DIVISI" :key="d" :value="d">
              Divisi {{ d }}
            </option>
          </select>
        </div>
      </div>
    </div>

    <!-- Active Filter Chips -->
    <div v-if="activeChips.length > 0" class="pt-2 border-t border-[#EAE3D7] flex flex-wrap items-center justify-between gap-2">
      <div class="flex flex-wrap items-center gap-1.5">
        <span class="text-[11px] text-[#85847D] font-sans">Filter aktif:</span>
        <span
          v-for="chip in activeChips"
          :key="chip.id"
          class="inline-flex items-center gap-1 text-[11px] bg-[#F8F6F0] text-[#343630] border border-[#EAE3D7] px-2.5 py-0.5 rounded-lg"
        >
          <span>{{ chip.label }}</span>
          <button
            type="button"
            @click="chip.onClear"
            class="hover:text-[#984B50] cursor-pointer"
          >
            <X class="w-3 h-3" />
          </button>
        </span>
      </div>

      <button
        type="button"
        @click="emit('reset')"
        class="inline-flex items-center gap-1 text-xs text-[#984B50] hover:underline font-editorial italic cursor-pointer"
      >
        <RotateCcw class="w-3 h-3" />
        <span>Reset Semua</span>
      </button>
    </div>
  </div>
</template>
