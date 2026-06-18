<template>
  <div class="flex flex-col flex-1 min-h-0 w-full">
    <header class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4 shrink-0">
      <div>
        <h1 class="text-xl sm:text-2xl font-bold text-slate-900 dark:text-white tracking-tight">Customer messages</h1>
        <p class="text-sm text-slate-500 mt-0.5">Chat with prospects and clients — history is saved.</p>
      </div>
      <div v-if="totalUnread > 0" class="inline-flex items-center gap-2 self-start px-3 py-1.5 rounded-full bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 text-xs font-semibold">
        <span class="w-2 h-2 rounded-full bg-blue-500 animate-pulse"></span>
        {{ totalUnread }} unread
      </div>
    </header>

    <div class="flex-1 flex min-h-0 rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 shadow-sm overflow-hidden">
      <!-- Conversation list -->
      <aside
        class="flex flex-col min-h-0 border-r border-slate-200 dark:border-slate-700 bg-slate-50/80 dark:bg-slate-900/40 transition-all duration-200"
        :class="[
          mobileShowThread ? 'hidden lg:flex' : 'flex',
          'w-full lg:w-[320px] xl:w-[360px] shrink-0'
        ]"
      >
        <div class="px-4 py-3 border-b border-slate-200 dark:border-slate-700 shrink-0">
          <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Inbox</p>
          <p class="text-sm text-slate-600 dark:text-slate-300 mt-0.5">{{ conversations.length }} conversation{{ conversations.length === 1 ? '' : 's' }}</p>
        </div>
        <div v-if="listLoading" class="p-6 text-sm text-slate-500 text-center">Loading…</div>
        <ul v-else class="flex-1 overflow-y-auto overscroll-contain">
          <li
            v-for="conv in conversations"
            :key="conv.id"
            class="relative border-b border-slate-100 dark:border-slate-700/80 last:border-0"
          >
            <button
              type="button"
              class="w-full text-left px-4 py-3.5 hover:bg-white dark:hover:bg-slate-800 transition-colors"
              :class="selectedId === conv.id ? 'bg-white dark:bg-slate-800 shadow-sm ring-1 ring-inset ring-blue-200 dark:ring-blue-800' : ''"
              @click="openThread(conv.id)"
            >
              <div class="flex items-start gap-3">
                <div
                  class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0 text-sm font-bold"
                  :class="conv.is_unregistered_guest ? 'bg-amber-100 text-amber-800' : 'bg-emerald-100 text-emerald-800'"
                >
                  {{ initials(conv.customer_name) }}
                </div>
                <div class="min-w-0 flex-1">
                  <div class="flex items-center justify-between gap-2">
                    <p class="font-semibold text-sm text-slate-900 dark:text-white truncate">{{ conv.customer_name }}</p>
                    <span v-if="conv.unread_count > 0" class="shrink-0 min-w-[1.25rem] h-5 px-1.5 flex items-center justify-center rounded-full bg-blue-600 text-white text-[10px] font-bold">{{ conv.unread_count }}</span>
                  </div>
                  <p class="text-xs text-slate-500 truncate mt-0.5">{{ conv.customer_email }}</p>
                  <p v-if="conv.latest_message" class="text-xs text-slate-400 mt-1 line-clamp-2 leading-relaxed">{{ conv.latest_message.body }}</p>
                  <p v-if="conv.is_unregistered_guest" class="text-[10px] font-medium text-amber-700 dark:text-amber-400 mt-1">Guest · email notify</p>
                </div>
              </div>
            </button>
          </li>
          <li v-if="!conversations.length" class="p-10 text-center text-sm text-slate-500">No conversations yet.</li>
        </ul>
      </aside>

      <!-- Thread -->
      <main
        class="flex-1 flex flex-col min-h-0 min-w-0 bg-white dark:bg-slate-800"
        :class="mobileShowThread ? 'flex' : 'hidden lg:flex'"
      >
        <template v-if="selectedId">
          <div class="flex items-start justify-between gap-3 px-4 sm:px-5 py-3 border-b border-slate-200 dark:border-slate-700 shrink-0 bg-white dark:bg-slate-800">
            <div class="min-w-0 flex-1">
              <button type="button" class="lg:hidden text-sm text-blue-600 font-medium mb-2 flex items-center gap-1" @click="mobileShowThread = false">
                ← Inbox
              </button>
              <p class="font-bold text-slate-900 dark:text-white truncate text-base">{{ activeConversation?.customer_name }}</p>
              <p class="text-xs text-slate-500 truncate">{{ activeConversation?.customer_email }}</p>
              <p v-if="activeConversation?.is_unregistered_guest" class="text-[11px] text-amber-700 dark:text-amber-400 mt-1 font-medium">
                Guest — replies are emailed automatically
              </p>
            </div>
            <div class="flex items-center gap-2 shrink-0">
              <select
                v-model="statusFilter"
                class="text-xs border border-slate-200 dark:border-slate-600 rounded-lg px-2.5 py-2 dark:bg-slate-900"
                @change="updateStatus"
              >
                <option value="open">Open</option>
                <option value="closed">Closed</option>
              </select>
              <button
                type="button"
                class="p-2 text-slate-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors"
                title="Delete conversation"
                @click="deleteConversation"
              >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
              </button>
            </div>
          </div>
          <ChatPanel
            class="flex-1 min-h-0"
            variant="admin"
            link-context="admin"
            hide-start-form
            :conversation-id="selectedId"
            :messages="messages"
            :loading="detailLoading"
            :sending="sending"
            :error="error"
            @send="sendAdminMessage"
          />
        </template>
        <div v-else class="flex-1 flex flex-col items-center justify-center text-center p-8 sm:p-12">
          <div class="w-16 h-16 rounded-2xl bg-slate-100 dark:bg-slate-700 flex items-center justify-center mb-4">
            <svg class="w-8 h-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
          </div>
          <p class="text-slate-600 dark:text-slate-300 font-medium">Select a conversation</p>
          <p class="text-sm text-slate-400 mt-1 max-w-xs">Choose a thread from the inbox to read history and reply.</p>
        </div>
      </main>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, watch } from 'vue';
