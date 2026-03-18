<template>
  <div class="max-w-4xl space-y-6">
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
      <button type="button" class="mt-1 text-sm text-emerald-600 hover:underline inline-flex items-center gap-1" @click="emit('open-add-master', 'handling_spec')">+ Add handling spec</button>
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
      <button type="button" class="mt-1 text-sm text-emerald-600 hover:underline inline-flex items-center gap-1" @click="emit('open-add-master', 'typical_application')">+ Add typical application</button>
    </div>
  </div>
</template>

<script setup>
import ChipsSelect from '../product/ChipsSelect.vue';

defineProps({
  form: { type: Object, required: true },
  productsForRelated: { type: Array, default: () => [] },
  handlingSpecs: { type: Array, default: () => [] },
  typicalApplications: { type: Array, default: () => [] },
});

const emit = defineEmits(['open-add-master']);
</script>
