<template>
    <AppShell>
        <div class="flex flex-col gap-6">
            <div class="rounded-2xl border border-zinc-200 bg-white p-6">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <h1 class="text-xl font-semibold tracking-tight">Dashboard Analitik Assessment</h1>
                        <p class="mt-1 text-sm text-zinc-600">Ringkasan performa assessment asset berdasarkan aktivitas scan.</p>
                    </div>
                    <div class="grid grid-cols-1 gap-2 sm:grid-cols-2">
                        <input
                            v-model="filters.from_date"
                            type="date"
                            class="rounded-xl border border-zinc-200 bg-white px-3 py-2 text-sm outline-none focus:border-zinc-300"
                        />
                        <input
                            v-model="filters.to_date"
                            type="date"
                            class="rounded-xl border border-zinc-200 bg-white px-3 py-2 text-sm outline-none focus:border-zinc-300"
                        />
                    </div>
                </div>
                <div class="mt-3 flex items-center gap-2">
                    <button
                        class="rounded-xl border border-zinc-200 bg-white px-4 py-2 text-sm font-semibold text-zinc-800 hover:bg-zinc-50"
                        type="button"
                        @click="fetchAnalytics"
                    >
                        Terapkan Filter
                    </button>
                    <button
                        class="rounded-xl border border-zinc-200 bg-white px-4 py-2 text-sm font-semibold text-zinc-800 hover:bg-zinc-50"
                        type="button"
                        @click="resetFilter"
                    >
                        Reset
                    </button>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-5">
                <div class="rounded-2xl border border-zinc-200 bg-white p-5">
                    <div class="text-sm text-zinc-500">Total Asset</div>
                    <div class="mt-1 text-2xl font-semibold text-zinc-900">{{ analytics.summary.total_assets }}</div>
                </div>
                <div class="rounded-2xl border border-zinc-200 bg-white p-5">
                    <div class="text-sm text-zinc-500">Total Scan</div>
                    <div class="mt-1 text-2xl font-semibold text-zinc-900">{{ analytics.summary.total_scans }}</div>
                </div>
                <div class="rounded-2xl border border-zinc-200 bg-white p-5">
                    <div class="text-sm text-zinc-500">Scan Cocok</div>
                    <div class="mt-1 text-2xl font-semibold text-zinc-900">{{ analytics.summary.matched_scans }}</div>
                </div>
                <div class="rounded-2xl border border-zinc-200 bg-white p-5">
                    <div class="text-sm text-zinc-500">Scan Unknown SKU</div>
                    <div class="mt-1 text-2xl font-semibold text-zinc-900">{{ analytics.summary.unknown_scans }}</div>
                </div>
                <div class="rounded-2xl border border-zinc-200 bg-white p-5">
                    <div class="text-sm text-zinc-500">Unique SKU Discanned</div>
                    <div class="mt-1 text-2xl font-semibold text-zinc-900">{{ analytics.summary.unique_sku_scanned }}</div>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
                <div class="rounded-2xl border border-zinc-200 bg-white p-6">
                    <h2 class="text-base font-semibold text-zinc-900">Scan per Kategori</h2>
                    <div class="mt-4 space-y-3">
                        <div v-for="row in analytics.scans_per_category" :key="`scan-${row.category}`">
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-zinc-700">{{ row.category }}</span>
                                <span class="font-semibold text-zinc-900">{{ row.total }}</span>
                            </div>
                            <div class="mt-1 h-2 rounded-full bg-zinc-100">
                                <div class="h-full rounded-full bg-indigo-500" :style="{ width: `${toPercent(row.total, scanCategoryMax)}%` }"></div>
                            </div>
                        </div>
                        <div v-if="analytics.scans_per_category.length === 0" class="text-sm text-zinc-600">Belum ada data.</div>
                    </div>
                </div>

                <div class="rounded-2xl border border-zinc-200 bg-white p-6">
                    <h2 class="text-base font-semibold text-zinc-900">Asset per Kategori</h2>
                    <div class="mt-4 space-y-3">
                        <div v-for="row in analytics.assets_per_category" :key="`asset-${row.category}`">
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-zinc-700">{{ row.category }}</span>
                                <span class="font-semibold text-zinc-900">{{ row.total }}</span>
                            </div>
                            <div class="mt-1 h-2 rounded-full bg-zinc-100">
                                <div class="h-full rounded-full bg-emerald-500" :style="{ width: `${toPercent(row.total, assetCategoryMax)}%` }"></div>
                            </div>
                        </div>
                        <div v-if="analytics.assets_per_category.length === 0" class="text-sm text-zinc-600">Belum ada data.</div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
                <div class="rounded-2xl border border-zinc-200 bg-white p-6">
                    <h2 class="text-base font-semibold text-zinc-900">Tren Scan Harian</h2>
                    <div class="mt-4 space-y-3">
                        <div v-for="row in analytics.scans_per_day" :key="row.date">
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-zinc-700">{{ formatDate(row.date) }}</span>
                                <span class="font-semibold text-zinc-900">{{ row.total }}</span>
                            </div>
                            <div class="mt-1 h-2 rounded-full bg-zinc-100">
                                <div class="h-full rounded-full bg-zinc-900" :style="{ width: `${toPercent(row.total, scansPerDayMax)}%` }"></div>
                            </div>
                        </div>
                        <div v-if="analytics.scans_per_day.length === 0" class="text-sm text-zinc-600">Belum ada data.</div>
                    </div>
                </div>

                <div class="rounded-2xl border border-zinc-200 bg-white p-6">
                    <h2 class="text-base font-semibold text-zinc-900">Distribusi Metode Scan</h2>
                    <div class="mt-4 space-y-3">
                        <div v-for="row in analytics.scans_per_method" :key="row.method">
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-zinc-700">{{ row.method }}</span>
                                <span class="font-semibold text-zinc-900">{{ row.total }}</span>
                            </div>
                            <div class="mt-1 h-2 rounded-full bg-zinc-100">
                                <div class="h-full rounded-full bg-violet-500" :style="{ width: `${toPercent(row.total, scansPerMethodMax)}%` }"></div>
                            </div>
                        </div>
                        <div v-if="analytics.scans_per_method.length === 0" class="text-sm text-zinc-600">Belum ada data.</div>
                    </div>
                </div>
            </div>
        </div>
    </AppShell>
