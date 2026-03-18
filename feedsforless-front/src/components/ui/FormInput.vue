<template>
  <div>
    <label
      v-if="label"
      :for="inputId"
      class="block text-sm font-medium text-slate-700 mb-1.5"
    >
      {{ label }}
    </label>
    <input
      :id="inputId"
      :type="type"
      :value="modelValue"
      :placeholder="placeholder"
      :required="required"
      :autocomplete="autocomplete"
      :aria-invalid="!!error"
      :aria-describedby="error ? `${inputId}-error` : undefined"
      class="w-full px-4 py-2.5 border rounded-xl text-slate-900 placeholder-slate-400 transition-all focus:outline-none focus:ring-2"
      :class="inputClasses"
      @input="emit('update:modelValue', $event.target.value)"
    />
    <p
      v-if="error"
      :id="`${inputId}-error`"
      class="mt-1.5 text-sm text-red-600"
      role="alert"
    >
      {{ error }}
    </p>
  </div>
</template>

<script setup>
import { computed, useId } from 'vue';

const props = defineProps({
  /** Bound value (v-model). */
  modelValue: {
    type: [String, Number],
    default: '',
  },
  /** Label text shown above the input. */
  label: {
    type: String,
    default: '',
  },
  /** Input type: text, email, password, etc. */
  type: {
    type: String,
    default: 'text',
  },
  /** Placeholder text. */
  placeholder: {
    type: String,
    default: '',
  },
  /** Whether the input is required. */
  required: {
    type: Boolean,
    default: false,
  },
  /** Autocomplete attribute (e.g. "email", "current-password"). */
  autocomplete: {
    type: String,
    default: undefined,
  },
  /** Validation error message; when set, input gets error styling. */
  error: {
    type: String,
    default: '',
  },
  /** Optional id for the input (for label association). Auto-generated if not provided. */
  id: {
    type: String,
    default: '',
  },
  /** Visual variant: "default" (blue focus for login/public), "admin" (emerald focus for admin panel). */
  variant: {
    type: String,
    default: 'default',
    validator: (v) => ['default', 'admin'].includes(v),
  },
});

const emit = defineEmits(['update:modelValue']);

const uid = useId();
const inputId = computed(() => props.id || `form-input-${uid}`);

const inputClasses = computed(() => {
  if (props.error) {
    return 'border-red-300 focus:ring-red-500/30 focus:border-red-500';
  }
  if (props.variant === 'admin') {
    return 'border-slate-200 focus:ring-emerald-500/30 focus:border-emerald-500';
  }
  return 'border-slate-200 focus:ring-[#2962ff]/30 focus:border-[#2962ff]';
});
</script>
