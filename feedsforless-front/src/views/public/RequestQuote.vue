<template>
  <div class="flex flex-col md:flex-row w-full max-w-7xl mx-auto gap-8 pt-6">
    <aside class="w-full md:w-60 shrink-0">
      <div class="mb-8">
        <h3 class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3 px-2">Categories</h3>
        <nav v-if="sidebarCategories.length" class="flex flex-col space-y-0.5">
          <router-link
            :to="catalogLink"
            class="text-left px-2 py-2 text-sm font-medium transition-colors border-l-4 border-transparent text-slate-600 hover:text-slate-900 dark:text-slate-300 dark:hover:text-white"
          >
            All Commodities
          </router-link>
          <router-link
            v-for="cat in sidebarCategories"
            :key="cat.id"
            :to="{ name: catalogLink.name, query: { categoryId: cat.id } }"
            class="text-left px-2 py-2 text-sm font-medium transition-colors border-l-4 border-transparent text-slate-600 hover:text-slate-900 dark:text-slate-300 dark:hover:text-white"
          >
            {{ cat.label }}
          </router-link>
        </nav>
        <div v-else class="px-2 py-3 text-xs text-slate-400">Loading categories…</div>
      </div>
      <!-- <div class="px-6 mb-8 mt-4 border-t border-slate-200 pt-6">
        <h3 class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3">Resources</h3>
        <nav class="flex flex-col space-y-3 text-sm font-bold text-[#2962ff]">
          <a href="#" class="hover:underline">Logistics Map</a>
          <a href="#" class="hover:underline">Bulk Volume Tiers</a>
          <a href="#" class="hover:underline">Tax Exemption Forms</a>
          <a href="#" class="hover:underline">Supplier Compliance</a>
        </nav>
      </div> -->
    </aside>

    <main class="flex-1 w-full bg-white relative p-6 lg:p-10">
      <div v-if="!submittedSuccess" class="mb-6">
        <router-link
          :to="backToCatalogLink"
          class="text-[11px] font-bold text-[#2962ff] hover:text-blue-800 uppercase tracking-wider flex items-center gap-1"
        >
          &larr; {{ product ? `Back to ${product.name}` : 'Back to Catalog' }}
        </router-link>
      </div>

      <div v-if="!productId" class="max-w-2xl">
        <p class="text-slate-600 mb-4">Select a product from the catalog to request a quote.</p>
        <router-link :to="backToCatalogLink" class="inline-block bg-[#2962ff] text-white font-bold text-sm px-4 py-2 hover:bg-blue-800">
          Go to Catalog
        </router-link>
      </div>

      <div v-else-if="loadingProduct" class="py-12">
        <PageLoader message="Loading product…" />
      </div>

      <div v-else-if="!product" class="max-w-2xl">
        <p class="text-slate-600 mb-4">Product not found.</p>
        <router-link :to="backToCatalogLink" class="inline-block bg-[#2962ff] text-white font-bold text-sm px-4 py-2 hover:bg-blue-800">
          Go to Catalog
        </router-link>
      </div>

      <!-- Success: Request Transmitted -->
      <div v-else-if="submittedSuccess" class="animate-in fade-in duration-300 max-w-2xl">
        <router-link
          :to="backToCatalogLink"
          class="text-[11px] font-bold text-[#2962ff] hover:text-blue-800 uppercase tracking-wider flex items-center gap-1 mb-6"
        >
          &larr; Back to {{ submittedProductName || 'Catalog' }}
        </router-link>
        <div class="bg-emerald-50 border border-emerald-200 rounded-xl p-8 md:p-10 text-center">
          <div class="w-14 h-14 bg-emerald-500 rounded-2xl flex items-center justify-center mx-auto mb-4">
            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
          </div>
          <h2 class="text-xl font-black text-emerald-800 uppercase tracking-tight mb-3">Request Transmitted</h2>
          <p class="text-slate-700 text-sm leading-relaxed mb-6">
            Your request for <strong>{{ submittedProductName }}</strong> has been received. Our logistics team will verify the route and pricing and email your formal quote to <strong>{{ submittedEmail }}</strong> shortly.
          </p>
          <router-link
            :to="returnToPortalLink"
            class="inline-block bg-slate-900 text-white font-bold text-[11px] uppercase tracking-wider px-6 py-3 rounded hover:bg-slate-800 transition-colors"
          >
            Return to Portal
          </router-link>
        </div>
      </div>

      <div v-else class="animate-in fade-in duration-300 mt-2">
        <div class="border border-slate-200 shadow-sm p-8 bg-white max-w-4xl">
          <div class="flex justify-between items-start mb-6">
            <div>
              <h2 class="text-xl font-black uppercase text-slate-900 tracking-tight">Request For Delivered Quote</h2>
              <p class="text-xs font-bold text-slate-500 border-b border-slate-200 pb-4 inline-block mt-1">
                Commodity: <span class="text-[#2962ff]">{{ product.name }}</span>
                <span class="mx-1 text-slate-300">|</span>
                Grade: <span class="text-slate-800">{{ product.grade || 'Standard' }}</span>
              </p>
            </div>
            <div class="border border-slate-200 px-3 py-1 bg-slate-50">
              <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">RFQ ID: {{ rfqId }}</span>
            </div>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-10">
            <div>
              <h3 class="flex items-center gap-2 text-[11px] font-black text-slate-900 uppercase tracking-widest mb-4 border-b-2 border-slate-900 pb-2">
                <span class="bg-slate-900 text-white w-4 h-4 rounded-md flex items-center justify-center text-[9px]">1</span>
                Corporate Entity Profile
              </h3>
              <div class="space-y-4">
                <div>
                  <label class="block text-[10px] font-bold text-slate-600 uppercase tracking-widest mb-1.5">Legal Business Name*</label>
                  <input v-model="form.legal_name" type="text" class="w-full min-h-[44px] border border-slate-300 bg-slate-50 px-3 py-2 text-sm focus:outline-none focus:border-[#2962ff] text-slate-800 font-medium touch-manipulation" />
                </div>
                <div>
                  <label class="block text-[10px] font-bold text-slate-600 uppercase tracking-widest mb-1.5">Authorized Procurement Contact*</label>
                  <input v-model="form.contact_name" type="text" class="w-full min-h-[44px] border border-slate-300 bg-slate-50 px-3 py-2 text-sm focus:outline-none focus:border-[#2962ff] text-slate-800 font-medium touch-manipulation" />
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                  <div>
                    <label class="block text-[10px] font-bold text-slate-600 uppercase tracking-widest mb-1.5">Business Email*</label>
                    <input v-model="form.email" type="email" class="w-full min-h-[44px] border border-slate-300 bg-slate-50 px-3 py-2 text-sm focus:outline-none focus:border-[#2962ff] text-slate-800 font-medium touch-manipulation" />
                  </div>
                  <div>
                    <label class="block text-[10px] font-bold text-slate-600 uppercase tracking-widest mb-1.5">Primary Phone*</label>
                    <input v-model="form.phone" type="tel" class="w-full min-h-[44px] border border-slate-300 bg-slate-50 px-3 py-2 text-sm focus:outline-none focus:border-[#2962ff] text-slate-800 font-medium touch-manipulation" />
                  </div>
                </div>
                <div>
                  <label class="block text-[10px] font-bold text-slate-600 uppercase tracking-widest mb-1.5">Sales Tax ID / Exemption Certificate</label>
                  <input v-model="form.tax_id" type="text" class="w-full min-h-[44px] border border-slate-300 bg-slate-50 px-3 py-2 text-sm focus:outline-none focus:border-[#2962ff] text-slate-800 font-medium touch-manipulation" />
                </div>
              </div>
            </div>

            <div>
              <h3 class="flex items-center gap-2 text-[11px] font-black text-slate-900 uppercase tracking-widest mb-4 border-b-2 border-slate-900 pb-2">
                <span class="bg-slate-900 text-white w-4 h-4 rounded-md flex items-center justify-center text-[9px]">2</span>
                Supply Chain & Logistics
              </h3>
              <div class="space-y-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                  <div>
                    <label class="block text-[10px] font-bold text-slate-600 uppercase tracking-widest mb-1.5">Request Volume*</label>
                    <input v-model.number="form.quantity" type="number" min="1" class="w-full min-h-[44px] border border-slate-300 bg-slate-50 px-3 py-2 text-sm focus:outline-none focus:border-[#2962ff] text-slate-800 font-medium touch-manipulation" />
                  </div>
                  <div>
                    <label class="block text-[10px] font-bold text-slate-600 uppercase tracking-widest mb-1.5">Logistics Unit*</label>
                    <select v-model.number="form.packaging_type_id" class="w-full min-h-[44px] border border-slate-300 bg-slate-50 px-3 py-2 text-sm focus:outline-none focus:border-[#2962ff] text-slate-800 font-medium touch-manipulation">
                      <option v-for="opt in packagingOptions" :key="opt.packaging_type_id" :value="opt.packaging_type_id">
                        {{ opt.type_name }}
                      </option>
                      <option v-if="!packagingOptions.length" value="">—</option>
                    </select>
                  </div>
                </div>
                <div>
                  <label class="block text-[10px] font-bold text-slate-600 uppercase tracking-widest mb-1.5">Destination Address*</label>
                  <input v-model="form.destination_address" type="text" class="w-full min-h-[44px] border border-slate-300 bg-slate-50 px-3 py-2 text-sm focus:outline-none focus:border-[#2962ff] text-slate-800 font-medium touch-manipulation" />
                </div>
                <div>
                  <label class="block text-[10px] font-bold text-slate-600 uppercase tracking-widest mb-1.5">Destination Zip Code*</label>
                  <input v-model="form.delivery_zip" type="text" class="w-full min-h-[44px] border border-slate-300 bg-slate-50 px-3 py-2 text-sm focus:outline-none focus:border-[#2962ff] text-slate-800 font-medium touch-manipulation" />
                </div>
                <div class="pt-2 border-t border-slate-100 flex flex-col gap-2">
                  <div class="flex items-center gap-2">
                    <input v-model="form.requires_liftgate" type="checkbox" id="liftgate" class="w-3.5 h-3.5 border-slate-300 text-[#2962ff] focus:ring-[#2962ff]" />
                    <label for="liftgate" class="text-xs font-bold text-slate-600 uppercase tracking-widest">Lift Gate Accessorial Required</label>
                  </div>
                  <div class="flex items-center gap-2">
                    <input v-model="form.requires_appointment" type="checkbox" id="predelivery" class="w-3.5 h-3.5 border-slate-300 text-[#2962ff] focus:ring-[#2962ff]" />
                    <label for="predelivery" class="text-xs font-bold text-slate-600 uppercase tracking-widest">Pre-Delivery Call Required</label>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="mt-8 border-t border-slate-200 pt-6 flex items-start sm:items-center justify-between flex-col sm:flex-row gap-6">
            <div class="flex items-start gap-3 bg-[#f5f8ff] p-4 border-l-4 border-[#2962ff] max-w-sm">
              <svg class="w-5 h-5 text-[#2962ff] shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
              <p class="text-[10px] font-bold text-[#003366] leading-relaxed">
                Official FeedsForLess industrial quotes incorporate current terminal pricing and verified Pacific Northwest freight lanes. Quotes expire in 48 hours unless otherwise noted.
              </p>
            </div>
            <div class="flex items-center gap-4 shrink-0">
              <router-link :to="backToCatalogLink" class="min-h-[44px] min-w-[44px] inline-flex items-center justify-center text-[11px] font-bold text-slate-500 uppercase tracking-wider hover:text-slate-800 transition-colors touch-manipulation">Cancel</router-link>
              <button
                type="button"
                :disabled="isAdmin || submitting"
                @click="submitQuote"
                :title="isAdmin ? 'Administrators cannot submit quote requests.' : ''"
                class="min-h-[44px] px-8 py-3.5 bg-[#2962ff] text-white font-bold text-[11px] uppercase tracking-wider hover:bg-blue-800 transition-colors disabled:opacity-60 disabled:cursor-not-allowed touch-manipulation"
              >
                {{ submitting ? 'Sending…' : 'Verify & Send RFQ' }}
              </button>
            </div>
          </div>
        </div>
      </div>
    </main>
  </div>
