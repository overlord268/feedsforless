<template>
  <div class="px-4 py-3 border-b border-slate-200 dark:border-slate-700 flex flex-col sm:flex-row sm:items-center gap-3 sm:justify-between">
    <div class="min-w-0">
      <p v-if="title" class="text-sm font-semibold text-slate-900 dark:text-white truncate">{{ title }}</p>
      <p v-if="subtitle" class="text-xs text-slate-500 mt-0.5 leading-snug">{{ subtitle }}</p>
      <p v-if="showCount" class="text-xs text-slate-500" :class="subtitle ? 'mt-0.5' : ''">
        {{ filteredCount }} of {{ totalCount }} {{ itemLabel }}
      </p>
    </div>
    <div class="flex flex-wrap items-center gap-2">
      <div v-if="searchable" class="relative">
        <svg class="absolute left-2.5 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
        </svg>
        <input
          :value="modelValue"
          type="search"
          :placeholder="placeholder"
          class="w-full sm:w-56 pl-8 pr-3 py-2 rounded-lg border border-slate-200 dark:border-slate-600 bg-slate-50 dark:bg-slate-900 text-sm text-slate-800 dark:text-slate-200 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500"
          @input="$emit('update:modelValue', $event.target.value)"
        >
      </div>
      <slot name="actions" />
    </div>
  </div>
</template>

<script setup>
defineProps({
  modelValue: { type: String, default: '' },
  title: { type: String, default: '' },
  subtitle: { type: String, default: '' },
  placeholder: { type: String, default: 'Search…' },
  searchable: { type: Boolean, default: true },
  showCount: { type: Boolean, default: true },
  filteredCount: { type: Number, default: 0 },
  totalCount: { type: Number, default: 0 },
  itemLabel: { type: String, default: 'records' },
});

defineEmits(['update:modelValue']);
</script>
