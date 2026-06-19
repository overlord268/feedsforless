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
          <router-link to="/admin/leads" :class="linkClass('/admin/leads')" class="flex items-center py-3 min-h-[44px] md:py-2.5 md:min-h-0" @click="$emit('close')">
            <svg class="w-5 h-5 mr-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
            Leads
          </router-link>
          <router-link to="/admin/messages" :class="linkClass('/admin/messages')" class="flex items-center py-3 min-h-[44px] md:py-2.5 md:min-h-0" @click="$emit('close')">
            <svg class="w-5 h-5 mr-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
            Messages
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
            <router-link to="/admin/products/import" :class="linkClass('/admin/products/import')" class="flex items-center py-3 min-h-[44px] md:py-2.5 md:min-h-0 pl-9" @click="$emit('close')">
              <svg class="w-4 h-4 mr-2.5 shrink-0 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
              Import
            </router-link>
            <router-link to="/admin/products/pricing" :class="linkClass('/admin/products/pricing')" class="flex items-center py-3 min-h-[44px] md:py-2.5 md:min-h-0 pl-9" @click="$emit('close')">
              <svg class="w-4 h-4 mr-2.5 shrink-0 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
              Profit margins
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
          <p class="px-3 text-[11px] font-bold text-slate-500 uppercase tracking-widest mb-2">System</p>
          <nav class="space-y-0.5">
            <router-link to="/admin/users" :class="linkClass('/admin/users')" class="flex items-center py-3 min-h-[44px] md:py-2.5 md:min-h-0" @click="$emit('close')">
              <svg class="w-5 h-5 mr-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
              Users
            </router-link>
            <router-link to="/admin/agent-tokens" :class="linkClass('/admin/agent-tokens')" class="flex items-center py-3 min-h-[44px] md:py-2.5 md:min-h-0" @click="$emit('close')">
              <svg class="w-5 h-5 mr-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
              AI Agent Tokens
            </router-link>
            <router-link to="/admin/settings/ffl-sku" :class="linkClass('/admin/settings/ffl-sku')" class="flex items-center py-3 min-h-[44px] md:py-2.5 md:min-h-0" @click="$emit('close')">
              <svg class="w-5 h-5 mr-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
              Configuration
            </router-link>
          </nav>
        </div>

        <div>
          <p class="px-3 text-[11px] font-bold text-slate-500 uppercase tracking-widest mb-2">Nutritional analysis</p>
          <nav class="space-y-0.5">
            <router-link to="/admin/nutritional-parameters" :class="linkClass('/admin/nutritional-parameters')" class="flex items-center py-3 min-h-[44px] md:py-2.5 md:min-h-0" @click="$emit('close')">
              <svg class="w-5 h-5 mr-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/></svg>
              Parameter
            </router-link>
          </nav>
        </div>
        <div>
          <p class="px-3 text-[11px] font-bold text-slate-500 uppercase tracking-widest mb-2">Technical Config</p>
          <nav class="space-y-0.5">
            <router-link to="/admin/parameters" :class="linkClass('/admin/parameters')" class="flex items-center py-3 min-h-[44px] md:py-2.5 md:min-h-0" @click="$emit('close')">
              <svg class="w-5 h-5 mr-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/></svg>
              Parameters
            </router-link>
            <router-link to="/admin/test-methods" :class="linkClass('/admin/test-methods')" class="flex items-center py-3 min-h-[44px] md:py-2.5 md:min-h-0" @click="$emit('close')">
              <svg class="w-5 h-5 mr-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
              Methods
            </router-link>
            <router-link to="/admin/packaging-types" :class="linkClass('/admin/packaging-types')" class="flex items-center py-3 min-h-[44px] md:py-2.5 md:min-h-0" @click="$emit('close')">
              <svg class="w-5 h-5 mr-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V7a2 2 0 00-2-2H6a2 2 0 00-2 2v6m16 0v4a2 2 0 01-2 2H6a2 2 0 01-2-2v-4m16 0H4m5-8h6m-6 4h6"/></svg>
              Packaging
            </router-link>
            <router-link to="/admin/measure-units" :class="linkClass('/admin/measure-units')" class="flex items-center py-3 min-h-[44px] md:py-2.5 md:min-h-0" @click="$emit('close')">
              <svg class="w-5 h-5 mr-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"/></svg>
              Units
            </router-link>
            <router-link to="/admin/handling-specs" :class="linkClass('/admin/handling-specs')" class="flex items-center py-3 min-h-[44px] md:py-2.5 md:min-h-0" @click="$emit('close')">
              <svg class="w-5 h-5 mr-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-11h6a1 1 0 011 1v6a1 1 0 01-1 1h-1m-6 0h6m-6 0a1.5 1.5 0 103 0m7 0a1.5 1.5 0 11-3 0"/></svg>
              Handling Specs
            </router-link>
            <router-link to="/admin/typical-applications" :class="linkClass('/admin/typical-applications')" class="flex items-center py-3 min-h-[44px] md:py-2.5 md:min-h-0" @click="$emit('close')">
              <svg class="w-5 h-5 mr-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z"/></svg>
              Typical Applications
            </router-link>
          </nav>
        </div>
      </template>

      <template v-else>
        <nav class="space-y-0.5">
          <router-link to="/app/catalog" :class="linkClass('/app/catalog')" class="flex items-center py-3 min-h-[44px] md:py-2.5 md:min-h-0" @click="$emit('close')">
            <svg class="w-5 h-5 mr-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
            Catalog
          </router-link>
          <router-link to="/quotes" :class="linkClass('/quotes')" class="flex items-center py-3 min-h-[44px] md:py-2.5 md:min-h-0" @click="$emit('close')">
            <svg class="w-5 h-5 mr-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            My Quotes
          </router-link>
          <router-link to="/messages" :class="linkClass('/messages')" class="flex items-center py-3 min-h-[44px] md:py-2.5 md:min-h-0" @click="$emit('close')">
            <svg class="w-5 h-5 mr-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
            Messages
          </router-link>
          <router-link to="/addresses" :class="linkClass('/addresses')" class="flex items-center py-3 min-h-[44px] md:py-2.5 md:min-h-0" @click="$emit('close')">
            <svg class="w-5 h-5 mr-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            Addresses
          </router-link>
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