</template>

<script setup>
import { ref, computed, reactive, watch, onMounted } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import { useAuthStore } from '../../stores/auth';
import api from '../../services/api';
import { useToast } from '../../composables/useToast';
import PageLoader from '../../components/ui/PageLoader.vue';

const props = defineProps({
  productId: { type: Number, default: null }
});

const router = useRouter();
const route = useRoute();
const authStore = useAuthStore();
const toast = useToast();

const product = ref(null);
const loadingProduct = ref(false);
const submitting = ref(false);
const submittedSuccess = ref(false);
const submittedProductName = ref('');
const submittedEmail = ref('');
const rfqId = ref('RFQ' + Math.random().toString(36).slice(2, 10).toUpperCase());

const isLoggedIn = computed(() => !!authStore.token);
const isAdmin = computed(() => {
  return !!authStore.token && authStore.user?.roles && authStore.user.roles.some((r) => r.name === 'admin');
});

const form = reactive({
  legal_name: '',
  contact_name: '',
  email: '',
  phone: '',
  tax_id: '',
  quantity: 24,
  packaging_type_id: null,
  destination_address: '',
  delivery_zip: '',
  requires_liftgate: false,
  requires_appointment: false
});

const packagingOptions = computed(() => {
  return product.value?.packaging_options || [];
});

