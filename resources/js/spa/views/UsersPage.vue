<template>
    <AppShell>
        <div class="rounded-2xl border border-zinc-200 bg-white p-6">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-xl font-semibold tracking-tight">User Management</h1>
                    <p class="mt-1 text-sm text-zinc-600">Kelola akun user dan akses admin.</p>
                </div>
                <button
                    class="rounded-xl bg-zinc-900 px-4 py-2 text-sm font-semibold text-white hover:bg-zinc-800"
                    type="button"
                    @click="openCreate"
                >
                    Tambah User
                </button>
            </div>

            <div class="mt-5 flex items-center gap-2">
                <input
                    class="w-full rounded-xl border border-zinc-200 bg-white px-3 py-2 text-sm outline-none focus:border-zinc-300"
                    type="text"
                    placeholder="Cari (nama / email)..."
                    v-model="q"
                    @keydown.enter="fetchUsers(1)"
                />
                <button
                    class="shrink-0 rounded-xl border border-zinc-200 bg-white px-3 py-2 text-sm font-semibold text-zinc-800 hover:bg-zinc-50"
                    type="button"
                    @click="fetchUsers(1)"
                >
                    Cari
                </button>
            </div>

            <div class="mt-5 overflow-x-auto">
                <table class="min-w-full text-left text-sm">
                    <thead class="text-xs uppercase text-zinc-500">
                        <tr>
                            <th class="py-2">Nama</th>
                            <th class="py-2">Email</th>
                            <th class="py-2">Role</th>
                            <th class="py-2 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="item in users.data" :key="item.id" class="border-t border-zinc-100">
                            <td class="py-3 font-medium text-zinc-900">{{ item.name }}</td>
                            <td class="py-3 text-zinc-700">{{ item.email }}</td>
                            <td class="py-3">
                                <span
                                    class="inline-flex items-center rounded-full border px-2 py-0.5 text-xs font-semibold"
                                    :class="item.is_admin ? 'border-emerald-200 bg-emerald-50 text-emerald-800' : 'border-zinc-200 bg-zinc-50 text-zinc-700'"
                                >
                                    {{ item.is_admin ? 'Admin' : 'User' }}
                                </span>
                            </td>
                            <td class="py-3 text-right">
                                <button
                                    class="rounded-lg border border-zinc-200 bg-white px-3 py-1.5 text-xs font-semibold text-zinc-800 hover:bg-zinc-50"
                                    type="button"
                                    @click="openEdit(item)"
                                >
                                    Edit
                                </button>
                            </td>
                        </tr>
                        <tr v-if="!loading && users.data.length === 0">
                            <td class="py-6 text-center text-sm text-zinc-600" colspan="4">Belum ada data.</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="mt-6 flex items-center justify-between">
                <button
                    class="rounded-xl border border-zinc-200 bg-white px-3 py-2 text-sm font-semibold text-zinc-800 disabled:opacity-50"
                    type="button"
                    :disabled="!users.prev_page_url || loading"
                    @click="fetchUsers(users.current_page - 1)"
                >
                    Prev
                </button>
                <div class="text-sm text-zinc-600">Halaman {{ users.current_page }} / {{ users.last_page }}</div>
                <button
                    class="rounded-xl border border-zinc-200 bg-white px-3 py-2 text-sm font-semibold text-zinc-800 disabled:opacity-50"
                    type="button"
                    :disabled="!users.next_page_url || loading"
                    @click="fetchUsers(users.current_page + 1)"
                >
                    Next
                </button>
            </div>
        </div>

        <div v-if="modalOpen" class="fixed inset-0 z-50 grid place-items-center bg-black/40 p-6">
            <div class="w-full max-w-lg rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <div class="text-lg font-semibold tracking-tight">
                            {{ editingId ? 'Edit User' : 'Tambah User' }}
                        </div>
                        <div class="mt-1 text-sm text-zinc-600">
                            {{ editingId ? 'Ubah data user.' : 'Buat akun user baru.' }}
                        </div>
                    </div>
                    <button class="rounded-xl border border-zinc-200 bg-white px-3 py-2 text-sm font-semibold text-zinc-800 hover:bg-zinc-50" type="button" @click="closeModal">
                        Tutup
                    </button>
                </div>

                <form class="mt-5 space-y-4" @submit.prevent="saveUser">
                    <div>
                        <label class="text-sm font-medium text-zinc-700">Nama</label>
                        <input
                            class="mt-1 w-full rounded-xl border border-zinc-200 bg-white px-3 py-2 text-sm outline-none focus:border-zinc-300"
                            type="text"
                            v-model="form.name"
                        />
                    </div>
                    <div>
                        <label class="text-sm font-medium text-zinc-700">Email</label>
                        <input
                            class="mt-1 w-full rounded-xl border border-zinc-200 bg-white px-3 py-2 text-sm outline-none focus:border-zinc-300"
                            type="email"
                            v-model="form.email"
                        />
                    </div>
                    <div>
                        <label class="text-sm font-medium text-zinc-700">{{ editingId ? 'Password (opsional)' : 'Password' }}</label>
                        <input
                            class="mt-1 w-full rounded-xl border border-zinc-200 bg-white px-3 py-2 text-sm outline-none focus:border-zinc-300"
                            type="password"
                            v-model="form.password"
                        />
                    </div>
                    <label class="flex items-center gap-2 text-sm text-zinc-700">
                        <input class="h-4 w-4 rounded border-zinc-300" type="checkbox" v-model="form.is_admin" />
                        Jadikan Admin
                    </label>

                    <div v-if="error" class="rounded-xl border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-800">
                        {{ error }}
                    </div>

                    <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                        <button
                            class="rounded-xl bg-zinc-900 px-4 py-2 text-sm font-semibold text-white hover:bg-zinc-800 disabled:opacity-60"
                            type="submit"
                            :disabled="saving"
                        >
                            {{ saving ? 'Menyimpan...' : 'Simpan' }}
                        </button>

                        <button
                            v-if="editingId"
                            class="rounded-xl border border-red-200 bg-white px-4 py-2 text-sm font-semibold text-red-700 hover:bg-red-50 disabled:opacity-60"
                            type="button"
                            :disabled="saving"
                            @click="deleteUser"
                        >
                            Hapus
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AppShell>
</template>

