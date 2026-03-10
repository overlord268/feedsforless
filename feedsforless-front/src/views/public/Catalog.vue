<template>
  <div class="flex flex-col md:flex-row w-full min-h-screen bg-white">
    <aside class="w-full md:w-60 shrink-0 border-r border-slate-200 bg-slate-50 min-h-full flex flex-col pt-6 z-10">
      <div class="px-0 mb-8">
        <h3 class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3 px-6">Categories</h3>
        <nav class="flex flex-col space-y-0.5">
          <button
            @click="selectCategory(null)"
            class="text-left px-6 py-2 text-sm font-bold transition-colors border-l-4"
            :class="currentView === 'grid' 
              ? 'bg-[#2962ff] text-white border-blue-700' 
              : 'text-slate-600 border-transparent hover:bg-slate-200 hover:text-slate-900'"
          >
            All Commodities
          </button>
          <button
            v-for="cat in allCategories"
            :key="cat.id"
            @click="selectCategory(cat)"
            class="text-left px-6 py-2 text-sm font-medium transition-colors border-l-4"
            :class="currentView === 'list' && selectedCategory && selectedCategory.id === cat.id
              ? 'bg-[#2962ff] text-white border-blue-700 font-bold' 
              : 'text-slate-600 border-transparent hover:bg-slate-200 hover:text-slate-900'"
          >
            {{ cat.label }}
          </button>
        </nav>
      </div>
      <div class="px-6 mb-8 mt-4 border-t border-slate-200 pt-6">
        <h3 class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3">Resources</h3>
        <nav class="flex flex-col space-y-3 text-sm font-bold text-[#2962ff]">
          <a href="#" class="hover:underline">Logistics Map</a>
          <a href="#" class="hover:underline">Bulk Volume Tiers</a>
          <a href="#" class="hover:underline">Tax Exemption Forms</a>
          <a href="#" class="hover:underline">Supplier Compliance</a>
        </nav>
      </div>
    </aside>

    <main class="flex-1 w-full bg-white relative p-6 lg:p-10">
      <div v-if="currentView === 'grid'" class="animate-in fade-in duration-300">
        <h1 class="text-3xl font-black italic text-[#003366] uppercase mb-1">Industrial Feed Commodity Catalog</h1>
        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-8 border-b-2 border-slate-900 pb-4">Direct Supply Chain Portal | North American Distribution</p>
        
        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 border-l border-t border-slate-200">
          <div 
            v-for="cat in allCategories" 
            :key="cat.id"
            @click="selectCategory(cat)"
            class="border-r border-b border-slate-200 p-6 flex flex-col h-56 hover:bg-slate-50 cursor-pointer group transition-colors"
          >
            <h2 class="text-[#2962ff] font-black uppercase tracking-tight mb-4 text-base">{{ cat.label }}</h2>
            <div class="flex-1 overflow-hidden">
              <ul class="text-xs text-slate-600 space-y-2">
                <li v-for="product in getCategoryProducts(cat.id).slice(0, 3)" :key="product.id" class="truncate">
                  {{ product.name }}
                </li>
              </ul>
            </div>
            <div class="mt-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest group-hover:text-[#2962ff] transition-colors">
              View Full Directory &rarr;
            </div>
          </div>
        </div>
      </div>

      <div v-else-if="currentView === 'list'" class="animate-in fade-in duration-300">
        <div class="flex items-end justify-between border-b-2 border-slate-200 pb-4 mb-4">
          <h1 class="text-2xl font-black uppercase text-slate-900 tracking-tight">{{ selectedCategory ? selectedCategory.label : 'Search Results' }}</h1>
          <span class="text-sm font-medium text-slate-500">{{ filteredProducts.length }} Products Available</span>
        </div>

        <div class="overflow-x-auto min-h-[500px]">
          <table class="w-full text-left text-sm whitespace-nowrap lg:whitespace-normal">
            <thead>
              <tr class="text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-200">
                <th class="py-3 px-4 min-w-[300px]">Product Identity</th>
                <th class="py-3 px-4 w-[120px]">Grade / Spec</th>
                <th class="py-3 px-4 w-1/4">Packaging</th>
                <th class="py-3 px-4 w-[160px] text-right">Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr 
                v-for="product in filteredProducts" 
                :key="product.id" 
                class="border-b border-slate-200 hover:bg-[#f5f8ff] group transition-colors"
              >
                <td class="py-4 px-4 align-top">
                  <div class="font-bold text-[#2962ff] text-base mb-0.5 leading-tight cursor-pointer">{{ product.name }}</div>
                  <div class="text-[11px] text-slate-500 max-w-sm leading-relaxed truncate">{{ product.description || 'Standard specification applicable for general use.' }}</div>
                </td>
                <td class="py-4 px-4 align-top font-bold text-slate-700 text-xs">
                  {{ product.grade || '--' }}
                </td>
                <td class="py-4 px-4 align-top text-slate-600 text-xs">
                  <span v-if="product.packaging_options && product.packaging_options.length > 0">
                    {{ product.packaging_options.map(p => p.type_name).join(', ') }}
                  </span>
                  <span v-else>Bags, Totes, Bulk Truck</span>
                </td>
                <td class="py-4 px-4 align-top text-right min-w-[200px]">
                  <div class="flex items-center justify-end gap-2 flex-wrap">
                    <button v-if="product.tds_document_path" @click.prevent="previewDocument(product.id, 'tds')" class="bg-slate-200 text-slate-600 hover:bg-slate-300 text-[9px] font-bold px-2 py-1.5 uppercase tracking-wider rounded shrink-0">TDS</button>
                    <button v-if="product.sds_document_path" @click.prevent="previewDocument(product.id, 'sds')" class="bg-slate-200 text-slate-600 hover:bg-slate-300 text-[9px] font-bold px-2 py-1.5 uppercase tracking-wider rounded shrink-0">SDS</button>
                    <button v-if="product.coa_document_path" @click.prevent="previewDocument(product.id, 'coa')" class="bg-slate-200 text-slate-600 hover:bg-slate-300 text-[9px] font-bold px-2 py-1.5 uppercase tracking-wider rounded shrink-0">ODA</button>
                    <router-link
                      :to="requestQuoteRoute(product)"
                      class="inline-block bg-[#2962ff] text-white font-bold text-[11px] uppercase tracking-wider px-4 py-2 rounded opacity-0 group-hover:opacity-100 transition-opacity hover:bg-blue-800 shrink-0"
                    >
                      Request Quote
                    </router-link>
                  </div>
                </td>
              </tr>
              <tr v-if="filteredProducts.length === 0 && !loading">
                <td colspan="4" class="py-8 text-center text-slate-500 font-medium">No products match the selected criteria.</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

    </main>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import api from '../../services/api';

