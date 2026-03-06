<template>
  <div class="space-y-1">
    <template v-for="node in tree" :key="node.id">
      <div class="flex items-start gap-2 py-1" :style="{ paddingLeft: (depth || 0) * 16 + 'px' }">
        <button
          v-if="node.children && node.children.length"
          type="button"
          class="shrink-0 p-0.5 text-slate-500 hover:text-slate-700"
          @click="toggle(node.id)"
        >
          <svg class="w-4 h-4 transition-transform" :class="{ 'rotate-90': expanded.has(node.id) }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
          </svg>
        </button>
        <span v-else class="w-4 shrink-0" />
        <label class="flex items-center gap-2 cursor-pointer flex-1 min-w-0">
          <input
            type="checkbox"
            :checked="modelValue.includes(node.id)"
            class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500"
            @change="toggleCategory(node.id)"
          />
          <span class="text-sm text-slate-700 truncate">{{ node.label || node.name }}</span>
        </label>
      </div>
      <CategoryTreeSelect
        v-if="node.children && node.children.length && expanded.has(node.id)"
        :model-value="modelValue"
        :categories="node.children"
        :depth="(depth || 0) + 1"
        :expanded-set="expanded"
        @update:model-value="$emit('update:modelValue', $event)"
        @toggle-expand="toggleExpand"
      />
    </template>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue';

const props = defineProps({
  modelValue: { type: Array, default: () => [] },
  categories: { type: Array, default: () => [] },
  depth: { type: Number, default: 0 },
  expandedSet: { type: Set, default: undefined },
});

const emit = defineEmits(['update:modelValue', 'toggle-expand']);

const localExpanded = ref(new Set());
if (props.categories?.length && !props.expandedSet) {
  props.categories.forEach(c => localExpanded.value.add(c.id));
}
const expanded = props.expandedSet || localExpanded;

const tree = props.categories;

function toggle(id) {
  const s = new Set(expanded.value);
  if (s.has(id)) s.delete(id);
  else s.add(id);
  expanded.value = s;
  emit('toggle-expand', { id });
}

function toggleExpand() {
  emit('toggle-expand');
}

function toggleCategory(id) {
  const next = props.modelValue.includes(id)
    ? props.modelValue.filter(x => x !== id)
    : [...props.modelValue, id];
  emit('update:modelValue', next);
}

watch(() => props.expandedSet, (s) => { if (s) expanded.value = s; }, { immediate: true });
</script>
