<template>
  <CrudTable
    :title="toolbarTitle"
    :subtitle="segmentDescription"
    search-placeholder="Search contacts…"
    item-label="contacts"
    empty-message="No contacts for this filter."
    empty-search-message="No contacts match your search."
    loading-message="Loading contacts…"
    table-class="min-w-[900px]"
    :columns="columns"
    :items="leadsWithKeys"
    :loading="loading"
    :default-sort="{ key: 'legal_business_name', dir: 'asc' }"
  >
    <template #toolbar>
      <button
        type="button"
        class="px-3 py-2 rounded-lg bg-emerald-600 text-white text-xs font-semibold hover:bg-emerald-700 disabled:opacity-60"
        :disabled="exporting"
        @click="$emit('export', 'xlsx')"
      >
        {{ exporting ? '…' : 'Excel' }}
      </button>
      <button
        type="button"
        class="px-3 py-2 rounded-lg border border-slate-200 dark:border-slate-600 text-slate-700 dark:text-slate-200 text-xs font-semibold hover:bg-slate-50 dark:hover:bg-slate-700/50 disabled:opacity-60"
        :disabled="exporting"
        @click="$emit('export', 'csv')"
      >
        CSV
      </button>
    </template>
    <template #row="{ item: lead }">
      <td class="px-3 py-2.5 text-slate-800 dark:text-slate-200 max-w-[160px] truncate" :title="lead.legal_business_name">{{ lead.legal_business_name || '—' }}</td>
      <td class="px-3 py-2.5 text-slate-700 dark:text-slate-300">{{ lead.first_name || '—' }}</td>
      <td class="px-3 py-2.5 text-slate-700 dark:text-slate-300">{{ lead.last_name || '—' }}</td>
      <td class="px-3 py-2.5 text-slate-600 dark:text-slate-400 max-w-[140px] truncate" :title="lead.email">{{ lead.email || '—' }}</td>
      <td class="px-3 py-2.5 text-slate-600 dark:text-slate-400 max-w-[140px] truncate" :title="lead.business_email">{{ lead.business_email || '—' }}</td>
      <td class="px-3 py-2.5 text-slate-600 dark:text-slate-400 whitespace-nowrap">{{ lead.phone || '—' }}</td>
      <td class="px-3 py-2.5 text-slate-600 dark:text-slate-400 font-mono text-xs">{{ lead.zip_code || '—' }}</td>
      <td class="px-3 py-2.5 text-slate-600 dark:text-slate-400">{{ lead.state || '—' }}</td>
    </template>
  </CrudTable>
</template>

<script setup>
import { computed } from 'vue';
import CrudTable from '../CrudTable.vue';

const props = defineProps({
  leads: { type: Array, default: () => [] },
  loading: { type: Boolean, default: false },
  exporting: { type: Boolean, default: false },
  segmentLabel: { type: String, default: '' },
  segmentDescription: { type: String, default: '' },
  filterNumber: { type: Number, default: null },
});

defineEmits(['export']);

const toolbarTitle = computed(() => {
  if (props.filterNumber) return `Segment ${props.filterNumber} · ${props.segmentLabel}`;
  return props.segmentLabel || 'Contacts';
});

const columns = [
  { key: 'legal_business_name', label: 'Business', thClass: 'px-3 py-2.5 text-[11px] font-semibold text-slate-500 uppercase tracking-wider' },
  { key: 'first_name', label: 'First', thClass: 'px-3 py-2.5 text-[11px] font-semibold text-slate-500 uppercase tracking-wider' },
  { key: 'last_name', label: 'Last', thClass: 'px-3 py-2.5 text-[11px] font-semibold text-slate-500 uppercase tracking-wider' },
  { key: 'email', label: 'Email', thClass: 'px-3 py-2.5 text-[11px] font-semibold text-slate-500 uppercase tracking-wider' },
  { key: 'business_email', label: 'Biz. email', thClass: 'px-3 py-2.5 text-[11px] font-semibold text-slate-500 uppercase tracking-wider' },
  { key: 'phone', label: 'Phone', sortable: false, thClass: 'px-3 py-2.5 text-[11px] font-semibold text-slate-500 uppercase tracking-wider' },
  { key: 'zip_code', label: 'ZIP', thClass: 'px-3 py-2.5 text-[11px] font-semibold text-slate-500 uppercase tracking-wider' },
  { key: 'state', label: 'State', thClass: 'px-3 py-2.5 text-[11px] font-semibold text-slate-500 uppercase tracking-wider' },
];

const leadsWithKeys = computed(() =>
  props.leads.map((lead, index) => ({
    ...lead,
    key: `${lead.email}-${index}`,
  })),
);
</script>