const props = defineProps({
  searchQuery: {
    type: String,
    default: ''
  }
});

const router = useRouter();
const route = useRoute();
const products = ref([]);
const loading = ref(true);
const selectedCategory = ref(null);
const currentView = ref('grid');

const apiBaseUrl = api.defaults.baseURL || '';

function requestQuoteRoute(product) {
  const inApp = route.path.startsWith('/app');
  return { name: inApp ? 'AppRequestQuote' : 'RequestQuote', query: { productId: product.id } };
}

function previewDocument(productId, type) {
  const url = `${apiBaseUrl}/api/v1/catalog/products/${productId}/documents/${type}`;
  window.open(url, '_blank', 'noopener,noreferrer');
}

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
      (p.categories || []).some(c => c.id === selectedCategory.value.id)
    );
  }

  if (props.searchQuery && props.searchQuery.trim()) {
    const q = props.searchQuery.toLowerCase();
    list = list.filter(p =>
      p.name?.toLowerCase().includes(q) ||
      p.sku?.toLowerCase().includes(q) ||
      p.description?.toLowerCase().includes(q) ||
      p.grade?.toLowerCase().includes(q)
    );
  }

  return list;
});

const getCategoryProducts = (catId) => {
  return products.value.filter(p => (p.categories || []).some(c => c.id === catId));
};

const selectCategory = (cat) => {
  if (cat === null) {
    selectedCategory.value = null;
    currentView.value = 'grid';
  } else {
    selectedCategory.value = cat;
    currentView.value = 'list';
  }
};

const fetchProducts = async () => {
  loading.value = true;
  try {
    const response = await api.get('/api/v1/catalog/products');
    const items = response.data.data || response.data;
    products.value = items.map(p => {
      const defaultPackagingId = p.packaging_options?.length > 0 
        ? p.packaging_options[0].packaging_type_id 
        : null;

      return {
        ...p,
        qtyToAdd: 24,
        adding: false,
        selectedPackagingId: defaultPackagingId
      };
    });
  } catch (error) {
    console.error('Error fetching', error);
  } finally {
    loading.value = false;
  }
};

onMounted(() => {
  fetchProducts();
});

// Open in list view with category when coming from Home with ?categoryId=
watch(
  () => [route.query.categoryId, products.value.length],
  ([categoryId]) => {
    const id = categoryId ? Number(categoryId) : null;
    if (!id || !products.value.length) return;
    const cat = allCategories.value.find(c => c.id === id);
    if (cat) selectCategory(cat);
  },
  { immediate: true }
);

// When search has text, show list view with filtered results
watch(
  () => props.searchQuery,
  (q) => {
    const hasSearch = q && String(q).trim().length > 0;
    if (hasSearch) {
      selectedCategory.value = null;
      currentView.value = 'list';
    } else if (currentView.value === 'list' && !selectedCategory.value) {
      currentView.value = 'grid';
    }
  }
);
</script>