<script setup lang="ts">
import { Trash2 } from 'lucide-vue-next';

defineProps<{
  isOpen: boolean;
  title: string;
  message: string;
  note?: string;
  confirmLabel?: string;
}>();

const emit = defineEmits<{
  (e: 'close'): void;
  (e: 'confirm'): void;
}>();
</script>

<template>
  <div
    v-if="isOpen"
    class="fixed inset-0 z-50 flex items-center justify-center bg-[#343630]/60 backdrop-blur-xs p-4 animate-in fade-in duration-150"
  >
    <div class="bg-[#FFFDF8] rounded-3xl max-w-sm w-full p-5 sm:p-6 shadow-xl border border-[#EAE3D7] space-y-4">
      <div class="w-12 h-12 bg-[#984B50]/15 text-[#984B50] rounded-full flex items-center justify-center mx-auto border border-[#984B50]/20">
        <Trash2 class="w-5 h-5" />
      </div>

      <div class="text-center">
        <h3 class="font-serif font-bold text-[#343630] text-base sm:text-lg">
          {{ title }}
        </h3>
        <p class="text-xs text-[#85847D] mt-1.5 font-sans leading-relaxed">
          {{ message }}
        </p>

        <div
          v-if="note"
          class="mt-2.5 p-2.5 bg-[#F8F6F0] border border-[#EAE3D7] rounded-xl text-left text-[11px] font-editorial italic text-[#85847D]"
        >
          {{ note }}
        </div>
      </div>

      <div class="flex gap-2 pt-2">
        <button
          type="button"
          @click="emit('close')"
          class="flex-1 min-h-[44px] py-2 border border-[#EAE3D7] text-[#85847D] text-xs font-medium rounded-xl hover:bg-[#F8F6F0] cursor-pointer"
        >
          Batal
        </button>
        <button
          type="button"
          @click="emit('confirm')"
          class="flex-1 min-h-[44px] py-2 bg-[#984B50] hover:bg-[#853f44] text-[#FFFDF8] text-xs font-medium rounded-xl cursor-pointer"
        >
          {{ confirmLabel || 'Hapus' }}
        </button>
      </div>
    </div>
  </div>
</template>
