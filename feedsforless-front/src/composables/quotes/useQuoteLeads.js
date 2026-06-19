import { ref } from 'vue';
import { useToast } from '../useToast';
import { downloadFromResponse } from '../useFileDownload';
import {
  exportQuoteLeads,
  fetchQuoteLeadCounts,
  fetchQuoteLeadDefinitions,
  fetchQuoteLeads,
} from '../../services/adminQuotesApi';

export const LEAD_FILTER_IDS = [
  'unregistered_with_quotes',
  'without_accepted_quote',
  'registered_no_quotes',
];

export function useQuoteLeads() {
  const toast = useToast();
  const leads = ref([]);
  const leadsLoading = ref(false);
  const exporting = ref(false);
  const filterCounts = ref({});
  const filterDefinitions = ref([]);
  const activeViewLabel = ref('');
  const activeViewDescription = ref('');
  const activeFilterNumber = ref(null);

  async function loadDefinitions() {
    try {
      filterDefinitions.value = await fetchQuoteLeadDefinitions();
    } catch (e) {
      console.error(e);
      filterDefinitions.value = [];
    }
  }

  async function fetchFilterCounts() {
    try {
      filterCounts.value = await fetchQuoteLeadCounts();
    } catch (e) {
      console.error(e);
      filterCounts.value = {};
    }
  }

  async function loadLeads(filter) {
    leadsLoading.value = true;
    try {
      const data = await fetchQuoteLeads(filter);
      leads.value = data?.data ?? [];
      activeViewLabel.value = data?.filter_label ?? filter;
      activeViewDescription.value = data?.filter_description ?? '';
      activeFilterNumber.value = data?.filter_number ?? null;
      filterCounts.value[filter] = data?.count ?? leads.value.length;
    } catch (e) {
      console.error(e);
      leads.value = [];
      toast.error('Could not load contacts for this filter.');
    } finally {
      leadsLoading.value = false;
    }
  }

  async function exportLeads(filter, format) {
    if (exporting.value) return;
    exporting.value = true;
    try {
      const response = await exportQuoteLeads(filter, format);
      await downloadFromResponse(response, `quote_leads_${filter}.${format}`);
    } catch (e) {
      console.error(e);
      toast.error('Export failed.');
    } finally {
      exporting.value = false;
    }
  }

  return {
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
  };
}
