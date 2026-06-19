import { reactive, computed, ref, unref } from 'vue';
import { updateQuoteStatus } from '../../services/adminQuotesApi';
import { useToast } from '../useToast';

export function useQuoteStatusForm(quoteIdRef, quoteRef) {
  const toast = useToast();
  const detailForm = reactive({ status: 'pending', admin_note: '', customer_message: '' });
  const savingStatus = ref(false);

  const showCustomerMessage = computed(() =>
    ['rejected', 'cancelled', 'expired'].includes(detailForm.status),
  );

  function syncFromQuote() {
    const quote = unref(quoteRef);
    detailForm.status = quote?.status || 'pending';
    detailForm.admin_note = quote?.admin_note || '';
    detailForm.customer_message = quote?.customer_message || '';
  }

  async function saveStatus() {
    const quote = unref(quoteRef);
    const quoteId = unref(quoteIdRef);
    if (!quote || !quoteId) return false;
    savingStatus.value = true;
    try {
      await updateQuoteStatus(quoteId, {
        status: detailForm.status,
        admin_note: detailForm.admin_note,
        customer_message: detailForm.customer_message || null,
      });
      quote.status = detailForm.status;
      quote.customer_message = detailForm.customer_message;
      toast.success('Status updated successfully.');
      return true;
    } catch (e) {
      console.error(e);
      toast.error('Could not update status.');
      return false;
    } finally {
      savingStatus.value = false;
    }
  }

  return {
    detailForm,
    savingStatus,
    showCustomerMessage,
    syncFromQuote,
    saveStatus,
  };
}
