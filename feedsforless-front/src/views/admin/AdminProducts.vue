<template>
  <div class="space-y-5">
    <div v-if="successMessage" class="p-3 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm font-medium">{{ successMessage }}</div>
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
      <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">Products</h1>
      <div class="flex flex-wrap gap-2 shrink-0">
        <router-link
          :to="{ name: 'AdminProductImport' }"
          class="inline-flex items-center justify-center px-4 py-3 sm:px-5 sm:py-2.5 border border-slate-200 text-slate-700 font-medium rounded-xl hover:bg-slate-50 transition-colors touch-manipulation"
        >
          Import
        </router-link>
        <router-link
          :to="{ name: 'AdminProductCreate' }"
          class="inline-flex items-center justify-center px-4 py-3 sm:px-5 sm:py-2.5 bg-emerald-600 text-white font-medium rounded-xl hover:bg-emerald-700 transition-colors shadow-sm touch-manipulation"
        >
          + Add Product
        </router-link>
      </div>
    </div>

    <div class="flex flex-wrap gap-2 items-center">
      <div class="flex gap-1 p-1 bg-slate-200/80 rounded-xl w-fit">
        <button
          v-for="tab in statusTabs"
          :key="tab.id"
          type="button"
          :class="['px-4 py-2 rounded-lg text-sm font-medium transition-colors', statusFilter === tab.id ? 'bg-white text-slate-800 shadow-sm' : 'text-slate-600 hover:text-slate-800']"
          @click="statusFilter = tab.id"
        >
          {{ tab.label }}
        </button>
      </div>
    </div>

    <div
      v-if="selectedIds.size > 0"
      class="flex flex-wrap items-center gap-2 p-3 rounded-xl bg-slate-50 border border-slate-200"
    >
      <span class="text-sm font-medium text-slate-700">{{ selectedIds.size }} selected</span>
      <button type="button" class="px-3 py-1.5 text-sm rounded-lg border border-slate-200 bg-white hover:bg-slate-50" @click="runBulk('restore')">Restore</button>
      <button type="button" class="px-3 py-1.5 text-sm rounded-lg border border-slate-200 bg-white hover:bg-slate-50" @click="runBulk('draft')">Set draft</button>
      <button type="button" class="px-3 py-1.5 text-sm rounded-lg border border-emerald-200 text-emerald-700 bg-emerald-50 hover:bg-emerald-100" @click="runBulk('publish')">Publish</button>
      <button type="button" class="px-3 py-1.5 text-sm rounded-lg border border-amber-200 text-amber-800 bg-amber-50 hover:bg-amber-100" @click="runBulk('delete')">Move to trash</button>
      <button type="button" class="px-3 py-1.5 text-sm rounded-lg border border-red-200 text-red-700 bg-red-50 hover:bg-red-100" @click="runBulk('force_delete')">Delete permanently</button>
      <button type="button" class="px-3 py-1.5 text-sm text-slate-500 hover:text-slate-800" @click="clearSelection">Clear</button>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-card overflow-hidden min-h-[420px] flex flex-col">
      <div class="overflow-x-auto table-scroll flex-1 min-h-0">
        <table class="w-full text-sm min-w-[700px]">
          <thead class="bg-slate-50/80 border-b border-slate-200">
            <tr class="text-left">
              <th class="px-4 py-3.5 w-10">
                <input
                  type="checkbox"
                  class="rounded border-slate-300"
                  :checked="allVisibleSelected"
                  :indeterminate.prop="someVisibleSelected && !allVisibleSelected"
                  @change="toggleSelectAll"
                >
              </th>
              <th class="px-4 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider">SKU</th>
              <th class="px-4 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider">Name</th>
              <th class="px-4 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider">Price</th>
              <th class="px-4 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider">Status</th>
              <th class="px-4 py-3.5 w-28 text-right text-xs font-semibold text-slate-500 uppercase tracking-wider">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            <tr
              v-for="product in filteredProducts"
              :key="product.id"
              :class="product.deleted_at ? 'bg-red-50/40' : 'hover:bg-slate-50/70'"
              class="transition-colors"
            >
              <td class="px-4 py-4">
                <input
                  type="checkbox"
                  class="rounded border-slate-300"
                  :checked="selectedIds.has(product.id)"
                  @change="toggleSelect(product.id)"
                >
              </td>
              <td class="px-4 py-4 font-mono text-slate-700">{{ product.sku || '—' }}</td>
              <td class="px-4 py-4 text-slate-800">{{ product.name }}</td>
              <td class="px-4 py-4 text-slate-600">
                {{ product.packaging_options?.length ? formatPrice(product.packaging_options[0].base_price_per_unit) : '—' }}
              </td>
              <td class="px-4 py-4">
                <span v-if="product.deleted_at" class="inline-flex px-2.5 py-1 rounded-md text-xs font-medium bg-red-100 text-red-800">Deleted</span>
                <span
                  v-else
                  :class="[
                    'inline-flex px-2.5 py-1 rounded-md text-xs font-medium',
                    product.status === 'published' ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-600'
                  ]"
                >
                  {{ product.status === 'published' ? 'Published' : 'Draft' }}
                </span>
              </td>
              <td class="px-4 py-4 text-right">
                <div class="flex items-center justify-end gap-1">
                  <button
                    v-if="product.deleted_at"
                    type="button"
                    class="p-2 text-slate-400 hover:text-emerald-600 hover:bg-emerald-50 rounded-lg transition-colors"
                    title="Restore"
                    @click="restoreProduct(product.id)"
                  >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                  </button>
                  <button
                    v-if="product.deleted_at"
                    type="button"
                    class="p-2 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors"
                    title="Delete permanently"
                    @click="confirmForceDelete(product)"
                  >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                  </button>
                  <router-link
                    v-if="!product.deleted_at"
                    :to="{ name: 'AdminProductEdit', params: { id: product.id } }"
                    class="p-2 text-slate-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-colors"
                    title="Edit"
                  >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                  </router-link>
                  <button
                    v-if="!product.deleted_at"
                    type="button"
                    class="p-2 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors"
                    title="Move to trash"
                    @click="confirmDelete(product)"
                  >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
      <p v-if="!loading && filteredProducts.length === 0" class="px-6 py-12 text-center text-slate-500">
        No products in this view.
        <router-link :to="{ name: 'AdminProductCreate' }" class="text-emerald-600 hover:underline">Create one</router-link>
        or
        <router-link :to="{ name: 'AdminProductImport' }" class="text-emerald-600 hover:underline">import</router-link>.
      </p>
      <div v-if="loading" class="px-6 py-12 text-center text-slate-500">Loading…</div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import api from '../../services/api';