</template>

<script setup>
import { computed, onMounted, reactive, ref } from 'vue';
import AppShell from '../layouts/AppShell.vue';

const loading = ref(false);
const filters = reactive({
    from_date: '',
    to_date: '',
});
const analytics = reactive({
    summary: {
        total_assets: 0,
        total_scans: 0,
        matched_scans: 0,
        unknown_scans: 0,
        unique_sku_scanned: 0,
    },
    scans_per_category: [],
    assets_per_category: [],
    scans_per_day: [],
    scans_per_method: [],
});

const scanCategoryMax = computed(() => Math.max(1, ...analytics.scans_per_category.map((item) => Number(item.total || 0))));
const assetCategoryMax = computed(() => Math.max(1, ...analytics.assets_per_category.map((item) => Number(item.total || 0))));
const scansPerDayMax = computed(() => Math.max(1, ...analytics.scans_per_day.map((item) => Number(item.total || 0))));
const scansPerMethodMax = computed(() => Math.max(1, ...analytics.scans_per_method.map((item) => Number(item.total || 0))));

async function fetchAnalytics() {
    loading.value = true;
    try {
        const { data } = await window.axios.get('/api/assessment/analytics', {
            params: {
                from_date: filters.from_date || undefined,
                to_date: filters.to_date || undefined,
            },
        });
        Object.assign(analytics, data);
    } finally {
        loading.value = false;
    }
}

function resetFilter() {
    filters.from_date = '';
    filters.to_date = '';
    fetchAnalytics();
}

function toPercent(total, maxValue) {
    if (!maxValue) return 0;
    return Math.max(6, Math.round((Number(total || 0) / maxValue) * 100));
}

function formatDate(value) {
    if (!value) return '-';
    return new Intl.DateTimeFormat('id-ID', { dateStyle: 'medium' }).format(new Date(value));
}

onMounted(() => fetchAnalytics());
</script>
