<template>
  <div class="relative" ref="containerRef">
    <button
      type="button"
      class="p-2 md:p-2.5 text-slate-500 hover:text-slate-700 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-lg md:rounded-xl transition-colors relative touch-manipulation min-h-[44px] min-w-[44px] md:min-h-0 md:min-w-0 flex items-center justify-center"
      aria-label="Chat notifications"
      @click="toggleOpen"
    >
      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
      </svg>
      <span
        v-if="unreadCount > 0"
        class="absolute top-1.5 right-1.5 min-w-[18px] h-[18px] px-1 bg-blue-600 text-white text-[10px] font-bold rounded-md flex items-center justify-center animate-pulse"
      >
        {{ unreadCount > 99 ? '99+' : unreadCount }}
      </span>
    </button>

    <div
      v-show="open"
      class="absolute right-0 top-full mt-1 w-[min(100vw-2rem,20rem)] max-h-[70vh] overflow-hidden bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-xl z-50 flex flex-col"
    >
      <div class="p-3 border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/50 flex items-center justify-between">
        <span class="text-xs font-bold text-slate-600 dark:text-slate-300 uppercase tracking-wider">Messages</span>
        <span v-if="unreadCount > 0" class="text-xs font-medium text-blue-600">{{ unreadCount }} unread</span>
      </div>
      <div class="overflow-y-auto flex-1">
        <p v-if="!recentPreview.length" class="p-4 text-sm text-slate-500">No conversations yet.</p>
        <ul v-else class="py-1">
          <li
            v-for="conv in recentPreview"
            :key="conv.id"
            class="px-4 py-3 hover:bg-slate-50 dark:hover:bg-slate-700/50 border-b border-slate-100 dark:border-slate-700 last:border-0 cursor-pointer"
            @click="goToConversation(conv.id)"
          >
            <div class="flex items-start justify-between gap-2">
              <div class="min-w-0 flex-1">
                <p class="text-sm font-semibold text-slate-800 dark:text-white truncate">{{ conv.customer_name }}</p>
                <p class="text-xs text-slate-500 truncate">{{ conv.latest_message?.body || '—' }}</p>
              </div>
              <span v-if="conv.unread_count > 0" class="shrink-0 text-[10px] font-bold bg-blue-600 text-white px-1.5 py-0.5 rounded-full">{{ conv.unread_count }}</span>
            </div>
          </li>
        </ul>
      </div>
      <div class="p-2 border-t border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/50">
        <router-link to="/admin/messages" class="block text-center text-sm font-medium text-blue-600 hover:text-blue-700 py-2" @click="open = false">
          Open inbox
        </router-link>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { useRouter } from 'vue-router';
import { unlockNotificationSound } from '../../composables/useNotificationSound';
import { useAdminChatNotifier } from '../../composables/useAdminChatNotifier';

const router = useRouter();
const open = ref(false);
const containerRef = ref(null);

const { unreadCount, recentPreview } = useAdminChatNotifier();

function toggleOpen() {
  unlockNotificationSound();
  open.value = !open.value;
}

function goToConversation(id) {
  open.value = false;
  router.push({ path: '/admin/messages', query: { conversation: id } });
}

function onClickOutside(e) {
  if (containerRef.value && !containerRef.value.contains(e.target)) {
    open.value = false;
  }
}

onMounted(() => {
  document.addEventListener('click', onClickOutside);
});

onUnmounted(() => {
  document.removeEventListener('click', onClickOutside);
});
</script>
