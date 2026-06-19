<template>
  <NotificationDropdown
    ref="dropdownRef"
    title="Messages"
    aria-label="Chat notifications"
    @toggle="onToggle"
  >
    <template #icon>
      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
      </svg>
    </template>
    <template #badges>
      <span
        v-if="unreadCount > 0"
        class="absolute top-1.5 right-1.5 min-w-[18px] h-[18px] px-1 bg-blue-600 text-white text-[10px] font-bold rounded-md flex items-center justify-center animate-pulse"
      >
        {{ unreadCount > 99 ? '99+' : unreadCount }}
      </span>
    </template>
    <template #header-meta>
      <span v-if="unreadCount > 0" class="text-xs font-medium text-blue-600">{{ unreadCount }} unread</span>
    </template>
    <template #body>
      <template v-if="quoteChatPreview.length">
        <p class="px-4 pt-3 pb-1 text-[10px] font-bold uppercase tracking-wider text-emerald-600">Quote chats</p>
        <ul class="py-1 border-b border-slate-100 dark:border-slate-700">
          <li
            v-for="quote in quoteChatPreview"
            :key="`quote-${quote.quote_request_id}`"
            class="px-4 py-3 hover:bg-slate-50 dark:hover:bg-slate-700/50 cursor-pointer"
            @click="goToQuoteChat(quote.quote_request_id)"
          >
            <div class="flex items-start justify-between gap-2">
              <div class="min-w-0 flex-1">
                <p class="text-sm font-semibold text-slate-800 dark:text-white truncate">
                  RFQ #{{ quote.quote_request_id }} · {{ quote.customer_name }}
                </p>
                <p class="text-xs text-slate-500 truncate">{{ quote.latest_message?.body || 'New message' }}</p>
              </div>
              <span v-if="quote.quote_chat_unread_count > 0" class="shrink-0 text-[10px] font-bold bg-emerald-600 text-white px-1.5 py-0.5 rounded-full">{{ quote.quote_chat_unread_count }}</span>
            </div>
          </li>
        </ul>
      </template>

      <template v-if="recentPreview.length">
        <p class="px-4 pt-3 pb-1 text-[10px] font-bold uppercase tracking-wider text-slate-400">General inbox</p>
        <ul class="py-1">
          <li
            v-for="conv in recentPreview"
            :key="conv.id"
            class="px-4 py-3 hover:bg-slate-50 dark:hover:bg-slate-700/50 border-b border-slate-100 dark:border-slate-700 last:border-0 cursor-pointer"
            @click="goToConversation(conv.id)"
          >
            <div class="flex items-start justify-between gap-2">
              <div class="min-w-0 flex-1">
                <p class="text-sm font-semibold text-slate-800 dark:text-white truncate">{{ conv.customer_name }}</p>
                <p class="text-xs text-slate-500 truncate">{{ conv.latest_message?.body || '—' }}</p>
              </div>
              <span v-if="conv.unread_count > 0" class="shrink-0 text-[10px] font-bold bg-blue-600 text-white px-1.5 py-0.5 rounded-full">{{ conv.unread_count }}</span>
            </div>
          </li>
        </ul>
      </template>

      <p v-if="!recentPreview.length && !quoteChatPreview.length" class="p-4 text-sm text-slate-500">No new messages.</p>
    </template>
    <template #footer>
      <div class="flex gap-1">
        <router-link to="/admin/quotes" class="flex-1 text-center text-sm font-medium text-emerald-600 hover:text-emerald-700 py-2" @click="closeDropdown">
          Quotes
        </router-link>
        <router-link to="/admin/messages" class="flex-1 text-center text-sm font-medium text-blue-600 hover:text-blue-700 py-2" @click="closeDropdown">
          Inbox
        </router-link>
      </div>
    </template>
  </NotificationDropdown>
</template>

<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { unlockNotificationSound } from '../../composables/useNotificationSound';
import { useAdminChatNotifier } from '../../composables/useAdminChatNotifier';
import NotificationDropdown from './NotificationDropdown.vue';

const router = useRouter();
const dropdownRef = ref(null);

const { unreadCount, recentPreview, quoteChatPreview, refresh } = useAdminChatNotifier();

function onToggle(isOpen) {
  unlockNotificationSound();
  if (isOpen) refresh();
}

function closeDropdown() {
  dropdownRef.value?.close();
}

function goToConversation(id) {
  closeDropdown();
  router.push({ path: '/admin/messages', query: { conversation: id } });
}

function goToQuoteChat(quoteId) {
  closeDropdown();
  router.push({ name: 'AdminQuoteDetails', params: { id: quoteId }, hash: '#quote-chat' });
}
</script>
