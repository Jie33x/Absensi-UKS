<script setup lang="ts">
import { ref, computed, onMounted, watch } from 'vue';
import {
  UserPlus,
  Search,
  Edit2,
  Trash2,
  X,
  CheckCircle2,
  Users,
  Layers,
  RotateCcw,
} from 'lucide-vue-next';
import {
  type Anggota,
  type Angkatan,
  type Divisi,
  DAFTAR_KELAS_JURUSAN,
  DAFTAR_DIVISI,
} from '../types';
import { useMemberStore } from '../stores/member';
import MemberFormModal from '../components/MemberFormModal.vue';
import DeleteConfirmModal from '../components/DeleteConfirmModal.vue';

const memberStore = useMemberStore();

const search = ref('');
const filterAngkatan = ref('');
const filterKelas = ref('');
const filterDivisi = ref('');

const isAddModalOpen = ref(false);
const editingMember = ref<Anggota | null>(null);
const deletingMember = ref<Anggota | null>(null);
const actionSuccessMsg = ref<string | null>(null);

async function loadMembers() {
  await memberStore.fetchMembers({
    search: search.value,
    angkatan: filterAngkatan.value,
    kelas: filterKelas.value,
    divisi: filterDivisi.value,
  });
}

onMounted(() => {
  loadMembers();
});

watch([search, filterAngkatan, filterKelas, filterDivisi], () => {
  loadMembers();
});

function handleOpenAddModal() {
  editingMember.value = null;
  isAddModalOpen.value = true;
}

function handleOpenEditModal(member: Anggota) {
  editingMember.value = member;
  isAddModalOpen.value = true;
}

function handleCloseModal() {
  isAddModalOpen.value = false;
  editingMember.value = null;
}

async function handleSaveMember(payload: { nama: string; angkatan: Angkatan; kelas_jurusan: string; divisi: Divisi }) {
  try {
    if (editingMember.value) {
      await memberStore.updateMember(editingMember.value.id, payload);
      actionSuccessMsg.value = 'Data anggota berhasil diperbarui.';
    } else {
      await memberStore.addMember(payload);
      actionSuccessMsg.value = 'Anggota baru berhasil dicatat.';
    }

    setTimeout(() => {
      actionSuccessMsg.value = null;
    }, 3000);
    handleCloseModal();
  } catch (err: any) {
    alert(err.message || 'Terjadi kesalahan saat menyimpan data');
  }
}

async function handleDeleteMember() {
  if (!deletingMember.value) return;

  try {
    const res = await memberStore.deleteMember(deletingMember.value.id);
    actionSuccessMsg.value = res.message || 'Data anggota berhasil dihapus.';
    setTimeout(() => {
      actionSuccessMsg.value = null;
    }, 3000);
    deletingMember.value = null;
  } catch (err: any) {
    alert(err.message || 'Terjadi kesalahan');
  }
}

function handleResetFilters() {
  search.value = '';
  filterAngkatan.value = '';
  filterKelas.value = '';
  filterDivisi.value = '';
}

const hasActiveFilters = computed(() => {
  return Boolean(search.value || filterAngkatan.value || filterKelas.value || filterDivisi.value);
});
</script>

