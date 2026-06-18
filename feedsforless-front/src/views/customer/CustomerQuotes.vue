<template>
  <div class="min-h-full bg-[#f8fafc] dark:bg-slate-900 animate-in fade-in duration-500">
    <div class="max-w-5xl mx-auto px-4 py-8 md:py-10">
      <div class="flex flex-wrap items-center justify-between gap-4 mb-8">
        <div>
          <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-1">Account</p>
          <h1 class="text-2xl md:text-3xl font-black text-[#003366] dark:text-white tracking-tight">My Quotes</h1>
          <p class="text-sm text-slate-500 mt-1">Track requests, review pricing, and manage your RFQ history.</p>
        </div>
        <button
          type="button"
          class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-[#003366] text-white text-sm font-bold hover:bg-[#002244] transition-colors shadow-sm"
          @click="cartOpen = true"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
          </svg>
          Request basket
          <span v-if="rfqCount > 0" class="min-w-[1.25rem] h-5 px-1 flex items-center justify-center rounded-md bg-white/20 text-xs font-bold">{{ rfqCount }}</span>
        </button>
      </div>

      <div class="flex flex-col sm:flex-row sm:items-center gap-3 mb-6">
        <div class="flex gap-2 flex-wrap">
          <button
            v-for="tab in filterTabs"
            :key="tab.key"
            type="button"
            class="px-4 py-2 rounded-lg text-sm font-semibold transition-colors"
            :class="filter === tab.key ? 'bg-[#2962ff] text-white' : 'bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-700 hover:border-[#2962ff]/40'"
            @click="filter = tab.key"
          >
            {{ tab.label }}
            <span v-if="tab.count != null" class="ml-1 opacity-80">({{ tab.count }})</span>
          </button>
        </div>
        <div class="relative sm:ml-auto w-full sm:w-56">
          <svg class="absolute left-2.5 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
          <input
            v-model="searchQuery"
            type="search"
            placeholder="Search quotes…"
            class="w-full pl-8 pr-3 py-2 rounded-lg border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 text-sm"
          >
        </div>
      </div>

      <div v-if="loading" class="py-16 text-center text-slate-500">Loading quotes…</div>

      <div v-else-if="displayQuotes.length === 0" class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-12 text-center">
        <p class="text-slate-500 mb-4">{{ searchQuery ? 'No quotes match your search.' : emptyMessage }}</p>
        <router-link to="/catalog" class="inline-flex items-center px-4 py-2 rounded-xl bg-[#2962ff] text-white text-sm font-bold hover:bg-blue-700 transition-colors">
          Browse catalog
        </router-link>
      </div>

      <div v-else class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden divide-y divide-slate-100 dark:divide-slate-700">
        <router-link
          v-for="quote in displayQuotes"
          :key="quote.id"
          :to="{ name: 'CustomerQuoteDetails', params: { id: quote.id } }"
          class="flex flex-col sm:flex-row sm:items-center gap-4 px-5 py-4 hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors group"
        >
          <div class="flex-1 min-w-0">
            <div class="flex flex-wrap items-center gap-2 mb-1">
              <span class="font-bold text-slate-900 dark:text-white group-hover:text-[#2962ff]">RFQ #{{ quote.id }}</span>
              <span class="inline-flex px-2 py-0.5 rounded-md text-[11px] font-bold ring-1 ring-inset" :class="quoteStatusClass(quote.status)">
                {{ quoteStatusLabel(quote.status) }}
              </span>
            </div>
            <p class="text-sm text-slate-500">
              {{ formatQuoteDate(quote.created_at) }}
              · {{ quote.items?.length || 0 }} item{{ (quote.items?.length || 0) === 1 ? '' : 's' }}
              · ZIP {{ quote.delivery_zip || '—' }}
            </p>
          </div>
          <div class="flex items-center gap-4 shrink-0">
            <div class="text-right">
              <p class="text-[10px] font-bold uppercase text-slate-400">Est. total</p>
              <p class="text-lg font-black text-[#003366] dark:text-white">${{ formatQuoteMoney(quote.total_estimated_cost) }}</p>
            </div>
            <svg class="w-5 h-5 text-slate-300 group-hover:text-[#2962ff] transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
          </div>
        </router-link>
      </div>
    </div>

    <!-- Request basket drawer (unchanged logic) -->
    <Transition name="cart-overlay">
      <div v-show="cartOpen" class="fixed inset-0 bg-black/40 z-40 backdrop-blur-sm" @click="cartOpen = false"/>
    </Transition>
    <Transition name="cart-drawer">
      <aside
        v-show="cartOpen"
        class="fixed top-0 right-0 bottom-0 w-full max-w-md bg-white dark:bg-slate-800 shadow-2xl z-50 flex flex-col border-l border-slate-200 dark:border-slate-700 overflow-hidden"
      >
        <div class="flex items-center justify-between px-5 py-4 border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 shrink-0">
          <h2 class="text-lg font-bold text-slate-800 dark:text-white">Request basket</h2>
          <button type="button" class="p-2 rounded-xl text-slate-500 hover:bg-slate-200 dark:hover:bg-slate-700" @click="cartOpen = false">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
          </button>
        </div>
        <div class="flex-1 overflow-y-auto">
          <div v-if="!rfqList?.items?.length" class="p-8 text-center">
            <p class="text-slate-500 mb-4">Your basket is empty.</p>
            <router-link to="/catalog" class="text-[#2962ff] font-semibold text-sm" @click="cartOpen = false">Browse catalog</router-link>
          </div>
          <template v-else>
            <ul class="divide-y divide-slate-100 dark:divide-slate-700">
              <li v-for="item in rfqList.items" :key="item.id" class="px-5 py-4 flex items-center gap-4">
                <div class="flex-1 min-w-0">
                  <h4 class="font-semibold text-slate-900 dark:text-white truncate">{{ item.product?.name || 'Product' }}</h4>
                  <p class="text-sm text-slate-500">{{ item.packaging_type?.name || 'Default' }} · {{ item.quantity }} units</p>
                </div>
                <button type="button" class="p-2 text-slate-400 hover:text-red-500" @click="removeRfqItem(item.id)">
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                </button>
              </li>
            </ul>
            <div class="p-5 border-t border-slate-200 dark:border-slate-700">
              <form @submit.prevent="submitQuote" class="space-y-4">
                <div>
                  <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Saved address</label>
                  <select v-model="form.target_address_id" @change="fillZipFromAddress" class="w-full rounded-xl border border-slate-200 dark:border-slate-600 px-3 py-2 text-sm bg-white dark:bg-slate-900">
                    <option :value="null">— Enter ZIP below —</option>
                    <option v-for="address in addresses" :key="address.id" :value="address.id">{{ address.address_line_1 }}, {{ address.city }}</option>
                  </select>
                </div>
                <div>
                  <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">ZIP code *</label>
                  <input v-model="form.delivery_zip" type="text" required class="w-full rounded-xl border border-slate-200 dark:border-slate-600 px-3 py-2 text-sm bg-white dark:bg-slate-900"/>
                </div>
                <label class="flex items-center gap-2 text-sm"><input v-model="form.requires_liftgate" type="checkbox" class="rounded"/> Requires liftgate</label>
                <label class="flex items-center gap-2 text-sm"><input v-model="form.requires_appointment" type="checkbox" class="rounded"/> Requires appointment</label>
                <p v-if="submitError" class="text-red-500 text-sm">{{ submitError }}</p>
                <button type="submit" :disabled="submitting" class="w-full py-3 rounded-xl bg-[#2962ff] text-white font-bold text-sm disabled:opacity-70">
                  {{ submitting ? 'Submitting…' : 'Submit quote request' }}
                </button>
              </form>
            </div>
          </template>
        </div>
      </aside>
    </Transition>

    <AddressFormModal :show="addressModalOpen" @close="addressModalOpen = false" @saved="onNewAddressSaved"/>
  </div>
</template>

<style scoped>
.cart-overlay-enter-active, .cart-overlay-leave-active { transition: opacity 0.3s ease; }
.cart-overlay-enter-from, .cart-overlay-leave-to { opacity: 0; }
.cart-drawer-enter-active, .cart-drawer-leave-active { transition: transform 0.35s cubic-bezier(0.32, 0.72, 0, 1); }
.cart-drawer-enter-from, .cart-drawer-leave-to { transform: translateX(100%); }
</style>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue';
import api from '../../services/api';
import AddressFormModal from '../../components/customer/AddressFormModal.vue';
import { useToast } from '../../composables/useToast';
import { useConfirm } from '../../composables/useConfirm';
import { useSortableTable } from '../../composables/useSortableTable';
import {
  quoteStatusLabel,
  quoteStatusClass,
  quoteIsActive,
  quoteIsClosed,
  formatQuoteDate,
  formatQuoteMoney,
} from '../../composables/useQuoteStatus';

const loading = ref(true);
const cartOpen = ref(false);
const addressModalOpen = ref(false);
const rfqList = ref(null);
const quotes = ref([]);
const addresses = ref([]);
const errors = ref({});
const submitError = ref('');
const submitting = ref(false);
const filter = ref('all');

const form = reactive({
  target_address_id: null,
  delivery_zip: '',
  requires_liftgate: false,
  requires_appointment: false,
});

const rfqCount = computed(() => rfqList.value?.items?.length ?? 0);

const filteredQuotes = computed(() => {
  if (filter.value === 'active') return quotes.value.filter(q => quoteIsActive(q.status));
  if (filter.value === 'closed') return quotes.value.filter(q => quoteIsClosed(q.status));
  return quotes.value;
});

const { searchQuery, processedItems: displayQuotes } = useSortableTable(filteredQuotes, {
  defaultSort: { key: 'id', dir: 'desc' },
  getSortValue: (quote, key) => {
    if (key === 'id') return quote.id;
    if (key === 'status') return quote.status;
    if (key === 'total') return Number(quote.total_estimated_cost ?? 0);
    return quote[key];
  },
  getSearchText: (quote) => [
    quote.id,
    quote.status,
    quote.delivery_zip,
    quote.total_estimated_cost,
    quote.items?.length,
  ].join(' '),
});

const filterTabs = computed(() => [
  { key: 'all', label: 'All', count: quotes.value.length },
  { key: 'active', label: 'Active', count: quotes.value.filter(q => quoteIsActive(q.status)).length },
  { key: 'closed', label: 'Closed', count: quotes.value.filter(q => quoteIsClosed(q.status)).length },
]);

const emptyMessage = computed(() => {
  if (filter.value === 'active') return 'No active quote requests.';
  if (filter.value === 'closed') return 'No closed quotes yet.';
  return 'You have not submitted any quote requests yet.';
});

function unwrapList(data) {
  const raw = data?.data ?? data;
  return Array.isArray(raw) ? raw : (raw?.data ?? []);
}

async function fetchData() {
  loading.value = true;
  try {
    const [rfqRes, quotesRes, addrRes] = await Promise.all([
      api.get('/api/v1/rfq-list'),
      api.get('/api/v1/quote-requests', { params: { per_page: 100 } }),
      api.get('/api/v1/addresses'),
    ]);
    rfqList.value = rfqRes.data.data;
    quotes.value = unwrapList(quotesRes.data);
    addresses.value = unwrapList(addrRes.data);
  } catch (e) {
    console.error(e);
    quotes.value = [];
  } finally {
    loading.value = false;
  }
}

function fillZipFromAddress() {
  const addr = addresses.value.find(a => a.id === form.target_address_id);
  form.delivery_zip = addr?.zip_code || '';
}

async function onNewAddressSaved(newAddress) {
  addressModalOpen.value = false;
  await fetchData();
  if (newAddress?.id) {
    form.target_address_id = newAddress.id;
    form.delivery_zip = newAddress.zip_code || '';
  }
}

async function removeRfqItem(itemId) {
  const ok = await useConfirm().show({ title: 'Remove item', message: 'Remove from basket?', confirmLabel: 'Remove', cancelLabel: 'Keep', variant: 'danger' });
  if (!ok) return;
  try {
    await api.delete(`/api/v1/rfq-list/items/${itemId}`);
    const res = await api.get('/api/v1/rfq-list');
    rfqList.value = res.data.data;
    useToast().success('Item removed.');
  } catch {
    useToast().error('Could not remove item.');
  }
}

async function submitQuote() {
  if (!rfqList.value?.id) return;
  submitting.value = true;
  errors.value = {};
  submitError.value = '';
  try {
    await api.post('/api/v1/quote-requests', {
      rfq_list_id: rfqList.value.id,
      target_address_id: form.target_address_id,
      delivery_zip: form.delivery_zip,
      requires_liftgate: form.requires_liftgate,
      requires_appointment: form.requires_appointment,
    });
    form.target_address_id = null;
    form.delivery_zip = '';
    form.requires_liftgate = false;
    form.requires_appointment = false;
    cartOpen.value = false;
    await fetchData();
    useToast().success('Quote request submitted.');
  } catch (error) {
    if (error.response?.status === 422) {
      errors.value = error.response.data.errors || {};
    } else {
      submitError.value = error.response?.data?.message || 'Submission failed.';
    }
  } finally {
    submitting.value = false;
  }
}

onMounted(fetchData);
</script>