const backToCatalogLink = computed(() => {
  const inApp = route.path.startsWith('/app');
  if (product.value?.slug) {
    return inApp
      ? { name: 'AppProductDetail', params: { slug: product.value.slug } }
      : { name: 'ProductDetail', params: { slug: product.value.slug } };
  }
  if (product.value?.id) {
    return inApp
      ? { name: 'AppProductDetail', params: { slug: String(product.value.id) } }
      : { name: 'ProductDetail', params: { slug: String(product.value.id) } };
  }
  return { name: inApp ? 'AppCatalog' : 'Catalog', query: {} };
});

/** Sidebar: always link to Catalog (not ProductDetail) so "All Commodities" works */
const catalogLink = computed(() => {
  const inApp = route.path.startsWith('/app');
  return { name: inApp ? 'AppCatalog' : 'Catalog', query: {} };
});

const sidebarCategories = ref([]);

function flattenCategories(tree) {
  if (!Array.isArray(tree)) return [];
  const out = [];
  for (const node of tree) {
    out.push({ id: node.id, label: node.label, slug: node.slug });
    if (node.children?.length) out.push(...flattenCategories(node.children));
  }
  return out.sort((a, b) => (a.label || '').localeCompare(b.label || ''));
}

async function fetchSidebarCategories() {
  try {
    const { data } = await api.get('/api/v1/catalog/categories');
    const raw = data?.data ?? data;
    sidebarCategories.value = flattenCategories(Array.isArray(raw) ? raw : []);
  } catch (e) {
    console.error('Failed to load sidebar categories', e);
    sidebarCategories.value = [];
  }
}

