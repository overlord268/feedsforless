<template>
  <div class="space-y-5">
    <div
      v-if="successMessage"
      class="p-3 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm font-medium"
    >
      {{ successMessage }}
    </div>

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
      <div>
        <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">Profit margins</h1>
        <p class="text-slate-500 mt-0.5 text-sm">
          Internal reference — markup is baked into customer-facing prices. With volume tiers, margin applies per tier (not the locked base field).
        </p>
      </div>
      <router-link
        :to="{ name: 'AdminProducts' }"
        class="inline-flex items-center justify-center px-4 py-2.5 border border-slate-200 text-slate-700 font-medium rounded-xl hover:bg-slate-50 transition-colors text-sm"
      >
        ← Back to products
      </router-link>
    </div>

    <div class="p-4 rounded-xl bg-slate-50 border border-slate-200 text-sm text-slate-600 space-y-1">
      <p><strong>How pricing is calculated</strong></p>
      <ul class="list-disc pl-5 space-y-0.5">
        <li><strong>No tiers:</strong> margin % on the presentation base price.</li>
        <li><strong>Fixed $ tiers:</strong> margin % on each tier price (base field is locked in product edit).</li>
        <li><strong>Per tier:</strong> optional margin override by quantity range (falls back to product → global).</li>
      </ul>
    </div>

    <section class="p-5 rounded-2xl bg-white border border-slate-200/80 shadow-card space-y-4">
      <div>
        <h2 class="text-sm font-semibold text-slate-800 uppercase tracking-wider">Global margin</h2>
        <p class="text-sm text-slate-500 mt-1">Applies to all products without a custom margin.</p>
      </div>
      <div class="flex flex-wrap items-end gap-3">
        <div>
          <label class="block text-xs font-medium text-slate-500 mb-1">Default margin (%)</label>
          <div class="flex items-center gap-2">
            <input
              v-model.number="globalMarginInput"
              type="number"
              min="0"
              max="999"
              step="0.01"
              class="w-28 px-3 py-2 border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
            >
            <span class="text-slate-500 text-sm">%</span>
          </div>
        </div>
        <button
          type="button"
          class="px-4 py-2 bg-emerald-600 text-white text-sm font-medium rounded-lg hover:bg-emerald-700 disabled:opacity-50"
          :disabled="savingGlobal"
          @click="saveGlobalMargin"
        >
          {{ savingGlobal ? 'Saving…' : 'Save global' }}
        </button>
        <p class="text-sm text-slate-600 w-full sm:w-auto">
          Current effective global: <strong>{{ formatPercent(globalMargin) }}</strong>
        </p>
      </div>
    </section>

    <div class="flex flex-wrap gap-2 items-center">
      <div class="flex gap-1 p-1 bg-slate-200/80 rounded-xl w-fit">
        <button
          v-for="tab in statusTabs"
          :key="tab.id"
          type="button"
          :class="['px-4 py-2 rounded-lg text-sm font-medium transition-colors', statusFilter === tab.id ? 'bg-white text-slate-800 shadow-sm' : 'text-slate-600 hover:text-slate-800']"
          @click="changeStatusFilter(tab.id)"
        >
          {{ tab.label }}
        </button>
      </div>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-card overflow-hidden min-h-[420px] flex flex-col">
      <TableSearchToolbar
        v-model="searchQuery"
        title="Product margins"
        search-placeholder="Search products on this page…"
        :filtered-count="tableProducts.length"
        :total-count="products.length"
        item-label="products"
      />
      <div v-if="loading" class="py-16 flex justify-center text-slate-500">Loading…</div>
      <div v-else class="overflow-x-auto table-scroll flex-1 min-h-0">
        <table class="w-full text-sm min-w-[980px]">
          <thead class="bg-slate-50/80 border-b border-slate-200">
            <tr class="text-left">
              <th class="px-4 py-2.5 text-[11px] font-semibold text-slate-500 uppercase tracking-wider w-[220px] cursor-pointer select-none hover:text-slate-700" @click="toggleSort('name')">
                <span class="inline-flex items-center gap-1">Product <TableSortIcon :active="sort.key === 'name'" :dir="sort.dir" /></span>
              </th>
              <th class="px-4 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider">Pricing &amp; margins</th>
              <th class="px-4 py-3.5 w-28 text-right text-xs font-semibold text-slate-500 uppercase tracking-wider">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            <tr
              v-for="row in tableProducts"
              :key="row.id"
              class="product-row transition-colors duration-200 align-top"
              :class="row.deleted_at ? 'bg-red-50/40' : 'hover:bg-slate-50/50'"
            >
              <td class="px-4 py-4">
                <div class="font-mono text-xs text-slate-500">{{ row.sku }}</div>
                <div class="font-semibold text-slate-900 leading-snug">{{ row.name }}</div>
                <div class="text-xs text-slate-400 mt-1 capitalize">{{ row.status }}</div>
              </td>
              <td class="px-4 py-4">
                <div v-if="!previewGroups(row).length" class="text-slate-400 text-sm">No pricing configured</div>
                <div v-else class="space-y-3">
                  <div
                    v-for="group in previewGroups(row)"
                    :key="group.presentation_id ?? group.label"
                    class="pricing-panel rounded-2xl border border-slate-200/90 bg-white shadow-sm overflow-hidden transition-shadow duration-300 hover:shadow-md"
                    :class="{ 'ring-1 ring-emerald-200/60': isPanelExpanded(row.id, group) }"
                  >
                    <!-- Collapsible header (tiers) -->
                    <button
                      v-if="group.has_tiers && group.tiers?.length"
                      type="button"
                      class="w-full px-4 py-3 flex items-center gap-3 text-left bg-gradient-to-r from-slate-50 to-white border-b border-slate-100 transition-colors duration-200 hover:from-slate-100/80 hover:to-slate-50/90 group/header"
                      :aria-expanded="isPanelExpanded(row.id, group)"
                      @click="togglePanel(row.id, group)"
                    >
                      <span
                        class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-white border border-slate-200 text-slate-500 shadow-sm transition-all duration-300 group-hover/header:border-emerald-300 group-hover/header:text-emerald-600"
                        :class="{ 'border-emerald-200 text-emerald-600 bg-emerald-50': isPanelExpanded(row.id, group) }"
                      >
                        <svg
                          class="w-4 h-4 transition-transform duration-300 ease-out"
                          :class="{ 'rotate-90': isPanelExpanded(row.id, group) }"
                          fill="none"
                          stroke="currentColor"
                          viewBox="0 0 24 24"
                        >
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                      </span>
                      <div class="flex-1 min-w-0">
                        <div class="flex flex-wrap items-center gap-2">
                          <span class="text-xs font-bold text-slate-800 uppercase tracking-wider">{{ group.label }}</span>
                          <span class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-semibold bg-slate-100 text-slate-600 ring-1 ring-slate-200">
                            {{ group.tiers.length }} {{ group.tiers.length === 1 ? 'tier' : 'tiers' }}
                          </span>
                        </div>
                        <p class="text-[11px] text-slate-500 mt-0.5 truncate">
                          {{ isPanelExpanded(row.id, group) ? 'Tap to hide tier breakdown' : `Tap to view breakdown · ${groupSummary(group)}` }}
                        </p>
                      </div>
                      <div class="text-right shrink-0 hidden sm:block">
                        <div class="text-[10px] font-semibold uppercase tracking-wider text-slate-400">From</div>
                        <div class="text-sm font-bold tabular-nums text-emerald-700">{{ groupSummary(group) }}</div>
                      </div>
                    </button>

                    <!-- Static header (no tiers) -->
                    <div
                      v-else-if="group.base_price != null"
                      class="px-4 py-2.5 border-b border-slate-100 bg-gradient-to-r from-slate-50 to-white"
                    >
                      <span class="text-xs font-bold text-slate-700 uppercase tracking-wider">{{ group.label }}</span>
                    </div>

                    <!-- Single base (no tiers) — always visible -->
                    <div v-if="!group.has_tiers && group.base_price != null" class="p-4 animate-fade-in">
                      <div class="grid grid-cols-[1fr_auto_1fr] gap-3 items-center text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-2">
                        <span>Cost base</span>
                        <span>Margin</span>
                        <span class="text-right">Customer price</span>
                      </div>
                      <div class="grid grid-cols-[1fr_auto_1fr] gap-3 items-center">
                        <span class="text-sm font-medium tabular-nums text-slate-700">${{ formatPrice(group.base_price) }}/T</span>
                        <div class="flex items-center gap-1.5">
                          <input
                            v-model="rowDrafts[row.id].marginInput"
                            type="number"
                            min="0"
                            max="999"
                            step="0.01"
                            class="w-16 px-2 py-1.5 border border-slate-200 rounded-lg text-sm text-center focus:ring-2 focus:ring-emerald-500 transition-shadow"
                            @input="onRowMarginInput(row)"
                          >
                          <span class="text-slate-400 text-xs">%</span>
                        </div>
                        <span class="text-right text-base font-bold tabular-nums text-emerald-700">
                          ${{ formatPrice(group.price_with_margin) }}/T
                        </span>
                      </div>
                      <div class="mt-3 flex flex-wrap items-center gap-2">
                        <span
                          class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-semibold ring-1"
                          :class="marginSourceBadgeClass(rowDrafts[row.id].source === 'product' ? 'product' : 'global')"
                        >
                          {{ rowDrafts[row.id].source === 'product' ? 'Custom' : 'Global' }}
                        </span>
                        <button
                          v-if="rowDrafts[row.id].dirty"
                          type="button"
                          class="px-2.5 py-1 text-xs font-medium rounded-lg bg-emerald-600 text-white hover:bg-emerald-700 transition-colors"
                          :disabled="rowDrafts[row.id].saving"
                          @click="saveRowMargin(row)"
                        >
                          Save
                        </button>
                        <button
                          v-if="row.profit_margin_percent != null"
                          type="button"
                          class="text-xs text-slate-500 hover:text-emerald-700 underline transition-colors"
                          @click="resetToGlobal(row)"
                        >
                          Use global
                        </button>
                      </div>
                    </div>

                    <!-- Volume tiers — collapsible body -->
                    <Transition name="accordion">
                      <div
                        v-if="group.has_tiers && group.tiers?.length && isPanelExpanded(row.id, group)"
                        class="accordion-body"
                      >
                        <div class="p-4 space-y-3 border-t border-slate-100/80 bg-gradient-to-b from-white to-slate-50/40">
                          <div class="grid grid-cols-[minmax(88px,1fr)_minmax(120px,1.2fr)_minmax(100px,1fr)] gap-3 items-center text-[10px] font-bold uppercase tracking-wider text-slate-400 px-0.5">
                            <span>Range</span>
                            <span>Margin %</span>
                            <span class="text-right">Customer price</span>
                          </div>
                          <TransitionGroup name="tier-row" tag="div" class="space-y-2">
                            <div
                              v-for="(tier, tierIdx) in group.tiers"
                              :key="tier.id"
                              class="tier-row grid grid-cols-[minmax(88px,1fr)_minmax(120px,1.2fr)_minmax(100px,1fr)] gap-3 items-center rounded-xl border border-slate-100/90 bg-white px-3 py-2.5 shadow-sm transition-all duration-200 hover:border-emerald-200/80 hover:shadow"
                            >
                              <div>
                                <div class="text-sm font-semibold tabular-nums text-slate-800">{{ tier.quantity_range }}</div>
                                <div v-if="tier.internal_base_price != null" class="text-[10px] text-slate-400 mt-0.5">
                                  Cost {{ tier.pricing_mode === 'fixed_price' ? '$' + formatPrice(tier.internal_base_price) : formatPrice(tier.discount_percentage) + '% off' }}
                                </div>
                              </div>
                              <div class="flex flex-wrap items-center gap-1.5">
                                <input
                                  v-model="rowDrafts[row.id].tierDrafts[tier.id].marginInput"
                                  type="number"
                                  min="0"
                                  max="999"
                                  step="0.01"
                                  class="w-16 px-2 py-1.5 border border-slate-200 rounded-lg text-sm text-center focus:ring-2 focus:ring-violet-500 transition-shadow"
                                  @input="onTierMarginInput(row, tier.id)"
                                  @click.stop
                                >
                                <span class="text-slate-400 text-xs">%</span>
                                <span
                                  class="inline-flex px-1.5 py-0.5 rounded-full text-[10px] font-semibold ring-1"
                                  :class="marginSourceBadgeClass(tier.margin_source)"
                                >
                                  {{ marginSourceLabel(tier.margin_source) }}
                                </span>
                                <button
                                  v-if="rowDrafts[row.id].tierDrafts[tier.id]?.dirty"
                                  type="button"
                                  class="px-2 py-0.5 text-[10px] font-semibold rounded-md bg-emerald-600 text-white hover:bg-emerald-700 disabled:opacity-50 transition-colors"
                                  :disabled="rowDrafts[row.id].tierDrafts[tier.id]?.saving"
                                  @click.stop="saveTierMargin(row, tier.id)"
                                >
                                  Save
                                </button>
                                <button
                                  v-if="tier.tier_profit_margin_percent != null || rowDrafts[row.id].tierDrafts[tier.id]?.isCustom"
                                  type="button"
                                  class="text-[10px] text-slate-500 hover:text-emerald-700 underline"
                                  @click.stop="resetTierMargin(row, tier.id)"
                                >
                                  Default
                                </button>
                              </div>
                              <div class="text-right">
                                <div class="text-base font-bold tabular-nums text-emerald-700">
                                  ${{ formatPrice(tier.price_with_margin) }}/T
                                </div>
                              </div>
                            </div>
                          </TransitionGroup>

                          <div v-if="hasTiers(row)" class="pt-3 mt-1 border-t border-dashed border-slate-200">
                            <p class="text-[10px] font-semibold uppercase tracking-wider text-slate-400 mb-2">Product default (fallback)</p>
                            <div class="flex flex-wrap items-center gap-2">
                              <input
                                v-model="rowDrafts[row.id].marginInput"
                                type="number"
                                min="0"
                                max="999"
                                step="0.01"
                                class="w-16 px-2 py-1.5 border border-slate-200 rounded-lg text-sm text-center"
                                @input="onRowMarginInput(row)"
                                @click.stop
                              >
                              <span class="text-slate-400 text-xs">%</span>
                              <button
                                v-if="rowDrafts[row.id].dirty"
                                type="button"
                                class="px-2 py-0.5 text-[10px] font-semibold rounded-md bg-slate-700 text-white hover:bg-slate-800 transition-colors"
                                :disabled="rowDrafts[row.id].saving"
                                @click.stop="saveRowMargin(row)"
                              >
                                Save default
                              </button>
                            </div>
                          </div>
                        </div>
                      </div>
                    </Transition>
                  </div>
                </div>
              </td>
              <td class="px-4 py-4 text-right">
                <router-link
                  :to="{ name: 'AdminProductEdit', params: { id: row.id } }"
                  class="inline-block text-xs font-medium text-emerald-600 hover:text-emerald-800"
                >
                  Edit product
                </router-link>
              </td>
            </tr>
            <tr v-if="!products.length">
              <td colspan="3" class="px-4 py-12 text-center text-slate-500">No products found.</td>
            </tr>
          </tbody>
        </table>
      </div>

      <div v-if="meta.last_page > 1" class="flex items-center justify-between px-4 py-3 border-t border-slate-100 text-sm text-slate-600">
        <span>Page {{ meta.current_page }} of {{ meta.last_page }} ({{ meta.total }} products)</span>
        <div class="flex gap-2">
          <button
            type="button"
            class="px-3 py-1.5 rounded-lg border border-slate-200 disabled:opacity-40"
            :disabled="meta.current_page <= 1"
            @click="goPage(meta.current_page - 1)"
          >
            Previous
          </button>
          <button
            type="button"
            class="px-3 py-1.5 rounded-lg border border-slate-200 disabled:opacity-40"
            :disabled="meta.current_page >= meta.last_page"
            @click="goPage(meta.current_page + 1)"
          >
            Next
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue';
import api from '../../services/api';
import { useToast } from '../../composables/useToast';
import { useSortableTable } from '../../composables/useSortableTable';
import TableSearchToolbar from '../../components/admin/TableSearchToolbar.vue';
import TableSortIcon from '../../components/admin/TableSortIcon.vue';
import {
  marginSourceBadgeClass,
  marginSourceLabel,
  previewPresentationGroups,
  productHasTiers,
} from '../../composables/useProfitMargin';

