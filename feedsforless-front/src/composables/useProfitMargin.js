/**
 * Client-side profit margin preview (mirrors backend ProfitMarginService).
 */
export function applyProfitMargin(basePrice, marginPercent) {
  const base = Number(basePrice) || 0;
  const margin = Math.max(0, Number(marginPercent) || 0);
  return Math.round(base * (1 + margin / 100) * 100) / 100;
}

export function effectiveMarginPercent(productMargin, globalMargin) {
  if (productMargin !== null && productMargin !== undefined && productMargin !== '') {
    return Math.max(0, Number(productMargin));
  }
  return Math.max(0, Number(globalMargin) || 0);
}

export function marginSourceLabel(source) {
  if (source === 'tier') return 'Tier';
  if (source === 'product') return 'Product';
  return 'Global';
}

export function marginSourceBadgeClass(source) {
  if (source === 'tier') return 'bg-violet-100 text-violet-700 ring-violet-200';
  if (source === 'product') return 'bg-sky-100 text-sky-700 ring-sky-200';
  return 'bg-slate-100 text-slate-600 ring-slate-200';
}

export function customerPriceForTier(tier, presentationBase, marginPercent) {
  const margin = Math.max(0, Number(marginPercent) || 0);
  const mode = tier.pricing_mode || 'percentage';

  if (mode === 'fixed_price') {
    const fixed = Number(tier.fixed_price ?? tier.internal_base_price);
    if (Number.isNaN(fixed) || fixed < 0) return null;
    return applyProfitMargin(fixed, margin);
  }

  const base = Number(presentationBase);
  if (Number.isNaN(base) || base <= 0) return null;
  const disc = Math.max(0, Number(tier.discount_percentage) || 0);
  const marginedBase = applyProfitMargin(base, margin);
  return Math.round(marginedBase * (1 - disc / 100) * 100) / 100;
}

export function resolveTierMarginPercent(tier, productDraft, globalMargin, tierDraft) {
  if (tierDraft?.isCustom) {
    return Math.max(0, Number(tierDraft.marginInput) || 0);
  }
  if (tier.tier_profit_margin_percent != null && tier.tier_profit_margin_percent !== '') {
    return Math.max(0, Number(tier.tier_profit_margin_percent));
  }
  if (productDraft?.isCustom) {
    return Math.max(0, Number(productDraft.marginInput) || 0);
  }
  return Math.max(0, Number(globalMargin) || 0);
}

export function resolveTierMarginSource(tier, productDraft, tierDraft) {
  if (tierDraft?.isCustom || (tier.tier_profit_margin_percent != null && tier.tier_profit_margin_percent !== '')) {
    return tierDraft?.isCustom || tier.tier_profit_margin_percent != null ? 'tier' : 'product';
  }
  if (tierDraft?.isCustom) return 'tier';
  if (tier.tier_profit_margin_percent != null && tier.tier_profit_margin_percent !== '') return 'tier';
  if (productDraft?.isCustom || productDraft?.source === 'product') return 'product';
  return 'global';
}

export function previewPresentationGroups(groups, globalMargin, rowDraft) {
  const productMargin = rowDraft?.isCustom ? rowDraft.marginInput : null;

  return (groups || []).map((group) => {
    if (group.has_tiers && group.tiers?.length) {
      return {
        ...group,
        tiers: group.tiers.map((tier) => {
          const tierDraft = rowDraft?.tierDrafts?.[tier.id];
          const margin = resolveTierMarginPercent(tier, rowDraft, globalMargin, tierDraft);
          const source = tierDraft?.isCustom
            ? 'tier'
            : (tier.tier_profit_margin_percent != null ? 'tier' : (rowDraft?.isCustom ? 'product' : (tier.margin_source || 'global')));

          return {
            ...tier,
            effective_margin_percent: margin,
            margin_percent: margin,
            margin_source: source,
            price_with_margin: customerPriceForTier(tier, group.presentation_base, margin),
          };
        }),
      };
    }

    const margin = effectiveMarginPercent(productMargin, globalMargin);

    return {
      ...group,
      margin_percent: margin,
      price_with_margin: group.base_price != null
        ? applyProfitMargin(group.base_price, margin)
        : null,
    };
  });
}

export function productHasTiers(row) {
  return (row.presentation_groups || []).some((g) => g.has_tiers && g.tiers?.length);
}
