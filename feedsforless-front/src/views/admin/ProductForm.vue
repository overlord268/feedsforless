<template>
  <div class="flex flex-col h-full min-h-0 bg-white rounded-2xl border border-slate-200 shadow-sm">
    <ProductFormHeader
      :is-edit="isEdit"
      :status="form.status"
      :saving="saving"
      @update:status="form.status = $event"
      @save="save"
      @discard="discard"
    />

    <ProductFormTabsNav v-model="activeTab" :tabs="tabs" />

    <div class="flex-1 overflow-y-auto p-6 min-h-0">
      <template v-if="!catalogsReady">
        <div class="flex flex-col items-center justify-center py-12 text-slate-500">
          <svg class="w-10 h-10 animate-spin text-emerald-500 mb-3" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" /><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" /></svg>
          <p class="text-sm font-medium">Loading catalogs (categories, parameters, units…)…</p>
        </div>
        <ProductFormSkeleton class="mt-8" />
      </template>
      <template v-else>
        <template v-if="isEdit && productLoading">
          <div class="flex flex-col items-center justify-center py-8 text-slate-500">
            <svg class="w-8 h-8 animate-spin text-emerald-500 mb-2" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" /><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" /></svg>
            <p class="text-sm font-medium">Loading product…</p>
          </div>
          <ProductFormSkeleton class="mt-6" />
        </template>
        <Transition v-else name="tab-fade" mode="out-in">
          <ProductFormGeneralTab
            v-if="activeTab === 0"
            :key="0"
            :form="form"
          />
          <ProductFormCategorizationTab
            v-else-if="activeTab === 1"
            :key="1"
            :form="form"
            :category-error="categoryError"
            :categories-tree="categoriesTree"
            :doc-files="docFiles"
            :product-id="productId"
            @open-add-master="openAddMaster"
            @download-document="downloadDocument"
            @preview-document="previewDocument"
          />
          <ProductFormPresentationsTab
            v-else-if="activeTab === 2"
            :key="2"
            :form="form"
            :packaging-types="packagingTypes"
            @add-presentation="addPresentation"
            @remove-presentation="removePresentation"
            @add-volume-tier="addVolumeTier"
            @remove-volume-tier="removeVolumeTier"
            @open-add-master="openAddMaster"
          />
          <ProductFormSpecsTab
            v-else-if="activeTab === 3"
            :key="3"
            :form="form"
            :nutritional-parameters="nutritionalParameters"
            :measure-units="measureUnits"
            :parameters="parameters"
            :test-methods="testMethods"
            @open-add-master="openAddMaster"
            @add-nutritional-row="addNutritionalRow"
            @remove-nutritional-row="removeNutritionalRow"
            @add-spec-row="addSpecRow"
            @remove-spec-row="removeSpecRow"
          />
          <ProductFormRelationsTab
            v-else-if="activeTab === 4"
            :key="4"
            :form="form"
            :products-for-related="productsForRelated"
            :handling-specs="handlingSpecs"
            :typical-applications="typicalApplications"
            @open-add-master="openAddMaster"
          />
        </Transition>

        <div v-if="addModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/40 backdrop-blur-sm" @click.self="closeAddModal">
          <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full p-6 max-h-[90vh] overflow-y-auto border border-slate-200/80">
            <h2 class="text-lg font-semibold text-slate-900 mb-4">{{ addModalTitle }}</h2>
            <form @submit.prevent="submitAddMaster" class="space-y-4">
              <template v-if="addModalType === 'category'">
                <FormInput v-model="addForm.label" variant="admin" label="Name (label) *" type="text" required placeholder="e.g. Ingredients" />
                <FormInput v-model="addForm.slug" variant="admin" label="Slug *" type="text" required placeholder="e.g. ingredients" />
                <div>
                  <label class="block text-sm font-medium text-slate-700 mb-1.5">Parent</label>
                  <select v-model="addForm.parent_id" class="w-full px-4 py-2.5 border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500">
                    <option :value="null">None</option>
                    <option v-for="c in catalog.categories" :key="c.id" :value="c.id">{{ c.label }}</option>
                  </select>
                </div>
              </template>
              <template v-else-if="addModalType === 'packaging_type'">
                <FormInput v-model="addForm.name" variant="admin" label="Name *" type="text" required placeholder="e.g. Bag" />
              </template>
              <template v-else-if="addModalType === 'parameter'">
                <FormInput v-model="addForm.label" variant="admin" label="Label *" type="text" required placeholder="e.g. pH" />
                <FormInput v-model="addForm.type" variant="admin" label="Type" type="text" placeholder="Optional" />
              </template>
              <template v-else-if="addModalType === 'nutritional_parameter'">
                <FormInput v-model="addForm.label" variant="admin" label="Label *" type="text" required placeholder="e.g. Protein" />
                <FormInput v-model="addForm.notation" variant="admin" label="Notation" type="text" placeholder="Optional" />
              </template>
              <template v-else-if="addModalType === 'test_method'">
                <FormInput v-model="addForm.label" variant="admin" label="Label *" type="text" required placeholder="e.g. AOCS" />
              </template>
              <template v-else-if="addModalType === 'measure_unit'">
                <FormInput v-model="addForm.label" variant="admin" label="Label *" type="text" required placeholder="e.g. Kilogram" />
                <FormInput v-model="addForm.notation" variant="admin" label="Notation" type="text" placeholder="e.g. kg" />
              </template>
              <template v-else-if="addModalType === 'handling_spec'">
                <FormInput v-model="addForm.label" variant="admin" label="Label *" type="text" required placeholder="e.g. Refrigerated" />
                <FormInput v-model="addForm.requirement" variant="admin" label="Requirement" type="text" placeholder="Optional" />
              </template>
              <template v-else-if="addModalType === 'typical_application'">
                <FormInput v-model="addForm.label" variant="admin" label="Label *" type="text" required placeholder="e.g. Animal feed" />
                <div>
                  <label class="block text-sm font-medium text-slate-700 mb-1.5">Description</label>
                  <textarea v-model="addForm.description" rows="2" class="w-full px-4 py-2.5 border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500" placeholder="Optional"></textarea>
                </div>
              </template>
              <div class="flex gap-2 justify-end pt-2">
                <button type="button" class="px-4 py-2.5 rounded-xl border border-slate-200 text-slate-700 hover:bg-slate-50 font-medium transition-colors" @click="closeAddModal">Cancel</button>
                <button type="submit" class="px-4 py-2.5 rounded-xl bg-emerald-600 text-white font-medium hover:bg-emerald-700 shadow-sm shadow-emerald-500/20 transition-colors disabled:opacity-70" :disabled="addSaving">{{ addSaving ? 'Saving…' : 'Add' }}</button>
              </div>
            </form>
          </div>
        </div>
      </template>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, watch, onMounted, defineAsyncComponent } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import api from '../../services/api';
