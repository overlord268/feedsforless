<template>
  <div class="space-y-8 animate-in fade-in duration-500">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
      <div>
        <h1 class="text-3xl font-bold tracking-tight text-slate-900">Product Catalog</h1>
        <p class="text-slate-500 mt-1">Browse our available ingredients and request quotes.</p>
      </div>
      <div class="flex items-center gap-3">
        <div class="relative">
          <input
            v-model="searchQuery"
            type="text"
            placeholder="Search products..."
            class="w-64 pl-10 pr-4 py-2.5 border border-slate-200 rounded-xl bg-white text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500 transition-all shadow-sm"
          />
          <svg class="w-4 h-4 text-slate-400 absolute left-3.5 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
          </svg>
        </div>
      </div>
    </div>
    <div v-if="allCategories.length > 0" class="flex flex-wrap gap-2">
      <button
        @click="selectedCategory = null"
        class="px-4 py-2 rounded-full text-sm font-medium transition-all duration-200 border"
        :class="selectedCategory === null
          ? 'bg-blue-600 text-white border-blue-600 shadow-md shadow-blue-500/20'
          : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50 hover:border-slate-300'"
      >
        All Products
      </button>
      <button
        v-for="cat in allCategories"
        :key="cat.id"
        @click="selectedCategory = cat.id"
        class="px-4 py-2 rounded-full text-sm font-medium transition-all duration-200 border"
        :class="selectedCategory === cat.id
          ? 'bg-blue-600 text-white border-blue-600 shadow-md shadow-blue-500/20'
          : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50 hover:border-slate-300'"
      >
        {{ cat.label }}
      </button>
    </div>
    <div v-if="loading" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      <div v-for="i in 6" :key="i" class="bg-white rounded-2xl border border-slate-200 p-6 animate-pulse">
        <div class="h-4 bg-slate-200 rounded w-1/3 mb-4"></div>
        <div class="h-6 bg-slate-200 rounded w-3/4 mb-3"></div>
        <div class="h-4 bg-slate-200 rounded w-full mb-2"></div>
        <div class="h-4 bg-slate-200 rounded w-2/3 mb-6"></div>
        <div class="flex gap-2">
          <div class="h-6 bg-slate-100 rounded-full w-16"></div>
          <div class="h-6 bg-slate-100 rounded-full w-20"></div>
        </div>
      </div>
    </div>

    <div v-else-if="filteredProducts.length === 0" class="text-center py-20">
      <div class="w-16 h-16 bg-slate-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
        <svg class="w-8 h-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
        </svg>
      </div>
      <h3 class="text-lg font-semibold text-slate-700 mb-1">No products found</h3>
      <p class="text-sm text-slate-500">Try adjusting your search or filter criteria.</p>
    </div>

    <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      <div
        v-for="product in filteredProducts"
        :key="product.id"
        class="bg-white rounded-2xl border border-slate-200 shadow-sm hover:shadow-lg hover:border-slate-300 transition-all duration-300 flex flex-col group overflow-hidden"
      >
        <!-- Card Header -->
        <div class="p-6 pb-4 flex-1">
          <div class="flex items-start justify-between mb-3">
            <span class="text-xs font-mono text-slate-400 tracking-wider">{{ product.sku }}</span>
            <span
              class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold"
              :class="stockStatusClass(product.stock_status)"
            >
              <span class="w-1.5 h-1.5 rounded-full mr-1.5" :class="stockDotClass(product.stock_status)"></span>
              {{ formatStockStatus(product.stock_status) }}
            </span>
          </div>

          <h3 class="text-lg font-bold text-slate-900 mb-1 group-hover:text-blue-600 transition-colors leading-snug">
            {{ product.name }}
          </h3>

          <p v-if="product.grade" class="text-sm text-blue-600 font-medium mb-3">
            Grade: {{ product.grade }}
          </p>

          <p class="text-sm text-slate-500 leading-relaxed line-clamp-3 mb-4">
            {{ product.description || 'No description available.' }}
          </p>

          <div v-if="product.categories && product.categories.length > 0" class="flex flex-wrap gap-1.5">
            <span
              v-for="cat in product.categories"
              :key="cat.id"
              class="inline-flex items-center px-2.5 py-1 bg-slate-100 text-slate-600 rounded-lg text-xs font-medium"
            >
              {{ cat.label }}
            </span>
          </div>         
          <div v-if="product.packaging_options && product.packaging_options.length > 0" class="mb-4">
            <label class="block text-xs font-medium text-slate-500 uppercase tracking-wider mb-1.5">Select Packaging</label>
            <select 
              v-model="product.selectedPackagingId"
              class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500 transition-all font-medium text-slate-700"
            >
              <option v-for="pack in product.packaging_options" :key="pack.packaging_type_id" :value="pack.packaging_type_id">
                {{ pack.type_name || 'Standard Packaging' }} 
              </option>
            </select>
          </div>

        </div>

        <div class="border-t border-slate-100 px-6 py-4 bg-slate-50/50 flex flex-col gap-3">
          <div class="flex items-center justify-between">
            <div class="text-sm text-slate-500">
              <span v-if="product.availability" class="flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                {{ product.availability }}
              </span>
            </div>
            
            <div class="flex items-center gap-2">
              <input 
                type="number" 
                v-model.number="product.qtyToAdd"
                min="1"
                class="w-16 px-2 py-2 border border-slate-200 rounded-lg text-sm text-center focus:outline-none focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500 transition-all font-medium text-slate-700"
              />
              <button 
                @click="addToQuote(product)"
                :disabled="product.adding || (!product.selectedPackagingId && product.packaging_options?.length > 0)"
                class="px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors shadow-sm shadow-blue-500/20 disabled:opacity-70 disabled:cursor-not-allowed flex items-center justify-center min-w-[124px]"
              >
                <svg v-if="product.adding" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                <span v-else>Add to Quote</span>
              </button>
            </div>
          </div>
          <p v-if="!product.selectedPackagingId && product.packaging_options?.length > 0" class="text-xs text-red-500 text-right">
            Please select a packaging type.
          </p>
        </div>
      </div>
    </div>

    <!-- Pagination Info -->
    <div v-if="!loading && filteredProducts.length > 0" class="text-center text-sm text-slate-400 pt-2">
      Showing {{ filteredProducts.length }} product{{ filteredProducts.length !== 1 ? 's' : '' }}
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import api from '../../services/api';

