import { computed, unref } from 'vue';

function trim(s) {
  return (s || '').trim();
}

export function useQuoteRequester(quoteRef) {
  const companyName = computed(() => {
    const q = unref(quoteRef);
    if (!q?.requester) return q?.guest_company_name || '—';
    return q.requester.company_name || q.guest_company_name || '—';
  });

  const contactName = computed(() => {
    const q = unref(quoteRef);
    if (!q?.requester) return q?.guest_contact_name || '—';
    const first = q.requester.first_name || '';
    const last = q.requester.last_name || '';
    const full = trim(`${first} ${last}`);
    return full || q.requester.contact_name || q?.guest_contact_name || '—';
  });

  const email = computed(() => {
    const q = unref(quoteRef);
    return q?.requester?.email || q?.guest_email || '';
  });

  const phone = computed(() => {
    const q = unref(quoteRef);
    return q?.requester?.phone || q?.guest_phone || '';
  });

  const taxId = computed(() => {
    const q = unref(quoteRef);
    return q?.requester?.tax_id ?? q?.guest_tax_id ?? '';
  });

  return { companyName, contactName, email, phone, taxId };
}