const { showToast } = useToast();

const products = ref([]);
const loading = ref(true);

const {
  searchQuery,
  sort,
  processedItems: tableProducts,
  toggleSort,
} = useSortableTable(products, {
  defaultSort: { key: 'name', dir: 'asc' },
  getSortValue: (row, key) => (key === 'name' ? row.name || row.sku || '' : row[key]),
  getSearchText: (row) => [row.sku, row.name, row.status].join(' '),
});
const savingGlobal = ref(false);
const successMessage = ref('');
const globalMargin = ref(15);
const globalMarginInput = ref(15);
const statusFilter = ref('all');
const page = ref(1);
const meta = ref({ current_page: 1, last_page: 1, total: 0, per_page: 25 });
const rowDrafts = reactive({});
const expandedPanels = reactive({});

function panelKey(rowId, group) {
  return `${rowId}-${group.presentation_id ?? group.label}`;
}

function isPanelExpanded(rowId, group) {
  return !!expandedPanels[panelKey(rowId, group)];
}

function togglePanel(rowId, group) {
  const key = panelKey(rowId, group);
  expandedPanels[key] = !expandedPanels[key];
}

function groupSummary(group) {
  if (!group.has_tiers || !group.tiers?.length) {
    if (group.price_with_margin != null) {
      return `$${formatPrice(group.price_with_margin)}/T`;
    }
    return '—';
  }
  const prices = group.tiers.map((t) => t.price_with_margin).filter((p) => p != null && !Number.isNaN(Number(p)));
  if (!prices.length) {
    return `${group.tiers.length} tiers · click to expand`;
  }
  const min = Math.min(...prices.map(Number));
  const max = Math.max(...prices.map(Number));
  if (Math.abs(min - max) < 0.01) {
    return `$${formatPrice(min)}/T`;
  }
  return `$${formatPrice(min)} – $${formatPrice(max)}/T`;
}

