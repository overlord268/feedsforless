/** @typedef {'percentage' | 'fixed_price'} TierPricingMode */

/**
 * @param {Array<{ pricing_mode?: string }>} tiers
 */
export function derivePricingModeFromTiers(tiers) {
  return tiers?.[0]?.pricing_mode === 'fixed_price' ? 'fixed_price' : 'percentage';
}

/**
 * On save only: last tier has no upper cap (∞).
 * @param {Array<{ max_quantity?: number|null }>} tiers
 */
export function ensureLastTierOpenEnded(tiers) {
  if (!tiers?.length) return;
  tiers[tiers.length - 1].max_quantity = null;
}

/**
 * @param {Array<{ max_quantity?: number|null, min_quantity?: number }>} tiers
 */
export function canAddVolumeTier(tiers) {
  if (!tiers?.length) return true;
  const last = tiers[tiers.length - 1];
  const max = Number(last.max_quantity);
  const min = Number(last.min_quantity) || 1;
  return Number.isFinite(max) && max > min;
}

/**
 * Keep tier min quantities chained: tier[0].min = 1, tier[i].min = tier[i-1].max
 * @param {Array<{ min_quantity?: number, max_quantity?: number|null }>} tiers
 */
export function syncVolumeTierChain(tiers) {
  if (!Array.isArray(tiers) || tiers.length === 0) return;
  tiers[0].min_quantity = 1;
  for (let i = 1; i < tiers.length; i++) {
    const prevMax = Number(tiers[i - 1].max_quantity);
    tiers[i].min_quantity = Number.isFinite(prevMax) && prevMax > 0 ? prevMax : tiers[i].min_quantity;
  }
}

/**
 * @param {Array<{ min_quantity?: number, max_quantity?: number|null }>} tiers
 * @param {number} tierIndex
 */
export function onPresentationTierMaxChange(tiers, tierIndex) {
  const tier = tiers[tierIndex];
  if (!tier) return;
  const min = Number(tier.min_quantity) || 1;
  const max = Number(tier.max_quantity);
  if (Number.isFinite(max) && max <= min) {
    tier.max_quantity = min + 1;
  }
  syncVolumeTierChain(tiers);
}

/**
 * @param {{ tier_pricing_mode?: TierPricingMode, volume_tiers?: unknown[] }} pres
 * @param {number} minQuantity
 */
export function createVolumeTier(pres, minQuantity) {
  const mode = pres.tier_pricing_mode === 'fixed_price' ? 'fixed_price' : 'percentage';
  return {
    tier_name: '',
    min_quantity: minQuantity,
    max_quantity: null,
    pricing_mode: mode,
    discount_percentage: 0,
    fixed_price: null,
  };
}

/**
 * User toggled All % / All $ — reset incompatible value fields.
 * @param {{ tier_pricing_mode?: TierPricingMode, volume_tiers?: Array<Record<string, unknown>> }} pres
 * @param {TierPricingMode} mode
 */
export function applyPresentationPricingMode(pres, mode) {
  pres.tier_pricing_mode = mode;
  for (const tier of pres.volume_tiers || []) {
    tier.pricing_mode = mode;
    if (mode === 'fixed_price') {
      tier.discount_percentage = 0;
    } else {
      tier.fixed_price = null;
    }
  }
}

/**
 * Sync chain while editing — does not clear the last tier's "To" field.
 * @param {{ tier_pricing_mode?: TierPricingMode, volume_tiers?: Array<Record<string, unknown>> }} pres
 */
export function normalizePresentationVolumeTiers(pres) {
  if (!pres.volume_tiers?.length) {
    pres.tier_pricing_mode = pres.tier_pricing_mode || 'percentage';
    return;
  }

  if (!pres.tier_pricing_mode) {
    pres.tier_pricing_mode = derivePricingModeFromTiers(pres.volume_tiers);
  }

  for (const tier of pres.volume_tiers) {
    tier.pricing_mode = pres.tier_pricing_mode;
  }

  syncVolumeTierChain(pres.volume_tiers);
}

/**
 * @param {{ tier_pricing_mode?: TierPricingMode, volume_tiers?: Array<Record<string, unknown>> }} pres
 */
export function finalizePresentationVolumeTiersForSave(pres) {
  normalizePresentationVolumeTiers(pres);
  ensureLastTierOpenEnded(pres.volume_tiers);
}

export function presentationHasTiers(pres) {
  return Array.isArray(pres?.volume_tiers) && pres.volume_tiers.length > 0;
}

/** Base price is editable for All %; locked for All $ when tiers exist. */
export function isPresentationBasePriceLocked(pres) {
  return presentationHasTiers(pres) && pres.tier_pricing_mode === 'fixed_price';
}

/**
 * @param {{ tier_pricing_mode?: TierPricingMode, base_price_per_unit?: number, volume_tiers?: Array<{ min_quantity?: number, max_quantity?: number|null }> }} pres
 * @param {number} presentationIndex
 * @returns {string|null}
 */
export function validatePresentationVolumeTiers(pres, presentationIndex) {
  const tiers = pres.volume_tiers || [];
  if (!tiers.length) return null;

  const label = `Presentation ${presentationIndex + 1}`;

  if (pres.tier_pricing_mode === 'percentage') {
    const base = Number(pres.base_price_per_unit);
    if (!Number.isFinite(base) || base <= 0) {
      return `${label}: set a base price before using % volume tiers.`;
    }
  }

  for (let i = 0; i < tiers.length - 1; i++) {
    const min = Number(tiers[i].min_quantity) || 1;
    const max = Number(tiers[i].max_quantity);
    if (!Number.isFinite(max) || max <= min) {
      return `${label}: tier ${i + 1} needs a valid "To" value before the next row.`;
    }
  }

  return null;
}

/**
 * @param {unknown} value
 */
export function parseTierNumber(value) {
  if (value == null || value === '') return null;
  const n = Number(value);
  return Number.isFinite(n) ? n : null;
}
