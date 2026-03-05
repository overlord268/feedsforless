<template>
  <div>
    <h1>My Addresses</h1>

    <form @submit.prevent="createAddress">
      <h2>Add New Address</h2>

      <div>
        <label for="address_line_1">Address Line 1</label>
        <input type="text" id="address_line_1" v-model="form.address_line_1">
        <span v-if="errors.address_line_1">{{ errors.address_line_1[0] }}</span>
      </div>

      <div>
        <label for="city">City</label>
        <input type="text" id="city" v-model="form.city">
        <span v-if="errors.city">{{ errors.city[0] }}</span>
      </div>

      <div>
        <label for="state">State</label>
        <input type="text" id="state" v-model="form.state">
        <span v-if="errors.state">{{ errors.state[0] }}</span>
      </div>

      <div>
        <label for="postal_code">Postal Code</label>
        <input type="text" id="postal_code" v-model="form.postal_code">
        <span v-if="errors.postal_code">{{ errors.postal_code[0] }}</span>
      </div>

      <div>
        <label for="country">Country</label>
        <input type="text" id="country" v-model="form.country">
        <span v-if="errors.country">{{ errors.country[0] }}</span>
      </div>

      <div>
        <label for="is_default">Set as Default</label>
        <input type="checkbox" id="is_default" v-model="form.is_default">
        <span v-if="errors.is_default">{{ errors.is_default[0] }}</span>
      </div>

      <button type="submit">Save Address</button>
    </form>

    <hr>

    <h2>Address List</h2>
    <table>
      <thead>
        <tr>
          <th>ID</th>
          <th>Address</th>
          <th>City</th>
          <th>State</th>
          <th>Country</th>
          <th>Default</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="address in addresses" :key="address.id">
          <td>{{ address.id }}</td>
          <td>{{ address.address_line_1 }}</td>
          <td>{{ address.city }}</td>
          <td>{{ address.state }}</td>
          <td>{{ address.country }}</td>
          <td>{{ address.is_default ? 'Yes' : 'No' }}</td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue';
import api from '../../services/api';

const addresses = ref([]);
const errors = ref({});

const form = reactive({
  address_line_1: '',
  city: '',
  state: '',
  postal_code: '',
  country: '',
  is_default: false
});

const fetchAddresses = async () => {
  try {
    const response = await api.get('/api/v1/addresses');
    addresses.value = response.data.data || response.data;
  } catch (error) {
    console.error(error);
  }
};

const createAddress = async () => {
  errors.value = {};
  try {
    await api.post('/api/v1/addresses', form);
    fetchAddresses();
    Object.assign(form, {
      address_line_1: '',
      city: '',
      state: '',
      postal_code: '',
      country: '',
      is_default: false
    });
  } catch (error) {
    if (error.response?.status === 422) {
      errors.value = error.response.data.errors;
    }
  }
};

onMounted(() => {
  fetchAddresses();
});
</script>