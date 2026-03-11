<template>
  <div class="space-y-5">
    <div v-if="successMessage" class="p-3 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm font-medium">{{ successMessage }}</div>
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
      <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">Products</h1>
      <router-link
        :to="{ name: 'AdminProductCreate' }"
        class="inline-flex items-center justify-center px-4 py-3 sm:px-5 sm:py-2.5 bg-emerald-600 text-white font-medium rounded-xl hover:bg-emerald-700 transition-colors shadow-sm shrink-0 touch-manipulation"
      >
        + Add Product
      </router-link>
    </div>

    <div class="flex gap-1 p-1 bg-slate-200/80 rounded-xl w-fit">
      <button
        type="button"
        :class="['px-4 py-2 rounded-lg text-sm font-medium transition-colors', filterTab === 'all' ? 'bg-white text-slate-800 shadow-sm' : 'text-slate-600 hover:text-slate-800']"
        @click="filterTab = 'all'"
      >
        All Products
      </button>
      <button
        type="button"
        :class="['px-4 py-2 rounded-lg text-sm font-medium transition-colors', filterTab === 'filters' ? 'bg-white text-slate-800 shadow-sm' : 'text-slate-600 hover:text-slate-800']"
        @click="filterTab = 'filters'"
      >
        Filters
      </button>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-card overflow-hidden min-h-[420px] flex flex-col">
      <div class="overflow-x-auto table-scroll flex-1 min-h-0">
        <table class="w-full text-sm min-w-[600px]">
          <thead class="bg-slate-50/80 border-b border-slate-200">
            <tr class="text-left">
              <th class="px-6 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider">SKU</th>
              <th class="px-6 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider">Name</th>
              <th class="px-6 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider">Price</th>
              <th class="px-6 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider">Status</th>
              <th class="px-6 py-3.5 w-24 text-right text-xs font-semibold text-slate-500 uppercase tracking-wider">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            <tr v-for="product in filteredProducts" :key="product.id" class="hover:bg-slate-50/70 transition-colors">
              <td class="px-6 py-4 font-mono text-slate-700">{{ product.sku }}</td>
              <td class="px-6 py-4 text-slate-800">{{ product.name }}</td>
              <td class="px-6 py-4 text-slate-600">
                {{ product.packaging_options?.length ? formatPrice(product.packaging_options[0].base_price_per_unit) : '—' }}
              </td>
              <td class="px-6 py-4">
                <span
                  :class="[
                    'inline-flex px-2.5 py-1 rounded-md text-xs font-medium',
                    product.status === 'published' ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-600'
                  ]"
                >
                  {{ product.status === 'published' ? 'Published' : 'Draft' }}
                </span>
              </td>
              <td class="px-6 py-4 text-right">
                <div class="flex items-center justify-end gap-1">
                  <router-link
                    :to="{ name: 'AdminProductEdit', params: { id: product.id } }"
                    class="p-2 text-slate-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-colors"
                    title="Edit"
                  >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                  </router-link>
                  <button
                    type="button"
                    @click="confirmDelete(product)"
                    class="p-2 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors"
                    title="Delete"
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
        No products. <router-link :to="{ name: 'AdminProductCreate' }" class="text-emerald-600 hover:underline">Create the first one</router-link>.
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
const filterTab = ref('all');
const successMessage = ref('');

const filteredProducts = computed(() => {
  if (filterTab.value === 'filters') return products.value;
  return products.value;
});

function formatPrice(val) {
  if (val == null) return '—';
  return new Intl.NumberFormat('en', { style: 'decimal', minimumFractionDigits: 2 }).format(val);
}

async function confirmDelete(product) {
  const ok = await useConfirm().show({ title: 'Delete product', message: `Delete product "${product.name}"?`, confirmLabel: 'Delete', cancelLabel: 'Cancel', variant: 'danger' });
  if (!ok) return;
  await deleteProduct(product.id);
}

async function deleteProduct(id) {
  try {
    await api.delete(`/api/v1/admin/products/${id}`);
    products.value = products.value.filter(p => p.id !== id);
  } catch (e) {
    console.error(e);
    useToast().error('Could not delete product.');
  }
}

async function fetchProducts() {
  loading.value = true;
  try {
    const { data } = await api.get('/api/v1/admin/products');
    const raw = data?.data ?? data;
    products.value = Array.isArray(raw) ? raw : (raw?.data ?? []);
  } catch (e) {
    console.error(e);
    products.value = [];
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
