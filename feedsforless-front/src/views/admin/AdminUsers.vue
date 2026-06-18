<template>
  <div class="space-y-5">
    <div>
      <h1 class="text-2xl font-bold text-slate-900 tracking-tight">System (Users)</h1>
      <p class="text-slate-500 mt-0.5">User and role management (admin / customer).</p>
    </div>

    <CrudTable
      :columns="columns"
      :items="users"
      :loading="loading"
      title="Users"
      search-placeholder="Search users…"
      item-label="users"
      :default-sort="{ key: 'id', dir: 'desc' }"
    >
      <template #row="{ item: user }">
        <td class="px-4 py-2.5 text-slate-700">{{ user.id }}</td>
        <td class="px-4 py-2.5 text-slate-800">{{ userName(user) }}</td>
        <td class="px-4 py-2.5 text-slate-600">{{ user.email }}</td>
        <td class="px-4 py-2.5">
          <span class="inline-flex px-2.5 py-0.5 rounded-md text-xs font-semibold" :class="(user.roles?.[0]?.name === 'admin') ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-600'">
            {{ user.roles?.[0]?.name || 'customer' }}
          </span>
        </td>
        <td class="px-4 py-2.5">
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
      </template>
    </CrudTable>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue';
import api from '../../services/api';
import CrudTable from '../../components/admin/CrudTable.vue';

const columns = [
  { key: 'id', label: 'ID' },
  { key: 'name', label: 'Name', sortValue: (u) => userName(u) },
  { key: 'email', label: 'Email' },
  { key: 'role', label: 'Role', sortValue: (u) => u.roles?.[0]?.name || 'customer' },
  { key: 'actions', label: 'Action', thClass: 'px-4 py-2.5 text-[11px] font-semibold text-slate-500 uppercase tracking-wider w-48' },
];

const users = ref([]);
const loading = ref(true);
const roleForm = reactive({});
const saving = reactive({});
const errors = reactive({});

function userName(user) {
  return `${user.first_name || ''} ${user.last_name || ''}`.trim() || '—';
}

async function fetchUsers() {
  loading.value = true;
  try {
    const { data } = await api.get('/api/v1/admin/users');
    const raw = data?.data ?? data;
    users.value = Array.isArray(raw) ? raw : (raw?.data ?? []);
    users.value.forEach((u) => {
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
