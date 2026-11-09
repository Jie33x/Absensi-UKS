<script setup lang="ts">
import Navbar from './components/Navbar.vue';
</script>

<template>
  <div class="min-h-screen bg-[#F8F6F0] flex flex-col text-[#343630] selection:bg-[#C99491]/30 selection:text-[#343630]">
    <!-- Top Navbar -->
    <Navbar />

    <!-- Main Router Content -->
    <div class="flex-1">
      <router-view />
    </div>

    <!-- Vintage Editorial Stationery Footer -->
    <footer class="border-t border-[#EAE3D7] bg-[#F8F6F0]/80 py-6 mt-auto">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-[#85847D]">
        <div class="flex items-center gap-2">
          <span class="w-1.5 h-1.5 rounded-full bg-[#984B50]"></span>
          <span class="font-serif font-semibold text-[#343630]">Palang Merah Remaja Wira</span>
          <span>·</span>
          <span class="font-editorial italic">"Siamo Tutti Fratelli" (Kita Semua Bersaudara)</span>
        </div>
        <div class="font-sans text-[11px] text-[#85847D]">
          Buku Induk Presensi Digital · Standar Waktu: Asia/Makassar (WITA, UTC+8)
        </div>
      </div>
    </footer>
  </div>
</template>
