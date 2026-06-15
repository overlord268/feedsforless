<template>
  <div class="w-full space-y-6">
    <div class="flex items-center justify-between gap-4">
      <h2 class="text-lg font-semibold text-slate-800">Presentations and volume pricing</h2>
      <button
        type="button"
        class="shrink-0 px-4 py-2 bg-emerald-600 text-white text-sm font-medium rounded-xl hover:bg-emerald-700 transition-colors"
        @click="emit('add-presentation')"
      >
        + Add Presentation
      </button>
    </div>
    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
      <div
        v-for="(pres, index) in form.packaging"
        :key="index"
        class="presentation-card min-w-0 border border-slate-200 rounded-2xl p-5 lg:p-6 bg-white shadow-sm space-y-4"
      >
        <div class="flex justify-between items-start gap-2">
          <span class="font-medium text-slate-700">Presentation {{ index + 1 }}</span>
          <button
            v-if="form.packaging.length > 1"
            type="button"
            class="text-slate-400 hover:text-red-600 p-1 shrink-0"
            @click="emit('remove-presentation', index)"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
          </button>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div class="sm:col-span-2">
            <label class="block text-sm font-medium text-slate-600 mb-1">Packaging type</label>
            <div class="flex gap-2 min-w-0">
              <select
                v-model="pres.packaging_type_id"
                class="flex-1 min-w-0 px-3 py-2.5 border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 text-sm"
              >
                <option :value="null">Select…</option>
                <option v-for="pt in packagingTypes" :key="pt.id" :value="pt.id">{{ pt.name || pt.label }}</option>
              </select>
              <button type="button" class="shrink-0 p-2.5 border border-slate-200 rounded-xl text-emerald-600 hover:bg-emerald-50" title="Add packaging type" @click="emit('open-add-master', 'packaging_type', { packagingIndex: index })">+</button>
            </div>
          </div>
          <div class="min-w-0">
            <label class="block text-sm font-medium text-slate-600 mb-1">Quantity per pallet</label>
            <input
              v-model.number="pres.quantity_per_pallet"
              type="number"
              min="1"
              class="w-full min-w-0 px-3 py-2.5 border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 text-sm"
            />
          </div>
          <div class="min-w-0">
            <label class="block text-sm font-medium text-slate-600 mb-1">Base price</label>
            <input
              v-model.number="pres.base_price_per_unit"
              type="number"
              step="0.01"
              min="0"
              :disabled="isPresentationBasePriceLocked(pres)"
              class="w-full min-w-0 px-3 py-2.5 border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 text-sm disabled:bg-slate-100 disabled:text-slate-400 disabled:cursor-not-allowed"
            />
            <p v-if="isPresentationBasePriceLocked(pres)" class="mt-1 text-[11px] text-slate-500 leading-snug">
              Locked with fixed-price ($) tiers — each row sets the price for its range.
            </p>
            <p v-else-if="presentationHasTiers(pres)" class="mt-1 text-[11px] text-slate-500 leading-snug">
              Reference for % volume discounts on this presentation.
            </p>
            <p v-else class="mt-1 text-[11px] text-slate-500 leading-snug">
              Price per ton for this packaging option.
            </p>
          </div>
        </div>
        <div class="min-w-0">
          <div class="flex flex-wrap items-center justify-between mb-2 gap-2">
            <label class="text-sm font-medium text-slate-600">Volume tiers</label>
            <div class="flex items-center gap-2">
              <div
                v-if="presentationHasTiers(pres)"
                class="inline-flex rounded-lg border border-slate-200 overflow-hidden text-xs font-semibold"
              >
                <button
                  type="button"
                  class="px-2.5 py-1.5 transition-colors"
                  :class="pres.tier_pricing_mode !== 'fixed_price' ? 'bg-emerald-600 text-white' : 'bg-white text-slate-600 hover:bg-slate-50'"
                  @click="setPresentationPricingMode(pres, 'percentage')"
                >
                  All %
                </button>
                <button
                  type="button"
                  class="px-2.5 py-1.5 border-l border-slate-200 transition-colors"
                  :class="pres.tier_pricing_mode === 'fixed_price' ? 'bg-emerald-600 text-white' : 'bg-white text-slate-600 hover:bg-slate-50'"
                  @click="setPresentationPricingMode(pres, 'fixed_price')"
                >
                  All $
                </button>
              </div>
              <button
                type="button"
                class="text-sm text-emerald-600 hover:underline shrink-0"
                @click="emit('add-volume-tier', index)"
              >
                + Add row
              </button>
            </div>
          </div>
          <div v-if="pres.volume_tiers.length === 0" class="text-xs text-slate-500 py-2">
            No volume tiers — pricing uses base price only.
            <button type="button" class="text-emerald-600 hover:underline ml-1" @click="emit('add-volume-tier', index)">
              Add first tier
            </button>
          </div>
          <template v-else>
            <div class="tier-header tier-row-grid gap-x-2 mb-1 px-0.5 text-[10px] font-semibold text-slate-500 uppercase tracking-wide">
              <span>From</span>
              <span>To</span>
              <span>{{ pres.tier_pricing_mode === 'fixed_price' ? 'Price' : 'Discount' }}</span>
              <span></span>
            </div>
            <div class="space-y-1.5">
              <div
                v-for="(tier, ti) in pres.volume_tiers"
                :key="ti"
                class="tier-row-grid gap-x-2 gap-y-2 items-center"
              >
                <input
                  :value="tier.min_quantity"
                  type="number"
                  readonly
                  tabindex="-1"
                  class="tier-input min-w-0 tier-input-readonly"
                  title="Start (auto)"
                />
                <input
                  v-model.number="tier.max_quantity"
                  type="number"
                  :min="(Number(tier.min_quantity) || 1) + 1"
                  class="tier-input min-w-0"
                  :placeholder="ti === pres.volume_tiers.length - 1 ? '∞ if empty' : 'Required'"
                  title="End quantity"
                  @change="onTierMaxChange(pres, ti)"
                />
                <input
                  v-if="pres.tier_pricing_mode !== 'fixed_price'"
                  v-model.number="tier.discount_percentage"
                  type="number"
                  min="0"
                  max="100"
                  step="0.01"
                  class="tier-input min-w-0"
                  placeholder="%"
                />
                <input
                  v-else
                  v-model.number="tier.fixed_price"
                  type="number"
                  min="0"
                  step="0.01"
                  class="tier-input min-w-0"
                  placeholder="Price"
                />
                <button
                  type="button"
                  class="tier-remove"
                  title="Remove tier"
                  @click="emit('remove-volume-tier', index, ti)"
                >
                  ×
                </button>
              </div>
            </div>
            <p class="mt-2.5 text-xs text-slate-500 leading-relaxed">
              Set a "To" on the last row, then <strong>+ Add row</strong> for another tier.
              The final row saved applies to ∞ tons (leave its "To" empty).
              <span v-if="pres.tier_pricing_mode === 'fixed_price'"> All tiers use fixed $/T.</span>
              <span v-else> All tiers use % off base price.</span>
            </p>
          </template>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import {
  applyPresentationPricingMode,
  isPresentationBasePriceLocked,
  onPresentationTierMaxChange,
  presentationHasTiers,
} from '../../composables/useVolumeTierRules';

