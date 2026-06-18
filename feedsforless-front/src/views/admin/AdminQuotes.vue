<template>
  <div class="space-y-5">
    <div>
      <h1 class="text-xl sm:text-2xl font-bold text-slate-900 dark:text-white tracking-tight">Quotes (RFQs)</h1>
      <p class="text-slate-500 mt-0.5 text-sm">Manage quote requests and export contact segments.</p>
    </div>

    <!-- View selector cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-2.5">
      <button
        v-for="card in viewCards"
        :key="card.id"
        type="button"
        class="group relative text-left rounded-xl border px-3.5 py-3 transition-all"
        :class="activeView === card.id
          ? 'border-emerald-500 bg-white dark:bg-slate-800 shadow-sm ring-1 ring-emerald-500/20'
          : 'border-slate-200/90 dark:border-slate-700 bg-slate-50/60 dark:bg-slate-800/40 hover:border-slate-300 dark:hover:border-slate-600 hover:bg-white dark:hover:bg-slate-800'"
        @click="selectView(card.id)"
      >
        <div class="flex items-center justify-between gap-2">
          <span
            class="flex h-7 w-7 shrink-0 items-center justify-center rounded-md text-[10px] font-bold uppercase"
            :class="activeView === card.id ? 'bg-emerald-600 text-white' : 'bg-white dark:bg-slate-700 text-slate-500 dark:text-slate-400 border border-slate-200/80 dark:border-slate-600'"
          >
            {{ card.badge }}
          </span>
          <span
            class="inline-flex min-w-[1.5rem] justify-center rounded-md px-1.5 py-0.5 text-[11px] font-bold tabular-nums"
            :class="activeView === card.id ? 'bg-emerald-600 text-white' : 'bg-slate-200/80 dark:bg-slate-700 text-slate-600 dark:text-slate-300'"
          >
            {{ card.count ?? '—' }}
          </span>
        </div>
        <p class="mt-2 text-xs font-semibold text-slate-900 dark:text-white leading-tight">{{ card.title }}</p>
        <p class="mt-0.5 text-[11px] text-slate-500 leading-snug line-clamp-2">{{ card.description }}</p>
      </button>
    </div>

    <!-- Contacts panel -->
    <div
      v-if="isLeadView"
      class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200/80 dark:border-slate-700 shadow-card overflow-hidden"
    >
      <div class="px-4 py-3 border-b border-slate-200 dark:border-slate-700 flex flex-col sm:flex-row sm:items-center gap-3 sm:justify-between">
        <div class="min-w-0">
          <p class="text-sm font-semibold text-slate-900 dark:text-white truncate">
            <span v-if="activeFilterNumber" class="text-emerald-600">Segment {{ activeFilterNumber }} · </span>{{ activeViewLabel }}
          </p>
          <p v-if="activeViewDescription" class="text-xs text-slate-500 mt-0.5 leading-snug">{{ activeViewDescription }}</p>
          <p class="text-xs text-slate-500">{{ filteredLeads.length }} of {{ leads.length }} contacts</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
          <div class="relative">
            <svg class="absolute left-2.5 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            <input
              v-model="leadsSearch"
              type="search"
              placeholder="Search contacts…"
              class="w-full sm:w-56 pl-8 pr-3 py-2 rounded-lg border border-slate-200 dark:border-slate-600 bg-slate-50 dark:bg-slate-900 text-sm text-slate-800 dark:text-slate-200 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500"
            />
          </div>
          <button
            type="button"
            class="px-3 py-2 rounded-lg bg-emerald-600 text-white text-xs font-semibold hover:bg-emerald-700 disabled:opacity-60"
            :disabled="exporting"
            @click="exportLeads('xlsx')"
          >
            {{ exporting ? '…' : 'Excel' }}
          </button>
          <button
            type="button"
            class="px-3 py-2 rounded-lg border border-slate-200 dark:border-slate-600 text-slate-700 dark:text-slate-200 text-xs font-semibold hover:bg-slate-50 dark:hover:bg-slate-700/50 disabled:opacity-60"
            :disabled="exporting"
            @click="exportLeads('csv')"
          >
            CSV
          </button>
        </div>
      </div>

      <div class="overflow-x-auto table-scroll">
        <table class="w-full text-sm min-w-[900px]">
          <thead class="bg-slate-50/80 dark:bg-slate-900/50 border-b border-slate-200 dark:border-slate-700">
            <tr class="text-left">
              <th
                v-for="col in leadColumns"
                :key="col.key"
                class="px-3 py-2.5 text-[11px] font-semibold text-slate-500 uppercase tracking-wider"
                :class="col.sortable ? 'cursor-pointer select-none hover:text-slate-700 dark:hover:text-slate-300' : ''"
                @click="col.sortable && toggleLeadSort(col.key)"
              >
                <span class="inline-flex items-center gap-1">
                  {{ col.label }}
                  <TableSortIcon v-if="col.sortable" :active="leadSort.key === col.key" :dir="leadSort.dir" />
                </span>
              </th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
            <tr
              v-for="(lead, index) in filteredLeads"
              :key="`${lead.email}-${index}`"
              class="hover:bg-slate-50/70 dark:hover:bg-slate-700/20 transition-colors"
            >
              <td class="px-3 py-2.5 text-slate-800 dark:text-slate-200 max-w-[160px] truncate" :title="lead.legal_business_name">{{ lead.legal_business_name || '—' }}</td>
              <td class="px-3 py-2.5 text-slate-700 dark:text-slate-300">{{ lead.first_name || '—' }}</td>
              <td class="px-3 py-2.5 text-slate-700 dark:text-slate-300">{{ lead.last_name || '—' }}</td>
              <td class="px-3 py-2.5 text-slate-600 dark:text-slate-400 max-w-[140px] truncate" :title="lead.email">{{ lead.email || '—' }}</td>
              <td class="px-3 py-2.5 text-slate-600 dark:text-slate-400 max-w-[140px] truncate" :title="lead.business_email">{{ lead.business_email || '—' }}</td>
              <td class="px-3 py-2.5 text-slate-600 dark:text-slate-400 whitespace-nowrap">{{ lead.phone || '—' }}</td>
              <td class="px-3 py-2.5 text-slate-600 dark:text-slate-400 font-mono text-xs">{{ lead.zip_code || '—' }}</td>
              <td class="px-3 py-2.5 text-slate-600 dark:text-slate-400">{{ lead.state || '—' }}</td>
            </tr>
          </tbody>
        </table>
      </div>
      <p v-if="!leadsLoading && filteredLeads.length === 0" class="px-4 py-10 text-center text-sm text-slate-500">
        {{ leadsSearch ? 'No contacts match your search.' : 'No contacts for this filter.' }}
      </p>
      <div v-if="leadsLoading" class="px-4 py-10 text-center text-sm text-slate-500">Loading contacts…</div>
    </div>

    <!-- Quotes panel -->
    <div
      v-if="activeView === 'quotes'"
      class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200/80 dark:border-slate-700 shadow-card overflow-hidden"
    >
      <div class="px-4 py-3 border-b border-slate-200 dark:border-slate-700 flex flex-col sm:flex-row sm:items-center gap-3 sm:justify-between">
        <div>
          <p class="text-sm font-semibold text-slate-900 dark:text-white">All quote requests</p>
          <p class="text-xs text-slate-500">{{ filteredQuotes.length }} of {{ quotes.length }} quotes</p>
        </div>
        <div class="relative">
          <svg class="absolute left-2.5 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
          <input
            v-model="quotesSearch"
            type="search"
            placeholder="Search quotes…"
            class="w-full sm:w-56 pl-8 pr-3 py-2 rounded-lg border border-slate-200 dark:border-slate-600 bg-slate-50 dark:bg-slate-900 text-sm text-slate-800 dark:text-slate-200 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500"
          />
        </div>
      </div>

      <div class="overflow-x-auto table-scroll">
        <table class="w-full text-sm min-w-[640px]">
          <thead class="bg-slate-50/80 dark:bg-slate-900/50 border-b border-slate-200 dark:border-slate-700">
            <tr class="text-left">
              <th
                class="px-4 py-2.5 text-[11px] font-semibold text-slate-500 uppercase tracking-wider cursor-pointer select-none hover:text-slate-700 dark:hover:text-slate-300 w-16"
                @click="toggleQuoteSort('id')"
              >
                <span class="inline-flex items-center gap-1">ID <TableSortIcon :active="quoteSort.key === 'id'" :dir="quoteSort.dir" /></span>
              </th>
              <th
                class="px-4 py-2.5 text-[11px] font-semibold text-slate-500 uppercase tracking-wider cursor-pointer select-none hover:text-slate-700 dark:hover:text-slate-300"
                @click="toggleQuoteSort('customer')"
              >
                <span class="inline-flex items-center gap-1">Customer <TableSortIcon :active="quoteSort.key === 'customer'" :dir="quoteSort.dir" /></span>
              </th>
              <th
                class="px-4 py-2.5 text-[11px] font-semibold text-slate-500 uppercase tracking-wider cursor-pointer select-none hover:text-slate-700 dark:hover:text-slate-300 hidden sm:table-cell"
                @click="toggleQuoteSort('zip')"
              >
                <span class="inline-flex items-center gap-1">ZIP <TableSortIcon :active="quoteSort.key === 'zip'" :dir="quoteSort.dir" /></span>
              </th>
              <th
                class="px-4 py-2.5 text-[11px] font-semibold text-slate-500 uppercase tracking-wider cursor-pointer select-none hover:text-slate-700 dark:hover:text-slate-300"
                @click="toggleQuoteSort('status')"
              >
                <span class="inline-flex items-center gap-1">Status <TableSortIcon :active="quoteSort.key === 'status'" :dir="quoteSort.dir" /></span>
              </th>
              <th
                class="px-4 py-2.5 text-[11px] font-semibold text-slate-500 uppercase tracking-wider cursor-pointer select-none hover:text-slate-700 dark:hover:text-slate-300 hidden md:table-cell"
                @click="toggleQuoteSort('total')"
              >
                <span class="inline-flex items-center gap-1">Total est. <TableSortIcon :active="quoteSort.key === 'total'" :dir="quoteSort.dir" /></span>
              </th>
              <th class="px-4 py-2.5 text-[11px] font-semibold text-slate-500 uppercase tracking-wider w-16 text-center">Chat</th>
              <th class="px-4 py-2.5 text-[11px] font-semibold text-slate-500 uppercase tracking-wider w-24">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
            <tr
              v-for="quote in filteredQuotes"
              :key="quote.id"
              class="hover:bg-slate-50/70 dark:hover:bg-slate-700/20 transition-colors"
              :class="quote.quote_chat_unread_count > 0 ? 'bg-blue-50/40 dark:bg-blue-900/10' : ''"
            >
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
              <td class="px-4 py-2.5 text-slate-800 dark:text-slate-200">{{ quoteCustomer(quote) }}</td>
              <td class="px-4 py-2.5 text-slate-600 dark:text-slate-400 font-mono text-xs hidden sm:table-cell">{{ quote.delivery_zip || '—' }}</td>
              <td class="px-4 py-2.5">
                <span :class="statusClass(quote.status)" class="inline-flex px-2 py-0.5 rounded text-[11px] font-semibold capitalize">
                  {{ quote.status }}
                </span>
              </td>
              <td class="px-4 py-2.5 text-slate-700 dark:text-slate-300 font-medium tabular-nums hidden md:table-cell">${{ formatNum(quote.total_estimated_cost) }}</td>
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
                  :to="quoteLink(quote)"
                  class="text-emerald-600 hover:text-emerald-700 text-xs font-semibold hover:underline"
                >
                  View
                </router-link>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
      <p v-if="!loading && filteredQuotes.length === 0" class="px-4 py-10 text-center text-sm text-slate-500">
        {{ quotesSearch ? 'No quotes match your search.' : 'No quotes yet.' }}
      </p>
      <div v-if="loading" class="px-4 py-10 text-center text-sm text-slate-500">Loading quotes…</div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, onActivated } from 'vue';
