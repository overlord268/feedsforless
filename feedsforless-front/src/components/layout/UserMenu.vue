<template>
  <div class="relative" ref="containerRef">
    <button
      type="button"
      class="flex items-center gap-2 pl-2 cursor-pointer rounded-xl py-1.5 pr-2 transition-colors min-h-[44px] md:min-h-0"
      :class="variant === 'dark' ? 'hover:bg-white/10 text-white' : 'hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-900'"
      @click="open = !open"
    >
      <div class="h-9 w-9 bg-emerald-500 rounded-full flex items-center justify-center text-white font-semibold text-sm shrink-0">
        {{ userInitial }}
      </div>
      <div class="hidden sm:block text-left">
        <p class="text-sm font-medium leading-none" :class="variant === 'dark' ? 'text-white' : 'text-slate-800 dark:text-slate-200'">{{ displayLabel }}</p>
        <p class="text-xs leading-none" :class="variant === 'dark' ? 'text-blue-200' : 'text-slate-500 dark:text-slate-400'">FeedsForLess</p>
      </div>
      <svg class="w-4 h-4 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
    </button>

    <div
      v-show="open"
      class="absolute right-0 top-full mt-1 w-64 py-2 bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-xl z-[9999]"
    >
      <router-link
        to="/account"
        class="flex items-center gap-3 px-4 py-2.5 text-sm text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors"
        @click="open = false"
      >
        <svg class="w-5 h-5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
        Configuration
      </router-link>

      <div class="px-4 py-2.5 flex items-center justify-between gap-3 border-t border-slate-100 dark:border-slate-700">
        <span class="text-sm text-slate-600 dark:text-slate-300">Dark mode</span>
        <div class="flex items-center gap-2">
          <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
          <button
            type="button"
            role="switch"
            :aria-checked="themeStore.isDark"
            class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2"
            :class="themeStore.isDark ? 'bg-emerald-600' : 'bg-slate-200 dark:bg-slate-600'"
            @click="themeStore.toggle()"
          >
            <span class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition-transform" :class="themeStore.isDark ? 'translate-x-5' : 'translate-x-1'" style="top: 2px;" />
          </button>
          <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/></svg>
        </div>
      </div>

      <button
        type="button"
        class="flex items-center gap-3 w-full px-4 py-2.5 text-sm text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors border-t border-slate-100 dark:border-slate-700"
        @click="logout"
      >
        <svg class="w-5 h-5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
        Log out
      </button>

      <p class="px-4 pt-2 pb-1 text-[10px] text-slate-400 dark:text-slate-500 text-center">v1</p>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';

defineProps({
  variant: { type: String, default: 'default' } // 'default' | 'dark'
});
import { useRouter } from 'vue-router';
import { useAuthStore } from '../../stores/auth';
import { useThemeStore } from '../../stores/theme';

const router = useRouter();
const authStore = useAuthStore();
const themeStore = useThemeStore();
const open = ref(false);
const containerRef = ref(null);

const userInitial = computed(() => {
  const u = authStore.user;
  if (u?.first_name) return (u.first_name[0] || '').toUpperCase();
  if (u?.name) return String(u.name)[0].toUpperCase();
  if (u?.email) return (u.email[0] || '').toUpperCase();
  return 'A';
});

const displayLabel = computed(() => {
  const u = authStore.user;
  if (u?.first_name || u?.last_name) return [u.first_name, u.last_name].filter(Boolean).join(' ').trim() || u?.email;
  if (u?.name) return u.name;
  return u?.email || 'Account';
});

function logout() {
  open.value = false;
  authStore.logout();
  router.push('/login');
}

function onClickOutside(e) {
  if (containerRef.value && !containerRef.value.contains(e.target)) open.value = false;
}

onMounted(() => document.addEventListener('click', onClickOutside));
onUnmounted(() => document.removeEventListener('click', onClickOutside));
</script>
