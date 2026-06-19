<template>
  <CrudTable
    title="All quote requests"
    search-placeholder="Search quotes…"
    item-label="quotes"
    empty-message="No quotes yet."
    empty-search-message="No quotes match your search."
    loading-message="Loading quotes…"
    :columns="columns"
    :items="quotes"
    :loading="loading"
    :default-sort="{ key: 'id', dir: 'desc' }"
    :row-class="quoteRowClass"
  >
    <template #row="{ item: quote }">
      <td class="px-4 py-2.5 text-slate-600 dark:text-slate-400 font-mono text-xs">
        <span class="inline-flex items-center gap-1.5">
          #{{ quote.id }}
          <span
            v-if="quote.quote_chat_unread_count > 0"
            class="inline-flex h-2 w-2 rounded-full bg-blue-500 animate-pulse"
            title="Unread quote chat"
          />
        </span>
      </td>
      <td class="px-4 py-2.5 text-slate-800 dark:text-slate-200">{{ quoteCustomerName(quote) }}</td>
      <td class="px-4 py-2.5 text-slate-600 dark:text-slate-400 font-mono text-xs hidden sm:table-cell">{{ quote.delivery_zip || '—' }}</td>
      <td class="px-4 py-2.5">
        <QuoteStatusBadge :status="quote.status" />
      </td>
      <td class="px-4 py-2.5 text-slate-700 dark:text-slate-300 font-medium tabular-nums hidden md:table-cell">${{ formatQuoteMoney(quote.total_estimated_cost) }}</td>
      <td class="px-4 py-2.5 text-center">
        <router-link
          v-if="quote.quote_chat_unread_count > 0"
          :to="{ name: 'AdminQuoteDetails', params: { id: quote.id }, hash: '#quote-chat' }"
          class="inline-flex items-center justify-center min-w-[2rem] h-7 px-2 rounded-full bg-blue-600 text-white text-[11px] font-bold hover:bg-blue-700"
          :title="`${quote.quote_chat_unread_count} unread message(s)`"
        >
          {{ quote.quote_chat_unread_count }}
        </router-link>
        <span v-else class="text-slate-300 dark:text-slate-600 text-xs">—</span>
      </td>
      <td class="px-4 py-2.5">
        <router-link
          :to="quoteDetailLink(quote)"
          class="text-emerald-600 hover:text-emerald-700 text-xs font-semibold hover:underline"
        >
          View
        </router-link>
      </td>
    </template>
  </CrudTable>
</template>

<script setup>
import CrudTable from '../CrudTable.vue';
import QuoteStatusBadge from './QuoteStatusBadge.vue';
import {
  formatQuoteMoney,
  quoteCustomerName,
  quoteDetailLink,
} from '../../../composables/useQuoteStatus';

defineProps({
  quotes: { type: Array, default: () => [] },
  loading: { type: Boolean, default: false },
});

const columns = [
  { key: 'id', label: 'ID', thClass: 'px-4 py-2.5 text-[11px] font-semibold text-slate-500 uppercase tracking-wider w-16' },
  {
    key: 'customer',
    label: 'Customer',
    sortValue: (q) => quoteCustomerName(q),
    searchValue: (q) => quoteCustomerName(q),
  },
  {
    key: 'zip',
    label: 'ZIP',
    thClass: 'px-4 py-2.5 text-[11px] font-semibold text-slate-500 uppercase tracking-wider hidden sm:table-cell',
    sortValue: (q) => q.delivery_zip,
  },
  { key: 'status', label: 'Status' },
  {
    key: 'total',
    label: 'Total est.',
    thClass: 'px-4 py-2.5 text-[11px] font-semibold text-slate-500 uppercase tracking-wider hidden md:table-cell',
    sortValue: (q) => Number(q.total_estimated_cost ?? 0),
    searchValue: (q) => formatQuoteMoney(q.total_estimated_cost),
  },
  { key: 'chat', label: 'Chat', sortable: false, thClass: 'px-4 py-2.5 text-[11px] font-semibold text-slate-500 uppercase tracking-wider w-16 text-center' },
  { key: 'actions', label: 'Actions', sortable: false, thClass: 'px-4 py-2.5 text-[11px] font-semibold text-slate-500 uppercase tracking-wider w-24' },
];

function quoteRowClass(quote) {
  return quote.quote_chat_unread_count > 0 ? 'bg-blue-50/40 dark:bg-blue-900/10' : '';
}
</script>
