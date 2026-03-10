<template>
  <div class="space-y-6">
    <div>
      <h1 class="text-2xl font-bold text-slate-900 dark:text-white tracking-tight">My Account</h1>
      <p class="text-slate-500 dark:text-slate-400 mt-0.5">Update your profile and contact information.</p>
    </div>

    <form class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-card overflow-hidden max-w-xl" @submit.prevent="save">
      <div class="p-6 space-y-4">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">First name</label>
            <input v-model="form.first_name" type="text" class="w-full px-4 py-2.5 border border-slate-200 dark:border-slate-600 rounded-xl bg-white dark:bg-slate-700 text-slate-900 dark:text-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500" />
          </div>
          <div>
            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Last name</label>
            <input v-model="form.last_name" type="text" class="w-full px-4 py-2.5 border border-slate-200 dark:border-slate-600 rounded-xl bg-white dark:bg-slate-700 text-slate-900 dark:text-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500" />
          </div>
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Email</label>
          <input v-model="form.email" type="email" required class="w-full px-4 py-2.5 border border-slate-200 dark:border-slate-600 rounded-xl bg-white dark:bg-slate-700 text-slate-900 dark:text-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500" />
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Phone</label>
          <input v-model="form.phone" type="tel" class="w-full px-4 py-2.5 border border-slate-200 dark:border-slate-600 rounded-xl bg-white dark:bg-slate-700 text-slate-900 dark:text-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500" />
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Job title</label>
          <input v-model="form.job_title" type="text" class="w-full px-4 py-2.5 border border-slate-200 dark:border-slate-600 rounded-xl bg-white dark:bg-slate-700 text-slate-900 dark:text-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500" />
        </div>
      </div>
      <div class="px-6 py-4 border-t border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-700/30 flex justify-end">
        <button type="submit" :disabled="saving" class="px-5 py-2.5 rounded-xl bg-emerald-600 text-white font-medium hover:bg-emerald-700 disabled:opacity-70">
          {{ saving ? 'Saving…' : 'Save changes' }}
        </button>
      </div>
    </form>
  </div>
</template>

<script setup>
import { reactive, ref, onMounted } from 'vue';
import { useAuthStore } from '../stores/auth';
import { useToast } from '../composables/useToast';

const authStore = useAuthStore();
const toast = useToast();
const saving = ref(false);

const form = reactive({
  first_name: '',
  last_name: '',
  email: '',
  phone: '',
  job_title: ''
});

function fillForm() {
  const u = authStore.user;
  if (!u) return;
  form.first_name = u.first_name ?? '';
  form.last_name = u.last_name ?? '';
  form.email = u.email ?? '';
  form.phone = u.phone_number ?? u.phone ?? '';
  form.job_title = u.job_title ?? '';
}

async function save() {
  saving.value = true;
  try {
    await authStore.updateProfile({
      first_name: form.first_name,
      last_name: form.last_name,
      email: form.email,
      phone: form.phone,
      job_title: form.job_title
    });
    toast.success('Profile updated successfully.');
  } catch (err) {
    const msg = err.response?.data?.message || err.response?.data?.errors?.email?.[0] || 'Failed to update profile.';
    toast.error(msg);
  } finally {
    saving.value = false;
  }
}

onMounted(() => {
  fillForm();
});
</script>
