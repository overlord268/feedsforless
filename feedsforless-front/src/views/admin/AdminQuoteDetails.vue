<template>
  <div class="space-y-6 max-w-5xl mx-auto pb-12">
    <div class="flex items-center gap-4">
      <router-link :to="{ name: 'AdminQuotes' }" class="p-2 -ml-2 text-slate-400 hover:text-slate-600 rounded-lg transition-colors">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
      </router-link>
      <div>
        <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Quote / RFQ Details</h1>
        <p class="text-slate-500 mt-0.5 text-sm">Review request details, set pricing, and update status.</p>
      </div>
    </div>

    <div v-if="loading" class="py-12 flex justify-center">
      <div class="text-slate-500">Loading quote details...</div>
    </div>

    <div v-else-if="!quote" class="py-12 flex justify-center">
      <div class="text-slate-500">Quote not found.</div>
    </div>

    <div v-else class="space-y-6">
      <!-- Header Info Card -->
      <div class="bg-white rounded-2xl border border-slate-200/80 shadow-card p-6 flex flex-wrap items-center justify-between gap-4">
        <div>
          <span class="text-xs font-bold text-slate-400 uppercase tracking-widest block mb-1">RFQ ID</span>
          <span class="text-lg font-mono text-slate-800 font-semibold">#{{ quote.id }}</span>
        </div>
        <div>
          <span class="text-xs font-bold text-slate-400 uppercase tracking-widest block mb-1">Current Status</span>
          <span :class="statusClass(quote.status)" class="inline-flex px-4 py-1.5 rounded-lg text-sm font-black capitalize">
            {{ quote.status }}
          </span>
        </div>
        <div class="text-right">
          <span class="text-xs font-bold text-slate-400 uppercase tracking-widest block mb-1">Total Estimated</span>
          <span class="text-2xl font-black text-emerald-600">${{ formatNum(computedTotalCost) }}</span>
        </div>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Client Information -->
        <div class="lg:col-span-2 space-y-6">
          <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6">
            <h3 class="flex items-center gap-2 text-[11px] font-black text-slate-900 uppercase tracking-widest mb-4 border-b-2 border-slate-100 pb-3">
              <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
              Corporate Entity Profile
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-y-4 gap-x-6">
              <div>
                <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Legal Business Name</span>
                <span class="text-sm font-medium text-slate-800">{{ quote.requester?.company_name || '—' }}</span>
              </div>
              <div>
                <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Authorized Contact</span>
                <span class="text-sm font-medium text-slate-800">{{ quote.requester?.first_name }} {{ quote.requester?.last_name }}</span>
                <div class="text-xs text-slate-500 mt-0.5" v-if="quote.requester?.job_title">{{ quote.requester?.job_title }}</div>
              </div>
              <div>
                <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Business Email</span>
                <a :href="'mailto:' + quote.requester?.email" class="text-sm font-medium text-blue-600 hover:underline">{{ quote.requester?.email || '—' }}</a>
              </div>
              <div>
                <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Primary Phone</span>
                <span class="text-sm font-medium text-slate-800">{{ quote.requester?.phone || '—' }}</span>
              </div>
              <div class="md:col-span-2">
                <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Tax ID / Registration</span>
                <span class="text-sm font-medium text-slate-800">{{ quote.requester?.tax_id || '—' }}</span>
              </div>
            </div>
          </div>

          <!-- Logistics -->
          <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6">
            <h3 class="flex items-center gap-2 text-[11px] font-black text-slate-900 uppercase tracking-widest mb-4 border-b-2 border-slate-100 pb-3">
              <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
              Supply Chain & Logistics
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-y-4 gap-x-6">
              <div class="md:col-span-2">
                <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Destination Location</span>
                <span class="text-sm font-medium text-slate-800 mb-1 block">{{ quote.delivery_zip }} (ZIP Code)</span>
                <!-- Since address is usually in address profile or handled separately if implemented -->
              </div>
              
              <div class="md:col-span-2 flex flex-col gap-2 mt-2 pt-4 border-t border-slate-100">
                <div class="flex items-center justify-between text-sm py-1">
                  <span class="text-slate-600 font-medium">Lift Gate Accessorial Required</span>
                  <span class="font-bold" :class="quote.requires_liftgate ? 'text-emerald-600' : 'text-slate-400'">{{ quote.requires_liftgate ? 'Yes' : 'No' }}</span>
                </div>
                <div class="flex items-center justify-between text-sm py-1">
                  <span class="text-slate-600 font-medium">Pre-Delivery Call Required</span>
                  <span class="font-bold" :class="quote.requires_appointment ? 'text-emerald-600' : 'text-slate-400'">{{ quote.requires_appointment ? 'Yes' : 'No' }}</span>
                </div>
              </div>
            </div>
          </div>

          <!-- Items and Prices -->
          <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-0 overflow-hidden">
            <div class="p-6 border-b border-slate-100 flex items-center justify-between">
              <h3 class="flex items-center gap-2 text-[11px] font-black text-slate-900 uppercase tracking-widest">
                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                Requested Commodities & Pricing
              </h3>
            </div>
            
            <div class="p-6">
              <p v-if="!quote.items || quote.items.length === 0" class="text-slate-500 text-sm italic">No items found for this request.</p>
              <div v-else class="space-y-6">
                <div v-for="item in quote.items" :key="item.id" class="border border-slate-200 rounded-xl p-4 bg-slate-50/50">
                  <div class="flex flex-col md:flex-row md:items-start justify-between gap-4 mb-4 border-b border-slate-200 pb-4">
                    <div>
                      <h4 class="font-bold text-[#2962ff] text-base">{{ item.product?.name || 'Unknown Product' }}</h4>
                      <div class="text-xs font-medium text-slate-500 mt-1">
                        {{ item.qty }} × {{ item.packaging_type?.name || 'Units' }}
                      </div>
                    </div>
                  </div>
                  
                  <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                      <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-1.5">Unit Product Cost ($)</label>
                      <input v-model.number="priceForm[item.id].estimated_product_cost" type="number" step="0.01" min="0" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-900 focus:ring-2 focus:ring-[#2962ff] focus:border-[#2962ff] font-mono" placeholder="0.00" />
                    </div>
                    <div>
                      <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-1.5">Unit Freight Cost ($)</label>
                      <input v-model.number="priceForm[item.id].estimated_freight_cost" type="number" step="0.01" min="0" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-900 focus:ring-2 focus:ring-[#2962ff] focus:border-[#2962ff] font-mono" placeholder="0.00" />
                    </div>
                    <div class="flex flex-col justify-end bg-slate-100 rounded-lg p-2 px-3 text-right">
                      <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-0.5">Line Total</span>
                      <span class="text-sm font-bold text-slate-800 font-mono">
                        ${{ formatNum((priceForm[item.id].estimated_product_cost + priceForm[item.id].estimated_freight_cost) * item.qty) }}
                      </span>
                    </div>
                  </div>
                </div>

                <div class="flex justify-end pt-2">
                  <button type="button" class="px-6 py-2.5 rounded-xl bg-emerald-600 text-white text-sm font-bold tracking-wide hover:bg-emerald-700 transition-colors shadow-sm disabled:opacity-70 flex items-center gap-2" :disabled="savingPrices" @click="savePrices">
                    <svg v-if="savingPrices" class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                    {{ savingPrices ? 'Saving…' : 'Save Prices & Mark Quoted' }}
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Sidebar Actions -->
        <div class="space-y-6">
          <div class="bg-slate-50 rounded-2xl border border-slate-200/80 shadow-sm p-6">
            <h3 class="font-bold text-slate-800 mb-4 text-sm">Status Management</h3>
            <div class="space-y-4">
              <div class="flex items-end gap-3">
                <div class="flex-1">
                  <label class="block text-xs font-medium text-slate-600 mb-1.5">Quote Status</label>
                  <select v-model="detailForm.status" class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm text-slate-900 focus:ring-2 focus:ring-[#2962ff] focus:border-[#2962ff] bg-white">
                    <option value="pending">Pending</option>
                    <option value="quoted">Quoted</option>
                    <option value="accepted">Accepted</option>
                    <option value="rejected">Rejected</option>
                    <option value="expired">Expired</option>
                    <option value="cancelled">Cancelled</option>
                  </select>
                </div>
                <button type="button" class="px-4 py-2.5 rounded-xl bg-[#2962ff] text-white text-sm font-bold tracking-wide hover:bg-blue-800 transition-colors shadow-sm disabled:opacity-70 flex items-center justify-center gap-2" :disabled="savingStatus" @click="saveStatus">
                  <svg v-if="savingStatus" class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                  <span v-else>Update</span>
                </button>
              </div>

              <div class="bg-amber-50 rounded-xl border border-amber-200/80 shadow-sm p-4">
                 <div class="flex gap-3">
                   <svg class="w-5 h-5 text-amber-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                   <p class="text-[11px] text-amber-800 font-medium leading-relaxed">
                     When you click "Save Prices & Mark Quoted", the client will be able to see the new price and the status will automatically change to Quoted.
                   </p>
                 </div>
              </div>

              <div class="pt-4 border-t border-slate-200/80">
                <div class="flex justify-between items-center mb-1.5">
                  <label class="block text-xs font-medium text-slate-600">Internal Note (Admin Only)</label>
                  <span v-if="savingNote" class="text-[10px] text-slate-400 font-medium animate-pulse">Saving...</span>
                  <span v-else-if="noteSaved" class="text-[10px] text-emerald-600 font-bold transition-opacity">Saved</span>
                </div>
                <textarea v-model="detailForm.admin_note" rows="4" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-900 focus:ring-2 focus:ring-[#2962ff] focus:border-[#2962ff] bg-white resize-none" placeholder="Notes entirely hidden from customer..."></textarea>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted, computed, watch } from 'vue';
import { useRoute } from 'vue-router';
import api from '../../services/api';
import { useToast } from '../../composables/useToast';

const route = useRoute();
const toast = useToast();

const quoteId = route.params.id;
const quote = ref(null);
const loading = ref(true);

const detailForm = reactive({ status: 'pending', admin_note: '' });
const priceForm = reactive({});
const savingStatus = ref(false);
const savingPrices = ref(false);
const savingNote = ref(false);
const noteSaved = ref(false);

let noteDebounceTimer = null;
let ignoreFirstNoteWatch = true;

watch(() => detailForm.admin_note, (newVal) => {
  if (ignoreFirstNoteWatch) {
    ignoreFirstNoteWatch = false;
    return;
  }
  
  noteSaved.value = false;
  clearTimeout(noteDebounceTimer);
  
  noteDebounceTimer = setTimeout(async () => {
    if (!quote.value) return;
    savingNote.value = true;
    try {
      await api.put(`/api/v1/admin/quote-requests/${quote.value.id}/status`, {
        status: detailForm.status,
        admin_note: newVal,
      });
      quote.value.admin_note = newVal;
      
      noteSaved.value = true;
      setTimeout(() => { noteSaved.value = false; }, 3000);
    } catch (e) {
      console.error('Error auto-saving note', e);
      toast.error('Failed to auto-save note.');
    } finally {
      savingNote.value = false;
    }
  }, 700);
});

const computedTotalCost = computed(() => {
  if (!quote.value?.items) return quote.value?.total_estimated_cost || 0;
  
  let total = 0;
  quote.value.items.forEach(item => {
    const prices = priceForm[item.id] || { estimated_product_cost: 0, estimated_freight_cost: 0 };
    total += (Number(prices.estimated_product_cost) + Number(prices.estimated_freight_cost)) * item.qty;
  });
  
  return total;
});

function formatNum(v) {
  if (v == null || isNaN(v)) return '0.00';
  return Number(v).toFixed(2);
}

function statusClass(status) {
  const map = {
    pending: 'bg-amber-100 text-amber-700 border border-amber-200',
    quoted: 'bg-blue-100 text-blue-700 border border-blue-200',
    accepted: 'bg-emerald-100 text-emerald-700 border border-emerald-200',
    rejected: 'bg-red-100 text-red-700 border border-red-200',
    expired: 'bg-slate-100 text-slate-600 border border-slate-200',
    cancelled: 'bg-slate-100 text-slate-600 border border-slate-200',
  };
  return map[status] || 'bg-slate-100 text-slate-600 border border-slate-200';
}

async function fetchQuote() {
  if (!quoteId) return;
  loading.value = true;
  try {
    const { data } = await api.get(`/api/v1/admin/quote-requests/${quoteId}`);
    quote.value = data?.data ?? data;
    
    // Initialize forms
    detailForm.status = quote.value?.status || 'pending';
    detailForm.admin_note = quote.value?.admin_note || '';
    
    Object.keys(priceForm).forEach(k => delete priceForm[k]);
    (quote.value?.items || []).forEach(it => {
      priceForm[it.id] = {
        estimated_product_cost: it.estimated_product_cost ?? 0,
        estimated_freight_cost: it.estimated_freight_cost ?? 0,
      };
    });
  } catch (e) {
    console.error(e);
    toast.error('Could not load quote details.');
  } finally {
    loading.value = false;
  }
}

async function saveStatus() {
  if (!quote.value) return;
  savingStatus.value = true;
  try {
    await api.put(`/api/v1/admin/quote-requests/${quote.value.id}/status`, {
      status: detailForm.status,
      admin_note: detailForm.admin_note,
    });
    quote.value.status = detailForm.status;
    toast.success('Status updated successfully.');
  } catch (e) {
    console.error(e);
    toast.error('Could not update status.');
  } finally {
    savingStatus.value = false;
  }
}

async function savePrices() {
  if (!quote.value || !quote.value.items?.length) return;
  savingPrices.value = true;
  try {
    const items = quote.value.items.map(it => ({
      id: it.id,
      estimated_product_cost: Number(priceForm[it.id]?.estimated_product_cost) || 0,
      estimated_freight_cost: Number(priceForm[it.id]?.estimated_freight_cost) || 0,
    }));
    const { data } = await api.put(`/api/v1/admin/quote-requests/${quote.value.id}/prices`, { items });
    quote.value = data?.data ?? data;
    detailForm.status = 'quoted'; // Automatically updated in backend
    toast.success('Prices saved and quote marked as Quoted.');
  } catch (e) {
    console.error(e);
    toast.error('Error saving prices. Ensure each line has a valid cost.');
  } finally {
    savingPrices.value = false;
  }
}

onMounted(() => {
  fetchQuote();
});
</script>