import { useToast } from '../../composables/useToast';
import { useConfirm } from '../../composables/useConfirm';
import ProductFormHeader from '../../components/admin/ProductFormHeader.vue';
import ProductFormTabsNav from '../../components/admin/ProductFormTabsNav.vue';
import ProductFormSkeleton from '../../components/admin/ProductFormSkeleton.vue';
import FormInput from '../../components/ui/FormInput.vue';

const ProductFormGeneralTab = defineAsyncComponent(() => import('../../components/admin/ProductFormGeneralTab.vue'));
const ProductFormCategorizationTab = defineAsyncComponent(() => import('../../components/admin/ProductFormCategorizationTab.vue'));
const ProductFormPresentationsTab = defineAsyncComponent(() => import('../../components/admin/ProductFormPresentationsTab.vue'));
const ProductFormSpecsTab = defineAsyncComponent(() => import('../../components/admin/ProductFormSpecsTab.vue'));
const ProductFormRelationsTab = defineAsyncComponent(() => import('../../components/admin/ProductFormRelationsTab.vue'));

const route = useRoute();
const router = useRouter();
const activeTab = ref(0);
const saving = ref(false);
const categoryError = ref(false);
const catalogsReady = ref(false);
const productLoading = ref(false);
const addModalOpen = ref(false);
const addModalType = ref('');
const addModalContext = ref({});
const addForm = reactive({
  label: '', slug: '', parent_id: null, name: '', type: '', notation: '', requirement: '', description: '',
});
const addSaving = ref(false);

