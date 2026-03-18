<template>
  <nav class="flex gap-1 px-6 border-b border-slate-200 bg-slate-50/50 shrink-0 overflow-x-auto">
    <button
      v-for="(tab, idx) in tabs"
      :key="tab.id"
      type="button"
      :class="[
        'px-4 py-3 text-sm font-medium rounded-t-lg border-b-2 whitespace-nowrap transition-colors',
        modelValue === idx
          ? 'border-emerald-500 text-emerald-600 bg-white -mb-px'
          : 'border-transparent text-slate-500 hover:text-slate-700 hover:bg-slate-100'
      ]"
      @click="emit('update:modelValue', idx)"
    >
      {{ tab.label }}
    </button>
  </nav>
</template>

<script setup>
defineProps({
  /** List of tabs: { id: string, label: string }. */
  tabs: {
    type: Array,
    required: true,
  },
  /** Index of the active tab. */
  modelValue: {
    type: Number,
    default: 0,
  },
});

const emit = defineEmits(['update:modelValue']);
</script>
