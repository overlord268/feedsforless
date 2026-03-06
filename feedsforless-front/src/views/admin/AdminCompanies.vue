<template>
  <div class="space-y-5">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
      <div>
        <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Companies</h1>
        <p class="text-slate-500 mt-0.5">Company catalog.</p>
      </div>
      <button
        type="button"
        class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-emerald-600 text-white font-medium hover:bg-emerald-700 transition"
        @click="openAdd"
      >
        + Add company
      </button>
    </div>
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-card overflow-hidden">
      <div class="overflow-x-auto table-scroll">
        <table class="w-full text-sm min-w-[600px]">
          <thead class="bg-slate-50/80 border-b border-slate-200">
            <tr class="text-left">
              <th class="px-6 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider">ID</th>
              <th class="px-6 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider">Name</th>
              <th class="px-6 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider">Tax classification</th>
              <th class="px-6 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider">Tax ID</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            <tr v-for="item in items" :key="item.id" class="hover:bg-slate-50/70 transition-colors">
              <td class="px-6 py-4 text-slate-700">{{ item.id }}</td>
              <td class="px-6 py-4 text-slate-800">{{ item.name }}</td>
              <td class="px-6 py-4 text-slate-600">{{ item.tax_classification ?? '—' }}</td>
              <td class="px-6 py-4 text-slate-600">{{ item.tax_registration_number ?? '—' }}</td>
            </tr>
          </tbody>
        </table>
      </div>
      <p v-if="!loading && items.length === 0" class="px-6 py-12 text-center text-slate-500">No records.</p>
      <div v-if="loading" class="px-6 py-12 text-center text-slate-500">Loading…</div>
    </div>

    <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/40 backdrop-blur-sm" @click.self="showModal = false">
      <div class="bg-white rounded-2xl shadow-xl max-w-md w-full p-6 max-h-[90vh] overflow-y-auto">
        <h2 class="text-lg font-semibold text-slate-900 mb-4">Add company</h2>
        <form @submit.prevent="submitAdd" class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Name</label>
            <input v-model="form.name" type="text" required class="w-full rounded-lg border border-slate-300 px-3 py-2 text-slate-900" placeholder="Company name" />
          </div>
          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Tax classification</label>
            <input v-model="form.tax_classification" type="text" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-slate-900" placeholder="Optional" />
          </div>
          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Tax ID / Tax registration number</label>
            <input v-model="form.tax_registration_number" type="text" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-slate-900" placeholder="Optional" />
          </div>
          <div class="flex gap-2 justify-end pt-2">
            <button type="button" class="px-4 py-2 rounded-lg border border-slate-300 text-slate-700 hover:bg-slate-50" @click="showModal = false">Cancel</button>
            <button type="submit" class="px-4 py-2 rounded-lg bg-emerald-600 text-white hover:bg-emerald-700" :disabled="saving">Save</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue';
import api from '../../services/api';

const items = ref([]);
const loading = ref(true);
const showModal = ref(false);
const saving = ref(false);
const form = reactive({ name: '', tax_classification: '', tax_registration_number: '' });

async function fetchItems() {
  loading.value = true;
  try {
    const { data } = await api.get('/api/v1/admin/companies');
    const raw = data?.data ?? data;
    items.value = Array.isArray(raw) ? raw : (raw?.data ?? []);
  } catch (e) {
    console.error(e);
    items.value = [];
  } finally {
    loading.value = false;
  }
}

function openAdd() {
  form.name = '';
  form.tax_classification = '';
  form.tax_registration_number = '';
  showModal.value = true;
}

async function submitAdd() {
  saving.value = true;
  try {
    await api.post('/api/v1/admin/companies', {
      name: form.name,
      tax_classification: form.tax_classification || null,
      tax_registration_number: form.tax_registration_number || null,
    });
    showModal.value = false;
    await fetchItems();
  } catch (e) {
    console.error(e);
  } finally {
    saving.value = false;
  }
}

onMounted(fetchItems);
</script>
