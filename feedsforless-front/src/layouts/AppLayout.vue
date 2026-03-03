<template>
    <div class="flex min-h-screen bg-background">
        <Sidebar />

        <div class="flex-1 flex flex-col min-w-0 overflow-hidden">
            <header class="h-16 bg-surface border-b border-slate-200 flex items-center justify-between px-8 shadow-sm">
                <div class="flex-1"></div>
                
                <div class="flex items-center gap-4">
                    <div class="text-right" v-if="authStore.user">
                        <p class="text-sm font-bold text-slate-900 leading-none">
                            {{ authStore.user.first_name }} {{ authStore.user.last_name }}
                        </p>
                        <p class="text-xs text-slate-500 mt-1 capitalize">
                            {{ isAdmin ? 'Administrator' : 'Customer' }}
                        </p>
                    </div>
                    
                    <button 
                        @click="handleLogout"
                        class="px-4 py-2 text-sm font-medium text-slate-700 bg-white border border-slate-300 rounded-md hover:bg-red-50 hover:text-red-600 hover:border-red-200 transition-all"
                    >
                        Logout
                    </button>
                </div>
            </header>

            <main class="flex-1 overflow-y-auto p-8">
                <router-view />
            </main>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue';
import { useAuthStore } from '../stores/auth';
import Sidebar from '../components/layout/Sidebar.vue';

const authStore = useAuthStore();
const isAdmin = computed(() => authStore.user?.roles?.some(r => r.name === 'admin'));

const handleLogout = async () => {
    await authStore.logout();
};
</script>