<template>
  <div>
    <h1>Users</h1>

    <table>
      <thead>
        <tr>
          <th>ID</th>
          <th>Name</th>
          <th>Email</th>
          <th>Assign Role</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="user in users" :key="user.id">
          <td>{{ user.id }}</td>
          <td>{{ user.first_name }} {{ user.last_name }}</td>
          <td>{{ user.email }}</td>
          <td>
            <form @submit.prevent="assignRole(user.id)">
              <select v-model="roleForm[user.id]">
                <option value="customer">Customer</option>
                <option value="admin">Admin</option>
              </select>
              <button type="submit">Assign</button>
              <span v-if="errors[user.id]">{{ errors[user.id] }}</span>
            </form>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue';
import api from '../../services/api';

const users = ref([]);
const roleForm = reactive({});
const errors = reactive({});

const fetchUsers = async () => {
  try {
    const response = await api.get('/api/v1/admin/users');
    users.value = response.data.data || response.data;
    users.value.forEach(user => {
      if (!roleForm[user.id]) {
        roleForm[user.id] = user.roles?.[0]?.name || 'customer';
      }
    });
  } catch (error) {
    console.error(error);
  }
};

const assignRole = async (id) => {
  errors[id] = null;
  try {
    await api.post(`/api/v1/admin/users/${id}/roles`, {
      role: roleForm[id]
    });
    fetchUsers();
  } catch (error) {
    if (error.response?.status === 422) {
      errors[id] = 'Validation error';
    }
  }
};

onMounted(() => {
  fetchUsers();
});
</script>