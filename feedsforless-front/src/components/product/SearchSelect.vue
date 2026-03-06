<template>
  <div class="relative" ref="wrapRef">
    <input
      ref="inputRef"
      type="text"
      :value="searchText"
      class="w-full px-3 py-2 border border-slate-200 rounded-lg focus:ring-2 focus:ring-emerald-500/50 focus:border-emerald-500"
      :placeholder="placeholder"
      @input="searchText = $event.target.value; open = true"
      @focus="open = true"
      @blur="onBlur"
    />
    <div
      v-show="open && filteredOptions.length > 0"
      class="absolute z-10 w-full mt-1 bg-white border border-slate-200 rounded-lg shadow-lg max-h-48 overflow-y-auto"
    >
      <button
        v-for="opt in filteredOptions"
        :key="opt[valueKey]"
        type="button"
        class="w-full text-left px-3 py-2 text-sm hover:bg-slate-50 first:rounded-t-lg last:rounded-b-lg"
        @mousedown.prevent="select(opt)"
      >
        {{ opt[labelKey] ?? opt[valueKey] }}
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue';

const props = defineProps({
  modelValue: { type: [Number, String], default: null },
  options: { type: Array, default: () => [] },
  labelKey: { type: String, default: 'label' },
  valueKey: { type: String, default: 'id' },
  placeholder: { type: String, default: 'Search…' },
});

const emit = defineEmits(['update:modelValue']);
const inputRef = ref(null);
const wrapRef = ref(null);
const open = ref(false);
const searchText = ref('');

const selectedLabel = computed(() => {
  if (props.modelValue == null) return '';
  const o = props.options.find(opt => opt[props.valueKey] == props.modelValue);
  return o ? (o[props.labelKey] ?? o[props.valueKey]) : '';
});

const filteredOptions = computed(() => {
  const list = props.options || [];
  const q = (searchText.value || '').toLowerCase();
  if (!q) return list.slice(0, 20);
  return list.filter(o => String(o[props.labelKey] ?? o[props.valueKey] ?? '').toLowerCase().includes(q)).slice(0, 20);
});

function select(opt) {
  emit('update:modelValue', opt[props.valueKey]);
  searchText.value = opt[props.labelKey] ?? opt[props.valueKey];
  open.value = false;
}

function onBlur() {
  setTimeout(() => { open.value = false; }, 150);
}

watch(selectedLabel, (v) => { searchText.value = v; }, { immediate: true });
watch(() => props.modelValue, () => { searchText.value = selectedLabel.value; });
</script>
