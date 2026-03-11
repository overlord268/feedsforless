<template>
  <div class="flex-1 flex flex-col md:flex-row w-full h-full min-w-0 bg-white">
    <!-- Sidebar: Browse by Category -->
    <aside class="w-full md:w-60 shrink-0 border-r border-slate-200 bg-slate-50/80 min-h-full flex flex-col pt-6 pb-8">
      <h2 class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3 px-6">Browse by Category</h2>
      <nav class="flex flex-col space-y-0.5 px-3">
        <button
          @click="toggleCategory(null)"
          class="flex items-center w-full text-left px-3 py-2.5 text-sm font-bold rounded-lg transition-colors border-l-4"
          :class="!selectedCategory
            ? 'bg-[#2962ff] text-white border-[#2962ff]'
            : 'text-slate-600 border-transparent hover:bg-slate-200 hover:text-slate-900'"
        >
          All
        </button>
        <div v-for="category in categories" :key="category.id" class="mt-0.5">
          <button
            @click="toggleCategory(category.id)"
            class="flex items-center justify-between w-full text-left px-3 py-2.5 text-sm font-medium rounded-lg transition-colors border-l-4"
            :class="isCategoryActive(category.id)
              ? 'bg-[#2962ff] text-white border-[#2962ff] font-bold'
              : 'text-slate-600 border-transparent hover:bg-slate-200 hover:text-slate-900'"
          >
            {{ category.label }}
            <svg v-if="category.children && category.children.length > 0" class="w-4 h-4 text-slate-400 shrink-0" :class="{ 'rotate-90': expandedCategories.includes(category.id) }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
          </button>
          <div v-show="expandedCategories.includes(category.id) && category.children && category.children.length > 0" class="pl-4 mt-0.5 space-y-0.5">
            <button
              v-for="sub in category.children"
              :key="sub.id"
              @click="toggleCategory(sub.id)"
              class="block w-full text-left px-3 py-1.5 text-xs rounded-lg text-slate-500 hover:bg-slate-200 hover:text-slate-800 transition-colors"
              :class="{ 'text-[#2962ff] font-semibold': isCategoryActive(sub.id) }"
            >
              {{ sub.label }}
            </button>
          </div>
        </div>
      </nav>
    </aside>

    <!-- Main product area -->
    <main class="flex-1 min-w-0 p-6 lg:p-10">
      <div v-if="loading" class="flex items-center justify-center py-20 animate-in fade-in duration-200">
        <LoadingSpinner size="lg" label="Loading catalog…" />
      </div>

      <div v-else-if="filteredGroupedProducts.length === 0" class="text-center py-20 bg-slate-50 border border-slate-200 rounded-xl">
        <svg class="w-14 h-14 text-slate-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
        <h3 class="text-xl font-bold text-slate-800 mb-2">No products found</h3>
        <p class="text-sm text-slate-500 mb-6">Try clearing the category filter or adjusting your search.</p>
        <button v-if="selectedCategory || searchQuery" @click="resetFilters" class="px-5 py-2.5 bg-[#2962ff] text-white text-sm font-bold rounded-lg hover:bg-[#1a4fc4] transition-colors">
          Show all
        </button>
      </div>

      <div v-else class="space-y-10 pb-16">
        <section v-for="group in filteredGroupedProducts" :key="group.category.id" class="space-y-4">
          <div class="flex items-center gap-3 pb-2 border-b-2 border-slate-200">
            <div class="w-8 h-8 rounded-lg bg-[#f0f4ff] text-[#2962ff] flex items-center justify-center">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6z"/></svg>
            </div>
            <h2 class="text-lg font-black text-slate-800 tracking-tight">{{ group.category.label }}</h2>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5">
            <div
              v-for="product in group.products"
              :key="product.id"
              class="relative bg-white border border-slate-200 rounded-xl p-5 hover:shadow-lg hover:border-[#2962ff]/40 transition-all duration-200 group cursor-pointer overflow-hidden"
              @click="goToCatalog(product)"
            >
              <div class="flex items-start gap-3 mb-3">
                <div class="w-11 h-11 rounded-xl bg-slate-100 text-[#2962ff] flex items-center justify-center shrink-0 group-hover:bg-[#f0f4ff] transition-colors">
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                </div>
                <h3 class="text-sm font-bold text-[#003366] leading-tight line-clamp-2 group-hover:text-[#2962ff] transition-colors">
                  {{ product.name }}
                </h3>
              </div>
              <div class="text-[11px] text-slate-500 space-y-1 pl-0.5">
                <p v-if="product.sku" class="truncate font-mono text-slate-400">{{ product.sku }}</p>
                <p v-if="product.grade" class="truncate">Grade: {{ product.grade }}</p>
                <div class="flex items-center flex-wrap gap-x-2 gap-y-1 mt-2">
                  <span v-if="product.availability" class="text-slate-500">{{ product.availability }}</span>
                  <span v-if="product.stock_status" class="text-slate-500">{{ formatStockStatus(product.stock_status) }}</span>
                </div>
              </div>
              <!-- Request Quote: always visible on mobile (touch), hover-reveal on desktop -->
              <div class="mt-3 pt-3 border-t border-slate-100 md:absolute md:bottom-0 md:left-0 md:right-0 md:mt-0 md:pt-0 md:border-t-0 md:p-4 md:bg-gradient-to-t md:from-white md:to-white/95 md:opacity-0 md:group-hover:opacity-100 transition-opacity flex justify-end">
                <router-link
                  :to="{ name: 'RequestQuote', query: { productId: product.id } }"
                  class="inline-block min-h-[44px] min-w-[44px] flex items-center justify-center bg-[#2962ff] text-white text-[10px] font-bold uppercase tracking-wider px-4 py-2.5 rounded-lg hover:bg-[#1a4fc4] transition-colors touch-manipulation"
                  @click.stop
                >
                  Request Quote
                </router-link>
              </div>
            </div>
          </div>
        </section>
      </div>
    </main>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import { useRouter } from 'vue-router';
