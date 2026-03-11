<template>
  <main class="min-h-screen flex items-center justify-center p-4 bg-slate-100">
    <div class="w-full max-w-lg">
      <div class="flex items-center justify-center gap-3 mb-6">
        <div class="w-10 h-10 bg-[#2962ff] rounded-xl flex items-center justify-center text-white">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
        </div>
        <span class="font-black italic text-xl text-[#003366]">FEEDSFOR<span class="text-[#2962ff]">LESS</span></span>
      </div>
      <div class="bg-white p-8 rounded-2xl border border-slate-200/80 shadow-card">
        <div class="text-center mb-6">
          <h1 class="text-3xl font-black italic uppercase text-[#003366] tracking-tight">Create account</h1>
          <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mt-2 border-b-2 border-slate-900/10 pb-4 inline-block">Register for B2B access</p>
        </div>

        <form class="space-y-4" @submit.prevent="handleSubmit">
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label for="first_name" class="block text-sm font-medium text-slate-700 mb-1.5">Nombre</label>
              <input id="first_name" v-model="form.first_name" type="text" required class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-slate-900 placeholder-slate-400 focus:ring-2 focus:ring-[#2962ff]/30 focus:border-[#2962ff] transition-all" placeholder="Juan" />
            </div>
            <div>
              <label for="last_name" class="block text-sm font-medium text-slate-700 mb-1.5">Apellido</label>
              <input id="last_name" v-model="form.last_name" type="text" required class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-slate-900 placeholder-slate-400 focus:ring-2 focus:ring-[#2962ff]/30 focus:border-[#2962ff] transition-all" placeholder="Pérez" />
            </div>
          </div>
          <div>
            <label for="company_name" class="block text-sm font-medium text-slate-700 mb-1.5">Company</label>
            <input id="company_name" v-model="form.company_name" type="text" required class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-slate-900 placeholder-slate-400 focus:ring-2 focus:ring-[#2962ff]/30 focus:border-[#2962ff] transition-all" placeholder="Acme Inc." />
          </div>
          <div>
            <label for="email" class="block text-sm font-medium text-slate-700 mb-1.5">Email</label>
            <input id="email" v-model="form.email" type="email" required class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-slate-900 placeholder-slate-400 focus:ring-2 focus:ring-[#2962ff]/30 focus:border-[#2962ff] transition-all" placeholder="tu@email.com" />
          </div>
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label for="password" class="block text-sm font-medium text-slate-700 mb-1.5">Password</label>
              <input id="password" v-model="form.password" type="password" required minlength="8" class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-slate-900 placeholder-slate-400 focus:ring-2 focus:ring-[#2962ff]/30 focus:border-[#2962ff] transition-all" placeholder="••••••••" />
            </div>
            <div>
              <label for="password_confirmation" class="block text-sm font-medium text-slate-700 mb-1.5">Confirm password</label>
              <input id="password_confirmation" v-model="form.password_confirmation" type="password" required class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-slate-900 placeholder-slate-400 focus:ring-2 focus:ring-[#2962ff]/30 focus:border-[#2962ff] transition-all" placeholder="••••••••" />
            </div>
          </div>
          <div v-if="errorMessage" class="text-red-600 text-sm text-center py-3 px-4 bg-red-50 rounded-xl border border-red-100">
            {{ errorMessage }}
          </div>
          <button type="submit" :disabled="isLoading" class="w-full py-3 bg-[#2962ff] text-white font-bold text-[11px] uppercase tracking-wider rounded-xl hover:bg-blue-800 transition-colors shadow-sm shadow-blue-500/20 disabled:opacity-70 disabled:cursor-not-allowed flex items-center justify-center gap-2">
            <svg v-if="isLoading" class="animate-spin w-5 h-5" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
            <span>{{ isLoading ? 'Creating account…' : 'Register' }}</span>
          </button>
        </form>

        <p class="text-center text-sm text-slate-500 mt-6 font-medium">
          Already have an account? <router-link to="/login" class="text-[#2962ff] font-bold hover:underline ml-1">Sign in</router-link>
        </p>
      </div>
    </div>
  </main>
</template>

<script setup>
import { reactive, ref } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '../stores/auth';

const router = useRouter();
const authStore = useAuthStore();

const isLoading = ref(false);
const errorMessage = ref('');

const form = reactive({
    first_name: '',
    last_name: '',
    company_name: '',
    email: '',
    password: '',
    password_confirmation: ''
});

const handleSubmit = async () => {
    if (form.password !== form.password_confirmation) {
        errorMessage.value = 'Passwords do not match';
        return;
    }

    isLoading.value = true;
    errorMessage.value = '';

    try {
        await authStore.register(form);
        router.push({ name: 'Dashboard' });
    } catch (error) {
        errorMessage.value = error.response?.data?.message || 'Registration failed.';
    } finally {
        isLoading.value = false;
    }
};
</script>