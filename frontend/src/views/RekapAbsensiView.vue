<script setup lang="ts">
import { ref, computed, onMounted, watch } from 'vue';
import {
  FileSpreadsheet,
  FileText,
  Eye,
  Edit2,
  Trash2,
  Clock,
  CheckCircle2,
  ChevronLeft,
  ChevronRight,
  RotateCcw,
} from 'lucide-vue-next';
import { jsPDF } from 'jspdf';
import autoTable from 'jspdf-autotable';
import * as XLSX from 'xlsx';
import type { Absensi } from '../types';
import { useTimezone } from '../composables/useTimezone';
import { useAttendanceStore } from '../stores/attendance';

import AdminDashboardStats from '../components/AdminDashboardStats.vue';
import AttendanceFilterBar from '../components/AttendanceFilterBar.vue';
import SignaturePreviewModal from '../components/SignaturePreviewModal.vue';
import AttendanceDetailModal from '../components/AttendanceDetailModal.vue';
import AttendanceEditModal from '../components/AttendanceEditModal.vue';
import DeleteConfirmModal from '../components/DeleteConfirmModal.vue';

const attendanceStore = useAttendanceStore();
const { getMakassarDateTime, formatIndoDate } = useTimezone();

const EMPTY_BOTANICAL_IMAGE = '/assets/images/botanical_empty_state_1794136771538.jpg';

const BULAN_OPTIONS = [
  { value: '1', label: 'Januari' },
  { value: '2', label: 'Februari' },
  { value: '3', label: 'Maret' },
  { value: '4', label: 'April' },
  { value: '5', label: 'Mei' },
  { value: '6', label: 'Juni' },
  { value: '7', label: 'Juli' },
  { value: '8', label: 'Agustus' },
  { value: '9', label: 'September' },
  { value: '10', label: 'Oktober' },
  { value: '11', label: 'November' },
  { value: '12', label: 'Desember' },
];

const ITEMS_PER_PAGE = 10;
const currentMakassar = getMakassarDateTime();

// Period Filters
const filterBulan = ref<string>(String(currentMakassar.month));
const filterTahun = ref<string>(String(currentMakassar.year));
const filterAngkatan = ref<string>('');
const filterKelas = ref<string>('');
const filterDivisi = ref<string>('');
const searchName = ref<string>('');

// Pagination
const currentPage = ref<number>(1);

// Modals
const selectedSignature = ref<{
  imageUrl: string;
  nama: string;
  tanggal: string;
  waktu: string;
} | null>(null);

const detailModalRecord = ref<Absensi | null>(null);
const editModalRecord = ref<Absensi | null>(null);
const deleteConfirmRecord = ref<Absensi | null>(null);
const actionSuccessMsg = ref<string | null>(null);

async function loadRecords() {
  await attendanceStore.fetchRecords({
    bulan: filterBulan.value,
    tahun: filterTahun.value,
    angkatan: filterAngkatan.value,
    kelas_jurusan: filterKelas.value,
    divisi: filterDivisi.value,
    search: searchName.value,
    sort_by: 'tanggal',
    sort_order: 'desc',
  });
  await attendanceStore.fetchStatistik();
  currentPage.value = 1;
}

onMounted(() => {
  loadRecords();
});

watch([filterBulan, filterTahun, filterAngkatan, filterKelas, filterDivisi, searchName], () => {
  loadRecords();
});

function handleResetFilter() {
  filterBulan.value = '';
  filterTahun.value = '';
  filterAngkatan.value = '';
  filterKelas.value = '';
  filterDivisi.value = '';
  searchName.value = '';
  currentPage.value = 1;
}

