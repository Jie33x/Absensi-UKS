<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { KeyRound, Shield, CheckCircle2, AlertCircle } from 'lucide-vue-next';
import { useTimezone } from '../composables/useTimezone';
import { useAuthStore } from '../stores/auth';
import { api } from '../services/api';

const authStore = useAuthStore();
const { currentTime } = useTimezone();

const username = ref(authStore.username || 'sekretaris');
const currentPassword = ref('');
const newPassword = ref('');
const confirmPassword = ref('');

const isLoading = ref(false);
const successMsg = ref<string | null>(null);
const errorMsg = ref<string | null>(null);

async function handleUpdateProfile() {
  errorMsg.value = null;
  successMsg.value = null;

  if (!currentPassword.value) {
    errorMsg.value = 'Password saat ini wajib diisi untuk verifikasi keamanan.';
    return;
  }

  if (newPassword.value && newPassword.value !== confirmPassword.value) {
    errorMsg.value = 'Konfirmasi password baru tidak cocok.';
    return;
  }

  if (newPassword.value && newPassword.value.length < 5) {
    errorMsg.value = 'Password baru minimal 5 karakter.';
    return;
  }

  isLoading.value = true;

  try {
    const res = await api.updateAdminProfile({
      current_password: currentPassword.value,
      new_username: username.value,
      new_password: newPassword.value || undefined,
    });

    successMsg.value = 'Profil dan keamanan admin berhasil diperbarui.';
    if (res.admin?.username) {
      authStore.updateCredentials(res.admin.username, res.token);
    }
    currentPassword.value = '';
    newPassword.value = '';
    confirmPassword.value = '';
  } catch (err: any) {
    errorMsg.value = err.message || 'Terjadi kesalahan sistem.';
  } finally {
    isLoading.value = false;
  }
}

onMounted(() => {
  if (authStore.username) {
    username.value = authStore.username;
  }
});
</script>

<template>
  <div class="max-w-2xl mx-auto space-y-6">
    <div>
      <h2 class="font-serif text-2xl sm:text-3xl font-bold text-[#343630] tracking-tight flex items-center gap-2">
        <span>Pengaturan Akun Sekretariat</span>
      </h2>
      <p class="text-xs sm:text-sm font-editorial italic text-[#85847D] mt-0.5">
        Kelola kredensial akses sekretaris dan preferensi keamanan sistem.
      </p>
    </div>

    <div
      v-if="successMsg"
      class="p-4 bg-[#FFFDF8] border border-[#A5B19B] text-[#77846B] rounded-xl text-xs sm:text-sm flex items-center gap-2 animate-in fade-in"
    >
      <CheckCircle2 class="w-5 h-5 text-[#77846B] shrink-0" />
      <span>{{ successMsg }}</span>
    </div>

    <div
      v-if="errorMsg"
      class="p-4 bg-[#FFFDF8] border border-[#C99491] text-[#984B50] rounded-xl text-xs sm:text-sm flex items-center gap-2 animate-in fade-in"
    >
      <AlertCircle class="w-5 h-5 text-[#984B50] shrink-0" />
      <span>{{ errorMsg }}</span>
    </div>

    <div class="bg-[#FFFDF8] rounded-2xl border border-[#EAE3D7] p-6 sm:p-7 shadow-2xs">
      <form @submit.prevent="handleUpdateProfile" class="space-y-4 text-xs sm:text-sm">
        <div>
          <label class="block font-medium text-[#343630] mb-1.5 font-sans">
            Nama Pengguna (Username)
          </label>
          <input
            type="text"
            v-model="username"
            required
            class="w-full px-3.5 py-2.5 min-h-[44px] bg-[#F8F6F0] border border-[#EAE3D7] rounded-xl text-base sm:text-sm text-[#343630] focus:ring-1 focus:ring-[#984B50] focus:outline-none"
          />
        </div>

        <div class="pt-2 border-t border-[#EAE3D7]">
          <span class="text-xs font-semibold text-[#343630] uppercase tracking-wider block mb-3 font-sans">
            Ganti Kata Sandi
          </span>

          <div class="space-y-3">
            <div>
              <label class="block font-medium text-[#343630] mb-1.5 font-sans">
                Password Saat Ini <span class="text-[#984B50]">*</span>
              </label>
              <input
                type="password"
                v-model="currentPassword"
                placeholder="Masukkan password admin saat ini"
                required
                class="w-full px-3.5 py-2.5 min-h-[44px] bg-[#F8F6F0] border border-[#EAE3D7] rounded-xl text-base sm:text-sm text-[#343630] focus:ring-1 focus:ring-[#984B50] focus:outline-none"
              />
            </div>

            <div>
              <label class="block font-medium text-[#343630] mb-1.5 font-sans">
                Password Baru (Kosongkan jika tidak ingin mengganti)
              </label>
              <input
                type="password"
                v-model="newPassword"
                placeholder="Minimal 5 karakter"
                class="w-full px-3.5 py-2.5 min-h-[44px] bg-[#F8F6F0] border border-[#EAE3D7] rounded-xl text-base sm:text-sm text-[#343630] focus:ring-1 focus:ring-[#984B50] focus:outline-none"
              />
            </div>

            <div v-if="newPassword">
              <label class="block font-medium text-[#343630] mb-1.5 font-sans">
                Konfirmasi Password Baru <span class="text-[#984B50]">*</span>
              </label>
              <input
                type="password"
                v-model="confirmPassword"
                placeholder="Ketik ulang password baru"
                required
                class="w-full px-3.5 py-2.5 min-h-[44px] bg-[#F8F6F0] border border-[#EAE3D7] rounded-xl text-base sm:text-sm text-[#343630] focus:ring-1 focus:ring-[#984B50] focus:outline-none"
              />
            </div>
          </div>
        </div>

        <div class="pt-3 flex justify-end">
          <button
            type="submit"
            :disabled="isLoading"
            class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-2.5 min-h-[44px] bg-[#984B50] hover:bg-[#853f44] text-[#FFFDF8] font-medium rounded-xl shadow-xs transition-colors cursor-pointer disabled:opacity-50 text-xs sm:text-sm"
          >
            <KeyRound class="w-4 h-4" />
            <span>{{ isLoading ? 'Menyimpan...' : 'Simpan Perubahan' }}</span>
          </button>
        </div>
      </form>
    </div>

    <!-- System info plaque -->
    <div class="bg-[#FFFDF8] rounded-2xl border border-[#EAE3D7] p-5 text-xs text-[#85847D] space-y-2">
      <div class="flex items-center gap-2 font-serif font-bold text-[#343630]">
        <Shield class="w-4 h-4 text-[#77846B]" />
        <span>Informasi Sistem & Parameter Keamanan</span>
      </div>
      <p class="font-editorial italic">
        • <strong>Zona Waktu Terdaftar:</strong> Asia/Makassar (WITA, UTC+8) — {{ currentTime.formattedDateIndo }} pukul {{ currentTime.waktu }} WITA.
      </p>
      <p>
        • <strong>Autentikasi:</strong> Laravel Sanctum dengan token bearer yang aman dan proteksi otorisasi admin.
      </p>
      <p>
        • <strong>Database & Storage:</strong> Supabase PostgreSQL dengan foreign key, snapshot integritas histori, dan Supabase Storage untuk berkas paraf.
      </p>
    </div>
  </div>
</template>
