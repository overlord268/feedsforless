<template>
  <div
    class="mb-6 rounded-xl border-2 border-amber-200 dark:border-amber-600/60 bg-amber-50 dark:bg-amber-950/40 shadow-sm overflow-hidden"
  >
    <div class="flex items-start gap-4 p-4 md:p-5">
      <div
        class="shrink-0 w-10 h-10 rounded-lg bg-amber-500/20 dark:bg-amber-500/30 flex items-center justify-center"
      >
        <svg
          class="w-5 h-5 text-amber-600 dark:text-amber-400"
          fill="none"
          stroke="currentColor"
          viewBox="0 0 24 24"
        >
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"
          />
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"
          />
        </svg>
      </div>
      <div class="min-w-0 flex-1">
        <h3
          class="text-sm font-bold text-amber-900 dark:text-amber-200 uppercase tracking-wide mb-1"
        >
          {{ title }}
        </h3>
        <p class="text-sm text-amber-800 dark:text-amber-300/90 leading-relaxed">
          {{ message }}
        </p>
      </div>
    </div>
  </div>
</template>

<script setup>
defineProps({
  /** Banner heading (e.g. "Demo view only"). */
  title: {
    type: String,
    default: 'Demo view only',
  },
  /** Body text explaining the demo context. */
  message: {
    type: String,
    default:
      'You are viewing the catalog as a client would see it. This screen is for preview only—you cannot request quotes or complete purchase flows here. Use the admin menu to manage the portal.',
  },
});
</script>