import api from '../../services/api';
import LoadingSpinner from '../../components/ui/LoadingSpinner.vue';

const props = defineProps({
  searchQuery: {
    type: String,
    default: ''
  }
});

const emit = defineEmits(['update:category']);

const router = useRouter();
const loading = ref(true);
const products = ref([]);
const categoryMap = ref(new Map());

const categories = ref([]); // Reconstructed tree
const expandedCategories = ref([]);
const selectedCategory = ref(null);

const fetchCatalog = async () => {
  loading.value = true;
  try {
    const response = await api.get('/api/v1/catalog/products');
    const items = response.data.data || response.data;
    products.value = items;
    
    // Extract unique categories and build a hierarchy
    const map = new Map();
    items.forEach(p => {
      if (p.categories && p.categories.length > 0) {
        p.categories.forEach(c => {
          if (!map.has(c.id)) {
            map.set(c.id, { ...c, children: [] });
          }
        });
      }
    });

    categoryMap.value = map;

    // For simplicity, just use flat categories if there's no explicit hierarchy returned from API.
    // Assuming backend returns flat categories for now, but trying to simulate grouping.
    let rootCats = Array.from(map.values()).sort((a,b) => a.label.localeCompare(b.label));
    categories.value = rootCats;
    
    // Auto-expand all for visual parity
    expandedCategories.value = rootCats.map(c => c.id);

  } catch (error) {
    console.error('Failed to fetch catalog:', error);
  } finally {
    loading.value = false;
  }
};

const goToCatalog = (product) => {
  const categoryId = product.categories?.[0]?.id;
  router.push({ name: 'Catalog', query: categoryId ? { categoryId } : {} });
};

const toggleCategory = (id) => {
  if (selectedCategory.value === id) {
    selectedCategory.value = null;
  } else {
    selectedCategory.value = id;
    if (id != null && !expandedCategories.value.includes(id)) {
      expandedCategories.value.push(id);
    }
  }
  emit('update:category', selectedCategory.value);
};

const isCategoryActive = (id) => {
  return selectedCategory.value === id;
};

const resetFilters = () => {
  selectedCategory.value = null;
  // Let the parent handle search query clearing if needed
};

// Groups products by their first assigned category
const groupedProducts = computed(() => {
  const groups = new Map();
  
  // Use categoryMap to define groups
  categoryMap.value.forEach(cat => {
    groups.set(cat.id, {
      category: cat,
      products: []
    });
  });

  // Default "Uncategorized" group just in case
  groups.set(-1, { category: { id: -1, label: 'Other Products' }, products: [] });

  products.value.forEach(p => {
    if (p.categories && p.categories.length > 0) {
       // Just put in the first category for grouping display
       const catId = p.categories[0].id;
       if (groups.has(catId)) {
         groups.get(catId).products.push(p);
       }
    } else {
       groups.get(-1).products.push(p);
    }
  });

  return Array.from(groups.values())
    .filter(g => g.products.length > 0)
    .sort((a, b) => {
      if (a.category.id === -1) return 1;
      if (b.category.id === -1) return -1;
      return a.category.label.localeCompare(b.category.label);
    });
});

const filteredGroupedProducts = computed(() => {
  let result = groupedProducts.value;

  // Filter by category selection (shows the specific group, or if a product in another group has this category)
  if (selectedCategory.value) {
    // Reconstruct groups that match the selected category
    const catGroupsMap = new Map();
    products.value.forEach(p => {
       if (p.categories && p.categories.some(c => c.id === selectedCategory.value)) {
          const mainCat = p.categories[0];
          if (!catGroupsMap.has(mainCat.id)) {
             catGroupsMap.set(mainCat.id, { category: mainCat, products: [] });
          }
          catGroupsMap.get(mainCat.id).products.push(p);
       }
    });
    result = Array.from(catGroupsMap.values());
  }

  // Filter by search query within the groups
  if (props.searchQuery && props.searchQuery.trim()) {
    const q = props.searchQuery.toLowerCase();
    
    result = result.map(group => {
      const filteredProds = group.products.filter(p => 
        p.name?.toLowerCase().includes(q) ||
        p.sku?.toLowerCase().includes(q) ||
        p.description?.toLowerCase().includes(q) ||
        p.grade?.toLowerCase().includes(q)
      );
      return { ...group, products: filteredProds };
    }).filter(g => g.products.length > 0);
  }

  return result;
});

const formatStockStatus = (status) => {
  if (!status) return '';
  return status.replace(/_/g, ' ').replace(/\b\w/g, c => c.toUpperCase());
};

onMounted(() => {
  fetchCatalog();
});

</script>
