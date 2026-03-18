<template>
  <header
    class="sticky top-0 z-20 flex flex-wrap items-center justify-between gap-4 px-6 py-4 bg-white border-b border-slate-200 shrink-0"
  >
    <h1 class="text-xl font-bold text-slate-800 tracking-tight">
      {{ isEdit ? 'Edit Product' : 'Create New Product' }}
    </h1>
    <div class="flex items-center gap-3">
      <div class="flex items-center gap-2">
        <span class="text-sm font-medium text-slate-600">Status</span>
        <template v-if="status === 'published'">
          <span class="inline-flex items-center px-3 py-1.5 rounded-lg bg-emerald-100 text-emerald-800 text-sm font-medium">Published</span>
          <button
            type="button"
            class="px-3 py-1.5 text-sm font-medium text-slate-600 hover:text-slate-800 hover:bg-slate-100 rounded-lg transition-colors"
            @click="emit('update:status', 'draft')"
          >
            Unpublish
          </button>
        </template>
        <template v-else>
          <span class="inline-flex items-center px-3 py-1.5 rounded-lg bg-slate-100 text-slate-600 text-sm font-medium">Draft</span>
          <button
            type="button"
            class="px-4 py-2 rounded-xl bg-emerald-600 text-white text-sm font-medium hover:bg-emerald-700 transition-colors shadow-sm"
            @click="emit('update:status', 'published')"
          >
            Publish
          </button>
        </template>
      </div>
      <button
        type="button"
        class="px-4 py-2.5 bg-emerald-600 text-white font-medium rounded-xl hover:bg-emerald-700 transition-colors shadow-sm disabled:opacity-70 disabled:cursor-not-allowed"
        :disabled="saving"
        @click="emit('save')"
      >
        {{ saving ? 'Saving…' : 'Save Changes' }}
      </button>
      <button
        type="button"
        class="px-4 py-2.5 border border-slate-300 text-slate-700 font-medium rounded-xl hover:bg-slate-50 transition-colors"
        @click="emit('discard')"
      >
        Discard
      </button>
      <router-link
        :to="closeTo"
        class="p-2.5 text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded-xl transition-colors"
        aria-label="Close"
      >
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
        </svg>
      </router-link>
    </div>
  </header>
</template>

<script setup>
defineProps({
  /** Whether we are editing an existing product. */
  isEdit: {
    type: Boolean,
    default: false,
  },
  /** Current status: 'draft' | 'published'. */
  status: {
    type: String,
    default: 'draft',
  },
  /** Whether save is in progress (disables Save button). */
  saving: {
    type: Boolean,
    default: false,
  },
  /** Route for the Close link (e.g. { name: 'AdminProducts' }). */
  closeTo: {
    type: Object,
    default: () => ({ name: 'AdminProducts' }),
  },
});

const emit = defineEmits(['update:status', 'save', 'discard']);
</script>
