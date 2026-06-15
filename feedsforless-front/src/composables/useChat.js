import { ref, computed } from 'vue';
import api from '../services/api';
import { useAuthStore } from '../stores/auth';
import { getChatSession, saveChatSession } from './useChatSession';
import { playNotificationSound } from './useNotificationSound';

function chatHeaders() {
  const session = getChatSession();
  const headers = {};
  if (session) {
    headers['X-Conversation-Id'] = String(session.conversation_id);
    headers['X-Conversation-Token'] = session.access_token;
  }
  return headers;
}

export function useChat() {
  const authStore = useAuthStore();
  const conversation = ref(null);
  const messages = ref([]);
  const loading = ref(false);
  const sending = ref(false);
  const error = ref('');
  let pollTimer = null;
  let chatIsOpen = false;
  let soundInitialized = false;
  let lastNotifiedAdminMessageId = 0;

  const conversationId = computed(() => conversation.value?.id ?? null);
  const unreadCount = computed(() => conversation.value?.unread_count ?? 0);

  function applyGuestSession(payload) {
    if (payload?.guest_session) {
      saveChatSession(payload.guest_session);
    }
  }

  function acknowledgeAdminMessages(list) {
    for (const msg of list) {
      if (msg?.is_from_staff && msg.id > lastNotifiedAdminMessageId) {
        lastNotifiedAdminMessageId = msg.id;
      }
    }
  }

  function maybeNotifyNewAdminMessage(latestMessage, { playSound = false } = {}) {
    if (!latestMessage?.is_from_staff) return;

    if (!soundInitialized) {
      lastNotifiedAdminMessageId = Math.max(lastNotifiedAdminMessageId, latestMessage.id);
      soundInitialized = true;
      return;
    }

    if (latestMessage.id > lastNotifiedAdminMessageId) {
      lastNotifiedAdminMessageId = latestMessage.id;
      if (playSound && !chatIsOpen) {
        playNotificationSound();
      }
    }
  }

  async function peekCurrent({ playSound = false } = {}) {
    try {
      const { data } = await api.get('/api/v1/conversations/current', { headers: chatHeaders() });
      if (data?.data) {
        conversation.value = data.data;
        applyGuestSession(data);
        maybeNotifyNewAdminMessage(data.data.latest_message, { playSound });
      } else {
        conversation.value = null;
      }
    } catch {
      /* silent for background polls */
    }
  }

  async function loadCurrent() {
    loading.value = true;
    error.value = '';
    try {
      const { data } = await api.get('/api/v1/conversations/current', { headers: chatHeaders() });
      if (data?.data) {
        conversation.value = data.data;
        applyGuestSession(data);
        maybeNotifyNewAdminMessage(data.data.latest_message, { playSound: false });
        if (data.data.id) {
          await loadMessages(data.data.id);
        }
      } else {
        conversation.value = null;
        messages.value = [];
      }
    } catch (e) {
      error.value = e.response?.data?.message || 'Could not load conversation.';
    } finally {
      loading.value = false;
    }
  }

  async function loadMessages(id) {
    const cid = id ?? conversationId.value;
    if (!cid) return;
    try {
      const { data } = await api.get(`/api/v1/conversations/${cid}`, { headers: chatHeaders() });
      conversation.value = data.data;
      messages.value = data.data?.messages ?? [];
      acknowledgeAdminMessages(messages.value);
    } catch (e) {
      error.value = e.response?.data?.message || 'Could not load messages.';
    }
  }

  async function startConversation({ email, name, message }) {
    sending.value = true;
    error.value = '';
    try {
      const session = getChatSession();
      const payload = {
        email: email || authStore.user?.email,
        name: name || undefined,
        message: message || undefined,
        conversation_id: session?.conversation_id,
        access_token: session?.access_token,
      };
      const { data } = await api.post('/api/v1/conversations', payload, { headers: chatHeaders() });
      conversation.value = data.data;
      applyGuestSession(data);
      if (data.data?.id) {
        await loadMessages(data.data.id);
      }
      return data.data;
    } catch (e) {
      error.value = e.response?.data?.message || 'Could not start conversation.';
      throw e;
    } finally {
      sending.value = false;
    }
  }

  async function sendMessage(body) {
    if (!conversationId.value || !body?.trim()) return;
    sending.value = true;
    error.value = '';
    try {
      const session = getChatSession();
      const { data } = await api.post(
        `/api/v1/conversations/${conversationId.value}/messages`,
        {
          body: body.trim(),
          access_token: session?.access_token,
        },
        { headers: chatHeaders() }
      );
      messages.value.push(data.data);
      await loadMessages(conversationId.value);
    } catch (e) {
      error.value = e.response?.data?.message || 'Could not send message.';
      throw e;
    } finally {
      sending.value = false;
    }
  }

  function canPoll() {
    return !!authStore.token || !!getChatSession();
  }

  function startPolling(intervalMs = 4000) {
    stopPolling();
    if (!canPoll()) return;

    pollTimer = setInterval(async () => {
      if (!canPoll()) return;
      if (chatIsOpen && conversationId.value) {
        await loadMessages(conversationId.value);
      } else {
        await peekCurrent({ playSound: true });
      }
    }, intervalMs);
  }

  function stopPolling() {
    if (pollTimer) {
      clearInterval(pollTimer);
      pollTimer = null;
    }
  }

  function setChatOpen(isOpen) {
    chatIsOpen = isOpen;
  }

  return {
    conversation,
    messages,
    loading,
    sending,
    error,
    conversationId,
    unreadCount,
    canPoll,
    loadCurrent,
    peekCurrent,
    loadMessages,
    startConversation,
    sendMessage,
    startPolling,
    stopPolling,
    setChatOpen,
  };
}