import api from '../../services/api';
import { useToast } from '../../composables/useToast';
import { useAdminChatNotifier } from '../../composables/useAdminChatNotifier';
import { useSortableTable } from '../../composables/useSortableTable';
import TableSortIcon from '../../components/admin/TableSortIcon.vue';

const toast = useToast();
const { refresh: refreshChatNotifier } = useAdminChatNotifier();
const quotes = ref([]);
const loading = ref(true);
const leads = ref([]);
const leadsLoading = ref(false);
const exporting = ref(false);
const activeView = ref('quotes');
const activeViewLabel = ref('');
const activeViewDescription = ref('');
const activeFilterNumber = ref(null);
const filterCounts = ref({});

const {
  searchQuery: quotesSearch,
  sort: quoteSort,
  processedItems: filteredQuotes,
  toggleSort: toggleQuoteSort,
} = useSortableTable(quotes, {
  defaultSort: { key: 'id', dir: 'desc' },
  getSortValue: (quote, key) => {
    if (key === 'customer') return quoteCustomer(quote);
    if (key === 'zip') return quote.delivery_zip;
    if (key === 'total') return Number(quote.total_estimated_cost ?? 0);
    return quote[key];
  },
  getSearchText: (quote) => [
    quote.id,
    quoteCustomer(quote),
    quote.delivery_zip,
    quote.status,
    formatNum(quote.total_estimated_cost),
  ].join(' '),
});

