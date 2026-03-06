<template>
  <div class="flex flex-col h-full min-h-0 bg-white rounded-2xl border border-slate-200 shadow-sm">
    <header class="sticky top-0 z-20 flex flex-wrap items-center justify-between gap-4 px-6 py-4 bg-white border-b border-slate-200 shrink-0">
      <h1 class="text-xl font-bold text-slate-800 tracking-tight">
        {{ isEdit ? 'Edit Product' : 'Create New Product' }}
      </h1>
      <div class="flex items-center gap-3">
        <div class="flex items-center gap-2">
          <span class="text-sm font-medium text-slate-600">Status</span>
          <template v-if="form.status === 'published'">
            <span class="inline-flex items-center px-3 py-1.5 rounded-lg bg-emerald-100 text-emerald-800 text-sm font-medium">Published</span>
            <button type="button" @click="form.status = 'draft'" class="px-3 py-1.5 text-sm font-medium text-slate-600 hover:text-slate-800 hover:bg-slate-100 rounded-lg transition-colors">Unpublish</button>
          </template>
          <template v-else>
            <span class="inline-flex items-center px-3 py-1.5 rounded-lg bg-slate-100 text-slate-600 text-sm font-medium">Draft</span>
            <button type="button" @click="form.status = 'published'" class="px-4 py-2 rounded-xl bg-emerald-600 text-white text-sm font-medium hover:bg-emerald-700 transition-colors shadow-sm">Publish</button>
          </template>
        </div>
        <button
          type="button"
          class="px-4 py-2.5 bg-emerald-600 text-white font-medium rounded-xl hover:bg-emerald-700 transition-colors shadow-sm"
          :disabled="saving"
          @click="save"
        >
          {{ saving ? 'Saving…' : 'Save Changes' }}
        </button>
        <button
          type="button"
          class="px-4 py-2.5 border border-slate-300 text-slate-700 font-medium rounded-xl hover:bg-slate-50 transition-colors"
          @click="discard"
        >
          Discard
        </button>
        <router-link
          :to="{ name: 'AdminProducts' }"
          class="p-2.5 text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded-xl transition-colors"
          aria-label="Close"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
        </router-link>
      </div>
    </header>

    <nav class="flex gap-1 px-6 border-b border-slate-200 bg-slate-50/50 shrink-0 overflow-x-auto">
      <button
        v-for="(tab, idx) in tabs"
        :key="tab.id"
        type="button"
        :class="[
          'px-4 py-3 text-sm font-medium rounded-t-lg border-b-2 whitespace-nowrap transition-colors',
          activeTab === idx
            ? 'border-emerald-500 text-emerald-600 bg-white -mb-px'
            : 'border-transparent text-slate-500 hover:text-slate-700 hover:bg-slate-100'
        ]"
        @click="activeTab = idx"
      >
        {{ tab.label }}
      </button>
    </nav>

    <div class="flex-1 overflow-y-auto p-6 min-h-0">
      <div v-if="!catalogsReady" class="flex flex-col items-center justify-center py-20 text-slate-500">
        <svg class="w-12 h-12 animate-spin text-emerald-500 mb-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" /><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" /></svg>
        <p class="font-medium">Loading catalogs (categories, parameters, units…)…</p>
      </div>
      <template v-else>
      <div v-show="activeTab === 0" class="max-w-4xl space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1.5">Name</label>
            <input
              v-model="form.name"
              type="text"
              class="w-full px-4 py-2.5 border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500/50 focus:border-emerald-500"
              placeholder="Product name"
            />
          </div>
          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1.5">SKU</label>
            <input
              v-model="form.sku"
              type="text"
              class="w-full px-4 py-2.5 border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500/50 focus:border-emerald-500"
              placeholder="SKU code"
            />
          </div>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1.5">Grade</label>
            <input
              v-model="form.grade"
              type="text"
              class="w-full px-4 py-2.5 border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500/50 focus:border-emerald-500"
              placeholder="e.g. Grade 2"
            />
          </div>
          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1.5">Base price (reference)</label>
            <input
              v-model.number="form.base_price_ref"
              type="number"
              step="0.01"
              min="0"
              class="w-full px-4 py-2.5 border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500/50 focus:border-emerald-500"
              placeholder="0.00"
            />
          </div>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1.5">Lead time (days)</label>
            <input
              v-model.number="form.lead_time_days"
              type="number"
              min="0"
              class="w-full px-4 py-2.5 border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500/50 focus:border-emerald-500"
              placeholder="10"
            />
          </div>
          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1.5">Max lead time (days)</label>
            <input
              v-model.number="form.max_lead_time_days"
              type="number"
              min="0"
              class="w-full px-4 py-2.5 border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500/50 focus:border-emerald-500"
              placeholder="12"
            />
          </div>
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1.5">Origin address</label>
          <input
            v-model="form.origin_address"
            type="text"
            class="w-full px-4 py-2.5 border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500/50 focus:border-emerald-500"
            placeholder="Product origin"
          />
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1.5">Shelf life template</label>
          <input
            v-model="form.shelf_life_template"
            type="text"
            class="w-full px-4 py-2.5 border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500/50 focus:border-emerald-500"
            placeholder="Optional"
          />
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1.5">Description</label>
          <div class="border border-slate-200 rounded-xl overflow-hidden focus-within:ring-2 focus-within:ring-emerald-500/50 focus-within:border-emerald-500">
            <div class="flex flex-wrap gap-1 p-2 bg-slate-50 border-b border-slate-200">
              <button type="button" class="p-1.5 rounded hover:bg-slate-200" title="Bold" @click="formatDesc('bold')"><b>B</b></button>
              <button type="button" class="p-1.5 rounded hover:bg-slate-200" title="Italic" @click="formatDesc('italic')"><i>I</i></button>
              <button type="button" class="p-1.5 rounded hover:bg-slate-200" title="List" @click="formatDesc('insertUnorderedList')">• List</button>
            </div>
            <div
              ref="descEditorRef"
              contenteditable="true"
              class="min-h-[120px] p-4 text-slate-800 outline-none prose prose-sm max-w-none"
              :data-placeholder="'Detailed product description…'"
              @input="form.description = $event.target?.innerHTML || ''"
            />
          </div>
        </div>
      </div>

      <div v-show="activeTab === 1" class="max-w-4xl space-y-8">
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-2">Categories <span class="text-red-500">*</span></label>
          <p class="text-xs text-slate-500 mb-2">Product must have at least one category (avoids orphan products).</p>
          <div class="border border-slate-200 rounded-xl p-4 bg-slate-50/50 max-h-80 overflow-y-auto" :class="{ 'border-red-300 bg-red-50/30': categoryError }">
            <CategoryTreeSelect v-model="form.category_ids" :categories="categoriesTree" />
          </div>
          <button type="button" class="mt-2 text-sm text-emerald-600 hover:underline inline-flex items-center gap-1" @click="openAddMaster('category')">+ Add category</button>
        </div>
        <div>
          <p class="text-sm font-medium text-slate-700 mb-2">Technical documents</p>
          <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="space-y-2">
              <p class="text-xs font-medium text-slate-600">TDS</p>
              <template v-if="form.tds_document_path && productId">
                <div class="flex flex-wrap gap-2 mb-2">
                  <button type="button" class="text-sm text-emerald-600 hover:underline" @click="downloadDocument('tds')">Download</button>
                  <button type="button" class="text-sm text-slate-600 hover:underline" @click="previewDocument('tds')">Preview</button>
                </div>
              </template>
              <DropZone label="" :file="docFiles.tds" @update:file="docFiles.tds = $event" />
            </div>
            <div class="space-y-2">
              <p class="text-xs font-medium text-slate-600">SDS</p>
              <template v-if="form.sds_document_path && productId">
                <div class="flex flex-wrap gap-2 mb-2">
                  <button type="button" class="text-sm text-emerald-600 hover:underline" @click="downloadDocument('sds')">Download</button>
                  <button type="button" class="text-sm text-slate-600 hover:underline" @click="previewDocument('sds')">Preview</button>
                </div>
              </template>
              <DropZone label="" :file="docFiles.sds" @update:file="docFiles.sds = $event" />
            </div>
            <div class="space-y-2">
              <p class="text-xs font-medium text-slate-600">COA</p>
              <template v-if="form.coa_document_path && productId">
                <div class="flex flex-wrap gap-2 mb-2">
                  <button type="button" class="text-sm text-emerald-600 hover:underline" @click="downloadDocument('coa')">Download</button>
                  <button type="button" class="text-sm text-slate-600 hover:underline" @click="previewDocument('coa')">Preview</button>
                </div>
              </template>
              <DropZone label="" :file="docFiles.coa" @update:file="docFiles.coa = $event" />
            </div>
          </div>
        </div>
      </div>

      <div v-show="activeTab === 2" class="max-w-5xl space-y-6">
        <div class="flex items-center justify-between">
          <h2 class="text-lg font-semibold text-slate-800">Presentations and volume pricing</h2>
          <button
            type="button"
            class="px-4 py-2 bg-emerald-600 text-white text-sm font-medium rounded-xl hover:bg-emerald-700 transition-colors"
            @click="addPresentation"
          >
            + Add Presentation
          </button>
        </div>
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
          <div
            v-for="(pres, index) in form.packaging"
            :key="index"
            class="border border-slate-200 rounded-2xl p-5 bg-white shadow-sm space-y-4"
          >
            <div class="flex justify-between items-start">
              <span class="font-medium text-slate-700">Presentation {{ index + 1 }}</span>
              <button
                v-if="form.packaging.length > 1"
                type="button"
                class="text-slate-400 hover:text-red-600 p-1"
                @click="removePresentation(index)"
              >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
              </button>
            </div>
            <div>
              <label class="block text-sm font-medium text-slate-600 mb-1">Packaging type</label>
              <div class="flex gap-2">
                <select
                  v-model="pres.packaging_type_id"
                  class="flex-1 px-4 py-2.5 border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500/50"
                >
                  <option :value="null">Select…</option>
                  <option v-for="pt in packagingTypes" :key="pt.id" :value="pt.id">{{ pt.name || pt.label }}</option>
                </select>
                <button type="button" class="shrink-0 p-2.5 border border-slate-200 rounded-xl text-emerald-600 hover:bg-emerald-50" title="Add packaging type" @click="openAddMaster('packaging_type', { packagingIndex: index })">+</button>
              </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-slate-600 mb-1">Quantity per pallet</label>
                <input
                  v-model.number="pres.quantity_per_pallet"
                  type="number"
                  min="1"
                  class="w-full px-4 py-2.5 border border-slate-200 rounded-xl"
                />
              </div>
              <div>
                <label class="block text-sm font-medium text-slate-600 mb-1">Base price</label>
                <input
                  v-model.number="pres.base_price_per_unit"
                  type="number"
                  step="0.01"
                  min="0"
                  class="w-full px-4 py-2.5 border border-slate-200 rounded-xl"
                />
              </div>
            </div>
            <div>
              <div class="flex items-center justify-between mb-2">
                <label class="text-sm font-medium text-slate-600">Volume tiers</label>
                <button
                  type="button"
                  class="text-sm text-emerald-600 hover:underline"
                  @click="addVolumeTier(index)"
                >
                  + Add row
                </button>
              </div>
              <div class="overflow-x-auto table-scroll">
                <table class="w-full text-sm min-w-[600px]">
                  <thead>
                    <tr class="border-b border-slate-200 text-slate-600">
                      <th class="text-left py-2 pr-2">Min</th>
                      <th class="text-left py-2 pr-2">Max</th>
                      <th class="text-left py-2 pr-2">Discount %</th>
                      <th class="w-10"></th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="(tier, ti) in pres.volume_tiers" :key="ti" class="border-b border-slate-100">
                      <td class="py-2 pr-2"><input v-model.number="tier.min_quantity" type="number" min="0" class="w-full px-2 py-1.5 border rounded" /></td>
                      <td class="py-2 pr-2"><input v-model.number="tier.max_quantity" type="number" min="0" class="w-full px-2 py-1.5 border rounded" placeholder="Unlimited" /></td>
                      <td class="py-2 pr-2"><input v-model.number="tier.discount_percentage" type="number" min="0" max="100" step="0.01" class="w-full px-2 py-1.5 border rounded" /></td>
                      <td class="py-2">
                        <button type="button" class="text-slate-400 hover:text-red-600" @click="removeVolumeTier(index, ti)">×</button>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div v-show="activeTab === 3" class="max-w-5xl space-y-10">
        <section class="bg-slate-50/50 border border-slate-200 rounded-2xl p-6">
          <h2 class="text-lg font-semibold text-slate-800 pb-3 mb-4 border-b border-slate-200">Nutritional analysis</h2>
          <p class="text-sm text-slate-500 mb-4">Parameters and units from the Nutritional analysis menu. Use Parameter, Value and Unit per row.</p>
          <div class="overflow-x-auto table-scroll">
            <table class="w-full text-sm min-w-[560px]">
              <thead class="bg-white border border-slate-200 rounded-t-xl">
                <tr class="text-slate-600">
                  <th class="text-left p-3 rounded-tl-xl">
                    <span class="font-medium">Parameter</span>
                    <button type="button" class="ml-2 text-emerald-600 hover:text-emerald-700 text-xs font-medium" @click="openAddMaster('nutritional_parameter')">+ Add</button>
                  </th>
                  <th class="text-left p-3 font-medium">Value</th>
                  <th class="text-left p-3">
                    <span class="font-medium">Unit</span>
                    <button type="button" class="ml-2 text-emerald-600 hover:text-emerald-700 text-xs font-medium" @click="openAddMaster('measure_unit')">+ Add</button>
                  </th>
                  <th class="w-24 text-right p-3 rounded-tr-xl">
                    <button type="button" class="text-emerald-600 hover:text-emerald-700 text-xs font-medium" @click="addNutritionalRow">+ Add row</button>
                  </th>
                </tr>
              </thead>
              <tbody class="bg-white border-x border-b border-slate-200">
                <tr v-for="(row, ri) in form.nutritional_analysis" :key="ri" class="border-t border-slate-100 hover:bg-slate-50/50">
                  <td class="p-3">
                    <select v-model="row.nutritional_parameter_id" class="w-full px-3 py-2.5 border border-slate-200 rounded-xl text-slate-800 focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 bg-white">
                      <option :value="null">— Select parameter</option>
                      <option v-for="p in nutritionalParameters" :key="p.id" :value="p.id">{{ p.label }}</option>
                    </select>
                  </td>
                  <td class="p-3">
                    <input v-model="row.value" type="text" class="w-full px-3 py-2.5 border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500" placeholder="Value" />
                  </td>
                  <td class="p-3">
                    <select v-model="row.measure_unit_id" class="w-full px-3 py-2.5 border border-slate-200 rounded-xl text-slate-800 focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 bg-white">
                      <option :value="null">—</option>
                      <option v-for="mu in measureUnits" :key="mu.id" :value="mu.id">{{ mu.label || mu.notation || mu.name }}</option>
                    </select>
                  </td>
                  <td class="p-3 text-right">
                    <button type="button" class="p-2 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors" @click="removeNutritionalRow(ri)" title="Remove row">×</button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </section>

        <section class="bg-slate-50/50 border border-slate-200 rounded-2xl p-6">
          <h2 class="text-lg font-semibold text-slate-800 pb-3 mb-4 border-b border-slate-200">Technical specifications</h2>
          <p class="text-sm text-slate-500 mb-4">Parameter, test method, specification and unit per row.</p>
          <div class="overflow-x-auto table-scroll">
            <table class="w-full text-sm min-w-[640px]">
              <thead class="bg-white border border-slate-200 rounded-t-xl">
                <tr class="text-slate-600">
                  <th class="text-left p-3 rounded-tl-xl">
                    <span class="font-medium">Parameter</span>
                    <button type="button" class="ml-2 text-emerald-600 hover:text-emerald-700 text-xs font-medium" @click="openAddMaster('parameter')">+ Add</button>
                  </th>
                  <th class="text-left p-3">
                    <span class="font-medium">Test method</span>
                    <button type="button" class="ml-2 text-emerald-600 hover:text-emerald-700 text-xs font-medium" @click="openAddMaster('test_method')">+ Add</button>
                  </th>
                  <th class="text-left p-3 font-medium">Specification</th>
                  <th class="text-left p-3">
                    <span class="font-medium">Unit</span>
                    <button type="button" class="ml-2 text-emerald-600 hover:text-emerald-700 text-xs font-medium" @click="openAddMaster('measure_unit')">+ Add</button>
                  </th>
                  <th class="w-24 text-right p-3 rounded-tr-xl">
                    <button type="button" class="text-emerald-600 hover:text-emerald-700 text-xs font-medium" @click="addSpecRow">+ Add row</button>
                  </th>
                </tr>
              </thead>
              <tbody class="bg-white border-x border-b border-slate-200">
                <tr v-for="(row, ri) in form.specifications" :key="ri" class="border-t border-slate-100 hover:bg-slate-50/50">
                  <td class="p-3">
                    <select v-model="row.parameter_id" class="w-full px-3 py-2.5 border border-slate-200 rounded-xl text-slate-800 focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 bg-white">
                      <option :value="null">— Select parameter</option>
                      <option v-for="p in parameters" :key="p.id" :value="p.id">{{ p.label }}</option>
                    </select>
                  </td>
                  <td class="p-3">
                    <select v-model="row.test_method_id" class="w-full px-3 py-2.5 border border-slate-200 rounded-xl text-slate-800 focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 bg-white">
                      <option :value="null">— Select method</option>
                      <option v-for="tm in testMethods" :key="tm.id" :value="tm.id">{{ tm.label }}</option>
                    </select>
                  </td>
                  <td class="p-3">
                    <input v-model="row.specification" type="text" class="w-full px-3 py-2.5 border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500" placeholder="e.g. &lt; 14%" />
                  </td>
                  <td class="p-3">
                    <select v-model="row.measure_unit_id" class="w-full px-3 py-2.5 border border-slate-200 rounded-xl text-slate-800 focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 bg-white">
                      <option :value="null">—</option>
                      <option v-for="mu in measureUnits" :key="mu.id" :value="mu.id">{{ mu.label || mu.notation || mu.name }}</option>
                    </select>
                  </td>
                  <td class="p-3 text-right">
                    <button type="button" class="p-2 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors" @click="removeSpecRow(ri)" title="Remove row">×</button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </section>
      </div>

      <div v-show="activeTab === 4" class="max-w-4xl space-y-6">
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-2">Related products</label>
          <ChipsSelect
            :model-value="form.related_product_ids"
            :options="productsForRelated"
            label-key="name"
            value-key="id"
            placeholder="Search products…"
            @update:model-value="form.related_product_ids = $event"
          />
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-2">Handling specs</label>
          <ChipsSelect
            :model-value="form.handling_spec_ids"
            :options="handlingSpecs"
            label-key="label"
            value-key="id"
            placeholder="Select…"
            @update:model-value="form.handling_spec_ids = $event"
          />
          <button type="button" class="mt-1 text-sm text-emerald-600 hover:underline inline-flex items-center gap-1" @click="openAddMaster('handling_spec')">+ Add handling spec</button>
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-2">Typical applications</label>
          <ChipsSelect
            :model-value="form.application_ids"
            :options="typicalApplications"
            label-key="label"
            value-key="id"
            placeholder="Select…"
            @update:model-value="form.application_ids = $event"
          />
          <button type="button" class="mt-1 text-sm text-emerald-600 hover:underline inline-flex items-center gap-1" @click="openAddMaster('typical_application')">+ Add typical application</button>
        </div>
      </div>

      <div v-if="addModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/40 backdrop-blur-sm" @click.self="closeAddModal">
        <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full p-6 max-h-[90vh] overflow-y-auto border border-slate-200/80">
          <h2 class="text-lg font-semibold text-slate-900 mb-4">{{ addModalTitle }}</h2>
          <form @submit.prevent="submitAddMaster" class="space-y-4">
            <template v-if="addModalType === 'category'">
              <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Name (label) *</label>
                <input v-model="addForm.label" type="text" required class="w-full rounded-lg border border-slate-300 px-3 py-2" placeholder="e.g. Ingredients" />
              </div>
              <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Slug *</label>
                <input v-model="addForm.slug" type="text" required class="w-full rounded-lg border border-slate-300 px-3 py-2 font-mono text-sm" placeholder="ej-ingredientes" />
              </div>
              <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Parent</label>
                <select v-model="addForm.parent_id" class="w-full rounded-lg border border-slate-300 px-3 py-2">
                  <option :value="null">None</option>
                  <option v-for="c in catalog.categories" :key="c.id" :value="c.id">{{ c.label }}</option>
                </select>
              </div>
            </template>
            <template v-else-if="addModalType === 'packaging_type'">
              <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Name *</label>
                <input v-model="addForm.name" type="text" required class="w-full rounded-lg border border-slate-300 px-3 py-2" placeholder="e.g. Bag" />
              </div>
            </template>
            <template v-else-if="addModalType === 'parameter'">
              <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Label *</label>
                <input v-model="addForm.label" type="text" required class="w-full rounded-lg border border-slate-300 px-3 py-2" placeholder="e.g. pH" />
              </div>
              <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Type</label>
                <input v-model="addForm.type" type="text" class="w-full rounded-lg border border-slate-300 px-3 py-2" placeholder="Optional" />
              </div>
            </template>
            <template v-else-if="addModalType === 'nutritional_parameter'">
              <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Label *</label>
                <input v-model="addForm.label" type="text" required class="w-full rounded-lg border border-slate-300 px-3 py-2" placeholder="e.g. Protein" />
              </div>
              <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Notation</label>
                <input v-model="addForm.notation" type="text" class="w-full rounded-lg border border-slate-300 px-3 py-2" placeholder="Optional" />
              </div>
            </template>
            <template v-else-if="addModalType === 'test_method'">
              <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Label *</label>
                <input v-model="addForm.label" type="text" required class="w-full rounded-lg border border-slate-300 px-3 py-2" placeholder="e.g. AOCS" />
              </div>
            </template>
            <template v-else-if="addModalType === 'measure_unit'">
              <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Label *</label>
                <input v-model="addForm.label" type="text" required class="w-full rounded-lg border border-slate-300 px-3 py-2" placeholder="e.g. Kilogram" />
              </div>
              <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Notation</label>
                <input v-model="addForm.notation" type="text" class="w-full rounded-lg border border-slate-300 px-3 py-2" placeholder="e.g. kg" />
              </div>
            </template>
            <template v-else-if="addModalType === 'handling_spec'">
              <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Label *</label>
                <input v-model="addForm.label" type="text" required class="w-full rounded-lg border border-slate-300 px-3 py-2" placeholder="e.g. Refrigerated" />
              </div>
              <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Requirement</label>
                <input v-model="addForm.requirement" type="text" class="w-full rounded-lg border border-slate-300 px-3 py-2" placeholder="Optional" />
              </div>
            </template>
            <template v-else-if="addModalType === 'typical_application'">
              <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Label *</label>
                <input v-model="addForm.label" type="text" required class="w-full rounded-lg border border-slate-300 px-3 py-2" placeholder="e.g. Animal feed" />
              </div>
              <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Description</label>
                <textarea v-model="addForm.description" rows="2" class="w-full rounded-lg border border-slate-300 px-3 py-2" placeholder="Optional"></textarea>
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
import { ref, reactive, computed, watch, onMounted, nextTick } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import api from '../../services/api';
import { useToast } from '../../composables/useToast';
import { useConfirm } from '../../composables/useConfirm';
import CategoryTreeSelect from '../../components/product/CategoryTreeSelect.vue';
import DropZone from '../../components/product/DropZone.vue';
import ChipsSelect from '../../components/product/ChipsSelect.vue';

