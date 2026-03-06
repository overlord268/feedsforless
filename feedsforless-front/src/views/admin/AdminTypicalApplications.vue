<template>
  <div class="space-y-5">
    <div v-if="successMessage" class="p-3 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm font-medium">{{ successMessage }}</div>
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
      <div>
        <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Typical applications</h1>
        <p class="text-slate-500 mt-0.5">Technical config.</p>
      </div>
      <button type="button" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-emerald-600 text-white font-medium hover:bg-emerald-700 transition" @click="openAdd">+ Add application</button>
    </div>
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-card overflow-hidden">
      <div class="overflow-x-auto table-scroll">
        <table class="w-full text-sm min-w-[600px]">
          <thead class="bg-slate-50 border-b border-slate-200">
            <tr class="text-left text-slate-600 font-medium">
              <th class="px-6 py-4">ID</th>
              <th class="px-6 py-4">Label</th>
              <th class="px-6 py-4">Description</th>
              <th class="px-6 py-4 w-24">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            <tr v-for="item in items" :key="item.id" class="hover:bg-slate-50/50">
              <td class="px-6 py-4 text-slate-700">{{ item.id }}</td>
              <td class="px-6 py-4 text-slate-800">{{ item.label }}</td>
              <td class="px-6 py-4 text-slate-600 max-w-xs truncate">{{ item.description ?? '—' }}</td>
              <td class="px-6 py-4">
                <div class="flex items-center gap-1">
                  <button type="button" @click="openEdit(item)" class="p-2 text-slate-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-colors" title="Edit"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg></button>
                  <button type="button" @click="confirmDelete(item)" class="p-2 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors" title="Delete"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
      <p v-if="!loading && items.length === 0" class="px-6 py-12 text-center text-slate-500">No records.</p>
      <div v-if="loading" class="px-6 py-12 text-center text-slate-500">Loading…</div>
    </div>
    <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/40 backdrop-blur-sm" @click.self="showModal = false">
      <div class="bg-white rounded-2xl shadow-xl max-w-md w-full p-6">
        <h2 class="text-lg font-semibold text-slate-900 mb-4">{{ editingId ? 'Edit typical application' : 'Add typical application' }}</h2>
        <form @submit.prevent="submitForm" class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Label</label>
            <input v-model="form.label" type="text" required class="w-full rounded-lg border border-slate-300 px-3 py-2 text-slate-900" placeholder="e.g. Animal feed" />
          </div>
          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Description</label>
            <textarea v-model="form.description" rows="3" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-slate-900" placeholder="Optional"></textarea>
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
import { useConfirm } from '../../composables/useConfirm';

const items = ref([]);
const loading = ref(true);
const showModal = ref(false);
const saving = ref(false);
const editingId = ref(null);
const successMessage = ref('');
const form = reactive({ label: '', description: '' });

function setSuccess(msg) { successMessage.value = msg; setTimeout(() => { successMessage.value = ''; }, 4000); }

async function fetchItems() {
  loading.value = true;
  try {
    const { data } = await api.get('/api/v1/admin/typical-applications');
    const raw = data?.data ?? data;
    items.value = Array.isArray(raw) ? raw : (raw?.data ?? []);
  } catch (e) { console.error(e); items.value = []; } finally { loading.value = false; }
}

function openAdd() { editingId.value = null; form.label = ''; form.description = ''; showModal.value = true; }
function openEdit(item) { editingId.value = item.id; form.label = item.label ?? ''; form.description = item.description ?? ''; showModal.value = true; }

async function submitForm() {
  saving.value = true;
  try {
    if (editingId.value) {
      await api.put(`/api/v1/admin/typical-applications/${editingId.value}`, { label: form.label, description: form.description || null });
      setSuccess('Typical application updated.');
    } else {
      await api.post('/api/v1/admin/typical-applications', { label: form.label, description: form.description || null });
      setSuccess('Typical application created.');
    }
    showModal.value = false;
    await fetchItems();
  } catch (e) { console.error(e); } finally { saving.value = false; }
}

async function confirmDelete(item) {
  const ok = await useConfirm().show({ title: 'Delete typical application', message: `Delete "${item.label}"?`, confirmLabel: 'Delete', cancelLabel: 'Cancel', variant: 'danger' });
  if (!ok) return;
  try {
    await api.delete(`/api/v1/admin/typical-applications/${item.id}`);
    setSuccess('Typical application deleted.');
    await fetchItems();
  } catch (e) { console.error(e); }
}

onMounted(fetchItems);
</script>
