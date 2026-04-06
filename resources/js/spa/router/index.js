import { createRouter, createWebHistory } from 'vue-router';
import { useAuth } from '../stores/auth';
import AssetFormPage from '../views/AssetFormPage.vue';
import AssessmentAnalyticsPage from '../views/AssessmentAnalyticsPage.vue';
import AssessmentReportPage from '../views/AssessmentReportPage.vue';
import AssetsListPage from '../views/AssetsListPage.vue';
import DashboardPage from '../views/DashboardPage.vue';
import LoginPage from '../views/LoginPage.vue';
import RegisterPage from '../views/RegisterPage.vue';
import ScanPage from '../views/ScanPage.vue';
import UsersPage from '../views/UsersPage.vue';

export function createSpaRouter() {
    const router = createRouter({
        history: createWebHistory(),
        routes: [
            { path: '/login', name: 'login', component: LoginPage, meta: { public: true } },
            { path: '/register', name: 'register', component: RegisterPage, meta: { public: true } },

            { path: '/', name: 'dashboard', component: DashboardPage, meta: { requiresAuth: true } },
            { path: '/assets', name: 'assets', component: AssetsListPage, meta: { requiresAuth: true } },
            { path: '/assets/new', name: 'asset.new', component: AssetFormPage, meta: { requiresAuth: true } },
            { path: '/assets/:id/edit', name: 'asset.edit', component: AssetFormPage, meta: { requiresAuth: true } },
            { path: '/scan', name: 'scan', component: ScanPage, meta: { requiresAuth: true } },
            { path: '/assessment/dashboard', name: 'assessment.dashboard', component: AssessmentAnalyticsPage, meta: { requiresAuth: true } },
            { path: '/assessment/report', name: 'assessment.report', component: AssessmentReportPage, meta: { requiresAuth: true } },
            { path: '/users', name: 'users', component: UsersPage, meta: { requiresAuth: true, requiresAdmin: true } },
        ],
    });

    router.beforeEach(async (to) => {
        const auth = useAuth();
        await auth.ensureLoaded();

        if (to.meta.public && auth.isAuthenticated.value) {
            return { path: '/' };
        }

        if (to.meta.requiresAuth && !auth.isAuthenticated.value) {
            return { path: '/login' };
        }

        if (to.meta.requiresAdmin && !auth.user.value?.is_admin) {
            return { path: '/' };
        }
    });

    return router;
}

