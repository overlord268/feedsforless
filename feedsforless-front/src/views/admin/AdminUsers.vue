<template>
  <div class="space-y-5">
    <div>
      <h1 class="text-2xl font-bold text-slate-900 tracking-tight">System (Users)</h1>
      <p class="text-slate-500 mt-0.5">User and role management (admin / customer).</p>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-card overflow-hidden">
      <div class="overflow-x-auto table-scroll">
        <table class="w-full text-sm min-w-[600px]">
          <thead class="bg-slate-50/80 border-b border-slate-200">
            <tr class="text-left">
              <th class="px-6 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider">ID</th>
              <th class="px-6 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider">Name</th>
              <th class="px-6 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider">Email</th>
              <th class="px-6 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider">Role</th>
              <th class="px-6 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider">Action</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            <tr v-for="user in users" :key="user.id" class="hover:bg-slate-50/70 transition-colors">
              <td class="px-6 py-4 text-slate-700">{{ user.id }}</td>
              <td class="px-6 py-4 text-slate-800">{{ (user.first_name || '') + ' ' + (user.last_name || '') || '—' }}</td>
              <td class="px-6 py-4 text-slate-600">{{ user.email }}</td>
              <td class="px-6 py-4">
                <span class="inline-flex px-2.5 py-0.5 rounded-md text-xs font-semibold" :class="(user.roles?.[0]?.name === 'admin') ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-600'">
                  {{ user.roles?.[0]?.name || 'customer' }}
                </span>
              </td>
              <td class="px-6 py-4">
                <form class="flex flex-wrap items-center gap-2" @submit.prevent="assignRole(user.id)">
                  <select v-model="roleForm[user.id]" class="rounded-lg border border-slate-300 px-2 py-1.5 text-sm text-slate-900">
                    <option value="customer">Customer</option>
                    <option value="admin">Admin</option>
                  </select>
                  <button type="submit" class="px-3 py-1.5 rounded-lg bg-emerald-600 text-white text-sm font-medium hover:bg-emerald-700" :disabled="saving[user.id]">
                    {{ saving[user.id] ? '…' : 'Assign' }}
                  </button>
                  <span v-if="errors[user.id]" class="text-red-500 text-xs">{{ errors[user.id] }}</span>
                </form>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
      <p v-if="!loading && users.length === 0" class="px-6 py-12 text-center text-slate-500">No users.</p>
      <div v-if="loading" class="px-6 py-12 text-center text-slate-500">Loading…</div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue';
import api from '../../services/api';

const users = ref([]);
const loading = ref(true);
const roleForm = reactive({});
const saving = reactive({});
const errors = reactive({});

async function fetchUsers() {
  loading.value = true;
  try {
    const { data } = await api.get('/api/v1/admin/users');
    const raw = data?.data ?? data;
    users.value = Array.isArray(raw) ? raw : (raw?.data ?? []);
    users.value.forEach(u => {
      if (roleForm[u.id] === undefined) {
        roleForm[u.id] = u.roles?.[0]?.name || 'customer';
      }
    });
  } catch (e) {
    console.error(e);
    users.value = [];
  } finally {
    loading.value = false;
  }
}

async function assignRole(userId) {
  errors[userId] = null;
  saving[userId] = true;
  try {
    await api.post(`/api/v1/admin/users/${userId}/roles`, { role: roleForm[userId] });
    await fetchUsers();
  } catch (e) {
    if (e.response?.status === 422) {
      errors[userId] = e.response?.data?.message || 'Validation error';
    } else {
      errors[userId] = 'Error assigning role';
    }
  } finally {
    saving[userId] = false;
  }
}

onMounted(fetchUsers);
</script>