const statusTabs = [
  { id: 'all', label: 'All' },
  { id: 'active', label: 'Active' },
  { id: 'deleted', label: 'Deleted' },
  { id: 'draft', label: 'Draft' },
  { id: 'published', label: 'Published' },
];

function formatPrice(val) {
  if (val == null) return '—';
  return new Intl.NumberFormat('en', { style: 'decimal', minimumFractionDigits: 2 }).format(val);
}

function formatPercent(val) {
  return `${formatPrice(val)}%`;
}

function initRowDraft(row) {
  const hasCustom = row.profit_margin_percent != null;
  const tierDrafts = {};
  (row.presentation_groups || []).forEach((group) => {
    (group.tiers || []).forEach((tier) => {
      tierDrafts[tier.id] = {
        marginInput: tier.tier_profit_margin_percent != null
          ? tier.tier_profit_margin_percent
          : tier.effective_margin_percent,
        isCustom: tier.tier_profit_margin_percent != null,
        dirty: false,
        saving: false,
      };
    });
  });
  rowDrafts[row.id] = {
    marginInput: hasCustom ? row.profit_margin_percent : row.effective_margin_percent,
    source: row.margin_source,
    dirty: false,
    saving: false,
    isCustom: hasCustom,
    tierDrafts,
  };
}

