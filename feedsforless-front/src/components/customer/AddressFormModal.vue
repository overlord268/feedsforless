<template>
  <Transition name="modal">
    <div v-if="show" class="fixed inset-0 z-[60] flex items-center justify-center p-4">
      <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="$emit('close')" />
      <div class="relative w-full max-w-md bg-white rounded-2xl shadow-xl border border-slate-200 overflow-hidden animate-in zoom-in-95 duration-200">
        <div class="px-6 py-4 border-b border-slate-200 flex items-center justify-between bg-slate-50/80">
          <h3 class="text-lg font-bold text-slate-900">{{ editing ? 'Edit address' : 'Add new address' }}</h3>
          <button type="button" @click="$emit('close')" class="p-2 rounded-xl text-slate-500 hover:bg-slate-200 hover:text-slate-800 transition-colors" aria-label="Close">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
          </button>
        </div>
        <form @submit.prevent="submit" class="p-6 space-y-4">
          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Address line 1 <span class="text-red-500">*</span></label>
            <input v-model="form.address_line_1" type="text" required class="w-full px-4 py-2.5 border border-slate-200 rounded-xl bg-white text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500" placeholder="Street, number, suite">
            <p v-if="errors.address_line_1" class="text-red-500 text-xs mt-1">{{ errors.address_line_1[0] }}</p>
          </div>
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-slate-700 mb-1">City <span class="text-red-500">*</span></label>
              <input v-model="form.city" type="text" required class="w-full px-4 py-2.5 border border-slate-200 rounded-xl bg-white text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500">
              <p v-if="errors.city" class="text-red-500 text-xs mt-1">{{ errors.city[0] }}</p>
            </div>
            <div>
              <label class="block text-sm font-medium text-slate-700 mb-1">State <span class="text-red-500">*</span></label>
              <input v-model="form.state" type="text" required class="w-full px-4 py-2.5 border border-slate-200 rounded-xl bg-white text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500">
              <p v-if="errors.state" class="text-red-500 text-xs mt-1">{{ errors.state[0] }}</p>
            </div>
          </div>
          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">ZIP code <span class="text-red-500">*</span></label>
            <input v-model="form.zip_code" type="text" required class="w-full px-4 py-2.5 border border-slate-200 rounded-xl bg-white text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500" placeholder="e.g. 12345">
            <p v-if="errors.zip_code" class="text-red-500 text-xs mt-1">{{ errors.zip_code[0] }}</p>
          </div>
          <div class="flex gap-3 pt-2">
            <button type="button" @click="$emit('close')" class="flex-1 px-4 py-2.5 rounded-xl border border-slate-200 text-slate-700 font-medium hover:bg-slate-50 transition-colors">Cancel</button>
            <button type="submit" :disabled="saving" class="flex-1 px-4 py-2.5 rounded-xl bg-blue-600 text-white font-medium hover:bg-blue-700 transition-colors disabled:opacity-70 flex items-center justify-center gap-2">
              <svg v-if="saving" class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/></svg>
              <span>{{ saving ? 'Saving…' : (editing ? 'Save changes' : 'Add address') }}</span>
            </button>
          </div>
        </form>
      </div>
    </div>
  </Transition>
</template>

<script setup>
import { ref, reactive, watch, computed } from 'vue';
import api from '../../services/api';

const props = defineProps({
  show: { type: Boolean, default: false },
  address: { type: Object, default: null }
});

const emit = defineEmits(['close', 'saved']);

const saving = ref(false);
const errors = ref({});

const form = reactive({
  address_line_1: '',
  city: '',
  state: '',
  zip_code: ''
});

const editing = computed(() => !!props.address?.id);

watch(() => props.show, (visible) => {
  if (visible) {
    errors.value = {};
    if (props.address) {
      form.address_line_1 = props.address.address_line_1 || '';
      form.city = props.address.city || '';
      form.state = props.address.state || '';
      form.zip_code = props.address.zip_code || '';
    } else {
      form.address_line_1 = '';
      form.city = '';
      form.state = '';
      form.zip_code = '';
    }
  }
}, { immediate: true });

watch(() => props.address, (addr) => {
  if (addr) {
    form.address_line_1 = addr.address_line_1 || '';
    form.city = addr.city || '';
    form.state = addr.state || '';
    form.zip_code = addr.zip_code || '';
  }
}, { deep: true });

async function submit() {
  errors.value = {};
  saving.value = true;
  try {
    if (editing.value) {
      const { data } = await api.put(`/api/v1/addresses/${props.address.id}`, form);
      emit('saved', data.data);
    } else {
      const { data } = await api.post('/api/v1/addresses', form);
      emit('saved', data.data);
    }
    emit('close');
  } catch (err) {
    if (err.response?.status === 422) {
      errors.value = err.response.data.errors || {};
    }
  } finally {
    saving.value = false;
  }
}
</script>

<style scoped>
.modal-enter-active,
.modal-leave-active { transition: opacity 0.2s ease; }
.modal-enter-from,
.modal-leave-to { opacity: 0; }
</style>
