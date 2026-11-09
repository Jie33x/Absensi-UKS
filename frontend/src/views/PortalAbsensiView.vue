<script setup lang="ts">
import { ref, watch } from 'vue';
import { Send, AlertCircle } from 'lucide-vue-next';
import confetti from 'canvas-confetti';
import type { Anggota } from '../types';
import SignaturePad from '../components/SignaturePad.vue';
import MemberSearchInput from '../components/MemberSearchInput.vue';
import MemberIdentityBadge from '../components/MemberIdentityBadge.vue';
import AttendanceSuccessCard from '../components/AttendanceSuccessCard.vue';
import { useTimezone } from '../composables/useTimezone';
import { api } from '../services/api';

const { currentTime } = useTimezone();

// Search state
const searchQuery = ref('');
const searchResults = ref<Anggota[]>([]);
const isSearching = ref(false);
const showDropdown = ref(false);

// Selected member
const selectedMember = ref<Anggota | null>(null);

// Signature
const signatureData = ref<string | null>(null);

// Status checking & submission
const isCheckingToday = ref(false);
const alreadyAttendedInfo = ref<{
  alreadyAttended: boolean;
  waktu?: string;
  tanggal?: string;
  message?: string;
} | null>(null);

const isSubmitting = ref(false);
const submitSuccess = ref<{
  nama: string;
  waktu: string;
  tanggal: string;
  kelas: string;
  divisi: string;
} | null>(null);

const errorMessage = ref<string | null>(null);

// Debounced search
let debounceTimer: number | null = null;
watch(searchQuery, (query) => {
  if (debounceTimer) clearTimeout(debounceTimer);

  if (!query.trim()) {
    searchResults.value = [];
    showDropdown.value = false;
    return;
  }

  isSearching.value = true;
  debounceTimer = window.setTimeout(async () => {
    try {
      const results = await api.searchAnggota(query.trim());
      searchResults.value = results.slice(0, 5);
      showDropdown.value = true;
    } catch (err) {
      console.error('Failed to search members:', err);
    } finally {
      isSearching.value = false;
    }
  }, 120);
});

async function checkMemberAttendance(member: Anggota) {
  isCheckingToday.value = true;
  alreadyAttendedInfo.value = null;
  errorMessage.value = null;
  try {
    const data = await api.checkToday(member.id);
    if (data.alreadyAttended) {
      alreadyAttendedInfo.value = data;
    }
  } catch (err) {
    console.error('Failed to check attendance:', err);
  } finally {
    isCheckingToday.value = false;
  }
}

function handleSelectMember(member: Anggota) {
  selectedMember.value = member;
  searchQuery.value = '';
  showDropdown.value = false;
  signatureData.value = null;
  submitSuccess.value = null;
  checkMemberAttendance(member);
}

function handleResetForm() {
  selectedMember.value = null;
  searchQuery.value = '';
  searchResults.value = [];
  showDropdown.value = false;
  signatureData.value = null;
  alreadyAttendedInfo.value = null;
  errorMessage.value = null;
  submitSuccess.value = null;
}

async function handleSubmitAttendance() {
  if (!selectedMember.value) {
    errorMessage.value = 'Silakan pilih nama anggota terlebih dahulu.';
    return;
  }

  if (alreadyAttendedInfo.value?.alreadyAttended) {
    errorMessage.value = `Pemberitahuan: ${selectedMember.value.nama} sudah melakukan absensi hari ini pada pukul ${alreadyAttendedInfo.value.waktu} WITA.`;
    return;
  }

  if (!signatureData.value) {
    errorMessage.value = 'Tanda tangan digital wajib dibubuhkan sebelum mengirim absensi.';
    return;
  }

  isSubmitting.value = true;
  errorMessage.value = null;

  try {
    const data = await api.submitAbsensi({
      anggota_id: selectedMember.value.id,
      tanda_tangan: signatureData.value,
    });

    if (data.sudahAbsen) {
      alreadyAttendedInfo.value = {
        alreadyAttended: true,
        waktu: data.waktu,
        message: data.error,
      };
      errorMessage.value = data.error || 'Sudah melakukan absensi hari ini.';
      return;
    }

    if (!data.success) {
      throw new Error(data.error || 'Gagal mengirim absensi.');
    }

    // Elegant watercolor confetti
    try {
      confetti({
        particleCount: 60,
        spread: 55,
        origin: { y: 0.6 },
        colors: ['#984B50', '#C99491', '#A5B19B', '#77846B', '#F8F6F0'],
      });
    } catch {
      // Ignored
    }

    submitSuccess.value = {
      nama: data.absensi.nama,
      waktu: data.absensi.waktu,
      tanggal: data.absensi.tanggal,
      kelas: `${data.absensi.angkatan} ${data.absensi.kelas_jurusan}`,
      divisi: data.absensi.divisi,
    };

    selectedMember.value = null;
    signatureData.value = null;
    alreadyAttendedInfo.value = null;
  } catch (err: any) {
    errorMessage.value = err.message || 'Terjadi gangguan saat mengirim absensi.';
  } finally {
    isSubmitting.value = false;
  }
}
</script>

