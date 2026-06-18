import { ref, computed } from 'vue';
import api from '../services/api';
import { playNotificationSound } from './useNotificationSound';

const unreadCount = ref(0);
const generalUnreadCount = ref(0);
const quoteChatUnreadCount = ref(0);
const recentPreview = ref([]);
const quoteChatPreview = ref([]);
let pollTimer = null;
let initialized = false;
let subscriberCount = 0;
let lastNotifiedCustomerMessageId = 0;
let lastNotifiedQuoteMessageId = 0;

function extractList(listRes) {
  const raw = listRes.data?.data ?? listRes.data;
  return Array.isArray(raw) ? raw : (raw?.data ?? []);
}

function findNewCustomerMessageId(list) {
  let newest = null;
  for (const conv of list) {
    const msg = conv.latest_message;
    if (!msg || msg.is_from_staff) continue;
    if (msg.id > lastNotifiedCustomerMessageId && (newest === null || msg.id > newest)) {
      newest = msg.id;
    }
  }
  return newest;
}

function findNewQuoteMessageId(quotes) {
  let newest = null;
  for (const quote of quotes) {
    const msg = quote.latest_message;
    if (!msg?.id) continue;
    if (msg.id > lastNotifiedQuoteMessageId && (newest === null || msg.id > newest)) {
      newest = msg.id;
    }
  }
  return newest;
}

function seedBaselineFromList(list) {
  for (const conv of list) {
    const msg = conv.latest_message;
    if (msg?.id > lastNotifiedCustomerMessageId) {
      lastNotifiedCustomerMessageId = msg.id;
    }
  }
}

function seedQuoteBaseline(quotes) {
  for (const quote of quotes) {
    const msg = quote.latest_message;
    if (msg?.id > lastNotifiedQuoteMessageId) {
      lastNotifiedQuoteMessageId = msg.id;
    }
  }
}

async function fetchSnapshot() {
  const [countRes, listRes, quoteRes] = await Promise.all([
    api.get('/api/v1/admin/conversations/unread-count'),
    api.get('/api/v1/admin/conversations', { params: { page: 1 } }),
    api.get('/api/v1/admin/quote-requests/chat-notifications'),
  ]);

  generalUnreadCount.value = countRes.data?.general_unread_count ?? 0;
  quoteChatUnreadCount.value = countRes.data?.quote_chat_unread_count ?? quoteRes.data?.unread_count ?? 0;
  unreadCount.value = countRes.data?.unread_count ?? (generalUnreadCount.value + quoteChatUnreadCount.value);

  const list = extractList(listRes);
  recentPreview.value = list.filter((conv) => (conv.unread_count ?? 0) > 0).slice(0, 5);
  quoteChatPreview.value = quoteRes.data?.quotes ?? [];

  return { list, quotes: quoteChatPreview.value };
}

async function poll({ playSound = false } = {}) {
  try {
    const { list, quotes } = await fetchSnapshot();
    const newCustomerMessageId = findNewCustomerMessageId(list);
    const newQuoteMessageId = findNewQuoteMessageId(quotes);

    if (!initialized) {
      seedBaselineFromList(list);
      seedQuoteBaseline(quotes);
    } else if (playSound) {
      if (newCustomerMessageId !== null) {
        lastNotifiedCustomerMessageId = newCustomerMessageId;
        playNotificationSound();
      } else if (newQuoteMessageId !== null) {
        lastNotifiedQuoteMessageId = newQuoteMessageId;
        playNotificationSound();
      }
    }

    initialized = true;
  } catch {
    /* silent */
  }
}

async function refresh() {
  try {
    await fetchSnapshot();
  } catch {
    /* silent */
  }
}

export function useAdminChatNotifier() {
  function startPolling(intervalMs = 4000) {
    subscriberCount += 1;
    if (pollTimer) return;

    poll({ playSound: false });
    pollTimer = window.setInterval(() => poll({ playSound: true }), intervalMs);
  }

  function stopPolling() {
    subscriberCount = Math.max(0, subscriberCount - 1);
    if (subscriberCount === 0 && pollTimer) {
      clearInterval(pollTimer);
      pollTimer = null;
    }
  }

  function acknowledgeMessageIds(ids) {
    for (const id of ids) {
      if (typeof id === 'number' && id > lastNotifiedCustomerMessageId) {
        lastNotifiedCustomerMessageId = id;
      }
    }
  }

  function acknowledgeQuoteMessageIds(ids) {
    for (const id of ids) {
      if (typeof id === 'number' && id > lastNotifiedQuoteMessageId) {
        lastNotifiedQuoteMessageId = id;
      }
    }
  }

  const hasNotifications = computed(() => unreadCount.value > 0);

  return {
    unreadCount,
    generalUnreadCount,
    quoteChatUnreadCount,
    recentPreview,
    quoteChatPreview,
    hasNotifications,
    refresh,
    acknowledgeMessageIds,
    acknowledgeQuoteMessageIds,
    startPolling,
    stopPolling,
  };
}
