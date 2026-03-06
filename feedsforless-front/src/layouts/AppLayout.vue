<template>
  <div class="min-h-screen bg-slate-100 flex font-sans text-slate-900">
    <div class="fixed inset-0 z-30 bg-black/50 transition-opacity md:hidden" :class="sidebarOpen ? 'opacity-100' : 'opacity-0 pointer-events-none'" aria-hidden="true" @click="sidebarOpen = false"></div>
    <Sidebar :open="isDesktop || sidebarOpen" @close="sidebarOpen = false" />
    <div class="flex-1 flex flex-col min-w-0 overflow-hidden min-h-screen">
      <header class="bg-white border-b border-slate-200 h-14 flex items-center justify-between gap-3 px-3 sm:px-4 md:px-5 lg:px-8 shrink-0 shadow-sm z-10 w-full">
        <button type="button" class="md:hidden p-2 -ml-1 text-slate-600 hover:bg-slate-100 rounded-lg touch-manipulation shrink-0" aria-label="Open menu" @click="sidebarOpen = true">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
        </button>
        <div class="relative flex-1 min-w-0 max-w-xl">
          <svg class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
          <input type="text" placeholder="Search..." class="w-full pl-9 md:pl-10 pr-3 md:pr-4 py-2 md:py-2.5 border border-slate-200 rounded-lg md:rounded-xl bg-slate-50 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 transition-all" />
        </div>
        <div class="flex items-center gap-1 sm:gap-2 ml-2 md:ml-4 shrink-0">
          <button type="button" class="p-2 md:p-2.5 text-slate-500 hover:text-slate-700 hover:bg-slate-100 rounded-lg md:rounded-xl transition-colors relative touch-manipulation min-h-[44px] min-w-[44px] md:min-h-0 md:min-w-0 flex items-center justify-center">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
            <span class="absolute top-1.5 right-1.5 w-2 h-2 bg-red-500 rounded-full"></span>
          </button>
          <div class="h-6 w-px bg-slate-200"></div>
          <div class="flex items-center gap-2 pl-2 cursor-pointer hover:bg-slate-50 rounded-xl py-1.5 pr-2 transition-colors">
            <div class="h-9 w-9 bg-emerald-500 rounded-full flex items-center justify-center text-white font-semibold text-sm shrink-0">
              {{ userInitial }}
            </div>
            <div class="hidden sm:block text-left">
              <p class="text-sm font-medium text-slate-800 leading-none">Admin</p>
              <p class="text-xs text-slate-500 leading-none">FeedsForLess</p>
            </div>
            <svg class="w-4 h-4 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
          </div>
        </div>
      </header>

      <main class="flex-1 flex flex-col min-h-0 p-4 sm:p-5 md:p-6 lg:p-8 w-full overflow-y-auto bg-slate-50/50" :class="{ 'overflow-hidden': route.meta.productForm }">
        <div class="max-w-6xl mx-auto w-full flex-1 flex flex-col min-h-0" :class="{ 'max-w-none': route.meta.productForm }">
          <router-view class="flex-1 flex flex-col min-h-0"></router-view>
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

const route = useRoute();
const authStore = useAuthStore();
const sidebarOpen = ref(false);
const isDesktop = ref(typeof window !== 'undefined' && window.matchMedia('(min-width: 768px)').matches);

function updateIsDesktop() {
  isDesktop.value = window.matchMedia('(min-width: 768px)').matches;
  if (isDesktop.value) sidebarOpen.value = false;
}
onMounted(() => {
  window.addEventListener('resize', updateIsDesktop);
});
onUnmounted(() => {
  window.removeEventListener('resize', updateIsDesktop);
});
watch(() => route.path, () => { sidebarOpen.value = false; });

const userInitial = computed(() => {
  const u = authStore.user;
  if (u?.first_name) return u.first_name[0].toUpperCase();
  if (u?.name) return String(u.name)[0].toUpperCase();
  if (u?.email) return u.email[0].toUpperCase();
  return 'A';
});
</script>
