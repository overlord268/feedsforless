<template>
  <div
    class="border-2 border-dashed rounded-xl p-6 text-center transition-colors"
    :class="isDrag ? 'border-emerald-500 bg-emerald-50' : 'border-slate-200 hover:border-slate-300 bg-slate-50/50'"
    @dragover.prevent="isDrag = true"
    @dragleave.prevent="isDrag = false"
    @drop.prevent="onDrop"
  >
    <input
      ref="inputRef"
      type="file"
      class="hidden"
      @change="onChange"
    />
    <div class="flex flex-col items-center gap-2">
      <div class="w-12 h-12 rounded-xl bg-slate-200 flex items-center justify-center text-slate-500">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" /></svg>
      </div>
      <p class="text-sm font-medium text-slate-600">{{ label }}</p>
      <p v-if="file" class="text-xs text-slate-500 truncate max-w-full">{{ file.name }}</p>
      <button
        type="button"
        class="text-sm text-emerald-600 hover:underline"
        @click="inputRef?.click()"
      >
        {{ file ? 'Cambiar archivo' : 'Arrastrar o clic' }}
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';

defineProps({ label: { type: String, default: 'Documento' }, file: { type: File, default: null } });
const emit = defineEmits(['update:file']);
const inputRef = ref(null);
const isDrag = ref(false);

function onDrop(e) {
  isDrag.value = false;
  const f = e.dataTransfer?.files?.[0];
  if (f) emit('update:file', f);
}

function onChange(e) {
  const f = e.target?.files?.[0];
  if (f) emit('update:file', f);
}
</script>
