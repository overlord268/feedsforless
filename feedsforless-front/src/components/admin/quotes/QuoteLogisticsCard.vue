<template>
  <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-5">
    <h3 class="flex items-center gap-2 text-[11px] font-black text-slate-900 uppercase tracking-widest mb-3 border-b border-slate-100 pb-2">
      <svg class="w-4 h-4 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
      Logistics
    </h3>
    <dl class="space-y-3 text-sm">
      <div>
        <dt class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Destination</dt>
        <dd class="font-medium text-slate-800 mt-0.5">
          <span v-if="quote.guest_destination_address" class="block">{{ quote.guest_destination_address }}</span>
          <span>{{ quote.delivery_zip }} (ZIP)</span>
        </dd>
      </div>
      <div class="pt-2 border-t border-slate-100 space-y-2">
        <div class="flex items-center justify-between">
          <span class="text-slate-600">Lift gate</span>
          <span class="font-bold text-sm" :class="quote.requires_liftgate ? 'text-emerald-600' : 'text-slate-400'">{{ quote.requires_liftgate ? 'Yes' : 'No' }}</span>
        </div>
        <div class="flex items-center justify-between">
          <span class="text-slate-600">Pre-delivery call</span>
          <span class="font-bold text-sm" :class="quote.requires_appointment ? 'text-emerald-600' : 'text-slate-400'">{{ quote.requires_appointment ? 'Yes' : 'No' }}</span>
        </div>
      </div>
    </dl>
  </div>
</template>

<script setup>
defineProps({
  quote: { type: Object, required: true },
});
</script>