defineProps({
  form: { type: Object, required: true },
  packagingTypes: { type: Array, default: () => [] },
});

const emit = defineEmits([
  'add-presentation',
  'remove-presentation',
  'add-volume-tier',
  'remove-volume-tier',
  'open-add-master',
]);

function setPresentationPricingMode(pres, mode) {
  applyPresentationPricingMode(pres, mode);
}

function onTierMaxChange(pres, tierIndex) {
  onPresentationTierMaxChange(pres.volume_tiers, tierIndex);
}
</script>

<style scoped>
.presentation-card {
  container-type: inline-size;
}

.tier-row-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  min-width: 0;
}

.tier-header {
  display: none;
}

.tier-row-grid > :nth-child(4) {
  grid-column: 1 / -1;
  justify-self: end;
}

@container (min-width: 28rem) {
  .tier-header {
    display: grid;
  }

  .tier-row-grid {
    grid-template-columns: 3.25rem 3.25rem minmax(0, 1fr) 1.5rem;
  }

  .tier-row-grid > :nth-child(4) {
    grid-column: auto;
    justify-self: center;
  }
}

@container (min-width: 36rem) {
  .tier-row-grid {
    grid-template-columns: 3.75rem 3.75rem minmax(0, 1fr) 1.75rem;
  }
}

.tier-input {
  width: 100%;
  min-width: 0;
  padding: 0.35rem 0.4rem;
  font-size: 0.8125rem;
  line-height: 1.2;
  border: 1px solid rgb(226 232 240);
  border-radius: 0.375rem;
  background: white;
}

.tier-input-readonly {
  background: rgb(248 250 252);
  color: rgb(100 116 139);
  cursor: default;
}

.tier-input:focus {
  outline: none;
  box-shadow: 0 0 0 2px rgb(16 185 129 / 0.25);
  border-color: rgb(16 185 129);
}

.tier-input-readonly:focus {
  box-shadow: none;
  border-color: rgb(226 232 240);
}

.tier-remove {
  width: 1.5rem;
  height: 1.5rem;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.125rem;
  line-height: 1;
  color: rgb(148 163 184);
  border-radius: 0.25rem;
}

.tier-remove:hover {
  color: rgb(220 38 38);
  background: rgb(254 242 242);
}
</style>
