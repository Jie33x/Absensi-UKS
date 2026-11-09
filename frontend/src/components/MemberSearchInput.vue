<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue';
import { Search } from 'lucide-vue-next';
import type { Anggota } from '../types';

defineProps<{
  modelValue: string;
  isSearching: boolean;
  searchResults: Anggota[];
  showDropdown: boolean;
}>();

const emit = defineEmits<{
  (e: 'update:modelValue', value: string): void;
  (e: 'select', member: Anggota): void;
  (e: 'focus'): void;
  (e: 'close'): void;
}>();

const containerRef = ref<HTMLDivElement | null>(null);

function handleClickOutside(event: MouseEvent) {
  if (containerRef.value && !containerRef.value.contains(event.target as Node)) {
    emit('close');
  }
}

onMounted(() => {
  document.addEventListener('mousedown', handleClickOutside);
});

onUnmounted(() => {
  document.removeEventListener('mousedown', handleClickOutside);
});
</script>

<template>
  <div ref="containerRef" class="relative space-y-1.5">
    <label
      for="member-search-input"
      class="block text-xs font-semibold text-[#343630] font-sans"
    >
      Cari Nama Anggota
    </label>

    <div class="relative">
      <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-[#85847D]">
        <Search class="w-4 h-4 text-[#984B50]" />
      </div>

      <input
        id="member-search-input"
        type="text"
        :value="modelValue"
        @input="emit('update:modelValue', ($event.target as HTMLInputElement).value)"
        @focus="emit('focus')"
        placeholder="Cari nama anggota..."
        autocomplete="off"
        class="w-full pl-10 pr-4 py-3 bg-[#F8F6F0] border border-[#EAE3D7] rounded-xl text-sm text-[#343630] placeholder:text-[#85847D]/60 focus:ring-1 focus:ring-[#984B50] focus:border-[#984B50] focus:outline-none transition-all shadow-2xs"
      />

      <div
        v-if="isSearching"
        class="absolute inset-y-0 right-0 pr-3.5 flex items-center pointer-events-none"
      >
        <div class="w-4 h-4 border-2 border-[#984B50] border-t-transparent rounded-full animate-spin"></div>
      </div>
    </div>

    <p class="text-[11px] text-[#85847D] font-sans">
      Ketik nama Anda untuk memulai absensi.
    </p>

    <!-- AUTOCOMPLETE DROPDOWN (Max 5 results) -->
    <div
      v-if="showDropdown && searchResults.length > 0"
      class="absolute z-50 left-0 right-0 mt-1 bg-[#FFFDF8] rounded-xl shadow-lg border border-[#EAE3D7] divide-y divide-[#EAE3D7]/60 overflow-hidden animate-in fade-in-50 slide-in-from-top-1 duration-150"
    >
      <button
        v-for="member in searchResults"
        :key="member.id"
        type="button"
        @click="emit('select', member)"
        class="w-full text-left p-3.5 hover:bg-[#F8F6F0] transition-colors flex items-center justify-between group cursor-pointer"
      >
        <div>
          <h4 class="text-sm font-serif font-bold text-[#343630] group-hover:text-[#984B50]">
            {{ member.nama }}
          </h4>
          <p class="text-xs text-[#85847D] mt-0.5 font-sans">
            {{ member.angkatan }} {{ member.kelas_jurusan }} • Angkatan {{ member.angkatan }} • Divisi {{ member.divisi }}
          </p>
        </div>
        <span class="text-xs font-medium text-[#984B50] group-hover:translate-x-0.5 transition-transform font-editorial italic shrink-0 ml-3">
          Pilih &rarr;
        </span>
      </button>
    </div>

    <!-- Empty State -->
    <div
      v-if="showDropdown && modelValue.trim() !== '' && searchResults.length === 0 && !isSearching"
      class="absolute z-50 left-0 right-0 mt-1 bg-[#FFFDF8] rounded-xl shadow-md border border-[#EAE3D7] p-4 text-center text-xs text-[#85847D]"
    >
      Nama anggota tidak ditemukan. Periksa kembali ejaan nama Anda.
    </div>
  </div>
</template>
