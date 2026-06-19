import { reactive, computed, ref, unref, watch } from 'vue';
import { lineItemTotal } from '../useQuoteStatus';
import { updateQuotePrices } from '../../services/adminQuotesApi';
import { useToast } from '../useToast';

function priceRowKey(itemId) {
  return String(itemId);
}

export function useQuotePricing(quoteRef) {
  const toast = useToast();
  const priceForm = reactive({});
  const savingPrices = ref(false);

  function syncFromQuote() {
    const quote = unref(quoteRef);
    Object.keys(priceForm).forEach((k) => delete priceForm[k]);
    (quote?.items || []).forEach((it) => {
      priceForm[priceRowKey(it.id)] = {
        estimated_product_cost: Number(it.estimated_product_cost) || 0,
        estimated_freight_cost: Number(it.estimated_freight_cost) || 0,
      };
    });
  }

  function getLineTotal(item) {
    return lineItemTotal(item, priceForm[priceRowKey(item.id)]);
  }

  const computedTotalCost = computed(() => {
    const quote = unref(quoteRef);
    if (!quote?.items) return quote?.total_estimated_cost || 0;
    return quote.items.reduce((sum, item) => sum + getLineTotal(item), 0);
  });

  async function savePrices() {
    const quote = unref(quoteRef);
    if (!quote?.items?.length) return null;
    savingPrices.value = true;
    try {
      const items = quote.items.map((it) => ({
        id: it.id,
        estimated_product_cost: Number(priceForm[priceRowKey(it.id)]?.estimated_product_cost) || 0,
        estimated_freight_cost: Number(priceForm[priceRowKey(it.id)]?.estimated_freight_cost) || 0,
      }));
      const updated = await updateQuotePrices(quote.id, items);
      syncFromQuote();
      toast.success('Prices saved and quote marked as Quoted.');
      return updated;
    } catch (e) {
      console.error(e);
      toast.error('Error saving prices. Ensure each line has a valid cost.');
      return null;
    } finally {
      savingPrices.value = false;
    }
  }

  watch(
    () => unref(quoteRef)?.items,
    () => syncFromQuote(),
    { immediate: true },
  );

  return {
    priceForm,
    priceRowKey,
    savingPrices,
    computedTotalCost,
    getLineTotal,
    syncFromQuote,
    savePrices,
  };
}
