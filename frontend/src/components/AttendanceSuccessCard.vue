<script setup lang="ts">
import { CheckCircle2 } from 'lucide-vue-next';

defineProps<{
  successData: {
    nama: string;
    waktu: string;
    tanggal: string;
    kelas: string;
    divisi: string;
  };
}>();

const emit = defineEmits<{
  (e: 'done'): void;
}>();
</script>

<template>
  <div class="mb-6 p-6 sm:p-7 bg-[#FFFDF8] border border-[#A5B19B] rounded-2xl shadow-xs text-center animate-in fade-in duration-200">
    <div class="w-12 h-12 bg-[#A5B19B]/20 text-[#77846B] rounded-full flex items-center justify-center mx-auto mb-3 border border-[#A5B19B]/40">
      <CheckCircle2 class="w-6 h-6" />
    </div>
    <h2 class="font-serif text-xl sm:text-2xl font-bold text-[#343630]">
      Absensi Berhasil Dicatat
    </h2>
    <p class="text-xs text-[#77846B] font-editorial italic mt-0.5">
      Terima kasih telah melakukan presensi kehadiran tepat waktu.
    </p>

    <!-- Concise Summary in Single Line/Row -->
    <div class="mt-5 max-w-md mx-auto bg-[#F8F6F0] rounded-xl p-4 border border-[#EAE3D7] text-left text-xs space-y-2">
      <div class="flex justify-between items-center">
        <span class="text-[#85847D]">Nama Anggota:</span>
        <span class="font-serif font-bold text-[#343630] text-sm">{{ successData.nama }}</span>
      </div>
      <div class="flex justify-between items-center">
        <span class="text-[#85847D]">Kelas / Divisi:</span>
        <span class="font-medium text-[#343630]">
          {{ successData.kelas }} • Divisi {{ successData.divisi }}
        </span>
      </div>
      <div class="flex justify-between items-center border-t border-[#EAE3D7] pt-2">
        <span class="text-[#85847D]">Waktu Absensi:</span>
        <span class="font-medium text-[#984B50] tabular-nums">
          {{ successData.waktu }} WITA ({{ successData.tanggal }})
        </span>
      </div>
    </div>

    <!-- Selesai Button -->
    <div class="mt-6">
      <button
        type="button"
        @click="emit('done')"
        class="inline-flex items-center gap-2 px-6 py-2.5 bg-[#984B50] hover:bg-[#853f44] text-[#FFFDF8] text-xs sm:text-sm font-medium rounded-xl shadow-xs transition-colors cursor-pointer"
      >
        <span>Selesai</span>
      </button>
    </div>
  </div>
</template>
