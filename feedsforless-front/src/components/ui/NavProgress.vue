<template>
  <Transition name="nav-progress">
    <div
      v-show="isNavigating"
      class="fixed top-0 left-0 right-0 h-0.5 bg-emerald-500 z-[10000] overflow-hidden pointer-events-none"
      role="progressbar"
      aria-hidden="true"
    >
      <div class="absolute inset-0 w-1/3 bg-emerald-400/90 animate-progress rounded-r-full" />
    </div>
  </Transition>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { useRouter } from 'vue-router';

const router = useRouter();
const isNavigating = ref(false);

let timeoutId = null;

function start() {
  if (timeoutId) clearTimeout(timeoutId);
  isNavigating.value = true;
  timeoutId = setTimeout(() => {
    timeoutId = null;
  }, 100);
}

function end() {
  timeoutId = setTimeout(() => {
    isNavigating.value = false;
    timeoutId = null;
  }, 200);
}

onMounted(() => {
  router.beforeEach(() => { start(); });
  router.afterEach(end);
  router.onError(end);
});

onUnmounted(() => {
  if (timeoutId) clearTimeout(timeoutId);
});
</script>

<style scoped>
.nav-progress-enter-active,
.nav-progress-leave-active {
  transition: opacity 0.15s ease;
}
.nav-progress-enter-from,
.nav-progress-leave-to {
  opacity: 0;
}
</style>
