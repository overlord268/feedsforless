import { ref, onMounted, onUnmounted, onActivated } from 'vue';
import { fetchAdminQuotes } from '../../services/adminQuotesApi';
import { usePageVisible } from '../usePageVisible';

export function useAdminQuotesList({ autoFetch = true } = {}) {
  const quotes = ref([]);
  const loading = ref(true);
  let pollTimer = null;
  let pollIntervalMs = 30000;

  async function fetchQuotes({ silent = false } = {}) {
    if (!silent) loading.value = true;
    try {
      quotes.value = await fetchAdminQuotes();
    } catch (e) {
      console.error(e);
      if (!silent) quotes.value = [];
    } finally {
      if (!silent) loading.value = false;
    }
  }

  function startPolling(intervalMs = 30000) {
    pollIntervalMs = intervalMs;
    stopPolling();
    pollTimer = window.setInterval(() => {
      if (!document.hidden && !loading.value) {
        fetchQuotes({ silent: true });
      }
    }, pollIntervalMs);
  }

  function stopPolling() {
    if (pollTimer) {
      clearInterval(pollTimer);
      pollTimer = null;
    }
  }

  usePageVisible(
    () => {
      fetchQuotes({ silent: true });
      if (pollIntervalMs) startPolling(pollIntervalMs);
    },
    stopPolling,
  );

  if (autoFetch) {
    onMounted(() => fetchQuotes());
  }
  onUnmounted(stopPolling);

  return {
    quotes,
    loading,
    fetchQuotes,
    startPolling,
    stopPolling,
  };
}

export function useAdminQuotesListWithPolling(options = {}) {
  const list = useAdminQuotesList(options);

  onMounted(() => list.startPolling());
  onActivated(() => {
    if (!document.hidden) list.fetchQuotes({ silent: true });
  });

  return list;
}