const {
  searchQuery: leadsSearch,
  sort: leadSort,
  processedItems: filteredLeads,
  toggleSort: toggleLeadSort,
} = useSortableTable(leads, {
  defaultSort: { key: 'legal_business_name', dir: 'asc' },
});

const LEAD_FILTERS = ['unregistered_with_quotes', 'without_accepted_quote', 'registered_no_quotes'];

const isLeadView = computed(() => LEAD_FILTERS.includes(activeView.value));

const viewCards = computed(() => [
  {
    id: 'quotes',
    title: 'All quotes',
    description: 'Review and manage RFQ requests.',
    count: quotes.value.length,
    badge: 'RFQ',
  },
  {
    id: 'unregistered_with_quotes',
    title: 'Guests with quotes',
    description: 'Unregistered users with at least one quote request.',
    count: filterCounts.value.unregistered_with_quotes,
    badge: '1',
  },
  {
    id: 'without_accepted_quote',
    title: 'No accepted quote',
    description: 'Registered or guest users without an accepted quote.',
    count: filterCounts.value.without_accepted_quote,
    badge: '2',
  },
  {
    id: 'registered_no_quotes',
    title: 'Registered, no RFQ',
    description: 'Registered accounts with no quote requests.',
    count: filterCounts.value.registered_no_quotes,
    badge: '3',
  },
]);

