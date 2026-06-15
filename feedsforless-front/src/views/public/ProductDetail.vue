<template>
  <div class="flex flex-1 flex-col md:flex-row w-full min-h-0 bg-white dark:bg-slate-900">
    <!-- Left sidebar: Categories + Resources (match design) -->
    <aside class="w-full md:w-64 shrink-0 border-r border-slate-200 dark:border-slate-700 bg-[#f8fafc] dark:bg-slate-800/60 min-h-0 flex flex-col pt-8 pb-8 px-4 hidden md:flex">
      <div class="mb-6">
        <h2 class="text-[11px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-3 px-2">Categories</h2>
        <nav class="flex flex-col space-y-0.5">
          <router-link
            :to="catalogLink"
            class="w-full text-left px-3 py-2.5 text-[13px] rounded-md transition-colors font-medium"
            :class="!primaryCategory ? 'bg-[#2962ff] text-white font-bold' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700/50'"
          >
            All Commodities
          </router-link>
          <router-link
            v-for="cat in allCategories"
            :key="cat.id"
            :to="{ name: catalogLink.name, query: { categoryId: cat.id } }"
            class="w-full text-left px-3 py-2.5 text-[13px] rounded-md transition-colors font-medium"
            :class="primaryCategory?.id === cat.id ? 'bg-[#2962ff] text-white font-bold' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700/50'"
          >
            {{ cat.label }}
          </router-link>
        </nav>
      </div>
      <!-- <div class="border-t border-slate-200/80 mb-8 mx-2"></div>
      <div>
        <h2 class="text-[11px] font-black text-slate-400/80 uppercase tracking-widest mb-4 px-2">Resources</h2>
        <nav class="flex flex-col space-y-3 px-2 text-[13px]">
          <a href="#" class="text-[#2962ff] hover:underline font-bold">Logistics Map</a>
          <a href="#" class="text-[#2962ff] hover:underline font-bold">Bulk Volume Tiers</a>
          <a href="#" class="text-[#2962ff] hover:underline font-bold">Tax Exemption Forms</a>
          <a href="#" class="text-[#2962ff] hover:underline font-bold">Supplier Compliance</a>
        </nav>
      </div> -->
    </aside>

    <!-- Main content -->
    <main class="flex-1 w-full min-w-0 min-h-0 overflow-y-auto overflow-x-hidden p-4 sm:p-6 md:p-8 lg:p-10">
      <div v-if="loading" class="py-12 flex justify-center">
        <PageLoader message="Loading product…" />
      </div>

      <template v-else-if="product">
        <router-link
          :to="catalogLink"
          class="inline-block text-[11px] font-bold text-[#2962ff] hover:text-blue-800 dark:hover:text-blue-300 uppercase tracking-wider mb-6"
        >
          &larr; Back to Catalog
        </router-link>

        <!-- Category badge (uppercase, from product) -->
        <p class="text-[10px] font-black text-slate-500 dark:text-slate-400 uppercase tracking-widest mb-1">
          {{ categoryLabel }}
        </p>
        <h1 class="text-2xl md:text-3xl font-black text-slate-900 dark:text-white tracking-tight mb-2">
          {{ product.name }}
        </h1>
        <p v-if="product.grade" class="text-sm font-bold text-slate-700 dark:text-slate-300 mb-8 pb-4 border-b border-slate-200 dark:border-slate-700">
          Grade Specification: {{ product.grade }}
        </p>
        <p v-else class="mb-8 pb-4 border-b border-slate-200 dark:border-slate-700"></p>

        <!-- Row: Technical Documentation (left) + Logistics Summary (right box) -->
        <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-6 mb-8">
          <div>
            <h3 class="text-[10px] font-black text-slate-500 dark:text-slate-400 uppercase tracking-widest mb-3">Technical Documentation</h3>
            <div class="flex flex-wrap gap-x-6 gap-y-2">
              <a
                v-if="product.sds_document_path"
                :href="documentUrl('sds')"
                target="_blank"
                rel="noopener noreferrer"
                class="inline-flex items-center gap-2 text-[#2962ff] dark:text-blue-400 font-bold text-xs hover:underline"
              >
                Safety Data Sheet (SDS)
                <svg class="w-4 h-4 text-slate-400" fill="currentColor" viewBox="0 0 20 20"><path d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z"/></svg>
              </a>
              <a
                v-if="product.tds_document_path"
                :href="documentUrl('tds')"
                target="_blank"
                rel="noopener noreferrer"
                class="inline-flex items-center gap-2 text-[#2962ff] dark:text-blue-400 font-bold text-xs hover:underline"
              >
                Technical Data Sheet (TDS/PDS)
                <svg class="w-4 h-4 text-slate-400" fill="currentColor" viewBox="0 0 20 20"><path d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z"/></svg>
              </a>
              <span v-if="!product.sds_document_path && !product.tds_document_path" class="text-slate-400 text-sm">—</span>
            </div>
          </div>
          <div class="lg:min-w-[280px] border border-slate-200 dark:border-slate-700 rounded-lg bg-slate-50 dark:bg-slate-800/50 p-4">
            <h3 class="text-[10px] font-black text-slate-500 dark:text-slate-400 uppercase tracking-widest mb-3">Logistics Summary</h3>
            <div class="space-y-2 text-sm">
              <div>
                <span class="font-bold text-slate-700 dark:text-slate-300">Packaging:</span>
                <span class="text-slate-600 dark:text-slate-400 ml-1">{{ packagingSummary }}</span>
              </div>
              <div>
                <span class="font-bold text-slate-700 dark:text-slate-300">Vendor ID:</span>
                <span class="text-slate-600 dark:text-slate-400 ml-1">{{ product.sku || '—' }}</span>
              </div>
              <div>
                <span class="font-bold text-slate-700 dark:text-slate-300">Lead Time:</span>
                <span class="ml-1 text-emerald-600 dark:text-emerald-400 font-semibold">{{ leadTimeText }}</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Product Overview (from product description) -->
        <div class="mb-8">
          <h3 class="text-[11px] font-black text-slate-700 dark:text-slate-300 uppercase tracking-widest mb-2">Product Overview</h3>
          <p class="text-sm text-slate-600 dark:text-slate-400 leading-relaxed">
            {{ product.description || 'Free-flowing, feed-grade calcium and phosphorus supplement supporting bone development and growth.' }}
          </p>
        </div>

        <!-- Intended Use & Applications (from product.typical_applications) -->
        <div v-if="product.typical_applications?.length" class="mb-8">
          <h3 class="text-[11px] font-black text-slate-700 dark:text-slate-300 uppercase tracking-widest mb-3">Intended Use & Applications</h3>
          <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
            <div
              v-for="app in product.typical_applications"
              :key="app.id"
              class="border border-slate-200 dark:border-slate-700 rounded-lg px-4 py-3 text-sm font-medium text-slate-700 dark:text-slate-300 bg-white dark:bg-slate-800/50"
            >
              {{ app.label }}
            </div>
          </div>
        </div>

        <!-- Technical Q&A (product-based fallback) -->
        <div class="border-t border-slate-200 dark:border-slate-700 pt-6">
          <h3 class="text-[11px] font-black text-slate-700 dark:text-slate-300 uppercase tracking-widest mb-2">Technical Q&A</h3>
          <p class="text-sm text-slate-600 dark:text-slate-400">
            <span class="font-bold text-slate-800 dark:text-slate-200">Q: Cattle use?</span>
            <span class="ml-1">{{ technicalQaAnswer }}</span>
          </p>
        </div>
      </template>

      <div v-else class="max-w-2xl">
        <p class="text-slate-600 dark:text-slate-400 mb-4">Product not found.</p>
        <router-link :to="catalogLink" class="inline-block bg-[#2962ff] text-white font-bold text-sm px-4 py-2 rounded hover:bg-blue-800">
          Go to Catalog
        </router-link>
      </div>
    </main>

    <!-- Right sidebar: Delivered Price Calculator (match design) -->
    <aside
      v-if="product"
      class="w-full md:w-[340px] shrink-0 border-t md:border-t-0 md:border-l border-slate-200 dark:border-slate-700 bg-[#e8eeff] dark:bg-slate-800/90 p-6 flex flex-col min-h-0"
    >
      <h2 class="text-sm font-black text-[#003366] dark:text-blue-100 uppercase tracking-widest mb-5">
        Delivered Price Calculator
      </h2>

      <div class="space-y-4">
        <div>
          <label class="block text-[10px] font-bold text-slate-600 dark:text-slate-400 uppercase tracking-widest mb-1.5">Order Volume (Tons)</label>
          <input
            v-model.number="orderVolume"
            type="number"
            min="1"
            step="1"
            class="w-full min-h-[44px] border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-3 py-2 text-sm font-medium text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-[#2962ff] focus:border-[#2962ff] rounded"
            placeholder="e.g. 24"
          />
        </div>
        <div>
          <label class="block text-[10px] font-bold text-slate-600 dark:text-slate-400 uppercase tracking-widest mb-1.5">Packaging Type</label>
          <select
            v-model.number="selectedPackagingOptionId"
            class="w-full min-h-[44px] border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-3 py-2 text-sm font-medium text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-[#2962ff] focus:border-[#2962ff] rounded"
          >
            <option v-for="opt in product.packaging_options" :key="opt.id" :value="opt.id">
              {{ opt.type_name }}
            </option>
          </select>
        </div>
        <div>
          <label class="block text-[10px] font-bold text-slate-600 dark:text-slate-400 uppercase tracking-widest mb-1.5">Destination Zip</label>
          <input
            v-model="destinationZip"
            type="text"
            class="w-full min-h-[44px] border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-3 py-2 text-sm font-medium text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-[#2962ff] focus:border-[#2962ff] rounded"
            placeholder="e.g. 46204"
          />
          <div class="mt-2 flex items-center gap-2">
            <input
              v-model="requiresLiftgate"
              type="checkbox"
              id="liftgate-calc"
              class="w-3.5 h-3.5 border-slate-300 text-[#2962ff] focus:ring-[#2962ff] rounded"
            />
            <label for="liftgate-calc" class="text-xs font-bold text-slate-600 dark:text-slate-400 uppercase tracking-widest">Liftgate</label>
          </div>
        </div>
      </div>

      <div
        v-if="effectiveProductPerTon != null && validVolume"
        class="mt-5 rounded-lg border border-slate-200/80 dark:border-slate-600/80 bg-slate-50/80 dark:bg-slate-800/40 px-3.5 py-3 space-y-1.5"
      >
        <div class="flex justify-between items-baseline gap-3">
          <span class="text-xs text-slate-500 dark:text-slate-400">Product price</span>
          <div class="text-right leading-tight">
            <span
              v-if="showStrikethroughBase"
              class="text-xs text-slate-400 line-through mr-1.5"
            >${{ formatNum(percentDiscountBasePerTon) }}</span>
            <span class="text-sm font-semibold text-slate-800 dark:text-slate-100 font-mono">
              ${{ formatNum(effectiveProductPerTon) }}/T
            </span>
          </div>
        </div>
        <p
          v-if="volumePricingBadge"
          class="text-xs text-emerald-700 dark:text-emerald-400"
        >
          {{ volumePricingBadge }}
        </p>
      </div>

      <div v-if="estimatedRange && validVolume" class="mt-4 p-4 bg-[#2962ff] dark:bg-blue-700 rounded-lg text-white">
        <div class="text-[10px] font-bold uppercase tracking-widest opacity-90 mb-1">Estimated Range (Delivered)</div>
        <div class="text-xl font-black tracking-tight">{{ estimatedRange }}</div>
        <div class="text-sm font-medium mt-1 opacity-95">~${{ formatNum(approxPerTonDelivered) }} per ton</div>
        <p class="text-[10px] mt-2 opacity-80 leading-relaxed">
          Includes product and estimated freight. Final price confirmed on quote.
        </p>
      </div>

      <details
        v-if="productBasePerTon != null && validVolume"
        class="mt-4 group"
      >
        <summary class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest cursor-pointer select-none hover:text-slate-700 dark:hover:text-slate-300 list-none flex items-center gap-1">
          <span class="transition-transform group-open:rotate-90">›</span>
          View price breakdown
        </summary>
        <div class="mt-3 space-y-2 text-[10px] font-bold text-slate-600 dark:text-slate-400 uppercase tracking-widest pl-3 border-l-2 border-slate-200 dark:border-slate-600">
          <div class="flex justify-between items-center gap-4">
            <span>Product</span>
            <span class="text-slate-800 dark:text-slate-200 font-mono shrink-0">${{ formatNum(effectiveProductPerTon) }}/T</span>
          </div>
          <div v-if="matchedVolumeTier && volumeTierIsFixed" class="flex justify-between items-center gap-4 normal-case font-medium text-slate-500">
            <span>Volume tier ({{ activeVolumeTierLabel }})</span>
          </div>
          <div v-else-if="volumeDiscountPercent > 0" class="flex justify-between items-center gap-4">
            <span>Volume discount</span>
            <span class="text-emerald-700 dark:text-emerald-400 font-mono shrink-0">−{{ volumeDiscountPercent }}%</span>
          </div>
          <div v-if="productSubtotal != null" class="flex justify-between items-center gap-4 normal-case font-medium text-slate-500">
            <span>Product total ({{ orderVolume }} T)</span>
            <span class="font-mono shrink-0">${{ formatNum(productSubtotal) }}</span>
          </div>
          <div class="flex justify-between items-center gap-4">
            <span>Est. freight</span>
            <span class="text-amber-600 dark:text-amber-400 normal-case font-medium shrink-0">$200–$400/T</span>
          </div>
        </div>
      </details>

      <router-link
        :to="requestQuoteLink"
        class="mt-6 w-full min-h-[48px] flex items-center justify-center bg-[#2962ff] dark:bg-blue-600 text-white font-bold text-[11px] uppercase tracking-wider rounded hover:bg-blue-800 dark:hover:bg-blue-700 transition-colors"
      >
        Request Formal Full Quote
      </router-link>

      <p class="mt-4 text-[10px] text-slate-500 dark:text-slate-400 leading-relaxed">
        Calculations are based on 53ft Van/Flatbed logistics. Prices fluctuate daily based on PNW freight indexes and terminal surcharges. Official quote required for binding contract.
      </p>
    </aside>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import { useRoute } from 'vue-router';
