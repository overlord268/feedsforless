<template>
  <span
    :class="[quoteStatusClass(status, { variant }), sizeClass, 'inline-flex rounded capitalize']"
  >
    {{ quoteStatusLabel(status) }}
  </span>
</template>

<script setup>
import { computed } from 'vue';
import { quoteStatusClass, quoteStatusLabel } from '../../../composables/useQuoteStatus';

const props = defineProps({
  status: { type: String, required: true },
  variant: { type: String, default: 'badge' },
  size: { type: String, default: 'sm' },
});

const sizeClass = computed(() => {
  if (props.size === 'md') return 'px-3 py-1 text-xs font-black';
  return 'px-2 py-0.5 text-[11px] font-semibold';
});
</script>
