<script setup lang="ts">
import { ref, computed, watch, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import {
  ClipboardList,
  Users,
  Settings,
  Menu,
} from 'lucide-vue-next';
import { useAuthStore } from '../stores/auth';
import AdminSidebar from '../components/AdminSidebar.vue';

const route = useRoute();
const router = useRouter();
const authStore = useAuthStore();

const isMobileDrawerOpen = ref(false);

const navigationItems = [
  {
    to: '/admin/rekap',
    label: 'Rekap Absensi',
    subtitle: 'Tabel arsip, filter & ekspor',
    icon: ClipboardList,
  },
  {
    to: '/admin/anggota',
    label: 'Data Anggota',
    subtitle: 'Kelola anggota, kelas & divisi',
    icon: Users,
  },
  {
    to: '/admin/pengaturan',
    label: 'Pengaturan Admin',
    subtitle: 'Ubah password & keamanan',
    icon: Settings,
  },
];

const currentNav = computed(() => {
  return navigationItems.find(n => route.path.startsWith(n.to)) || navigationItems[0];
});

const adminUsername = computed(() => authStore.username || 'sekretaris');

watch(isMobileDrawerOpen, (isOpen) => {
  if (isOpen) {
    document.body.style.overflow = 'hidden';
  } else {
    document.body.style.overflow = '';
  }
});

function handleNavigate(to: string) {
  router.push(to);
  isMobileDrawerOpen.value = false;
}

async function handleLogout() {
  await authStore.logout();
  router.push('/absensi');
}

onMounted(() => {
  authStore.checkAuth();
});
</script>

<template>
  <div class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8 py-3 sm:py-8">
    <!-- 1. MOBILE TOP NAVIGATION BAR (< lg) -->
    <div class="lg:hidden mb-4 bg-[#FFFDF8] rounded-2xl border border-[#EAE3D7] px-3.5 py-2.5 flex items-center justify-between shadow-2xs sticky top-16 z-30">
      <div class="flex items-center gap-2.5">
        <button
          type="button"
          @click="isMobileDrawerOpen = true"
          class="w-10 h-10 -ml-1 flex items-center justify-center rounded-xl bg-[#F8F6F0] text-[#343630] border border-[#EAE3D7] hover:bg-[#EAE3D7]/60 transition-colors cursor-pointer"
          aria-label="Buka menu navigasi admin"
        >
          <Menu class="w-5 h-5 text-[#984B50]" />
        </button>
        <div>
          <div class="flex items-center gap-1.5">
            <span class="text-[10px] font-sans font-semibold tracking-wider uppercase text-[#77846B]">
              Admin
            </span>
            <span class="text-[#D8D1C3]">·</span>
            <span class="font-serif font-bold text-sm text-[#343630]">
              {{ currentNav?.label }}
            </span>
          </div>
        </div>
      </div>

      <!-- Small admin username pill on mobile -->
      <div class="flex items-center gap-2">
        <div class="flex items-center gap-1.5 pl-2 py-1 pr-2.5 rounded-lg bg-[#F8F6F0] border border-[#EAE3D7] text-xs font-medium text-[#343630]">
          <div class="w-5 h-5 rounded-md bg-[#984B50] text-[#FFFDF8] flex items-center justify-center text-[10px] font-bold">
            {{ adminUsername.charAt(0).toUpperCase() }}
          </div>
          <span class="hidden xs:inline truncate max-w-[80px]">{{ adminUsername }}</span>
        </div>
      </div>
    </div>

    <!-- 2. MAIN LAYOUT WITH SIDEBAR + MAIN CONTENT VIEW -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
      <!-- Sidebar (Desktop & Mobile Drawer) -->
      <div class="lg:col-span-3">
        <AdminSidebar
          :is-mobile-open="isMobileDrawerOpen"
          :username="adminUsername"
          :nav-items="navigationItems"
          @close="isMobileDrawerOpen = false"
          @navigate="handleNavigate"
          @logout="handleLogout"
        />
      </div>

      <!-- Main Content Area -->
      <main class="lg:col-span-9 w-full min-w-0">
        <router-view />
      </main>
    </div>
  </div>
</template>
