import { ref, computed, onUnmounted } from 'vue';
import api from '../services/api';

export function useQuoteChat(quoteId, { admin = false } = {}) {
  const conversation = ref(null);
  const messages = ref([]);
  const loading = ref(false);
  const sending = ref(false);
  const error = ref('');
  let pollTimer = null;

  const basePath = admin
    ? `/api/v1/admin/quote-requests/${quoteId}/conversation`
    : `/api/v1/quote-requests/${quoteId}/conversation`;

  const conversationId = computed(() => conversation.value?.id ?? null);

  const chatMessages = computed(() =>
    (messages.value || []).filter((msg) => msg.message_type !== 'quote_reference')
  );

  async function loadConversation({ markRead = false } = {}) {
    if (!quoteId) return;
    loading.value = true;
    error.value = '';
    try {
      const params = admin && markRead ? { mark_read: 1 } : {};
      const { data } = await api.get(basePath, { params });
      conversation.value = data.data;
      messages.value = data.data?.messages ?? [];
    } catch (e) {
      error.value = e.response?.data?.message || 'Could not load quote conversation.';
      conversation.value = null;
      messages.value = [];
    } finally {
      loading.value = false;
    }
  }

  async function markConversationRead() {
    if (!admin || !quoteId) return;
    await loadConversation({ markRead: true });
  }

  async function sendMessage(body) {
    if (!body?.trim()) return;
    sending.value = true;
    error.value = '';
    try {
      const { data } = await api.post(`${basePath}/messages`, { body: body.trim() });
      messages.value.push(data.data);
      await loadConversation({ markRead: true });
    } catch (e) {
      error.value = e.response?.data?.message || 'Could not send message.';
      throw e;
    } finally {
      sending.value = false;
    }
  }

  function startPolling(intervalMs = 5000) {
    stopPolling();
    pollTimer = setInterval(() => {
      if (!loading.value && !sending.value) {
        loadConversation({ markRead: false });
      }
    }, intervalMs);
  }

  function stopPolling() {
    if (pollTimer) {
      clearInterval(pollTimer);
      pollTimer = null;
    }
  }

  onUnmounted(stopPolling);

  return {
    conversation,
    messages,
    chatMessages,
    loading,
    sending,
    error,
    conversationId,
    loadConversation,
    markConversationRead,
    sendMessage,
    startPolling,
    stopPolling,
  };
}
