<template>
  <div class="relative" ref="containerRef">
    <button
      type="button"
      class="p-2 md:p-2.5 text-slate-500 hover:text-slate-700 hover:bg-slate-100 rounded-lg md:rounded-xl transition-colors relative touch-manipulation min-h-[44px] min-w-[44px] md:min-h-0 md:min-w-0 flex items-center justify-center"
      aria-label="Quote notifications"
      @click="open = !open"
    >
      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
      <span v-if="pendingCount > 0" class="absolute top-1.5 right-1.5 min-w-[18px] h-[18px] px-1 bg-red-500 text-white text-[10px] font-bold rounded-md flex items-center justify-center">
        {{ pendingCount > 99 ? '99+' : pendingCount }}
      </span>
    </button>

    <div
      v-show="open"
      class="absolute right-0 top-full mt-1 w-80 max-h-[70vh] overflow-hidden bg-white rounded-xl border border-slate-200 shadow-xl z-50 flex flex-col"
    >
      <div class="p-3 border-b border-slate-200 bg-slate-50 flex items-center justify-between">
        <span class="text-xs font-bold text-slate-600 uppercase tracking-wider">Quote requests</span>
        <span v-if="pendingCount > 0" class="text-xs font-medium text-amber-600">{{ pendingCount }} pending</span>
      </div>
      <div class="overflow-y-auto flex-1">
        <p v-if="loading" class="p-4 text-sm text-slate-500">Loading…</p>
        <p v-else-if="!recent.length" class="p-4 text-sm text-slate-500">No recent requests.</p>
        <ul v-else class="py-2">
          <li
            v-for="quote in recent"
            :key="quote.id"
            class="px-4 py-3 hover:bg-slate-50 border-b border-slate-100 last:border-0 cursor-pointer transition-colors"
            @click="goToQuote(quote.id)"
          >
            <div class="flex items-start justify-between gap-2">
              <div class="min-w-0 flex-1">
                <p class="text-sm font-semibold text-slate-800 truncate">#{{ quote.id }} · {{ quote.customer_name || quote.requester?.email || 'Customer' }}</p>
                <p class="text-xs text-slate-500 mt-0.5">ZIP {{ quote.delivery_zip }} · {{ quote.status }}</p>
              </div>
              <span :class="quote.status === 'pending' ? 'bg-amber-100 text-amber-700' : 'bg-slate-100 text-slate-600'" class="shrink-0 text-[10px] font-semibold px-2 py-0.5 rounded-md capitalize">{{ quote.status }}</span>
            </div>
          </li>
        </ul>
      </div>
      <div class="p-2 border-t border-slate-200 bg-slate-50">
        <router-link to="/admin/quotes" class="block text-center text-sm font-medium text-emerald-600 hover:text-emerald-700 py-2" @click="open = false">
          View all quotes
        </router-link>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { useRouter } from 'vue-router';
import api from '../../services/api';

const router = useRouter();
const open = ref(false);
const loading = ref(true);
const pendingCount = ref(0);
const recent = ref([]);
const containerRef = ref(null);

async function fetchNotifications() {
  try {
    const { data } = await api.get('/api/v1/admin/quote-requests/notifications');
    pendingCount.value = data.pending_count ?? 0;
    recent.value = data.recent ?? [];
  } catch {
    recent.value = [];
  } finally {
    loading.value = false;
  }
}

function goToQuote(quoteId) {
  open.value = false;
  router.push({ path: '/admin/quotes', query: { open: quoteId } });
}

function onClickOutside(e) {
  if (containerRef.value && !containerRef.value.contains(e.target)) {
    open.value = false;
  }
}

onMounted(() => {
  fetchNotifications();
  document.addEventListener('click', onClickOutside);
});
onUnmounted(() => {
  document.removeEventListener('click', onClickOutside);
});
</script>
