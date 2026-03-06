<template>
  <Transition name="modal">
    <div v-if="state.visible" class="fixed inset-0 z-[100] flex items-center justify-center p-4">
      <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="cancel" />
      <div class="relative w-full max-w-md bg-white rounded-2xl shadow-2xl border border-slate-200 overflow-hidden">
        <div class="p-6 sm:p-8">
          <h3 class="text-lg font-bold text-slate-900 mb-2">{{ state.title }}</h3>
          <p class="text-slate-600 text-sm sm:text-base leading-relaxed">{{ state.message }}</p>
        </div>
        <div class="flex gap-3 px-6 sm:px-8 pb-6 sm:pb-8">
          <button
            type="button"
            @click="cancel"
            class="flex-1 px-4 py-2.5 rounded-xl border border-slate-200 text-slate-700 font-medium hover:bg-slate-50 transition-colors"
          >
            {{ state.cancelLabel }}
          </button>
          <button
            type="button"
            @click="confirm"
            class="flex-1 px-4 py-2.5 rounded-xl font-medium transition-colors shadow-sm"
            :class="state.variant === 'danger' ? 'bg-red-600 text-white hover:bg-red-700 shadow-red-500/25' : 'bg-blue-600 text-white hover:bg-blue-700 shadow-blue-500/25'"
          >
            {{ state.confirmLabel }}
          </button>
        </div>
      </div>
    </div>
  </Transition>
</template>

<script setup>
import { useConfirm } from '../../composables/useConfirm';

const { state, confirm, cancel } = useConfirm();
</script>

<style scoped>
.modal-enter-active,
.modal-leave-active {
  transition: opacity 0.2s ease;
}
.modal-enter-from,
.modal-leave-to {
  opacity: 0;
}
.modal-enter-active .relative,
.modal-leave-active .relative {
  transition: transform 0.2s ease;
}
.modal-enter-from .relative,
.modal-leave-to .relative {
  transform: scale(0.95);
}
</style>
