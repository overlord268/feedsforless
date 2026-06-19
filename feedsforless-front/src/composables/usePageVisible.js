import { ref, onMounted, onUnmounted } from 'vue';

export function usePageVisible(onVisible, onHidden) {
  function handleVisibility() {
    if (document.hidden) {
      onHidden?.();
    } else {
      onVisible?.();
    }
  }

  onMounted(() => {
    document.addEventListener('visibilitychange', handleVisibility);
  });

  onUnmounted(() => {
    document.removeEventListener('visibilitychange', handleVisibility);
  });

  return { isVisible: () => !document.hidden };
}

export function useIntervalWhenVisible(callback, intervalMs) {
  let timer = null;

  function start() {
    stop();
    if (document.hidden) return;
    timer = window.setInterval(callback, intervalMs);
  }

  function stop() {
    if (timer) {
      clearInterval(timer);
      timer = null;
    }
  }

  usePageVisible(start, stop);

  onMounted(start);
  onUnmounted(stop);

  return { start, stop };
}