<template>
  <div class="space-y-4 sm:space-y-5">
    <!-- 1. TOP SECTION -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-[#FFFDF8] sm:bg-transparent p-4 sm:p-0 rounded-2xl border sm:border-0 border-[#EAE3D7]">
      <div>
        <div class="flex items-center gap-2">
          <h2 class="font-serif text-xl sm:text-3xl font-bold text-[#343630] tracking-tight">
            Data Anggota
          </h2>
          <span class="text-xs font-semibold px-2 py-0.5 rounded-full bg-[#EAE3D7] text-[#77846B]">
            Total: {{ memberStore.members.length }} Anggota
          </span>
        </div>
        <p class="text-xs text-[#85847D] mt-0.5 font-sans">
          Daftar lengkap anggota resmi Palang Merah Remaja Wira.
        </p>
      </div>

      <!-- Prominent Tambah Anggota Button -->
      <button
        type="button"
        @click="handleOpenAddModal"
        class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-4 py-2.5 min-h-[44px] bg-[#984B50] hover:bg-[#853f44] text-[#FFFDF8] text-xs sm:text-sm font-medium rounded-xl shadow-xs transition-colors cursor-pointer"
      >
        <UserPlus class="w-4 h-4 shrink-0" />
        <span>Tambah Anggota</span>
      </button>
    </div>

    <div
      v-if="actionSuccessMsg"
      class="p-3 bg-[#FFFDF8] border border-[#A5B19B] text-[#77846B] rounded-xl text-xs sm:text-sm flex items-center gap-2"
    >
      <CheckCircle2 class="w-4 h-4 shrink-0 text-[#77846B]" />
      <span>{{ actionSuccessMsg }}</span>
    </div>

    <!-- 2. SEARCH & QUICK FILTERS -->
    <div class="bg-[#FFFDF8] rounded-2xl border border-[#EAE3D7] p-3.5 sm:p-4 shadow-2xs space-y-3">
      <!-- Search Input -->
      <div class="relative">
        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-[#85847D]">
          <Search class="w-4 h-4 text-[#984B50]" />
        </div>
        <input
          type="text"
          v-model="search"
          placeholder="Cari nama anggota..."
          class="w-full pl-10 pr-10 py-2.5 min-h-[44px] bg-[#F8F6F0] border border-[#EAE3D7] rounded-xl text-base sm:text-xs text-[#343630] placeholder:text-[#85847D]/60 focus:ring-1 focus:ring-[#984B50] focus:outline-none transition-all"
        />
        <button
          v-if="search"
          type="button"
          @click="search = ''"
          class="absolute inset-y-0 right-0 pr-3 min-w-[40px] flex items-center justify-center text-[#85847D] hover:text-[#343630]"
          aria-label="Hapus kata kunci pencarian"
        >
          <X class="w-4 h-4" />
        </button>
      </div>

      <!-- Filters Row -->
      <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5 pt-1 text-xs">
        <!-- Angkatan Filter -->
        <div class="flex items-center gap-1.5">
          <span class="text-[#85847D] font-sans font-medium shrink-0 text-[11px] sm:text-xs">
            Angkatan:
          </span>
          <select
            v-model="filterAngkatan"
            class="w-full px-2.5 py-2 min-h-[42px] sm:min-h-[36px] bg-[#F8F6F0] border border-[#EAE3D7] rounded-xl text-base sm:text-xs text-[#343630] focus:ring-1 focus:ring-[#984B50] focus:outline-none"
          >
            <option value="">Semua Angkatan</option>
            <option value="XI">Kelas 11 (XI)</option>
            <option value="X">Kelas 10 (X)</option>
          </select>
        </div>

        <!-- Kelas Filter -->
        <div class="flex items-center gap-1.5">
          <span class="text-[#85847D] font-sans font-medium shrink-0 text-[11px] sm:text-xs">
            Kelas:
          </span>
          <select
            v-model="filterKelas"
            class="w-full px-2.5 py-2 min-h-[42px] sm:min-h-[36px] bg-[#F8F6F0] border border-[#EAE3D7] rounded-xl text-base sm:text-xs text-[#343630] focus:ring-1 focus:ring-[#984B50] focus:outline-none"
          >
            <option value="">Semua Kelas</option>
            <option v-for="k in DAFTAR_KELAS_JURUSAN" :key="k" :value="k">
              {{ k }}
            </option>
          </select>
        </div>

        <!-- Divisi Filter -->
        <div class="flex items-center gap-1.5">
          <span class="text-[#85847D] font-sans font-medium shrink-0 text-[11px] sm:text-xs">
            Divisi:
          </span>
          <select
            v-model="filterDivisi"
            class="w-full px-2.5 py-2 min-h-[42px] sm:min-h-[36px] bg-[#F8F6F0] border border-[#EAE3D7] rounded-xl text-base sm:text-xs text-[#343630] focus:ring-1 focus:ring-[#984B50] focus:outline-none"
          >
            <option value="">Semua Divisi</option>
            <option v-for="d in DAFTAR_DIVISI" :key="d" :value="d">
              Divisi {{ d }}
            </option>
          </select>
        </div>
      </div>

      <!-- Reset Filter Button -->
      <div v-if="hasActiveFilters" class="flex justify-end pt-1">
        <button
          type="button"
          @click="handleResetFilters"
          class="inline-flex items-center gap-1 text-xs font-editorial italic text-[#984B50] hover:underline cursor-pointer"
        >
          <RotateCcw class="w-3 h-3" />
          <span>Reset Semua Filter</span>
        </button>
      </div>
    </div>

    <!-- 3. MEMBER LIST (MOBILE CARDS + DESKTOP TABLE) -->
    <div
      v-if="memberStore.loading"
      class="bg-[#FFFDF8] rounded-2xl border border-[#EAE3D7] p-12 text-center text-[#85847D] space-y-2 font-editorial italic shadow-2xs"
    >
      <div class="w-6 h-6 border-2 border-[#984B50] border-t-transparent rounded-full animate-spin mx-auto"></div>
      <p class="text-xs">Memuat data keanggotaan...</p>
    </div>

    <div
      v-else-if="memberStore.error"
      class="bg-[#FFFDF8] rounded-2xl border border-[#EAE3D7] p-8 text-center text-[#984B50] space-y-2 shadow-2xs"
    >
      <p class="text-xs sm:text-sm font-medium">{{ memberStore.error }}</p>
      <button
        type="button"
        @click="loadMembers"
        class="px-4 py-2 bg-[#EAE3D7] text-[#343630] text-xs font-medium rounded-xl cursor-pointer"
      >
        Coba Lagi
      </button>
    </div>

    <div
      v-else-if="memberStore.members.length === 0"
      class="bg-[#FFFDF8] rounded-2xl border border-[#EAE3D7] p-10 text-center text-[#85847D] space-y-2 shadow-2xs"
    >
      <Users class="w-8 h-8 mx-auto text-[#D8D1C3]" />
      <h4 class="font-serif text-base font-bold text-[#343630]">
        {{ hasActiveFilters ? 'Anggota tidak ditemukan.' : 'Belum ada anggota terdaftar. Tambahkan anggota untuk memulai.' }}
      </h4>
      <p class="text-xs text-[#85847D]">
        {{ hasActiveFilters
          ? 'Periksa kembali kata kunci pencarian atau kombinasi filter.'
          : 'Gunakan tombol Tambah Anggota di atas untuk mendaftarkan anggota baru.' }}
      </p>
    </div>

    <div v-else class="space-y-3">
      <!-- A. MOBILE MEMBER CARDS (< md) -->
      <div class="md:hidden space-y-3">
        <div
          v-for="(m, index) in memberStore.members"
          :key="m.id"
          class="bg-[#FFFDF8] rounded-2xl border border-[#EAE3D7] p-4 shadow-2xs space-y-3 transition-shadow hover:shadow-xs"
        >
          <div class="flex items-start justify-between gap-2 border-b border-[#EAE3D7]/60 pb-2.5">
            <div class="flex items-start gap-2.5">
              <span class="w-6 h-6 rounded-full bg-[#F8F6F0] border border-[#EAE3D7] text-[#85847D] text-[11px] font-mono flex items-center justify-center shrink-0 mt-0.5">
                {{ index + 1 }}
              </span>
              <div>
                <h4 class="font-serif font-bold text-base text-[#343630] leading-tight">
                  {{ m.nama }}
                </h4>
                <p class="text-xs text-[#85847D] font-medium mt-0.5">
                  Kelas: <strong class="text-[#343630]">{{ m.angkatan }} {{ m.kelas_jurusan }}</strong>
                </p>
              </div>
            </div>

            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-semibold tracking-wide bg-[#A5B19B]/20 text-[#77846B] border border-[#A5B19B]/30 shrink-0">
              {{ m.divisi }}
            </span>
          </div>

          <div class="flex items-center justify-between gap-2 pt-0.5 text-xs">
            <span class="inline-flex items-center gap-1 text-[11px] font-medium text-[#85847D] bg-[#F8F6F0] px-2.5 py-1 rounded-lg border border-[#EAE3D7]">
              <Layers class="w-3 h-3 text-[#77846B]" />
              <span>Angkatan {{ m.angkatan === 'XI' ? '11 (XI)' : '10 (X)' }}</span>
            </span>

            <div class="flex items-center gap-1">
              <button
                type="button"
                @click="handleOpenEditModal(m)"
                class="inline-flex items-center gap-1 px-3 py-2 min-h-[40px] text-xs font-medium text-[#77846B] hover:text-[#5e6954] hover:bg-[#F8F6F0] rounded-xl transition-colors cursor-pointer"
                title="Edit Anggota"
              >
                <Edit2 class="w-3.5 h-3.5" />
                <span>Edit</span>
              </button>

              <button
                type="button"
                @click="deletingMember = m"
                class="inline-flex items-center gap-1 px-3 py-2 min-h-[40px] text-xs font-medium text-[#984B50] hover:text-[#853f44] hover:bg-[#F8F6F0] rounded-xl transition-colors cursor-pointer"
                title="Hapus Anggota"
              >
                <Trash2 class="w-3.5 h-3.5" />
                <span>Hapus</span>
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- B. DESKTOP FULL TABLE (>= md) -->
      <div class="hidden md:block bg-[#FFFDF8] rounded-2xl border border-[#EAE3D7] shadow-2xs overflow-hidden">
        <div class="overflow-x-auto">
          <table class="w-full text-left text-xs border-collapse">
            <thead>
              <tr class="bg-[#F8F6F0] border-b border-[#EAE3D7] text-[#85847D] uppercase font-sans font-semibold text-[10px] tracking-wider">
                <th class="py-3 px-3.5 w-10 text-center">No</th>
                <th class="py-3 px-3.5">Nama Lengkap</th>
                <th class="py-3 px-3.5">Angkatan</th>
                <th class="py-3 px-3.5">Kelas</th>
                <th class="py-3 px-3.5">Divisi</th>
                <th class="py-3 px-3.5 text-center w-20">Aksi</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-[#EAE3D7]/60">
              <tr
                v-for="(m, index) in memberStore.members"
                :key="m.id"
                class="hover:bg-[#F8F6F0]/70 transition-colors group"
              >
                <td class="py-2.5 px-3.5 text-center font-mono text-[#85847D] tabular-nums">
                  {{ index + 1 }}
                </td>
                <td class="py-2.5 px-3.5">
                  <span class="font-serif font-bold text-[#343630] group-hover:text-[#984B50] block text-sm">
                    {{ m.nama }}
                  </span>
                </td>
                <td class="py-2.5 px-3.5">
                  <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium bg-[#F8F6F0] text-[#343630] border border-[#EAE3D7]">
                    {{ m.angkatan === 'XI' ? 'Kelas 11 (XI)' : 'Kelas 10 (X)' }}
                  </span>
                </td>
                <td class="py-2.5 px-3.5 font-medium text-[#343630]">
                  {{ m.angkatan }} {{ m.kelas_jurusan }}
                </td>
                <td class="py-2.5 px-3.5">
                  <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-medium bg-[#A5B19B]/20 text-[#77846B] border border-[#A5B19B]/30">
                    {{ m.divisi }}
                  </span>
                </td>
                <td class="py-2.5 px-3.5 text-center">
                  <div class="flex items-center justify-center gap-1.5">
                    <button
                      type="button"
                      @click="handleOpenEditModal(m)"
                      title="Edit Anggota"
                      class="p-1.5 text-[#85847D] hover:text-[#77846B] hover:bg-[#F8F6F0] rounded-lg transition-colors cursor-pointer"
                    >
                      <Edit2 class="w-3.5 h-3.5" />
                    </button>
                    <button
                      type="button"
                      @click="deletingMember = m"
                      title="Hapus Anggota"
                      class="p-1.5 text-[#85847D] hover:text-[#984B50] hover:bg-[#F8F6F0] rounded-lg transition-colors cursor-pointer"
                    >
                      <Trash2 class="w-3.5 h-3.5" />
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- 4. MODAL / BOTTOM SHEET: TAMBAH & EDIT ANGGOTA -->
    <MemberFormModal
      :is-open="isAddModalOpen"
      :editing-member="editingMember"
      @close="handleCloseModal"
      @save="handleSaveMember"
    />

    <!-- 5. MODAL: HAPUS ANGGOTA -->
    <DeleteConfirmModal
      :is-open="!!deletingMember"
      title="Hapus Data Anggota Ini?"
      :message="`Profil anggota ${deletingMember?.nama || ''} (${deletingMember?.angkatan || ''} ${deletingMember?.kelas_jurusan || ''}) akan dihapus.`"
      note="Catatan: Riwayat presensi lampau dari anggota ini tetap aman tersimpan dalam Rekap Absensi."
      confirm-label="Hapus"
      @close="deletingMember = null"
      @confirm="handleDeleteMember"
    />
  </div>
</template>
