<script setup lang="ts">
import { X } from 'lucide-vue-next';

defineProps<{
  signature: {
    imageUrl: string;
    nama: string;
    tanggal: string;
    waktu: string;
  } | null;
}>();

const emit = defineEmits<{
  (e: 'close'): void;
}>();
</script>

<template>
  <div
    v-if="signature"
    class="fixed inset-0 z-50 flex items-center justify-center bg-[#343630]/60 backdrop-blur-xs p-4 animate-in fade-in duration-150"
  >
    <div class="bg-[#FFFDF8] rounded-3xl max-w-sm w-full p-5 shadow-2xl border border-[#EAE3D7] space-y-4">
      <div class="flex items-center justify-between pb-3 border-b border-[#EAE3D7]">
        <div>
          <span class="text-[10px] font-sans font-semibold tracking-wider uppercase text-[#77846B] block">
            Paraf Presensi Digital
          </span>
          <h4 class="font-serif font-bold text-[#343630] text-base">
            {{ signature.nama }}
          </h4>
        </div>
        <button
          type="button"
          @click="emit('close')"
          class="p-1 text-[#85847D] hover:text-[#343630] rounded-lg cursor-pointer"
        >
          <X class="w-5 h-5" />
        </button>
      </div>

      <div class="p-4 bg-[#F8F6F0] rounded-2xl border border-[#EAE3D7] flex items-center justify-center">
        <img
          :src="signature.imageUrl"
          alt="Tanda tangan digital"
          class="max-h-40 max-w-full object-contain"
        />
      </div>

      <div class="text-[11px] font-editorial italic text-[#85847D] text-center">
        Tercatat pada {{ signature.tanggal }} pukul {{ signature.waktu }} WITA
      </div>
    </div>
  </div>
</template>