const route = useRoute();
const router = useRouter();
const activeTab = ref(0);
const saving = ref(false);
const descEditorRef = ref(null);
const categoryError = ref(false);
const catalogsReady = ref(false);
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
const testMethods = computed(() => catalog.testMethods);
const measureUnits = computed(() => catalog.measureUnits);
const handlingSpecs = computed(() => catalog.handlingSpecs);
const typicalApplications = computed(() => catalog.typicalApplications);
const productsForRelated = computed(() => catalog.products.filter(p => p.id !== productId.value));

const defaultPackaging = () => ({
  packaging_type_id: null,
  quantity_per_pallet: 40,
  base_price_per_unit: 0,
  volume_tiers: [{ tier_name: '', min_quantity: 1, max_quantity: null, discount_percentage: 0 }],
});
const defaultNutritionalRow = () => ({ nutritional_parameter_id: null, value: '', measure_unit_id: null });
const defaultSpecRow = () => ({ parameter_id: null, test_method_id: null, specification: '', measure_unit_id: null });

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

function formatDesc(cmd) {
  document.execCommand(cmd, false, null);
  if (descEditorRef.value) form.description = descEditorRef.value.innerHTML;
}

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
    console.error('Error loading catalogs', e);
  }
}

async function fetchProduct() {
  if (!productId.value) return;
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

    await nextTick();
    if (descEditorRef.value) descEditorRef.value.innerHTML = form.description || '';
  } catch (e) {
    console.error('Error loading product', e);
  }
}

function buildPayload() {
  const leadTime = form.lead_time_days != null ? (form.lead_time_days === 0 ? null : new Date(Date.now() + form.lead_time_days * 86400000).toISOString().slice(0, 10)) : null;
  const maxLeadTime = form.max_lead_time_days != null ? (form.max_lead_time_days === 0 ? null : new Date(Date.now() + form.max_lead_time_days * 86400000).toISOString().slice(0, 10)) : null;

  const payload = {
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
  return payload;
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
    console.error(e);
    const msg = e.response?.data?.message || e.response?.data?.errors ? JSON.stringify(e.response.data.errors) : e.message;
    useToast().error(msg || 'Error creating.');
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
  watch(() => form.description, (val) => { if (descEditorRef.value && descEditorRef.value.innerHTML !== val) descEditorRef.value.innerHTML = val || ''; }, { immediate: true });
});
</script>
