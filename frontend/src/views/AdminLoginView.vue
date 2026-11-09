<script setup lang="ts">
import { ref } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import { ShieldCheck, User, Lock, Eye, EyeOff, AlertCircle } from 'lucide-vue-next';
import { useAuthStore } from '../stores/auth';

const router = useRouter();
const route = useRoute();
const authStore = useAuthStore();

const loginUsername = ref('sekretaris');
const loginPassword = ref('pmr123');
const showPassword = ref(false);
const loginLoading = ref(false);
const loginError = ref<string | null>(null);

const BOTANICAL_LOGIN_IMAGE = '/assets/images/botanical_login_split_1794136771214.jpg';

async function handleLogin() {
  loginError.value = null;
  loginLoading.value = true;

  try {
    await authStore.login({
      username: loginUsername.value.trim(),
      password: loginPassword.value,
    });

    const redirectPath = (route.query.redirect as string) || '/admin/rekap';
    router.push(redirectPath);
  } catch (err: any) {
    loginError.value = err.message || 'Login gagal';
  } finally {
    loginLoading.value = false;
  }
}
</script>

<template>
  <div class="max-w-4xl mx-auto px-4 py-6 sm:py-16">
    <div class="bg-[#FFFDF8] rounded-3xl border border-[#EAE3D7] shadow-sm overflow-hidden grid grid-cols-1 md:grid-cols-12">
      <!-- Left Side: Botanical Illustration -->
      <div class="md:col-span-5 relative bg-[#F8F6F0] p-6 sm:p-8 flex flex-col justify-between border-b md:border-b-0 md:border-r border-[#EAE3D7]">
        <div>
          <span class="text-[10px] font-sans font-semibold tracking-widest uppercase text-[#77846B] block">
            Palang Merah Remaja
          </span>
          <h3 class="font-serif text-xl sm:text-2xl font-bold text-[#343630] mt-0.5">
            Portal Sekretariat
          </h3>
          <p class="text-xs font-editorial italic text-[#85847D] mt-0.5">
            "Siamo Tutti Fratelli" — Kita Semua Bersaudara
          </p>
        </div>

        <!-- Botanical Artwork -->
        <div class="my-4 sm:my-6 relative rounded-2xl overflow-hidden border border-[#EAE3D7]/80 shadow-2xs">
          <img
            :src="BOTANICAL_LOGIN_IMAGE"
            alt="Botanical watercolor study"
            class="w-full h-36 sm:h-64 object-cover object-center"
            referrerPolicy="no-referrer"
          />
        </div>

        <div class="text-[11px] font-editorial italic text-[#85847D] hidden sm:block border-t border-[#EAE3D7] pt-3">
          Administrasi Resmi Presensi & Data Anggota PMR Wira
        </div>
      </div>

      <!-- Right Side: Login Form -->
      <div class="md:col-span-7 p-6 sm:p-10 flex flex-col justify-center space-y-5">
        <div>
          <div class="w-10 h-10 rounded-xl bg-[#F8F6F0] border border-[#EAE3D7] flex items-center justify-center text-[#984B50] mb-2 sm:mb-3">
            <ShieldCheck class="w-5 h-5" />
          </div>
          <h2 class="font-serif text-2xl sm:text-3xl font-bold text-[#343630]">
            Masuk ke Portal Admin
          </h2>
          <p class="text-xs sm:text-sm text-[#85847D] mt-1 font-sans">
            Akses terbatas untuk sekretaris dan pengurus PMR Wira.
          </p>
        </div>

        <div
          v-if="loginError"
          class="p-3.5 bg-[#FFFDF8] border border-[#C99491] rounded-xl text-[#984B50] text-xs flex items-center gap-2"
        >
          <AlertCircle class="w-4 h-4 shrink-0 text-[#984B50]" />
          <span>{{ loginError }}</span>
        </div>

        <form @submit.prevent="handleLogin" class="space-y-4">
          <div>
            <label class="block text-xs font-semibold text-[#343630] mb-1.5 font-sans">
              Nama Pengguna (Username)
            </label>
            <div class="relative">
              <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-[#85847D]">
                <User class="w-4 h-4" />
              </div>
              <input
                type="text"
                v-model="loginUsername"
                placeholder="Masukkan username admin"
                required
                class="w-full pl-10 pr-4 py-2.5 min-h-[44px] bg-[#F8F6F0] border border-[#EAE3D7] rounded-xl text-base sm:text-sm text-[#343630] focus:ring-1 focus:ring-[#984B50] focus:border-[#984B50] focus:outline-none transition-all placeholder:text-[#85847D]/60"
              />
            </div>
          </div>

          <div>
            <label class="block text-xs font-semibold text-[#343630] mb-1.5 font-sans">
              Kata Sandi (Password)
            </label>
            <div class="relative">
              <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-[#85847D]">
                <Lock class="w-4 h-4" />
              </div>
              <input
                :type="showPassword ? 'text' : 'password'"
                v-model="loginPassword"
                placeholder="Masukkan password admin"
                required
                class="w-full pl-10 pr-11 py-2.5 min-h-[44px] bg-[#F8F6F0] border border-[#EAE3D7] rounded-xl text-base sm:text-sm text-[#343630] focus:ring-1 focus:ring-[#984B50] focus:border-[#984B50] focus:outline-none transition-all placeholder:text-[#85847D]/60"
              />
              <button
                type="button"
                @click="showPassword = !showPassword"
                class="absolute inset-y-0 right-0 pr-3.5 min-w-[44px] min-h-[44px] flex items-center justify-center text-[#85847D] hover:text-[#343630] cursor-pointer"
                :aria-label="showPassword ? 'Sembunyikan password' : 'Lihat password'"
              >
                <EyeOff v-if="showPassword" class="w-4 h-4" />
                <Eye v-else class="w-4 h-4" />
              </button>
            </div>
          </div>

          <div class="p-3 bg-[#F8F6F0] border border-[#EAE3D7] rounded-xl text-[11px] text-[#85847D] flex items-center justify-between font-editorial italic">
            <span>Kredensial Default:</span>
            <span class="font-sans font-medium text-[#343630] bg-[#FFFDF8] px-2 py-0.5 rounded border border-[#EAE3D7]">
              sekretaris / pmr123
            </span>
          </div>

          <button
            type="submit"
            :disabled="loginLoading"
            class="w-full min-h-[44px] py-2.5 bg-[#984B50] hover:bg-[#853f44] disabled:bg-[#D8D1C3] text-[#FFFDF8] font-medium text-sm rounded-xl shadow-xs transition-all cursor-pointer flex items-center justify-center gap-2"
          >
            <template v-if="loginLoading">
              <div class="w-4 h-4 border-2 border-[#FFFDF8] border-t-transparent rounded-full animate-spin"></div>
              <span>Memverifikasi Akses...</span>
            </template>
            <template v-else>
              <Lock class="w-4 h-4" />
              <span>Masuk ke Portal Admin</span>
            </template>
          </button>
        </form>
      </div>
    </div>
  </div>
</template>
