<template>
  <main class="min-h-screen flex items-center justify-center p-4 bg-slate-100">
    <div class="w-full max-w-md">
      <div class="flex items-center justify-center gap-3 mb-8">
        <div class="w-10 h-10 bg-emerald-500 rounded-xl flex items-center justify-center text-white">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
        </div>
        <span class="font-bold text-xl text-slate-800">FeedsFor<span class="text-emerald-500">Less</span></span>
      </div>
      <div class="bg-white p-8 rounded-2xl border border-slate-200/80 shadow-card">
        <div class="text-center mb-8">
          <h1 class="text-2xl font-bold tracking-tight text-slate-900">Sign in</h1>
          <p class="text-slate-500 mt-1 text-sm">Access your B2B portal</p>
        </div>

        <form class="space-y-5" @submit.prevent="handleSubmit">
          <div>
            <label for="email" class="block text-sm font-medium text-slate-700 mb-1.5">Email</label>
            <input
              id="email"
              v-model="form.email"
              type="email"
              required
              autocomplete="email"
              class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-slate-900 placeholder-slate-400 focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 transition-all"
              placeholder="tu@email.com"
            />
          </div>
          <div>
            <label for="password" class="block text-sm font-medium text-slate-700 mb-1.5">Password</label>
            <input
              id="password"
              v-model="form.password"
              type="password"
              required
              autocomplete="current-password"
              class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-slate-900 placeholder-slate-400 focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 transition-all"
              placeholder="••••••••"
            />
          </div>
          <div v-if="errorMessage" class="text-red-600 text-sm text-center py-3 px-4 bg-red-50 rounded-xl border border-red-100">
            {{ errorMessage }}
          </div>
          <button
            type="submit"
            :disabled="isLoading"
            class="w-full min-h-[48px] py-3 bg-emerald-600 text-white font-medium rounded-xl hover:bg-emerald-700 transition-colors shadow-sm shadow-emerald-500/20 disabled:opacity-70 disabled:cursor-not-allowed flex items-center justify-center gap-2 touch-manipulation"
          >
            <svg v-if="isLoading" class="animate-spin w-5 h-5" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
            <span>{{ isLoading ? 'Signing in…' : 'Sign in' }}</span>
          </button>
        </form>

        <p class="text-center text-sm text-slate-500 mt-6">
          Don't have an account? <router-link to="/register" class="text-emerald-600 font-medium hover:underline">Register</router-link>
        </p>
      </div>
    </div>
  </main>
</template>

<script setup>
import { reactive, ref } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '../stores/auth';
import { useToast } from '../composables/useToast';

const router = useRouter();
const authStore = useAuthStore();
const toast = useToast();

const isLoading = ref(false);
const errorMessage = ref('');

const form = reactive({
    email: '',
    password: ''
});

const handleSubmit = async () => {
    isLoading.value = true;
    errorMessage.value = '';

    try {
        await authStore.login(form);
        toast.success('You have signed in successfully.');
        router.push({ name: 'Dashboard', query: { welcome: '1' } });
    } catch (error) {
        errorMessage.value = error.response?.data?.message || 'Authentication failed. Please try again.';
    } finally {
        isLoading.value = false;
    }
};
</script>