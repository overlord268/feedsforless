<template>
  <div class="max-w-4xl space-y-6">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
      <FormInput
        v-model="form.name"
        variant="admin"
        label="Name"
        type="text"
        placeholder="Product name"
      />
      <div v-if="isEdit && form.sku">
        <label class="block text-sm font-medium text-slate-700 mb-1.5">SKU</label>
        <p class="px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-700 font-mono text-sm">
          {{ form.sku }}
        </p>
        <p class="mt-1 text-xs text-slate-500">Assigned automatically and cannot be changed.</p>
      </div>
      <div v-else-if="!isEdit">
        <label class="block text-sm font-medium text-slate-700 mb-1.5">SKU</label>
        <p class="px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-500 text-sm">
          Generated automatically on save from category, name, and grade.
        </p>
      </div>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
      <FormInput
        v-model="form.grade"
        variant="admin"
        label="Grade"
        type="text"
        placeholder="e.g. Grade 2"
      />
      <div>
        <label class="block text-sm font-medium text-slate-700 mb-1.5">Base price (reference)</label>
        <input
          v-model.number="form.base_price_ref"
          type="number"
          step="0.01"
          min="0"
          class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-slate-900 placeholder-slate-400 transition-all focus:outline-none focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500"
          placeholder="0.00"
        />
        <p class="mt-1 text-xs text-slate-500">
          Used by the calculator only when the product has no presentations. Each presentation uses its own base price.
        </p>
      </div>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
      <div>
        <label class="block text-sm font-medium text-slate-700 mb-1.5">Lead time (days)</label>
        <input
          v-model.number="form.lead_time_days"
          type="number"
          min="0"
          class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-slate-900 placeholder-slate-400 transition-all focus:outline-none focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500"
          placeholder="10"
        />
      </div>
      <div>
        <label class="block text-sm font-medium text-slate-700 mb-1.5">Max lead time (days)</label>
        <input
          v-model.number="form.max_lead_time_days"
          type="number"
          min="0"
          class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-slate-900 placeholder-slate-400 transition-all focus:outline-none focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500"
          placeholder="12"
        />
      </div>
    </div>
    <FormInput
      v-model="form.origin_address"
      variant="admin"
      label="Origin address"
      type="text"
      placeholder="Product origin"
    />
    <FormInput
      v-model="form.shelf_life_template"
      variant="admin"
      label="Shelf life template"
      type="text"
      placeholder="Optional"
    />
    <div>
      <label class="block text-sm font-medium text-slate-700 mb-1.5">Description</label>
      <div class="border border-slate-200 rounded-xl overflow-hidden focus-within:ring-2 focus-within:ring-emerald-500/30 focus-within:border-emerald-500">
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
</template>

<script setup>
import { ref, watch } from 'vue';
import FormInput from '../ui/FormInput.vue';

const props = defineProps({
  /** Product form state (reactive object from parent). Mutations are reflected in parent. */
  form: {
    type: Object,
    required: true,
  },
  isEdit: {
    type: Boolean,
    default: false,
  },
});

const descEditorRef = ref(null);

function formatDesc(cmd) {
  document.execCommand(cmd, false, null);
  if (descEditorRef.value && props.form) props.form.description = descEditorRef.value.innerHTML;
}

watch(
  () => props.form?.description,
  (val) => {
    if (descEditorRef.value && descEditorRef.value.innerHTML !== (val || '')) {
      descEditorRef.value.innerHTML = val || '';
    }
  },
  { immediate: true }
);
</script>
