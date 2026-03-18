<template>
  <div class="space-y-5">
    <div
      v-if="successMessage"
      class="p-3 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm font-medium"
    >
      {{ successMessage }}
    </div>
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
      <div>
        <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Parameters</h1>
        <p class="text-slate-500 mt-0.5">Technical config.</p>
      </div>
      <button
        type="button"
        class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-emerald-600 text-white font-medium hover:bg-emerald-700 transition"
        @click="openAdd"
      >
        + Add parameter
      </button>
    </div>

    <CrudTable :columns="columns" :items="items" :loading="loading">
      <template #row="{ item }">
        <td class="px-6 py-4 text-slate-700">{{ item.id }}</td>
        <td class="px-6 py-4 text-slate-800">{{ item.label }}</td>
        <td class="px-6 py-4 text-slate-600">{{ item.type ?? '—' }}</td>
        <td class="px-6 py-4">
          <div class="flex items-center gap-1">
            <button
              type="button"
              class="p-2 text-slate-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-colors"
              title="Edit"
              @click="openEdit(item)"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
              </svg>
            </button>
            <button
              type="button"
              class="p-2 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors"
              title="Delete"
              @click="confirmDelete(item)"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
              </svg>
            </button>
          </div>
        </td>
      </template>
    </CrudTable>

    <SimpleFormModal
      :show="showModal"
      :title="modalTitle"
      :saving="saving"
      @close="showModal = false"
      @submit="submitForm"
    >
      <FormInput
        v-model="form.label"
        variant="admin"
        label="Label"
        type="text"
        required
        placeholder="e.g. pH"
      />
      <FormInput
        v-model="form.type"
        variant="admin"
        label="Type"
        type="text"
        placeholder="Optional"
      />
    </SimpleFormModal>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue';
import api from '../../services/api';
import { useConfirm } from '../../composables/useConfirm';
import CrudTable from '../../components/admin/CrudTable.vue';
import SimpleFormModal from '../../components/admin/SimpleFormModal.vue';
import FormInput from '../../components/ui/FormInput.vue';

const columns = [
  { key: 'id', label: 'ID' },
  { key: 'label', label: 'Label' },
  { key: 'type', label: 'Type' },
  { key: 'actions', label: 'Actions', thClass: 'px-6 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider w-24' },
];

const items = ref([]);
const loading = ref(true);
const showModal = ref(false);
const saving = ref(false);
const editingId = ref(null);
const successMessage = ref('');
const form = reactive({ label: '', type: '' });

const modalTitle = computed(() =>
  editingId.value ? 'Edit parameter' : 'Add parameter'
);

function setSuccess(msg) {
  successMessage.value = msg;
  setTimeout(() => { successMessage.value = ''; }, 4000);
}

async function fetchItems() {
  loading.value = true;
  try {
    const { data } = await api.get('/api/v1/admin/parameters');
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
  editingId.value = null;
  form.label = '';
  form.type = '';
  showModal.value = true;
}

function openEdit(item) {
  editingId.value = item.id;
  form.label = item.label ?? '';
  form.type = item.type ?? '';
  showModal.value = true;
}

async function submitForm() {
  saving.value = true;
  try {
    if (editingId.value) {
      await api.put(`/api/v1/admin/parameters/${editingId.value}`, { label: form.label, type: form.type || null });
      setSuccess('Parameter updated.');
    } else {
      await api.post('/api/v1/admin/parameters', { label: form.label, type: form.type || null });
      setSuccess('Parameter created.');
    }
    showModal.value = false;
    await fetchItems();
  } catch (e) {
    console.error(e);
  } finally {
    saving.value = false;
  }
}

async function confirmDelete(item) {
  const ok = await useConfirm().show({ title: 'Delete parameter', message: `Delete "${item.label}"?`, confirmLabel: 'Delete', cancelLabel: 'Cancel', variant: 'danger' });
  if (!ok) return;
  try {
    await api.delete(`/api/v1/admin/parameters/${item.id}`);
    setSuccess('Parameter deleted.');
    await fetchItems();
  } catch (e) {
    console.error(e);
  }
}

onMounted(fetchItems);
</script>
