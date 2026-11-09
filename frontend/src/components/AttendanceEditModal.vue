<script setup lang="ts">
import { ref, watch } from 'vue';
import { X } from 'lucide-vue-next';
import type { Absensi } from '../types';

const props = defineProps<{
  record: Absensi | null;
}>();

const emit = defineEmits<{
  (e: 'close'): void;
  (e: 'save', payload: Partial<Absensi>): void;
}>();

const form = ref<Partial<Absensi>>({});

watch(
  () => props.record,
  (rec) => {
    if (rec) {
      form.value = { ...rec };
    } else {
      form.value = {};
    }
  },
  { immediate: true }
);

function handleSubmit() {
  emit('save', form.value);
}
</script>

<template>
  <div
    v-if="record"
    class="fixed inset-0 z-50 flex items-center justify-center bg-[#343630]/60 backdrop-blur-xs p-4 animate-in fade-in duration-150"
  >
    <div class="bg-[#FFFDF8] rounded-3xl max-w-md w-full p-6 shadow-2xl border border-[#EAE3D7] space-y-4">
      <div class="flex items-center justify-between pb-3 border-b border-[#EAE3D7]">
        <h3 class="font-serif font-bold text-[#343630] text-lg">
          Koreksi Data Absensi
        </h3>
        <button
          type="button"
          @click="emit('close')"
          class="p-1 text-[#85847D] hover:text-[#343630] cursor-pointer"
        >
          <X class="w-5 h-5" />
        </button>
      </div>

      <form @submit.prevent="handleSubmit" class="space-y-3.5 text-xs">
        <div>
          <label class="block font-medium text-[#343630] mb-1 font-sans">Nama Anggota</label>
          <input
            type="text"
            v-model="form.nama_snapshot"
            required
            class="w-full px-3 py-2 bg-[#F8F6F0] border border-[#EAE3D7] rounded-xl text-base sm:text-xs text-[#343630] focus:ring-1 focus:ring-[#984B50] focus:outline-none"
          />
        </div>

        <div class="grid grid-cols-2 gap-2">
          <div>
            <label class="block font-medium text-[#343630] mb-1 font-sans">Tanggal</label>
            <input
              type="date"
              v-model="form.tanggal"
              required
              class="w-full px-3 py-2 bg-[#F8F6F0] border border-[#EAE3D7] rounded-xl text-base sm:text-xs text-[#343630] focus:ring-1 focus:ring-[#984B50] focus:outline-none"
            />
          </div>
          <div>
            <label class="block font-medium text-[#343630] mb-1 font-sans">Waktu (WITA)</label>
            <input
              type="text"
              v-model="form.waktu"
              placeholder="HH:mm:ss"
              required
              class="w-full px-3 py-2 bg-[#F8F6F0] border border-[#EAE3D7] rounded-xl text-base sm:text-xs text-[#343630] focus:ring-1 focus:ring-[#984B50] focus:outline-none"
            />
          </div>
        </div>

        <div class="flex gap-2 pt-2">
          <button
            type="button"
            @click="emit('close')"
            class="flex-1 py-2 border border-[#EAE3D7] text-[#85847D] rounded-xl hover:bg-[#F8F6F0] cursor-pointer"
          >
            Batal
          </button>
          <button
            type="submit"
            class="flex-1 py-2 bg-[#984B50] hover:bg-[#853f44] text-[#FFFDF8] rounded-xl cursor-pointer"
          >
            Simpan Perubahan
          </button>
        </div>
      </form>
    </div>
  </div>
</template>
