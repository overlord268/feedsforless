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
      <QuoteSummaryBar
        :quote="quote"
        :contact-name="contactName"
        :total-cost="computedTotalCost"
      />

      <div class="grid grid-cols-1 xl:grid-cols-12 gap-5 items-start">
        <div class="xl:col-span-8 space-y-5">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <QuoteProfileCard
              :quote="quote"
              :company-name="companyName"
              :contact-name="contactName"
              :email="email"
              :phone="phone"
              :tax-id="taxId"
            />
            <QuoteLogisticsCard :quote="quote" />
          </div>

          <QuotePricingEditor
            :items="quote.items"
            :price-form="priceForm"
            :price-row-key="priceRowKey"
            :line-total="getLineTotal"
            :saving="savingPrices"
            @save="onSavePrices"
          />
        </div>

        <aside class="xl:col-span-4 space-y-5 xl:sticky xl:top-4">
          <QuoteStatusPanel
            v-model:status="detailForm.status"
            v-model:admin-note="detailForm.admin_note"
            v-model:customer-message="detailForm.customer_message"
            :saving-status="savingStatus"
            :saving-note="savingNote"
            :note-saved="noteSaved"
            :show-customer-message="showCustomerMessage"
            @save-status="saveStatus"
          />

          <QuoteChatSection
            :quote-id="quote.id"
            :conversation-id="quoteConversationId"
            :messages="quoteChatMessages"
            :loading="quoteChatLoading"
            :sending="quoteChatSending"
            :error="quoteChatError"
            @send="sendQuoteChatMessage"
          />
        </aside>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, watch } from 'vue';
import { useRoute } from 'vue-router';
import { useToast } from '../../composables/useToast';
import { useQuoteChat } from '../../composables/useQuoteChat';
import { useAdminChatNotifier } from '../../composables/useAdminChatNotifier';
import { fetchAdminQuote } from '../../services/adminQuotesApi';
import { useQuoteRequester } from '../../composables/quotes/useQuoteRequester';
import { useQuotePricing } from '../../composables/quotes/useQuotePricing';
import { useQuoteStatusForm } from '../../composables/quotes/useQuoteStatusForm';
import { useDebouncedQuoteNote } from '../../composables/quotes/useDebouncedQuoteNote';
import QuoteSummaryBar from '../../components/admin/quotes/QuoteSummaryBar.vue';
import QuoteProfileCard from '../../components/admin/quotes/QuoteProfileCard.vue';
import QuoteLogisticsCard from '../../components/admin/quotes/QuoteLogisticsCard.vue';
import QuotePricingEditor from '../../components/admin/quotes/QuotePricingEditor.vue';
import QuoteStatusPanel from '../../components/admin/quotes/QuoteStatusPanel.vue';
import QuoteChatSection from '../../components/admin/quotes/QuoteChatSection.vue';

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

const { companyName, contactName, email, phone, taxId } = useQuoteRequester(quote);

const {
  priceForm,
  priceRowKey,
  savingPrices,
  computedTotalCost,
  getLineTotal,
  savePrices,
} = useQuotePricing(quote);

const {
  detailForm,
  savingStatus,
  showCustomerMessage,
  syncFromQuote,
  saveStatus,
} = useQuoteStatusForm(quoteId, quote);

const { savingNote, noteSaved, resetNoteWatch } = useDebouncedQuoteNote(quoteId, quote, detailForm);

async function fetchQuote() {
  if (!quoteId) return;
  loading.value = true;
  try {
    quote.value = await fetchAdminQuote(quoteId);
    syncFromQuote();
    resetNoteWatch();
  } catch (e) {
    console.error(e);
    toast.error('Could not load quote details.');
  } finally {
    loading.value = false;
  }
}

async function onSavePrices() {
  const updated = await savePrices();
  if (updated) {
    quote.value = updated;
    detailForm.status = 'quoted';
    syncFromQuote();
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
