<template>
  <div
    class="rounded bg-slate-200 dark:bg-slate-700 animate-pulse"
    :class="[widthClass, heightClass]"
  />
</template>

<script setup>
defineProps({
  width: { type: String, default: 'full' }, // full | sm | md | lg | 1/2 | 1/3
  height: { type: String, default: 'md' }  // sm | md | lg
});

const widthClass = {
  full: 'w-full',
  sm: 'w-16',
  md: 'w-32',
  lg: 'w-48',
  '1/2': 'w-1/2',
  '1/3': 'w-1/3'
};

const heightClass = {
  sm: 'h-3',
  md: 'h-4',
  lg: 'h-5'
};
</script>
