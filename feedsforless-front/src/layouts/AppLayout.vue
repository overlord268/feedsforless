<template>
  <div class="min-h-dvh bg-slate-100 dark:bg-slate-900 flex font-sans text-slate-900 dark:text-slate-100 overflow-x-hidden">
    <div class="fixed inset-0 z-30 bg-black/50 transition-opacity md:hidden" :class="sidebarOpen ? 'opacity-100' : 'opacity-0 pointer-events-none'" aria-hidden="true" @click="sidebarOpen = false"></div>
    <Sidebar :open="isDesktop || sidebarOpen" @close="sidebarOpen = false" />
    <div class="flex-1 flex flex-col min-w-0 overflow-hidden min-h-dvh">
      <header class="relative z-50 bg-white dark:bg-slate-800 border-b border-slate-200 dark:border-slate-700 h-14 flex items-center justify-between gap-2 sm:gap-3 px-3 sm:px-4 md:px-5 lg:px-8 shrink-0 shadow-sm w-full">
        <button type="button" class="md:hidden p-2 -ml-1 text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-lg touch-manipulation shrink-0" aria-label="Open menu" @click="sidebarOpen = true">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
        </button>
        <div class="relative flex-1 min-w-0 max-w-xl hidden sm:block">
          <svg class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
          <input type="text" placeholder="Search..." class="w-full pl-9 md:pl-10 pr-3 md:pr-4 py-2 md:py-2.5 border border-slate-200 dark:border-slate-600 rounded-lg md:rounded-xl bg-slate-50 dark:bg-slate-700 text-slate-900 dark:text-white placeholder-slate-400 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 transition-all" />
        </div>
        <div class="flex items-center gap-1 sm:gap-2 ml-auto shrink-0">
          <AdminChatNotifications v-if="isAdmin" />
          <AdminQuoteNotifications v-if="isAdmin" />
          <div v-if="isAdmin" class="hidden sm:block h-6 w-px bg-slate-200 dark:bg-slate-600"></div>
          <UserMenu />
        </div>
      </header>

      <main class="flex-1 flex flex-col min-h-0 w-full overflow-y-auto bg-slate-50/50 dark:bg-slate-900/50" :class="isFullBleed ? 'overflow-hidden p-3 sm:p-4 lg:p-5' : 'p-3 sm:p-5 md:p-6 lg:p-8'">
        <div class="mx-auto w-full flex-1 flex flex-col min-h-0" :class="isFullBleed ? 'max-w-none' : 'max-w-6xl'">
          <router-view :key="$route.fullPath" class="flex-1 flex flex-col min-h-0"></router-view>
        </div>
      </main>
    </div>
  </div>
</template>

<script setup>
import { computed, ref, watch, onMounted, onUnmounted } from 'vue';
import { useRoute } from 'vue-router';
import { useAuthStore } from '../stores/auth';
import Sidebar from '../components/layout/Sidebar.vue';
import AdminQuoteNotifications from '../components/layout/AdminQuoteNotifications.vue';
import AdminChatNotifications from '../components/layout/AdminChatNotifications.vue';
import UserMenu from '../components/layout/UserMenu.vue';
import { useAdminChatNotifier } from '../composables/useAdminChatNotifier';
import { installNotificationSoundUnlock } from '../composables/useNotificationSound';

const route = useRoute();
const authStore = useAuthStore();
const sidebarOpen = ref(false);
const isDesktop = ref(typeof window !== 'undefined' && window.matchMedia('(min-width: 768px)').matches);
const { startPolling, stopPolling } = useAdminChatNotifier();
let removeSoundUnlock = null;

const isFullBleed = computed(() => !!(route.meta.productForm || route.meta.fullBleed));

function updateIsDesktop() {
  isDesktop.value = window.matchMedia('(min-width: 768px)').matches;
  if (isDesktop.value) sidebarOpen.value = false;
}
onMounted(() => {
  window.addEventListener('resize', updateIsDesktop);
  removeSoundUnlock = installNotificationSoundUnlock();
});
onUnmounted(() => {
  window.removeEventListener('resize', updateIsDesktop);
  removeSoundUnlock?.();
  stopPolling();
});
watch(() => route.path, () => { sidebarOpen.value = false; });

const isAdmin = computed(() => {
  if (!authStore.user || !authStore.user.roles) return false;
  return authStore.user.roles.some(role => ['admin', 'Admin', 'Super Admin'].includes(role.name));
});

watch(isAdmin, (admin) => {
  if (admin) startPolling(4000);
  else stopPolling();
}, { immediate: true });
</script>
