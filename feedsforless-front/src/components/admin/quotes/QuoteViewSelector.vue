<template>
  <div
    class="grid gap-2.5"
    :class="gridClass"
  >
    <button
      v-for="card in cards"
      :key="card.id"
      type="button"
      class="group relative text-left rounded-xl border px-3.5 py-3 transition-all"
      :class="activeId === card.id
        ? 'border-emerald-500 bg-white dark:bg-slate-800 shadow-sm ring-1 ring-emerald-500/20'
        : 'border-slate-200/90 dark:border-slate-700 bg-slate-50/60 dark:bg-slate-800/40 hover:border-slate-300 dark:hover:border-slate-600 hover:bg-white dark:hover:bg-slate-800'"
      @click="$emit('select', card.id)"
    >
      <div class="flex items-center justify-between gap-2">
        <span
          class="flex h-7 w-7 shrink-0 items-center justify-center rounded-md text-[10px] font-bold uppercase"
          :class="activeId === card.id ? 'bg-emerald-600 text-white' : 'bg-white dark:bg-slate-700 text-slate-500 dark:text-slate-400 border border-slate-200/80 dark:border-slate-600'"
        >
          {{ card.badge }}
        </span>
        <span
          class="inline-flex min-w-[1.5rem] justify-center rounded-md px-1.5 py-0.5 text-[11px] font-bold tabular-nums"
          :class="activeId === card.id ? 'bg-emerald-600 text-white' : 'bg-slate-200/80 dark:bg-slate-700 text-slate-600 dark:text-slate-300'"
        >
          {{ card.count ?? '—' }}
        </span>
      </div>
      <p class="mt-2 text-xs font-semibold text-slate-900 dark:text-white leading-tight">{{ card.title }}</p>
      <p class="mt-0.5 text-[11px] text-slate-500 leading-snug line-clamp-2">{{ card.description }}</p>
    </button>
  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  cards: { type: Array, required: true },
  activeId: { type: String, required: true },
  columns: { type: Number, default: 4 },
});

defineEmits(['select']);

const gridClass = computed(() => {
  if (props.columns === 3) return 'grid-cols-1 sm:grid-cols-3';
  return 'grid-cols-2 lg:grid-cols-4';
});
</script>
