<script setup lang="ts">
import { X } from 'lucide-vue-next';
import type { Absensi } from '../types';
import { formatIndoDate } from '../composables/useTimezone';

defineProps<{
  record: Absensi | null;
}>();

const emit = defineEmits<{
  (e: 'close'): void;
}>();
</script>

<template>
  <div
    v-if="record"
    class="fixed inset-0 z-50 flex items-center justify-center bg-[#343630]/60 backdrop-blur-xs p-4 animate-in fade-in duration-150"
  >
    <div class="bg-[#FFFDF8] rounded-3xl max-w-md w-full p-6 shadow-2xl border border-[#EAE3D7] space-y-4">
      <div class="flex items-center justify-between pb-3 border-b border-[#EAE3D7]">
        <div>
          <span class="text-[10px] font-sans font-semibold tracking-wider uppercase text-[#77846B] block">
            Lembar Arsip Kehadiran
          </span>
          <h3 class="font-serif font-bold text-[#343630] text-lg">
            {{ record.nama_snapshot }}
          </h3>
        </div>
        <button
          type="button"
          @click="emit('close')"
          class="p-1 text-[#85847D] hover:text-[#343630] cursor-pointer"
        >
          <X class="w-5 h-5" />
        </button>
      </div>

      <div class="space-y-2 text-xs bg-[#F8F6F0] p-4 rounded-xl border border-[#EAE3D7]">
        <div class="flex justify-between">
          <span class="text-[#85847D]">Kelas / Jurusan:</span>
          <span class="font-medium text-[#343630]">{{ record.angkatan_snapshot }} {{ record.kelas_jurusan_snapshot }}</span>
        </div>
        <div class="flex justify-between">
          <span class="text-[#85847D]">Angkatan:</span>
          <span class="font-medium text-[#343630]">Kelas {{ record.angkatan_snapshot === 'XI' ? '11 (XI)' : '10 (X)' }}</span>
        </div>
        <div class="flex justify-between">
          <span class="text-[#85847D]">Divisi:</span>
          <span class="font-medium text-[#77846B]">Divisi {{ record.divisi_snapshot }}</span>
        </div>
        <div class="flex justify-between border-t border-[#EAE3D7] pt-2">
          <span class="text-[#85847D]">Tanggal:</span>
          <span class="font-medium text-[#343630]">{{ formatIndoDate(record.tanggal) }}</span>
        </div>
        <div class="flex justify-between">
          <span class="text-[#85847D]">Waktu Presensi:</span>
          <span class="font-medium text-[#984B50]">{{ record.waktu }} WITA (Asia/Makassar)</span>
        </div>
      </div>

      <div v-if="record.tanda_tangan" class="space-y-1">
        <span class="text-xs font-semibold text-[#343630] font-sans">Tanda Tangan Digital</span>
        <div class="p-3 bg-[#F8F6F0] rounded-xl border border-[#EAE3D7] flex items-center justify-center">
          <img :src="record.tanda_tangan" alt="Tanda tangan" class="max-h-28 object-contain" />
        </div>
      </div>

      <div class="pt-2">
        <button
          type="button"
          @click="emit('close')"
          class="w-full py-2 bg-[#EAE3D7] text-[#343630] text-xs font-medium rounded-xl cursor-pointer"
        >
          Tutup
        </button>
      </div>
    </div>
  </div>
</template>
