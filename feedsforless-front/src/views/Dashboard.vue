<template>
  <div class="space-y-6 sm:space-y-8">
    <div v-if="welcomeMessage" class="p-3 rounded-xl bg-emerald-50 dark:bg-emerald-900/30 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-200 text-sm font-medium">
      You have signed in successfully.
    </div>

    <!-- ========== CLIENT DASHBOARD ========== -->
    <template v-if="!isAdmin">
      <div class="flex flex-col gap-4">
        <div>
          <h1 class="text-2xl sm:text-3xl font-bold tracking-tight text-slate-900 dark:text-white">Welcome back</h1>
          <p class="text-slate-500 dark:text-slate-400 mt-1 text-sm sm:text-base">Here’s an overview of your activity and quick access to what you need.</p>
        </div>
      </div>

      <div class="grid grid-cols-2 md:grid-cols-4 gap-3 sm:gap-4">
        <div class="bg-white dark:bg-slate-800 p-4 rounded-xl border border-slate-200 dark:border-slate-700 shadow-card hover:shadow-card-hover transition-shadow">
          <p class="text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wide">Pending quotes</p>
          <p class="text-2xl font-bold text-slate-900 dark:text-white mt-1 tabular-nums">{{ clientStats.pending_quotes ?? '–' }}</p>
          <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Awaiting pricing</p>
        </div>
        <div class="bg-white dark:bg-slate-800 p-4 rounded-xl border border-slate-200 dark:border-slate-700 shadow-card hover:shadow-card-hover transition-shadow">
          <p class="text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wide">Quoted</p>
          <p class="text-2xl font-bold text-emerald-600 dark:text-emerald-400 mt-1 tabular-nums">{{ clientStats.quoted_quotes ?? '–' }}</p>
          <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Ready to review</p>
        </div>
        <div class="bg-white dark:bg-slate-800 p-4 rounded-xl border border-slate-200 dark:border-slate-700 shadow-card hover:shadow-card-hover transition-shadow">
          <p class="text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wide">Accepted</p>
          <p class="text-2xl font-bold text-slate-900 dark:text-white mt-1 tabular-nums">{{ clientStats.accepted_quotes ?? '–' }}</p>
          <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Confirmed orders</p>
        </div>
        <div class="bg-white dark:bg-slate-800 p-4 rounded-xl border border-slate-200 dark:border-slate-700 shadow-card hover:shadow-card-hover transition-shadow">
          <p class="text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wide">Addresses</p>
          <p class="text-2xl font-bold text-slate-900 dark:text-white mt-1 tabular-nums">{{ clientStats.addresses_count ?? '–' }}</p>
          <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Delivery locations</p>
        </div>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 bg-white dark:bg-slate-800 p-4 sm:p-6 rounded-xl border border-slate-200 dark:border-slate-700 shadow-card">
          <h2 class="text-lg font-semibold text-slate-800 dark:text-white mb-4">Quick actions</h2>
          <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            <router-link to="/app/catalog" class="flex items-center gap-3 p-4 rounded-xl border border-slate-200 dark:border-slate-600 hover:border-emerald-500 hover:bg-emerald-50 dark:hover:bg-emerald-900/20 transition-colors group">
              <div class="w-10 h-10 rounded-lg bg-emerald-100 dark:bg-emerald-900/50 flex items-center justify-center text-emerald-600 dark:text-emerald-400 group-hover:scale-105 transition-transform">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
              </div>
              <div>
                <p class="font-medium text-slate-900 dark:text-white">Browse catalog</p>
                <p class="text-xs text-slate-500 dark:text-slate-400">Request quotes for products</p>
              </div>
            </router-link>
            <router-link to="/quotes" class="flex items-center gap-3 p-4 rounded-xl border border-slate-200 dark:border-slate-600 hover:border-emerald-500 hover:bg-emerald-50 dark:hover:bg-emerald-900/20 transition-colors group">
              <div class="w-10 h-10 rounded-lg bg-slate-100 dark:bg-slate-700 flex items-center justify-center text-slate-600 dark:text-slate-300 group-hover:scale-105 transition-transform">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
              </div>
              <div>
                <p class="font-medium text-slate-900 dark:text-white">My quotes</p>
                <p class="text-xs text-slate-500 dark:text-slate-400">View and manage requests</p>
              </div>
            </router-link>
            <router-link to="/addresses" class="flex items-center gap-3 p-4 rounded-xl border border-slate-200 dark:border-slate-600 hover:border-emerald-500 hover:bg-emerald-50 dark:hover:bg-emerald-900/20 transition-colors group">
              <div class="w-10 h-10 rounded-lg bg-slate-100 dark:bg-slate-700 flex items-center justify-center text-slate-600 dark:text-slate-300 group-hover:scale-105 transition-transform">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
              </div>
              <div>
                <p class="font-medium text-slate-900 dark:text-white">Addresses</p>
                <p class="text-xs text-slate-500 dark:text-slate-400">Delivery locations</p>
              </div>
            </router-link>
          </div>
        </div>

        <div class="bg-white dark:bg-slate-800 p-4 sm:p-6 rounded-xl border border-slate-200 dark:border-slate-700 shadow-card flex flex-col">
          <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-semibold text-slate-800 dark:text-white">Recent quotes</h2>
            <router-link v-if="(clientStats.recent_quotes?.length || 0) > 0" to="/quotes" class="text-sm font-medium text-emerald-600 dark:text-emerald-400 hover:underline">View all</router-link>
          </div>
          <div v-if="clientLoading" class="flex items-center justify-center py-12 text-slate-400 text-sm">Loading…</div>
          <div v-else-if="!clientStats.recent_quotes?.length" class="flex flex-col items-center justify-center py-12 text-slate-500 dark:text-slate-400 text-sm text-center">
            <svg class="w-12 h-12 text-slate-300 dark:text-slate-600 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            <p>No quote requests yet.</p>
            <router-link to="/app/catalog" class="mt-2 text-emerald-600 dark:text-emerald-400 font-medium hover:underline">Browse catalog</router-link>
          </div>
          <div v-else class="space-y-3 overflow-y-auto flex-1 min-h-0">
            <div v-for="(quote, i) in clientStats.recent_quotes" :key="quote.id" class="flex gap-3 pb-3 border-b border-slate-100 dark:border-slate-700 last:border-0 last:pb-0">
              <div class="w-8 h-8 rounded-full flex items-center justify-center shrink-0" :class="quote.status === 'pending' ? 'bg-amber-100 dark:bg-amber-900/50 text-amber-700 dark:text-amber-400' : quote.status === 'quoted' ? 'bg-emerald-100 dark:bg-emerald-900/50 text-emerald-700 dark:text-emerald-400' : 'bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300'">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
              </div>
              <div class="min-w-0 flex-1">
                <p class="text-sm font-medium text-slate-800 dark:text-slate-200">#{{ quote.id }}</p>
                <p class="text-xs text-slate-500 dark:text-slate-400 truncate">{{ quoteItemsSummary(quote) }}</p>
                <span class="inline-flex mt-1 px-2 py-0.5 rounded text-xs font-medium" :class="statusClass(quote.status)">{{ quote.status }}</span>
              </div>
              <router-link :to="{ name: 'CustomerQuotes', query: { id: quote.id } }" class="text-xs text-emerald-600 dark:text-emerald-400 font-medium shrink-0 self-center">View</router-link>
            </div>
          </div>
        </div>
      </div>
    </template>

    <!-- ========== ADMIN DASHBOARD ========== -->
    <template v-else>
      <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
          <h1 class="text-2xl sm:text-3xl font-bold tracking-tight text-slate-900 dark:text-white">Dashboard</h1>
          <p class="text-slate-500 dark:text-slate-400 mt-1 text-sm sm:text-base">Overview of B2B operations and quote requests.</p>
        </div>
        <router-link to="/admin/quotes" class="inline-flex items-center justify-center gap-2 px-4 py-3 sm:px-5 sm:py-2.5 bg-emerald-600 text-white font-medium rounded-xl hover:bg-emerald-700 transition-colors shadow-sm shadow-emerald-500/20">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
          Manage quotes
        </router-link>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-3 gap-4 sm:gap-6">
        <div class="bg-white dark:bg-slate-800 p-4 sm:p-6 rounded-xl sm:rounded-2xl border border-slate-200 dark:border-slate-700 shadow-card hover:shadow-card-hover transition-shadow relative overflow-hidden group">
          <div class="absolute right-0 top-0 -mt-4 -mr-4 w-28 h-28 bg-amber-50 dark:bg-amber-900/20 rounded-full opacity-60 group-hover:scale-110 transition-transform duration-300"></div>
          <div class="flex items-center justify-between relative z-10">
            <div>
              <p class="text-sm font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wide">Pending RFQs</p>
              <p class="text-3xl font-bold text-slate-900 dark:text-white mt-2 tabular-nums">{{ adminStats.active_quotes ?? '–' }}</p>
            </div>
            <div class="w-12 h-12 bg-amber-100 dark:bg-amber-900/50 rounded-xl flex items-center justify-center text-amber-600 dark:text-amber-400 shrink-0">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            </div>
          </div>
        </div>
        <div class="bg-white dark:bg-slate-800 p-4 sm:p-6 rounded-xl sm:rounded-2xl border border-slate-200 dark:border-slate-700 shadow-card hover:shadow-card-hover transition-shadow relative overflow-hidden group">
          <div class="absolute right-0 top-0 -mt-4 -mr-4 w-28 h-28 bg-emerald-50 dark:bg-emerald-900/20 rounded-full opacity-60 group-hover:scale-110 transition-transform duration-300"></div>
          <div class="flex items-center justify-between relative z-10">
            <div>
              <p class="text-sm font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wide">Products</p>
              <p class="text-3xl font-bold text-slate-900 dark:text-white mt-2 tabular-nums">{{ adminStats.published_products ?? adminStats.total_products ?? '–' }}</p>
              <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">published</p>
            </div>
            <div class="w-12 h-12 bg-emerald-100 dark:bg-emerald-900/50 rounded-xl flex items-center justify-center text-emerald-600 dark:text-emerald-400 shrink-0">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
            </div>
          </div>
        </div>
        <div class="bg-white dark:bg-slate-800 p-4 sm:p-6 rounded-xl sm:rounded-2xl border border-slate-200 dark:border-slate-700 shadow-card hover:shadow-card-hover transition-shadow relative overflow-hidden group">
          <div class="absolute right-0 top-0 -mt-4 -mr-4 w-28 h-28 bg-slate-100 dark:bg-slate-700 rounded-full opacity-60 group-hover:scale-110 transition-transform duration-300"></div>
          <div class="flex items-center justify-between relative z-10">
            <div>
              <p class="text-sm font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wide">Users</p>
              <p class="text-3xl font-bold text-slate-900 dark:text-white mt-2 tabular-nums">{{ adminStats.total_users ?? '–' }}</p>
            </div>
            <div class="w-12 h-12 bg-slate-100 dark:bg-slate-700 rounded-xl flex items-center justify-center text-slate-600 dark:text-slate-300 shrink-0">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
            </div>
          </div>
        </div>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 sm:gap-8">
        <div class="lg:col-span-2 bg-white dark:bg-slate-800 p-4 sm:p-6 rounded-xl sm:rounded-2xl border border-slate-200 dark:border-slate-700 shadow-card">
          <h2 class="text-lg font-semibold text-slate-800 dark:text-white mb-6">RFQ volume (last 7 days)</h2>
          <div v-if="adminLoading" class="h-64 flex items-center justify-center text-slate-400 text-sm">Loading…</div>
          <div v-else class="h-64 flex items-end justify-between gap-1 sm:gap-2 px-2 pb-4">
            <template v-for="(day, idx) in (adminStats.rfq_by_day || [])" :key="day.date">
              <div class="flex-1 flex flex-col items-center gap-1 min-w-0">
                <span class="text-xs font-medium text-slate-500 dark:text-slate-400 opacity-0 sm:opacity-100 group-hover:opacity-100 transition-opacity">{{ day.count }}</span>
                <div
                  class="w-full rounded-t-md min-h-[8px] transition-all relative group"
                  :class="day.count > 0 ? 'bg-emerald-500 hover:bg-emerald-600' : 'bg-slate-100 dark:bg-slate-700'"
                  :style="{ height: barHeight(day.count) + '%' }"
                  :title="`${day.label}: ${day.count}`"
                ></div>
              </div>
            </template>
          </div>
          <div class="flex justify-between text-xs text-slate-500 dark:text-slate-400 px-2 mt-3 font-medium">
            <template v-for="day in (adminStats.rfq_by_day || [])" :key="day.date">
              <span>{{ day.label }}</span>
            </template>
          </div>
        </div>

        <div class="bg-white dark:bg-slate-800 p-4 sm:p-6 rounded-xl sm:rounded-2xl border border-slate-200 dark:border-slate-700 shadow-card overflow-hidden flex flex-col">
          <div class="flex items-center justify-between mb-4 shrink-0">
            <h2 class="text-lg font-semibold text-slate-800 dark:text-white">Recent quotes</h2>
            <router-link to="/admin/quotes" class="text-sm font-medium text-emerald-600 dark:text-emerald-400 hover:underline">View all</router-link>
          </div>
          <div v-if="adminLoading" class="flex items-center justify-center py-12 text-slate-400 text-sm">Loading…</div>
          <div v-else-if="!adminStats.recent_quotes?.length" class="flex items-center justify-center py-12 text-slate-500 dark:text-slate-400 text-sm">No recent activity.</div>
          <div v-else class="space-y-4 overflow-y-auto flex-1 min-h-0">
            <div v-for="(quote, i) in adminStats.recent_quotes" :key="quote.id" class="flex relative">
              <div v-if="i < (adminStats.recent_quotes?.length || 0) - 1" class="absolute left-4 top-10 bottom-0 w-px bg-slate-100 dark:bg-slate-700"></div>
              <div class="w-8 h-8 rounded-full flex items-center justify-center shrink-0 z-10 ring-4 ring-white dark:ring-slate-800" :class="quote.status === 'pending' ? 'bg-amber-100 dark:bg-amber-900/50 text-amber-700 dark:text-amber-400' : 'bg-emerald-100 dark:bg-emerald-900/50 text-emerald-700 dark:text-emerald-400'">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
              </div>
              <div class="ml-4 pb-5">
                <router-link :to="{ name: 'AdminQuotes', query: { id: quote.id } }" class="text-sm font-medium text-slate-800 dark:text-slate-200 hover:text-emerald-600 dark:hover:text-emerald-400">#{{ quote.id }}</router-link>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">{{ quote.customer_name || quote.requester?.email || 'Customer' }}</p>
                <span class="inline-flex mt-1.5 px-2 py-0.5 rounded-md text-xs font-medium" :class="quote.status === 'pending' ? 'bg-amber-50 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400' : 'bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400'">{{ quote.status }}</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </template>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import api from '../services/api';
