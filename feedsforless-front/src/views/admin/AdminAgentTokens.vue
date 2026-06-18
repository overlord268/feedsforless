<template>
  <div class="space-y-5">
    <div
      v-if="successMessage"
      class="p-3 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm font-medium"
    >
      {{ successMessage }}
    </div>

    <div
      v-if="plainTokenDisplay"
      class="p-4 rounded-xl bg-amber-50 border border-amber-200 space-y-3"
    >
      <p class="text-sm font-semibold text-amber-900">
        Copy this token now. It will not be shown again.
      </p>
      <div class="flex flex-col sm:flex-row gap-2">
        <code class="flex-1 p-3 rounded-lg bg-white border border-amber-200 text-xs text-slate-800 break-all font-mono">
          {{ plainTokenDisplay }}
        </code>
        <button
          type="button"
          class="px-4 py-2 rounded-xl bg-amber-600 text-white text-sm font-medium hover:bg-amber-700 shrink-0"
          @click="copyPlainToken"
        >
          Copy
        </button>
      </div>
      <button
        type="button"
        class="text-sm text-amber-800 underline"
        @click="plainTokenDisplay = ''"
      >
        Dismiss
      </button>
    </div>

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
      <div>
        <h1 class="text-2xl font-bold text-slate-900 tracking-tight">AI Agent API Tokens</h1>
        <p class="text-slate-500 mt-0.5">
          Bearer tokens for external AI agents (e.g. Claude). Manage create, rotate, and revoke.
        </p>
      </div>
      <button
        type="button"
        class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-emerald-600 text-white font-medium hover:bg-emerald-700 transition"
        @click="openCreate"
      >
        + Generate token
      </button>
    </div>

    <CrudTable :columns="columns" :items="items" :loading="loading" title="AI agent tokens" search-placeholder="Search tokens…" item-label="tokens">
      <template #row="{ item }">
        <td class="px-6 py-4 text-slate-800 font-medium">{{ item.name }}</td>
        <td class="px-6 py-4">
          <span
            :class="item.is_active
              ? 'inline-flex px-2 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800'
              : 'inline-flex px-2 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-600'"
          >
            {{ item.is_active ? 'Active' : 'Revoked' }}
          </span>
        </td>
        <td class="px-6 py-4 text-slate-500 text-sm">{{ formatDate(item.last_used_at) }}</td>
        <td class="px-6 py-4 text-slate-500 text-sm">{{ formatDate(item.created_at) }}</td>
        <td class="px-6 py-4">
          <div v-if="item.is_active" class="flex items-center gap-1">
            <button
              type="button"
              class="px-2 py-1 text-xs font-medium text-blue-700 hover:bg-blue-50 rounded-lg"
              title="Rotate"
              @click="confirmRotate(item)"
            >
              Rotate
            </button>
            <button
              type="button"
              class="px-2 py-1 text-xs font-medium text-red-700 hover:bg-red-50 rounded-lg"
              title="Revoke"
              @click="confirmRevoke(item)"
            >
              Revoke
            </button>
          </div>
          <span v-else class="text-slate-400 text-xs">—</span>
        </td>
      </template>
    </CrudTable>

    <SimpleFormModal
      :show="showModal"
      title="Generate AI agent token"
      :saving="saving"
      @close="showModal = false"
      @submit="submitCreate"
    >
      <FormInput
        v-model="form.name"
        variant="admin"
        label="Token name"
        type="text"
        required
        placeholder="e.g. Claude Production"
      />
    </SimpleFormModal>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue';
import api from '../../services/api';
import { useConfirm } from '../../composables/useConfirm';
import CrudTable from '../../components/admin/CrudTable.vue';
import SimpleFormModal from '../../components/admin/SimpleFormModal.vue';
import FormInput from '../../components/ui/FormInput.vue';

const columns = [
  { key: 'name', label: 'Name' },
  { key: 'status', label: 'Status', sortValue: (i) => (i.is_active ? 'active' : 'revoked') },
  { key: 'last_used', label: 'Last used', sortValue: (i) => i.last_used_at || '' },
  { key: 'created', label: 'Created', sortValue: (i) => i.created_at || '' },
  { key: 'actions', label: 'Actions', thClass: 'px-4 py-2.5 text-[11px] font-semibold text-slate-500 uppercase tracking-wider w-32' },
];

const items = ref([]);
const loading = ref(true);
const showModal = ref(false);
const saving = ref(false);
const successMessage = ref('');
const plainTokenDisplay = ref('');
const form = reactive({ name: '' });

function setSuccess(msg) {
  successMessage.value = msg;
  setTimeout(() => { successMessage.value = ''; }, 4000);
}

function formatDate(iso) {
  if (!iso) return '—';
  try {
    return new Date(iso).toLocaleString();
  } catch {
    return iso;
  }
}

async function fetchItems() {
  loading.value = true;
  try {
    const { data } = await api.get('/api/v1/admin/agent-api-tokens');
    items.value = data?.data ?? [];
  } catch (e) {
    console.error(e);
    items.value = [];
  } finally {
    loading.value = false;
  }
}

function openCreate() {
  form.name = '';
  showModal.value = true;
}

async function submitCreate() {
  saving.value = true;
  try {
    const { data } = await api.post('/api/v1/admin/agent-api-tokens', { name: form.name });
    plainTokenDisplay.value = data.plain_token ?? '';
    showModal.value = false;
    setSuccess(data.message || 'Token created.');
    await fetchItems();
  } catch (e) {
    console.error(e);
  } finally {
    saving.value = false;
  }
}

async function confirmRotate(item) {
  const ok = await useConfirm().show({
    title: 'Rotate token',
    message: `Rotate "${item.name}"? The current token will stop working immediately.`,
    confirmLabel: 'Rotate',
    cancelLabel: 'Cancel',
    variant: 'danger',
  });
  if (!ok) return;
  try {
    const { data } = await api.post(`/api/v1/admin/agent-api-tokens/${item.id}/rotate`);
    plainTokenDisplay.value = data.plain_token ?? '';
    setSuccess(data.message || 'Token rotated.');
    await fetchItems();
  } catch (e) {
    console.error(e);
  }
}

async function confirmRevoke(item) {
  const ok = await useConfirm().show({
    title: 'Revoke token',
    message: `Revoke "${item.name}"? The agent will no longer be able to authenticate.`,
    confirmLabel: 'Revoke',
    cancelLabel: 'Cancel',
    variant: 'danger',
  });
  if (!ok) return;
  try {
    await api.delete(`/api/v1/admin/agent-api-tokens/${item.id}`);
    setSuccess('Token revoked.');
    await fetchItems();
  } catch (e) {
    console.error(e);
  }
}

async function copyPlainToken() {
  if (!plainTokenDisplay.value) return;
  try {
    await navigator.clipboard.writeText(plainTokenDisplay.value);
    setSuccess('Token copied to clipboard.');
  } catch {
    setSuccess('Copy failed — select the token manually.');
  }
}

onMounted(fetchItems);
</script>
