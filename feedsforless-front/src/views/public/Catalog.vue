<template>
  <div class="flex flex-col md:flex-row w-full h-full min-h-0 bg-white dark:bg-slate-900">
    <CatalogSidebar
      :categories-with-links="sidebarCategoriesWithLinks"
      :selected-category="selectedCategory"
      :catalog-root-link="catalogRootLink"
      teleport-target-id="mobile-catalog-categories"
      :show-teleport="isMounted && hasMobileCategoriesTarget"
    />

    <main class="flex-1 w-full bg-white dark:bg-slate-900 relative p-4 md:p-6 lg:p-10">
      <AdminDemoBanner v-if="isAdminPreview" />

      <div v-if="currentView === 'grid'" class="animate-in fade-in duration-300">
        <h1 class="text-2xl md:text-3xl font-black italic text-[#003366] dark:text-blue-300 uppercase mb-1">Industrial Feed Commodity Catalog</h1>
        <p class="text-[10px] md:text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-8 border-b-[3px] border-slate-900 dark:border-slate-700 pb-4">Direct Supply Chain Portal | North American Distribution</p>
        
        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 border-t border-l border-slate-200 dark:border-slate-700">
          <router-link
            v-for="cat in allCategories"
            :key="cat.id"
            :to="categoryHubLink(cat)"
            class="border-r border-b border-slate-200 dark:border-slate-700 p-6 flex flex-col h-64 hover:bg-slate-50 dark:hover:bg-slate-800/50 cursor-pointer group transition-colors relative"
          >
            <div class="flex items-center justify-between mb-6">
              <h2 class="text-[#2962ff] dark:text-blue-400 font-black uppercase tracking-tight text-[15px]">{{ cat.label }}</h2>
              <svg class="w-3.5 h-3.5 text-slate-300 dark:text-slate-600 group-hover:text-[#2962ff] dark:group-hover:text-blue-400 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </div>
            <div class="flex-1 overflow-hidden">
              <ul class="text-[13px] text-slate-600 dark:text-slate-300 space-y-3 font-medium">
                <li v-for="product in getCategoryProducts(cat.id).slice(0, 3)" :key="product.id" class="truncate">
                  {{ product.name }}
                </li>
              </ul>
            </div>
            <div class="mt-4 text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest transition-colors mb-2">
              View Full Directory &rarr;
            </div>
          </router-link>
        </div>
      </div>

      <div v-else-if="currentView === 'list'" class="animate-in fade-in duration-300">
        <div class="flex flex-col md:flex-row md:items-end justify-between border-b-2 border-slate-200 dark:border-slate-700 pb-4 mb-4 gap-2">
          <div class="flex items-center gap-3">
            <button @click="currentView = 'grid'; selectedCategory = null" class="md:hidden p-1.5 -ml-1.5 text-slate-400 hover:text-slate-600 dark:hover:text-slate-300">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            </button>
            <h1 class="text-xl md:text-2xl font-black uppercase text-slate-900 dark:text-white tracking-tight">{{ selectedCategory ? selectedCategory.label : 'Search Results' }}</h1>
          </div>
          <span class="text-sm font-medium text-slate-500 dark:text-slate-400">{{ filteredProducts.length }} Products Available</span>
        </div>

        <div class="overflow-x-auto min-h-[500px] relative -mx-4 md:mx-0 px-4 md:px-0">
          <div v-if="loading" class="absolute inset-0 flex items-center justify-center bg-white/90 dark:bg-slate-900/90 z-10 rounded-lg">
            <PageLoader message="Loading products…" />
          </div>
          <table v-show="!loading" class="w-full text-left text-sm whitespace-nowrap lg:whitespace-normal animate-in fade-in duration-200">
            <thead>
              <tr class="text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest border-b border-slate-200 dark:border-slate-700">
                <th class="py-3 px-4 min-w-[200px] md:min-w-[300px]">Product Identity</th>
                <th class="py-3 px-4 w-[120px] hidden md:table-cell">Grade / Spec</th>
                <th class="py-3 px-4 w-1/4 hidden lg:table-cell">Packaging</th>
                <th class="py-3 px-4 w-[160px] text-right">Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr 
                v-for="product in filteredProducts" 
                :key="product.id" 
                class="border-b border-slate-200 dark:border-slate-700 hover:outline hover:outline-2 hover:outline-[#2962ff] dark:hover:outline-blue-500 hover:outline-offset-[-2px] bg-white dark:bg-slate-900 group transition-all duration-100 cursor-pointer"
                @click="goToProduct(product)"
              >
                <td class="py-4 px-4 align-top" @click.stop="goToProduct(product)">
                  <div class="font-bold text-[#2962ff] dark:text-blue-400 text-sm md:text-base mb-0.5 leading-tight">{{ product.name }}</div>
                  <div class="text-[11px] text-slate-500 dark:text-slate-400 max-w-sm leading-relaxed truncate">{{ product.description || 'Standard specification applicable for general use.' }}</div>
                  <div class="md:hidden mt-2 text-xs text-slate-500 dark:text-slate-400">
                    <span class="font-bold text-slate-700 dark:text-slate-300 mr-2">{{ product.grade || '--' }}</span>
                  </div>
                </td>
                <td class="py-4 px-4 align-top font-bold text-slate-700 dark:text-slate-300 text-xs hidden md:table-cell">
                  {{ product.grade || '--' }}
                </td>
                <td class="py-4 px-4 align-top text-slate-600 dark:text-slate-400 text-xs hidden lg:table-cell">
                  <span v-if="product.packaging_options && product.packaging_options.length > 0">
                    {{ product.packaging_options.map(p => p.type_name).join(', ') }}
                  </span>
                  <span v-else>Bags, Totes, Bulk Truck</span>
                </td>
                <td class="py-4 px-4 align-top text-right min-w-[150px] md:min-w-[200px]" @click.stop>
                  <div class="flex items-center justify-end gap-1.5 md:gap-2 flex-wrap">
                    <button v-if="product.tds_document_path" @click.prevent="previewDocument(product.id, 'tds')" class="bg-slate-200 dark:bg-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-300 dark:hover:bg-slate-600 text-[9px] font-bold px-2 py-1.5 uppercase tracking-wider rounded shrink-0">TDS</button>
                    <button v-if="product.sds_document_path" @click.prevent="previewDocument(product.id, 'sds')" class="bg-slate-200 dark:bg-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-300 dark:hover:bg-slate-600 text-[9px] font-bold px-2 py-1.5 uppercase tracking-wider rounded shrink-0">SDS</button>
                    <button v-if="product.coa_document_path" @click.prevent="previewDocument(product.id, 'coa')" class="bg-slate-200 dark:bg-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-300 dark:hover:bg-slate-600 text-[9px] font-bold px-2 py-1.5 uppercase tracking-wider rounded shrink-0">ODA</button>
                    <router-link
                      :to="requestQuoteRoute(product)"
                      class="inline-block min-h-[40px] md:min-h-[44px] min-w-[40px] flex items-center justify-center bg-[#2962ff] dark:bg-blue-600 text-white font-bold text-[10px] md:text-[11px] uppercase tracking-wider px-3 md:px-4 py-2 rounded opacity-100 transition-opacity hover:bg-blue-800 dark:hover:bg-blue-700 shrink-0 touch-manipulation"
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
import { useAuthStore } from '../../stores/auth';
import api from '../../services/api';
import AdminDemoBanner from '../../components/ui/AdminDemoBanner.vue';
import CatalogSidebar from '../../components/catalog/CatalogSidebar.vue';
import PageLoader from '../../components/ui/PageLoader.vue';

