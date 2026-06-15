<template>
  <ol class="relative flex flex-col sm:flex-row sm:items-start gap-0 sm:gap-0">
    <li
      v-for="(step, i) in steps"
      :key="step.key"
      class="relative flex sm:flex-1 sm:flex-col sm:items-center gap-3 sm:gap-2 pb-8 sm:pb-0 last:pb-0"
    >
      <div class="flex sm:flex-col items-center gap-3 sm:gap-2 shrink-0">
        <div
          class="flex h-9 w-9 items-center justify-center rounded-full border-2 text-sm font-bold shrink-0 z-10 bg-white"
          :class="dotClass(step.state)"
        >
          <svg v-if="step.state === 'complete'" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
          </svg>
          <span v-else>{{ i + 1 }}</span>
        </div>
        <div
          v-if="!step.isLast"
          class="hidden sm:block absolute top-[18px] left-[calc(50%+20px)] right-[calc(-50%+20px)] h-0.5"
          :class="step.state === 'complete' ? 'bg-emerald-400' : 'bg-slate-200'"
        />
        <div
          v-if="!step.isLast"
          class="sm:hidden absolute left-[18px] top-10 bottom-0 w-0.5"
          :class="step.state === 'complete' ? 'bg-emerald-400' : 'bg-slate-200'"
        />
      </div>
      <div class="sm:text-center pt-0 sm:pt-1 min-w-0 flex-1">
        <p class="text-sm font-bold text-slate-900" :class="step.state === 'upcoming' ? 'text-slate-400' : ''">{{ step.label }}</p>
        <p class="text-xs text-slate-500 mt-0.5 leading-snug max-w-[180px] sm:mx-auto">{{ step.description }}</p>
      </div>
    </li>
  </ol>
</template>

<script setup>
defineProps({
  steps: { type: Array, default: () => [] },
});

function dotClass(state) {
  if (state === 'complete') return 'border-emerald-500 bg-emerald-500 text-white';
  if (state === 'current') return 'border-[#2962ff] bg-[#2962ff] text-white';
  return 'border-slate-200 text-slate-400';
}
</script>
