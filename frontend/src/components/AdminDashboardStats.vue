<script setup lang="ts">
import { Users, UserCheck, CalendarCheck, PieChart } from 'lucide-vue-next';
import type { RekapStatistik } from '../types';

defineProps<{
  stats: RekapStatistik | null;
}>();
</script>

<template>
  <div v-if="stats" class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 mb-4">
    <!-- Card 1: Total Anggota -->
    <div class="bg-[#FFFDF8] rounded-2xl border border-[#EAE3D7] p-3.5 sm:p-4 shadow-2xs space-y-1">
      <div class="flex items-center justify-between">
        <span class="text-[10px] font-sans font-semibold tracking-wider uppercase text-[#77846B]">
          Total Anggota
        </span>
        <div class="w-7 h-7 rounded-lg bg-[#F8F6F0] border border-[#EAE3D7] flex items-center justify-center text-[#77846B]">
          <Users class="w-3.5 h-3.5" />
        </div>
      </div>
      <div class="font-serif font-bold text-xl sm:text-2xl text-[#343630]">
        {{ stats.total_anggota }}
      </div>
      <p class="text-[10px] text-[#85847D] font-sans">Anggota terdaftar</p>
    </div>

    <!-- Card 2: Hari Ini -->
    <div class="bg-[#FFFDF8] rounded-2xl border border-[#EAE3D7] p-3.5 sm:p-4 shadow-2xs space-y-1">
      <div class="flex items-center justify-between">
        <span class="text-[10px] font-sans font-semibold tracking-wider uppercase text-[#984B50]">
          Hadir Hari Ini
        </span>
        <div class="w-7 h-7 rounded-lg bg-[#F8F6F0] border border-[#EAE3D7] flex items-center justify-center text-[#984B50]">
          <UserCheck class="w-3.5 h-3.5" />
        </div>
      </div>
      <div class="font-serif font-bold text-xl sm:text-2xl text-[#984B50]">
        {{ stats.total_absensi_hari_ini }}
      </div>
      <p class="text-[10px] text-[#85847D] font-sans">Presensi hari ini (WITA)</p>
    </div>

    <!-- Card 3: Bulan Ini -->
    <div class="bg-[#FFFDF8] rounded-2xl border border-[#EAE3D7] p-3.5 sm:p-4 shadow-2xs space-y-1">
      <div class="flex items-center justify-between">
        <span class="text-[10px] font-sans font-semibold tracking-wider uppercase text-[#77846B]">
          Total Bulan Ini
        </span>
        <div class="w-7 h-7 rounded-lg bg-[#F8F6F0] border border-[#EAE3D7] flex items-center justify-center text-[#77846B]">
          <CalendarCheck class="w-3.5 h-3.5" />
        </div>
      </div>
      <div class="font-serif font-bold text-xl sm:text-2xl text-[#343630]">
        {{ stats.total_absensi_bulan_ini }}
      </div>
      <p class="text-[10px] text-[#85847D] font-sans">Akumulasi presensi</p>
    </div>

    <!-- Card 4: Divisi Teraktif -->
    <div class="bg-[#FFFDF8] rounded-2xl border border-[#EAE3D7] p-3.5 sm:p-4 shadow-2xs space-y-1">
      <div class="flex items-center justify-between">
        <span class="text-[10px] font-sans font-semibold tracking-wider uppercase text-[#77846B]">
          Divisi PMR
        </span>
        <div class="w-7 h-7 rounded-lg bg-[#F8F6F0] border border-[#EAE3D7] flex items-center justify-center text-[#77846B]">
          <PieChart class="w-3.5 h-3.5" />
        </div>
      </div>
      <div class="font-serif font-bold text-sm sm:text-base text-[#343630] truncate">
        4 Divisi Aktif
      </div>
      <p class="text-[10px] text-[#85847D] font-sans">PRIMA • LITBANG • PDD • KEB</p>
    </div>
  </div>
</template>