const props = defineProps({
  searchQuery: { type: String, default: '' },
  categorySlug: { type: String, default: null },
  categoryParentSlug: { type: String, default: null },
  categoryChildSlug: { type: String, default: null }
});

const router = useRouter();
const route = useRoute();
const authStore = useAuthStore();
const products = ref([]);
const loading = ref(true);
const selectedCategory = ref(null);
const currentView = ref('grid');
const isMounted = ref(false);
const hasMobileCategoriesTarget = ref(false);
/** Full category list for sidebar (from API), so it doesn't disappear when viewing a category hub */
const sidebarCategoriesFromApi = ref([]);

const isClient = computed(() => {
  return !!authStore.token && (!authStore.user?.roles || !authStore.user.roles.some((r) => ['admin', 'Admin', 'Super Admin'].includes(r.name)));
});

/** True when an admin is viewing the catalog (preview of client experience). Show demo notice. */
const isAdminPreview = computed(() => {
  return !!authStore.token && !isClient.value;
});

const apiBaseUrl = api.defaults.baseURL || '';

function goToProduct(product) {
  const inApp = route.path.startsWith('/app');
  const name = inApp ? 'AppProductDetail' : 'ProductDetail';
  const slug = product.slug || String(product.id);
  router.push({ name, params: { slug } });
}

