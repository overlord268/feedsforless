<template>
  <div class="bg-white rounded-2xl border border-slate-200/80 shadow-card px-5 py-4 flex flex-wrap items-center gap-6 sm:gap-10">
    <div>
      <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest block mb-0.5">RFQ ID</span>
      <span class="text-base font-mono text-slate-800 font-semibold">#{{ quote.id }}</span>
    </div>
    <div>
      <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest block mb-0.5">Status</span>
      <QuoteStatusBadge :status="quote.status" variant="admin" size="md" />
    </div>
    <div>
      <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest block mb-0.5">Contact</span>
      <span class="text-sm font-medium text-slate-800">{{ contactName }}</span>
    </div>
    <div class="sm:ml-auto text-left sm:text-right">
      <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest block mb-0.5">Total estimated</span>
      <span class="text-xl font-black text-emerald-600">${{ formatQuoteMoney(totalCost) }}</span>
    </div>
  </div>
</template>

<script setup>
import QuoteStatusBadge from './QuoteStatusBadge.vue';
import { formatQuoteMoney } from '../../../composables/useQuoteStatus';

defineProps({
  quote: { type: Object, required: true },
  contactName: { type: String, default: '—' },
  totalCost: { type: Number, default: 0 },
});
</script>