import api from '../../services/api';
import PageLoader from '../../components/ui/PageLoader.vue';

const props = defineProps({
  productId: { type: Number, default: null },
  productSlug: { type: String, default: null }
});

const route = useRoute();
const product = ref(null);
const loading = ref(true);
const allProducts = ref([]);

const orderVolume = ref(24);
const selectedPackagingOptionId = ref(null);
const destinationZip = ref('46204');
const requiresLiftgate = ref(false);

const apiBaseUrl = api.defaults.baseURL || '';

const catalogLink = computed(() => {
  const inApp = route.path.startsWith('/app');
  return { name: inApp ? 'AppCatalog' : 'Catalog', query: {} };
});

const primaryCategory = computed(() => {
  const p = product.value;
  if (!p?.categories?.length) return null;
  return p.categories[0];
});

const categoryLabel = computed(() => {
  if (primaryCategory.value) return primaryCategory.value.label;
  return 'Commodity';
});

const allCategories = computed(() => {
  const map = new Map();
  allProducts.value.forEach(p => {
    (p.categories || []).forEach(c => {
      if (!map.has(c.id)) map.set(c.id, c);
    });
  });
  return Array.from(map.values()).sort((a, b) => a.label.localeCompare(b.label));
});

const packagingSummary = computed(() => {
  if (!product.value?.packaging_options?.length) return 'Bags, Totes, Bulk Truck';
  return product.value.packaging_options.map(p => p.type_name).join(', ');
});

