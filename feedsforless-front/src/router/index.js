import { createRouter, createWebHistory } from 'vue-router';
import { useAuthStore } from '../stores/auth';

const routes = [
    {
        path: '/login',
        name: 'Login',
        component: () => import('../views/Login.vue'),
        meta: { requiresGuest: true }
    },
    {
        path: '/register',
        name: 'Register',
        component: () => import('../views/Register.vue'),
        meta: { requiresGuest: true }
    },
    {
        path: '/',
        component: () => import('../layouts/AppLayout.vue'),
        meta: { requiresAuth: true },
        children: [
            {
                path: 'dashboard',
                name: 'Dashboard',
                component: () => import('../views/Dashboard.vue')
            },
            {
                path: 'catalog',
                name: 'Catalog',
                component: () => import('../views/public/Catalog.vue')
            },
            {
                path: 'admin/products',
                name: 'AdminProducts',
                component: () => import('../views/admin/AdminProducts.vue')
            },
            {
                path: 'admin/categories',
                name: 'AdminCategories',
                component: () => import('../views/admin/AdminCategories.vue')
            },
            {
                path: 'admin/quotes',
                name: 'AdminQuotes',
                component: () => import('../views/admin/AdminQuotes.vue')
            },
            {
                path: 'admin/users',
                name: 'AdminUsers',
                component: () => import('../views/admin/AdminUsers.vue')
            },
            {
                path: 'admin/companies',
                name: 'AdminCompanies',
                component: () => import('../views/admin/AdminCompanies.vue')
            },
            {
                path: 'admin/packaging-types',
                name: 'AdminPackagingTypes',
                component: () => import('../views/admin/AdminPackagingTypes.vue')
            },
            {
                path: 'admin/specifications',
                name: 'AdminSpecifications',
                component: () => import('../views/admin/AdminSpecifications.vue')
            },
            {
                path: 'admin/nutritional-analysis',
                name: 'AdminNutritionalAnalysis',
                component: () => import('../views/admin/AdminNutritionalAnalysis.vue')
            },
            {
                path: 'admin/handling-specs',
                name: 'AdminHandlingSpecs',
                component: () => import('../views/admin/AdminHandlingSpecs.vue')
            },
            {
                path: 'admin/typical-applications',
                name: 'AdminTypicalApplications',
                component: () => import('../views/admin/AdminTypicalApplications.vue')
            },
            {
                path: 'admin/measure-units',
                name: 'AdminMeasureUnits',
                component: () => import('../views/admin/AdminMeasureUnits.vue')
            },
            {
                path: 'admin/parameters',
                name: 'AdminParameters',
                component: () => import('../views/admin/AdminParameters.vue')
            },
            {
                path: 'admin/test-methods',
                name: 'AdminTestMethods',
                component: () => import('../views/admin/AdminTestMethods.vue')
            },
            {
                path: 'addresses',
                name: 'CustomerAddresses',
                component: () => import('../views/customer/CustomerAddresses.vue')
            },
            {
                path: 'quotes',
                name: 'CustomerQuotes',
                component: () => import('../views/customer/CustomerQuotes.vue')
            }
        ]
    },
    {
        path: '/:pathMatch(.*)*',
        redirect: '/dashboard'
    }
];

const router = createRouter({
    history: createWebHistory(),
    routes
});

router.beforeEach((to, from) => {
    const authStore = useAuthStore();
    const isAuthenticated = !!authStore.token;

    if (to.meta.requiresAuth && !isAuthenticated) {
        return { name: 'Login' };
    }

    if (to.meta.requiresGuest && isAuthenticated) {
        return { name: 'Dashboard' };
    }
});

export default router;