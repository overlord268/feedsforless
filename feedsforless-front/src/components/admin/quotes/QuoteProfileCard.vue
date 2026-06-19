<template>
  <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-5">
    <h3 class="flex items-center gap-2 text-[11px] font-black text-slate-900 uppercase tracking-widest mb-3 border-b border-slate-100 pb-2">
      <svg class="w-4 h-4 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
      Corporate profile
    </h3>
    <dl class="space-y-3 text-sm">
      <div>
        <dt class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Business</dt>
        <dd class="font-medium text-slate-800 mt-0.5">{{ companyName || '—' }}</dd>
      </div>
      <div>
        <dt class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Contact</dt>
        <dd class="font-medium text-slate-800 mt-0.5">{{ contactName || '—' }}</dd>
        <dd v-if="quote?.requester?.job_title" class="text-xs text-slate-500">{{ quote.requester.job_title }}</dd>
      </div>
      <div>
        <dt class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Email</dt>
        <dd class="mt-0.5">
          <a v-if="email" :href="'mailto:' + email" class="font-medium text-blue-600 hover:underline break-all">{{ email }}</a>
          <span v-else class="text-slate-800">—</span>
        </dd>
      </div>
      <div class="grid grid-cols-2 gap-3">
        <div>
          <dt class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Phone</dt>
          <dd class="font-medium text-slate-800 mt-0.5">{{ phone || '—' }}</dd>
        </div>
        <div>
          <dt class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Tax ID</dt>
          <dd class="font-medium text-slate-800 mt-0.5 truncate" :title="taxId">{{ taxId || '—' }}</dd>
        </div>
      </div>
    </dl>
  </div>
</template>

<script setup>
defineProps({
  quote: { type: Object, required: true },
  companyName: { type: String, default: '' },
  contactName: { type: String, default: '' },
  email: { type: String, default: '' },
  phone: { type: String, default: '' },
  taxId: { type: String, default: '' },
});
</script>
