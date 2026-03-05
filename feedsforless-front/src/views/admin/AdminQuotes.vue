<template>
  <div>
    <h1>Quotes</h1>

    <table>
      <thead>
        <tr>
          <th>ID</th>
          <th>Customer</th>
          <th>Status</th>
          <th>Notes</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="quote in quotes" :key="quote.id">
          <td>{{ quote.id }}</td>
          <td>{{ quote.customer_name }}</td>
          <td>{{ quote.status }}</td>
          <td>{{ quote.notes }}</td>
          <td>
            <form @submit.prevent="updateStatus(quote.id)">
              <select v-model="statusForm[quote.id]">
                <option value="pending">Pending</option>
                <option value="approved">Approved</option>
                <option value="rejected">Rejected</option>
              </select>
              <input type="text" v-model="noteForm[quote.id]" placeholder="Admin Note">
              <button type="submit">Update</button>
              <span v-if="errors[quote.id]">{{ errors[quote.id] }}</span>
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

const quotes = ref([]);
const statusForm = reactive({});
const noteForm = reactive({});
const errors = reactive({});

const fetchQuotes = async () => {
  try {
    const response = await api.get('/api/v1/admin/quote-requests');
    quotes.value = response.data.data || response.data;
    quotes.value.forEach(quote => {
      if (!statusForm[quote.id]) {
        statusForm[quote.id] = quote.status;
        noteForm[quote.id] = quote.admin_note || '';
      }
    });
  } catch (error) {
    console.error(error);
  }
};

const updateStatus = async (id) => {
  errors[id] = null;
  try {
    await api.put(`/api/v1/admin/quote-requests/${id}/prices`, {
      status: statusForm[id],
      admin_note: noteForm[id]
    });
    fetchQuotes();
  } catch (error) {
    if (error.response?.status === 422) {
      errors[id] = 'Validation error check inputs';
    }
  }
};

onMounted(() => {
  fetchQuotes();
});
</script>