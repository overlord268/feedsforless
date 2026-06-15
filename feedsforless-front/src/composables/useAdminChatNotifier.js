import { ref } from 'vue';
import api from '../services/api';
import { playNotificationSound } from './useNotificationSound';

const unreadCount = ref(0);
const recentPreview = ref([]);
let pollTimer = null;
let initialized = false;
let subscriberCount = 0;
/** Highest customer/guest message id we have already notified about. */
let lastNotifiedCustomerMessageId = 0;

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

function seedBaselineFromList(list) {
  for (const conv of list) {
    const msg = conv.latest_message;
    if (msg?.id > lastNotifiedCustomerMessageId) {
      lastNotifiedCustomerMessageId = msg.id;
    }
  }
}

async function fetchSnapshot() {
  const [countRes, listRes] = await Promise.all([
    api.get('/api/v1/admin/conversations/unread-count'),
    api.get('/api/v1/admin/conversations', { params: { page: 1 } }),
  ]);

  const count = countRes.data?.unread_count ?? 0;
  const list = extractList(listRes);
  recentPreview.value = list.slice(0, 5);
  unreadCount.value = count;

  return list;
}

async function poll({ playSound = false } = {}) {
  try {
    const list = await fetchSnapshot();
    const newCustomerMessageId = findNewCustomerMessageId(list);

    if (!initialized) {
      seedBaselineFromList(list);
    } else if (playSound && newCustomerMessageId !== null) {
      lastNotifiedCustomerMessageId = newCustomerMessageId;
      playNotificationSound();
    }

    initialized = true;
  } catch {
    /* silent */
  }
}

/** Refresh badge/list only — does not affect sound baseline. */
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

  /** Mark specific message ids as already notified (admin opened the thread). */
  function acknowledgeMessageIds(ids) {
    for (const id of ids) {
      if (typeof id === 'number' && id > lastNotifiedCustomerMessageId) {
        lastNotifiedCustomerMessageId = id;
      }
    }
  }

  return {
    unreadCount,
    recentPreview,
    refresh,
    acknowledgeMessageIds,
    startPolling,
    stopPolling,
  };
}
