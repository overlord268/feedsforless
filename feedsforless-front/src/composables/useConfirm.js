import { ref } from 'vue';

const state = ref({
  visible: false,
  title: 'Confirm',
  message: '',
  confirmLabel: 'Confirm',
  cancelLabel: 'Cancel',
  variant: 'primary'
});

let resolveFn = null;

export function useConfirm() {
  function show(options = {}) {
    state.value = {
      visible: true,
      title: options.title ?? 'Confirm',
      message: options.message ?? '',
      confirmLabel: options.confirmLabel ?? 'Confirm',
      cancelLabel: options.cancelLabel ?? 'Cancel',
      variant: options.variant ?? 'primary'
    };
    return new Promise((resolve) => {
      resolveFn = resolve;
    });
  }

  function confirm() {
    if (resolveFn) resolveFn(true);
    resolveFn = null;
    state.value.visible = false;
  }

  function cancel() {
    if (resolveFn) resolveFn(false);
    resolveFn = null;
    state.value.visible = false;
  }

  return { state, show, confirm, cancel };
}
