<template>
  <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
    <div class="px-5 py-4 border-b border-slate-100 flex flex-wrap items-center justify-between gap-3">
      <h3 class="flex items-center gap-2 text-[11px] font-black text-slate-900 uppercase tracking-widest">
        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
        Commodities & pricing
      </h3>
      <button
        type="button"
        class="px-4 py-2 rounded-xl bg-emerald-600 text-white text-xs font-bold hover:bg-emerald-700 transition-colors shadow-sm disabled:opacity-70 inline-flex items-center gap-2"
        :disabled="saving || !items?.length"
        @click="$emit('save')"
      >
        <svg v-if="saving" class="animate-spin h-3.5 w-3.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/></svg>
        {{ saving ? 'Saving…' : 'Save & mark quoted' }}
      </button>
    </div>

    <div class="p-5">
      <p v-if="!items?.length" class="text-slate-500 text-sm italic">No items found for this request.</p>
      <div v-else class="space-y-4">
        <div
          v-for="item in items"
          :key="item.id"
          class="grid grid-cols-1 lg:grid-cols-12 gap-3 lg:gap-4 items-end border border-slate-200 rounded-xl p-4 bg-slate-50/50"
        >
          <div class="lg:col-span-4 min-w-0">
            <p class="font-bold text-[#2962ff] text-sm truncate">{{ item.product?.name || 'Unknown Product' }}</p>
            <p class="text-xs text-slate-500 mt-0.5">{{ item.qty }} × {{ item.packaging_type?.name || 'Units' }}</p>
          </div>
          <div class="lg:col-span-2">
            <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-1">Product $</label>
            <input
              v-model.number="priceForm[priceRowKey(item.id)].estimated_product_cost"
              type="number"
              step="0.01"
              min="0"
              class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm font-mono focus:ring-2 focus:ring-[#2962ff] focus:border-[#2962ff]"
              placeholder="0.00"
            />
          </div>
          <div class="lg:col-span-2">
            <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-1">Freight $</label>
            <input
              v-model.number="priceForm[priceRowKey(item.id)].estimated_freight_cost"
              type="number"
              step="0.01"
              min="0"
              class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm font-mono focus:ring-2 focus:ring-[#2962ff] focus:border-[#2962ff]"
              placeholder="0.00"
            />
          </div>
          <div class="lg:col-span-4 lg:text-right">
            <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Line total</span>
            <span class="text-sm font-bold text-slate-800 font-mono">${{ formatQuoteMoney(lineTotal(item)) }}</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { formatQuoteMoney } from '../../../composables/useQuoteStatus';

defineProps({
  items: { type: Array, default: () => [] },
  priceForm: { type: Object, required: true },
  priceRowKey: { type: Function, required: true },
  lineTotal: { type: Function, required: true },
  saving: { type: Boolean, default: false },
});

defineEmits(['save']);
</script>
