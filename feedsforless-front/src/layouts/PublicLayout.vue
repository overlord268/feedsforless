<template>
  <div class="h-screen bg-white dark:bg-slate-900 font-sans text-slate-900 dark:text-slate-100 w-full flex flex-col overflow-hidden">
    <!-- Header -->
    <header class="bg-[#003366] dark:bg-slate-800 sticky top-0 z-40 w-full shadow-md">
      <div class="w-full px-4 lg:px-6 py-3 flex items-center justify-between gap-4 md:gap-6">
        
        <div class="flex items-center gap-2 md:gap-3 shrink-0">
          <!-- Mobile Menu Toggle (Left) -->
          <button v-if="!isAuthRoute" @click="isMobileMenuOpen = true" class="md:hidden p-2 -ml-2 text-white hover:bg-white/10 rounded transition-colors">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
          </button>
          
          <!-- Logo -->
          <router-link to="/" class="flex items-center gap-2 md:gap-3 shrink-0">
            <div class="bg-white text-[#003366] font-black italic text-lg md:text-xl px-2 py-0.5 leading-none tracking-tighter">
              FFL
            </div>
            <div class="text-white font-black text-lg md:text-xl tracking-wide">
              FEEDSFORLESS
            </div>
          </router-link>
        </div>

        <!-- Desktop Search -->
        <div v-if="!isAuthRoute" class="flex-1 w-full max-w-4xl mx-auto hidden md:block">
          <div class="relative flex items-center w-full">
            <svg class="w-5 h-5 text-slate-400 absolute left-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            <input 
              v-model="searchQuery"
              @keyup.enter="performSearch"
              type="text" 
              placeholder="Search commodities, grades, or SDS..." 
              class="w-full pl-10 pr-4 py-2 bg-white/10 dark:bg-slate-900/50 border border-transparent dark:border-slate-700 text-white placeholder-blue-200 dark:placeholder-slate-400 text-sm focus:outline-none focus:bg-white focus:text-slate-900 focus:placeholder-slate-500 dark:focus:bg-slate-800 dark:focus:text-white rounded transition-colors font-medium"
            />
          </div>
        </div>

        <div v-if="!isAuthRoute" class="flex items-center gap-4 md:gap-5 shrink-0 text-white ml-auto">
          <!-- Always visible User/Login Controls (Desktop & Mobile) -->
          <template v-if="isLoggedIn">
            <div class="hidden md:block">
              <UserMenu variant="dark" />
            </div>
          </template>
          <template v-else>
            <router-link to="/login" class="text-[12px] font-bold uppercase tracking-wider hover:text-blue-200 transition-colors">Sign In</router-link>
            <router-link to="/register" class="bg-white text-[#003366] text-[12px] font-bold uppercase tracking-wider px-4 py-2 rounded hover:bg-slate-100 transition-colors">Sign Up</router-link>
          </template>
        </div>
      </div>
      <div class="w-full h-1 bg-green-700"></div>
    </header>

    <!-- Base view -->
    <main class="flex-1 w-full flex flex-col pt-0 overflow-y-auto overflow-x-hidden min-h-0 bg-white dark:bg-slate-900">
      <router-view :search-query="debouncedSearch"></router-view>
    </main>

    <!-- Mobile Menu Overlay -->
    <div v-show="isMobileMenuOpen" class="fixed inset-0 z-50 md:hidden flex" aria-modal="true" role="dialog">
      <!-- Backdrop -->
      <div 
        class="fixed inset-0 bg-black/50 dark:bg-black/70 transition-opacity" 
        @click="isMobileMenuOpen = false"
        aria-hidden="true"
      ></div>

      <!-- Slide-over panel -->
      <div
        class="relative flex w-full max-w-xs flex-col bg-white dark:bg-slate-800 h-full shadow-xl transition-transform transform"
        :class="isMobileMenuOpen ? 'translate-x-0' : '-translate-x-full'"
      >
        <div class="flex items-center justify-between px-4 pt-5 pb-2">
          <div class="flex items-center gap-2">
            <div class="bg-[#003366] dark:bg-slate-700 text-white font-black italic text-lg px-2 py-0.5 leading-none tracking-tighter">FFL</div>
          </div>
          <button type="button" @click="isMobileMenuOpen = false" class="-m-2 p-2 text-slate-400 hover:text-slate-500 dark:hover:text-slate-300">
            <span class="sr-only">Close menu</span>
            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
          </button>
        </div>

        <!-- Mobile Search -->
        <div class="px-4 mt-4 mb-2">
          <div class="relative flex items-center w-full">
            <svg class="w-5 h-5 text-slate-400 absolute left-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            <input 
              v-model="searchQuery"
              @keyup.enter="performSearch(); isMobileMenuOpen = false"
              type="text" 
              placeholder="Search..." 
              class="w-full pl-10 pr-4 py-2.5 bg-slate-100 dark:bg-slate-700 border-none text-sm focus:outline-none focus:ring-2 focus:ring-blue-400 dark:focus:ring-blue-500 placeholder-slate-500 dark:placeholder-slate-400 text-slate-900 dark:text-white font-medium rounded-lg"
            />
          </div>
        </div>

        <!-- Teleport target for Catalog Categories -->
        <div id="mobile-catalog-categories"></div>

        <div class="flex-1 overflow-y-auto px-4 py-4 space-y-4 flex flex-col justify-between">
          <div>
            <template v-if="!isLoggedIn">
              <div class="flex flex-col gap-3">
                <router-link to="/register" @click="isMobileMenuOpen = false" class="block w-full text-center border border-slate-200 dark:border-slate-600 text-slate-700 dark:text-slate-300 text-[12px] font-bold uppercase tracking-wider px-4 py-3 rounded-lg">Sign Up</router-link>
              </div>
            </template>
          </div>

          <template v-if="isLoggedIn">
            <div class="border-t border-slate-200 dark:border-slate-700 pt-4 flex flex-col gap-1 mt-auto">
              <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2 px-3">Account Menu</div>
              <router-link to="/account" @click="isMobileMenuOpen = false" class="px-3 py-2 text-sm font-medium text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-800 rounded-lg">Profile</router-link>
              <router-link to="/quotes" @click="isMobileMenuOpen = false" class="px-3 py-2 text-sm font-medium text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-800 rounded-lg">My Qtr</router-link>
              <router-link to="/addresses" @click="isMobileMenuOpen = false" class="px-3 py-2 text-sm font-medium text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-800 rounded-lg">Addresses</router-link>
              <div class="px-3 py-2 flex items-center justify-between">
                <span class="text-sm font-medium text-slate-700 dark:text-slate-200">Dark mode</span>
                <button type="button" role="switch" :aria-checked="themeStore.isDark" class="relative inline-flex h-5 w-9 shrink-0 cursor-pointer rounded-xl border-2 border-transparent transition-colors focus:outline-none focus:ring-2 focus:ring-[#2962ff] focus:ring-offset-2" :class="themeStore.isDark ? 'bg-[#2962ff]' : 'bg-slate-300 dark:bg-slate-600'" @click="themeStore.toggle()">
                  <span class="pointer-events-none inline-block h-4 w-4 transform rounded-lg bg-white shadow ring-0 transition-transform" :class="themeStore.isDark ? 'translate-x-4' : 'translate-x-0'"></span>
                </button>
              </div>
              <button type="button" @click="logout" class="px-3 py-2 text-sm font-bold text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg text-left">Log out</button>
            </div>
          </template>
        </div>
      </div>
    </div>

    <footer v-if="!isAuthRoute" class="bg-[#0b1320] text-slate-300 py-6 md:py-8 text-sm w-full shrink-0">
      <div class="w-full px-4 lg:px-12 max-w-screen-2xl mx-auto">
        <!-- Main Footer Links Grid -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-x-4 gap-y-6 md:gap-6">
          <div class="col-span-2 md:col-span-1 pr-4 md:pr-8 mb-2 md:mb-0">
            <div class="bg-white text-[#003366] font-black italic text-xl md:text-2xl px-3 py-1 leading-none tracking-tighter inline-block mb-2">
              FFL
            </div>
            <h4 class="text-white font-bold text-xs uppercase tracking-widest mb-2 md:mb-3">FeedsForLess Industrial</h4>
            <p class="text-slate-400 text-[11px] md:text-xs leading-relaxed max-w-md">
              Providing high-efficiency procurement for commercial livestock, poultry, and specialty feed manufacturers across North America.
            </p>
          </div>
          
          <div>
            <h5 class="text-white font-bold text-[10px] md:text-xs uppercase tracking-widest mb-3 md:mb-4">Company</h5>
            <ul class="space-y-2 md:space-y-3 text-slate-400 text-xs">
              <li><a href="#" class="hover:text-white transition-colors">About Us</a></li>
              <li><a href="#" class="hover:text-white transition-colors">Resource Center</a></li>
              <li><a href="#" class="hover:text-white transition-colors">Sustainability</a></li>
              <li><a href="#" class="hover:text-white transition-colors">Careers</a></li>
            </ul>
          </div>
          
          <div>
            <h5 class="text-white font-bold text-[10px] md:text-xs uppercase tracking-widest mb-3 md:mb-4">Support</h5>
            <ul class="space-y-2 md:space-y-3 text-slate-400 text-xs">
              <li><a href="#" class="hover:text-white transition-colors">Safety Data Sheets</a></li>
              <li><a href="#" class="hover:text-white transition-colors">Technical Standards</a></li>
              <li><a href="#" class="hover:text-white transition-colors">Terms of Sale</a></li>
              <li><a href="#" class="hover:text-white transition-colors">Logistics Support</a></li>
            </ul>
          </div>
          
          <div class="col-span-2 md:col-span-1 mt-2 md:mt-0 pt-4 md:pt-0 border-t border-slate-700/50 md:border-0">
            <h5 class="text-white font-bold text-[10px] md:text-xs uppercase tracking-widest mb-2 md:mb-4">Compliance</h5>
            <p class="text-slate-400 text-xs leading-relaxed mb-2">
              ISO 9001:2015 Certified Logistics & Distribution
            </p>
            <p class="text-slate-500 text-[10px] md:text-xs">
              © 2026 FeedsForLess LLC.
            </p>
          </div>
        </div>
      </div>
    </footer>
  </div>
</template>

<script setup>
import { ref, watch, computed, onMounted, onUnmounted } from 'vue';
import { useAuthStore } from '../stores/auth';
import { useThemeStore } from '../stores/theme';
import { useRoute, useRouter } from 'vue-router';
import UserMenu from '../components/layout/UserMenu.vue';

const route = useRoute();
const router = useRouter();
const authStore = useAuthStore();
const themeStore = useThemeStore();
const isLoggedIn = computed(() => !!authStore.token);
const isAuthRoute = computed(() => ['Login', 'Register'].includes(route.name));
const searchQuery = ref('');
const debouncedSearch = ref('');
const isMobileMenuOpen = ref(false);

const closeMobileMenu = () => { isMobileMenuOpen.value = false; };

const logout = async () => {
  isMobileMenuOpen.value = false;
  await authStore.logout();
  router.push('/');
};

onMounted(() => {
  window.addEventListener('close-mobile-menu', closeMobileMenu);
});

onUnmounted(() => {
  window.removeEventListener('close-mobile-menu', closeMobileMenu);
});

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
