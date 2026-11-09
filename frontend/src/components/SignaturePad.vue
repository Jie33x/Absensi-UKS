<script setup lang="ts">
import { ref, onMounted, onUnmounted, watch } from 'vue';
import { Eraser, Check, PenTool, Sparkles } from 'lucide-vue-next';

const props = defineProps<{
  modelValue?: string | null;
}>();

const emit = defineEmits<{
  (e: 'update:modelValue', val: string | null): void;
  (e: 'save', val: string | null): void;
}>();

const canvasRef = ref<HTMLCanvasElement | null>(null);
const isDrawing = ref(false);
const isEmpty = ref(true);
const isSaved = ref(false);

function initCanvas() {
  const canvas = canvasRef.current;
  if (!canvas) return;

  const rect = canvas.getBoundingClientRect();
  const width = rect.width > 0 ? rect.width : 500;
  const height = rect.height > 0 ? rect.height : 180;
  const dpr = window.devicePixelRatio || 1;

  canvas.width = width * dpr;
  canvas.height = height * dpr;

  const ctx = canvas.getContext('2d');
  if (ctx) {
    ctx.scale(dpr, dpr);
    ctx.strokeStyle = '#343630'; // Charcoal ink
    ctx.lineWidth = 2.4;
    ctx.lineCap = 'round';
    ctx.lineJoin = 'round';
  }
}

function getCoordinates(e: PointerEvent) {
  const canvas = canvasRef.current;
  if (!canvas) return { x: 0, y: 0 };
  const rect = canvas.getBoundingClientRect();
  return {
    x: e.clientX - rect.left,
    y: e.clientY - rect.top,
  };
}

function handlePointerDown(e: PointerEvent) {
  e.preventDefault();
  const canvas = canvasRef.current;
  if (!canvas) return;
  try {
    canvas.setPointerCapture(e.pointerId);
  } catch {
    // Ignored
  }

  const ctx = canvas.getContext('2d');
  if (!ctx) return;

  const { x, y } = getCoordinates(e);
  ctx.beginPath();
  ctx.moveTo(x, y);

  isDrawing.value = true;
  isEmpty.value = false;
  isSaved.value = false;
}

function handlePointerMove(e: PointerEvent) {
  if (!isDrawing.value) return;
  e.preventDefault();
  const canvas = canvasRef.current;
  if (!canvas) return;

  const ctx = canvas.getContext('2d');
  if (!ctx) return;

  const { x, y } = getCoordinates(e);
  ctx.lineTo(x, y);
  ctx.stroke();
}

function handlePointerUp(e: PointerEvent) {
  if (!isDrawing.value) return;
  e.preventDefault();
  const canvas = canvasRef.current;
  if (!canvas) return;

  try {
    canvas.releasePointerCapture(e.pointerId);
  } catch {
    // Ignored
  }

  isDrawing.value = false;
  const dataUrl = canvas.toDataURL('image/png');
  isSaved.value = true;
  emit('update:modelValue', dataUrl);
  emit('save', dataUrl);
}

function handleClear() {
  const canvas = canvasRef.current;
  if (!canvas) return;
  const ctx = canvas.getContext('2d');
  if (!ctx) return;

  const dpr = window.devicePixelRatio || 1;
  ctx.clearRect(0, 0, canvas.width / dpr, canvas.height / dpr);
  isEmpty.value = true;
  isSaved.value = false;
  emit('update:modelValue', null);
  emit('save', null);
}

function handleGenerateSample() {
  const canvas = canvasRef.current;
  if (!canvas) return;
  initCanvas();
  const ctx = canvas.getContext('2d');
  if (!ctx) return;

  ctx.beginPath();
  ctx.strokeStyle = '#343630';
  ctx.lineWidth = 2.4;
  ctx.lineCap = 'round';
  ctx.lineJoin = 'round';

  ctx.moveTo(45, 100);
  ctx.bezierCurveTo(75, 45, 105, 130, 145, 80);
  ctx.bezierCurveTo(165, 55, 195, 120, 235, 95);
  ctx.lineTo(285, 95);
  ctx.moveTo(65, 120);
  ctx.lineTo(265, 115);
  ctx.stroke();

  isEmpty.value = false;
  isSaved.value = true;
  const dataUrl = canvas.toDataURL('image/png');
  emit('update:modelValue', dataUrl);
  emit('save', dataUrl);
}