const products = ref([]);
const loading = ref(true);
const searchQuery = ref('');
const selectedCategory = ref(null);

// Extract unique categories from products
const allCategories = computed(() => {
  const map = new Map();
  products.value.forEach(p => {
    (p.categories || []).forEach(c => {
      if (!map.has(c.id)) map.set(c.id, c);
    });
  });
  return Array.from(map.values()).sort((a, b) => a.label.localeCompare(b.label));
});

const filteredProducts = computed(() => {
  let list = products.value;

  if (selectedCategory.value !== null) {
    list = list.filter(p =>
      (p.categories || []).some(c => c.id === selectedCategory.value)
    );
  }

  if (searchQuery.value.trim()) {
    const q = searchQuery.value.toLowerCase();
    list = list.filter(p =>
      p.name?.toLowerCase().includes(q) ||
      p.sku?.toLowerCase().includes(q) ||
      p.description?.toLowerCase().includes(q) ||
      p.grade?.toLowerCase().includes(q)
    );
  }

  return list;
});

const formatStockStatus = (status) => {
  if (!status) return 'Unknown';
  return status.replace(/_/g, ' ').replace(/\b\w/g, c => c.toUpperCase());
};

const stockStatusClass = (status) => {
  switch (status) {
    case 'in_stock': return 'bg-emerald-50 text-emerald-700';
    case 'low_stock': return 'bg-amber-50 text-amber-700';
    case 'out_of_stock': return 'bg-red-50 text-red-700';
    default: return 'bg-slate-100 text-slate-600';
  }
};

const stockDotClass = (status) => {
  switch (status) {
    case 'in_stock': return 'bg-emerald-500';
    case 'low_stock': return 'bg-amber-500';
    case 'out_of_stock': return 'bg-red-500';
    default: return 'bg-slate-400';
  }
};

const fetchProducts = async () => {
  loading.value = true;
  try {
    const response = await api.get('/api/v1/catalog/products');
    const items = response.data.data || response.data;
    
    // Initialize component-level state for ordering
    products.value = items.map(p => {
      // Auto-select first packaging option if available
      const defaultPackagingId = p.packaging_options?.length > 0 
        ? p.packaging_options[0].packaging_type_id 
        : null;

      return {
        ...p,
        qtyToAdd: 1,
        adding: false,
        selectedPackagingId: defaultPackagingId
      };
    });
  } catch (error) {
    console.error('Error fetching products:', error);
  } finally {
    loading.value = false;
  }
};

const addToQuote = async (product) => {
  if (!product.qtyToAdd || product.qtyToAdd < 1) return;
  
  if (product.packaging_options?.length > 0 && !product.selectedPackagingId) {
    alert("Please select a packaging type first.");
    return;
  }

  product.adding = true;
  try {
    await api.post('/api/v1/rfq-list/items', {
      product_id: product.id,
      quantity: product.qtyToAdd,
      packaging_type_id: product.selectedPackagingId || 1
    });
    alert(`${product.qtyToAdd} x ${product.name} added to your quote request!`);
    product.qtyToAdd = 1;
  } catch (error) {
    console.error('Failed to add to quote:', error);
    if (error.response?.data?.message) {
        alert(`Error: ${error.response.data.message}`);
    } else {
        alert('Failed to add product to quote request.');
    }
  } finally {
    product.adding = false;
  }
};

onMounted(() => {
  fetchProducts();
});
</script>

<style scoped>
.line-clamp-3 {
  display: -webkit-box;
  -webkit-line-clamp: 3;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>