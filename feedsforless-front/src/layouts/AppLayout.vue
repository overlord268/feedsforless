<template>
  <div class="min-h-screen bg-slate-50 flex font-sans text-slate-900">
    <Sidebar />
    <div class="flex-1 flex flex-col min-w-0 overflow-hidden">
      <!-- Top header -->
      <header class="bg-white border-b border-slate-200 h-16 flex items-center justify-between px-6 lg:px-10 shrink-0 shadow-sm z-10 w-full">
        <h1 class="text-xl font-semibold text-slate-800 tracking-tight">{{ currentRouteName }}</h1>
        <div class="flex items-center gap-4">
            <!-- Search placeholder -->
            <div class="relative hidden sm:block">
                <input type="text" placeholder="Buscar..." class="w-64 pl-10 pr-4 py-2 border border-slate-200 rounded-lg bg-slate-50 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500 transition-all">
                <svg class="w-4 h-4 text-slate-400 absolute left-3 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
            </div>
            
            <!-- Notifications -->
            <button class="relative p-2 text-slate-400 hover:text-slate-600 transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
                <span class="absolute top-1 right-1 w-2.5 h-2.5 bg-red-500 rounded-full border-2 border-white"></span>
            </button>

            <div class="h-8 w-px bg-slate-200 mx-1"></div>

            <div class="flex items-center gap-3 cursor-pointer hover:bg-slate-50 p-1.5 rounded-lg transition-colors">
                <div class="h-9 w-9 bg-gradient-to-tr from-blue-600 to-blue-400 rounded-full flex items-center justify-center border-2 border-white shadow-sm">
                    <span class="text-sm font-bold text-white tracking-widest leading-none">A</span>
                </div>
                <div class="hidden md:block">
                    <p class="text-sm font-medium text-slate-700 leading-none mb-1">Admin Panel</p>
                    <p class="text-xs text-slate-500 leading-none">FeedsForLess</p>
                </div>
            </div>
        </div>
      </header>
      
      <!-- Main Content -->
      <main class="flex-1 overflow-y-auto p-6 lg:p-10 w-full">
        <div class="max-w-7xl mx-auto w-full">
          <router-view></router-view>
        </div>
      </main>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { useRoute } from 'vue-router';
import Sidebar from '../components/layout/Sidebar.vue';

const route = useRoute();
const currentRouteName = computed(() => {
    return route.name || 'Dashboard Overview';
});
</script>