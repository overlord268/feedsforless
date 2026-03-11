<template>
  <div
    class="flex flex-col items-center justify-center py-12 px-4 animate-in fade-in duration-200"
    :class="fullHeight ? 'min-h-screen' : 'min-h-[280px]'"
  >
    <LoadingSpinner :size="spinnerSize" :label="message" />
  </div>
</template>

<script setup>
import LoadingSpinner from './LoadingSpinner.vue';

defineProps({
  message: { type: String, default: 'Loading…' },
  spinnerSize: { type: String, default: 'lg' },
  fullHeight: { type: Boolean, default: false }
});
</script>
