const STATUS_LABELS = {
  pending: 'Under review',
  quoted: 'Quote ready',
  accepted: 'Confirmed',
  rejected: 'Declined',
  expired: 'Expired',
  cancelled: 'Cancelled',
};

const STATUS_CLASSES_CUSTOMER = {
  pending: 'bg-amber-50 text-amber-800 ring-amber-200',
  quoted: 'bg-blue-50 text-blue-800 ring-blue-200',
  accepted: 'bg-emerald-50 text-emerald-800 ring-emerald-200',
  rejected: 'bg-red-50 text-red-800 ring-red-200',
  expired: 'bg-slate-100 text-slate-600 ring-slate-200',
  cancelled: 'bg-slate-100 text-slate-600 ring-slate-200',
};

const STATUS_CLASSES_ADMIN = {
  pending: 'bg-amber-100 text-amber-700 border border-amber-200',
  quoted: 'bg-blue-100 text-blue-700 border border-blue-200',
  accepted: 'bg-emerald-100 text-emerald-700 border border-emerald-200',
  rejected: 'bg-red-100 text-red-700 border border-red-200',
  expired: 'bg-slate-100 text-slate-600 border border-slate-200',
  cancelled: 'bg-slate-100 text-slate-600 border border-slate-200',
};

const STATUS_CLASSES_BADGE = {
  pending: 'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-300',
  quoted: 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300',
  accepted: 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-300',
  rejected: 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300',
  expired: 'bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-400',
  cancelled: 'bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-400',
};

const STATUS_CLASS_MAP = {
  customer: STATUS_CLASSES_CUSTOMER,
  admin: STATUS_CLASSES_ADMIN,
  badge: STATUS_CLASSES_BADGE,
};

export function quoteStatusLabel(status) {
  return STATUS_LABELS[status] || status || 'Unknown';
}

export function quoteStatusClass(status, { variant = 'customer' } = {}) {
  const map = STATUS_CLASS_MAP[variant] ?? STATUS_CLASSES_CUSTOMER;
  const fallback = variant === 'customer'
    ? 'bg-slate-100 text-slate-600 ring-slate-200'
    : variant === 'badge'
      ? 'bg-slate-100 text-slate-600'
      : 'bg-slate-100 text-slate-600 border border-slate-200';
  return map[status] || fallback;
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

export function lineItemTotal(item, prices = null) {
  const product = Number(prices?.estimated_product_cost ?? item?.estimated_product_cost) || 0;
  const freight = Number(prices?.estimated_freight_cost ?? item?.estimated_freight_cost) || 0;
  const qty = Number(item?.qty) || 0;
  if (!prices && item?.line_total_cost != null && item.line_total_cost !== '') {
    return Number(item.line_total_cost);
  }
  return (product + freight) * qty;
}

export function quoteCustomerName(quote) {
  return quote?.customer_name || quote?.requester?.email || '—';
}

export function quoteDetailLink(quote) {
  const base = { name: 'AdminQuoteDetails', params: { id: quote.id } };
  if ((quote.quote_chat_unread_count ?? 0) > 0) {
    return { ...base, hash: '#quote-chat' };
  }
  return base;
}
