<template>
  <div class="max-w-5xl space-y-6">
    <div class="flex items-center justify-between">
      <h2 class="text-lg font-semibold text-slate-800">Presentations and volume pricing</h2>
      <button
        type="button"
        class="px-4 py-2 bg-emerald-600 text-white text-sm font-medium rounded-xl hover:bg-emerald-700 transition-colors"
        @click="emit('add-presentation')"
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
            @click="emit('remove-presentation', index)"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
          </button>
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-600 mb-1">Packaging type</label>
          <div class="flex gap-2">
            <select
              v-model="pres.packaging_type_id"
              class="flex-1 px-4 py-2.5 border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500"
            >
              <option :value="null">Select…</option>
              <option v-for="pt in packagingTypes" :key="pt.id" :value="pt.id">{{ pt.name || pt.label }}</option>
            </select>
            <button type="button" class="shrink-0 p-2.5 border border-slate-200 rounded-xl text-emerald-600 hover:bg-emerald-50" title="Add packaging type" @click="emit('open-add-master', 'packaging_type', { packagingIndex: index })">+</button>
          </div>
        </div>
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-slate-600 mb-1">Quantity per pallet</label>
            <input
              v-model.number="pres.quantity_per_pallet"
              type="number"
              min="1"
              class="w-full px-4 py-2.5 border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500"
            />
          </div>
          <div>
            <label class="block text-sm font-medium text-slate-600 mb-1">Base price</label>
            <input
              v-model.number="pres.base_price_per_unit"
              type="number"
              step="0.01"
              min="0"
              class="w-full px-4 py-2.5 border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500"
            />
          </div>
        </div>
        <div>
          <div class="flex items-center justify-between mb-2">
            <label class="text-sm font-medium text-slate-600">Volume tiers</label>
            <button type="button" class="text-sm text-emerald-600 hover:underline" @click="emit('add-volume-tier', index)">
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
                  <td class="py-2 pr-2"><input v-model.number="tier.min_quantity" type="number" min="0" class="w-full px-2 py-1.5 border border-slate-200 rounded focus:ring-2 focus:ring-emerald-500/30" /></td>
                  <td class="py-2 pr-2"><input v-model.number="tier.max_quantity" type="number" min="0" class="w-full px-2 py-1.5 border border-slate-200 rounded focus:ring-2 focus:ring-emerald-500/30" placeholder="Unlimited" /></td>
                  <td class="py-2 pr-2"><input v-model.number="tier.discount_percentage" type="number" min="0" max="100" step="0.01" class="w-full px-2 py-1.5 border border-slate-200 rounded focus:ring-2 focus:ring-emerald-500/30" /></td>
                  <td class="py-2">
                    <button type="button" class="text-slate-400 hover:text-red-600" @click="emit('remove-volume-tier', index, ti)">×</button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
defineProps({
  form: { type: Object, required: true },
  packagingTypes: { type: Array, default: () => [] },
});

const emit = defineEmits([
  'add-presentation',
  'remove-presentation',
  'add-volume-tier',
  'remove-volume-tier',
  'open-add-master',
]);
</script>