const returnToPortalLink = computed(() => {
  if (route.path.startsWith('/app')) {
    return { name: 'AppCatalog' };
  }
  return { name: 'Catalog' };
});

async function fetchProduct() {
  if (props.productId == null || Number.isNaN(Number(props.productId))) return;
  const id = Number(props.productId);
  if (id < 1) return;
  loadingProduct.value = true;
  product.value = null;
  try {
    const { data } = await api.get(`/api/v1/catalog/products/${id}`);
    const raw = data?.data ?? data;
    product.value = raw;
    const opts = raw?.packaging_options || [];
    if (opts.length && !form.packaging_type_id) {
      form.packaging_type_id = opts[0].packaging_type_id;
    }
    if (authStore.user) {
      form.legal_name = authStore.user.company_name || '';
      form.contact_name = [authStore.user.first_name, authStore.user.last_name].filter(Boolean).join(' ') || authStore.user.name || '';
      form.email = authStore.user.email || '';
      form.phone = authStore.user.phone || '';
    }
  } catch {
    product.value = null;
  } finally {
    loadingProduct.value = false;
  }
}

watch(() => props.productId, fetchProduct, { immediate: true });

// Prefill from ProductDetail (route query)
watch(
  () => route.query,
  (q) => {
    if (q.quantity != null) form.quantity = Number(q.quantity) || 24;
    if (q.packaging_type_id != null) form.packaging_type_id = Number(q.packaging_type_id) || null;
    if (q.delivery_zip != null) form.delivery_zip = String(q.delivery_zip).trim() || form.delivery_zip;
    if (q.requires_liftgate === '1' || q.requires_liftgate === 'true') form.requires_liftgate = true;
  },
  { immediate: true }
);

