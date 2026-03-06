<template>
  <div class="space-y-5">
    <div>
      <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Quotes (RFQs)</h1>
      <p class="text-slate-500 mt-0.5">Requests sent by customers. Review, set prices and change status.</p>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-card overflow-hidden">
      <div class="overflow-x-auto table-scroll">
        <table class="w-full text-sm min-w-[600px]">
          <thead class="bg-slate-50/80 border-b border-slate-200">
            <tr class="text-left">
              <th class="px-6 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider">ID</th>
              <th class="px-6 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider">Customer</th>
              <th class="px-6 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider">ZIP</th>
              <th class="px-6 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider">Status</th>
              <th class="px-6 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider">Total est.</th>
              <th class="px-6 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            <tr v-for="quote in quotes" :key="quote.id" class="hover:bg-slate-50/70 transition-colors">
              <td class="px-6 py-4 text-slate-700 font-mono">#{{ quote.id }}</td>
              <td class="px-6 py-4 text-slate-800">{{ quote.customer_name || quote.requester?.email || '—' }}</td>
              <td class="px-6 py-4 text-slate-600">{{ quote.delivery_zip }}</td>
              <td class="px-6 py-4">
                <span :class="statusClass(quote.status)" class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-semibold capitalize">
                  {{ quote.status }}
                </span>
              </td>
              <td class="px-6 py-4 text-slate-700 font-medium">${{ formatNum(quote.total_estimated_cost) }}</td>
              <td class="px-6 py-4">
                <button
                  type="button"
                  class="text-emerald-600 hover:underline font-medium"
                  @click="openDetail(quote)"
                >
                  View / Edit
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
      <p v-if="!loading && quotes.length === 0" class="px-6 py-12 text-center text-slate-500">No quotes.</p>
      <div v-if="loading" class="px-6 py-12 text-center text-slate-500">Loading…</div>
    </div>

    <div v-if="detailQuote" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/40 backdrop-blur-sm overflow-y-auto" @click.self="closeDetail">
      <div class="bg-white rounded-2xl shadow-2xl max-w-2xl w-full my-8 max-h-[90vh] overflow-y-auto border border-slate-200/80">
        <div class="p-6 border-b border-slate-200 flex items-center justify-between sticky top-0 bg-white">
          <h2 class="text-lg font-semibold text-slate-900">Quote #{{ detailQuote.id }}</h2>
          <button type="button" class="p-2 text-slate-400 hover:text-slate-600 rounded-lg" @click="closeDetail" aria-label="Close">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
          </button>
        </div>
        <div class="p-6 space-y-6">
          <p class="text-sm text-slate-600"><strong>Customer:</strong> {{ detailQuote.customer_name || detailQuote.requester?.email }}</p>
          <p class="text-sm text-slate-600"><strong>Delivery ZIP:</strong> {{ detailQuote.delivery_zip }}</p>

          <div class="border border-slate-200 rounded-xl p-4 bg-slate-50/50">
            <h3 class="font-medium text-slate-800 mb-3">Status and internal note</h3>
            <div class="flex flex-wrap gap-3 items-end">
              <div class="flex-1 min-w-[140px]">
                <label class="block text-xs font-medium text-slate-500 mb-1">Status</label>
                <select v-model="detailForm.status" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-900">
                  <option value="pending">Pending</option>
                  <option value="quoted">Quoted</option>
                  <option value="accepted">Accepted</option>
                  <option value="rejected">Rejected</option>
                  <option value="expired">Expired</option>
                  <option value="cancelled">Cancelled</option>
                </select>
              </div>
              <div class="flex-[2] min-w-[200px]">
                <label class="block text-xs font-medium text-slate-500 mb-1">Internal note (admin)</label>
                <input v-model="detailForm.admin_note" type="text" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-900" placeholder="Optional" />
              </div>
              <button type="button" class="px-4 py-2 rounded-lg bg-emerald-600 text-white text-sm font-medium hover:bg-emerald-700" :disabled="savingStatus" @click="saveStatus">
                {{ savingStatus ? 'Saving…' : 'Save status' }}
              </button>
            </div>
          </div>

          <div class="border border-slate-200 rounded-xl p-4">
            <h3 class="font-medium text-slate-800 mb-3">Lines and prices</h3>
            <p v-if="!detailQuote.items || detailQuote.items.length === 0" class="text-slate-500 text-sm">No items.</p>
            <ul v-else class="space-y-3">
              <li v-for="item in detailQuote.items" :key="item.id" class="flex flex-wrap items-center gap-3 text-sm border-b border-slate-100 pb-3 last:border-0">
                <span class="flex-1 min-w-[120px]">{{ item.product?.name || 'Product' }} (x{{ item.qty }})</span>
                <div class="flex gap-2 items-center">
                  <input v-model.number="priceForm[item.id].estimated_product_cost" type="number" step="0.01" min="0" class="w-24 rounded border border-slate-300 px-2 py-1 text-right" placeholder="Prod." />
                  <input v-model.number="priceForm[item.id].estimated_freight_cost" type="number" step="0.01" min="0" class="w-24 rounded border border-slate-300 px-2 py-1 text-right" placeholder="Freight" />
                </div>
              </li>
            </ul>
            <button type="button" class="mt-3 px-4 py-2 rounded-lg bg-slate-800 text-white text-sm font-medium hover:bg-slate-700" :disabled="savingPrices" @click="savePrices">
              {{ savingPrices ? 'Saving…' : 'Save prices and mark as Quoted' }}
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue';
import api from '../../services/api';
import { useToast } from '../../composables/useToast';

