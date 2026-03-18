<template>
  <div
    v-if="show"
    class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/40 backdrop-blur-sm"
    @click.self="emit('close')"
  >
    <div
      class="bg-white rounded-2xl shadow-xl max-w-md w-full p-6 border border-slate-200/80"
      role="dialog"
      aria-modal="true"
      :aria-labelledby="titleId"
    >
      <h2 :id="titleId" class="text-lg font-semibold text-slate-900 mb-4">
        {{ title }}
      </h2>
      <form @submit.prevent="emit('submit')" class="space-y-4">
        <slot />
        <div class="flex gap-2 justify-end pt-2">
          <button
            type="button"
            class="px-4 py-2.5 rounded-xl border border-slate-200 text-slate-700 hover:bg-slate-50 font-medium transition-colors"
            @click="emit('close')"
          >
            {{ cancelLabel }}
          </button>
          <button
            type="submit"
            class="px-4 py-2.5 rounded-xl bg-emerald-600 text-white font-medium hover:bg-emerald-700 shadow-sm shadow-emerald-500/20 transition-colors disabled:opacity-70 disabled:cursor-not-allowed"
            :disabled="saving"
          >
            {{ saveLabel }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { computed, useId } from 'vue';

defineProps({
  /** Whether the modal is visible. */
  show: {
    type: Boolean,
    default: false,
  },
  /** Modal title (e.g. "Add measure unit" / "Edit measure unit"). */
  title: {
    type: String,
    default: '',
  },
  /** Whether a submit is in progress (disables Save button). */
  saving: {
    type: Boolean,
    default: false,
  },
  /** Label for the cancel button. */
  cancelLabel: {
    type: String,
    default: 'Cancel',
  },
  /** Label for the submit button. */
  saveLabel: {
    type: String,
    default: 'Save',
  },
});

const emit = defineEmits(['close', 'submit']);

const uid = useId();
const titleId = computed(() => `simple-form-modal-title-${uid}`);
</script>
