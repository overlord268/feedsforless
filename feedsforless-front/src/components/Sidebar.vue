<template>
  <aside class="w-72 flex-shrink-0 bg-slate-900 text-white flex flex-col border-r border-slate-800 transition-all duration-300">
    <div class="h-16 flex items-center px-6 border-b border-slate-800 bg-slate-950/50 shrink-0">
      <div class="h-8 w-8 bg-blue-500 rounded-lg flex items-center justify-center mr-3 shadow-lg shadow-blue-500/20">
        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
      </div>
      <span class="font-bold text-lg tracking-wide">FeedsFor<span class="text-blue-400">Less</span></span>
    </div>

    <div class="flex-1 overflow-y-auto py-6 px-4 space-y-8 no-scrollbar scroll-smooth">
      <div>
        <h3 class="px-3 text-[11px] font-bold text-slate-500 uppercase tracking-widest mb-3">General</h3>
        <nav class="space-y-1">
          <router-link to="/dashboard" class="flex items-center px-4 py-2.5 text-sm font-medium rounded-xl transition-all duration-200 group relative overflow-hidden" :class="[ $route.path === '/dashboard' ? 'bg-blue-600/10 text-blue-400 font-semibold before:content-[\'\'] before:absolute before:left-0 before:top-0 before:bottom-0 before:w-1 before:bg-blue-500 before:rounded-r-full' : 'text-slate-300 hover:bg-slate-800 hover:text-white' ]">
            <svg class="w-5 h-5 mr-3 opacity-70" :class="{ 'text-blue-500 opacity-100': $route.path === '/dashboard' }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
            Dashboard
          </router-link>
          <router-link to="/admin/quotes" class="flex items-center px-4 py-2.5 text-sm font-medium rounded-xl transition-all duration-200 group relative overflow-hidden" :class="[ $route.path.startsWith('/admin/quotes') ? 'bg-blue-600/10 text-blue-400 font-semibold before:content-[\'\'] before:absolute before:left-0 before:top-0 before:bottom-0 before:w-1 before:bg-blue-500 before:rounded-r-full' : 'text-slate-300 hover:bg-slate-800 hover:text-white' ]">
            <svg class="w-5 h-5 mr-3 opacity-70" :class="{ 'text-blue-500 opacity-100': $route.path.startsWith('/admin/quotes') }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
            Admin. Quotes
          </router-link>
          <router-link to="/admin/companies" class="flex items-center px-4 py-2.5 text-sm font-medium rounded-xl transition-all duration-200 group relative overflow-hidden" :class="[ $route.path === '/admin/companies' ? 'bg-blue-600/10 text-blue-400 font-semibold before:content-[\'\'] before:absolute before:left-0 before:top-0 before:bottom-0 before:w-1 before:bg-blue-500 before:rounded-r-full' : 'text-slate-300 hover:bg-slate-800 hover:text-white' ]">
            <svg class="w-5 h-5 mr-3 opacity-70" :class="{ 'text-blue-500 opacity-100': $route.path === '/admin/companies' }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
            Company Directory
          </router-link>
        </nav>
      </div>

      <div>
        <h3 class="px-3 text-[11px] font-bold text-slate-500 uppercase tracking-widest mb-3">Technical Catalog</h3>
        <nav class="space-y-1">
          <router-link to="/admin/categories" class="flex items-center px-4 py-2.5 text-sm font-medium rounded-xl transition-all duration-200 group relative overflow-hidden" :class="[ $route.path.startsWith('/admin/categories') ? 'bg-blue-600/10 text-blue-400 font-semibold before:content-[\'\'] before:absolute before:left-0 before:top-0 before:bottom-0 before:w-1 before:bg-blue-500 before:rounded-r-full' : 'text-slate-300 hover:bg-slate-800 hover:text-white' ]">
            <svg class="w-5 h-5 mr-3 opacity-70" :class="{ 'text-blue-500 opacity-100': $route.path.startsWith('/admin/categories') }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path></svg>
            Categories
          </router-link>
          <router-link to="/admin/products" class="flex items-center px-4 py-2.5 text-sm font-medium rounded-xl transition-all duration-200 group relative overflow-hidden" :class="[ $route.path.startsWith('/admin/products') ? 'bg-blue-600/10 text-blue-400 font-semibold before:content-[\'\'] before:absolute before:left-0 before:top-0 before:bottom-0 before:w-1 before:bg-blue-500 before:rounded-r-full' : 'text-slate-300 hover:bg-slate-800 hover:text-white' ]">
             <svg class="w-5 h-5 mr-3 opacity-70" :class="{ 'text-blue-500 opacity-100': $route.path.startsWith('/admin/products') }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
            Core Products
          </router-link>
          <router-link to="/admin/packaging-types" class="flex items-center px-4 py-2.5 text-sm font-medium rounded-xl transition-all duration-200 group relative overflow-hidden" :class="[ $route.path.startsWith('/admin/packaging-types') ? 'bg-blue-600/10 text-blue-400 font-semibold before:content-[\'\'] before:absolute before:left-0 before:top-0 before:bottom-0 before:w-1 before:bg-blue-500 before:rounded-r-full' : 'text-slate-300 hover:bg-slate-800 hover:text-white' ]">
            <svg class="w-5 h-5 mr-3 opacity-70" :class="{ 'text-blue-500 opacity-100': $route.path.startsWith('/admin/packaging-types') }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"></path></svg>
            Packaging Types
          </router-link>
        </nav>
      </div>

      <div>
        <h3 class="px-3 text-[11px] font-bold text-slate-500 uppercase tracking-widest mb-3">Specs &amp; Quality</h3>
        <nav class="space-y-1">
          <router-link to="/admin/specifications" class="flex items-center px-4 py-2.5 text-sm font-medium rounded-xl transition-all duration-200 group relative overflow-hidden" :class="[ $route.path.startsWith('/admin/specifications') ? 'bg-blue-600/10 text-blue-400 font-semibold before:content-[\'\'] before:absolute before:left-0 before:top-0 before:bottom-0 before:w-1 before:bg-blue-500 before:rounded-r-full' : 'text-slate-300 hover:bg-slate-800 hover:text-white' ]">
             <svg class="w-5 h-5 mr-3 opacity-70" :class="{ 'text-blue-500 opacity-100': $route.path.startsWith('/admin/specifications') }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
            Specifications (Limits)
          </router-link>
          <router-link to="/admin/nutritional-analysis" class="flex items-center px-4 py-2.5 text-sm font-medium rounded-xl transition-all duration-200 group relative overflow-hidden" :class="[ $route.path.startsWith('/admin/nutritional-analysis') ? 'bg-blue-600/10 text-blue-400 font-semibold before:content-[\'\'] before:absolute before:left-0 before:top-0 before:bottom-0 before:w-1 before:bg-blue-500 before:rounded-r-full' : 'text-slate-300 hover:bg-slate-800 hover:text-white' ]">
            <svg class="w-5 h-5 mr-3 opacity-70" :class="{ 'text-blue-500 opacity-100': $route.path.startsWith('/admin/nutritional-analysis') }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"></path></svg>
            Nutritional Analysis
          </router-link>
          <router-link to="/admin/handling-specs" class="flex items-center px-4 py-2.5 text-sm font-medium rounded-xl transition-all duration-200 group relative overflow-hidden" :class="[ $route.path.startsWith('/admin/handling-specs') ? 'bg-blue-600/10 text-blue-400 font-semibold before:content-[\'\'] before:absolute before:left-0 before:top-0 before:bottom-0 before:w-1 before:bg-blue-500 before:rounded-r-full' : 'text-slate-300 hover:bg-slate-800 hover:text-white' ]">
            <svg class="w-5 h-5 mr-3 opacity-70" :class="{ 'text-blue-500 opacity-100': $route.path.startsWith('/admin/handling-specs') }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
            Handling &amp; Storage
          </router-link>
          <router-link to="/admin/typical-applications" class="flex items-center px-4 py-2.5 text-sm font-medium rounded-xl transition-all duration-200 group relative overflow-hidden" :class="[ $route.path.startsWith('/admin/typical-applications') ? 'bg-blue-600/10 text-blue-400 font-semibold before:content-[\'\'] before:absolute before:left-0 before:top-0 before:bottom-0 before:w-1 before:bg-blue-500 before:rounded-r-full' : 'text-slate-300 hover:bg-slate-800 hover:text-white' ]">
             <svg class="w-5 h-5 mr-3 opacity-70" :class="{ 'text-blue-500 opacity-100': $route.path.startsWith('/admin/typical-applications') }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 10l-2 1m0 0l-2-1m2 1v2.5M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
            Typical Applications
          </router-link>
        </nav>
      </div>

      <div>
        <h3 class="px-3 text-[11px] font-bold text-slate-500 uppercase tracking-widest mb-3">Master Parameters</h3>
        <nav class="space-y-1">
          <router-link to="/admin/measure-units" class="flex items-center px-4 py-2.5 text-sm font-medium rounded-xl transition-all duration-200 group relative overflow-hidden" :class="[ $route.path.startsWith('/admin/measure-units') ? 'bg-blue-600/10 text-blue-400 font-semibold before:content-[\'\'] before:absolute before:left-0 before:top-0 before:bottom-0 before:w-1 before:bg-blue-500 before:rounded-r-full' : 'text-slate-300 hover:bg-slate-800 hover:text-white' ]">
            <svg class="w-5 h-5 mr-3 opacity-70" :class="{ 'text-blue-500 opacity-100': $route.path.startsWith('/admin/measure-units') }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"></path></svg>
            Unidades de Medida
          </router-link>
          <router-link to="/admin/parameters" class="flex items-center px-4 py-2.5 text-sm font-medium rounded-xl transition-all duration-200 group relative overflow-hidden" :class="[ $route.path.startsWith('/admin/parameters') ? 'bg-blue-600/10 text-blue-400 font-semibold before:content-[\'\'] before:absolute before:left-0 before:top-0 before:bottom-0 before:w-1 before:bg-blue-500 before:rounded-r-full' : 'text-slate-300 hover:bg-slate-800 hover:text-white' ]">
            <svg class="w-5 h-5 mr-3 opacity-70" :class="{ 'text-blue-500 opacity-100': $route.path.startsWith('/admin/parameters') }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
            Quality Parameters
          </router-link>
          <router-link to="/admin/test-methods" class="flex items-center px-4 py-2.5 text-sm font-medium rounded-xl transition-all duration-200 group relative overflow-hidden" :class="[ $route.path.startsWith('/admin/test-methods') ? 'bg-blue-600/10 text-blue-400 font-semibold before:content-[\'\'] before:absolute before:left-0 before:top-0 before:bottom-0 before:w-1 before:bg-blue-500 before:rounded-r-full' : 'text-slate-300 hover:bg-slate-800 hover:text-white' ]">
            <svg class="w-5 h-5 mr-3 opacity-70" :class="{ 'text-blue-500 opacity-100': $route.path.startsWith('/admin/test-methods') }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"></path></svg>
            Test Methods
          </router-link>
        </nav>
      </div>

    </div>

    <div class="p-4 border-t border-slate-800 bg-slate-950/30 shrink-0">
        <button @click="logout" class="flex items-center w-full px-4 py-2.5 text-sm font-medium text-slate-300 rounded-lg hover:bg-white/10 hover:text-white transition-colors gap-3">
          <svg class="w-5 h-5 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
          Log out
        </button>
    </div>
  </aside>
</template>

<script setup>
import { useAuthStore } from '../stores/auth';
import { useRouter } from 'vue-router';

const authStore = useAuthStore();
const router = useRouter();

const logout = () => {
  authStore.logout();
  router.push('/login');
};
</script>