onMounted(() => {
  fetchSidebarCategories();
  if (authStore.user) {
    form.legal_name = authStore.user.company_name || form.legal_name;
    form.contact_name = form.contact_name || [authStore.user.first_name, authStore.user.last_name].filter(Boolean).join(' ') || authStore.user.name || '';
    form.email = authStore.user.email || form.email;
    form.phone = authStore.user.phone || form.phone;
  }
  const q = route.query;
  if (q.quantity != null) form.quantity = Number(q.quantity) || 24;
  if (q.packaging_type_id != null) form.packaging_type_id = Number(q.packaging_type_id) || null;
  if (q.delivery_zip != null) form.delivery_zip = String(q.delivery_zip).trim() || form.delivery_zip;
  if (q.requires_liftgate === '1' || q.requires_liftgate === 'true') form.requires_liftgate = true;
});

async function submitQuote() {
  if (isAdmin.value || !product.value) return;
  if (!form.quantity || form.quantity < 1) {
    toast.error('Enter a valid request volume.');
    return;
  }
  const packagingId = form.packaging_type_id || packagingOptions.value[0]?.packaging_type_id;
  if (!packagingId) {
    toast.error('Select a logistics unit.');
    return;
  }
  if (!form.delivery_zip || !form.delivery_zip.trim()) {
    toast.error('Destination zip code is required.');
    return;
  }
  if (!isLoggedIn.value) {
    if (!form.email || !form.email.trim()) {
      toast.error('Business email is required.');
      return;
    }
    if (!form.legal_name || !form.legal_name.trim()) {
      toast.error('Legal business name is required.');
      return;
    }
    if (!form.contact_name || !form.contact_name.trim()) {
      toast.error('Authorized procurement contact is required.');
      return;
    }
    if (!form.phone || !form.phone.trim()) {
      toast.error('Primary phone is required.');
      return;
    }
  }
  submitting.value = true;
  try {
    if (isLoggedIn.value) {
      const addRes = await api.post('/api/v1/rfq-list/items', {
        product_id: product.value.id,
        packaging_type_id: packagingId,
        quantity: form.quantity
      });
      const rfqListId = addRes.data?.data?.id;
      if (!rfqListId) throw new Error('Could not get RFQ list id');
      await api.post('/api/v1/quote-requests', {
        rfq_list_id: rfqListId,
        delivery_zip: form.delivery_zip.trim(),
        requires_liftgate: !!form.requires_liftgate,
        requires_appointment: !!form.requires_appointment
      });
    } else {
      await api.post('/api/v1/quote-requests/guest', {
        product_id: product.value.id,
        packaging_type_id: packagingId,
        quantity: form.quantity,
        delivery_zip: form.delivery_zip.trim(),
        destination_address: form.destination_address?.trim() || null,
        requires_liftgate: !!form.requires_liftgate,
        requires_appointment: !!form.requires_appointment,
        email: form.email.trim(),
        legal_name: form.legal_name.trim(),
        contact_name: form.contact_name.trim(),
        phone: form.phone.trim(),
        tax_id: form.tax_id?.trim() || null
      });
    }
    submittedProductName.value = product.value?.name || '';
    submittedEmail.value = form.email?.trim() || authStore.user?.email || '';
    submittedSuccess.value = true;
  } catch (err) {
    console.error(err);
    const msg = err.response?.data?.message || 'Failed to submit quote request.';
    toast.error(msg);
  } finally {
    submitting.value = false;
  }
}
</script>