<template>
  <div class="max-w-2xl mx-auto px-4 py-8 sm:py-10">
    <!-- 1. HERO SECTION -->
    <div class="text-center mb-8">
      <span class="text-[11px] font-sans font-semibold tracking-widest uppercase text-[#77846B] block mb-1.5">
        Palang Merah Remaja
      </span>

      <h1 class="font-serif text-2xl sm:text-3xl font-bold text-[#343630] tracking-tight">
        ABSENSI ANGGOTA PMR
      </h1>
      <p class="mt-1.5 text-xs sm:text-sm text-[#85847D] font-sans">
        Silakan lakukan absensi hari ini.
      </p>
    </div>

    <!-- 2. SUCCESS FEEDBACK STATE -->
    <AttendanceSuccessCard
      v-if="submitSuccess"
      :success-data="submitSuccess"
      @done="handleResetForm"
    />

    <!-- 3. MAIN ATTENDANCE CARD -->
    <div
      v-else
      class="bg-[#FFFDF8] rounded-2xl border border-[#EAE3D7] shadow-xs p-6 sm:p-8 space-y-6"
    >
      <!-- A. SEARCH BAR -->
      <MemberSearchInput
        v-model="searchQuery"
        :is-searching="isSearching"
        :search-results="searchResults"
        :show-dropdown="showDropdown"
        @select="handleSelectMember"
        @focus="searchResults.length > 0 ? (showDropdown = true) : null"
        @close="showDropdown = false"
      />

      <!-- B. INFORMASI ANGGOTA TERPILIH -->
      <div
        v-if="selectedMember"
        class="pt-4 border-t border-[#EAE3D7] space-y-5 animate-in fade-in duration-150"
      >
        <MemberIdentityBadge
          :member="selectedMember"
          :current-time="currentTime"
          @change="handleResetForm"
        />

        <!-- Status Checking Indicator -->
        <div
          v-if="isCheckingToday"
          class="p-3 bg-[#FFFDF8] border border-[#EAE3D7] rounded-xl text-center text-xs text-[#85847D] flex items-center justify-center gap-2 font-editorial italic"
        >
          <div class="w-3.5 h-3.5 border-2 border-[#984B50] border-t-transparent rounded-full animate-spin"></div>
          <span>Memeriksa catatan kehadiran hari ini...</span>
        </div>

        <!-- Already Attended Warning -->
        <div
          v-if="alreadyAttendedInfo?.alreadyAttended"
          class="p-4 bg-[#FFFDF8] border border-[#C99491] rounded-xl text-[#343630] flex items-start gap-3"
        >
          <AlertCircle class="w-5 h-5 text-[#984B50] shrink-0 mt-0.5" />
          <div class="text-xs sm:text-sm">
            <h5 class="font-serif font-bold text-[#984B50]">
              Sudah Melakukan Absensi Hari Ini
            </h5>
            <p class="mt-1 text-[#85847D] leading-relaxed">
              {{ selectedMember.nama }} telah tercatat hadir pada hari ini pukul
              <strong class="text-[#343630] font-sans">{{ alreadyAttendedInfo.waktu }} WITA</strong>.
            </p>
            <button
              type="button"
              @click="handleResetForm"
              class="mt-3 inline-flex items-center gap-1 px-3 py-1.5 bg-[#EAE3D7] hover:bg-[#ded6c7] text-[#343630] text-xs font-medium rounded-lg transition-colors cursor-pointer"
            >
              Pilih Anggota Lain
            </button>
          </div>
        </div>

        <!-- C. AREA TANDA TANGAN (If not already attended) -->
        <div
          v-if="!alreadyAttendedInfo?.alreadyAttended"
          class="space-y-4 pt-2"
        >
          <SignaturePad
            v-model="signatureData"
            @save="(val) => {
              signatureData = val;
              if (val && errorMessage) errorMessage = null;
            }"
          />

          <div
            v-if="errorMessage"
            class="p-3 bg-[#FFFDF8] border border-[#C99491] rounded-xl text-[#984B50] text-xs flex items-center gap-2"
          >
            <AlertCircle class="w-4 h-4 shrink-0 text-[#984B50]" />
            <span>{{ errorMessage }}</span>
          </div>

          <!-- D. TOMBOL KIRIM ABSENSI -->
          <div class="pt-2">
            <button
              type="button"
              @click="handleSubmitAttendance"
              :disabled="isSubmitting || !signatureData"
              class="w-full py-3 bg-[#984B50] hover:bg-[#853f44] disabled:bg-[#D8D1C3] disabled:cursor-not-allowed text-[#FFFDF8] text-sm font-medium rounded-xl shadow-xs transition-all cursor-pointer flex items-center justify-center gap-2"
            >
              <template v-if="isSubmitting">
                <div class="w-4 h-4 border-2 border-[#FFFDF8] border-t-transparent rounded-full animate-spin"></div>
                <span>Mengirim Absensi...</span>
              </template>
              <template v-else>
                <Send class="w-4 h-4" />
                <span>Kirim Absensi</span>
              </template>
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Footer Info Note -->
    <div class="mt-8 text-center text-xs font-editorial italic text-[#85847D]">
      <p>Palang Merah Remaja Wira • Waktu Layanan: Asia/Makassar (WITA, UTC+8)</p>
    </div>
  </div>
</template>