const tabs = [
  { id: 'general', label: 'General Information' },
  { id: 'categorization', label: 'Categorization and Media' },
  { id: 'presentations', label: 'Presentations and Pricing' },
  { id: 'specs', label: 'Technical Specifications' },
  { id: 'relations', label: 'Relations' },
];

const isEdit = computed(() => !!route.params.id);
const productId = computed(() => route.params.id ? Number(route.params.id) : null);

const catalog = reactive({
  categories: [],
  packagingTypes: [],
  parameters: [],
  nutritionalParameters: [],
  testMethods: [],
  measureUnits: [],
  handlingSpecs: [],
  typicalApplications: [],
  products: [],
});

const categoriesTree = computed(() => {
  const list = catalog.categories || [];
  const byId = {};
  list.forEach(c => { byId[c.id] = { ...c, children: [] }; });
  const roots = [];
  list.forEach(c => {
    const node = byId[c.id];
    if (!c.parent_id) roots.push(node);
    else if (byId[c.parent_id]) byId[c.parent_id].children.push(node);
  });
  return roots;
});

const packagingTypes = computed(() => catalog.packagingTypes);
const parameters = computed(() => catalog.parameters);
const nutritionalParameters = computed(() => catalog.nutritionalParameters);
const testMethods = computed(() => catalog.testMethods);
const measureUnits = computed(() => catalog.measureUnits);
const handlingSpecs = computed(() => catalog.handlingSpecs);
const typicalApplications = computed(() => catalog.typicalApplications);
const productsForRelated = computed(() => catalog.products.filter(p => p.id !== productId.value));

const addModalTitle = computed(() => {
  const t = {
    category: 'Add category',
    packaging_type: 'Add packaging type',
    parameter: 'Add parameter',
    nutritional_parameter: 'Add parameter (Nutritional analysis)',
    test_method: 'Add test method',
    measure_unit: 'Add measure unit',
    handling_spec: 'Add handling spec',
    typical_application: 'Add typical application',
  };
  return t[addModalType.value] || 'Add';
});

function defaultPackaging() {
  return {
    packaging_type_id: null,
    quantity_per_pallet: 40,
    base_price_per_unit: 0,
    volume_tiers: [{ tier_name: '', min_quantity: 1, max_quantity: null, discount_percentage: 0 }],
  };
}
function defaultNutritionalRow() {
  return { nutritional_parameter_id: null, value: '', measure_unit_id: null };
}
function defaultSpecRow() {
  return { parameter_id: null, test_method_id: null, specification: '', measure_unit_id: null };
}

const form = reactive({
  name: '',
  sku: '',
  grade: '',
  description: '',
  status: 'draft',
  stock_status: 'in_stock',
  base_price_ref: null,
  lead_time_days: null,
  max_lead_time_days: null,
  origin_address: '',
  shelf_life_template: '',
  category_ids: [],
  packaging: [defaultPackaging()],
  nutritional_analysis: [defaultNutritionalRow()],
  specifications: [defaultSpecRow()],
  handling_spec_ids: [],
  application_ids: [],
  related_product_ids: [],
  tds_document_path: null,
  sds_document_path: null,
  coa_document_path: null,
});

const docFiles = reactive({ tds: null, sds: null, coa: null });

function addPresentation() {
  form.packaging.push(defaultPackaging());
}
function removePresentation(index) {
  form.packaging.splice(index, 1);
}
function addVolumeTier(presIndex) {
  form.packaging[presIndex].volume_tiers.push({ tier_name: '', min_quantity: 0, max_quantity: null, discount_percentage: 0 });
}
function removeVolumeTier(presIndex, tierIndex) {
  form.packaging[presIndex].volume_tiers.splice(tierIndex, 1);
}
function addNutritionalRow() {
  form.nutritional_analysis.push(defaultNutritionalRow());
}
function removeNutritionalRow(index) {
  form.nutritional_analysis.splice(index, 1);
}
function addSpecRow() {
  form.specifications.push(defaultSpecRow());
}
function removeSpecRow(index) {
  form.specifications.splice(index, 1);
}