import { useToast } from '../../composables/useToast';
import { useConfirm } from '../../composables/useConfirm';

const route = useRoute();
const router = useRouter();
const products = ref([]);
const loading = ref(true);
const statusFilter = ref('all');
const successMessage = ref('');
const selectedIds = ref(new Set());
const bulkRunning = ref(false);

const statusTabs = [
  { id: 'all', label: 'All' },
  { id: 'active', label: 'Active' },
  { id: 'deleted', label: 'Deleted' },
  { id: 'draft', label: 'Draft' },
  { id: 'published', label: 'Published' },
];

const filteredProducts = computed(() => {
  return products.value.filter((product) => {
    if (statusFilter.value === 'active') return !product.deleted_at;
    if (statusFilter.value === 'deleted') return !!product.deleted_at;
    if (statusFilter.value === 'draft') return !product.deleted_at && product.status !== 'published';
    if (statusFilter.value === 'published') return !product.deleted_at && product.status === 'published';
    return true;
  });
});

const allVisibleSelected = computed(() => {
  const visible = filteredProducts.value;
  return visible.length > 0 && visible.every((p) => selectedIds.value.has(p.id));
});

const someVisibleSelected = computed(() => filteredProducts.value.some((p) => selectedIds.value.has(p.id)));

function formatPrice(val) {
  if (val == null) return '—';
  return new Intl.NumberFormat('en', { style: 'decimal', minimumFractionDigits: 2 }).format(val);
}

