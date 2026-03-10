import { defineStore } from 'pinia';
import { ref } from 'vue';

const STORAGE_KEY = 'ffl-theme';

export const useThemeStore = defineStore('theme', () => {
  const isDark = ref(false);

  function apply(isDarkMode) {
    if (typeof document === 'undefined') return;
    isDark.value = !!isDarkMode;
    if (isDarkMode) {
      document.documentElement.classList.add('dark');
      localStorage.setItem(STORAGE_KEY, 'dark');
    } else {
      document.documentElement.classList.remove('dark');
      localStorage.setItem(STORAGE_KEY, 'light');
    }
  }

  function toggle() {
    apply(!isDark.value);
  }

  function init() {
    const saved = localStorage.getItem(STORAGE_KEY);
    apply(saved === 'dark');
  }

  return { isDark, apply, toggle, init };
});