async function fetchCatalogs() {
  try {
    const { data } = await api.get('/api/v1/admin/catalogs');
    const raw = data?.data ?? data ?? {};
    catalog.categories = Array.isArray(raw.categories) ? raw.categories : [];
    catalog.packagingTypes = Array.isArray(raw.packaging_types) ? raw.packaging_types : [];
    catalog.parameters = Array.isArray(raw.parameters) ? raw.parameters : [];
    catalog.nutritionalParameters = Array.isArray(raw.nutritional_parameters) ? raw.nutritional_parameters : [];
    catalog.testMethods = Array.isArray(raw.test_methods) ? raw.test_methods : [];
    catalog.measureUnits = Array.isArray(raw.measure_units) ? raw.measure_units : [];
    catalog.handlingSpecs = Array.isArray(raw.handling_specs) ? raw.handling_specs : [];
    catalog.typicalApplications = Array.isArray(raw.typical_applications) ? raw.typical_applications : [];
    catalog.products = Array.isArray(raw.products) ? raw.products : [];
  } catch (e) {
    const msg = e.response?.data?.message || (e.response?.data?.errors && JSON.stringify(e.response.data.errors)) || e.message;
    useToast().error(msg || 'Failed to load catalogs. Please try again.');
  }
}

async function fetchProduct() {
  if (!productId.value) return;
  productLoading.value = true;
  try {
    const { data } = await api.get(`/api/v1/admin/products/${productId.value}`);
    const p = data.data || data;
    form.name = p.name ?? '';
    form.sku = p.sku ?? '';
    form.grade = p.grade ?? '';
    form.base_price_ref = p.base_price_ref != null ? Number(p.base_price_ref) : null;
    form.description = p.description ?? '';
    form.status = p.status ?? 'draft';
    form.stock_status = p.stock_status ?? 'in_stock';
    form.origin_address = p.origin_address ?? '';
    form.shelf_life_template = p.shelf_life_template ?? '';
    if (p.lead_time) {
      const d = new Date(p.lead_time);
      form.lead_time_days = Math.round((d - new Date()) / 86400000);
    } else {
      form.lead_time_days = null;
    }
    if (p.max_lead_time) {
      const d = new Date(p.max_lead_time);
      form.max_lead_time_days = Math.round((d - new Date()) / 86400000);
    } else {
      form.max_lead_time_days = null;
    }
    form.category_ids = (p.categories || []).map(c => c.id);
    form.handling_spec_ids = (p.handling_specs || []).map(h => h.id);
    form.application_ids = (p.typical_applications || []).map(a => a.id);
    form.related_product_ids = (p.related_product_ids || []);
    form.tds_document_path = p.tds_document_path ?? null;
    form.sds_document_path = p.sds_document_path ?? null;
    form.coa_document_path = p.coa_document_path ?? null;
    docFiles.tds = null;
    docFiles.sds = null;
    docFiles.coa = null;

    const pack = p.packaging_options || p.packaging || [];
    form.packaging = pack.length ? pack.map(pa => ({
      packaging_type_id: pa.packaging_type_id,
      quantity_per_pallet: pa.quantity_per_pallet ?? 40,
      base_price_per_unit: pa.base_price_per_unit ?? 0,
      volume_tiers: (pa.volume_tiers || []).length ? pa.volume_tiers.map(t => ({
        tier_name: t.tier_name ?? '',
        min_quantity: t.min_quantity ?? 0,
        max_quantity: t.max_quantity ?? null,
        discount_percentage: t.discount_percentage ?? 0,
      })) : [{ tier_name: '', min_quantity: 1, max_quantity: null, discount_percentage: 0 }],
    })) : [defaultPackaging()];

    const nut = p.nutritional_analysis || [];
    form.nutritional_analysis = nut.length ? nut.map(n => ({ nutritional_parameter_id: n.nutritional_parameter_id, value: String(n.value ?? ''), measure_unit_id: n.measure_unit_id })) : [defaultNutritionalRow()];
    const spec = p.specifications || [];
    form.specifications = spec.length ? spec.map(s => ({ parameter_id: s.parameter_id, test_method_id: s.test_method_id, specification: s.specification ?? '', measure_unit_id: s.measure_unit_id })) : [defaultSpecRow()];
  } catch (e) {
    const msg = e.response?.data?.message || (e.response?.data?.errors && JSON.stringify(e.response.data.errors)) || e.message;
    useToast().error(msg || 'Failed to load product. Please try again.');
  } finally {
    productLoading.value = false;
  }
}