function mergeProductRow(row, data) {
  Object.assign(row, data);
  initRowDraft(row);
}

function hasTiers(row) {
  return productHasTiers(row);
}

function previewGroups(row) {
  const draft = rowDrafts[row.id];
  if (!draft || !row.presentation_groups?.length) return [];
  return previewPresentationGroups(row.presentation_groups, globalMargin.value, draft);
}

function onRowMarginInput(row) {
  const draft = rowDrafts[row.id];
  if (!draft) return;
  draft.dirty = true;
  draft.isCustom = true;
  draft.source = 'product';
}

function onTierMarginInput(row, tierId) {
  const td = rowDrafts[row.id]?.tierDrafts?.[tierId];
  if (!td) return;
  td.dirty = true;
  td.isCustom = true;
}

async function saveTierMargin(row, tierId) {
  const td = rowDrafts[row.id]?.tierDrafts?.[tierId];
  if (!td) return;
  td.saving = true;
  try {
    const { data } = await api.patch(`/api/v1/admin/pricing/tiers/${tierId}`, {
      profit_margin_percent: td.marginInput,
    });
    mergeProductRow(row, data.data);
    showToast('Tier margin saved.', 'success');
  } catch (e) {
    const msg = e.response?.data?.errors?.profit_margin_percent?.[0]
      || e.response?.data?.message
      || 'Failed to save tier margin.';
    showToast(msg, 'error');
    initRowDraft(row);
  } finally {
    td.saving = false;
  }
}

