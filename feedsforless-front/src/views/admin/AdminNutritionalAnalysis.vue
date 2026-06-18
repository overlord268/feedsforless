<template>
  <div class="space-y-8">
    <div>
      <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Nutritional analysis</h1>
      <p class="text-slate-500 mt-0.5">Parameters and measure units used in product nutritional analysis tables.</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
      <div>
        <div class="p-4 border-b border-slate-200 flex flex-wrap items-center justify-between gap-3 bg-white rounded-t-xl border border-b-0 border-slate-200/80">
          <h2 class="text-lg font-semibold text-slate-800">Parameters</h2>
          <router-link to="/admin/parameters" class="text-sm text-emerald-600 hover:underline font-medium">Manage parameters →</router-link>
        </div>
        <CrudTable
          :columns="parameterColumns"
          :items="parameters"
          :loading="loadingParams"
          search-placeholder="Search parameters…"
          item-label="parameters"
          empty-message="No parameters yet."
        >
          <template #row="{ item }">
            <td class="px-4 py-2.5 text-slate-800">{{ item.label }}</td>
            <td class="px-4 py-2.5 text-slate-600">{{ item.type ?? '—' }}</td>
          </template>
        </CrudTable>
      </div>

      <div>
        <div class="p-4 border-b border-slate-200 flex flex-wrap items-center justify-between gap-3 bg-white rounded-t-xl border border-b-0 border-slate-200/80">
          <h2 class="text-lg font-semibold text-slate-800">Measure units</h2>
          <router-link to="/admin/measure-units" class="text-sm text-emerald-600 hover:underline font-medium">Manage units →</router-link>
        </div>
        <CrudTable
          :columns="unitColumns"
          :items="measureUnits"
          :loading="loadingUnits"
          search-placeholder="Search units…"
          item-label="units"
          empty-message="No measure units yet."
        >
          <template #row="{ item }">
            <td class="px-4 py-2.5 text-slate-800">{{ item.label }}</td>
            <td class="px-4 py-2.5 text-slate-600">{{ item.notation ?? '—' }}</td>
          </template>
        </CrudTable>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import api from '../../services/api';
import CrudTable from '../../components/admin/CrudTable.vue';

const parameterColumns = [
  { key: 'label', label: 'Label' },
  { key: 'type', label: 'Type' },
];

const unitColumns = [
  { key: 'label', label: 'Label' },
  { key: 'notation', label: 'Notation' },
];

const parameters = ref([]);
const measureUnits = ref([]);
const loadingParams = ref(true);
const loadingUnits = ref(true);

async function fetchParameters() {
  loadingParams.value = true;
  try {
    const { data } = await api.get('/api/v1/admin/parameters');
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
    const { data } = await api.get('/api/v1/admin/measure-units');
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
