<template>
  <div class="flex flex-col h-full min-h-0" :class="variant === 'admin' ? 'bg-slate-50/50 dark:bg-slate-900/30' : 'bg-white dark:bg-slate-800'">
    <div v-if="!conversationId && !hideStartForm" class="p-4 sm:p-5 border-b border-slate-200 dark:border-slate-700 shrink-0 bg-white dark:bg-slate-800">
      <p class="text-sm font-semibold text-slate-800 dark:text-white">Start a conversation</p>
      <p class="text-xs text-slate-500 mt-1 mb-4">Our team will reply here and by email when applicable.</p>
      <form class="space-y-3 max-w-md" @submit.prevent="onStart">
        <input
          v-if="!isLoggedIn"
          v-model="startForm.email"
          type="email"
          required
          placeholder="Business email"
          class="w-full px-3.5 py-2.5 text-sm border border-slate-200 dark:border-slate-600 rounded-xl dark:bg-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500/30 focus:border-blue-500"
        />
        <input
          v-if="!isLoggedIn"
          v-model="startForm.name"
          type="text"
          placeholder="Your name (optional)"
          class="w-full px-3.5 py-2.5 text-sm border border-slate-200 dark:border-slate-600 rounded-xl dark:bg-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500/30 focus:border-blue-500"
        />
        <textarea
          v-model="startForm.message"
          rows="3"
          :required="!isLoggedIn"
          placeholder="How can we help?"
          class="w-full px-3.5 py-2.5 text-sm border border-slate-200 dark:border-slate-600 rounded-xl resize-none dark:bg-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500/30 focus:border-blue-500"
        />
        <button
          type="submit"
          :disabled="starting"
          class="w-full sm:w-auto px-6 py-2.5 bg-[#2962ff] text-white text-sm font-semibold rounded-xl hover:bg-blue-700 disabled:opacity-60 shadow-sm"
        >
          {{ starting ? 'Sending…' : 'Send message' }}
        </button>
      </form>
    </div>

    <div ref="scrollEl" class="flex-1 overflow-y-auto overscroll-contain px-3 sm:px-5 py-4 space-y-4 min-h-[12rem]">
      <div v-if="loading && !messages.length" class="flex items-center justify-center h-full min-h-[8rem] text-sm text-slate-500">Loading messages…</div>
      <div v-else-if="conversationId && !messages.length" class="flex items-center justify-center h-full min-h-[8rem] text-sm text-slate-500">No messages yet.</div>
      <div
        v-for="msg in messages"
        :key="msg.id"
        class="flex"
        :class="messageRowClass(msg)"
      >
        <div
          v-if="msg.message_type === 'quote_reference' && msg.quote_reference"
          class="w-full max-w-md mx-auto rounded-2xl border border-dashed border-blue-200 dark:border-blue-800 bg-blue-50/80 dark:bg-blue-900/20 px-4 py-3 text-center shadow-sm"
        >
          <p class="text-[10px] font-bold uppercase tracking-wide text-blue-600 dark:text-blue-400 mb-1">Quote conversation</p>
          <p class="text-sm text-slate-700 dark:text-slate-200 mb-3">{{ msg.body }}</p>
          <router-link
            :to="{ path: quoteReferencePath(msg), hash: '#quote-chat' }"
            class="inline-flex items-center justify-center gap-2 px-4 py-2 rounded-xl bg-[#2962ff] text-white text-sm font-semibold hover:bg-blue-700 transition-colors"
          >
            Open {{ msg.quote_reference.label || 'quote' }}
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
          </router-link>
          <p class="text-[10px] mt-2 opacity-55 tabular-nums">{{ formatTime(msg.created_at) }}</p>
        </div>
        <div
          v-else
          class="max-w-[min(100%,28rem)] rounded-2xl px-4 py-2.5 text-sm shadow-sm"
          :class="msg.is_from_staff
            ? 'bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-600 text-slate-800 dark:text-slate-100 rounded-bl-md'
            : 'bg-[#2962ff] text-white rounded-br-md'"
        >
          <p v-if="msg.is_from_staff && msg.sender_name" class="text-[10px] font-bold uppercase tracking-wide opacity-60 mb-1">{{ msg.sender_name }}</p>
          <p class="whitespace-pre-wrap break-words leading-relaxed">{{ msg.body }}</p>
          <p class="text-[10px] mt-1.5 opacity-55 tabular-nums">{{ formatTime(msg.created_at) }}</p>
        </div>
      </div>
    </div>

    <form
      v-if="conversationId"
      class="p-3 sm:p-4 border-t border-slate-200 dark:border-slate-700 shrink-0 bg-white dark:bg-slate-800 flex flex-col sm:flex-row gap-2 sm:gap-3"
      @submit.prevent="onSend"
    >
      <textarea
        v-model="draft"
        rows="1"
        placeholder="Type a message…"
        class="flex-1 min-h-[44px] max-h-32 px-3.5 py-2.5 text-sm border border-slate-200 dark:border-slate-600 rounded-xl resize-y dark:bg-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500/30 focus:border-blue-500"
        @keydown.enter.exact.prevent="onSend"
      />
      <button
        type="submit"
        :disabled="sending || !draft.trim()"
        class="px-5 py-2.5 bg-[#2962ff] text-white text-sm font-semibold rounded-xl hover:bg-blue-700 disabled:opacity-60 shrink-0 min-h-[44px] sm:self-end"
      >
        {{ sending ? '…' : 'Send' }}
      </button>
    </form>

    <p v-if="error" class="px-4 pb-3 text-xs text-red-600 dark:text-red-400">{{ error }}</p>
  </div>
</template>

<script setup>
import { ref, watch, nextTick, computed } from 'vue';
import { useAuthStore } from '../../stores/auth';

const props = defineProps({
  conversationId: { type: [Number, String], default: null },
  messages: { type: Array, default: () => [] },
  loading: { type: Boolean, default: false },
  sending: { type: Boolean, default: false },
  starting: { type: Boolean, default: false },
  error: { type: String, default: '' },
  hideStartForm: { type: Boolean, default: false },
  variant: { type: String, default: 'default' },
  linkContext: { type: String, default: 'customer' },
});

const emit = defineEmits(['start', 'send']);

const authStore = useAuthStore();
const isLoggedIn = computed(() => !!authStore.token);
const draft = ref('');
const scrollEl = ref(null);

const startForm = ref({
  email: authStore.user?.email || '',
  name: '',
  message: '',
});

watch(() => props.messages.length, async () => {
  await nextTick();
  if (scrollEl.value) {
    scrollEl.value.scrollTop = scrollEl.value.scrollHeight;
  }
});

function formatTime(iso) {
  if (!iso) return '';
  try {
    return new Date(iso).toLocaleString(undefined, { month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' });
  } catch {
    return '';
  }
}

function onStart() {
  emit('start', { ...startForm.value });
}

function onSend() {
  if (!draft.value.trim()) return;
  emit('send', draft.value);
  draft.value = '';
}

function messageRowClass(msg) {
  if (msg.message_type === 'quote_reference') return 'justify-center';
  return msg.is_from_staff ? 'justify-start' : 'justify-end';
}

function quoteReferencePath(msg) {
  const ref = msg.quote_reference;
  if (!ref) return '/';
  if (props.linkContext === 'admin' && ref.admin_path) return ref.admin_path;
  return ref.customer_path || ref.admin_path || '/';
}
</script>