async function resetTierMargin(row, tierId) {
  const td = rowDrafts[row.id]?.tierDrafts?.[tierId];
  if (!td) return;
  td.saving = true;
  try {
    const { data } = await api.patch(`/api/v1/admin/pricing/tiers/${tierId}`, {
      profit_margin_percent: null,
    });
    mergeProductRow(row, data.data);
    showToast('Tier margin reset to default.', 'success');
  } catch (e) {
    showToast(e.response?.data?.message || 'Failed to reset tier margin.', 'error');
  } finally {
    td.saving = false;
  }
}

async function loadGlobalMargin() {
  const { data } = await api.get('/api/v1/admin/pricing/margins');
  globalMargin.value = data.data.global_profit_margin_percent;
  globalMarginInput.value = globalMargin.value;
}

async function loadProducts() {
  loading.value = true;
  try {
    const params = { page: page.value, per_page: 25 };
    if (statusFilter.value !== 'all') params.status = statusFilter.value;
    const { data } = await api.get('/api/v1/admin/pricing/products', { params });
    products.value = data.data;
    meta.value = data.meta;
    products.value.forEach(initRowDraft);
  } catch (e) {
    showToast(e.response?.data?.message || 'Failed to load products.', 'error');
  } finally {
    loading.value = false;
  }
}

