<template>
  <div class="space-y-8">
    <div>
      <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Nutritional analysis</h1>
      <p class="text-slate-500 mt-0.5">Parameters and measure units used in product nutritional analysis tables.</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
      <div class="bg-white rounded-2xl border border-slate-200/80 shadow-card overflow-hidden">
        <div class="p-5 border-b border-slate-200 flex flex-wrap items-center justify-between gap-3">
          <h2 class="text-lg font-semibold text-slate-800">Parameters</h2>
          <router-link
            to="/admin/parameters"
            class="text-sm text-emerald-600 hover:underline font-medium"
          >
            Manage parameters →
          </router-link>
        </div>
        <div class="overflow-x-auto table-scroll">
          <table class="w-full text-sm min-w-[320px]">
            <thead class="bg-slate-50/80 border-b border-slate-200">
              <tr class="text-left">
                <th class="px-4 py-3 text-xs font-semibold text-slate-500 uppercase">Label</th>
                <th class="px-4 py-3 text-xs font-semibold text-slate-500 uppercase">Type</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
              <tr v-for="item in parameters" :key="item.id" class="hover:bg-slate-50/70">
                <td class="px-4 py-3 text-slate-800">{{ item.label }}</td>
                <td class="px-4 py-3 text-slate-600">{{ item.type ?? '—' }}</td>
              </tr>
            </tbody>
          </table>
        </div>
        <p v-if="!loadingParams && parameters.length === 0" class="px-4 py-8 text-center text-slate-500 text-sm">No parameters yet.</p>
        <div v-if="loadingParams" class="px-4 py-8 text-center text-slate-500 text-sm">Loading…</div>
      </div>

      <div class="bg-white rounded-2xl border border-slate-200/80 shadow-card overflow-hidden">
        <div class="p-5 border-b border-slate-200 flex flex-wrap items-center justify-between gap-3">
          <h2 class="text-lg font-semibold text-slate-800">Measure units</h2>
          <router-link
            to="/admin/measure-units"
            class="text-sm text-emerald-600 hover:underline font-medium"
          >
            Manage units →
          </router-link>
        </div>
        <div class="overflow-x-auto table-scroll">
          <table class="w-full text-sm min-w-[320px]">
            <thead class="bg-slate-50/80 border-b border-slate-200">
              <tr class="text-left">
                <th class="px-4 py-3 text-xs font-semibold text-slate-500 uppercase">Label</th>
                <th class="px-4 py-3 text-xs font-semibold text-slate-500 uppercase">Notation</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
              <tr v-for="item in measureUnits" :key="item.id" class="hover:bg-slate-50/70">
                <td class="px-4 py-3 text-slate-800">{{ item.label }}</td>
                <td class="px-4 py-3 text-slate-600">{{ item.notation ?? '—' }}</td>
              </tr>
            </tbody>
          </table>
        </div>
        <p v-if="!loadingUnits && measureUnits.length === 0" class="px-4 py-8 text-center text-slate-500 text-sm">No measure units yet.</p>
        <div v-if="loadingUnits" class="px-4 py-8 text-center text-slate-500 text-sm">Loading…</div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import api from '../../services/api';

const parameters = ref([]);
const measureUnits = ref([]);
const loadingParams = ref(true);
const loadingUnits = ref(true);

async function fetchParameters() {
  loadingParams.value = true;
  try {
    const { data } = await api.get('/api/v1/admin/parameters', { params: { per_page: 100 } });
    const raw = data?.data ?? data;
    parameters.value = Array.isArray(raw) ? raw : (raw?.data ?? []);
  } catch (e) {
    console.error(e);
    parameters.value = [];
  } finally {
    loadingParams.value = false;
  }
}

async function fetchMeasureUnits() {
  loadingUnits.value = true;
  try {
    const { data } = await api.get('/api/v1/admin/measure-units', { params: { per_page: 100 } });
    const raw = data?.data ?? data;
    measureUnits.value = Array.isArray(raw) ? raw : (raw?.data ?? []);
  } catch (e) {
    console.error(e);
    measureUnits.value = [];
  } finally {
    loadingUnits.value = false;
  }
}

onMounted(() => {
  fetchParameters();
  fetchMeasureUnits();
});
</script>
