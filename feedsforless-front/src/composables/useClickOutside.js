import { onMounted, onUnmounted } from 'vue';

export function useClickOutside(containerRef, onOutside) {
  function handler(event) {
    if (containerRef.value && !containerRef.value.contains(event.target)) {
      onOutside(event);
    }
  }

  onMounted(() => document.addEventListener('click', handler));
  onUnmounted(() => document.removeEventListener('click', handler));
}