const leadTimeText = computed(() => {
  const p = product.value;
  if (!p) return '—';
  if (p.lead_time || p.stock_status === 'in_stock') return 'Stocked / Immediate';
  return p.availability || '—';
});

const technicalQaAnswer = computed(() => {
  const p = product.value;
  if (!p) return '';
  if (p.description && p.description.length < 200) return p.description;
  return 'Calcium and phosphorus supplement for bone development and metabolism.';
});

const selectedPackaging = computed(() => {
  if (!product.value?.packaging_options?.length) return null;
  const opts = product.value.packaging_options;
  const id = Number(selectedPackagingOptionId.value);
  if (!Number.isNaN(id)) {
    const match = opts.find(p => Number(p.id) === id);
    if (match) return match;
  }
  return opts[0];
});

const hasPresentations = computed(() => (product.value?.packaging_options?.length ?? 0) > 0);

/** Base $/T of the selected presentation (always used for % tier math). */
const selectedPresentationBasePerTon = computed(() => {
  const pack = selectedPackaging.value;
  if (!pack) return null;
  const n = Number(pack.base_price_per_unit);
  return Number.isFinite(n) ? n : null;
});

/** Product-level base — only when there are no presentations. */
const fallbackProductBasePerTon = computed(() => {
  if (hasPresentations.value) return null;
  const p = product.value;
  if (p?.base_price_ref != null) return Number(p.base_price_ref);
  return selectedPresentationBasePerTon.value;
});

