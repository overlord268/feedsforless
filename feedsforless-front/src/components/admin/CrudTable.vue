<template>
  <div class="bg-white rounded-2xl border border-slate-200/80 shadow-card overflow-hidden">
    <div class="overflow-x-auto table-scroll">
      <table class="w-full text-sm min-w-[600px]">
        <thead class="bg-slate-50/80 border-b border-slate-200">
          <tr class="text-left">
            <th
              v-for="col in columns"
              :key="col.key"
              :class="col.thClass ?? 'px-6 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider'"
            >
              {{ col.label }}
            </th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
          <tr
            v-for="item in items"
            :key="item.id"
            class="hover:bg-slate-50/70 transition-colors"
          >
            <slot name="row" :item="item" />
          </tr>
        </tbody>
      </table>
    </div>
    <p
      v-if="!loading && items.length === 0"
      class="px-6 py-12 text-center text-slate-500"
    >
      {{ emptyMessage }}
    </p>
    <div v-if="loading" class="px-6 py-12 text-center text-slate-500">
      {{ loadingMessage }}
    </div>
  </div>
</template>

<script setup>
defineProps({
  /** Column definitions: { key, label, thClass? }. key is for reference; row content comes from the slot. */
  columns: {
    type: Array,
    required: true,
  },
  /** Data rows (each must have an id for :key). */
  items: {
    type: Array,
    default: () => [],
  },
  /** Whether data is loading (shows loading state). */
  loading: {
    type: Boolean,
    default: false,
  },
  /** Message when there are no items. */
  emptyMessage: {
    type: String,
    default: 'No records.',
  },
  /** Message shown while loading. */
  loadingMessage: {
    type: String,
    default: 'Loading…',
  },
});
</script>