function buildPayload() {
  const leadTime = form.lead_time_days != null ? (form.lead_time_days === 0 ? null : new Date(Date.now() + form.lead_time_days * 86400000).toISOString().slice(0, 10)) : null;
  const maxLeadTime = form.max_lead_time_days != null ? (form.max_lead_time_days === 0 ? null : new Date(Date.now() + form.max_lead_time_days * 86400000).toISOString().slice(0, 10)) : null;

  return {
    name: form.name,
    sku: form.sku,
    grade: form.grade || null,
    base_price_ref: form.base_price_ref != null ? Number(form.base_price_ref) : null,
    description: form.description || null,
    status: form.status,
    stock_status: form.stock_status,
    origin_address: form.origin_address || null,
    shelf_life_template: form.shelf_life_template || null,
    tds_document_path: form.tds_document_path || null,
    sds_document_path: form.sds_document_path || null,
    coa_document_path: form.coa_document_path || null,
    lead_time: leadTime,
    max_lead_time: maxLeadTime,
    category_ids: form.category_ids,
    handling_spec_ids: form.handling_spec_ids,
    application_ids: form.application_ids,
    related_product_ids: form.related_product_ids,
    packaging: form.packaging
      .filter(p => p.packaging_type_id != null)
      .map(p => ({
        packaging_type_id: p.packaging_type_id,
        quantity_per_pallet: p.quantity_per_pallet ?? 1,
        base_price_per_unit: Number(p.base_price_per_unit) || 0,
        volume_tiers: (p.volume_tiers || [])
          .filter(t => t.min_quantity != null || t.tier_name)
          .map(t => ({
            tier_name: t.tier_name || `Tier ${t.min_quantity}-${t.max_quantity ?? '∞'}`,
            min_quantity: Number(t.min_quantity) || 0,
            max_quantity: t.max_quantity != null && t.max_quantity !== '' ? Number(t.max_quantity) : null,
            discount_percentage: Number(t.discount_percentage) || 0,
          })),
      })),
    nutritional_analysis: form.nutritional_analysis
      .filter(n => n.nutritional_parameter_id != null)
      .map(n => ({ nutritional_parameter_id: n.nutritional_parameter_id, value: String(n.value ?? ''), measure_unit_id: n.measure_unit_id || null })),
    specifications: form.specifications
      .filter(s => s.parameter_id != null && (s.specification !== '' || s.test_method_id != null))
      .map(s => ({ parameter_id: s.parameter_id, test_method_id: s.test_method_id, specification: s.specification || '', measure_unit_id: s.measure_unit_id })),
  };
}

async function uploadDocument(file, type) {
  const fd = new FormData();
  fd.append('file', file);
  fd.append('type', type);
  const { data } = await api.post('/api/v1/admin/products/documents/upload', fd, {
    headers: { 'Content-Type': 'multipart/form-data' },
  });
  return data.path;
}

async function fetchDocumentBlob(type) {
  const { data } = await api.get(`/api/v1/admin/products/${productId.value}/documents/${type}`, { responseType: 'blob' });
  return data;
}

function downloadDocument(type) {
  fetchDocumentBlob(type).then((blob) => {
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = `${type.toUpperCase()}.pdf`;
    a.click();
    URL.revokeObjectURL(url);
  }).catch((e) => useToast().error(e.response?.status === 404 ? 'Document not found.' : 'Could not download.'));
}

function previewDocument(type) {
  fetchDocumentBlob(type).then((blob) => {
    const url = URL.createObjectURL(blob);
    window.open(url, '_blank', 'noopener');
    setTimeout(() => URL.revokeObjectURL(url), 60000);
  }).catch((e) => useToast().error(e.response?.status === 404 ? 'Document not found.' : 'Could not open preview.'));
}

