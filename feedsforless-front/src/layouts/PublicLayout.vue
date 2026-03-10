<template>
  <div class="min-h-screen bg-white dark:bg-slate-900 font-sans text-slate-900 dark:text-slate-100 w-full flex flex-col">
    <header class="bg-[#003366] sticky top-0 z-40 w-full shadow-md">
      <div class="w-full px-4 lg:px-6 py-3 flex items-center justify-between gap-6">
        <router-link to="/" class="flex items-center gap-3 shrink-0">
          <div class="bg-white text-[#003366] font-black italic text-xl px-2 py-0.5 leading-none tracking-tighter">
            FFL
          </div>
          <div class="text-white font-black text-xl tracking-wide">
            FEEDSFORLESS
          </div>
        </router-link>

        <div class="flex-1 w-full max-w-4xl mx-auto">
          <div class="relative flex items-center w-full">
            <svg class="w-5 h-5 text-slate-400 absolute left-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            <input 
              v-model="searchQuery"
              @keyup.enter="performSearch"
              type="text" 
              placeholder="Search commodities, grades, or SDS..." 
              class="w-full pl-10 pr-4 py-2 bg-white text-sm focus:outline-none focus:ring-2 focus:ring-blue-400 placeholder-slate-500 text-slate-800 font-medium"
            />
          </div>
        </div>

        <div class="flex items-center gap-6 shrink-0 text-white">
          <router-link to="/catalog" class="text-[11px] font-bold uppercase tracking-wider hover:text-blue-200 transition-colors">Catalog</router-link>
          <a href="#" class="text-[11px] font-bold uppercase tracking-wider hover:text-blue-200 transition-colors">Track Freight</a>
          <UserMenu v-if="isLoggedIn" variant="dark" />
          <template v-else>
            <router-link to="/login" class="text-[11px] font-bold uppercase tracking-wider hover:text-blue-200 transition-colors">My Account</router-link>
            <a href="#" class="bg-white text-[#003366] text-[11px] font-bold uppercase tracking-wider px-4 py-2 hover:bg-slate-100 transition-colors">Quick Order</a>
          </template>
        </div>
      </div>
      <div class="w-full h-1 bg-green-700"></div>
    </header>

    <main class="flex-1 w-full flex flex-col pt-0">
      <router-view :search-query="debouncedSearch"></router-view>
    </main>

    <footer class="bg-[#0b1320] text-slate-300 py-8 text-sm w-full mt-auto">
      <div class="w-full px-4 lg:px-12 max-w-screen-2xl mx-auto">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
          <div class="pr-8">
            <div class="bg-white text-[#003366] font-black italic text-2xl px-3 py-1 leading-none tracking-tighter inline-block mb-2">
              FFL
            </div>
            <h4 class="text-white font-bold text-xs uppercase tracking-widest mb-3">FeedsForLess Industrial</h4>
            <p class="text-slate-400 text-xs leading-relaxed">
              Providing high-efficiency procurement for commercial livestock, poultry, and specialty feed manufacturers across North America.
            </p>
          </div>
          
          <div>
            <h5 class="text-white font-bold text-xs uppercase tracking-widest mb-4">Company</h5>
            <ul class="space-y-3 text-slate-400 text-xs">
              <li><a href="#" class="hover:text-white transition-colors">About Us</a></li>
              <li><a href="#" class="hover:text-white transition-colors">Resource Center</a></li>
              <li><a href="#" class="hover:text-white transition-colors">Sustainability</a></li>
              <li><a href="#" class="hover:text-white transition-colors">Careers</a></li>
            </ul>
          </div>
          
          <div>
            <h5 class="text-white font-bold text-xs uppercase tracking-widest mb-4">Support</h5>
            <ul class="space-y-3 text-slate-400 text-xs">
              <li><a href="#" class="hover:text-white transition-colors">Safety Data Sheets</a></li>
              <li><a href="#" class="hover:text-white transition-colors">Technical Standards</a></li>
              <li><a href="#" class="hover:text-white transition-colors">Terms of Sale</a></li>
              <li><a href="#" class="hover:text-white transition-colors">Logistics Support</a></li>
            </ul>
          </div>
          
          <div>
            <h5 class="text-white font-bold text-xs uppercase tracking-widest mb-4">Compliance</h5>
            <p class="text-slate-400 text-xs leading-relaxed mb-2">
              ISO 9001:2015 Certified Logistics & Distribution
            </p>
            <p class="text-slate-500 text-xs">
              © 2026 FeedsForLess LLC.
            </p>
          </div>
        </div>
      </div>
    </footer>
  </div>
</template>

<script setup>
import { ref, watch, computed } from 'vue';
import { useAuthStore } from '../stores/auth';
import UserMenu from '../components/layout/UserMenu.vue';

const authStore = useAuthStore();
const isLoggedIn = computed(() => !!authStore.token);
const searchQuery = ref('');
const debouncedSearch = ref('');

let debounceTimer = null;
watch(searchQuery, (val) => {
  if (debounceTimer) clearTimeout(debounceTimer);
  debounceTimer = setTimeout(() => {
    debouncedSearch.value = val;
    debounceTimer = null;
  }, 280);
});

const performSearch = () => {
  debouncedSearch.value = searchQuery.value;
};
</script>
