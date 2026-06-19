<template>
  <div class="space-y-5">
    <div>
      <h1 class="text-xl sm:text-2xl font-bold text-slate-900 dark:text-white tracking-tight">Quotes (RFQs)</h1>
      <p class="text-slate-500 mt-0.5 text-sm">Review and manage quote requests.</p>
    </div>

    <AdminQuotesTable
      :quotes="quotes"
      :loading="loading"
    />
  </div>
</template>

<script setup>
import { onActivated } from 'vue';
import { useAdminQuotesListWithPolling } from '../../composables/quotes/useAdminQuotesList';
import AdminQuotesTable from '../../components/admin/quotes/AdminQuotesTable.vue';

const { quotes, loading, fetchQuotes } = useAdminQuotesListWithPolling();

onActivated(() => {
  if (!document.hidden) fetchQuotes({ silent: true });
});
</script>
