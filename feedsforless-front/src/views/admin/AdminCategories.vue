<template>
  <div>
    <h1>Categories</h1>

    <form @submit.prevent="createCategory">
      <h2>Create Category</h2>
      
      <div>
        <label for="name">Name</label>
        <input type="text" id="name" v-model="form.name">
        <span v-if="errors.name">{{ errors.name[0] }}</span>
      </div>

      <div>
        <label for="slug">Slug</label>
        <input type="text" id="slug" v-model="form.slug">
        <span v-if="errors.slug">{{ errors.slug[0] }}</span>
      </div>

      <button type="submit">Save Category</button>
    </form>

    <hr>

    <h2>Category List</h2>
    <table>
      <thead>
        <tr>
          <th>ID</th>
          <th>Name</th>
          <th>Slug</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="category in categories" :key="category.id">
          <td>{{ category.id }}</td>
          <td>{{ category.name }}</td>
          <td>{{ category.slug }}</td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue';
import api from '../../services/api';

const categories = ref([]);
const errors = ref({});

const form = reactive({
  name: '',
  slug: ''
});

const fetchCategories = async () => {
  try {
    const response = await api.get('/api/v1/admin/categories');
    categories.value = response.data.data || response.data;
  } catch (error) {
    console.error(error);
  }
};

const createCategory = async () => {
  errors.value = {};
  try {
    await api.post('/api/v1/admin/categories', form);
    fetchCategories();
    form.name = '';
    form.slug = '';
  } catch (error) {
    if (error.response?.status === 422) {
      errors.value = error.response.data.errors;
    }
  }
};

onMounted(() => {
  fetchCategories();
});
</script>