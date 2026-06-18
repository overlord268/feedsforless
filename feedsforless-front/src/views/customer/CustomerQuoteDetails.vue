<template>
  <div class="min-h-full bg-[#f8fafc] dark:bg-slate-900 animate-in fade-in duration-300">
    <div class="max-w-6xl mx-auto px-4 py-8 md:py-10">
      <router-link
        to="/quotes"
        class="inline-flex items-center gap-2 text-sm font-semibold text-[#2962ff] hover:text-[#003366] mb-6 transition-colors"
      >
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        Back to My Quotes
      </router-link>

      <div v-if="loading" class="py-16 text-center text-slate-500">Loading quote…</div>

      <div v-else-if="!quote" class="py-16 text-center">
        <p class="text-slate-600 mb-4">Quote not found.</p>
        <router-link to="/quotes" class="text-[#2962ff] font-semibold text-sm">Return to quotes</router-link>
      </div>

      <template v-else>
        <header class="mb-8">
          <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
              <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-1">Quote request</p>
              <h1 class="text-2xl md:text-3xl font-black text-[#003366] dark:text-white tracking-tight">RFQ #{{ quote.id }}</h1>
              <p class="text-sm text-slate-500 mt-1">Submitted {{ formatQuoteDate(quote.created_at) }} · Updated {{ formatQuoteDate(quote.updated_at) }}</p>
            </div>
            <span class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-bold ring-1 ring-inset capitalize" :class="quoteStatusClass(quote.status)">
              {{ quoteStatusLabel(quote.status) }}
            </span>
          </div>
        </header>

        <section class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm p-6 mb-6">
          <h2 class="text-[11px] font-black uppercase tracking-widest text-slate-400 mb-6">Progress</h2>
          <QuoteTimeline :steps="timelineSteps" />
        </section>

        <div
          v-if="quote.customer_message"
          class="mb-6 p-4 rounded-xl border"
          :class="quote.status === 'quoted' ? 'bg-blue-50 border-blue-200 text-blue-900' : 'bg-amber-50 border-amber-200 text-amber-900'"
        >
          <p class="text-[10px] font-black uppercase tracking-widest mb-1 opacity-70">Message from our team</p>
          <p class="text-sm whitespace-pre-wrap">{{ quote.customer_message }}</p>
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-12 gap-5 items-start">
          <section class="xl:col-span-8 bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-100 dark:border-slate-700">
              <h2 class="text-[11px] font-black uppercase tracking-widest text-slate-400">Requested items</h2>
            </div>
            <div class="divide-y divide-slate-100 dark:divide-slate-700">
              <div v-for="item in quote.items" :key="item.id" class="p-5">
                <div class="flex flex-wrap items-start justify-between gap-3 mb-3">
                  <div>
                    <h3 class="font-bold text-[#2962ff] dark:text-blue-400">{{ item.product?.name || 'Product' }}</h3>
                    <p class="text-sm text-slate-500 mt-0.5">{{ item.qty }} × {{ item.packaging_type?.name || 'units' }}</p>
                  </div>
                  <p v-if="showPricing" class="text-lg font-black text-slate-900 dark:text-white">${{ formatQuoteMoney(lineItemTotal(item)) }}</p>
                </div>
                <div v-if="showPricing" class="grid grid-cols-3 gap-3 text-center bg-slate-50 dark:bg-slate-900/50 rounded-xl p-3">
                  <div>
                    <p class="text-[10px] font-bold uppercase text-slate-400">Product</p>
                    <p class="text-sm font-mono font-semibold text-slate-800 dark:text-slate-200">${{ formatQuoteMoney(item.estimated_product_cost) }}</p>
                  </div>
                  <div>
                    <p class="text-[10px] font-bold uppercase text-slate-400">Freight</p>
                    <p class="text-sm font-mono font-semibold text-slate-800 dark:text-slate-200">${{ formatQuoteMoney(item.estimated_freight_cost) }}</p>
                  </div>
                  <div>
                    <p class="text-[10px] font-bold uppercase text-slate-400">Line total</p>
                    <p class="text-sm font-mono font-bold text-emerald-700">${{ formatQuoteMoney(lineItemTotal(item)) }}</p>
                  </div>
                </div>
                <p v-else class="text-xs text-slate-400 italic">Pricing will appear when your quote is ready.</p>
              </div>
            </div>
            <div v-if="showPricing" class="px-5 py-4 bg-slate-50 dark:bg-slate-900/50 border-t border-slate-100 dark:border-slate-700 flex justify-between items-center">
              <span class="text-sm font-bold text-slate-600 uppercase tracking-wider">Estimated total</span>
              <span class="text-2xl font-black text-[#003366] dark:text-white">${{ formatQuoteMoney(quote.total_estimated_cost) }}</span>
            </div>
          </section>

          <aside class="xl:col-span-4 space-y-5 xl:sticky xl:top-4">
            <section class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm p-5">
              <h2 class="text-[11px] font-black uppercase tracking-widest text-slate-400 mb-4">Delivery</h2>
              <dl class="space-y-3 text-sm">
                <div>
                  <dt class="text-[10px] font-bold uppercase text-slate-400">ZIP code</dt>
                  <dd class="font-semibold text-slate-800 dark:text-slate-200">{{ quote.delivery_zip || '—' }}</dd>
                </div>
                <div v-if="quote.address">
                  <dt class="text-[10px] font-bold uppercase text-slate-400">Address</dt>
                  <dd class="text-slate-700 dark:text-slate-300">{{ quote.address.address_line_1 }}, {{ quote.address.city }}</dd>
                </div>
                <div class="pt-3 border-t border-slate-100 dark:border-slate-700 space-y-2">
                  <div class="flex justify-between">
                    <span class="text-slate-600">Liftgate</span>
                    <span class="font-semibold" :class="quote.requires_liftgate ? 'text-emerald-600' : 'text-slate-400'">{{ quote.requires_liftgate ? 'Yes' : 'No' }}</span>
                  </div>
                  <div class="flex justify-between">
                    <span class="text-slate-600">Appointment</span>
                    <span class="font-semibold" :class="quote.requires_appointment ? 'text-emerald-600' : 'text-slate-400'">{{ quote.requires_appointment ? 'Yes' : 'No' }}</span>
                  </div>
                </div>
              </dl>

              <div v-if="quote.status === 'quoted'" class="mt-5 pt-5 border-t border-slate-100 dark:border-slate-700 flex flex-col gap-2">
                <button
                  type="button"
                  class="w-full py-2.5 rounded-xl bg-emerald-600 text-white text-sm font-bold hover:bg-emerald-700 transition-colors"
                  :disabled="acting"
                  @click="acceptQuote"
                >
                  Accept quote
                </button>
                <button
                  type="button"
                  class="w-full py-2.5 rounded-xl border border-red-200 text-red-600 text-sm font-semibold hover:bg-red-50 transition-colors"
                  :disabled="acting"
                  @click="rejectQuote"
                >
                  Decline
                </button>
              </div>
            </section>

            <section id="quote-chat" class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden flex flex-col scroll-mt-4 h-[min(400px,calc(100vh-14rem))]">
              <div class="px-5 py-3 border-b border-slate-100 dark:border-slate-700 shrink-0">
                <h2 class="text-[11px] font-black uppercase tracking-widest text-slate-400">Quote conversation</h2>
                <p class="text-[11px] text-slate-500 mt-0.5">RFQ #{{ quote.id }} — separate from general messages.</p>
              </div>
              <ChatPanel
                class="flex-1 min-h-0"
                link-context="customer"
                hide-start-form
                :conversation-id="quoteConversationId"
                :messages="quoteChatMessages"
                :loading="quoteChatLoading"
                :sending="quoteChatSending"
                :error="quoteChatError"
                @send="sendQuoteChatMessage"
              />
            </section>
          </aside>
        </div>
      </template>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { useRoute } from 'vue-router';