/** Display / no-tier pricing. */
const presentationBasePerTon = computed(() => {
  if (hasPresentations.value) {
    return selectedPresentationBasePerTon.value;
  }
  return fallbackProductBasePerTon.value;
});

const productBasePerTon = computed(() => presentationBasePerTon.value);

const presentationTierPricingMode = computed(() => {
  const tiers = selectedPackaging.value?.volume_tiers;
  if (!tiers?.length) return null;
  const first = tiers[0]?.pricing_mode;
  return first === 'fixed_price' ? 'fixed_price' : 'percentage';
});

const percentDiscountBasePerTon = computed(() => selectedPresentationBasePerTon.value);

const validVolume = computed(() => {
  const v = Number(orderVolume.value);
  return !Number.isNaN(v) && v >= 1;
});

const matchedVolumeTier = computed(() => {
  const pack = selectedPackaging.value;
  if (!pack?.volume_tiers?.length || !validVolume.value) return null;
  return resolveMatchedVolumeTier(orderVolume.value, pack.volume_tiers);
});

const volumeTierIsFixed = computed(() => {
  const tier = matchedVolumeTier.value;
  return tier != null && (tier.pricing_mode || 'percentage') === 'fixed_price';
});

const volumeDiscountPercent = computed(() => {
  const tier = matchedVolumeTier.value;
  if (!tier || (tier.pricing_mode || 'percentage') !== 'percentage') return 0;
  return Math.max(0, Number(tier.discount_percentage) || 0);
});

