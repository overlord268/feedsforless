<template>
  <div>
    <h1>Products</h1>
    
    <form @submit.prevent="createProduct">
      <h2>Create Product</h2>
      
      <div>
        <label for="name">Name</label>
        <input type="text" id="name" v-model="form.name">
        <span v-if="errors.name">{{ errors.name[0] }}</span>
      </div>

      <div>
        <label for="sku">SKU</label>
        <input type="text" id="sku" v-model="form.sku">
        <span v-if="errors.sku">{{ errors.sku[0] }}</span>
      </div>

      <div>
        <label for="description">Description</label>
        <textarea id="description" v-model="form.description"></textarea>
        <span v-if="errors.description">{{ errors.description[0] }}</span>
      </div>

      <div>
        <label for="price">Price</label>
        <input type="number" step="0.01" id="price" v-model="form.price">
        <span v-if="errors.price">{{ errors.price[0] }}</span>
      </div>

      <div>
        <label for="stock_quantity">Stock</label>
        <input type="number" id="stock_quantity" v-model="form.stock_quantity">
        <span v-if="errors.stock_quantity">{{ errors.stock_quantity[0] }}</span>
      </div>

      <div>
        <label for="category_id">Category</label>
        <select id="category_id" v-model="form.category_id">
          <option v-for="category in categories" :key="category.id" :value="category.id">
            {{ category.name }}
          </option>
        </select>
        <span v-if="errors.category_id">{{ errors.category_id[0] }}</span>
      </div>

      <div>
        <label for="status">Status</label>
        <select id="status" v-model="form.status">
          <option value="draft">Draft</option>
          <option value="published">Published</option>
        </select>
        <span v-if="errors.status">{{ errors.status[0] }}</span>
      </div>

      <button type="submit">Save Product</button>
    </form>

    <hr>

    <h2>Product List</h2>
    <table>
      <thead>
        <tr>
          <th>ID</th>
          <th>SKU</th>
          <th>Name</th>
          <th>Price</th>
          <th>Stock</th>
          <th>Status</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="product in products" :key="product.id">
          <td>{{ product.id }}</td>
          <td>{{ product.sku }}</td>
          <td>{{ product.name }}</td>
          <td>{{ product.price }}</td>
          <td>{{ product.stock_quantity }}</td>
          <td>{{ product.status }}</td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue';
import api from '../../services/api';

const products = ref([]);
const categories = ref([]);
const errors = ref({});

const form = reactive({
  name: '',
  sku: '',
  description: '',
  price: 0,
  stock_quantity: 0,
  category_id: null,
  status: 'draft'
});

const fetchProducts = async () => {
  try {
    const response = await api.get('/api/v1/admin/products');
    products.value = response.data.data || response.data;
  } catch (error) {
    console.error(error);
  }
};

const fetchCategories = async () => {
  try {
    const response = await api.get('/api/v1/admin/categories');
    categories.value = response.data.data || response.data;
  } catch (error) {
    console.error(error);
  }
};

const createProduct = async () => {
  errors.value = {};
  try {
    await api.post('/api/v1/admin/products', form);
    fetchProducts();
    Object.assign(form, {
      name: '',
      sku: '',
      description: '',
      price: 0,
      stock_quantity: 0,
      category_id: null,
      status: 'draft'
    });
  } catch (error) {
    if (error.response?.status === 422) {
      errors.value = error.response.data.errors;
    }
  }
};

onMounted(() => {
  fetchProducts();
  fetchCategories();
});
</script>