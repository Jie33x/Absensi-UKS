<script setup lang="ts">
import { Calendar, Clock, RotateCcw } from 'lucide-vue-next';
import type { Anggota } from '../types';
import type { MakassarDateTime } from '../composables/useTimezone';

defineProps<{
  member: Anggota;
  currentTime: MakassarDateTime;
}>();

const emit = defineEmits<{
  (e: 'change'): void;
}>();
</script>

<template>
  <div class="bg-[#F8F6F0] border border-[#EAE3D7] rounded-xl p-4 sm:p-5 relative">
    <div class="flex items-start justify-between gap-3">
      <div>
        <span class="text-[10px] font-sans font-semibold tracking-wider uppercase text-[#77846B] block">
          Anggota Terpilih
        </span>
        <h3 class="text-xl sm:text-2xl font-serif font-bold text-[#343630] mt-0.5">
          {{ member.nama }}
        </h3>
        <p class="text-xs text-[#85847D] mt-1 font-sans">
          {{ member.angkatan }} {{ member.kelas_jurusan }} • Angkatan {{ member.angkatan }} • Divisi {{ member.divisi }}
        </p>
        <p class="text-[11px] text-[#85847D]/80 mt-2 font-editorial italic flex items-center gap-1.5">
          <Calendar class="w-3 h-3 text-[#77846B]" />
          <span>{{ currentTime.formattedDateIndo }}</span>
          <span>•</span>
          <Clock class="w-3 h-3 text-[#984B50]" />
          <span class="tabular-nums font-sans font-medium text-[#343630]">{{ currentTime.waktu }} WITA</span>
        </p>
      </div>

      <!-- Tombol Kecil "Ganti" -->
      <button
        type="button"
        @click="emit('change')"
        class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-medium text-[#85847D] hover:text-[#984B50] bg-[#FFFDF8] hover:bg-[#EAE3D7]/40 border border-[#EAE3D7] rounded-lg transition-colors cursor-pointer shrink-0"
        title="Ganti anggota lain"
      >
        <RotateCcw class="w-3 h-3" />
        <span>Ganti</span>
      </button>
    </div>
  </div>
</template>
