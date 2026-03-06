import { ref } from 'vue';

const toasts = ref([]);

export function useToast() {
  function toast(message, type = 'info') {
    const id = Math.random().toString(36).slice(2);
    toasts.value = [...toasts.value, { id, message, type }];
    setTimeout(() => remove(id), 4500);
    return id;
  }

  function remove(id) {
    toasts.value = toasts.value.filter((t) => t.id !== id);
  }

  function success(message) {
    return toast(message, 'success');
  }

  function error(message) {
    return toast(message, 'error');
  }

  function info(message) {
    return toast(message, 'info');
  }

  return { toasts, toast, remove, success, error, info };
}
