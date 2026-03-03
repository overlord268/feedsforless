<template>
    <aside class="flex flex-col w-64 min-h-screen bg-primary text-surface">
        <div class="h-16 flex items-center px-6 border-b border-white/10">
            <h2 class="text-xl font-bold tracking-tight">FeedsForLess</h2>
        </div>
        
        <nav class="flex-1 p-4 space-y-8">
            <div class="space-y-2">
                <h3 class="px-3 text-xs font-semibold text-slate-400 uppercase tracking-wider">Menu</h3>
                <router-link to="/dashboard" class="flex items-center px-3 py-2 text-sm font-medium rounded-md hover:bg-white/10 transition-colors">
                    Dashboard
                </router-link>
            </div>

            <div v-if="isAdmin" class="space-y-2">
                <h3 class="px-3 text-xs font-semibold text-slate-400 uppercase tracking-wider">Admin Panel</h3>
                <router-link to="/admin/products" class="flex items-center px-3 py-2 text-sm font-medium rounded-md hover:bg-white/10 transition-colors">
                    Products
                </router-link>
                <router-link to="/admin/categories" class="flex items-center px-3 py-2 text-sm font-medium rounded-md hover:bg-white/10 transition-colors">
                    Categories
                </router-link>
                <router-link to="/admin/quotes" class="flex items-center px-3 py-2 text-sm font-medium rounded-md hover:bg-white/10 transition-colors">
                    Quote Requests
                </router-link>
                <router-link to="/admin/users" class="flex items-center px-3 py-2 text-sm font-medium rounded-md hover:bg-white/10 transition-colors">
                    Users & Roles
                </router-link>
            </div>

            <div v-else class="space-y-2">
                <h3 class="px-3 text-xs font-semibold text-slate-400 uppercase tracking-wider">B2B Portal</h3>
                <router-link to="/catalog" class="flex items-center px-3 py-2 text-sm font-medium rounded-md hover:bg-white/10 transition-colors">
                    Catalog
                </router-link>
                <router-link to="/quotes" class="flex items-center px-3 py-2 text-sm font-medium rounded-md hover:bg-white/10 transition-colors">
                    My Quotes
                </router-link>
                <router-link to="/addresses" class="flex items-center px-3 py-2 text-sm font-medium rounded-md hover:bg-white/10 transition-colors">
                    Addresses
                </router-link>
            </div>
        </nav>
    </aside>
</template>

<script setup>
import { computed } from 'vue';
import { useAuthStore } from '../../stores/auth';

const authStore = useAuthStore();
const isAdmin = computed(() => authStore.user?.roles?.some(r => r.name === 'admin'));
</script>