// Active chips
const activeChips = computed(() => {
  const list: { id: string; label: string; onClear: () => void }[] = [];
  if (filterBulan.value) {
    const bLabel = BULAN_OPTIONS.find((b) => b.value === filterBulan.value)?.label || filterBulan.value;
    list.push({ id: 'bulan', label: `Bulan: ${bLabel}`, onClear: () => (filterBulan.value = '') });
  }
  if (filterTahun.value) {
    list.push({ id: 'tahun', label: `Tahun: ${filterTahun.value}`, onClear: () => (filterTahun.value = '') });
  }
  if (filterAngkatan.value) {
    list.push({ id: 'angkatan', label: `Angkatan ${filterAngkatan.value}`, onClear: () => (filterAngkatan.value = '') });
  }
  if (filterKelas.value) {
    list.push({ id: 'kelas', label: `Kelas: ${filterKelas.value}`, onClear: () => (filterKelas.value = '') });
  }
  if (filterDivisi.value) {
    list.push({ id: 'divisi', label: `Divisi: ${filterDivisi.value}`, onClear: () => (filterDivisi.value = '') });
  }
  if (searchName.value.trim()) {
    list.push({ id: 'search', label: `"${searchName.value.trim()}"`, onClear: () => (searchName.value = '') });
  }
  return list;
});

const totalPages = computed(() => Math.ceil(attendanceStore.records.length / ITEMS_PER_PAGE) || 1);

const paginatedRecords = computed(() => {
  const start = (currentPage.value - 1) * ITEMS_PER_PAGE;
  return attendanceStore.records.slice(start, start + ITEMS_PER_PAGE);
});

function handleExportExcel() {
  const excelData = attendanceStore.records.map((r, idx) => ({
    No: idx + 1,
    'Nama Lengkap': r.nama_snapshot,
    'Angkatan': `Kelas ${r.angkatan_snapshot === 'XI' ? '11 (XI)' : '10 (X)'}`,
    'Kelas / Jurusan': `${r.angkatan_snapshot} ${r.kelas_jurusan_snapshot}`,
    'Divisi': r.divisi_snapshot,
    'Tanggal': r.tanggal,
    'Waktu (WITA)': `${r.waktu} WITA`,
    'Status Tanda Tangan': r.tanda_tangan ? 'Ada (Digital)' : 'Tidak Ada',
  }));

  const worksheet = XLSX.utils.json_to_sheet(excelData);
  worksheet['!cols'] = [
    { wch: 6 },
    { wch: 28 },
    { wch: 18 },
    { wch: 20 },
    { wch: 18 },
    { wch: 14 },
    { wch: 16 },
    { wch: 22 },
  ];

  const workbook = XLSX.utils.book_new();
  XLSX.utils.book_append_sheet(workbook, worksheet, 'Rekap Absensi PMR');
  XLSX.writeFile(workbook, `Rekap_Absensi_PMR_${currentMakassar.tanggal}.xlsx`);
}

function handleExportPDF() {
  const doc = new jsPDF({
    orientation: 'landscape',
    unit: 'pt',
    format: 'a4',
  });

  doc.setFillColor(152, 75, 80);
  doc.rect(0, 0, 842, 45, 'F');

  doc.setTextColor(255, 253, 248);
  doc.setFontSize(15);
  doc.setFont('helvetica', 'bold');
  doc.text('PALANG MERAH REMAJA (PMR) WIRA', 40, 28);

  doc.setFontSize(9);
  doc.setFont('helvetica', 'normal');
  doc.text('BUKU INDUK REKAPITULASI PRESENSI ANGGOTA', 570, 28);

  doc.setTextColor(52, 54, 48);
  doc.setFontSize(12);
  doc.setFont('helvetica', 'bold');
  const bulanLabel = filterBulan.value ? BULAN_OPTIONS.find((b) => b.value === filterBulan.value)?.label : 'Semua Bulan';
  const tahunLabel = filterTahun.value || 'Semua Tahun';
  const angkatanLabel = filterAngkatan.value ? `Angkatan ${filterAngkatan.value === 'XI' ? 'Kelas 11 (XI)' : 'Kelas 10 (X)'}` : 'Semua Angkatan';
  doc.text(`Periode: ${bulanLabel} ${tahunLabel} | ${angkatanLabel} ${filterDivisi.value ? `| Divisi: ${filterDivisi.value}` : ''}`, 40, 70);

  doc.setFontSize(9);
  doc.setFont('helvetica', 'italic');
  doc.setTextColor(133, 132, 125);
  doc.text(`Dicetak pada: ${currentMakassar.formattedDateIndo} pukul ${currentMakassar.waktu} WITA (Asia/Makassar)`, 40, 85);

  const tableBody = attendanceStore.records.map((r, i) => [
    i + 1,
    r.nama_snapshot,
    `${r.angkatan_snapshot} ${r.kelas_jurusan_snapshot}`,
    r.angkatan_snapshot === 'XI' ? 'Kelas 11' : 'Kelas 10',
    r.divisi_snapshot,
    r.tanggal,
    `${r.waktu} WITA`,
    r.tanda_tangan ? 'Terverifikasi (Digital)' : 'Belum Ada',
  ]);

  autoTable(doc, {
    startY: 95,
    head: [['No', 'Nama Anggota', 'Kelas / Jurusan', 'Angkatan', 'Divisi', 'Tanggal', 'Jam Hadir', 'Tanda Tangan']],
    body: tableBody,
    theme: 'grid',
    headStyles: {
      fillColor: [152, 75, 80],
      textColor: 255,
      fontStyle: 'bold',
      fontSize: 9,
    },
    styles: {
      fontSize: 8.5,
      cellPadding: 5,
    },
    alternateRowStyles: {
      fillColor: [248, 246, 240],
    },
  });

  doc.save(`Rekap_Absensi_PMR_${currentMakassar.tanggal}.pdf`);
}

