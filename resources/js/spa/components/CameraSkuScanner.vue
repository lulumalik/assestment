<template>
    <div ref="rootEl" class="rounded-2xl border border-zinc-200 bg-white p-4">
        <div class="flex items-center justify-between">
            <div class="text-sm font-semibold text-zinc-900">Scan dengan Kamera</div>
            <button
                class="rounded-xl border border-zinc-200 bg-white px-3 py-2 text-sm font-semibold text-zinc-800 hover:bg-zinc-50"
                type="button"
                @click="emit('close')"
            >
                Tutup
            </button>
        </div>

        <div class="mt-3 overflow-hidden rounded-xl bg-black">
            <QrcodeStream
                class="h-80 w-full object-contain"
                :paused="paused"
                :formats="barcodeFormats"
                :constraints="cameraConstraints"
                @decode="onDecode"
                @detect="onDetect"
                @init="onInit"
                @error="onError"
            />
        </div>

        <div class="mt-3 text-sm text-zinc-600">
            Izinkan akses kamera, lalu arahkan barcode ke kamera.
        </div>

        <div class="mt-3 flex items-center gap-2">
            <button
                class="rounded-xl bg-zinc-900 px-3 py-2 text-sm font-semibold text-white hover:bg-zinc-800 disabled:opacity-60"
                type="button"
                :disabled="paused || barcodeLoopBusy"
                @click="scanNow"
            >
                {{ barcodeLoopBusy ? 'Memindai...' : 'Scan Sekarang' }}
            </button>
            <div class="text-xs text-zinc-500">Posisikan barcode memenuhi frame dan hindari blur.</div>
        </div>

        <div v-if="error" class="mt-3 rounded-xl border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-800">
            {{ error }}
        </div>
    </div>
</template>

<script setup>
import { onBeforeUnmount, ref } from 'vue';
import { QrcodeStream } from 'vue-qrcode-reader';

const emit = defineEmits(['decoded', 'close']);

const rootEl = ref(null);
const error = ref('');
const paused = ref(false);
const barcodeFormats = [
    'qr_code',
    'upc_a',
    'upc_e',
    'ean_13',
    'ean_8',
    'code_128',
    'code_39',
    'itf',
    'codabar',
];
const cameraConstraints = {
    facingMode: { ideal: 'environment' },
    width: { ideal: 1920 },
    height: { ideal: 1080 },
};
const barcodeDetectorFormats = ['upc_a', 'upc_e', 'ean_13', 'ean_8', 'code_128', 'code_39', 'itf', 'codabar'];
let barcodeDetector = null;
let barcodeLoopId = null;
const barcodeLoopBusy = ref(false);

function captureJpegDataUrl() {
    const video = rootEl.value?.querySelector?.('video');
    const vw = video?.videoWidth || 0;
    const vh = video?.videoHeight || 0;
    if (!vw || !vh) return null;

    const maxW = 1280;
    const scale = vw > maxW ? maxW / vw : 1;
    const w = Math.round(vw * scale);
    const h = Math.round(vh * scale);

    const canvas = document.createElement('canvas');
    canvas.width = w;
    canvas.height = h;
    const ctx = canvas.getContext('2d');
    if (!ctx) return null;
    ctx.drawImage(video, 0, 0, w, h);
    return canvas.toDataURL('image/jpeg', 0.8);
}

function finalizeDecode(text) {
    const value = (text || '').trim();
    if (!value) return;
    error.value = '';
    const image = captureJpegDataUrl();
    paused.value = true;
    stopBarcodeFallback();
    if (navigator?.vibrate) navigator.vibrate(50);
    emit('decoded', { sku: value, image });
}

function onDecode(result) {
    if (typeof result === 'string') {
        finalizeDecode(result);
    }
}

function onDetect(detectedCodes) {
    const matched = Array.isArray(detectedCodes)
        ? detectedCodes.find((code) => (code?.rawValue || code?.text || '').trim().length > 0)
        : null;
    const value = matched?.rawValue || matched?.text || '';
    finalizeDecode(value);
}

async function onInit(initPromise) {
    error.value = '';
    paused.value = false;
    stopBarcodeFallback();
    try {
        await initPromise;
        await startBarcodeFallback();
    } catch (e) {
        error.value = e?.message || 'Tidak bisa mengakses kamera.';
        stopBarcodeFallback();
    }
}

