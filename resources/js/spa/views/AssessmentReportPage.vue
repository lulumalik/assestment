<template>
    <AppShell>
        <div class="flex flex-col gap-6">
            <div class="rounded-2xl border border-zinc-200 bg-white p-6">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h1 class="text-xl font-semibold tracking-tight">Laporan Assessment</h1>
                        <p class="mt-1 text-sm text-zinc-600">Riwayat scan asset dengan filter dan ekspor PDF.</p>
                    </div>
                    <button
                        class="no-print rounded-xl bg-zinc-900 px-4 py-2 text-sm font-semibold text-white hover:bg-zinc-800"
                        type="button"
                        @click="exportPdf"
                    >
                        Export PDF
                    </button>
                </div>

                <div class="no-print mt-5 grid grid-cols-1 gap-3 md:grid-cols-5">
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
                    <select
                        v-model="filters.category"
                        class="rounded-xl border border-zinc-200 bg-white px-3 py-2 text-sm outline-none focus:border-zinc-300"
                    >
                        <option value="">Semua Kategori</option>
                        <option v-for="option in categoryOptions" :key="option" :value="option">{{ option }}</option>
                    </select>
                    <select
                        v-model="filters.method"
                        class="rounded-xl border border-zinc-200 bg-white px-3 py-2 text-sm outline-none focus:border-zinc-300"
                    >
                        <option value="">Semua Metode</option>
                        <option value="keyboard">keyboard</option>
                        <option value="camera">camera</option>
                    </select>
                    <input
                        v-model="filters.q"
                        type="text"
                        placeholder="Cari SKU / nama / scanner"
                        class="rounded-xl border border-zinc-200 bg-white px-3 py-2 text-sm outline-none focus:border-zinc-300"
                        @keydown.enter="fetchReport(1)"
                    />
                </div>

                <div class="no-print mt-3 flex items-center gap-2">
                    <button
                        class="rounded-xl border border-zinc-200 bg-white px-4 py-2 text-sm font-semibold text-zinc-800 hover:bg-zinc-50"
                        type="button"
                        @click="fetchReport(1)"
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

            <div class="rounded-2xl border border-zinc-200 bg-white p-6">
                <div class="overflow-x-auto">
                    <table class="min-w-full text-left text-sm">
                        <thead class="text-xs uppercase text-zinc-500">
                            <tr>
                                <th class="py-2">Waktu Scan</th>
                                <th class="py-2">SKU</th>
                                <th class="py-2">Nama Asset</th>
                                <th class="py-2">Kategori</th>
                                <th class="py-2">Lokasi</th>
                                <th class="py-2">Metode</th>
                                <th class="py-2">Scanner</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="item in report.data" :key="item.id" class="border-t border-zinc-100">
                                <td class="py-3 text-zinc-700">{{ formatDateTime(item.scanned_at) }}</td>
                                <td class="py-3 font-medium text-zinc-900">{{ item.sku }}</td>
                                <td class="py-3 text-zinc-700">{{ item.asset_name || 'SKU tidak terdaftar' }}</td>
                                <td class="py-3 text-zinc-700">{{ item.asset_category || 'Tanpa Kategori' }}</td>
                                <td class="py-3 text-zinc-700">{{ item.asset_location || '-' }}</td>
                                <td class="py-3 text-zinc-700">{{ item.method || '-' }}</td>
                                <td class="py-3 text-zinc-700">{{ item.scanned_by_name || '-' }}</td>
                            </tr>
                            <tr v-if="!loading && report.data.length === 0">
                                <td class="py-6 text-center text-sm text-zinc-600" colspan="7">Belum ada data scan pada filter ini.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="no-print mt-6 flex items-center justify-between">
                    <button
                        class="rounded-xl border border-zinc-200 bg-white px-3 py-2 text-sm font-semibold text-zinc-800 disabled:opacity-50"
                        type="button"
                        :disabled="!report.prev_page_url || loading"
                        @click="fetchReport(report.current_page - 1)"
                    >
                        Prev
                    </button>
                    <div class="text-sm text-zinc-600">Halaman {{ report.current_page }} / {{ report.last_page }}</div>
                    <button
                        class="rounded-xl border border-zinc-200 bg-white px-3 py-2 text-sm font-semibold text-zinc-800 disabled:opacity-50"
                        type="button"
                        :disabled="!report.next_page_url || loading"
                        @click="fetchReport(report.current_page + 1)"
                    >
                        Next
                    </button>
                </div>
            </div>
        </div>
    </AppShell>
</template>

<script setup>
import { onMounted, reactive, ref } from 'vue';
import AppShell from '../layouts/AppShell.vue';

const loading = ref(false);
const categoryOptions = ref([]);
const filters = reactive({
    from_date: '',
    to_date: '',
    category: '',
    method: '',
    q: '',
});
const report = reactive({
    data: [],
    current_page: 1,
    last_page: 1,
    next_page_url: null,
    prev_page_url: null,
});

async function fetchReport(page = 1) {
    loading.value = true;
    try {
        const params = {
            page,
            from_date: filters.from_date || undefined,
            to_date: filters.to_date || undefined,
            category: filters.category || undefined,
            method: filters.method || undefined,
            q: filters.q || undefined,
        };
        const [{ data: reportData }, { data: analyticsData }] = await Promise.all([
            window.axios.get('/api/assessment/report', { params }),
            window.axios.get('/api/assessment/analytics', {
                params: {
                    from_date: filters.from_date || undefined,
                    to_date: filters.to_date || undefined,
                },
            }),
        ]);
        Object.assign(report, reportData);
        categoryOptions.value = (analyticsData.assets_per_category || []).map((item) => item.category).filter(Boolean);
    } finally {
        loading.value = false;
    }
}

function resetFilter() {
    filters.from_date = '';
    filters.to_date = '';
    filters.category = '';
    filters.method = '';
    filters.q = '';
    fetchReport(1);
}

function formatDateTime(value) {
    if (!value) return '-';
    return new Intl.DateTimeFormat('id-ID', {
        dateStyle: 'medium',
        timeStyle: 'short',
    }).format(new Date(value));
}

function exportPdf() {
    window.print();
}

onMounted(() => fetchReport(1));
</script>

<style scoped>
@media print {
    .no-print {
        display: none;
    }
}
</style>
