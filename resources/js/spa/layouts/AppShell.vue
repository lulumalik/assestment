<template>
    <div class="min-h-dvh bg-zinc-100">
        <div class="flex min-h-dvh">
            <aside class="hidden w-72 flex-col border-r border-zinc-200 bg-white lg:flex">
                <div class="px-5 py-5">
                    <div class="flex items-center gap-3">
                        <div class="grid h-10 w-10 place-items-center rounded-2xl bg-indigo-600 text-sm font-bold text-white">MA</div>
                        <div>
                            <div class="text-sm font-semibold leading-5 text-zinc-900">Management Asset</div>
                            <div class="text-xs text-zinc-600">Scanner SKU & Database</div>
                        </div>
                    </div>
                </div>

                <div class="px-3">
                    <div class="px-3 py-2 text-xs font-semibold text-zinc-500">MENU</div>
                    <nav class="space-y-1">
                        <RouterLink
                            class="flex items-center gap-3 rounded-xl px-3 py-2 text-sm font-medium text-zinc-700 hover:bg-zinc-50"
                            active-class="bg-gray-200 text-white hover:bg-gray-200"
                            to="/"
                        >
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none">
                                <path d="M4 10.5L12 4l8 6.5V20a1 1 0 0 1-1 1h-5v-7H10v7H5a1 1 0 0 1-1-1v-9.5Z" stroke="currentColor" stroke-width="2" stroke-linejoin="round" />
                            </svg>
                            Dashboard
                        </RouterLink>
                        <RouterLink
                            class="flex items-center gap-3 rounded-xl px-3 py-2 text-sm font-medium text-zinc-700 hover:bg-zinc-50"
                            active-class="bg-gray-200 text-white hover:bg-gray-200"
                            to="/assets"
                        >
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none">
                                <path d="M4 7a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V7Z" stroke="currentColor" stroke-width="2" />
                                <path d="M8 9h8M8 13h6" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
                            </svg>
                            Assets
                        </RouterLink>
                        <RouterLink
                            class="flex items-center gap-3 rounded-xl px-3 py-2 text-sm font-medium text-zinc-700 hover:bg-zinc-50"
                            active-class="bg-gray-200 text-white hover:bg-gray-200"
                            to="/scan"
                        >
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none">
                                <path d="M7 7V6a2 2 0 0 1 2-2h1M17 7V6a2 2 0 0 0-2-2h-1M7 17v1a2 2 0 0 0 2 2h1M17 17v1a2 2 0 0 1-2 2h-1" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
                                <path d="M8 12h8" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
                            </svg>
                            Scanner SKU
                        </RouterLink>
                        <RouterLink
                            class="flex items-center gap-3 rounded-xl px-3 py-2 text-sm font-medium text-zinc-700 hover:bg-zinc-50"
                            active-class="bg-gray-200 text-white hover:bg-gray-200"
                            to="/assessment/dashboard"
                        >
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none">
                                <path d="M5 19V9M12 19V5M19 19v-7" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
                            </svg>
                            Analitik Assessment
                        </RouterLink>
                        <RouterLink
                            class="flex items-center gap-3 rounded-xl px-3 py-2 text-sm font-medium text-zinc-700 hover:bg-zinc-50"
                            active-class="bg-gray-200 text-white hover:bg-gray-200"
                            to="/assessment/report"
                        >
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none">
                                <path d="M7 4h7l3 3v13H7V4Z" stroke="currentColor" stroke-width="2" stroke-linejoin="round" />
                                <path d="M14 4v3h3M10 12h4M10 16h4" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
                            </svg>
                            Laporan Assessment
                        </RouterLink>
                        <RouterLink
                            v-if="auth.user.value?.is_admin"
                            class="flex items-center gap-3 rounded-xl px-3 py-2 text-sm font-medium text-zinc-700 hover:bg-zinc-50"
                            active-class="bg-gray-200 text-white hover:bg-gray-200"
                            to="/users"
                        >
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none">
                                <path
                                    d="M16 11a4 4 0 1 1-8 0 4 4 0 0 1 8 0Z"
                                    stroke="currentColor"
                                    stroke-width="2"
                                />
                                <path
                                    d="M4 20a8 8 0 0 1 16 0"
                                    stroke="currentColor"
                                    stroke-width="2"
                                    stroke-linecap="round"
                                />
                            </svg>
                            User Management
                        </RouterLink>
                    </nav>
                </div>

                <div class="mt-auto border-t border-zinc-200 p-4">
                    <div class="flex items-center justify-between gap-3">
                        <div class="min-w-0">
                            <div class="truncate text-sm font-semibold text-zinc-900">{{ auth.user.value?.name }}</div>
                            <div class="truncate text-xs text-zinc-600">{{ auth.user.value?.email }}</div>
                        </div>
                        <button
                            class="rounded-xl border border-zinc-200 bg-white px-3 py-2 text-xs font-semibold text-zinc-800 hover:bg-zinc-50"
                            type="button"
                            @click="onLogout"
                        >
                            Logout
                        </button>
                    </div>
                </div>
            </aside>

            <div class="min-w-0 flex-1">
                <header class="sticky top-0 z-30 border-b border-zinc-200 bg-white/80 backdrop-blur">
                    <div class="mx-auto flex max-w-6xl items-center justify-between gap-4 px-6 py-3">
                        <div class="w-full max-w-md">
                            <div class="relative">
                                <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-zinc-400" viewBox="0 0 24 24" fill="none">
                                    <path d="M11 19a8 8 0 1 1 0-16 8 8 0 0 1 0 16Z" stroke="currentColor" stroke-width="2" />
                                    <path d="M21 21l-4.3-4.3" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
                                </svg>
                                <input
                                    class="w-full rounded-xl border border-zinc-200 bg-white px-9 py-2 text-sm outline-none focus:border-zinc-300"
                                    type="text"
                                    placeholder="Search..."
                                    v-model="search"
                                />
                            </div>
                        </div>

                        <div class="flex items-center gap-3">
                            <div class="hidden text-sm text-zinc-700 sm:block">{{ auth.user.value?.name }}</div>
                            <button
                                class="rounded-xl border border-zinc-200 bg-white px-3 py-2 text-sm font-semibold text-zinc-800 hover:bg-zinc-50 lg:hidden"
                                type="button"
                                @click="onLogout"
                            >
                                Logout
                            </button>
                        </div>
                    </div>
                </header>

                <main class="mx-auto max-w-6xl px-6 py-6">
                    <slot />
                </main>
            </div>
        </div>
    </div>
</template>

<script setup>
import { RouterLink, useRouter } from 'vue-router';
import { ref } from 'vue';
import { useAuth } from '../stores/auth';

const auth = useAuth();
const router = useRouter();
const search = ref('');

async function onLogout() {
    await auth.logout();
    router.replace('/login');
}
</script>

