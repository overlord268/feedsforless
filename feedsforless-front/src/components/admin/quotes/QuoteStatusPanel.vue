<template>
  <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-5">
    <h3 class="font-bold text-slate-800 mb-3 text-sm">Status</h3>
    <div class="space-y-3">
      <div class="flex gap-2">
        <select
          :value="status"
          class="flex-1 min-w-0 rounded-lg border border-slate-300 px-3 py-2 text-sm focus:ring-2 focus:ring-[#2962ff] focus:border-[#2962ff] bg-white"
          @input="$emit('update:status', $event.target.value)"
        >
          <option value="pending">Pending</option>
          <option value="quoted">Quoted</option>
          <option value="accepted">Accepted</option>
          <option value="rejected">Rejected</option>
          <option value="expired">Expired</option>
          <option value="cancelled">Cancelled</option>
        </select>
        <button
          type="button"
          class="px-4 py-2 rounded-xl bg-[#2962ff] text-white text-sm font-bold hover:bg-blue-800 disabled:opacity-70 shrink-0"
          :disabled="savingStatus"
          @click="$emit('save-status')"
        >
          {{ savingStatus ? '…' : 'Update' }}
        </button>
      </div>

      <p class="text-[11px] text-amber-800 bg-amber-50 border border-amber-100 rounded-lg px-3 py-2 leading-relaxed">
        Saving prices marks the quote as <strong>Quoted</strong> and shows pricing to the client.
      </p>

      <div v-if="showCustomerMessage">
        <label class="block text-xs font-medium text-slate-600 mb-1">Message to customer</label>
        <textarea
          :value="customerMessage"
          rows="2"
          class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm resize-none focus:ring-2 focus:ring-[#2962ff]"
          placeholder="Reason or next steps…"
          @input="$emit('update:customerMessage', $event.target.value)"
        />
      </div>

      <div>
        <div class="flex justify-between items-center mb-1">
          <label class="text-xs font-medium text-slate-600">Internal note</label>
          <span v-if="savingNote" class="text-[10px] text-slate-400 animate-pulse">Saving…</span>
          <span v-else-if="noteSaved" class="text-[10px] text-emerald-600 font-bold">Saved</span>
        </div>
        <textarea
          :value="adminNote"
          rows="3"
          class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm resize-none focus:ring-2 focus:ring-[#2962ff]"
          placeholder="Admin only — hidden from customer"
          @input="$emit('update:adminNote', $event.target.value)"
        />
      </div>
    </div>
  </div>
</template>

<script setup>
defineProps({
  status: { type: String, required: true },
  adminNote: { type: String, default: '' },
  customerMessage: { type: String, default: '' },
  savingStatus: { type: Boolean, default: false },
  savingNote: { type: Boolean, default: false },
  noteSaved: { type: Boolean, default: false },
  showCustomerMessage: { type: Boolean, default: false },
});

defineEmits(['update:status', 'update:adminNote', 'update:customerMessage', 'save-status']);
</script>