const volumeDiscountPerTon = computed(() => {
  const base = percentDiscountBasePerTon.value;
  if (base == null || volumeDiscountPercent.value <= 0) return 0;
  return base * (volumeDiscountPercent.value / 100);
});

const effectiveProductPerTon = computed(() => {
  const tier = matchedVolumeTier.value;
  if (tier) {
    const baked = Number(tier.customer_price_per_ton);
    if (!Number.isNaN(baked) && baked >= 0) {
      return baked;
    }
    if ((tier.pricing_mode || 'percentage') === 'fixed_price') {
      const fixed = Number(tier.fixed_price);
      return !Number.isNaN(fixed) && fixed >= 0 ? fixed : null;
    }
    const base = percentDiscountBasePerTon.value;
    if (base == null) return null;
    const disc = Math.max(0, Number(tier.discount_percentage) || 0);
    return base * (1 - disc / 100);
  }

  return presentationBasePerTon.value;
});

const activeVolumeTierLabel = computed(() => {
  const tier = matchedVolumeTier.value;
  if (!tier) return null;
  const max = tier.max_quantity != null && tier.max_quantity !== '' ? tier.max_quantity : '∞';
  return `${tier.min_quantity}–${max} T`;
});

const showStrikethroughBase = computed(() => {
  const base = percentDiscountBasePerTon.value;
  const effective = effectiveProductPerTon.value;
  if (base == null || effective == null || !matchedVolumeTier.value) return false;
  if (presentationTierPricingMode.value === 'fixed_price') return false;
  return Math.abs(base - effective) > 0.001;
});

const volumePricingBadge = computed(() => {
  const tier = matchedVolumeTier.value;
  if (!tier || !validVolume.value) return null;
  if (volumeTierIsFixed.value) {
    return `Volume pricing · ${activeVolumeTierLabel.value}`;
  }
  if (volumeDiscountPercent.value > 0) {
    const packName = selectedPackaging.value?.type_name;
    const base = percentDiscountBasePerTon.value;
    const suffix = packName && base != null
      ? ` · ${packName} base $${formatNum(base)}/T`
      : '';
    return `${volumeDiscountPercent.value}% volume discount applied${suffix}`;
  }
  return null;
});

const productSubtotal = computed(() => {
  const price = effectiveProductPerTon.value;
  if (price == null || !validVolume.value) return null;
  return price * Math.max(1, Number(orderVolume.value));
});

const LOGISTICS_LOW_PER_TON = 200;
const LOGISTICS_HIGH_PER_TON = 400;

