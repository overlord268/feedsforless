<template>
  <div
    v-if="show"
    class="relative w-full overflow-hidden border-b-4 border-green-600"
    role="region"
    aria-label="Welcome message for new subscribers"
  >
    <!-- Background image + overlays -->
    <div
      class="absolute inset-0 bg-cover bg-center bg-no-repeat scale-105"
      :style="bannerBackgroundStyle"
      aria-hidden="true"
    />
    <div class="absolute inset-0 bg-gradient-to-r from-[#001a33]/95 via-[#003366]/88 to-[#003366]/75" aria-hidden="true" />
    <div
      class="absolute inset-0 opacity-30 mix-blend-overlay"
      style="background-image: radial-gradient(circle at 20% 50%, rgba(34, 197, 94, 0.35) 0%, transparent 50%), radial-gradient(circle at 80% 20%, rgba(59, 130, 246, 0.25) 0%, transparent 45%);"
      aria-hidden="true"
    />

    <div class="relative z-10 w-full px-4 sm:px-6 lg:px-10 py-8 md:py-10 lg:py-12">
      <button
        type="button"
        class="absolute top-3 right-3 sm:top-4 sm:right-4 p-2 rounded-full text-white/70 hover:text-white hover:bg-white/15 transition-colors"
        aria-label="Dismiss welcome message"
        @click="$emit('dismiss')"
      >
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
        </svg>
      </button>

      <div class="flex flex-col lg:flex-row lg:items-center gap-6 lg:gap-10 max-w-[1400px] mx-auto pr-8 sm:pr-10">
        <div class="flex-1 min-w-0 space-y-3 md:space-y-4">
          <div class="inline-flex items-center gap-2 rounded-full bg-green-500/20 border border-green-400/40 px-3 py-1">
            <span class="flex h-2 w-2 rounded-full bg-green-400 animate-pulse" aria-hidden="true" />
            <span class="text-[11px] sm:text-xs font-bold uppercase tracking-widest text-green-300">
              Market Insights
            </span>
          </div>
          <h2 class="text-2xl sm:text-3xl lg:text-4xl font-black text-white leading-tight tracking-tight max-w-2xl">
            Welcome — you're on the list
          </h2>
          <p class="text-sm sm:text-base lg:text-lg text-blue-100/95 leading-relaxed max-w-2xl">
            Explore our commodity catalog while you wait for your first report.
            Create a free account to request delivered quotes, save RFQs, and chat with our team.
          </p>
        </div>

        <div class="shrink-0 flex flex-col items-stretch sm:items-end lg:items-end lg:self-center lg:ml-auto">
          <router-link
            :to="registerTo"
            class="group inline-flex items-center justify-center gap-2 bg-white text-[#003366] text-sm sm:text-base font-bold uppercase tracking-wide px-6 sm:px-8 py-3.5 sm:py-4 rounded-lg shadow-lg shadow-black/25 hover:bg-green-50 hover:shadow-xl transition-all whitespace-nowrap"
          >
            Create free account
            <svg
              class="w-5 h-5 group-hover:translate-x-1 transition-transform"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
              aria-hidden="true"
            >
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
            </svg>
          </router-link>
          <p class="mt-2 text-[11px] text-blue-200/80 text-center sm:text-right hidden sm:block">
            Already have an account?
            <router-link to="/login" class="text-white font-semibold underline-offset-2 hover:underline">
              Sign in
            </router-link>
          </p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
defineProps({
  show: { type: Boolean, default: false },
  registerTo: { type: Object, required: true },
});

defineEmits(['dismiss']);

/** Local assets: public/images/ (optimized via scripts/optimize-newsletter-banner.mjs) */
const bannerBackgroundStyle = {
  backgroundImage:
    "image-set(url('/images/newsletter-banner.webp') type('image/webp'), url('/images/newsletter-banner.jpg') type('image/jpeg'))",
};
</script>
