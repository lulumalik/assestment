import { computed, reactive } from 'vue';

const state = reactive({
    user: null,
    loaded: false,
    loading: false,
});

let loadingPromise = null;

async function loadMe() {
    try {
        const { data } = await window.axios.get('/api/auth/me');
        state.user = data.user;
    } catch {
        state.user = null;
    } finally {
        state.loaded = true;
    }
}

export function useAuth() {
    async function ensureLoaded() {
        if (state.loaded) return;
        if (!loadingPromise) {
            state.loading = true;
            loadingPromise = loadMe().finally(() => {
                state.loading = false;
                loadingPromise = null;
            });
        }
        await loadingPromise;
    }

    async function login(payload) {
        const { data } = await window.axios.post('/api/auth/login', payload);
        state.user = data.user;
        state.loaded = true;
        return data.user;
    }

    async function register(payload) {
        const { data } = await window.axios.post('/api/auth/register', payload);
        state.user = data.user;
        state.loaded = true;
        return data.user;
    }

    async function logout() {
        await window.axios.post('/api/auth/logout');
        state.user = null;
        state.loaded = true;
    }

    return {
        state,
        user: computed(() => state.user),
        isAuthenticated: computed(() => !!state.user),
        isLoading: computed(() => state.loading),
        ensureLoaded,
        login,
        register,
        logout,
    };
}

