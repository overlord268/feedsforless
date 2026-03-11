<template>
  <main class="min-h-screen flex items-center justify-center p-4 bg-slate-100">
    <div class="w-full max-w-md">
      <div class="flex items-center justify-center gap-3 mb-8">
        <div class="w-10 h-10 bg-[#2962ff] rounded-xl flex items-center justify-center text-white">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
        </div>
        <span class="font-black italic text-xl text-[#003366]">FEEDSFOR<span class="text-[#2962ff]">LESS</span></span>
      </div>
      <div class="bg-white p-8 rounded-2xl border border-slate-200/80 shadow-card">
        <div class="text-center mb-8">
          <h1 class="text-3xl font-black italic uppercase text-[#003366] tracking-tight">Sign in</h1>
          <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mt-2 border-b-2 border-slate-900/10 pb-4 inline-block">Access your B2B portal</p>
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
              class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-slate-900 placeholder-slate-400 focus:ring-2 focus:ring-[#2962ff]/30 focus:border-[#2962ff] transition-all"
              placeholder="you@example.com"
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
              class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-slate-900 placeholder-slate-400 focus:ring-2 focus:ring-[#2962ff]/30 focus:border-[#2962ff] transition-all"
              placeholder="••••••••"
            />
          </div>
          <div v-if="errorMessage" class="text-red-600 text-sm text-center py-3 px-4 bg-red-50 rounded-xl border border-red-100">
            {{ errorMessage }}
          </div>
          <button
            type="submit"
            :disabled="isLoading"
            class="w-full min-h-[48px] py-3 bg-[#2962ff] text-white font-bold text-[11px] uppercase tracking-wider rounded-xl hover:bg-blue-800 transition-colors shadow-sm shadow-blue-500/20 disabled:opacity-70 disabled:cursor-not-allowed flex items-center justify-center gap-2 touch-manipulation"
          >
            <svg v-if="isLoading" class="animate-spin w-5 h-5" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
            <span>{{ isLoading ? 'Signing in…' : 'Sign in' }}</span>
          </button>
        </form>

        <p class="text-center text-sm text-slate-500 mt-6 font-medium">
          Don't have an account? <router-link to="/register" class="text-[#2962ff] font-bold hover:underline ml-1">Register</router-link>
        </p>
      </div>
    </div>
  </main>
</template>

<script setup>
import { reactive, ref } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import { useAuthStore } from '../stores/auth';
import { useToast } from '../composables/useToast';

const router = useRouter();
const route = useRoute();
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
        const redirect = route.query.redirect;
        router.push(typeof redirect === 'string' && redirect.startsWith('/') ? redirect : { name: 'Dashboard', query: { welcome: '1' } });
    } catch (error) {
        errorMessage.value = error.response?.data?.message || 'Authentication failed. Please try again.';
    } finally {
        isLoading.value = false;
    }
};
</script>