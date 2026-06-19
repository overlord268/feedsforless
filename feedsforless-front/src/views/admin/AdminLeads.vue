<template>
  <div class="space-y-5">
    <div>
      <h1 class="text-xl sm:text-2xl font-bold text-slate-900 dark:text-white tracking-tight">Leads</h1>
    </div>

    <QuoteViewSelector
      :cards="viewCards"
      :active-id="activeSegment"
      :columns="3"
      @select="selectSegment"
    />

    <AdminQuoteLeadsTable
      :leads="leads"
      :loading="leadsLoading"
      :exporting="exporting"
      :segment-label="activeViewLabel"
      :segment-description="activeViewDescription"
      :filter-number="activeFilterNumber"
      @export="(format) => exportLeads(activeSegment, format)"
    />
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useQuoteLeads, LEAD_FILTER_IDS } from '../../composables/quotes/useQuoteLeads';
import QuoteViewSelector from '../../components/admin/quotes/QuoteViewSelector.vue';
import AdminQuoteLeadsTable from '../../components/admin/quotes/AdminQuoteLeadsTable.vue';

const route = useRoute();
const router = useRouter();

const activeSegment = ref(LEAD_FILTER_IDS[0]);

const {
  leads,
  leadsLoading,
  exporting,
  filterCounts,
  filterDefinitions,
  activeViewLabel,
  activeViewDescription,
  activeFilterNumber,
  loadDefinitions,
  fetchFilterCounts,
  loadLeads,
  exportLeads,
} = useQuoteLeads();

const viewCards = computed(() => {
  const defsById = Object.fromEntries(
    filterDefinitions.value.map((d) => [d.id, d]),
  );

  return LEAD_FILTER_IDS.map((filterId) => {
    const def = defsById[filterId];
    return {
      id: filterId,
      title: def?.title ?? filterId,
      description: def?.description ?? '',
      count: filterCounts.value[filterId],
      badge: String(def?.number ?? ''),
    };
  });
});

function isValidSegment(id) {
  return LEAD_FILTER_IDS.includes(id);
}

function selectSegment(segmentId) {
  if (activeSegment.value === segmentId) return;
  activeSegment.value = segmentId;
  router.replace({ query: { segment: segmentId } });
  loadLeads(segmentId);
}

function syncFromRoute() {
  const segment = route.query.segment;
  if (typeof segment === 'string' && isValidSegment(segment) && segment !== activeSegment.value) {
    activeSegment.value = segment;
    loadLeads(segment);
  }
}

onMounted(async () => {
  const segment = route.query.segment;
  if (typeof segment === 'string' && isValidSegment(segment)) {
    activeSegment.value = segment;
  }

  await Promise.all([loadDefinitions(), fetchFilterCounts()]);
  await loadLeads(activeSegment.value);

  if (!route.query.segment) {
    router.replace({ query: { segment: activeSegment.value } });
  }
});

watch(() => route.query.segment, () => {
  syncFromRoute();
});
</script>
