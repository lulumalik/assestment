<template>
    <div class="min-h-dvh bg-zinc-50">
        <div class="mx-auto flex min-h-dvh max-w-md flex-col justify-center px-6 py-10">
            <div class="rounded-2xl border border-zinc-200 bg-white p-7 shadow-sm">
                <div class="mb-6">
                    <h1 class="text-xl font-semibold tracking-tight">Masuk</h1>
                    <p class="mt-1 text-sm text-zinc-600">Management Asset</p>
                </div>

                <form class="space-y-4" @submit.prevent="onSubmit">
                    <div>
                        <label class="text-sm font-medium text-zinc-700">Email</label>
                        <input
                            class="mt-1 w-full rounded-xl border border-zinc-200 bg-white px-3 py-2 text-sm outline-none ring-0 focus:border-zinc-300"
                            type="email"
                            placeholder="nama@perusahaan.com"
                            autocomplete="email"
                            v-model="form.email"
                        />
                    </div>
                    <div>
                        <label class="text-sm font-medium text-zinc-700">Password</label>
                        <input
                            class="mt-1 w-full rounded-xl border border-zinc-200 bg-white px-3 py-2 text-sm outline-none ring-0 focus:border-zinc-300"
                            type="password"
                            placeholder="••••••••"
                            autocomplete="current-password"
                            v-model="form.password"
                        />
                    </div>
                    <div v-if="error" class="rounded-xl border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-800">
                        {{ error }}
                    </div>
                    <button
                        class="w-full rounded-xl bg-zinc-900 px-4 py-2 text-sm font-semibold text-white hover:bg-zinc-800"
                        type="submit"
                        :disabled="loading"
                    >
                        {{ loading ? 'Memproses...' : 'Masuk' }}
                    </button>
                </form>

                <div class="mt-6 text-center text-sm text-zinc-600">
                    Belum punya akun?
                    <RouterLink class="font-semibold text-zinc-900 hover:underline" to="/register">Daftar</RouterLink>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { reactive, ref } from 'vue';
import { RouterLink, useRouter } from 'vue-router';
import { useAuth } from '../stores/auth';

const router = useRouter();
const auth = useAuth();

const loading = ref(false);
const error = ref('');
const form = reactive({
    email: '',
    password: '',
    remember: true,
});

async function onSubmit() {
    error.value = '';
    loading.value = true;
    try {
        await auth.login(form);
        router.replace('/');
    } catch (e) {
        error.value = e?.response?.data?.message || e?.response?.data?.errors?.email?.[0] || 'Gagal login.';
    } finally {
        loading.value = false;
    }
}
</script>

