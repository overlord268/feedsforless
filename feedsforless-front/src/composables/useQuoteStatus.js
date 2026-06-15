const STATUS_LABELS = {
  pending: 'Under review',
  quoted: 'Quote ready',
  accepted: 'Confirmed',
  rejected: 'Declined',
  expired: 'Expired',
  cancelled: 'Cancelled',
};

const STATUS_CLASSES = {
  pending: 'bg-amber-50 text-amber-800 ring-amber-200',
  quoted: 'bg-blue-50 text-blue-800 ring-blue-200',
  accepted: 'bg-emerald-50 text-emerald-800 ring-emerald-200',
  rejected: 'bg-red-50 text-red-800 ring-red-200',
  expired: 'bg-slate-100 text-slate-600 ring-slate-200',
  cancelled: 'bg-slate-100 text-slate-600 ring-slate-200',
};

export function quoteStatusLabel(status) {
  return STATUS_LABELS[status] || status || 'Unknown';
}

export function quoteStatusClass(status) {
  return STATUS_CLASSES[status] || 'bg-slate-100 text-slate-600 ring-slate-200';
}

export function quoteIsClosed(status) {
  return ['accepted', 'rejected', 'expired', 'cancelled'].includes(status);
}

export function quoteIsActive(status) {
  return ['pending', 'quoted'].includes(status);
}

/**
 * Timeline steps for customer quote tracking.
 * @param {string} status
 */
export function quoteTimelineSteps(status) {
  const closedNegative = ['rejected', 'expired', 'cancelled'].includes(status);
  const steps = [
    { key: 'submitted', label: 'Submitted', description: 'Your request was received.' },
    { key: 'review', label: 'Under review', description: 'Our team is preparing pricing and logistics.' },
    { key: 'quoted', label: 'Quote ready', description: 'Review pricing and accept or decline.' },
    { key: 'done', label: closedNegative ? 'Closed' : 'Confirmed', description: closedNegative ? 'This request is no longer active.' : 'Quote accepted — next steps via your account rep.' },
  ];

  const indexByStatus = {
    pending: 1,
    quoted: 2,
    accepted: 3,
    rejected: 3,
    expired: 3,
    cancelled: 3,
  };

  const currentIndex = indexByStatus[status] ?? 0;

  return steps.map((step, i) => ({
    ...step,
    state: i < currentIndex ? 'complete' : i === currentIndex ? 'current' : 'upcoming',
    isLast: i === steps.length - 1,
  }));
}

export function formatQuoteMoney(value) {
  const n = Number(value);
  if (Number.isNaN(n)) return '0.00';
  return n.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

export function formatQuoteDate(iso) {
  if (!iso) return '—';
  try {
    return new Date(iso).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
  } catch {
    return '—';
  }
}

export function lineItemTotal(item) {
  const product = Number(item?.estimated_product_cost) || 0;
  const freight = Number(item?.estimated_freight_cost) || 0;
  const qty = Number(item?.qty) || 0;
  if (item?.line_total_cost != null && item.line_total_cost !== '') {
    return Number(item.line_total_cost);
  }
  return (product + freight) * qty;
}
