<template>
  <div :class="wrapperClass">
    <div v-if="variant === 'mobile'" class="flex items-center gap-3 mb-6">
      <div class="h-8 w-1 bg-[var(--funnel-primary)] shrink-0" />
      <div>
        <h2 class="font-headline text-lg font-bold text-slate-900 uppercase tracking-tight">Industrial Insights</h2>
        <p class="text-sm text-slate-600 mt-0.5 leading-relaxed">Join the inner circle of industrial procurement.</p>
      </div>
    </div>
    <div v-else class="mb-6 lg:mb-8">
      <h2 class="font-headline text-xl lg:text-2xl font-bold text-slate-900 mb-2 tracking-tight">Industrial Insights</h2>
      <p class="text-sm text-slate-600 leading-relaxed">Join the inner circle of industrial procurement.</p>
    </div>

    <form class="space-y-4 lg:space-y-6" @submit.prevent="onSubmit">
      <div class="space-y-1.5">
        <label class="funnel-label" for="funnel-name">Full Name *</label>
        <input
          id="funnel-name"
          v-model="form.name"
          type="text"
          required
          class="funnel-input"
          placeholder="John Doe"
          :disabled="submitting || success"
        />
      </div>
      <div class="space-y-1.5">
        <label class="funnel-label" for="funnel-email">Work Email *</label>
        <input
          id="funnel-email"
          v-model="form.email"
          type="email"
          required
          class="funnel-input"
          placeholder="user@company.com"
          :disabled="submitting || success"
        />
      </div>

      <p v-if="error" class="text-sm text-red-600 text-center">{{ error }}</p>

      <div class="pt-2 lg:pt-4">
        <button
          type="submit"
          class="funnel-btn-primary group"
          :class="success ? 'bg-[var(--funnel-tertiary)] hover:bg-[var(--funnel-tertiary)]' : ''"
          :disabled="submitting || success"
        >
          <span>{{ buttonLabel }}</span>
          <svg
            v-if="!submitting && !success"
            class="w-5 h-5 group-hover:translate-x-0.5 transition-transform"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
          >
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
          </svg>
        </button>
      </div>

      <p v-if="success" class="text-sm text-center text-emerald-700 font-medium leading-relaxed">
        You're subscribed. Check your inbox for confirmation and a commodity suggestion.
      </p>
      <p v-else class="text-[11px] text-slate-500 text-center mt-2 lg:mt-6">
        By subscribing, you agree to our
        <a href="#" class="text-blue-600 font-bold hover:underline" @click.prevent>Privacy Protocol</a>.
      </p>
      <p v-if="variant === 'mobile' && !success" class="text-[10px] text-center text-slate-500 uppercase mt-1">
        Trusted by 500+ agricultural procurement officers
      </p>
    </form>
  </div>
</template>

<script setup>
import { ref, computed, reactive } from 'vue';
import { useRouter } from 'vue-router';
import api from '../../services/api';

const router = useRouter();

const props = defineProps({
  variant: { type: String, default: 'desktop' },
});

const form = reactive({
  name: '',
  email: '',
});

const submitting = ref(false);
const success = ref(false);
const error = ref('');

const wrapperClass = computed(() =>
  props.variant === 'mobile'
    ? ''
    : 'funnel-glass rounded-lg shadow-2xl p-8 md:p-10 w-full max-w-md'
);

const buttonLabel = computed(() => {
  if (success.value) return 'Subscribed';
  if (submitting.value) return 'Subscribing…';
  return 'Subscribe';
});

async function onSubmit() {
  error.value = '';
  submitting.value = true;
  const name = form.name.trim();
  const email = form.email.trim().toLowerCase();
  try {
    await api.post('/api/v1/newsletter/market-insights', { name, email });
    await router.push({
      name: 'Catalog',
      query: { from: 'newsletter', email },
    });
  } catch (e) {
    error.value = e.response?.data?.message || 'Could not complete subscription. Please try again.';
    submitting.value = false;
  }
}
</script>
