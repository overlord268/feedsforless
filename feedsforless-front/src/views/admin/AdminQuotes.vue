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
                <span :class="statusClass(quote.status)" class="inline-flex px-2.5 py-0.5 rounded-md text-xs font-semibold capitalize">
                  {{ quote.status }}
                </span>
              </td>
              <td class="px-6 py-4 text-slate-700 font-medium">${{ formatNum(quote.total_estimated_cost) }}</td>
              <td class="px-6 py-4">
                <router-link
                  :to="{ name: 'AdminQuoteDetails', params: { id: quote.id } }"
                  class="text-emerald-600 hover:underline font-medium"
                >
                  View / Edit
                </router-link>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
      <p v-if="!loading && quotes.length === 0" class="px-6 py-12 text-center text-slate-500">No quotes.</p>
      <div v-if="loading" class="px-6 py-12 text-center text-slate-500">Loading…</div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import api from '../../services/api';
import { useToast } from '../../composables/useToast';

const router = useRouter();
const quotes = ref([]);
const loading = ref(true);

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

onMounted(fetchQuotes);
</script>
