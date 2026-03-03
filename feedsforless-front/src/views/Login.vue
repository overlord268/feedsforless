<template>
    <main class="grid place-items-center min-h-screen p-4 bg-background">
        <div class="bg-surface w-full max-w-md p-8 md:p-10 rounded-lg shadow-md flex flex-col gap-8">
            <div class="text-center grid gap-2">
                <h1 class="text-2xl font-bold text-slate-900">Welcome Back</h1>
                <p class="text-sm text-slate-500">Sign in to your B2B portal</p>
            </div>

            <form class="grid gap-6" @submit.prevent="handleSubmit">
                <div class="grid gap-1.5">
                    <label for="email" class="text-sm font-medium text-slate-900">Email Address</label>
                    <input
                        id="email"
                        v-model="form.email"
                        type="email"
                        required
                        autocomplete="email"
                        class="px-3 py-2 border border-slate-200 rounded-md text-sm outline-none transition-all focus:border-secondary focus:ring-4 focus:ring-secondary/10"
                    />
                </div>

                <div class="grid gap-1.5">
                    <label for="password" class="text-sm font-medium text-slate-900">Password</label>
                    <input
                        id="password"
                        v-model="form.password"
                        type="password"
                        required
                        autocomplete="current-password"
                        class="px-3 py-2 border border-slate-200 rounded-md text-sm outline-none transition-all focus:border-secondary focus:ring-4 focus:ring-secondary/10"
                    />
                </div>

                <div v-if="errorMessage" class="text-red-500 text-sm text-center p-2 bg-red-50 rounded-md">
                    {{ errorMessage }}
                </div>

                <button type="submit" :disabled="isLoading" class="bg-primary text-surface px-4 py-2 rounded-md font-medium text-sm transition-colors hover:bg-primary-hover disabled:opacity-70 disabled:cursor-not-allowed grid place-items-center">
                    <span v-if="isLoading">Signing in...</span>
                    <span v-else>Sign In</span>
                </button>
            </form>

            <div class="text-center text-sm text-slate-500">
                <p>Don't have an account? <router-link to="/register" class="text-secondary font-medium hover:underline">Register here</router-link></p>
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
    email: '',
    password: ''
});

const handleSubmit = async () => {
    isLoading.value = true;
    errorMessage.value = '';

    try {
        await authStore.login(form);
        router.push({ name: 'Dashboard' });
    } catch (error) {
        errorMessage.value = error.response?.data?.message || 'Authentication failed. Please try again.';
    } finally {
        isLoading.value = false;
    }
};
</script>