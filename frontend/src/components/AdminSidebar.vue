<script setup lang="ts">
import { useRoute } from 'vue-router';
import { LogOut, X } from 'lucide-vue-next';

defineProps<{
  isMobileOpen: boolean;
  username: string;
  navItems: {
    to: string;
    label: string;
    subtitle: string;
    icon: any;
  }[];
}>();

const emit = defineEmits<{
  (e: 'close'): void;
  (e: 'navigate', to: string): void;
  (e: 'logout'): void;
}>();

const route = useRoute();
</script>

<template>
  <div>
    <!-- 1. MOBILE DRAWER (< lg) -->
    <div v-if="isMobileOpen" class="lg:hidden fixed inset-0 z-50 flex">
      <!-- Dark Backdrop -->
      <div
        class="fixed inset-0 bg-[#343630]/60 backdrop-blur-xs transition-opacity duration-200"
        @click="emit('close')"
      />

      <!-- Drawer Panel -->
      <div class="relative w-4/5 max-w-[320px] bg-[#FFFDF8] h-full shadow-2xl border-r border-[#EAE3D7] flex flex-col justify-between p-5 z-10 animate-in slide-in-from-left duration-200">
        <div>
          <!-- Drawer Header -->
          <div class="flex items-center justify-between pb-4 border-b border-[#EAE3D7]">
            <div class="flex items-center gap-2.5">
              <div class="w-8 h-8 rounded-xl bg-[#F8F6F0] border border-[#EAE3D7] text-[#984B50] flex items-center justify-center font-bold text-sm">
                {{ username.charAt(0).toUpperCase() }}
              </div>
              <div>
                <span class="text-[10px] font-sans font-semibold text-[#77846B] tracking-wider uppercase block">
                  Sekretariat PMR
                </span>
                <h4 class="text-sm font-serif font-bold text-[#343630]">
                  {{ username }}
                </h4>
              </div>
            </div>

            <button
              type="button"
              @click="emit('close')"
              class="w-9 h-9 flex items-center justify-center rounded-xl text-[#85847D] hover:text-[#343630] hover:bg-[#F8F6F0] transition-colors cursor-pointer"
              aria-label="Tutup menu"
            >
              <X class="w-5 h-5" />
            </button>
          </div>

          <!-- Navigation Items -->
          <nav class="mt-4 space-y-1.5">
            <span class="text-[10px] font-sans font-semibold uppercase tracking-wider text-[#85847D] px-2 block mb-1">
              Menu Utama
            </span>
            <button
              v-for="item in navItems"
              :key="item.to"
              type="button"
              @click="emit('navigate', item.to)"
              :class="[
                'w-full flex items-center gap-3 px-3.5 py-3 rounded-xl text-left transition-all cursor-pointer min-h-[48px]',
                route.path.startsWith(item.to)
                  ? 'bg-[#984B50] text-[#FFFDF8] shadow-xs'
                  : 'text-[#343630] hover:bg-[#F8F6F0] hover:text-[#984B50]'
              ]"
            >
              <component
                :is="item.icon"
                :class="['w-5 h-5 shrink-0', route.path.startsWith(item.to) ? 'text-[#FFFDF8]' : 'text-[#77846B]']"
              />
              <div class="overflow-hidden">
                <span class="text-sm font-semibold block truncate">
                  {{ item.label }}
                </span>
                <span
                  :class="[
                    'text-[11px] block truncate font-editorial italic',
                    route.path.startsWith(item.to) ? 'text-[#FFFDF8]/85' : 'text-[#85847D]'
                  ]"
                >
                  {{ item.subtitle }}
                </span>
              </div>
            </button>
          </nav>
        </div>

        <!-- Drawer Footer -->
        <div class="pt-4 border-t border-[#EAE3D7] space-y-2">
          <button
            type="button"
            @click="emit('logout')"
            class="w-full flex items-center gap-2.5 px-3.5 py-3 rounded-xl text-left text-xs font-semibold text-[#984B50] hover:bg-[#F8F6F0] transition-colors cursor-pointer min-h-[44px]"
          >
            <LogOut class="w-4 h-4" />
            <span>Keluar (Logout)</span>
          </button>

          <div class="text-[10px] font-editorial italic text-[#85847D] text-center pt-1">
            PMR Wira • Waktu Baku WITA (UTC+8)
          </div>
        </div>
      </div>
    </div>

    <!-- 2. DESKTOP SIDEBAR (>= lg) -->
    <aside class="hidden lg:block">
      <div class="bg-[#FFFDF8] rounded-2xl border border-[#EAE3D7] p-4 shadow-2xs space-y-4 sticky top-24">
        <!-- Admin Profile Plaque -->
        <div class="p-3.5 bg-[#F8F6F0] rounded-xl border border-[#EAE3D7] flex items-center gap-3">
          <div class="w-10 h-10 rounded-xl bg-[#FFFDF8] border border-[#EAE3D7] text-[#984B50] flex items-center justify-center font-serif font-bold text-base shadow-2xs">
            {{ username.charAt(0).toUpperCase() }}
          </div>
          <div class="overflow-hidden">
            <span class="text-[10px] font-sans font-semibold text-[#77846B] tracking-wider uppercase block">
              Sekretariat PMR
            </span>
            <h4 class="text-sm font-serif font-bold text-[#343630] truncate">
              {{ username }}
            </h4>
          </div>
        </div>

        <!-- Navigation Links -->
        <nav class="space-y-1">
          <router-link
            v-for="item in navItems"
            :key="item.to"
            :to="item.to"
            :class="[
              'w-full flex items-center gap-3 px-3.5 py-3 rounded-xl text-left transition-all cursor-pointer min-h-[44px]',
              route.path.startsWith(item.to)
                ? 'bg-[#984B50] text-[#FFFDF8] shadow-xs'
                : 'text-[#343630] hover:bg-[#F8F6F0] hover:text-[#984B50]'
            ]"
          >
            <component
              :is="item.icon"
              :class="['w-4 h-4 shrink-0', route.path.startsWith(item.to) ? 'text-[#FFFDF8]' : 'text-[#77846B]']"
            />
            <div class="overflow-hidden">
              <span class="text-xs font-semibold block truncate">
                {{ item.label }}
              </span>
              <span
                :class="[
                  'text-[10px] block truncate font-editorial italic',
                  route.path.startsWith(item.to) ? 'text-[#FFFDF8]/80' : 'text-[#85847D]'
                ]"
              >
                {{ item.subtitle }}
              </span>
            </div>
          </router-link>

          <!-- Logout Button -->
          <div class="pt-2 border-t border-[#EAE3D7]">
            <button
              type="button"
              @click="emit('logout')"
              class="w-full flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-left text-xs font-medium text-[#85847D] hover:text-[#984B50] hover:bg-[#F8F6F0] transition-colors cursor-pointer min-h-[44px]"
            >
              <LogOut class="w-4 h-4 text-[#85847D]" />
              <span>Keluar (Logout)</span>
            </button>
          </div>
        </nav>
      </div>
    </aside>
  </div>
</template>
