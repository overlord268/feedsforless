import { createRouter, createWebHistory } from 'vue-router';
import { useAuthStore } from '../stores/auth';

const routes = [
    {
        path: '/',
        component: () => import('../layouts/PublicLayout.vue'),
        children: [
            {
                path: 'login',
                name: 'Login',
                component: () => import('../views/Login.vue'),
                meta: { requiresGuest: true }
            },
            {
                path: 'register',
                name: 'Register',
                component: () => import('../views/Register.vue'),
                meta: { requiresGuest: true }
            },
            {
                path: '',
                name: 'PublicHome',
                component: () => import('../views/public/Catalog.vue')
            },
            {
                path: 'catalog',
                name: 'Catalog',
                component: () => import('../views/public/Catalog.vue')
            },
            {
                path: 'catalog/request-quote',
                name: 'RequestQuote',
                component: () => import('../views/public/RequestQuote.vue'),
                props: (route) => {
                    const id = route.query.productId ? Number(route.query.productId) : null;
                    return { productId: id != null && !Number.isNaN(id) ? id : null };
                }
            },
            {
                path: 'account',
                name: 'Account',
                component: () => import('../views/Account.vue'),
                meta: { requiresAuth: true }
            },
            {
                path: 'addresses',
                name: 'CustomerAddresses',
                component: () => import('../views/customer/CustomerAddresses.vue'),
                meta: { requiresAuth: true }
            },
            {
                path: 'quotes',
                name: 'CustomerQuotes',
                component: () => import('../views/customer/CustomerQuotes.vue'),
                meta: { requiresAuth: true }
            }
        ]
    },
    {
        path: '/',
        component: () => import('../layouts/AppLayout.vue'),
        meta: { requiresAuth: true },
        children: [
            {
                path: 'app',
                redirect: { name: 'AppCatalog' }
            },
            {
                path: 'app/catalog',
                name: 'AppCatalog',
                component: () => import('../views/public/Catalog.vue')
            },
            {
                path: 'app/catalog/request-quote',
                name: 'AppRequestQuote',
                component: () => import('../views/public/RequestQuote.vue'),
                props: (route) => {
                    const id = route.query.productId ? Number(route.query.productId) : null;
                    return { productId: id != null && !Number.isNaN(id) ? id : null };
                }
            },
            {
                path: 'dashboard',
                name: 'Dashboard',
                component: () => import('../views/Dashboard.vue'),
                meta: { requiresAdmin: true }
            },
            {
                path: 'admin/products',
                name: 'AdminProducts',
                component: () => import('../views/admin/AdminProducts.vue'),
                meta: { requiresAdmin: true }
            },
            {
                path: 'admin/products/create',
                name: 'AdminProductCreate',
                component: () => import('../views/admin/ProductForm.vue'),
                meta: { productForm: true, requiresAdmin: true }
            },
            {
                path: 'admin/products/:id/edit',
                name: 'AdminProductEdit',
                component: () => import('../views/admin/ProductForm.vue'),
                meta: { productForm: true, requiresAdmin: true }
            },
            {
                path: 'admin/categories',
                name: 'AdminCategories',
                component: () => import('../views/admin/AdminCategories.vue'),
                meta: { requiresAdmin: true }
            },
            {
                path: 'admin/quotes',
                name: 'AdminQuotes',
                component: () => import('../views/admin/AdminQuotes.vue'),
                meta: { requiresAdmin: true }
            },
            {
                path: 'admin/quotes/:id',
                name: 'AdminQuoteDetails',
                component: () => import('../views/admin/AdminQuoteDetails.vue'),
                meta: { requiresAdmin: true }
            },
            {
                path: 'admin/users',
                name: 'AdminUsers',
                component: () => import('../views/admin/AdminUsers.vue'),
                meta: { requiresAdmin: true }
            },
            {
                path: 'admin/companies',
                name: 'AdminCompanies',
                component: () => import('../views/admin/AdminCompanies.vue'),
                meta: { requiresAdmin: true }
            },
            {
                path: 'admin/packaging-types',
                name: 'AdminPackagingTypes',
                component: () => import('../views/admin/AdminPackagingTypes.vue'),
                meta: { requiresAdmin: true }
            },
            {
                path: 'admin/specifications',
                name: 'AdminSpecifications',
                component: () => import('../views/admin/AdminSpecifications.vue'),
                meta: { requiresAdmin: true }
            },
            {
                path: 'admin/nutritional-parameters',
                name: 'AdminNutritionalParameters',
                component: () => import('../views/admin/AdminNutritionalParameters.vue'),
                meta: { requiresAdmin: true }
            },
            {
                path: 'admin/handling-specs',
                name: 'AdminHandlingSpecs',
                component: () => import('../views/admin/AdminHandlingSpecs.vue'),
                meta: { requiresAdmin: true }
            },
            {
                path: 'admin/typical-applications',
                name: 'AdminTypicalApplications',
                component: () => import('../views/admin/AdminTypicalApplications.vue'),
                meta: { requiresAdmin: true }
            },
            {
                path: 'admin/measure-units',
                name: 'AdminMeasureUnits',
                component: () => import('../views/admin/AdminMeasureUnits.vue'),
                meta: { requiresAdmin: true }
            },
            {
                path: 'admin/parameters',
                name: 'AdminParameters',
                component: () => import('../views/admin/AdminParameters.vue'),
                meta: { requiresAdmin: true }
            },
            {
                path: 'admin/test-methods',
                name: 'AdminTestMethods',
                component: () => import('../views/admin/AdminTestMethods.vue'),
                meta: { requiresAdmin: true }
            }
        ]
    },
    {
        path: '/:pathMatch(.*)*',
        redirect: '/'
    }
];

const router = createRouter({
    history: createWebHistory(),
    routes
});

function isAdminRoute(to) {
    return to.matched.some((r) => r.meta && r.meta.requiresAdmin);
}

function userIsAdmin(authStore) {
    const u = authStore.user;
    if (!u || !u.roles || !Array.isArray(u.roles)) return false;
    return u.roles.some((r) => r.name === 'admin');
}

router.beforeEach(async (to, from) => {
    const authStore = useAuthStore();
    const isAuthenticated = !!authStore.token;

    if (to.meta.requiresAuth && !isAuthenticated) {
        return { path: '/', query: to.query };
    }

    if (to.meta.requiresGuest && isAuthenticated) {
        if (userIsAdmin(authStore)) {
            return { name: 'Dashboard' };
        }
        return { name: 'Catalog' };
    }

    // Admin routes: only users with admin role
    if (isAuthenticated && isAdminRoute(to)) {
        if (!authStore.user && authStore.token) {
            try {
                await authStore.fetchUser();
            } catch {
                return { path: '/' };
            }
        }
        if (!userIsAdmin(authStore)) {
            return { name: 'Catalog' };
        }
    }

    // Logged in as ADMIN: public catalog → use app layout (/app/catalog)
    if (isAuthenticated && userIsAdmin(authStore) && (to.name === 'PublicHome' || to.name === 'Catalog' || to.name === 'RequestQuote')) {
        if (to.name === 'RequestQuote') {
            return { name: 'AppRequestQuote', query: to.query };
        }
        return { name: 'AppCatalog', query: to.query };
    }

    // Not logged in or regular user: if accessing /app/catalog, redirect to public catalog
    if (to.path.startsWith('/app') && (!isAuthenticated || !userIsAdmin(authStore))) {
        const path = to.path.replace(/^\/app/, '') || '/';
        return { path, query: to.query };
    }
});

export default router;