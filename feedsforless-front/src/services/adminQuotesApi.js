import api from './api';

export async function fetchAdminQuotes() {
  const { data } = await api.get('/api/v1/admin/quote-requests', {
    params: { per_page: 100 },
  });
  const raw = data?.data ?? data;
  return Array.isArray(raw) ? raw : (raw?.data ?? []);
}

export async function fetchAdminQuote(quoteId) {
  const { data } = await api.get(`/api/v1/admin/quote-requests/${quoteId}`);
  return data?.data ?? data;
}

export async function updateQuoteStatus(quoteId, payload) {
  const { data } = await api.put(`/api/v1/admin/quote-requests/${quoteId}/status`, payload);
  return data?.data ?? data;
}

export async function updateQuotePrices(quoteId, items) {
  const { data } = await api.put(`/api/v1/admin/quote-requests/${quoteId}/prices`, { items });
  return data?.data ?? data;
}

export async function fetchQuoteLeadCounts() {
  const { data } = await api.get('/api/v1/admin/quote-leads/counts');
  return data?.counts ?? {};
}

export async function fetchQuoteLeadDefinitions() {
  const { data } = await api.get('/api/v1/admin/quote-leads/definitions');
  return data?.definitions ?? [];
}

export async function fetchQuoteLeads(filter) {
  const { data } = await api.get('/api/v1/admin/quote-leads', { params: { filter } });
  return data;
}

export async function exportQuoteLeads(filter, format) {
  return api.get('/api/v1/admin/quote-leads/export', {
    params: { filter, format },
    responseType: 'blob',
  });
}