onMounted(() => {
  initCanvas();
  window.addEventListener('resize', initCanvas);
});

onUnmounted(() => {
  window.removeEventListener('resize', initCanvas);
});

watch(() => props.modelValue, (newVal) => {
  if (!newVal) {
    handleClear();
  }
});
</script>

<template>
  <div class="space-y-2.5">
    <div class="flex items-center justify-between text-xs">
      <label class="font-semibold text-[#343630] flex items-center gap-1.5 font-sans">
        <PenTool class="w-3.5 h-3.5 text-[#984B50]" />
        <span>Tanda Tangan Digital</span>
        <span class="text-[#984B50]">*</span>
      </label>
      <span class="text-[11px] font-editorial italic text-[#85847D]">
        Bubuhi paraf menggunakan jari atau tetikus
      </span>
    </div>

    <div class="relative border border-[#EAE3D7] rounded-xl bg-[#FFFDF8] overflow-hidden shadow-inner focus-within:border-[#984B50] transition-colors">
      <canvas
        ref="canvasRef"
        class="w-full h-44 touch-none cursor-crosshair block"
        @pointerdown="handlePointerDown"
        @pointermove="handlePointerMove"
        @pointerup="handlePointerUp"
        @pointercancel="handlePointerUp"
      />

      <div
        v-if="isEmpty"
        class="absolute inset-0 pointer-events-none flex flex-col items-center justify-center text-[#85847D]/60 select-none"
      >
        <PenTool class="w-6 h-6 mb-1 opacity-40 text-[#984B50]" />
        <p class="text-xs font-editorial italic">Bubuhi tanda tangan Anda di lembar ini</p>
        <p class="text-[10px] text-[#85847D]/50 mt-0.5">Area kanvas presensi</p>
      </div>

      <!-- Paper signature hairline -->
      <div class="absolute bottom-6 left-8 right-8 border-b border-[#EAE3D7]/80 pointer-events-none"></div>
    </div>

    <div class="flex flex-wrap items-center justify-between gap-2 pt-1 text-xs">
      <div class="flex items-center gap-2">
        <button
          type="button"
          @click="handleClear"
          :disabled="isEmpty"
          class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-[#343630] bg-[#EAE3D7] hover:bg-[#ded6c7] rounded-lg transition-colors disabled:opacity-40 disabled:cursor-not-allowed cursor-pointer"
        >
          <Eraser class="w-3.5 h-3.5 text-[#85847D]" />
          <span>Hapus</span>
        </button>

        <button
          type="button"
          @click="handleGenerateSample"
          class="inline-flex items-center gap-1 px-2.5 py-1.5 text-[11px] font-medium text-[#77846B] hover:text-[#343630] bg-[#FFFDF8] hover:bg-[#EAE3D7]/40 border border-[#EAE3D7] rounded-lg transition-colors cursor-pointer"
          title="Bubuhkan paraf otomatis"
        >
          <Sparkles class="w-3 h-3 text-[#A5B19B]" />
          <span>Contoh Tanda Tangan</span>
        </button>
      </div>

      <div class="flex items-center gap-2">
        <span
          v-if="isSaved && !isEmpty"
          class="inline-flex items-center gap-1 text-[11px] font-medium text-[#77846B] bg-[#A5B19B]/15 px-2.5 py-1 rounded-md border border-[#A5B19B]/30"
        >
          <Check class="w-3 h-3 text-[#77846B]" />
          <span>Paraf Terbaca</span>
        </span>
      </div>
    </div>
  </div>
</template>