function toggleSelect(id) {
  const next = new Set(selectedIds.value);
  if (next.has(id)) next.delete(id);
  else next.add(id);
  selectedIds.value = next;
}

function toggleSelectAll() {
  if (allVisibleSelected.value) {
    const next = new Set(selectedIds.value);
    filteredProducts.value.forEach((p) => next.delete(p.id));
    selectedIds.value = next;
  } else {
    const next = new Set(selectedIds.value);
    filteredProducts.value.forEach((p) => next.add(p.id));
    selectedIds.value = next;
  }
}

function clearSelection() {
  selectedIds.value = new Set();
}

const bulkLabels = {
  delete: 'move to trash',
  force_delete: 'permanently delete',
  restore: 'restore',
  publish: 'publish',
  draft: 'set as draft',
};

async function runBulk(action) {
  const ids = [...selectedIds.value];
  if (ids.length === 0) return;

  const label = bulkLabels[action] || action;
  const ok = await useConfirm().show({
    title: 'Bulk action',
    message: `${label.charAt(0).toUpperCase()}${label.slice(1)} ${ids.length} product(s)?`,
    confirmLabel: 'Continue',
    variant: action === 'force_delete' || action === 'delete' ? 'danger' : 'default',
  });
  if (!ok) return;

  bulkRunning.value = true;
  try {
    const { data } = await api.post('/api/v1/admin/products/bulk-action', {
      action,
      product_ids: ids,
    });
    const failed = data?.data?.failed ?? [];
    const succeeded = data?.data?.succeeded ?? 0;
    if (failed.length) {
      useToast().error(`${succeeded} ok, ${failed.length} failed. ${failed[0]?.message || ''}`);
    } else {
      useToast().success(data?.message || 'Bulk action completed.');
    }
    clearSelection();
    await fetchProducts();
  } catch (e) {
    useToast().error(e.response?.data?.message || 'Bulk action failed.');
  } finally {
    bulkRunning.value = false;
  }
}

async function confirmDelete(product) {
  const ok = await useConfirm().show({
    title: 'Move to trash',
    message: `Move "${product.name}" to trash?`,
    confirmLabel: 'Move to trash',
    variant: 'danger',
  });
  if (!ok) return;
  await deleteProduct(product.id);
}

async function confirmForceDelete(product) {
  const ok = await useConfirm().show({
    title: 'Delete permanently',
    message: `Permanently delete "${product.name}"? This cannot be undone and frees the slug for re-import.`,
    confirmLabel: 'Delete permanently',
    variant: 'danger',
  });
  if (!ok) return;
  try {
    await api.delete(`/api/v1/admin/products/${product.id}/force`);
    selectedIds.value.delete(product.id);
    selectedIds.value = new Set(selectedIds.value);
    await fetchProducts();
    useToast().success('Product permanently deleted.');
  } catch (e) {
    useToast().error(e.response?.data?.message || 'Could not delete product.');
  }
}

async function deleteProduct(id) {
  try {
    await api.delete(`/api/v1/admin/products/${id}`);
    await fetchProducts();
    useToast().success('Product moved to trash.');
  } catch (e) {
    useToast().error('Could not delete product.');
  }
}

async function restoreProduct(id) {
  try {
    await api.post(`/api/v1/admin/products/${id}/restore`);
    await fetchProducts();
    useToast().success('Product restored.');
  } catch (e) {
    useToast().error(e.response?.data?.message || 'Could not restore product.');
  }
}

async function fetchProducts() {
  loading.value = true;
  try {
    const { data } = await api.get('/api/v1/admin/products', { params: { per_page: 500 } });
    const raw = data?.data ?? data;
    products.value = Array.isArray(raw) ? raw : (raw?.data ?? []);
  } catch (e) {
    products.value = [];
    useToast().error(e.response?.data?.message || 'Could not load products.');
  } finally {
    loading.value = false;
  }
}

onMounted(() => {
  fetchProducts();
  if (route.query.saved === '1') {
    successMessage.value = 'Product saved successfully.';
    router.replace({ name: 'AdminProducts' });
    setTimeout(() => { successMessage.value = ''; }, 4000);
  }
});
</script>
