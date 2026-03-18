<template>
  <div class="max-w-4xl space-y-8">
    <div>
      <label class="block text-sm font-medium text-slate-700 mb-2">Categories <span class="text-red-500">*</span></label>
      <p class="text-xs text-slate-500 mb-2">Product must have at least one category (avoids orphan products).</p>
      <div
        class="border border-slate-200 rounded-xl p-4 bg-slate-50/50 max-h-80 overflow-y-auto"
        :class="{ 'border-red-300 bg-red-50/30': categoryError }"
      >
        <CategoryTreeSelect v-model="form.category_ids" :categories="categoriesTree" />
      </div>
      <button
        type="button"
        class="mt-2 text-sm text-emerald-600 hover:underline inline-flex items-center gap-1"
        @click="emit('open-add-master', 'category')"
      >
        + Add category
      </button>
    </div>
    <div>
      <p class="text-sm font-medium text-slate-700 mb-2">Technical documents</p>
      <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="space-y-2">
          <p class="text-xs font-medium text-slate-600">TDS</p>
          <template v-if="form.tds_document_path && productId">
            <div class="flex flex-wrap gap-2 mb-2">
              <button type="button" class="text-sm text-emerald-600 hover:underline" @click="emit('download-document', 'tds')">Download</button>
              <button type="button" class="text-sm text-slate-600 hover:underline" @click="emit('preview-document', 'tds')">Preview</button>
            </div>
          </template>
          <DropZone label="" :file="docFiles.tds" @update:file="docFiles.tds = $event" />
        </div>
        <div class="space-y-2">
          <p class="text-xs font-medium text-slate-600">SDS</p>
          <template v-if="form.sds_document_path && productId">
            <div class="flex flex-wrap gap-2 mb-2">
              <button type="button" class="text-sm text-emerald-600 hover:underline" @click="emit('download-document', 'sds')">Download</button>
              <button type="button" class="text-sm text-slate-600 hover:underline" @click="emit('preview-document', 'sds')">Preview</button>
            </div>
          </template>
          <DropZone label="" :file="docFiles.sds" @update:file="docFiles.sds = $event" />
        </div>
        <div class="space-y-2">
          <p class="text-xs font-medium text-slate-600">COA</p>
          <template v-if="form.coa_document_path && productId">
            <div class="flex flex-wrap gap-2 mb-2">
              <button type="button" class="text-sm text-emerald-600 hover:underline" @click="emit('download-document', 'coa')">Download</button>
              <button type="button" class="text-sm text-slate-600 hover:underline" @click="emit('preview-document', 'coa')">Preview</button>
            </div>
          </template>
          <DropZone label="" :file="docFiles.coa" @update:file="docFiles.coa = $event" />
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import CategoryTreeSelect from '../product/CategoryTreeSelect.vue';
import DropZone from '../product/DropZone.vue';

defineProps({
  form: { type: Object, required: true },
  categoryError: { type: Boolean, default: false },
  categoriesTree: { type: Array, default: () => [] },
  docFiles: { type: Object, required: true },
  productId: { type: [Number, String], default: null },
});

const emit = defineEmits(['open-add-master', 'download-document', 'preview-document']);
</script>