async function saveGlobalMargin() {
  savingGlobal.value = true;
  try {
    const { data } = await api.put('/api/v1/admin/pricing/margins/global', {
      profit_margin_percent: globalMarginInput.value,
    });
    globalMargin.value = data.data.global_profit_margin_percent;
    globalMarginInput.value = globalMargin.value;
    successMessage.value = 'Global margin saved.';
    setTimeout(() => { successMessage.value = ''; }, 3000);
    await loadProducts();
  } catch (e) {
    showToast(e.response?.data?.message || 'Failed to save global margin.', 'error');
  } finally {
    savingGlobal.value = false;
  }
}

async function saveRowMargin(row) {
  const draft = rowDrafts[row.id];
  draft.saving = true;
  try {
    const { data } = await api.patch(`/api/v1/admin/pricing/products/${row.id}`, {
      profit_margin_percent: draft.marginInput,
    });
    mergeProductRow(row, data.data);
    showToast('Product margin saved.', 'success');
  } catch (e) {
    const errors = e.response?.data?.errors;
    const msg = errors?.profit_margin_percent?.[0]
      || e.response?.data?.message
      || 'Failed to save product margin.';
    showToast(msg, 'error');
    initRowDraft(row);
  } finally {
    draft.saving = false;
  }
}

async function resetToGlobal(row) {
  const draft = rowDrafts[row.id];
  draft.saving = true;
  try {
    const { data } = await api.patch(`/api/v1/admin/pricing/products/${row.id}`, {
      profit_margin_percent: null,
    });
    mergeProductRow(row, data.data);
    showToast('Product margin reset to global.', 'success');
  } catch (e) {
    showToast(e.response?.data?.message || 'Failed to reset margin.', 'error');
  } finally {
    draft.saving = false;
  }
}

function changeStatusFilter(id) {
  statusFilter.value = id;
  page.value = 1;
  loadProducts();
}

function goPage(p) {
  page.value = p;
  loadProducts();
}

onMounted(async () => {
  try {
    await loadGlobalMargin();
    await loadProducts();
  } catch (e) {
    showToast('Failed to load pricing data.', 'error');
    loading.value = false;
  }
});
</script>

<style scoped>
.product-row {
  animation: row-fade-in 0.35s ease-out both;
}

@keyframes row-fade-in {
  from {
    opacity: 0;
    transform: translateY(4px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.animate-fade-in {
  animation: fade-in 0.3s ease-out;
}

@keyframes fade-in {
  from { opacity: 0; }
  to { opacity: 1; }
}

/* Accordion expand/collapse — gentle close, slightly richer open */
.accordion-enter-active {
  transition: opacity 0.22s ease-out, transform 0.22s ease-out;
  transform-origin: top;
}

.accordion-leave-active {
  transition: opacity 0.14s ease-in;
  transform-origin: top;
}

.accordion-enter-from {
  opacity: 0;
  transform: scaleY(0.985);
}

.accordion-enter-to {
  opacity: 1;
  transform: scaleY(1);
}

.accordion-leave-from {
  opacity: 1;
  transform: scaleY(1);
}

.accordion-leave-to {
  opacity: 0;
  transform: scaleY(1);
}

.accordion-body {
  overflow: hidden;
}

/* Tier rows — animate in only; collapse handled by accordion */
.tier-row-enter-active {
  transition: opacity 0.22s ease-out, transform 0.22s ease-out;
}

.tier-row-leave-active {
  transition: none;
}

.tier-row-enter-from {
  opacity: 0;
  transform: translateX(-4px);
}

.tier-row-enter-to {
  opacity: 1;
  transform: translateX(0);
}

.pricing-panel {
  will-change: box-shadow;
}
</style>
