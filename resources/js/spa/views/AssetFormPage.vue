<template>
    <AppShell>
        <div class="rounded-2xl border border-zinc-200 bg-white p-6">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h1 class="text-xl font-semibold tracking-tight">{{ isEdit ? 'Edit Asset' : 'Tambah Asset' }}</h1>
                    <p class="mt-1 text-sm text-zinc-600">Simpan informasi asset dan SKU untuk proses scan.</p>
                </div>
                <RouterLink class="rounded-xl border border-zinc-200 bg-white px-4 py-2 text-sm font-semibold text-zinc-800 hover:bg-zinc-50" to="/assets">
                    Kembali
                </RouterLink>
            </div>

            <form class="mt-6 grid grid-cols-1 gap-4" @submit.prevent="onSubmit">
                <div>
                    <label class="text-sm font-medium text-zinc-700">SKU</label>
                    <div class="mt-1 flex flex-col gap-2 sm:flex-row">
                        <input
                            class="w-full rounded-xl border border-zinc-200 bg-white px-3 py-2 text-sm outline-none focus:border-zinc-300"
                            type="text"
                            placeholder="Contoh: SKU-0001"
                            v-model="form.sku"
                        />
                        <button
                            class="shrink-0 rounded-xl border border-zinc-200 bg-white px-4 py-2 text-sm font-semibold text-zinc-800 hover:bg-zinc-50"
                            type="button"
                            @click="cameraOpen = true"
                        >
                            Scan
                        </button>
                    </div>
                </div>
                <div>
                    <label class="text-sm font-medium text-zinc-700">Nama</label>
                    <input
                        class="mt-1 w-full rounded-xl border border-zinc-200 bg-white px-3 py-2 text-sm outline-none focus:border-zinc-300"
                        type="text"
                        placeholder="Nama asset"
                        v-model="form.name"
                    />
                </div>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="text-sm font-medium text-zinc-700">Kategori</label>
                        <select
                            class="mt-1 w-full rounded-xl border border-zinc-200 bg-white px-3 py-2 text-sm outline-none focus:border-zinc-300"
                            v-model="form.category"
                        >
                            <option value="">Pilih kategori</option>
                            <option v-if="isCustomCategory" :value="form.category">{{ form.category }}</option>
                            <optgroup v-for="group in categoryOptions" :key="group.label" :label="group.label">
                                <option v-for="item in group.items" :key="`${group.label}-${item}`" :value="`${group.label} - ${item}`">
                                    {{ item }}
                                </option>
                            </optgroup>
                        </select>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-zinc-700">Jumlah</label>
                        <input
                            class="mt-1 w-full rounded-xl border border-zinc-200 bg-white px-3 py-2 text-sm outline-none focus:border-zinc-300"
                            type="number"
                            min="1"
                            step="1"
                            placeholder="Contoh: 1"
                            v-model.number="form.quantity"
                        />
                    </div>
                </div>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="text-sm font-medium text-zinc-700">Lokasi</label>
                        <input
                            class="mt-1 w-full rounded-xl border border-zinc-200 bg-white px-3 py-2 text-sm outline-none focus:border-zinc-300"
                            type="text"
                            placeholder="Contoh: Gudang A"
                            v-model="form.location"
                        />
                    </div>
                    <div>
                        <label class="text-sm font-medium text-zinc-700">Status</label>
                        <input
                            class="mt-1 w-full rounded-xl border border-zinc-200 bg-white px-3 py-2 text-sm outline-none focus:border-zinc-300"
                            type="text"
                            placeholder="Contoh: Aktif"
                            v-model="form.status"
                        />
                    </div>
                </div>
                <div>
                    <label class="text-sm font-medium text-zinc-700">Catatan</label>
                    <textarea
                        class="mt-1 w-full rounded-xl border border-zinc-200 bg-white px-3 py-2 text-sm outline-none focus:border-zinc-300"
                        rows="4"
                        placeholder="Opsional"
                        v-model="form.notes"
                    ></textarea>
                </div>

                <div v-if="error" class="rounded-xl border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-800">
                    {{ error }}
                </div>

                <div class="mt-2 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                    <button
                        class="rounded-xl bg-zinc-900 px-4 py-2 text-sm font-semibold text-white hover:bg-zinc-800 disabled:opacity-60"
                        type="submit"
                        :disabled="loading"
                    >
                        {{ loading ? 'Menyimpan...' : 'Simpan' }}
                    </button>

                    <button
                        v-if="isEdit"
                        class="rounded-xl border border-red-200 bg-white px-4 py-2 text-sm font-semibold text-red-700 hover:bg-red-50 disabled:opacity-60"
                        type="button"
                        :disabled="loading"
                        @click="onDelete"
                    >
                        Hapus
                    </button>
                </div>
            </form>
        </div>
        <div v-if="cameraOpen" class="fixed inset-0 z-50 grid place-items-center bg-black/40 p-6">
            <div class="w-full max-w-lg">
                <CameraSkuScanner
                    @close="cameraOpen = false"
                    @decoded="onCameraDecoded"
                />
            </div>
        </div>
    </AppShell>
