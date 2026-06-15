<template>
  <div v-if="!isAuthRoute" class="fixed z-50 flex flex-col items-end gap-2 bottom-[max(1rem,env(safe-area-inset-bottom))] right-[max(1rem,env(safe-area-inset-right))] left-auto">
    <Transition name="chat-pop">
      <div
        v-show="open"
        class="fixed sm:relative inset-x-0 sm:inset-x-auto bottom-0 sm:bottom-auto sm:right-0 w-full sm:w-[min(100vw-2rem,400px)] h-[min(100dvh,100%)] sm:h-[min(78dvh,560px)] shadow-2xl sm:rounded-2xl border border-slate-200 dark:border-slate-700 overflow-hidden flex flex-col bg-white dark:bg-slate-800"
      >
        <div class="flex items-center justify-between px-4 py-3.5 bg-[#003366] text-white shrink-0 safe-top">
          <div class="min-w-0 pr-2">
            <p class="text-sm font-bold truncate">Chat with FeedsForLess</p>
            <p class="text-[10px] text-blue-200">We reply during business hours</p>
          </div>
          <button type="button" class="p-2 hover:bg-white/10 rounded-xl shrink-0 touch-manipulation" aria-label="Close chat" @click="closeChat">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
          </button>
        </div>
        <ChatPanel
          class="flex-1 min-h-0"
          :conversation-id="conversationId"
          :messages="messages"
          :loading="loading"
          :sending="sending"
          :starting="sending"
          :error="error"
          @start="handleStart"
          @send="handleSend"
        />
      </div>
    </Transition>

    <button
      v-show="!open"
      type="button"
      class="relative w-14 h-14 rounded-full bg-[#2962ff] text-white shadow-lg shadow-blue-500/30 hover:bg-blue-700 transition-transform active:scale-95 flex items-center justify-center touch-manipulation"
      aria-label="Open chat"
      @click="openChat"
    >
      <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
      </svg>
      <span
        v-if="unreadCount > 0"
        class="absolute -top-0.5 -right-0.5 min-w-[1.25rem] h-5 px-1 flex items-center justify-center rounded-full bg-red-500 text-[10px] font-bold ring-2 ring-white dark:ring-slate-900 animate-pulse"
      >
        {{ unreadCount > 9 ? '9+' : unreadCount }}
      </span>
    </button>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted, onUnmounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useAuthStore } from '../../stores/auth';
import { saveChatSession } from '../../composables/useChatSession';
import { unlockNotificationSound, installNotificationSoundUnlock } from '../../composables/useNotificationSound';
import ChatPanel from './ChatPanel.vue';
import { useChat } from '../../composables/useChat';

const route = useRoute();
const router = useRouter();
const authStore = useAuthStore();
const open = ref(false);

const isAuthRoute = computed(() => ['Login', 'Register'].includes(route.name));

const {
  conversationId,
  messages,
  loading,
  sending,
  error,
  unreadCount,
  canPoll,
  loadCurrent,
  peekCurrent,
  startConversation,
  sendMessage,
  startPolling,
  stopPolling,
  setChatOpen,
} = useChat();

function syncBodyScroll() {
  document.body.classList.toggle('overflow-hidden', open.value && window.innerWidth < 640);
}

async function openChat() {
  unlockNotificationSound();
  open.value = true;
  setChatOpen(true);
  syncBodyScroll();
  await loadCurrent();
}

function closeChat() {
  open.value = false;
  setChatOpen(false);
  syncBodyScroll();
}

async function handleStart(form) {
  await startConversation(form);
  if (canPoll()) startPolling();
}

async function handleSend(body) {
  await sendMessage(body);
}

async function bootstrapChat() {
  if (!canPoll()) return;
  await peekCurrent({ playSound: false });
  startPolling(4000);
}

function applyGuestDeepLink() {
  if (authStore.token) return;

  const convId = route.query.conversation;
  const token = route.query.chat_token;
  if (convId && token) {
    saveChatSession({
      conversation_id: Number(convId),
      access_token: String(token),
    });
  }
}

function clearChatQueryParams() {
  if (!route.query.openChat) return;
  const nextQuery = { ...route.query };
  delete nextQuery.openChat;
  delete nextQuery.conversation;
  delete nextQuery.chat_token;
  router.replace({ path: route.path, query: nextQuery });
}

async function handleOpenChatDeepLink() {
  if (route.query.openChat !== '1') return;
  applyGuestDeepLink();
  await openChat();
  clearChatQueryParams();
}

watch(open, (isOpen) => {
  setChatOpen(isOpen);
  syncBodyScroll();
});

watch(() => authStore.token, () => {
  bootstrapChat();
});

watch(
  () => route.query.openChat,
  (val) => {
    if (val === '1') handleOpenChatDeepLink();
  }
);

const removeSoundUnlock = installNotificationSoundUnlock();

onMounted(async () => {
  await bootstrapChat();
  if (route.query.openChat === '1') {
    await handleOpenChatDeepLink();
  }
});

onUnmounted(() => {
  removeSoundUnlock?.();
  stopPolling();
  setChatOpen(false);
  document.body.classList.remove('overflow-hidden');
});
</script>

<style scoped>
.chat-pop-enter-active,
.chat-pop-leave-active {
  transition: opacity 0.2s ease, transform 0.25s cubic-bezier(0.32, 0.72, 0, 1);
}
.chat-pop-enter-from,
.chat-pop-leave-to {
  opacity: 0;
  transform: translateY(100%);
}
@media (min-width: 640px) {
  .chat-pop-enter-from,
  .chat-pop-leave-to {
    transform: translateY(12px) scale(0.96);
  }
}
</style>
