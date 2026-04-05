<template>
    <AppShell>
        <div class="rounded-2xl border border-zinc-200 bg-white p-6">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-xl font-semibold tracking-tight">Assets</h1>
                    <p class="mt-1 text-sm text-zinc-600">Daftar asset yang tersimpan di database.</p>
                </div>
                <RouterLink class="rounded-xl bg-zinc-900 px-4 py-2 text-sm font-semibold text-white hover:bg-zinc-800" to="/assets/new">
                    Tambah Asset
                </RouterLink>
            </div>

            <div class="mt-5 flex items-center gap-2">
                <input
                    class="w-full rounded-xl border border-zinc-200 bg-white px-3 py-2 text-sm outline-none focus:border-zinc-300"
                    type="text"
                    placeholder="Cari (SKU / nama / lokasi)..."
                    v-model="q"
                    @keydown.enter="fetchAssets(1)"
                />
                <button
                    class="shrink-0 rounded-xl border border-zinc-200 bg-white px-3 py-2 text-sm font-semibold text-zinc-800 hover:bg-zinc-50"
                    type="button"
                    @click="fetchAssets(1)"
                >
                    Cari
                </button>
            </div>

            <div class="mt-5 overflow-x-auto">
                <table class="min-w-full text-left text-sm">
                    <thead class="text-xs uppercase text-zinc-500">
                        <tr>
                            <th class="py-2">SKU</th>
                            <th class="py-2">Nama</th>
                            <th class="py-2">Lokasi</th>
                            <th class="py-2 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="item in assets.data" :key="item.id" class="border-t border-zinc-100">
                            <td class="py-3 font-medium text-zinc-900">{{ item.sku }}</td>
                            <td class="py-3 text-zinc-700">{{ item.name }}</td>
                            <td class="py-3 text-zinc-700">{{ item.location || '-' }}</td>
                            <td class="py-3 text-right">
                                <RouterLink
                                    class="rounded-lg border border-zinc-200 bg-white px-3 py-1.5 text-xs font-semibold text-zinc-800 hover:bg-zinc-50"
                                    :to="`/assets/${item.id}/edit`"
                                >
                                    Edit
                                </RouterLink>
                            </td>
                        </tr>
                        <tr v-if="!loading && assets.data.length === 0">
                            <td class="py-6 text-center text-sm text-zinc-600" colspan="4">Belum ada data.</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="mt-6 flex items-center justify-between">
                <button
                    class="rounded-xl border border-zinc-200 bg-white px-3 py-2 text-sm font-semibold text-zinc-800 disabled:opacity-50"
                    type="button"
                    :disabled="!assets.prev_page_url || loading"
                    @click="fetchAssets(assets.current_page - 1)"
                >
                    Prev
                </button>
                <div class="text-sm text-zinc-600">Halaman {{ assets.current_page }} / {{ assets.last_page }}</div>
                <button
                    class="rounded-xl border border-zinc-200 bg-white px-3 py-2 text-sm font-semibold text-zinc-800 disabled:opacity-50"
                    type="button"
                    :disabled="!assets.next_page_url || loading"
                    @click="fetchAssets(assets.current_page + 1)"
                >
                    Next
                </button>
            </div>
        </div>
    </AppShell>
</template>

<script setup>
import { onMounted, reactive, ref } from 'vue';
import { RouterLink } from 'vue-router';
import AppShell from '../layouts/AppShell.vue';

const q = ref('');
const loading = ref(false);
const assets = reactive({
    data: [],
    current_page: 1,
    last_page: 1,
    next_page_url: null,
    prev_page_url: null,
});

async function fetchAssets(page = 1) {
    loading.value = true;
    try {
        const { data } = await window.axios.get('/api/assets', {
            params: { page, q: q.value || undefined },
        });
        Object.assign(assets, data);
    } finally {
        loading.value = false;
    }
}

onMounted(() => fetchAssets(1));
</script>

