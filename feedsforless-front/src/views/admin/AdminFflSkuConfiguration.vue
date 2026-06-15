<template>
  <div class="space-y-5">
    <div
      v-if="successMessage"
      class="p-3 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm font-medium"
    >
      {{ successMessage }}
    </div>

    <div>
      <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Configuration — Grades</h1>
      <p class="text-slate-500 mt-0.5">
        Register grades once. When a product's <strong>Grade</strong> field matches a row here, that SKU suffix is used automatically.
      </p>
    </div>

    <div class="p-4 rounded-xl bg-slate-50 border border-slate-200 text-sm text-slate-700 space-y-2">
      <p><strong>SKU = FFL-{CAT}-{PROD}-{GRADE}</strong></p>
      <ul class="list-disc pl-5 space-y-1">
        <li><strong>CAT</strong> — automatic from category</li>
        <li><strong>PROD</strong> — automatic from product name</li>
        <li><strong>GRADE</strong> — from this table, matched against the product's Grade field</li>
      </ul>
      <p class="text-xs text-slate-500">
        Example: product Grade = <code class="font-mono">54% MgO (0.3–2.0 mm)</code> → suffix <code class="font-mono">54F</code> → SKU <code class="font-mono">FFL-MAG-MGOX-54F</code>
      </p>
    </div>

    <div class="p-4 rounded-xl bg-amber-50 border border-amber-200 text-amber-900 text-sm">
      Changes require your admin password and are logged.
    </div>

    <div class="flex gap-2 border-b border-slate-200">
      <button
        v-for="tab in tabs"
        :key="tab.id"
        type="button"
        class="px-4 py-2 text-sm font-medium border-b-2 -mb-px transition-colors"
        :class="activeTab === tab.id ? 'border-emerald-600 text-emerald-700' : 'border-transparent text-slate-500 hover:text-slate-800'"
        @click="activeTab = tab.id"
      >
        {{ tab.label }}
      </button>
    </div>

    <div v-if="loading" class="py-12 flex justify-center text-slate-500">Loading…</div>

    <template v-else>
      <section v-show="activeTab === 'grades'" class="space-y-4">
        <div class="flex justify-between items-center">
          <p class="text-sm text-slate-600">The Grade text must match what you enter on each product (same spelling).</p>
          <button
            type="button"
            class="px-4 py-2 rounded-xl bg-emerald-600 text-white text-sm font-medium hover:bg-emerald-700"
            @click="openCreate"
          >
            + Register grade
          </button>
        </div>
        <CrudTable :columns="gradeColumns" :items="grades" :loading="false">
          <template #row="{ item }">
            <td class="px-6 py-4 text-slate-800">{{ item.grade_spec }}</td>
            <td class="px-6 py-4 font-mono font-semibold text-emerald-700">{{ item.sku_code }}</td>
            <td class="px-6 py-4">
              <div class="flex gap-1">
                <button type="button" class="p-2 text-slate-400 hover:text-blue-600 rounded-lg" title="Edit" @click="openEdit(item)">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                </button>
                <button type="button" class="p-2 text-slate-400 hover:text-red-600 rounded-lg" title="Delete" @click="confirmDelete(item)">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                </button>
              </div>
            </td>
          </template>
        </CrudTable>
      </section>

      <section v-show="activeTab === 'audit'" class="space-y-4">
        <div v-if="auditLoading" class="text-slate-500 text-sm">Loading…</div>
        <div v-else-if="audits.length === 0" class="text-slate-500 text-sm">No changes yet.</div>
        <div v-else class="overflow-x-auto rounded-xl border border-slate-200">
          <table class="min-w-full text-sm">
            <thead class="bg-slate-50 text-left text-slate-600">
              <tr>
                <th class="px-4 py-3 font-medium">When</th>
                <th class="px-4 py-3 font-medium">User</th>
                <th class="px-4 py-3 font-medium">Action</th>
                <th class="px-4 py-3 font-medium">Change</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
              <tr v-for="entry in audits" :key="entry.id">
                <td class="px-4 py-3 whitespace-nowrap text-slate-600">{{ formatDate(entry.created_at) }}</td>
                <td class="px-4 py-3 text-slate-800">{{ entry.user?.name || '—' }}</td>
                <td class="px-4 py-3 capitalize">{{ entry.action }}</td>
                <td class="px-4 py-3 font-mono text-xs text-slate-600">{{ formatAuditChange(entry) }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </section>
    </template>

    <SimpleFormModal
      :show="showFormModal"
      :title="formMode === 'create' ? 'Register grade' : 'Edit grade'"
      :saving="saving"
      save-label="Save"
      @close="closeFormModal"
      @submit="submitForm"
    >
      <FormInput
        v-model="form.grade_spec"
        variant="admin"
        label="Grade (same text as on the product)"
        type="text"
        required
        placeholder="e.g. 54% MgO (0.3–2.0 mm)"
      />
      <FormInput
        v-model="form.sku_code"
        variant="admin"
        label="SKU suffix"
        type="text"
        required
        placeholder="e.g. 54F"
      />
      <FormInput
        v-model="form.password_confirmation"
        variant="admin"
        label="Password confirmation"
        type="password"
        required
        autocomplete="current-password"
        placeholder="Your admin login password"
      />
    </SimpleFormModal>

    <SimpleFormModal
      :show="showDeleteModal"
      title="Delete grade"
      :saving="saving"
      save-label="Delete"
      @close="showDeleteModal = false"
      @submit="submitDelete"
    >
      <p class="text-sm text-slate-600 mb-4">
        Delete grade <strong>{{ deleteTarget?.grade_spec }}</strong> → <code class="font-mono">{{ deleteTarget?.sku_code }}</code>?
      </p>
      <FormInput
        v-model="deletePassword"
        variant="admin"
        label="Password confirmation"
        type="password"
        required
        autocomplete="current-password"
      />
    </SimpleFormModal>
  </div>
</template>

<script setup>
import { onMounted, ref, watch } from 'vue';
import api from '../../services/api';
import CrudTable from '../../components/admin/CrudTable.vue';
import SimpleFormModal from '../../components/admin/SimpleFormModal.vue';
import FormInput from '../../components/ui/FormInput.vue';
import { useToast } from '../../composables/useToast';
import { useConfirm } from '../../composables/useConfirm';

const loading = ref(true);
const auditLoading = ref(false);
const saving = ref(false);
const successMessage = ref('');
const grades = ref([]);
const audits = ref([]);
const activeTab = ref('grades');

const showFormModal = ref(false);
const showDeleteModal = ref(false);
const formMode = ref('create');
const editingId = ref(null);
const deleteTarget = ref(null);
const deletePassword = ref('');

const form = ref({
  grade_spec: '',
  sku_code: '',
  password_confirmation: '',
});

const tabs = [
  { id: 'grades', label: 'Registered grades' },
  { id: 'audit', label: 'Audit log' },
];

const gradeColumns = [
  { key: 'grade_spec', label: 'Grade (product field)' },
  { key: 'sku_code', label: 'SKU suffix' },
  { key: 'actions', label: 'Actions' },
];

async function fetchConfig() {
  loading.value = true;
  try {
    const { data } = await api.get('/api/v1/admin/settings/ffl-sku');
    grades.value = data?.data?.grades ?? [];
  } catch (e) {
    useToast().error(e.response?.data?.message || 'Failed to load grades.');
  } finally {
    loading.value = false;
  }
}

async function fetchAudits() {
  auditLoading.value = true;
  try {
    const { data } = await api.get('/api/v1/admin/settings/ffl-sku/audits');
    audits.value = data?.data ?? [];
  } catch {
    audits.value = [];
  } finally {
    auditLoading.value = false;
  }
}

watch(activeTab, (tab) => {
  if (tab === 'audit') fetchAudits();
});

function openCreate() {
  formMode.value = 'create';
  editingId.value = null;
  form.value = { grade_spec: '', sku_code: '', password_confirmation: '' };
  showFormModal.value = true;
}

function openEdit(item) {
  formMode.value = 'edit';
  editingId.value = item.id;
  form.value = {
    grade_spec: item.grade_spec,
    sku_code: item.sku_code,
    password_confirmation: '',
  };
  showFormModal.value = true;
}

function closeFormModal() {
  showFormModal.value = false;
  form.value.password_confirmation = '';
}

async function confirmDelete(item) {
  const ok = await useConfirm().show({
    title: 'Delete grade',
    message: `Remove "${item.grade_spec}"?`,
    confirmLabel: 'Continue',
    variant: 'danger',
  });
  if (!ok) return;
  deleteTarget.value = item;
  deletePassword.value = '';
  showDeleteModal.value = true;
}

async function submitForm() {
  saving.value = true;
  try {
    const body = {
      grade_spec: form.value.grade_spec.trim(),
      sku_code: form.value.sku_code.trim(),
      password_confirmation: form.value.password_confirmation,
    };
    if (formMode.value === 'create') {
      await api.post('/api/v1/admin/settings/ffl-sku/grades', body);
    } else {
      await api.put(`/api/v1/admin/settings/ffl-sku/grades/${editingId.value}`, body);
    }
    successMessage.value = 'Grade saved.';
    closeFormModal();
    await fetchConfig();
    if (activeTab.value === 'audit') await fetchAudits();
  } catch (e) {
    useToast().error(e.response?.data?.message || JSON.stringify(e.response?.data?.errors) || 'Save failed.');
  } finally {
    saving.value = false;
  }
}

async function submitDelete() {
  saving.value = true;
  try {
    await api.delete(`/api/v1/admin/settings/ffl-sku/grades/${deleteTarget.value.id}`, {
      data: { password_confirmation: deletePassword.value },
    });
    successMessage.value = 'Grade deleted.';
    showDeleteModal.value = false;
    await fetchConfig();
    if (activeTab.value === 'audit') await fetchAudits();
  } catch (e) {
    useToast().error(e.response?.data?.message || 'Delete failed.');
  } finally {
    saving.value = false;
  }
}

function formatDate(iso) {
  return iso ? new Date(iso).toLocaleString() : '—';
}

function formatAuditChange(entry) {
  if (entry.action === 'created') return JSON.stringify(entry.after);
  if (entry.action === 'deleted') return JSON.stringify(entry.before);
  return `${JSON.stringify(entry.before)} → ${JSON.stringify(entry.after)}`;
}

onMounted(fetchConfig);
</script>
