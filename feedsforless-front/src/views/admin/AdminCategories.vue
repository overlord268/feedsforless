<template>
  <div class="space-y-5">
    <div v-if="successMessage" class="p-3 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm font-medium">{{ successMessage }}</div>
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
      <div>
        <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">Categories</h1>
        <p class="text-slate-500 mt-0.5 text-sm sm:text-base">Main catalog: organize products by category.</p>
      </div>
      <button type="button" class="inline-flex items-center justify-center gap-2 px-4 py-3 sm:py-2 rounded-xl bg-emerald-600 text-white font-medium hover:bg-emerald-700 transition touch-manipulation" @click="openAdd">+ Add category</button>
    </div>
    <div class="bg-white rounded-xl sm:rounded-2xl border border-slate-200/80 shadow-card overflow-hidden">
      <div class="overflow-x-auto table-scroll">
        <table class="w-full text-sm min-w-[600px]">
          <thead class="bg-slate-50/80 border-b border-slate-200">
            <tr class="text-left">
              <th class="px-6 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider">ID</th>
              <th class="px-6 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider">Name</th>
              <th class="px-6 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider">Slug</th>
              <th class="px-6 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider">Parent</th>
              <th class="px-6 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider w-24">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            <tr v-for="cat in categories" :key="cat.id" class="hover:bg-slate-50/70 transition-colors">
              <td class="px-6 py-4 text-slate-700">{{ cat.id }}</td>
              <td class="px-6 py-4 text-slate-800">{{ cat.label }}</td>
              <td class="px-6 py-4 text-slate-600 font-mono text-xs">{{ cat.slug }}</td>
              <td class="px-6 py-4 text-slate-600">{{ cat.parent_id ? `#${cat.parent_id}` : '—' }}</td>
              <td class="px-6 py-4">
                <div class="flex items-center gap-1">
                  <button type="button" @click="openEdit(cat)" class="p-2 text-slate-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-colors" title="Edit"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg></button>
                  <button type="button" @click="confirmDelete(cat)" class="p-2 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors" title="Delete"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
      <p v-if="!loading && categories.length === 0" class="px-6 py-12 text-center text-slate-500">No categories.</p>
      <div v-if="loading" class="px-6 py-12 text-center text-slate-500">Loading…</div>
    </div>

    <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/40 backdrop-blur-sm" @click.self="showModal = false">
      <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full p-6 border border-slate-200/80">
        <h2 class="text-lg font-semibold text-slate-900 mb-4">{{ editingId ? 'Edit category' : 'Add category' }}</h2>
        <form @submit.prevent="submitForm" class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Name (label) <span class="text-red-500">*</span></label>
            <input v-model="form.label" type="text" required class="w-full rounded-lg border border-slate-300 px-3 py-2 text-slate-900" placeholder="e.g. Ingredients" />
            <p v-if="errors.label" class="text-red-500 text-xs mt-1">{{ errors.label[0] }}</p>
          </div>
          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Slug <span class="text-red-500">*</span></label>
            <input v-model="form.slug" type="text" required class="w-full rounded-lg border border-slate-300 px-3 py-2 text-slate-900 font-mono text-sm" placeholder="ej-ingredientes" />
            <p v-if="errors.slug" class="text-red-500 text-xs mt-1">{{ errors.slug[0] }}</p>
          </div>
          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Parent category</label>
            <select v-model="form.parent_id" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-slate-900">
              <option :value="null">None (root)</option>
              <option v-for="c in categories.filter(c => c.id !== editingId)" :key="c.id" :value="c.id">{{ c.label }}</option>
            </select>
          </div>
          <div class="flex gap-2 justify-end pt-2">
            <button type="button" class="px-4 py-2 rounded-lg border border-slate-300 text-slate-700 hover:bg-slate-50" @click="closeModal">Cancel</button>
            <button type="submit" class="px-4 py-2 rounded-lg bg-emerald-600 text-white hover:bg-emerald-700" :disabled="saving">{{ saving ? 'Saving…' : 'Save' }}</button>
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

const categories = ref([]);
const loading = ref(true);
const showModal = ref(false);
const saving = ref(false);
const editingId = ref(null);
const errors = ref({});
const successMessage = ref('');

const form = reactive({ label: '', slug: '', parent_id: null });

function setSuccess(msg) { successMessage.value = msg; setTimeout(() => { successMessage.value = ''; }, 4000); }

async function fetchCategories() {
  loading.value = true;
  try {
    const { data } = await api.get('/api/v1/admin/categories', { params: { per_page: 200 } });
    const raw = data?.data ?? data;
    categories.value = Array.isArray(raw) ? raw : (raw?.data ?? []);
  } catch (e) {
    console.error(e);
    categories.value = [];
  } finally {
    loading.value = false;
  }
}

function openAdd() {
  editingId.value = null;
  form.label = '';
  form.slug = '';
  form.parent_id = null;
  errors.value = {};
  showModal.value = true;
}

function openEdit(cat) {
  editingId.value = cat.id;
  form.label = cat.label ?? '';
  form.slug = cat.slug ?? '';
  form.parent_id = cat.parent_id ?? null;
  errors.value = {};
  showModal.value = true;
}

function closeModal() {
  showModal.value = false;
  errors.value = {};
}

async function submitForm() {
  saving.value = true;
  errors.value = {};
  try {
    if (editingId.value) {
      await api.put(`/api/v1/admin/categories/${editingId.value}`, form);
      setSuccess('Category updated.');
    } else {
      await api.post('/api/v1/admin/categories', form);
      setSuccess('Category created.');
    }
    closeModal();
    await fetchCategories();
  } catch (e) {
    if (e.response?.status === 422) {
      errors.value = e.response.data.errors || {};
    } else {
      console.error(e);
    }
  } finally {
    saving.value = false;
  }
}

async function confirmDelete(cat) {
  const ok = await useConfirm().show({ title: 'Delete category', message: `Delete "${cat.label}"?`, confirmLabel: 'Delete', cancelLabel: 'Cancel', variant: 'danger' });
  if (!ok) return;
  try {
    await api.delete(`/api/v1/admin/categories/${cat.id}`);
    setSuccess('Category deleted.');
    await fetchCategories();
  } catch (e) {
    console.error(e);
  }
}

onMounted(fetchCategories);
</script>
