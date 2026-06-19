<template>
  <NotificationDropdown
    ref="dropdownRef"
    title="Quote requests"
    aria-label="Quote notifications"
    panel-class="w-[min(100vw-2rem,20rem)]"
    @toggle="onToggle"
  >
    <template #icon>
      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
    </template>
    <template #badges>
      <span
        v-if="pendingCount > 0"
        class="absolute top-1.5 right-1.5 min-w-[18px] h-[18px] px-1 bg-red-500 text-white text-[10px] font-bold rounded-md flex items-center justify-center"
      >
        {{ pendingCount > 99 ? '99+' : pendingCount }}
      </span>
      <span
        v-if="quoteChatUnreadCount > 0"
        class="absolute bottom-1 min-w-[16px] h-4 px-1 bg-blue-600 text-white text-[9px] font-bold rounded-md flex items-center justify-center animate-pulse"
        :class="pendingCount > 0 ? 'right-5' : 'right-1.5'"
      >
        {{ quoteChatUnreadCount > 9 ? '9+' : quoteChatUnreadCount }}
      </span>
    </template>
    <template #header-meta>
      <div class="flex items-center gap-2 text-[10px] font-semibold">
        <span v-if="quoteChatUnreadCount > 0" class="text-blue-600">{{ quoteChatUnreadCount }} new msg</span>
        <span v-if="pendingCount > 0" class="text-amber-600">{{ pendingCount }} pending</span>
      </div>
    </template>
    <template #body>
      <template v-if="quoteChatPreview.length">
        <p class="px-4 pt-3 pb-1 text-[10px] font-bold uppercase tracking-wider text-blue-600">New quote messages</p>
        <ul class="py-1 border-b border-slate-100 dark:border-slate-700">
          <li
            v-for="quote in quoteChatPreview"
            :key="`chat-${quote.quote_request_id}`"
            class="px-4 py-3 hover:bg-blue-50/60 dark:hover:bg-blue-900/20 cursor-pointer"
            @click="goToQuoteChat(quote.quote_request_id)"
          >
            <div class="flex items-start justify-between gap-2">
              <div class="min-w-0 flex-1">
                <p class="text-sm font-semibold text-slate-800 dark:text-white truncate">
                  RFQ #{{ quote.quote_request_id }} · {{ quote.customer_name }}
                </p>
                <p class="text-xs text-slate-500 truncate">{{ quote.latest_message?.body || 'New message' }}</p>
              </div>
              <span class="shrink-0 text-[10px] font-bold bg-blue-600 text-white px-1.5 py-0.5 rounded-full">{{ quote.quote_chat_unread_count }}</span>
            </div>
          </li>
        </ul>
      </template>

      <p v-if="loading" class="p-4 text-sm text-slate-500">Loading…</p>
      <p v-else-if="!recent.length && !quoteChatPreview.length" class="p-4 text-sm text-slate-500">No recent activity.</p>
      <template v-else-if="recent.length">
        <p class="px-4 pt-3 pb-1 text-[10px] font-bold uppercase tracking-wider text-slate-400">Recent requests</p>
        <ul class="py-1">
          <li
            v-for="quote in recent"
            :key="quote.id"
            class="px-4 py-3 hover:bg-slate-50 dark:hover:bg-slate-700/50 border-b border-slate-100 dark:border-slate-700 last:border-0 cursor-pointer transition-colors"
            @click="goToQuote(quote.id)"
          >
            <div class="flex items-start justify-between gap-2">
              <div class="min-w-0 flex-1">
                <p class="text-sm font-semibold text-slate-800 dark:text-white truncate flex items-center gap-1.5">
                  <span
                    v-if="unreadForQuote(quote.id) > 0"
                    class="inline-flex h-2 w-2 rounded-full bg-blue-500 shrink-0"
                    title="Unread chat"
                  />
                  #{{ quote.id }} · {{ quote.customer_name || quote.requester?.email || 'Customer' }}
                </p>
                <p class="text-xs text-slate-500 mt-0.5">ZIP {{ quote.delivery_zip }} · {{ quote.status }}</p>
              </div>
              <QuoteStatusBadge :status="quote.status" />
            </div>
          </li>
        </ul>
      </template>
    </template>
    <template #footer>
      <router-link to="/admin/quotes" class="block text-center text-sm font-medium text-emerald-600 hover:text-emerald-700 py-2" @click="closeDropdown">
        View all quotes
      </router-link>
    </template>
  </NotificationDropdown>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { useRouter } from 'vue-router';
import api from '../../services/api';
import { unlockNotificationSound } from '../../composables/useNotificationSound';
import { useAdminChatNotifier } from '../../composables/useAdminChatNotifier';
import NotificationDropdown from './NotificationDropdown.vue';
import QuoteStatusBadge from '../admin/quotes/QuoteStatusBadge.vue';

const router = useRouter();
const dropdownRef = ref(null);
const loading = ref(true);
const pendingCount = ref(0);
const recent = ref([]);
let pollTimer = null;

const { quoteChatPreview, quoteChatUnreadCount } = useAdminChatNotifier();

function unreadForQuote(quoteId) {
  const hit = quoteChatPreview.value.find((q) => q.quote_request_id === quoteId);
  return hit?.quote_chat_unread_count ?? 0;
}

async function fetchNotifications() {
  try {
    const { data } = await api.get('/api/v1/admin/quote-requests/notifications');
    pendingCount.value = data.pending_count ?? 0;
    recent.value = data.recent ?? [];
  } catch {
    recent.value = [];
  } finally {
    loading.value = false;
  }
}

async function refreshAll() {
  await fetchNotifications();
}

function onToggle(isOpen) {
  unlockNotificationSound();
  if (isOpen) refreshAll();
}

function closeDropdown() {
  dropdownRef.value?.close();
}

function goToQuote(quoteId) {
  closeDropdown();
  const hasUnread = unreadForQuote(quoteId) > 0;
  router.push({
    name: 'AdminQuoteDetails',
    params: { id: quoteId },
    hash: hasUnread ? '#quote-chat' : undefined,
  });
}

function goToQuoteChat(quoteId) {
  closeDropdown();
  router.push({ name: 'AdminQuoteDetails', params: { id: quoteId }, hash: '#quote-chat' });
}

onMounted(() => {
  fetchNotifications();
  pollTimer = window.setInterval(fetchNotifications, 30000);
});

onUnmounted(() => {
  if (pollTimer) clearInterval(pollTimer);
});
</script>
