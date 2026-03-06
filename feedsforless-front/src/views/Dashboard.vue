<template>
  <div class="space-y-6 sm:space-y-8">
    <div v-if="welcomeMessage" class="p-3 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm font-medium">
      You have signed in successfully.
    </div>
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
      <div>
        <h1 class="text-2xl sm:text-3xl font-bold tracking-tight text-slate-900">Dashboard</h1>
        <p class="text-slate-500 mt-1 text-sm sm:text-base">Overview of B2B operations and quotes.</p>
      </div>
      <router-link
        to="/admin/quotes"
        class="inline-flex items-center justify-center gap-2 px-4 py-3 sm:px-5 sm:py-2.5 bg-emerald-600 text-white font-medium rounded-xl hover:bg-emerald-700 transition-colors shadow-sm shadow-emerald-500/20 touch-manipulation"
      >
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
        Quotes
      </router-link>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 sm:gap-6">
      <div class="bg-white p-4 sm:p-6 rounded-xl sm:rounded-2xl border border-slate-200/80 shadow-card hover:shadow-card-hover transition-shadow relative overflow-hidden group">
        <div class="absolute right-0 top-0 -mt-4 -mr-4 w-28 h-28 bg-emerald-50 rounded-full opacity-60 group-hover:scale-110 transition-transform duration-300"></div>
        <div class="flex items-center justify-between relative z-10">
          <div>
            <p class="text-sm font-medium text-slate-500 uppercase tracking-wide">Active quotes</p>
            <p class="text-3xl font-bold text-slate-900 mt-2 tabular-nums">{{ stats.active_quotes || 0 }}</p>
          </div>
          <div class="w-12 h-12 bg-emerald-100 rounded-xl flex items-center justify-center text-emerald-600 shrink-0">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
          </div>
        </div>
      </div>
      <div class="bg-white p-4 sm:p-6 rounded-xl sm:rounded-2xl border border-slate-200/80 shadow-card hover:shadow-card-hover transition-shadow relative overflow-hidden group">
        <div class="absolute right-0 top-0 -mt-4 -mr-4 w-28 h-28 bg-slate-100 rounded-full opacity-60 group-hover:scale-110 transition-transform duration-300"></div>
        <div class="flex items-center justify-between relative z-10">
          <div>
            <p class="text-sm font-medium text-slate-500 uppercase tracking-wide">Products</p>
            <p class="text-3xl font-bold text-slate-900 mt-2 tabular-nums">{{ stats.total_products || 0 }}</p>
          </div>
          <div class="w-12 h-12 bg-slate-100 rounded-xl flex items-center justify-center text-slate-600 shrink-0">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
          </div>
        </div>
      </div>
      <div class="bg-white p-4 sm:p-6 rounded-xl sm:rounded-2xl border border-slate-200/80 shadow-card hover:shadow-card-hover transition-shadow relative overflow-hidden group">
        <div class="absolute right-0 top-0 -mt-4 -mr-4 w-28 h-28 bg-slate-100 rounded-full opacity-60 group-hover:scale-110 transition-transform duration-300"></div>
        <div class="flex items-center justify-between relative z-10">
          <div>
            <p class="text-sm font-medium text-slate-500 uppercase tracking-wide">Users</p>
            <p class="text-3xl font-bold text-slate-900 mt-2 tabular-nums">{{ stats.total_users || 0 }}</p>
          </div>
          <div class="w-12 h-12 bg-slate-100 rounded-xl flex items-center justify-center text-slate-600 shrink-0">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
          </div>
        </div>
      </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 sm:gap-8">
      <div class="lg:col-span-2 bg-white p-4 sm:p-6 rounded-xl sm:rounded-2xl border border-slate-200/80 shadow-card">
        <h2 class="text-lg font-semibold text-slate-800 mb-6">Request volume (RFQ)</h2>
        <div class="h-64 flex items-end justify-between gap-2 px-2 pb-4">
          <div class="flex-1 bg-slate-100 hover:bg-slate-200 rounded-t-md h-[30%] min-h-[24px] transition-colors relative group"><div class="absolute -top-9 left-1/2 -translate-x-1/2 bg-slate-800 text-white text-xs py-1.5 px-2.5 rounded-lg opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap shadow-lg">12</div></div>
          <div class="flex-1 bg-slate-100 hover:bg-slate-200 rounded-t-md h-[50%] min-h-[24px] transition-colors relative group"><div class="absolute -top-9 left-1/2 -translate-x-1/2 bg-slate-800 text-white text-xs py-1.5 px-2.5 rounded-lg opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap shadow-lg">25</div></div>
          <div class="flex-1 bg-emerald-500 hover:bg-emerald-600 rounded-t-md h-[80%] min-h-[24px] transition-colors relative group shadow shadow-emerald-500/20"><div class="absolute -top-9 left-1/2 -translate-x-1/2 bg-slate-800 text-white text-xs py-1.5 px-2.5 rounded-lg opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap shadow-lg">45</div></div>
          <div class="flex-1 bg-slate-100 hover:bg-slate-200 rounded-t-md h-[40%] min-h-[24px] transition-colors relative group"><div class="absolute -top-9 left-1/2 -translate-x-1/2 bg-slate-800 text-white text-xs py-1.5 px-2.5 rounded-lg opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap shadow-lg">18</div></div>
          <div class="flex-1 bg-slate-100 hover:bg-slate-200 rounded-t-md h-[60%] min-h-[24px] transition-colors relative group"><div class="absolute -top-9 left-1/2 -translate-x-1/2 bg-slate-800 text-white text-xs py-1.5 px-2.5 rounded-lg opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap shadow-lg">30</div></div>
          <div class="flex-1 bg-slate-100 hover:bg-slate-200 rounded-t-md h-[90%] min-h-[24px] transition-colors relative group"><div class="absolute -top-9 left-1/2 -translate-x-1/2 bg-slate-800 text-white text-xs py-1.5 px-2.5 rounded-lg opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap shadow-lg">52</div></div>
          <div class="flex-1 bg-slate-100 hover:bg-slate-200 rounded-t-md h-[70%] min-h-[24px] transition-colors relative group"><div class="absolute -top-9 left-1/2 -translate-x-1/2 bg-slate-800 text-white text-xs py-1.5 px-2.5 rounded-lg opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap shadow-lg">38</div></div>
        </div>
        <div class="flex justify-between text-xs text-slate-400 px-2 mt-3 font-medium">
          <span>Mon</span><span>Tue</span><span>Wed</span><span>Thu</span><span>Fri</span><span>Sat</span><span>Sun</span>
        </div>
      </div>

      <div class="bg-white p-4 sm:p-6 rounded-xl sm:rounded-2xl border border-slate-200/80 shadow-card overflow-hidden flex flex-col">
        <h2 class="text-lg font-semibold text-slate-800 mb-6 shrink-0">Recent quotes</h2>
        <div class="space-y-4 overflow-y-auto flex-1 min-h-0">
          <div v-if="loading" class="flex items-center justify-center py-12 text-slate-400 text-sm">Loading…</div>
          <div v-else-if="!stats.recent_quotes || stats.recent_quotes.length === 0" class="flex items-center justify-center py-12 text-slate-400 text-sm">No recent activity.</div>
          <div v-for="(quote, i) in stats.recent_quotes" :key="quote.id" class="flex relative">
            <div v-if="i < (stats.recent_quotes?.length || 0) - 1" class="absolute left-4 top-10 bottom-0 w-px bg-slate-100"></div>
            <div class="w-8 h-8 rounded-full flex items-center justify-center shrink-0 z-10 ring-4 ring-white" :class="quote.status === 'pending' ? 'bg-amber-100 text-amber-700' : 'bg-emerald-100 text-emerald-700'">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            </div>
            <div class="ml-4 pb-5">
              <p class="text-sm font-medium text-slate-800">#{{ quote.id }}</p>
              <p class="text-xs text-slate-500 mt-0.5">{{ quote.user?.name || 'Customer' }}</p>
              <span class="inline-flex mt-1.5 px-2 py-0.5 rounded-md text-xs font-medium" :class="quote.status === 'pending' ? 'bg-amber-50 text-amber-700' : 'bg-emerald-50 text-emerald-700'">{{ quote.status }}</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import api from '../services/api';

const route = useRoute();
const router = useRouter();
const stats = ref({});
const loading = ref(true);
const welcomeMessage = ref(false);

const fetchStats = async () => {
  try {
    const res = await api.get('/api/v1/admin/dashboard/stats');
    stats.value = res.data.data;
  } catch (e) {
    console.error('Error fetching stats', e);
  } finally {
    loading.value = false;
  }
};

onMounted(() => {
  fetchStats();
  if (route.query.welcome === '1') {
    welcomeMessage.value = true;
    router.replace({ name: 'Dashboard' });
    setTimeout(() => { welcomeMessage.value = false; }, 4000);
  }
});
</script>
