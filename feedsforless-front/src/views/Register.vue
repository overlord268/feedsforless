<template>
    <main class="grid place-items-center min-h-screen p-4 bg-background">
        <div class="bg-surface w-full max-w-2xl p-8 md:p-10 rounded-lg shadow-md flex flex-col gap-8">
            <div class="text-center grid gap-2">
                <h1 class="text-2xl font-bold text-slate-900">Create Account</h1>
                <p class="text-sm text-slate-500">Register for your B2B access</p>
            </div>

            <form class="grid gap-5" @submit.prevent="handleSubmit">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="grid gap-1.5">
                        <label for="first_name" class="text-sm font-medium text-slate-900">First Name</label>
                        <input id="first_name" v-model="form.first_name" type="text" required class="px-3 py-2 border border-slate-200 rounded-md text-sm outline-none transition-all focus:border-secondary focus:ring-4 focus:ring-secondary/10" />
                    </div>
                    <div class="grid gap-1.5">
                        <label for="last_name" class="text-sm font-medium text-slate-900">Last Name</label>
                        <input id="last_name" v-model="form.last_name" type="text" required class="px-3 py-2 border border-slate-200 rounded-md text-sm outline-none transition-all focus:border-secondary focus:ring-4 focus:ring-secondary/10" />
                    </div>
                </div>

                <div class="grid gap-1.5">
                    <label for="company_name" class="text-sm font-medium text-slate-900">Company Name</label>
                    <input id="company_name" v-model="form.company_name" type="text" required class="px-3 py-2 border border-slate-200 rounded-md text-sm outline-none transition-all focus:border-secondary focus:ring-4 focus:ring-secondary/10" />
                </div>

                <div class="grid gap-1.5">
                    <label for="email" class="text-sm font-medium text-slate-900">Email Address</label>
                    <input id="email" v-model="form.email" type="email" required class="px-3 py-2 border border-slate-200 rounded-md text-sm outline-none transition-all focus:border-secondary focus:ring-4 focus:ring-secondary/10" />
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="grid gap-1.5">
                        <label for="password" class="text-sm font-medium text-slate-900">Password</label>
                        <input id="password" v-model="form.password" type="password" required minlength="8" class="px-3 py-2 border border-slate-200 rounded-md text-sm outline-none transition-all focus:border-secondary focus:ring-4 focus:ring-secondary/10" />
                    </div>
                    <div class="grid gap-1.5">
                        <label for="password_confirmation" class="text-sm font-medium text-slate-900">Confirm Password</label>
                        <input id="password_confirmation" v-model="form.password_confirmation" type="password" required class="px-3 py-2 border border-slate-200 rounded-md text-sm outline-none transition-all focus:border-secondary focus:ring-4 focus:ring-secondary/10" />
                    </div>
                </div>

                <div v-if="errorMessage" class="text-red-500 text-sm text-center p-2 bg-red-50 rounded-md">
                    {{ errorMessage }}
                </div>

                <button type="submit" :disabled="isLoading" class="bg-primary text-surface px-4 py-2.5 rounded-md font-medium text-sm mt-2 transition-colors hover:bg-primary-hover disabled:opacity-70 disabled:cursor-not-allowed">
                    <span v-if="isLoading">Creating account...</span>
                    <span v-else>Register</span>
                </button>
            </form>

            <div class="text-center text-sm text-slate-500">
                <p>Already have an account? <router-link to="/login" class="text-secondary font-medium hover:underline">Sign in here</router-link></p>
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