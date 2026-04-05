<template>
    <AppShell>
        <div class="rounded-2xl border border-zinc-200 bg-white p-6">
            <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <h1 class="text-xl font-semibold tracking-tight">Scanner SKU</h1>
                    <p class="mt-1 text-sm text-zinc-600">
                        Arahkan kursor ke input lalu scan barcode (scanner biasanya seperti keyboard) dan tekan Enter.
                    </p>
                </div>
                <RouterLink
                    class="rounded-xl border border-zinc-200 bg-white px-4 py-2 text-sm font-semibold text-zinc-800 hover:bg-zinc-50"
                    to="/assets/new"
                >
                    Tambah Asset
                </RouterLink>
            </div>

            <div class="mt-6 flex flex-col gap-3 sm:flex-row sm:items-center">
                <input
                    ref="skuInput"
                    class="w-full rounded-xl border border-zinc-200 bg-white px-3 py-2 text-sm outline-none focus:border-zinc-300"
                    type="text"
                    placeholder="Scan / ketik SKU lalu Enter"
                    v-model="sku"
                    @keydown.enter.prevent="submitScan"
                />
                <button
                    class="shrink-0 rounded-xl bg-zinc-900 px-4 py-2 text-sm font-semibold text-white hover:bg-zinc-800 disabled:opacity-60"
                    type="button"
                    :disabled="loading || !sku.trim()"
                    @click="submitScan"
                >
                    {{ loading ? 'Menyimpan...' : 'Simpan Scan' }}
                </button>
                <button
                    class="shrink-0 rounded-xl border border-zinc-200 bg-white px-4 py-2 text-sm font-semibold text-zinc-800 hover:bg-zinc-50"
                    type="button"
                    @click="cameraOpen = true"
                >
                    Kamera
                </button>
            </div>

            <div v-if="message" class="mt-4 rounded-xl border border-zinc-200 bg-zinc-50 px-3 py-2 text-sm text-zinc-800">
                {{ message }}
            </div>

            <div v-if="last" class="mt-6 rounded-2xl border border-zinc-200 bg-white p-5">
                <div class="text-sm text-zinc-500">Hasil terakhir</div>
                <div class="mt-2 flex flex-col gap-3">
                    <div class="text-lg font-semibold text-zinc-900">{{ last.sku }}</div>
                    <div v-if="last.asset" class="text-sm text-zinc-700">
                        Asset: <span class="font-semibold">{{ last.asset.name }}</span>
                        <span class="text-zinc-500">({{ last.asset.location || '-' }})</span>
                    </div>
                    <div v-else class="text-sm text-zinc-700">
                        SKU belum terdaftar.
                        <RouterLink class="font-semibold text-zinc-900 hover:underline" :to="`/assets/new?sku=${encodeURIComponent(last.sku)}`">
                            Tambahkan sekarang
                        </RouterLink>
                    </div>
                    <img
                        v-if="last.image_url"
                        :src="last.image_url"
                        class="h-40 w-full rounded-xl border border-zinc-200 object-cover"
                        alt="Scan photo"
                    />
                </div>
            </div>

            <div v-if="cameraOpen" class="fixed inset-0 z-50 grid place-items-center bg-black/40 p-6">
                <div class="w-full max-w-lg">
                    <CameraSkuScanner
                        @close="cameraOpen = false"
                        @decoded="onCameraDecoded"
                    />
                </div>
            </div>
        </div>
    </AppShell>
</template>

<script setup>
import { nextTick, onMounted, ref } from 'vue';
import { RouterLink } from 'vue-router';
import AppShell from '../layouts/AppShell.vue';
import CameraSkuScanner from '../components/CameraSkuScanner.vue';

const skuInput = ref(null);
const sku = ref('');
const loading = ref(false);
const message = ref('');
const last = ref(null);
const cameraOpen = ref(false);
const lastSubmit = ref({ sku: '', at: 0 });

async function focusInput() {
    await nextTick();
    skuInput.value?.focus?.();
}

async function submitScan(method = 'keyboard', forcedSku = null, image = null) {
    const value = (forcedSku ?? sku.value).trim();
    if (!value) return;
    const now = Date.now();
    if (lastSubmit.value.sku === value && now - lastSubmit.value.at < 2500) return;
    lastSubmit.value = { sku: value, at: now };

    message.value = '';
    loading.value = true;
    try {
        const { data } = await window.axios.post('/api/scans', { sku: value, method, image });
        last.value = { sku: value, asset: data.asset, image_url: data.scan?.image_url || null };
        message.value = data.asset ? 'Scan tersimpan.' : 'Scan tersimpan, namun SKU belum ada di master asset.';
        sku.value = '';
        focusInput();
    } catch (e) {
        message.value = e?.response?.data?.message || 'Gagal menyimpan scan.';
    } finally {
        loading.value = false;
    }
}

async function onCameraDecoded(payload) {
    cameraOpen.value = false;
    const scannedSku = typeof payload === 'string' ? payload : payload?.sku;
    const image = typeof payload === 'string' ? null : payload?.image;
    if (!scannedSku) return;
    await submitScan('camera', scannedSku, image);
}

onMounted(() => focusInput());
</script>