async function handleUpdateRecord(updatedData: Partial<Absensi>) {
  if (!editModalRecord.value) return;
  try {
    await attendanceStore.updateRecord(editModalRecord.value.id, updatedData);
    actionSuccessMsg.value = 'Data absensi berhasil diperbarui.';
    setTimeout(() => (actionSuccessMsg.value = null), 3000);
    editModalRecord.value = null;
  } catch (err: any) {
    alert(err.message || 'Gagal memperbarui data');
  }
}

async function handleDeleteRecord() {
  if (!deleteConfirmRecord.value) return;
  try {
    await attendanceStore.deleteRecord(deleteConfirmRecord.value.id);
    actionSuccessMsg.value = 'Data absensi berhasil dihapus.';
    setTimeout(() => (actionSuccessMsg.value = null), 3000);
    deleteConfirmRecord.value = null;
  } catch (err: any) {
    alert(err.message || 'Gagal menghapus data');
  }
}
</script>

<template>
  <div class="space-y-4 sm:space-y-5">
    <!-- 0. ADMIN STATS OVERVIEW -->
    <AdminDashboardStats :stats="attendanceStore.statistik" />

    <!-- 1. HEADER SECTION -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-[#FFFDF8] sm:bg-transparent p-4 sm:p-0 rounded-2xl border sm:border-0 border-[#EAE3D7]">
      <div>
        <div class="flex items-center gap-2">
          <h2 class="font-serif text-xl sm:text-3xl font-bold text-[#343630] tracking-tight">
            Rekap Absensi PMR
          </h2>
          <span class="text-xs font-semibold px-2 py-0.5 rounded-full bg-[#EAE3D7] text-[#77846B]">
            Total: {{ attendanceStore.records.length }} Kehadiran
          </span>
        </div>
        <p class="text-xs text-[#85847D] mt-0.5 font-sans">
          Buku induk arsip presensi resmi bertanda tangan digital.
        </p>
      </div>

      <!-- Export Buttons -->
      <div class="flex items-center gap-2">
        <button
          type="button"
          @click="handleExportExcel"
          class="inline-flex items-center gap-1.5 px-3 py-2 min-h-[40px] text-xs font-medium text-[#77846B] bg-[#FFFDF8] hover:bg-[#EAE3D7]/50 border border-[#EAE3D7] rounded-xl shadow-2xs transition-colors cursor-pointer"
          title="Ekspor format Microsoft Excel"
        >
          <FileSpreadsheet class="w-4 h-4 text-[#77846B]" />
          <span>Ekspor Excel</span>
        </button>

        <button
          type="button"
          @click="handleExportPDF"
          class="inline-flex items-center gap-1.5 px-3 py-2 min-h-[40px] text-xs font-medium text-[#984B50] bg-[#FFFDF8] hover:bg-[#EAE3D7]/50 border border-[#EAE3D7] rounded-xl shadow-2xs transition-colors cursor-pointer"
          title="Ekspor dokumen PDF cetak"
        >
          <FileText class="w-4 h-4 text-[#984B50]" />
          <span>Ekspor PDF</span>
        </button>
      </div>
    </div>

    <!-- Alert / Toast -->
    <div
      v-if="actionSuccessMsg"
      class="p-3 bg-[#FFFDF8] border border-[#A5B19B] text-[#77846B] rounded-xl text-xs sm:text-sm flex items-center gap-2"
    >
      <CheckCircle2 class="w-4 h-4 shrink-0 text-[#77846B]" />
      <span>{{ actionSuccessMsg }}</span>
    </div>

    <!-- 2. FILTER COMPONENT -->
    <AttendanceFilterBar
      :bulan="filterBulan"
      :tahun="filterTahun"
      :angkatan="filterAngkatan"
      :kelas="filterKelas"
      :divisi="filterDivisi"
      :search="searchName"
      :active-chips="activeChips"
      :bulan-options="BULAN_OPTIONS"
      @update:bulan="filterBulan = $event"
      @update:tahun="filterTahun = $event"
      @update:angkatan="filterAngkatan = $event"
      @update:kelas="filterKelas = $event"
      @update:divisi="filterDivisi = $event"
      @update:search="searchName = $event"
      @reset="handleResetFilter"
    />

    <!-- 3. RECORDS LIST -->
    <div
      v-if="attendanceStore.loading"
      class="bg-[#FFFDF8] rounded-2xl border border-[#EAE3D7] p-12 text-center text-[#85847D] space-y-2 font-editorial italic shadow-2xs"
    >
      <div class="w-6 h-6 border-2 border-[#984B50] border-t-transparent rounded-full animate-spin mx-auto"></div>
      <p class="text-xs">Memuat arsip presensi...</p>
    </div>

    <div
      v-else-if="attendanceStore.records.length === 0"
      class="bg-[#FFFDF8] rounded-3xl border border-[#EAE3D7] p-8 sm:p-12 text-center shadow-2xs space-y-4 max-w-xl mx-auto"
    >
      <img
        :src="EMPTY_BOTANICAL_IMAGE"
        alt="Botanical illustration"
        class="w-36 h-36 mx-auto object-cover rounded-2xl border border-[#EAE3D7] shadow-xs"
      />
      <div>
        <h4 class="font-serif text-lg font-bold text-[#343630]">
          Belum Ada Catatan Kehadiran
        </h4>
        <p class="text-xs text-[#85847D] mt-1 font-editorial italic">
          Tidak ditemukan data presensi untuk kombinasi filter ini. Ubah periode atau reset filter untuk menampilkan catatan lain.
        </p>
      </div>
      <button
        type="button"
        @click="handleResetFilter"
        class="inline-flex items-center gap-1.5 px-4 py-2 bg-[#EAE3D7] hover:bg-[#ded6c7] text-[#343630] text-xs font-medium rounded-xl transition-colors cursor-pointer"
      >
        <RotateCcw class="w-3.5 h-3.5" />
        <span>Tampilkan Seluruh Arsip</span>
      </button>
    </div>

    <div v-else class="space-y-3">
      <!-- A. MOBILE VIEW (< md) -->
      <div class="md:hidden space-y-3">
        <div
          v-for="(r, index) in paginatedRecords"
          :key="r.id"
          class="bg-[#FFFDF8] rounded-2xl border border-[#EAE3D7] p-4 shadow-2xs space-y-2.5"
        >
          <div class="flex items-start justify-between gap-2 border-b border-[#EAE3D7]/60 pb-2">
            <div>
              <span class="text-[10px] font-sans font-semibold uppercase tracking-wider text-[#77846B]">
                {{ r.divisi_snapshot }}
              </span>
              <h4 class="font-serif font-bold text-base text-[#343630] leading-tight mt-0.5">
                {{ r.nama_snapshot }}
              </h4>
              <p class="text-xs text-[#85847D] font-sans mt-0.5">
                {{ r.angkatan_snapshot }} {{ r.kelas_jurusan_snapshot }} • Angkatan {{ r.angkatan_snapshot }}
              </p>
            </div>

            <span class="w-6 h-6 rounded-full bg-[#F8F6F0] border border-[#EAE3D7] text-[#85847D] text-[11px] font-mono flex items-center justify-center shrink-0">
              {{ (currentPage - 1) * ITEMS_PER_PAGE + index + 1 }}
            </span>
          </div>

          <div class="flex items-center justify-between gap-2 text-xs">
            <div class="flex items-center gap-2 text-[#85847D]">
              <span class="flex items-center gap-1 font-medium text-[#343630]">
                <Clock class="w-3.5 h-3.5 text-[#984B50]" />
                <span>{{ r.waktu }} WITA</span>
              </span>
              <span>•</span>
              <span>{{ formatIndoDate(r.tanggal) }}</span>
            </div>

            <button
              v-if="r.tanda_tangan"
              type="button"
              @click="selectedSignature = { imageUrl: r.tanda_tangan, nama: r.nama_snapshot, tanggal: r.tanggal, waktu: r.waktu }"
              class="inline-flex items-center gap-1 text-[11px] font-medium text-[#77846B] hover:text-[#5e6954] bg-[#A5B19B]/20 border border-[#A5B19B]/40 px-2 py-1 rounded-lg cursor-pointer"
            >
              <Eye class="w-3 h-3" />
              <span>Paraf</span>
            </button>
          </div>

          <div class="pt-1.5 border-t border-[#EAE3D7]/60 flex items-center justify-end gap-1.5 text-xs">
            <button
              type="button"
              @click="detailModalRecord = r"
              class="px-2.5 py-1 text-xs text-[#77846B] hover:bg-[#F8F6F0] rounded-lg cursor-pointer"
            >
              Detail
            </button>
            <button
              type="button"
              @click="editModalRecord = { ...r }"
              class="px-2.5 py-1 text-xs text-[#343630] hover:bg-[#F8F6F0] rounded-lg cursor-pointer"
            >
              Edit
            </button>
            <button
              type="button"
              @click="deleteConfirmRecord = r"
              class="px-2.5 py-1 text-xs text-[#984B50] hover:bg-[#F8F6F0] rounded-lg cursor-pointer"
            >
              Hapus
            </button>
          </div>
        </div>
      </div>

      <!-- B. DESKTOP VIEW (>= md) -->
      <div class="hidden md:block bg-[#FFFDF8] rounded-2xl border border-[#EAE3D7] shadow-2xs overflow-hidden">
        <div class="overflow-x-auto">
          <table class="w-full text-left text-xs border-collapse">
            <thead>
              <tr class="bg-[#F8F6F0] border-b border-[#EAE3D7] text-[#85847D] uppercase font-sans font-semibold text-[10px] tracking-wider">
                <th class="py-3 px-3.5 w-10 text-center">No</th>
                <th class="py-3 px-3.5">Nama Anggota</th>
                <th class="py-3 px-3.5">Kelas / Jurusan</th>
                <th class="py-3 px-3.5">Angkatan</th>
                <th class="py-3 px-3.5">Divisi</th>
                <th class="py-3 px-3.5">Tanggal</th>
                <th class="py-3 px-3.5">Waktu Hadir</th>
                <th class="py-3 px-3.5 text-center">Tanda Tangan</th>
                <th class="py-3 px-3.5 text-center w-24">Aksi</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-[#EAE3D7]/60">
              <tr
                v-for="(r, index) in paginatedRecords"
                :key="r.id"
                class="hover:bg-[#F8F6F0]/70 transition-colors group"
              >
                <td class="py-2.5 px-3.5 text-center font-mono text-[#85847D] tabular-nums">
                  {{ (currentPage - 1) * ITEMS_PER_PAGE + index + 1 }}
                </td>
                <td class="py-2.5 px-3.5">
                  <span class="font-serif font-bold text-[#343630] group-hover:text-[#984B50] block text-sm">
                    {{ r.nama_snapshot }}
                  </span>
                </td>
                <td class="py-2.5 px-3.5 font-medium text-[#343630]">
                  {{ r.angkatan_snapshot }} {{ r.kelas_jurusan_snapshot }}
                </td>
                <td class="py-2.5 px-3.5">
                  <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium bg-[#F8F6F0] text-[#343630] border border-[#EAE3D7]">
                    {{ r.angkatan_snapshot === 'XI' ? 'Kelas 11' : 'Kelas 10' }}
                  </span>
                </td>
                <td class="py-2.5 px-3.5">
                  <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-medium bg-[#A5B19B]/20 text-[#77846B] border border-[#A5B19B]/30">
                    {{ r.divisi_snapshot }}
                  </span>
                </td>
                <td class="py-2.5 px-3.5 text-[#343630]">
                  {{ r.tanggal }}
                </td>
                <td class="py-2.5 px-3.5 font-mono text-[#984B50] font-medium tabular-nums">
                  {{ r.waktu }} WITA
                </td>
                <td class="py-2.5 px-3.5 text-center">
                  <button
                    v-if="r.tanda_tangan"
                    type="button"
                    @click="selectedSignature = { imageUrl: r.tanda_tangan, nama: r.nama_snapshot, tanggal: r.tanggal, waktu: r.waktu }"
                    class="inline-flex items-center gap-1 px-2 py-1 text-[11px] font-medium text-[#77846B] hover:text-[#5e6954] bg-[#A5B19B]/20 border border-[#A5B19B]/40 rounded-lg transition-colors cursor-pointer"
                  >
                    <Eye class="w-3 h-3" />
                    <span>Lihat Paraf</span>
                  </button>
                  <span v-else class="text-[11px] text-[#85847D]/50 font-editorial italic">-</span>
                </td>
                <td class="py-2.5 px-3.5 text-center">
                  <div class="flex items-center justify-center gap-1">
                    <button
                      type="button"
                      @click="detailModalRecord = r"
                      title="Lihat Detail"
                      class="p-1 text-[#85847D] hover:text-[#77846B] rounded transition-colors cursor-pointer"
                    >
                      <Eye class="w-3.5 h-3.5" />
                    </button>
                    <button
                      type="button"
                      @click="editModalRecord = { ...r }"
                      title="Edit Absensi"
                      class="p-1 text-[#85847D] hover:text-[#343630] rounded transition-colors cursor-pointer"
                    >
                      <Edit2 class="w-3.5 h-3.5" />
                    </button>
                    <button
                      type="button"
                      @click="deleteConfirmRecord = r"
                      title="Hapus Absensi"
                      class="p-1 text-[#85847D] hover:text-[#984B50] rounded transition-colors cursor-pointer"
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

      <!-- 4. PAGINATION CONTROLS -->
      <div v-if="totalPages > 1" class="flex items-center justify-between p-3 bg-[#FFFDF8] rounded-xl border border-[#EAE3D7] text-xs">
        <span class="text-[#85847D]">
          Halaman <strong>{{ currentPage }}</strong> dari {{ totalPages }}
        </span>

        <div class="flex items-center gap-1.5">
          <button
            type="button"
            @click="currentPage > 1 ? currentPage-- : null"
            :disabled="currentPage === 1"
            class="p-1.5 rounded-lg border border-[#EAE3D7] text-[#343630] disabled:opacity-40 disabled:cursor-not-allowed hover:bg-[#F8F6F0] cursor-pointer"
          >
            <ChevronLeft class="w-4 h-4" />
          </button>
          <span class="px-2 font-mono text-[#343630]">{{ currentPage }}</span>
          <button
            type="button"
            @click="currentPage < totalPages ? currentPage++ : null"
            :disabled="currentPage === totalPages"
            class="p-1.5 rounded-lg border border-[#EAE3D7] text-[#343630] disabled:opacity-40 disabled:cursor-not-allowed hover:bg-[#F8F6F0] cursor-pointer"
          >
            <ChevronRight class="w-4 h-4" />
          </button>
        </div>
      </div>
    </div>

    <!-- 5. SIGNATURE PREVIEW MODAL -->
    <SignaturePreviewModal
      :signature="selectedSignature"
      @close="selectedSignature = null"
    />

    <!-- 6. EDIT MODAL -->
    <AttendanceEditModal
      :record="editModalRecord"
      @close="editModalRecord = null"
      @save="handleUpdateRecord"
    />

    <!-- 7. DELETE MODAL -->
    <DeleteConfirmModal
      :is-open="!!deleteConfirmRecord"
      title="Hapus Arsip Kehadiran?"
      :message="`Catatan absensi ${deleteConfirmRecord?.nama_snapshot || ''} tanggal ${deleteConfirmRecord?.tanggal || ''} akan dihapus secara permanen.`"
      confirm-label="Hapus"
      @close="deleteConfirmRecord = null"
      @confirm="handleDeleteRecord"
    />

    <!-- 8. DETAIL MODAL -->
    <AttendanceDetailModal
      :record="detailModalRecord"
      @close="detailModalRecord = null"
    />
  </div>
</template>