function onError(e) {
    error.value = e?.message || 'Scanner barcode tidak tersedia di browser ini.';
}

async function startBarcodeFallback() {
    if (typeof window === 'undefined' || !window.BarcodeDetector) return;
    let supported = [];
    try {
        supported = await window.BarcodeDetector.getSupportedFormats();
    } catch (e) {
        return;
    }
    const enabledFormats = barcodeDetectorFormats.filter((format) => supported.includes(format));
    if (!enabledFormats.length) return;
    barcodeDetector = new window.BarcodeDetector({ formats: enabledFormats });
    barcodeLoopId = window.setInterval(scanBarcodeFrame, 260);
}

function stopBarcodeFallback() {
    if (barcodeLoopId) {
        clearInterval(barcodeLoopId);
        barcodeLoopId = null;
    }
    barcodeDetector = null;
    barcodeLoopBusy.value = false;
}

async function scanBarcodeFrame() {
    if (paused.value || !barcodeDetector || barcodeLoopBusy.value) return;
    const video = rootEl.value?.querySelector?.('video');
    if (!video || video.readyState < 2) return;
    barcodeLoopBusy.value = true;
    try {
        const value = await detectFromVideo(video);
        finalizeDecode(value);
    } catch (e) {
    } finally {
        barcodeLoopBusy.value = false;
    }
}

async function scanNow() {
    error.value = '';
    await scanBarcodeFrame();
    if (!paused.value) {
        error.value = 'Barcode belum terbaca. Coba lebih dekat, luruskan, dan pastikan pencahayaan terang.';
    }
}

async function detectFromVideo(video) {
    const sources = buildDetectionSources(video);
    for (const source of sources) {
        let detectedCodes = [];
        try {
            detectedCodes = await barcodeDetector.detect(source);
        } catch (e) {
            detectedCodes = [];
        }
        const matched = Array.isArray(detectedCodes)
            ? detectedCodes.find((code) => (code?.rawValue || code?.text || '').trim().length > 0)
            : null;
        const value = matched?.rawValue || matched?.text || '';
        if (value) return value;
    }
    return '';
}

function buildDetectionSources(video) {
    const vw = video.videoWidth || 0;
    const vh = video.videoHeight || 0;
    if (!vw || !vh) return [video];
    const full = createScaledCanvas(video, 0, 0, vw, vh, 1280);
    const midStrip = createScaledCanvas(video, 0, Math.floor(vh * 0.24), vw, Math.floor(vh * 0.52), 1280);
    const lowStrip = createScaledCanvas(video, 0, Math.floor(vh * 0.45), vw, Math.floor(vh * 0.4), 1280);
    const enhancedMid = createHighContrastCanvas(midStrip);
    const enhancedLow = createHighContrastCanvas(lowStrip);
    return [midStrip, enhancedMid, lowStrip, enhancedLow, full];
}

function createScaledCanvas(video, sx, sy, sw, sh, maxWidth) {
    const scale = sw > maxWidth ? maxWidth / sw : 1;
    const w = Math.max(1, Math.round(sw * scale));
    const h = Math.max(1, Math.round(sh * scale));
    const canvas = document.createElement('canvas');
    canvas.width = w;
    canvas.height = h;
    const ctx = canvas.getContext('2d');
    if (ctx) {
        ctx.drawImage(video, sx, sy, sw, sh, 0, 0, w, h);
    }
    return canvas;
}

function createHighContrastCanvas(sourceCanvas) {
    const canvas = document.createElement('canvas');
    canvas.width = sourceCanvas.width;
    canvas.height = sourceCanvas.height;
    const ctx = canvas.getContext('2d');
    if (!ctx) return sourceCanvas;
    ctx.drawImage(sourceCanvas, 0, 0);
    const imageData = ctx.getImageData(0, 0, canvas.width, canvas.height);
    const pixels = imageData.data;
    for (let i = 0; i < pixels.length; i += 4) {
        const luminance = pixels[i] * 0.299 + pixels[i + 1] * 0.587 + pixels[i + 2] * 0.114;
        const normalized = luminance < 138 ? 0 : 255;
        pixels[i] = normalized;
        pixels[i + 1] = normalized;
        pixels[i + 2] = normalized;
    }
    ctx.putImageData(imageData, 0, 0);
    return canvas;
}

onBeforeUnmount(() => {
    stopBarcodeFallback();
});
</script>

