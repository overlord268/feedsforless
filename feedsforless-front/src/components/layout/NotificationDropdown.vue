<template>
  <div class="relative" ref="containerRef">
    <button
      type="button"
      class="p-2 md:p-2.5 text-slate-500 hover:text-slate-700 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-lg md:rounded-xl transition-colors relative touch-manipulation min-h-[44px] min-w-[44px] md:min-h-0 md:min-w-0 flex items-center justify-center"
      :aria-label="ariaLabel"
      @click="toggleOpen"
    >
      <slot name="icon" />
      <slot name="badges" />
    </button>

    <div
      v-show="open"
      class="absolute right-0 top-full mt-1 w-[min(100vw-2rem,22rem)] max-h-[70vh] overflow-hidden bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-xl z-50 flex flex-col"
      :class="panelClass"
    >
      <div class="p-3 border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/50 flex items-center justify-between gap-2">
        <span class="text-xs font-bold text-slate-600 dark:text-slate-300 uppercase tracking-wider">{{ title }}</span>
        <slot name="header-meta" />
      </div>
      <div class="overflow-y-auto flex-1">
        <slot name="body" />
      </div>
      <div v-if="$slots.footer" class="p-2 border-t border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/50">
        <slot name="footer" />
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { useClickOutside } from '../../composables/useClickOutside';

defineProps({
  title: { type: String, required: true },
  ariaLabel: { type: String, default: 'Notifications' },
  panelClass: { type: String, default: '' },
});

const open = ref(false);
const containerRef = ref(null);

const emit = defineEmits(['toggle']);

defineExpose({ open, toggleOpen, close: () => { open.value = false; } });

function toggleOpen() {
  open.value = !open.value;
  emit('toggle', open.value);
}

useClickOutside(containerRef, () => {
  open.value = false;
});
</script>