import { useRoute } from 'vue-router';
import api from '../../services/api';
import ChatPanel from '../../components/chat/ChatPanel.vue';
import { useAdminChatNotifier } from '../../composables/useAdminChatNotifier';
import { playNotificationSound } from '../../composables/useNotificationSound';
import { useConfirm } from '../../composables/useConfirm';
import { useToast } from '../../composables/useToast';

const route = useRoute();
const toast = useToast();
const { refresh, acknowledgeMessageIds } = useAdminChatNotifier();

const conversations = ref([]);
const listLoading = ref(true);
const detailLoading = ref(false);
const sending = ref(false);
const error = ref('');
const selectedId = ref(null);
const messages = ref([]);
const activeConversation = ref(null);
const statusFilter = ref('open');
const mobileShowThread = ref(false);
let pollTimer = null;

const totalUnread = computed(() => conversations.value.reduce((s, c) => s + (c.unread_count || 0), 0));

function initials(name) {
  if (!name) return '?';
  const parts = String(name).trim().split(/\s+/);
  if (parts.length >= 2) return (parts[0][0] + parts[1][0]).toUpperCase();
  return name.slice(0, 2).toUpperCase();
}

async function fetchList() {
  try {
    const { data } = await api.get('/api/v1/admin/conversations');
    const raw = data.data ?? data;
    conversations.value = Array.isArray(raw) ? raw : (raw?.data ?? []);
  } catch {
    conversations.value = [];
  } finally {
    listLoading.value = false;
  }
}

async function openThread(id) {
  selectedId.value = id;
  mobileShowThread.value = true;
  await loadThread(id);
}

async function loadThread(id) {
  detailLoading.value = true;
  error.value = '';
  const seenCustomerIds = messages.value.filter((m) => !m.is_from_staff).map((m) => m.id);
  try {
    const { data } = await api.get(`/api/v1/admin/conversations/${id}`);
    activeConversation.value = data.data;
    messages.value = data.data?.messages ?? [];
    statusFilter.value = data.data?.status || 'open';

    const newCustomerMessages = messages.value.filter(
      (m) => !m.is_from_staff && !seenCustomerIds.includes(m.id)
    );
    if (newCustomerMessages.length && seenCustomerIds.length > 0) {
      playNotificationSound();
    }

    acknowledgeMessageIds(messages.value.filter((m) => !m.is_from_staff).map((m) => m.id));
    await fetchList();
    await refresh();
  } catch (e) {
    error.value = e.response?.data?.message || 'Could not load conversation.';
  } finally {
    detailLoading.value = false;
  }
}

async function sendAdminMessage(body) {
  if (!selectedId.value) return;
  sending.value = true;
  try {
    await api.post(`/api/v1/admin/conversations/${selectedId.value}/messages`, { body });
    await loadThread(selectedId.value);
  } catch (e) {
    error.value = e.response?.data?.message || 'Send failed.';
  } finally {
    sending.value = false;
  }
}

async function updateStatus() {
  if (!selectedId.value) return;
  try {
    await api.patch(`/api/v1/admin/conversations/${selectedId.value}/status`, { status: statusFilter.value });
    toast.success('Status updated.');
  } catch {
    toast.error('Could not update status.');
  }
}

async function deleteConversation() {
  if (!selectedId.value) return;
  const ok = await useConfirm().show({
    title: 'Delete conversation',
    message: 'This permanently removes the chat and all messages. This cannot be undone.',
    confirmLabel: 'Delete',
    cancelLabel: 'Cancel',
    variant: 'danger',
  });
  if (!ok) return;
  try {
    await api.delete(`/api/v1/admin/conversations/${selectedId.value}`);
    selectedId.value = null;
    activeConversation.value = null;
    messages.value = [];
    mobileShowThread.value = false;
    await fetchList();
    await refresh();
    toast.success('Conversation deleted.');
  } catch {
    toast.error('Could not delete conversation.');
  }
}

onMounted(async () => {
  await fetchList();
  const openId = route.query.conversation;
  if (openId) await openThread(Number(openId));
  pollTimer = setInterval(async () => {
    await fetchList();
    if (selectedId.value) await loadThread(selectedId.value);
  }, 8000);
});

onUnmounted(() => {
  if (pollTimer) clearInterval(pollTimer);
});

watch(() => route.query.conversation, (id) => {
  if (id) openThread(Number(id));
});
</script>
