<template>
  <div id="quote-chat" class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden flex flex-col scroll-mt-4 h-[min(480px,calc(100vh-12rem))]">
    <div class="px-5 py-3 border-b border-slate-100 shrink-0">
      <h3 class="text-[11px] font-black text-slate-900 uppercase tracking-widest">Quote conversation</h3>
      <p class="text-[11px] text-slate-500 mt-0.5">RFQ #{{ quoteId }} only — not general chat.</p>
    </div>
    <ChatPanel
      class="flex-1 min-h-0"
      variant="admin"
      link-context="admin"
      hide-start-form
      :conversation-id="conversationId"
      :messages="messages"
      :loading="loading"
      :sending="sending"
      :error="error"
      @send="$emit('send', $event)"
    />
  </div>
</template>

<script setup>
import ChatPanel from '../../chat/ChatPanel.vue';

defineProps({
  quoteId: { type: [String, Number], required: true },
  conversationId: { type: [String, Number], default: null },
  messages: { type: Array, default: () => [] },
  loading: { type: Boolean, default: false },
  sending: { type: Boolean, default: false },
  error: { type: String, default: '' },
});

defineEmits(['send']);
</script>