<script setup>
import { onMounted, reactive, ref } from 'vue';
import AppShell from '../layouts/AppShell.vue';

const q = ref('');
const loading = ref(false);
const users = reactive({
    data: [],
    current_page: 1,
    last_page: 1,
    next_page_url: null,
    prev_page_url: null,
});

const modalOpen = ref(false);
const editingId = ref(null);
const saving = ref(false);
const error = ref('');
const form = reactive({
    name: '',
    email: '',
    password: '',
    is_admin: false,
});

async function fetchUsers(page = 1) {
    loading.value = true;
    try {
        const { data } = await window.axios.get('/api/users', {
            params: { page, q: q.value || undefined },
        });
        Object.assign(users, data);
    } finally {
        loading.value = false;
    }
}

function openCreate() {
    editingId.value = null;
    form.name = '';
    form.email = '';
    form.password = '';
    form.is_admin = false;
    error.value = '';
    modalOpen.value = true;
}

function openEdit(item) {
    editingId.value = item.id;
    form.name = item.name;
    form.email = item.email;
    form.password = '';
    form.is_admin = !!item.is_admin;
    error.value = '';
    modalOpen.value = true;
}

function closeModal() {
    modalOpen.value = false;
}

async function saveUser() {
    saving.value = true;
    error.value = '';
    try {
        if (editingId.value) {
            await window.axios.put(`/api/users/${editingId.value}`, {
                name: form.name,
                email: form.email,
                password: form.password || null,
                is_admin: form.is_admin,
            });
        } else {
            await window.axios.post('/api/users', {
                name: form.name,
                email: form.email,
                password: form.password,
                is_admin: form.is_admin,
            });
        }
        closeModal();
        await fetchUsers(users.current_page);
    } catch (e) {
        error.value = e?.response?.data?.message || 'Gagal menyimpan.';
    } finally {
        saving.value = false;
    }
}

async function deleteUser() {
    if (!editingId.value) return;
    if (!confirm('Hapus user ini?')) return;
    saving.value = true;
    error.value = '';
    try {
        await window.axios.delete(`/api/users/${editingId.value}`);
        closeModal();
        await fetchUsers(users.current_page);
    } catch (e) {
        error.value = e?.response?.data?.message || 'Gagal menghapus.';
    } finally {
        saving.value = false;
    }
}

onMounted(() => fetchUsers(1));
</script>