function productDetailRoute(product) {
  const inApp = route.path.startsWith('/app');
  const slug = product.slug || String(product.id);
  return { name: inApp ? 'AppProductDetail' : 'ProductDetail', params: { slug } };
}

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
  return Array.from(map.values()).sort((a, b) => (a.label || '').localeCompare(b.label || ''));
});

const inApp = computed(() => route.path.startsWith('/app'));

const catalogRootLink = computed(() =>
  inApp.value ? { name: 'AppCatalog' } : { name: 'Catalog' }
);

function categoryHubLink(cat) {
  const slug = cat?.slug || cat?.label?.toLowerCase?.()?.replace(/\s+/g, '-') || String(cat?.id);
  return inApp.value
    ? { name: 'AppCategoryHub', params: { categorySlug: slug } }
    : { name: 'CategoryHub', params: { categorySlug: slug } };
}

const sidebarCategoriesWithLinks = computed(() =>
  sidebarCategoriesFromApi.value.map(c => ({ ...c, link: categoryHubLink(c) }))
);

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
  // Dispatch event to smoothly close mobile menu if it's open
  window.dispatchEvent(new Event('close-mobile-menu'));
};

const fetchProducts = async () => {
  loading.value = true;
  try {
    const categorySlugParam = props.categoryChildSlug || props.categorySlug;
    const url = categorySlugParam
      ? `/api/v1/catalog/products?category_slug=${encodeURIComponent(categorySlugParam)}`
      : '/api/v1/catalog/products';
    const response = await api.get(url);
    const items = response.data.data ?? response.data;
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

function flattenCategoriesTree(tree) {
  if (!Array.isArray(tree)) return [];
  const out = [];
  for (const node of tree) {
    out.push({ id: node.id, label: node.label, slug: node.slug });
    if (node.children?.length) out.push(...flattenCategoriesTree(node.children));
  }
  return out.sort((a, b) => (a.label || '').localeCompare(b.label || ''));
}

async function fetchSidebarCategories() {
  try {
    const { data } = await api.get('/api/v1/catalog/categories');
    const raw = data?.data ?? data;
    sidebarCategoriesFromApi.value = flattenCategoriesTree(Array.isArray(raw) ? raw : []);
  } catch (e) {
    console.error('Failed to load sidebar categories', e);
    sidebarCategoriesFromApi.value = [];
  }
}

onMounted(() => {
  isMounted.value = true;
  hasMobileCategoriesTarget.value = !!document.getElementById('mobile-catalog-categories');
  fetchSidebarCategories();
  fetchProducts();
});

// Category hub from route (/:categorySlug or /:parent/:child)
watch(
  () => [props.categorySlug, props.categoryChildSlug, products.value.length, sidebarCategoriesFromApi.value.length],
  ([slug, childSlug]) => {
    const useSlug = childSlug || slug;
    if (!useSlug) {
      if (!props.categorySlug && !props.categoryChildSlug) selectedCategory.value = null;
      return;
    }
    const cat = sidebarCategoriesFromApi.value.find(c => (c.slug || '').toLowerCase() === (useSlug || '').toLowerCase());
    if (cat) {
      selectedCategory.value = cat;
      currentView.value = 'list';
    }
  },
  { immediate: true }
);

// Open in list view with category when coming from Home with ?categoryId=
watch(
  () => [route.query.categoryId, products.value.length],
  ([categoryId]) => {
    if (props.categorySlug || props.categoryChildSlug) return;
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