import api from '../../services/api';
import QuoteTimeline from '../../components/customer/QuoteTimeline.vue';
import ChatPanel from '../../components/chat/ChatPanel.vue';
import { useQuoteChat } from '../../composables/useQuoteChat';
import { useConfirm } from '../../composables/useConfirm';
import { useToast } from '../../composables/useToast';
import {
  quoteStatusLabel,
  quoteStatusClass,
  quoteTimelineSteps,
  formatQuoteDate,
  formatQuoteMoney,
  lineItemTotal,
} from '../../composables/useQuoteStatus';

const route = useRoute();
const toast = useToast();
const quote = ref(null);
const loading = ref(true);
const acting = ref(false);

const {
  conversationId: quoteConversationId,
  chatMessages: quoteChatMessages,
  loading: quoteChatLoading,
  sending: quoteChatSending,
  error: quoteChatError,
  loadConversation: loadQuoteChat,
  sendMessage: sendQuoteChatMessage,
  startPolling: startQuoteChatPolling,
  stopPolling: stopQuoteChatPolling,
} = useQuoteChat(route.params.id, { admin: false });

const timelineSteps = computed(() => quoteTimelineSteps(quote.value?.status));
const showPricing = computed(() => ['quoted', 'accepted'].includes(quote.value?.status));

async function fetchQuote() {
  loading.value = true;
  try {
    const { data } = await api.get(`/api/v1/quote-requests/${route.params.id}`);
    quote.value = data?.data ?? data;
  } catch (e) {
    console.error(e);
    quote.value = null;
  } finally {
    loading.value = false;
  }
}

async function acceptQuote() {
  const ok = await useConfirm().show({
    title: 'Accept quote',
    message: 'Confirm acceptance of this quote at the listed pricing?',
    confirmLabel: 'Accept',
    cancelLabel: 'Cancel',
  });
  if (!ok) return;
  acting.value = true;
  try {
    await api.post(`/api/v1/quote-requests/${quote.value.id}/accept`);
    toast.success('Quote accepted.');
    await fetchQuote();
  } catch {
    toast.error('Could not accept quote.');
  } finally {
    acting.value = false;
  }
}

async function rejectQuote() {
  const ok = await useConfirm().show({
    title: 'Decline quote',
    message: 'Are you sure you want to decline this quote?',
    confirmLabel: 'Decline',
    cancelLabel: 'Cancel',
    variant: 'danger',
  });
  if (!ok) return;
  acting.value = true;
  try {
    await api.post(`/api/v1/quote-requests/${quote.value.id}/reject`);
    toast.success('Quote declined.');
    await fetchQuote();
  } catch {
    toast.error('Could not decline quote.');
  } finally {
    acting.value = false;
  }
}

onMounted(async () => {
  await fetchQuote();
  await loadQuoteChat();
  startQuoteChatPolling();
  if (route.hash === '#quote-chat') {
    requestAnimationFrame(() => {
      document.getElementById('quote-chat')?.scrollIntoView({ behavior: 'smooth' });
    });
  }
});

onUnmounted(() => {
  stopQuoteChatPolling();
});
</script>
