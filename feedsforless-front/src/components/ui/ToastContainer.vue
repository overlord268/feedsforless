<template>
  <div class="fixed top-3 right-0 z-[100] flex flex-col items-end gap-2 pr-3 pointer-events-none">
    <TransitionGroup name="toast" tag="div" class="flex flex-col items-end gap-2">
      <div
        v-for="t in toasts"
        :key="t.id"
        class="pointer-events-auto flex items-center gap-2 py-2 pl-3 pr-2 rounded-lg shadow-lg border backdrop-blur-sm w-max max-w-[260px]"
        :class="toastClass(t.type)"
      >
        <span class="shrink-0" :class="iconClass(t.type)">
          <svg v-if="t.type === 'success'" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
          <svg v-else-if="t.type === 'error'" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
          <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        </span>
        <p class="text-xs font-medium flex-1 min-w-0 leading-snug">{{ t.message }}</p>
        <button type="button" @click="remove(t.id)" class="shrink-0 p-1 -mr-0.5 rounded opacity-60 hover:opacity-100 transition-opacity" aria-label="Dismiss">
          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
      </div>
    </TransitionGroup>
  </div>
</template>

<script setup>
import { useToast } from '../../composables/useToast';

const { toasts, remove } = useToast();

function toastClass(type) {
  switch (type) {
    case 'success':
      return 'bg-emerald-50/95 border-emerald-200 text-emerald-900';
    case 'error':
      return 'bg-red-50/95 border-red-200 text-red-900';
    default:
      return 'bg-slate-50/95 border-slate-200 text-slate-900';
  }
}

function iconClass(type) {
  switch (type) {
    case 'success':
      return 'text-emerald-600';
    case 'error':
      return 'text-red-600';
    default:
      return 'text-blue-600';
  }
}
</script>

<style scoped>
.toast-enter-active,
.toast-leave-active {
  transition: all 0.3s ease;
}
.toast-enter-from {
  opacity: 0;
  transform: translateX(100%);
}
.toast-leave-to {
  opacity: 0;
  transform: translateX(-100%);
}
.toast-move {
  transition: transform 0.3s ease;
}
</style>
