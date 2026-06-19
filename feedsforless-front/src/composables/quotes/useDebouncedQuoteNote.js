import { ref, watch, unref } from 'vue';
import { updateQuoteStatus } from '../../services/adminQuotesApi';
import { useToast } from '../useToast';

export function useDebouncedQuoteNote(quoteIdRef, quoteRef, detailForm, { delayMs = 700 } = {}) {
  const toast = useToast();
  const savingNote = ref(false);
  const noteSaved = ref(false);
  let noteDebounceTimer = null;
  let ignoreFirstNoteWatch = true;

  watch(
    () => detailForm.admin_note,
    (newVal) => {
      if (ignoreFirstNoteWatch) {
        ignoreFirstNoteWatch = false;
        return;
      }

      noteSaved.value = false;
      clearTimeout(noteDebounceTimer);

      noteDebounceTimer = setTimeout(async () => {
        const quote = unref(quoteRef);
        const quoteId = unref(quoteIdRef);
        if (!quote || !quoteId) return;
        savingNote.value = true;
        try {
          await updateQuoteStatus(quoteId, {
            status: detailForm.status,
            admin_note: newVal,
          });
          quote.admin_note = newVal;
          noteSaved.value = true;
          setTimeout(() => { noteSaved.value = false; }, 3000);
        } catch (e) {
          console.error('Error auto-saving note', e);
          toast.error('Failed to auto-save note.');
        } finally {
          savingNote.value = false;
        }
      }, delayMs);
    },
  );

  function resetNoteWatch() {
    ignoreFirstNoteWatch = true;
  }

  return { savingNote, noteSaved, resetNoteWatch };
}
