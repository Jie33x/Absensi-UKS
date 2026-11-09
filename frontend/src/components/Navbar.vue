<script setup lang="ts">
import { computed } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import { ShieldCheck, UserCheck, Clock } from 'lucide-vue-next';
import { useTimezone } from '../composables/useTimezone';
import { useAuthStore } from '../stores/auth';

const router = useRouter();
const route = useRoute();
const authStore = useAuthStore();
const { currentTime } = useTimezone();

const isAdminRoute = computed(() => route.path.startsWith('/admin'));

function navigateToAdmin() {
  if (authStore.isAuthenticated) {
    router.push({ name: 'admin-rekap' });
  } else {
    router.push({ name: 'admin-login' });
  }
}

function navigateToAbsensi() {
  router.push({ name: 'portal-absensi' });
}
</script>

<template>
  <header class="sticky top-0 z-40 bg-[#F8F6F0]/95 backdrop-blur-md border-b border-[#EAE3D7] transition-all">
    <div class="max-w-5xl mx-auto px-4 sm:px-6">
      <div class="flex items-center justify-between h-16 sm:h-18">
        <!-- Brand Logo & Name -->
        <div
          @click="navigateToAbsensi"
          class="flex items-center gap-2.5 sm:gap-3 cursor-pointer group"
        >
          <div class="relative flex items-center justify-center w-8 h-8 sm:w-9 sm:h-9 bg-[#FFFDF8] rounded-xl border border-[#EAE3D7] shadow-2xs overflow-hidden shrink-0 group-hover:border-[#984B50] transition-colors">
            <!-- Red Cross Emblem -->
            <div class="relative w-3.5 h-3.5 sm:w-4 sm:h-4 flex items-center justify-center">
              <div class="absolute w-1 sm:w-1.5 h-3.5 sm:h-4 bg-[#984B50] rounded-xs"></div>
              <div class="absolute w-3.5 sm:w-4 h-1 sm:h-1.5 bg-[#984B50] rounded-xs"></div>
            </div>
          </div>

          <div>
            <div class="flex items-center gap-1.5 sm:gap-2">
              <span class="font-serif text-base sm:text-lg font-bold text-[#343630] tracking-tight">
                PMR Wira
              </span>
              <span class="text-[9px] font-sans font-semibold tracking-wider uppercase px-1.5 py-0.5 rounded bg-[#EAE3D7] text-[#77846B]">
                Unit Sekolah
              </span>
            </div>
            <p class="text-[10px] font-editorial italic text-[#85847D] hidden sm:block">
              Palang Merah Remaja • Presensi Digital
            </p>
          </div>
        </div>

        <!-- Right Action: Discrete Portal Switcher -->
        <div class="flex items-center gap-3">
          <!-- Live Time WITA (subtle badge) -->
          <div class="hidden md:flex items-center gap-1.5 px-3 py-1 rounded-lg bg-[#FFFDF8] border border-[#EAE3D7] text-xs text-[#85847D]">
            <Clock class="w-3.5 h-3.5 text-[#984B50]" />
            <span class="tabular-nums font-medium text-[#343630]">{{ currentTime.waktu }}</span>
            <span class="text-[10px] font-semibold text-[#984B50]">WITA</span>
          </div>

          <!-- Switch to Admin Button -->
          <button
            v-if="!isAdminRoute"
            type="button"
            @click="navigateToAdmin"
            class="inline-flex items-center gap-1.5 px-3 py-1.5 sm:px-3.5 sm:py-2 rounded-xl text-xs font-medium text-[#85847D] hover:text-[#343630] hover:bg-[#FFFDF8] border border-transparent hover:border-[#EAE3D7] transition-all cursor-pointer"
            title="Masuk ke Portal Admin / Sekretaris"
          >
            <ShieldCheck class="w-3.5 h-3.5 text-[#984B50]" />
            <span>Portal Admin</span>
            <span v-if="authStore.isAuthenticated" class="w-1.5 h-1.5 rounded-full bg-[#A5B19B]"></span>
          </button>

          <!-- Switch to Absensi Button -->
          <button
            v-else
            type="button"
            @click="navigateToAbsensi"
            class="inline-flex items-center gap-1.5 px-3 py-1.5 sm:px-3.5 sm:py-2 rounded-xl text-xs font-medium text-[#77846B] hover:text-[#5e6954] hover:bg-[#FFFDF8] border border-[#EAE3D7] transition-all cursor-pointer shadow-2xs"
          >
            <UserCheck class="w-3.5 h-3.5" />
            <span>Ke Portal Absensi</span>
          </button>
        </div>
      </div>
    </div>
  </header>
</template>
