<template>
  <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200/80 dark:border-slate-700 shadow-card overflow-hidden">
    <TableSearchToolbar
      v-if="searchable || title"
      v-model="searchQuery"
      :title="title"
      :subtitle="subtitle"
      :placeholder="searchPlaceholder"
      :searchable="searchable"
      :filtered-count="displayItems.length"
      :total-count="items.length"
      :item-label="itemLabel"
    >
      <template v-if="$slots.toolbar" #actions>
        <slot name="toolbar" />
      </template>
    </TableSearchToolbar>

    <div class="overflow-x-auto table-scroll">
      <table class="w-full text-sm" :class="tableClass">
        <thead class="bg-slate-50/80 dark:bg-slate-900/50 border-b border-slate-200 dark:border-slate-700">
          <tr class="text-left">
            <th
              v-for="col in columns"
              :key="col.key"
              :class="[
                col.thClass ?? 'px-4 py-2.5 text-[11px] font-semibold text-slate-500 uppercase tracking-wider',
                isSortable(col) ? 'cursor-pointer select-none hover:text-slate-700 dark:hover:text-slate-300' : '',
              ]"
              @click="isSortable(col) && toggleSort(col.key)"
            >
              <span class="inline-flex items-center gap-1">
                {{ col.label }}
                <TableSortIcon v-if="isSortable(col)" :active="sort.key === col.key" :dir="sort.dir" />
              </span>
            </th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
          <tr
            v-for="item in displayItems"
            :key="item.id ?? item.key"
            class="hover:bg-slate-50/70 dark:hover:bg-slate-700/20 transition-colors"
            :class="rowClass ? rowClass(item) : ''"
          >
            <slot name="row" :item="item" />
          </tr>
        </tbody>
      </table>
    </div>

    <p
      v-if="!loading && displayItems.length === 0"
      class="px-4 py-10 text-center text-sm text-slate-500"
    >
      {{ searchQuery ? emptySearchMessage : emptyMessage }}
    </p>
    <div v-if="loading" class="px-4 py-10 text-center text-sm text-slate-500">
      {{ loadingMessage }}
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import { compareValues, matchesSearch } from '../../composables/useSortableTable';
import TableSearchToolbar from './TableSearchToolbar.vue';
import TableSortIcon from './TableSortIcon.vue';

const props = defineProps({
  columns: { type: Array, required: true },
  items: { type: Array, default: () => [] },
  loading: { type: Boolean, default: false },
  searchable: { type: Boolean, default: true },
  title: { type: String, default: '' },
  subtitle: { type: String, default: '' },
  searchPlaceholder: { type: String, default: 'Search…' },
  tableClass: { type: String, default: 'min-w-[600px]' },
  itemLabel: { type: String, default: 'records' },
  emptyMessage: { type: String, default: 'No records.' },
  emptySearchMessage: { type: String, default: 'No records match your search.' },
  loadingMessage: { type: String, default: 'Loading…' },
  defaultSort: {
    type: Object,
    default: () => ({ key: 'id', dir: 'asc' }),
  },
  rowClass: { type: Function, default: null },
});

const searchQuery = ref('');
const sort = ref({ ...props.defaultSort });

watch(
  () => props.defaultSort,
  (value) => {
    if (value?.key) sort.value = { ...value };
  },
  { deep: true },
);

function isSortable(col) {
  if (col.key === 'actions') return false;
  return col.sortable !== false;
}

function resolveSortValue(item, key) {
  const col = props.columns.find((c) => c.key === key);
  if (col?.sortValue) return col.sortValue(item);
  return item[key];
}

function resolveSearchText(item) {
  return props.columns
    .filter((col) => col.key !== 'actions')
    .map((col) => {
      if (col.searchValue) return col.searchValue(item);
      if (col.sortValue) return col.sortValue(item);
      return item[col.key];
    })
    .join(' ');
}

function toggleSort(key) {
  if (sort.value.key === key) {
    sort.value = { key, dir: sort.value.dir === 'asc' ? 'desc' : 'asc' };
  } else {
    sort.value = { key, dir: 'asc' };
  }
}

const displayItems = computed(() => {
  const q = searchQuery.value;
  let rows = props.items.filter((item) => matchesSearch(resolveSearchText(item), q));

  const { key, dir } = sort.value;
  rows = [...rows].sort((a, b) => compareValues(resolveSortValue(a, key), resolveSortValue(b, key), dir));

  return rows;
});
</script>