const quotes = ref([]);
const loading = ref(true);
const detailQuote = ref(null);
const detailForm = reactive({ status: 'pending', admin_note: '' });
const priceForm = reactive({});
const savingStatus = ref(false);
const savingPrices = ref(false);

function formatNum(v) {
  if (v == null) return '0.00';
  return Number(v).toFixed(2);
}

function statusClass(status) {
  const map = {
    pending: 'bg-amber-100 text-amber-700',
    quoted: 'bg-blue-100 text-blue-700',
    accepted: 'bg-emerald-100 text-emerald-700',
    rejected: 'bg-red-100 text-red-700',
    expired: 'bg-slate-100 text-slate-600',
    cancelled: 'bg-slate-100 text-slate-600',
  };
  return map[status] || 'bg-slate-100 text-slate-600';
}

async function fetchQuotes() {
  loading.value = true;
  try {
    const { data } = await api.get('/api/v1/admin/quote-requests');
    const raw = data?.data ?? data;
    quotes.value = Array.isArray(raw) ? raw : (raw?.data ?? []);
  } catch (e) {
    console.error(e);
    quotes.value = [];
  } finally {
    loading.value = false;
  }
}

function openDetail(quote) {
  detailQuote.value = quote;
  detailForm.status = quote.status || 'pending';
  detailForm.admin_note = quote.admin_note || '';
  Object.keys(priceForm).forEach(k => delete priceForm[k]);
  (quote.items || []).forEach(it => {
    priceForm[it.id] = {
      estimated_product_cost: it.estimated_product_cost ?? 0,
      estimated_freight_cost: it.estimated_freight_cost ?? 0,
    };
  });
}

function closeDetail() {
  detailQuote.value = null;
}

async function saveStatus() {
  if (!detailQuote.value) return;
  savingStatus.value = true;
  try {
    await api.put(`/api/v1/admin/quote-requests/${detailQuote.value.id}/status`, {
      status: detailForm.status,
      admin_note: detailForm.admin_note,
    });
    const idx = quotes.value.findIndex(q => q.id === detailQuote.value.id);
    if (idx !== -1) {
      quotes.value[idx] = { ...quotes.value[idx], status: detailForm.status, admin_note: detailForm.admin_note };
    }
    detailQuote.value = { ...detailQuote.value, status: detailForm.status, admin_note: detailForm.admin_note };
  } catch (e) {
    console.error(e);
    useToast().error('Could not update status.');
  } finally {
    savingStatus.value = false;
  }
}

async function savePrices() {
  if (!detailQuote.value || !detailQuote.value.items?.length) return;
  savingPrices.value = true;
  try {
    const items = detailQuote.value.items.map(it => ({
      id: it.id,
      estimated_product_cost: Number(priceForm[it.id]?.estimated_product_cost) || 0,
      estimated_freight_cost: Number(priceForm[it.id]?.estimated_freight_cost) || 0,
    }));
    await api.put(`/api/v1/admin/quote-requests/${detailQuote.value.id}/prices`, { items });
    await fetchQuotes();
    const updated = quotes.value.find(q => q.id === detailQuote.value.id);
    if (updated) detailQuote.value = updated;
    detailForm.status = 'quoted';
  } catch (e) {
    console.error(e);
    useToast().error('Error saving prices. Ensure each line has product and freight cost.');
  } finally {
    savingPrices.value = false;
  }
}

onMounted(fetchQuotes);
</script>
