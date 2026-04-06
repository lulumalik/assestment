<template>
    <AppShell>
        <div class="flex flex-col gap-6">
            <div class="rounded-2xl border border-zinc-200 bg-white p-6">
                <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h1 class="text-xl font-semibold tracking-tight">Dashboard</h1>
                        <p class="mt-1 text-sm text-zinc-600">Mulai dengan menambahkan asset, lalu scan SKU untuk mencatat aktivitas.</p>
                    </div>
                    <RouterLink class="rounded-xl bg-zinc-900 px-4 py-2 text-sm font-semibold text-white hover:bg-zinc-800" to="/scan">
                        Mulai Scan
                    </RouterLink>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
                <div class="rounded-2xl border border-zinc-200 bg-white p-5">
                    <div class="text-sm font-semibold text-zinc-900">Assets</div>
                    <div class="mt-1 text-sm text-zinc-600">Kelola master asset dan SKU.</div>
                    <div class="mt-4">
                        <RouterLink class="rounded-xl border border-zinc-200 bg-white px-4 py-2 text-sm font-semibold text-zinc-800 hover:bg-zinc-50 hover:text-white" to="/assets">
                            Buka Assets
                        </RouterLink>
                    </div>
                </div>
                <div class="rounded-2xl border border-zinc-200 bg-white p-5">
                    <div class="text-sm font-semibold text-zinc-900">Scanner SKU</div>
                    <div class="mt-1 text-sm text-zinc-600">Scan via keyboard atau kamera.</div>
                    <div class="mt-4">
                        <RouterLink class="rounded-xl border border-zinc-200 bg-white px-4 py-2 text-sm font-semibold text-zinc-800 hover:bg-zinc-50 hover:text-white" to="/scan">
                            Buka Scanner
                        </RouterLink>
                    </div>
                </div>
                <div class="rounded-2xl border border-zinc-200 bg-white p-5">
                    <div class="text-sm font-semibold text-zinc-900">Analitik Assessment</div>
                    <div class="mt-1 text-sm text-zinc-600">Pantau insight scan termasuk performa per kategori.</div>
                    <div class="mt-4">
                        <RouterLink class="rounded-xl border border-zinc-200 bg-white px-4 py-2 text-sm font-semibold text-zinc-800 hover:bg-zinc-50 hover:text-white" to="/assessment/dashboard">
                            Buka Analitik
                        </RouterLink>
                    </div>
                </div>
                <div class="rounded-2xl border border-zinc-200 bg-white p-5">
                    <div class="text-sm font-semibold text-zinc-900">Laporan Assessment</div>
                    <div class="mt-1 text-sm text-zinc-600">Lihat riwayat scan dan export ke PDF.</div>
                    <div class="mt-4">
                        <RouterLink class="rounded-xl border border-zinc-200 bg-white px-4 py-2 text-sm font-semibold text-zinc-800 hover:bg-zinc-50 hover:text-white" to="/assessment/report">
                            Buka Laporan
                        </RouterLink>
                    </div>
                </div>
                <div v-if="auth.user.value?.is_admin" class="rounded-2xl border border-zinc-200 bg-white p-5">
                    <div class="text-sm font-semibold text-zinc-900">User Management</div>
                    <div class="mt-1 text-sm text-zinc-600">Kelola akun user (admin saja).</div>
                    <div class="mt-4">
                        <RouterLink
                            class="rounded-xl border border-zinc-200 bg-white px-4 py-2 text-sm font-semibold text-zinc-800 hover:bg-zinc-50 hover:text-white"
                            to="/users"
                        >
                            Kelola User
                        </RouterLink>
                    </div>
                </div>
            </div>
        </div>
    </AppShell>
</template>

<script setup>
import AppShell from '../layouts/AppShell.vue';
import { RouterLink } from 'vue-router';
import { useAuth } from '../stores/auth';

const auth = useAuth();
</script>