</template>

<script setup>
import { computed, onMounted, reactive, ref } from 'vue';
import { RouterLink, useRoute, useRouter } from 'vue-router';
import AppShell from '../layouts/AppShell.vue';
import CameraSkuScanner from '../components/CameraSkuScanner.vue';

const route = useRoute();
const router = useRouter();

const loading = ref(false);
const error = ref('');
const cameraOpen = ref(false);
const assetId = computed(() => route.params.id);
const isEdit = computed(() => !!assetId.value);
const categoryOptions = [
    { label: 'IT Equipment', items: ['Laptop', 'Desktop', 'Server', 'Monitor', 'Printer & Scanner', 'Networking (Router, Switch, Access Point)', 'Storage (HDD, SSD, NAS)'] },
    { label: 'Furniture', items: ['Meja', 'Kursi', 'Lemari', 'Rak', 'Partisi'] },
    { label: 'Kendaraan', items: ['Mobil', 'Motor', 'Truk', 'Pickup'] },
    { label: 'Mesin & Produksi', items: ['Mesin Produksi', 'Mesin Packing', 'Mesin CNC', 'Conveyor'] },
    { label: 'Tools & Peralatan', items: ['Hand Tools (Obeng, Tang, dll)', 'Power Tools (Bor, Gerinda)', 'Alat Ukur (Multimeter, dll)'] },
    { label: 'Listrik & Utilitas', items: ['Genset', 'UPS', 'AC', 'Panel Listrik'] },
    { label: 'Properti', items: ['Gedung', 'Gudang', 'Tanah'] },
    { label: 'Asset Digital', items: ['Software License', 'Domain', 'Hosting', 'Subscription SaaS'] },
];
const categoryValueSet = new Set(categoryOptions.flatMap((group) => group.items.map((item) => `${group.label} - ${item}`)));
const isCustomCategory = computed(() => !!form.category && !categoryValueSet.has(form.category));

const form = reactive({
    sku: '',
    name: '',
    category: '',
    quantity: 1,
    location: '',
    status: '',
    notes: '',
});

async function loadAsset() {
    if (!isEdit.value) return;
    loading.value = true;
    try {
        const { data } = await window.axios.get(`/api/assets/${assetId.value}`);
        Object.assign(form, data.asset);
        if (!Number(form.quantity) || Number(form.quantity) < 1) {
            form.quantity = 1;
        }
    } finally {
        loading.value = false;
    }
}

async function onSubmit() {
    error.value = '';
    loading.value = true;
    try {
        const payload = {
            ...form,
            quantity: Number(form.quantity) > 0 ? Number(form.quantity) : 1,
        };
        if (isEdit.value) {
            await window.axios.put(`/api/assets/${assetId.value}`, payload);
        } else {
            await window.axios.post('/api/assets', payload);
        }
        router.replace('/assets');
    } catch (e) {
        error.value = e?.response?.data?.message || 'Gagal menyimpan.';
    } finally {
        loading.value = false;
    }
}

async function onDelete() {
    if (!isEdit.value) return;
    if (!confirm('Hapus asset ini?')) return;
    loading.value = true;
    try {
        await window.axios.delete(`/api/assets/${assetId.value}`);
        router.replace('/assets');
    } finally {
        loading.value = false;
    }
}

function onCameraDecoded(payload) {
    cameraOpen.value = false;
    const scannedSku = typeof payload === 'string' ? payload : payload?.sku;
    if (!scannedSku) return;
    form.sku = String(scannedSku).trim();
}

onMounted(() => {
    if (!isEdit.value) {
        const sku = route.query.sku;
        if (typeof sku === 'string' && sku.trim()) form.sku = sku.trim();
    }
    loadAsset();
});
</script>