const leadColumns = [
  { key: 'legal_business_name', label: 'Business', sortable: true },
  { key: 'first_name', label: 'First', sortable: true },
  { key: 'last_name', label: 'Last', sortable: true },
  { key: 'email', label: 'Email', sortable: true },
  { key: 'business_email', label: 'Biz. email', sortable: true },
  { key: 'phone', label: 'Phone', sortable: false },
  { key: 'zip_code', label: 'ZIP', sortable: true },
  { key: 'state', label: 'State', sortable: true },
];

function formatNum(v) {
  if (v == null) return '0.00';
  return Number(v).toFixed(2);
}

function quoteCustomer(quote) {
  return quote.customer_name || quote.requester?.email || '—';
}

function quoteLink(quote) {
  const base = { name: 'AdminQuoteDetails', params: { id: quote.id } };
  if (quote.quote_chat_unread_count > 0) {
    return { ...base, hash: '#quote-chat' };
  }
  return base;
}

function statusClass(status) {
  const map = {
    pending: 'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-300',
    quoted: 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300',
    accepted: 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-300',
    rejected: 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300',
    expired: 'bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-400',
    cancelled: 'bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-400',
  };
  return map[status] || 'bg-slate-100 text-slate-600';
}

async function fetchQuotes({ silent = false } = {}) {
  if (!silent) loading.value = true;
  try {
    const { data } = await api.get('/api/v1/admin/quote-requests');
    const raw = data?.data ?? data;
    quotes.value = Array.isArray(raw) ? raw : (raw?.data ?? []);
  } catch (e) {
    console.error(e);
    if (!silent) quotes.value = [];
  } finally {
    if (!silent) loading.value = false;
  }
}

