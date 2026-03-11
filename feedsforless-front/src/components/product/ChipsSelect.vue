<template>
  <div class="border border-slate-200 rounded-xl p-3 min-h-[44px] focus-within:ring-2 focus-within:ring-emerald-500/50 focus-within:border-emerald-500">
    <div class="flex flex-wrap gap-2 mb-2">
      <span
        v-for="id in modelValue"
        :key="id"
        class="inline-flex items-center gap-1 px-3 py-1 bg-emerald-100 text-emerald-800 text-sm font-medium rounded-md"
      >
        {{ labelForId(id) }}
        <button type="button" class="p-0.5 hover:bg-emerald-200 rounded-md" @click="remove(id)">×</button>
      </span>
    </div>
    <div class="relative" ref="wrapRef">
      <input
        ref="inputRef"
        v-model="searchText"
        type="text"
        class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/50"
        :placeholder="placeholder"
        @focus="open = true"
        @blur="onBlur"
      />
      <div
        v-show="open && filteredOptions.length > 0"
        class="absolute z-10 left-0 right-0 mt-1 bg-white border border-slate-200 rounded-lg shadow-lg max-h-48 overflow-y-auto"
      >
        <button
          v-for="opt in filteredOptions"
          :key="opt[valueKey]"
          type="button"
          class="w-full text-left px-3 py-2 text-sm hover:bg-slate-50"
          :disabled="modelValue.includes(opt[valueKey])"
          @mousedown.prevent="add(opt[valueKey])"
        >
          {{ opt[labelKey] ?? opt[valueKey] }}
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';

const props = defineProps({
  modelValue: { type: Array, default: () => [] },
  options: { type: Array, default: () => [] },
  labelKey: { type: String, default: 'label' },
  valueKey: { type: String, default: 'id' },
  placeholder: { type: String, default: 'Seleccionar…' },
});

const emit = defineEmits(['update:modelValue']);
const inputRef = ref(null);
const wrapRef = ref(null);
const open = ref(false);
const searchText = ref('');

function labelForId(id) {
  const o = (props.options || []).find(opt => opt[props.valueKey] == id);
  return o ? (o[props.labelKey] ?? o[props.valueKey]) : id;
}

const filteredOptions = computed(() => {
  const list = props.options || [];
  const q = (searchText.value || '').toLowerCase();
  if (!q) return list.slice(0, 15);
  return list.filter(o => String(o[props.labelKey] ?? o[props.valueKey] ?? '').toLowerCase().includes(q)).slice(0, 15);
});

function add(id) {
  if (props.modelValue.includes(id)) return;
  emit('update:modelValue', [...props.modelValue, id]);
  searchText.value = '';
  open.value = false;
}

function remove(id) {
  emit('update:modelValue', props.modelValue.filter(x => x !== id));
}

function onBlur() {
  setTimeout(() => { open.value = false; }, 150);
}
</script>
