<template>
  <div class="flex flex-col flex-1 min-h-0 w-full max-w-3xl mx-auto">
    <header class="mb-4 sm:mb-6 shrink-0">
      <h1 class="text-2xl sm:text-3xl font-bold text-slate-900 dark:text-white tracking-tight">Messages</h1>
      <p class="text-slate-500 mt-1 text-sm">Your conversation with our team is saved here.</p>
    </header>
    <div class="flex-1 min-h-[min(70dvh,560px)] rounded-2xl border border-slate-200 dark:border-slate-700 overflow-hidden shadow-sm bg-white dark:bg-slate-800 flex flex-col">
      <ChatPanel
        class="flex-1 min-h-0"
        link-context="customer"
        :conversation-id="conversationId"
        :messages="messages"
        :loading="loading"
        :sending="sending"
        :starting="sending"
        :error="error"
        :hide-start-form="!!conversationId"
        @start="handleStart"
        @send="handleSend"
      />
    </div>
  </div>
</template>

<script setup>
import { onMounted, onUnmounted } from 'vue';
import ChatPanel from '../../components/chat/ChatPanel.vue';
import { useChat } from '../../composables/useChat';

const {
  conversationId,
  messages,
  loading,
  sending,
  error,
  loadCurrent,
  startConversation,
  sendMessage,
  startPolling,
  stopPolling,
  setChatOpen,
} = useChat();

async function handleStart(form) {
  await startConversation(form);
}

async function handleSend(body) {
  await sendMessage(body);
}

onMounted(async () => {
  setChatOpen(true);
  await loadCurrent();
  startPolling();
});

onUnmounted(() => {
  setChatOpen(false);
  stopPolling();
});
</script>
