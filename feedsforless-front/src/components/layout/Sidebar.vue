<template>
  <aside
    class="fixed md:relative left-0 top-0 bottom-0 w-72 flex-shrink-0 bg-slate-100 dark:bg-slate-800 text-slate-800 dark:text-white flex flex-col border-r border-slate-200 dark:border-slate-700 transition-transform duration-300 ease-out z-40 md:!translate-x-0"
    :class="open ? 'translate-x-0' : '-translate-x-full'"
    :aria-hidden="!open"
  >
    <div class="h-14 md:h-16 flex items-center justify-between px-4 md:px-5 border-b border-slate-200 dark:border-slate-700 shrink-0">
      <div class="flex items-center min-w-0">
        <div class="h-8 w-8 bg-emerald-500 rounded-lg flex items-center justify-center mr-3 shrink-0">
          <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
        </div>
        <span class="font-bold text-lg tracking-wide truncate text-slate-800 dark:text-white">FeedsFor<span class="text-emerald-600 dark:text-emerald-400">Less</span></span>
      </div>
      <button
        type="button"
        class="md:hidden p-2 -mr-2 text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white rounded-lg transition-colors touch-manipulation"
        aria-label="Close menu"
        @click="$emit('close')"
      >
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
      </button>
    </div>
    <div class="p-3 border-b border-slate-200 dark:border-slate-700 shrink-0 hidden md:block">
      <div class="relative">
        <svg class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
        <input type="text" placeholder="Search for a product" class="w-full pl-9 pr-3 py-2 bg-slate-200/80 dark:bg-slate-700/80 border border-slate-300 dark:border-slate-600 rounded-lg text-sm text-slate-900 dark:text-white placeholder-slate-500 focus:outline-none focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500" />
      </div>
    </div>

    <div class="flex-1 overflow-y-auto py-4 px-3 space-y-6 overflow-x-hidden">
      <nav class="space-y-0.5">
        <router-link to="/dashboard" :class="linkClass('/dashboard')" class="flex items-center py-3 min-h-[44px] md:py-2.5 md:min-h-0" @click="$emit('close')">
          <svg class="w-5 h-5 mr-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
          Dashboard
        </router-link>
      </nav>

      <template v-if="isAdmin">
        <div>
          <router-link to="/admin/quotes" :class="linkClass('/admin/quotes')" class="flex items-center py-3 min-h-[44px] md:py-2.5 md:min-h-0" @click="$emit('close')">
            <svg class="w-5 h-5 mr-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            Quotes (RFQs)
          </router-link>
        </div>

        <div>
          <p class="px-3 text-[11px] font-bold text-slate-500 dark:text-slate-500 uppercase tracking-widest mb-2">Main Catalog</p>
          <nav class="space-y-0.5">
            <router-link to="/app/catalog" :class="linkClass('/app/catalog')" class="flex items-center py-3 min-h-[44px] md:py-2.5 md:min-h-0" @click="$emit('close')">
              <svg class="w-5 h-5 mr-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
              Browse Catalog
            </router-link>
            <router-link to="/admin/products" :class="linkClass('/admin/products')" class="flex items-center py-3 min-h-[44px] md:py-2.5 md:min-h-0" @click="$emit('close')">
              <svg class="w-5 h-5 mr-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
              Products
            </router-link>
            <router-link to="/admin/categories" :class="linkClass('/admin/categories')" class="flex items-center py-3 min-h-[44px] md:py-2.5 md:min-h-0" @click="$emit('close')">
              <svg class="w-5 h-5 mr-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
              Categories
            </router-link>
          </nav>
        </div>

        <div>
          <router-link to="/admin/companies" :class="linkClass('/admin/companies')" class="flex items-center py-3 min-h-[44px] md:py-2.5 md:min-h-0" @click="$emit('close')">
            <svg class="w-5 h-5 mr-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
            Companies
          </router-link>
        </div>
        <div>
          <router-link to="/admin/users" :class="linkClass('/admin/users')" class="flex items-center py-3 min-h-[44px] md:py-2.5 md:min-h-0" @click="$emit('close')">
            <svg class="w-5 h-5 mr-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
            System
          </router-link>
        </div>

        <div>
          <p class="px-3 text-[11px] font-bold text-slate-500 uppercase tracking-widest mb-2">Nutritional analysis</p>
          <nav class="space-y-0.5">
            <router-link to="/admin/nutritional-parameters" :class="linkClass('/admin/nutritional-parameters')" class="flex items-center py-3 min-h-[44px] md:py-2.5 md:min-h-0" @click="$emit('close')">Parameter</router-link>
          </nav>
        </div>
        <div>
          <p class="px-3 text-[11px] font-bold text-slate-500 uppercase tracking-widest mb-2">Technical Config</p>
          <nav class="space-y-0.5">
            <router-link to="/admin/parameters" :class="linkClass('/admin/parameters')" class="flex items-center py-3 min-h-[44px] md:py-2.5 md:min-h-0" @click="$emit('close')">Parameters</router-link>
            <router-link to="/admin/test-methods" :class="linkClass('/admin/test-methods')" class="flex items-center py-3 min-h-[44px] md:py-2.5 md:min-h-0" @click="$emit('close')">Methods</router-link>
            <router-link to="/admin/packaging-types" :class="linkClass('/admin/packaging-types')" class="flex items-center py-3 min-h-[44px] md:py-2.5 md:min-h-0" @click="$emit('close')">Packaging</router-link>
            <router-link to="/admin/measure-units" :class="linkClass('/admin/measure-units')" class="flex items-center py-3 min-h-[44px] md:py-2.5 md:min-h-0" @click="$emit('close')">Units</router-link>
            <router-link to="/admin/handling-specs" :class="linkClass('/admin/handling-specs')" class="flex items-center py-3 min-h-[44px] md:py-2.5 md:min-h-0" @click="$emit('close')">Handling Specs</router-link>
            <router-link to="/admin/typical-applications" :class="linkClass('/admin/typical-applications')" class="flex items-center py-3 min-h-[44px] md:py-2.5 md:min-h-0" @click="$emit('close')">Typical Applications</router-link>
          </nav>
        </div>
      </template>

      <template v-else>
        <nav class="space-y-0.5">
          <router-link to="/app/catalog" :class="linkClass('/app/catalog')" class="flex items-center py-3 min-h-[44px] md:py-2.5 md:min-h-0" @click="$emit('close')">Catalog</router-link>
          <router-link to="/quotes" :class="linkClass('/quotes')" class="flex items-center py-3 min-h-[44px] md:py-2.5 md:min-h-0" @click="$emit('close')">My Quotes</router-link>
          <router-link to="/addresses" :class="linkClass('/addresses')" class="flex items-center py-3 min-h-[44px] md:py-2.5 md:min-h-0" @click="$emit('close')">Addresses</router-link>
        </nav>
      </template>
    </div>
  </aside>
</template>

<script setup>
import { computed } from 'vue';
import { useRoute } from 'vue-router';
import { useAuthStore } from '../../stores/auth';

defineProps({
  open: { type: Boolean, default: true }
});
const emit = defineEmits(['close']);

const route = useRoute();
const authStore = useAuthStore();

const isAdmin = computed(() => {
  if (!authStore.user || !authStore.user.roles) return false;
  return authStore.user.roles.some(role => role.name === 'admin');
});

function linkClass(path) {
  const active = path === '/dashboard' ? route.path === path : route.path.startsWith(path);
  return [
    'flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-colors relative',
    active ? 'bg-emerald-500/20 text-emerald-600 dark:text-emerald-400 border-l-4 border-emerald-500' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700 hover:text-slate-900 dark:hover:text-white'
  ];
}
</script>