const deliveredEstimate = computed(() => {
  const productPerTon = effectiveProductPerTon.value;
  if (productPerTon == null || !validVolume.value) return null;
  const qty = Math.max(1, Number(orderVolume.value));
  const logisticsMid = (LOGISTICS_LOW_PER_TON + LOGISTICS_HIGH_PER_TON) / 2;
  return {
    totalLow: (productPerTon + LOGISTICS_LOW_PER_TON) * qty,
    totalHigh: (productPerTon + LOGISTICS_HIGH_PER_TON) * qty,
    perTonMid: productPerTon + logisticsMid,
  };
});

/** Midpoint of the logistics band ($200–$400/T), aligned with the range endpoints. */
const approxPerTonDelivered = computed(() => deliveredEstimate.value?.perTonMid ?? null);

const estimatedRange = computed(() => {
  const est = deliveredEstimate.value;
  if (!est) return null;
  return `$${formatNum(est.totalLow)} - $${formatNum(est.totalHigh)}`;
});

function formatNum(n) {
  if (n == null || Number.isNaN(n)) return '0.00';
  return Number(n).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

/**
 * Match order volume (tons) to the most specific volume tier for the selected packaging.
 */
function resolveMatchedVolumeTier(quantityTons, tiers) {
  const qty = Number(quantityTons);
  if (Number.isNaN(qty) || qty < 1 || !Array.isArray(tiers) || tiers.length === 0) {
    return null;
  }

  const normalized = tiers
    .map((tier) => {
      const min = Number(tier.min_quantity ?? 0);
      const maxRaw = tier.max_quantity;
      const maxNum = maxRaw != null && maxRaw !== '' ? Number(maxRaw) : null;
      const max = maxNum != null && !Number.isNaN(maxNum) && maxNum > 0 ? maxNum : null;

      return { ...tier, min, max };
    })
    .filter((tier) => !Number.isNaN(tier.min))
    .sort((a, b) => b.min - a.min);

  for (const tier of normalized) {
    if (qty < tier.min) continue;
    if (tier.max != null && qty > tier.max) continue;
    return tier;
  }

  const byMinAsc = [...normalized].sort((a, b) => a.min - b.min);
  const last = byMinAsc[byMinAsc.length - 1];
  if (last && qty >= last.min) {
    return last;
  }

  return null;
}

const requestQuoteLink = computed(() => {
  const inApp = route.path.startsWith('/app');
  const name = inApp ? 'AppRequestQuote' : 'RequestQuote';
  return {
    name,
    query: {
      productId: product.value?.id,
      quantity: validVolume.value ? orderVolume.value : 24,
      packaging_type_id: selectedPackaging.value?.packaging_type_id,
      delivery_zip: destinationZip.value || undefined,
      requires_liftgate: requiresLiftgate.value ? '1' : undefined
    }
  };
});

function documentUrl(type) {
  if (!product.value) return '#';
  return `${apiBaseUrl}/api/v1/catalog/products/${product.value.id}/documents/${type}`;
}

async function fetchProduct() {
  const slug = props.productSlug?.trim();
  const id = props.productId != null && !Number.isNaN(Number(props.productId)) ? Number(props.productId) : null;
  if (!slug && (id == null || id < 1)) {
    loading.value = false;
    return;
  }
  loading.value = true;
  product.value = null;
  try {
    const url = slug
      ? `/api/v1/catalog/products/by-slug/${encodeURIComponent(slug)}`
      : `/api/v1/catalog/products/${id}`;
    const { data } = await api.get(url);
    const raw = data?.data ?? data;
    product.value = raw;
    const opts = raw?.packaging_options || [];
    if (opts.length) {
      const currentId = Number(selectedPackagingOptionId.value);
      if (
        selectedPackagingOptionId.value == null
        || Number.isNaN(currentId)
        || !opts.some(p => Number(p.id) === currentId)
      ) {
        selectedPackagingOptionId.value = opts[0].id;
      }
    }
  } catch {
    product.value = null;
  } finally {
    loading.value = false;
  }
}

async function fetchAllProducts() {
  try {
    const { data } = await api.get('/api/v1/catalog/products');
    allProducts.value = data?.data ?? data ?? [];
  } catch (e) {
    console.error('Failed to load categories', e);
  }
}

watch(() => [props.productSlug, props.productId], fetchProduct, { immediate: true });
watch(
  () => [props.productSlug, props.productId],
  () => { fetchAllProducts(); },
  { immediate: true }
);
</script>