async function fetchFilterCounts() {
  await Promise.all(LEAD_FILTERS.map(async (filter) => {
    try {
      const { data } = await api.get('/api/v1/admin/quote-leads', { params: { filter } });
      filterCounts.value[filter] = data?.count ?? (data?.data?.length ?? 0);
    } catch (e) {
      console.error(e);
      filterCounts.value[filter] = 0;
    }
  }));
}

async function loadLeads(filter) {
  leadsLoading.value = true;
  leadsSearch.value = '';
  leadSort.value = { key: 'legal_business_name', dir: 'asc' };
  try {
    const { data } = await api.get('/api/v1/admin/quote-leads', { params: { filter } });
    leads.value = data?.data ?? [];
    activeViewLabel.value = data?.filter_label ?? filter;
    activeViewDescription.value = data?.filter_description ?? '';
    activeFilterNumber.value = data?.filter_number ?? null;
    filterCounts.value[filter] = data?.count ?? leads.value.length;
  } catch (e) {
    console.error(e);
    leads.value = [];
    toast.error('Could not load contacts for this filter.');
  } finally {
    leadsLoading.value = false;
  }
}

function selectView(viewId) {
  if (activeView.value === viewId) return;
  activeView.value = viewId;
  if (viewId === 'quotes') {
    quotesSearch.value = '';
    fetchQuotes();
    return;
  }
  loadLeads(viewId);
}

let quotesPollTimer = null;

function startQuotesPolling() {
  stopQuotesPolling();
  quotesPollTimer = window.setInterval(() => {
    if (activeView.value === 'quotes' && !loading.value) {
      fetchQuotes({ silent: true });
    }
  }, 15000);
}

function stopQuotesPolling() {
  if (quotesPollTimer) {
    clearInterval(quotesPollTimer);
    quotesPollTimer = null;
  }
}

function parseFilename(disposition, fallback) {
  if (!disposition) return fallback;
  const match = /filename="?([^"]+)"?/i.exec(disposition);
  return match?.[1] || fallback;
}

async function exportLeads(format) {
  if (!isLeadView.value || exporting.value) return;
  exporting.value = true;
  try {
    const response = await api.get('/api/v1/admin/quote-leads/export', {
      params: { filter: activeView.value, format },
      responseType: 'blob',
    });
    const fallback = `quote_leads_${activeView.value}.${format}`;
    const filename = parseFilename(response.headers['content-disposition'], fallback);
    const url = window.URL.createObjectURL(new Blob([response.data]));
    const link = document.createElement('a');
    link.href = url;
    link.setAttribute('download', filename);
    document.body.appendChild(link);
    link.click();
    link.remove();
    window.URL.revokeObjectURL(url);
  } catch (e) {
    console.error(e);
    toast.error('Export failed.');
  } finally {
    exporting.value = false;
  }
}

onMounted(async () => {
  await Promise.all([fetchQuotes(), fetchFilterCounts()]);
  startQuotesPolling();
});

onActivated(async () => {
  if (activeView.value === 'quotes') {
    await fetchQuotes({ silent: true });
    refreshChatNotifier();
  }
});

onUnmounted(() => {
  stopQuotesPolling();
});
</script>