async function save() {
  categoryError.value = false;
  if (!form.name || !form.sku) {
    useToast().error('Name and SKU are required.');
    return;
  }
  if (!form.category_ids || form.category_ids.length === 0) {
    categoryError.value = true;
    activeTab.value = 1;
    useToast().error('Product must have at least one category. Select at least one in the "Categorization and Media" tab.');
    return;
  }
  saving.value = true;
  try {
    if (docFiles.tds instanceof File) {
      form.tds_document_path = await uploadDocument(docFiles.tds, 'tds');
    }
    if (docFiles.sds instanceof File) {
      form.sds_document_path = await uploadDocument(docFiles.sds, 'sds');
    }
    if (docFiles.coa instanceof File) {
      form.coa_document_path = await uploadDocument(docFiles.coa, 'coa');
    }
    const payload = buildPayload();
    if (isEdit.value) {
      await api.put(`/api/v1/admin/products/${productId.value}`, payload);
    } else {
      await api.post('/api/v1/admin/products', payload);
    }
    router.push({ name: 'AdminProducts', query: { saved: '1' } });
  } catch (err) {
    const msg = err.response?.data?.message || err.response?.data?.errors ? JSON.stringify(err.response.data.errors) : err.message;
    useToast().error(msg || 'Error saving.');
  } finally {
    saving.value = false;
  }
}

async function discard() {
  const ok = await useConfirm().show({ title: 'Discard changes', message: 'Discard changes and return to list?', confirmLabel: 'Discard', cancelLabel: 'Stay', variant: 'danger' });
  if (ok) router.push({ name: 'AdminProducts' });
}

function resetAddForm() {
  addForm.label = '';
  addForm.slug = '';
  addForm.parent_id = null;
  addForm.name = '';
  addForm.type = '';
  addForm.notation = '';
  addForm.requirement = '';
  addForm.description = '';
}

function openAddMaster(type, context = {}) {
  addModalType.value = type;
  addModalContext.value = context;
  resetAddForm();
  addModalOpen.value = true;
}

function closeAddModal() {
  addModalOpen.value = false;
  addModalType.value = '';
  addModalContext.value = {};
}

async function submitAddMaster() {
  addSaving.value = true;
  const base = '/api/v1/admin';
  try {
    let res;
    if (addModalType.value === 'category') {
      res = await api.post(`${base}/categories`, { label: addForm.label, slug: addForm.slug, parent_id: addForm.parent_id });
      const newCat = res.data?.data ?? res.data;
      if (newCat?.id) form.category_ids = [...(form.category_ids || []), newCat.id];
    } else if (addModalType.value === 'packaging_type') {
      res = await api.post(`${base}/packaging-types`, { name: addForm.name });
      const newPt = res.data?.data ?? res.data;
      if (newPt?.id && addModalContext.value.packagingIndex != null) {
        form.packaging[addModalContext.value.packagingIndex].packaging_type_id = newPt.id;
      }
    } else if (addModalType.value === 'parameter') {
      await api.post(`${base}/parameters`, { label: addForm.label, type: addForm.type || null });
    } else if (addModalType.value === 'nutritional_parameter') {
      await api.post(`${base}/nutritional-parameters`, { label: addForm.label, notation: addForm.notation || null });
    } else if (addModalType.value === 'test_method') {
      await api.post(`${base}/test-methods`, { label: addForm.label });
    } else if (addModalType.value === 'measure_unit') {
      await api.post(`${base}/measure-units`, { label: addForm.label, notation: addForm.notation || null });
    } else if (addModalType.value === 'handling_spec') {
      await api.post(`${base}/handling-specs`, { label: addForm.label, requirement: addForm.requirement || null });
    } else if (addModalType.value === 'typical_application') {
      await api.post(`${base}/typical-applications`, { label: addForm.label, description: addForm.description || null });
    }
    await fetchCatalogs();
    closeAddModal();
  } catch (e) {
    const msg = e.response?.data?.message || (e.response?.data?.errors && JSON.stringify(e.response.data.errors)) || e.message;
    useToast().error(msg || 'Failed to create. Please try again.');
  } finally {
    addSaving.value = false;
  }
}

watch(() => (form.category_ids?.length || 0), (len) => {
  if (len > 0) categoryError.value = false;
});

onMounted(async () => {
  await fetchCatalogs();
  catalogsReady.value = true;
  await fetchProduct();
});
</script>

<style scoped>
.tab-fade-enter-active,
.tab-fade-leave-active {
  transition: opacity 0.2s ease;
}
.tab-fade-enter-from,
.tab-fade-leave-to {
  opacity: 0;
}
</style>
