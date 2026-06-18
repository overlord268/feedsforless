<template>
  <div class="space-y-5 max-w-7xl mx-auto pb-10">
    <div class="flex items-center gap-4">
      <router-link :to="{ name: 'AdminQuotes' }" class="p-2 -ml-2 text-slate-400 hover:text-slate-600 rounded-lg transition-colors">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
      </router-link>
      <div class="min-w-0 flex-1">
        <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">Quote / RFQ Details</h1>
        <p class="text-slate-500 mt-0.5 text-sm">Review request details, set pricing, and update status.</p>
      </div>
    </div>

    <div v-if="loading" class="py-12 flex justify-center">
      <div class="text-slate-500">Loading quote details...</div>
    </div>

    <div v-else-if="!quote" class="py-12 flex justify-center">
      <div class="text-slate-500">Quote not found.</div>
    </div>

    <div v-else class="space-y-5">
      <!-- Summary strip -->
      <div class="bg-white rounded-2xl border border-slate-200/80 shadow-card px-5 py-4 flex flex-wrap items-center gap-6 sm:gap-10">
        <div>
          <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest block mb-0.5">RFQ ID</span>
          <span class="text-base font-mono text-slate-800 font-semibold">#{{ quote.id }}</span>
        </div>
        <div>
          <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest block mb-0.5">Status</span>
          <span :class="statusClass(quote.status)" class="inline-flex px-3 py-1 rounded-lg text-xs font-black capitalize">
            {{ quote.status }}
          </span>
        </div>
        <div>
          <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest block mb-0.5">Contact</span>
          <span class="text-sm font-medium text-slate-800">{{ requesterContactName }}</span>
        </div>
        <div class="sm:ml-auto text-left sm:text-right">
          <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest block mb-0.5">Total estimated</span>
          <span class="text-xl font-black text-emerald-600">${{ formatNum(computedTotalCost) }}</span>
        </div>
      </div>

      <div class="grid grid-cols-1 xl:grid-cols-12 gap-5 items-start">
        <!-- Main column -->
        <div class="xl:col-span-8 space-y-5">
          <!-- Profile + Logistics side by side -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-5">
              <h3 class="flex items-center gap-2 text-[11px] font-black text-slate-900 uppercase tracking-widest mb-3 border-b border-slate-100 pb-2">
                <svg class="w-4 h-4 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                Corporate profile
              </h3>
              <dl class="space-y-3 text-sm">
                <div>
                  <dt class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Business</dt>
                  <dd class="font-medium text-slate-800 mt-0.5">{{ requesterCompanyName || '—' }}</dd>
                </div>
                <div>
                  <dt class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Contact</dt>
                  <dd class="font-medium text-slate-800 mt-0.5">{{ requesterContactName || '—' }}</dd>
                  <dd v-if="quote.requester?.job_title" class="text-xs text-slate-500">{{ quote.requester.job_title }}</dd>
                </div>
                <div>
                  <dt class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Email</dt>
                  <dd class="mt-0.5">
                    <a v-if="requesterEmail" :href="'mailto:' + requesterEmail" class="font-medium text-blue-600 hover:underline break-all">{{ requesterEmail }}</a>
                    <span v-else class="text-slate-800">—</span>
                  </dd>
                </div>
                <div class="grid grid-cols-2 gap-3">
                  <div>
                    <dt class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Phone</dt>
                    <dd class="font-medium text-slate-800 mt-0.5">{{ requesterPhone || '—' }}</dd>
                  </div>
                  <div>
                    <dt class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Tax ID</dt>
                    <dd class="font-medium text-slate-800 mt-0.5 truncate" :title="requesterTaxId">{{ requesterTaxId || '—' }}</dd>
                  </div>
                </div>
              </dl>
            </div>

            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-5">
              <h3 class="flex items-center gap-2 text-[11px] font-black text-slate-900 uppercase tracking-widest mb-3 border-b border-slate-100 pb-2">
                <svg class="w-4 h-4 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                Logistics
              </h3>
              <dl class="space-y-3 text-sm">
                <div>
                  <dt class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Destination</dt>
                  <dd class="font-medium text-slate-800 mt-0.5">
                    <span v-if="quote.guest_destination_address" class="block">{{ quote.guest_destination_address }}</span>
                    <span>{{ quote.delivery_zip }} (ZIP)</span>
                  </dd>
                </div>
                <div class="pt-2 border-t border-slate-100 space-y-2">
                  <div class="flex items-center justify-between">
                    <span class="text-slate-600">Lift gate</span>
                    <span class="font-bold text-sm" :class="quote.requires_liftgate ? 'text-emerald-600' : 'text-slate-400'">{{ quote.requires_liftgate ? 'Yes' : 'No' }}</span>
                  </div>
                  <div class="flex items-center justify-between">
                    <span class="text-slate-600">Pre-delivery call</span>
                    <span class="font-bold text-sm" :class="quote.requires_appointment ? 'text-emerald-600' : 'text-slate-400'">{{ quote.requires_appointment ? 'Yes' : 'No' }}</span>
                  </div>
                </div>
              </dl>
            </div>
          </div>

          <!-- Pricing -->
          <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-100 flex flex-wrap items-center justify-between gap-3">
              <h3 class="flex items-center gap-2 text-[11px] font-black text-slate-900 uppercase tracking-widest">
                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                Commodities & pricing
              </h3>
              <button
                type="button"
                class="px-4 py-2 rounded-xl bg-emerald-600 text-white text-xs font-bold hover:bg-emerald-700 transition-colors shadow-sm disabled:opacity-70 inline-flex items-center gap-2"
                :disabled="savingPrices || !quote.items?.length"
                @click="savePrices"
              >
                <svg v-if="savingPrices" class="animate-spin h-3.5 w-3.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/></svg>
                {{ savingPrices ? 'Saving…' : 'Save & mark quoted' }}
              </button>
            </div>

            <div class="p-5">
              <p v-if="!quote.items?.length" class="text-slate-500 text-sm italic">No items found for this request.</p>
              <div v-else class="space-y-4">
                <div
                  v-for="item in quote.items"
                  :key="item.id"
                  class="grid grid-cols-1 lg:grid-cols-12 gap-3 lg:gap-4 items-end border border-slate-200 rounded-xl p-4 bg-slate-50/50"
                >
                  <div class="lg:col-span-4 min-w-0">
                    <p class="font-bold text-[#2962ff] text-sm truncate">{{ item.product?.name || 'Unknown Product' }}</p>
                    <p class="text-xs text-slate-500 mt-0.5">{{ item.qty }} × {{ item.packaging_type?.name || 'Units' }}</p>
                  </div>
                  <div class="lg:col-span-2">
                    <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-1">Product $</label>
                    <input v-model.number="priceForm[priceRowKey(item.id)].estimated_product_cost" type="number" step="0.01" min="0" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm font-mono focus:ring-2 focus:ring-[#2962ff] focus:border-[#2962ff]" placeholder="0.00" />
                  </div>
                  <div class="lg:col-span-2">
                    <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-1">Freight $</label>
                    <input v-model.number="priceForm[priceRowKey(item.id)].estimated_freight_cost" type="number" step="0.01" min="0" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm font-mono focus:ring-2 focus:ring-[#2962ff] focus:border-[#2962ff]" placeholder="0.00" />
                  </div>
                  <div class="lg:col-span-4 lg:text-right">
                    <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Line total</span>
                    <span class="text-sm font-bold text-slate-800 font-mono">
                      ${{ formatNum(lineItemTotal(item)) }}
                    </span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Sticky sidebar: status + chat -->
        <aside class="xl:col-span-4 space-y-5 xl:sticky xl:top-4">
          <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-5">
            <h3 class="font-bold text-slate-800 mb-3 text-sm">Status</h3>
            <div class="space-y-3">
              <div class="flex gap-2">
                <select v-model="detailForm.status" class="flex-1 min-w-0 rounded-lg border border-slate-300 px-3 py-2 text-sm focus:ring-2 focus:ring-[#2962ff] focus:border-[#2962ff] bg-white">
                  <option value="pending">Pending</option>
                  <option value="quoted">Quoted</option>
                  <option value="accepted">Accepted</option>
                  <option value="rejected">Rejected</option>
                  <option value="expired">Expired</option>
                  <option value="cancelled">Cancelled</option>
                </select>
                <button type="button" class="px-4 py-2 rounded-xl bg-[#2962ff] text-white text-sm font-bold hover:bg-blue-800 disabled:opacity-70 shrink-0" :disabled="savingStatus" @click="saveStatus">
                  {{ savingStatus ? '…' : 'Update' }}
                </button>
              </div>

              <p class="text-[11px] text-amber-800 bg-amber-50 border border-amber-100 rounded-lg px-3 py-2 leading-relaxed">
                Saving prices marks the quote as <strong>Quoted</strong> and shows pricing to the client.
              </p>

              <div v-if="showCustomerMessage">
                <label class="block text-xs font-medium text-slate-600 mb-1">Message to customer</label>
                <textarea v-model="detailForm.customer_message" rows="2" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm resize-none focus:ring-2 focus:ring-[#2962ff]" placeholder="Reason or next steps…"></textarea>
              </div>

              <div>
                <div class="flex justify-between items-center mb-1">
                  <label class="text-xs font-medium text-slate-600">Internal note</label>
                  <span v-if="savingNote" class="text-[10px] text-slate-400 animate-pulse">Saving…</span>
                  <span v-else-if="noteSaved" class="text-[10px] text-emerald-600 font-bold">Saved</span>
                </div>
                <textarea v-model="detailForm.admin_note" rows="3" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm resize-none focus:ring-2 focus:ring-[#2962ff]" placeholder="Admin only — hidden from customer"></textarea>
              </div>
            </div>
          </div>

          <div id="quote-chat" class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden flex flex-col scroll-mt-4 h-[min(480px,calc(100vh-12rem))]">
            <div class="px-5 py-3 border-b border-slate-100 shrink-0">
              <h3 class="text-[11px] font-black text-slate-900 uppercase tracking-widest">Quote conversation</h3>
              <p class="text-[11px] text-slate-500 mt-0.5">RFQ #{{ quote.id }} only — not general chat.</p>
            </div>
            <ChatPanel
              class="flex-1 min-h-0"
              variant="admin"
              link-context="admin"
              hide-start-form
              :conversation-id="quoteConversationId"
              :messages="quoteChatMessages"
              :loading="quoteChatLoading"
              :sending="quoteChatSending"
              :error="quoteChatError"
              @send="sendQuoteChatMessage"
            />
          </div>
        </aside>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted, onUnmounted, computed, watch } from 'vue';
import { useRoute } from 'vue-router';
import api from '../../services/api';
import { useToast } from '../../composables/useToast';
import ChatPanel from '../../components/chat/ChatPanel.vue';
import { useQuoteChat } from '../../composables/useQuoteChat';
import { useAdminChatNotifier } from '../../composables/useAdminChatNotifier';

const route = useRoute();
const toast = useToast();
const { refresh: refreshChatNotifier } = useAdminChatNotifier();

const quoteId = route.params.id;
const quote = ref(null);
const loading = ref(true);

const {
  conversationId: quoteConversationId,
  chatMessages: quoteChatMessages,
  loading: quoteChatLoading,
  sending: quoteChatSending,
  error: quoteChatError,
  loadConversation: loadQuoteChat,
  markConversationRead: markQuoteChatRead,
  sendMessage: sendQuoteChatMessage,
  startPolling: startQuoteChatPolling,
  stopPolling: stopQuoteChatPolling,
} = useQuoteChat(quoteId, { admin: true });

const detailForm = reactive({ status: 'pending', admin_note: '', customer_message: '' });

const showCustomerMessage = computed(() =>
  ['rejected', 'cancelled', 'expired'].includes(detailForm.status)
);
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

const requesterCompanyName = computed(() => {
  const q = quote.value;
  if (!q?.requester) return q?.guest_company_name || '—';
  return q.requester.company_name || q.guest_company_name || '—';
});

const requesterContactName = computed(() => {
  const q = quote.value;
  if (!q?.requester) return q?.guest_contact_name || '—';
  const first = q.requester.first_name || '';
  const last = q.requester.last_name || '';
  const full = trim(`${first} ${last}`);
  return full || q.requester.contact_name || q?.guest_contact_name || '—';
});

const requesterEmail = computed(() => {
  const q = quote.value;
  return q?.requester?.email || q?.guest_email || '';
});

const requesterPhone = computed(() => {
  const q = quote.value;
  return q?.requester?.phone || q?.guest_phone || '';
});

const requesterTaxId = computed(() => {
  const q = quote.value;
  return q?.requester?.tax_id ?? q?.guest_tax_id ?? '';
});

function trim(s) {
  return (s || '').trim();
}

function priceRowKey(itemId) {
  return String(itemId);
}

function lineItemTotal(item) {
  const prices = priceForm[priceRowKey(item.id)];
  const product = Number(prices?.estimated_product_cost) || 0;
  const freight = Number(prices?.estimated_freight_cost) || 0;
  const qty = Number(item.qty) || 0;
  return (product + freight) * qty;
}

const computedTotalCost = computed(() => {
  if (!quote.value?.items) return quote.value?.total_estimated_cost || 0;

  return quote.value.items.reduce((sum, item) => sum + lineItemTotal(item), 0);
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

    detailForm.status = quote.value?.status || 'pending';
    detailForm.admin_note = quote.value?.admin_note || '';
    detailForm.customer_message = quote.value?.customer_message || '';

    syncPriceFormFromQuote();
  } catch (e) {
    console.error(e);
    toast.error('Could not load quote details.');
  } finally {
    loading.value = false;
  }
}

function syncPriceFormFromQuote() {
  Object.keys(priceForm).forEach(k => delete priceForm[k]);
  (quote.value?.items || []).forEach(it => {
    priceForm[priceRowKey(it.id)] = {
      estimated_product_cost: Number(it.estimated_product_cost) || 0,
      estimated_freight_cost: Number(it.estimated_freight_cost) || 0,
    };
  });
}

async function saveStatus() {
  if (!quote.value) return;
  savingStatus.value = true;
  try {
    await api.put(`/api/v1/admin/quote-requests/${quote.value.id}/status`, {
      status: detailForm.status,
      admin_note: detailForm.admin_note,
      customer_message: detailForm.customer_message || null,
    });
    quote.value.status = detailForm.status;
    quote.value.customer_message = detailForm.customer_message;
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
      estimated_product_cost: Number(priceForm[priceRowKey(it.id)]?.estimated_product_cost) || 0,
      estimated_freight_cost: Number(priceForm[priceRowKey(it.id)]?.estimated_freight_cost) || 0,
    }));
    const { data } = await api.put(`/api/v1/admin/quote-requests/${quote.value.id}/prices`, { items });
    quote.value = data?.data ?? data;
    detailForm.status = 'quoted';
    syncPriceFormFromQuote();
    toast.success('Prices saved and quote marked as Quoted.');
  } catch (e) {
    console.error(e);
    toast.error('Error saving prices. Ensure each line has a valid cost.');
  } finally {
    savingPrices.value = false;
  }
}

onMounted(async () => {
  await fetchQuote();
  const openChat = route.hash === '#quote-chat';
  await loadQuoteChat({ markRead: openChat });
  if (openChat) await refreshChatNotifier();
  startQuoteChatPolling();
  if (openChat) {
    requestAnimationFrame(() => {
      document.getElementById('quote-chat')?.scrollIntoView({ behavior: 'smooth' });
    });
  }
});

watch(() => route.hash, async (hash) => {
  if (hash === '#quote-chat') {
    await markQuoteChatRead();
    await refreshChatNotifier();
  }
});

onUnmounted(() => {
  stopQuoteChatPolling();
});
</script>