import { useAuthStore } from '../stores/auth';

const route = useRoute();
const router = useRouter();
const authStore = useAuthStore();

const isAdmin = computed(() => {
  const u = authStore.user;
  if (!u || !u.roles || !Array.isArray(u.roles)) return false;
  return u.roles.some((r) => r.name === 'admin');
});

const clientStats = ref({});
const clientLoading = ref(false);
const adminStats = ref({});
const adminLoading = ref(false);
const welcomeMessage = ref(false);

const clientFetch = async () => {
  clientLoading.value = true;
  try {
    const res = await api.get('/api/v1/dashboard');
    clientStats.value = res.data.data || {};
  } catch (e) {
    console.error('Error fetching client dashboard', e);
  } finally {
    clientLoading.value = false;
  }
};

const adminFetch = async () => {
  adminLoading.value = true;
  try {
    const res = await api.get('/api/v1/admin/dashboard/stats');
    adminStats.value = res.data.data || {};
  } catch (e) {
    console.error('Error fetching admin dashboard', e);
  } finally {
    adminLoading.value = false;
  }
};

function barHeight(count) {
  if (!count) return 0;
  const data = adminStats.value.rfq_by_day || [];
  const max = Math.max(1, ...data.map((d) => d.count));
  const pct = max ? (count / max) * 100 : 0;
  return Math.max(12, Math.round(pct));
}

function quoteItemsSummary(quote) {
  const items = quote.items || [];
  if (items.length === 0) return 'No items';
  const names = items.slice(0, 2).map((i) => i.product?.name || 'Product').filter(Boolean);
  return names.length ? names.join(', ') + (items.length > 2 ? ` +${items.length - 2}` : '') : 'Quote request';
}

function statusClass(status) {
  if (status === 'pending') return 'bg-amber-50 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400';
  if (status === 'quoted') return 'bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400';
  if (status === 'accepted') return 'bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300';
  return 'bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-400';
}

onMounted(() => {
  if (isAdmin.value) adminFetch();
  else clientFetch();

  if (route.query.welcome === '1') {
    welcomeMessage.value = true;
    router.replace({ name: 'Dashboard' });
    setTimeout(() => { welcomeMessage.value = false; }, 4000);
  }
});

watch(isAdmin, (admin) => {
  if (admin) adminFetch();
  else clientFetch();
});
</script>
