<template>
  <div class="space-y-10 animate-in fade-in duration-500 max-w-5xl mx-auto">
    
    <div>
      <h1 class="text-3xl font-bold tracking-tight text-slate-900">My Quotes</h1>
      <p class="text-slate-500 mt-1">Manage your current request and view your quote history.</p>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="space-y-8 animate-pulse">
      <div class="bg-white p-6 rounded-2xl border border-slate-200">
        <div class="h-6 bg-slate-200 rounded w-1/4 mb-4"></div>
        <div class="h-20 bg-slate-100 rounded w-full mb-4"></div>
        <div class="h-10 bg-slate-200 rounded w-32"></div>
      </div>
    </div>

    <div v-else class="space-y-10">

      <!-- CURRENT RFQ ITEM (CART) -->
      <section class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
          <h2 class="text-lg font-bold text-slate-800 flex items-center gap-2">
            <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
            Current Request Basket
          </h2>
          <span v-if="rfqList?.items?.length" class="text-sm font-medium text-slate-500">
            {{ rfqList.items.length }} item(s)
          </span>
        </div>

        <div v-if="!rfqList || !rfqList.items || rfqList.items.length === 0" class="p-10 text-center">
          <div class="w-16 h-16 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-4 border border-slate-100">
             <svg class="w-8 h-8 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
          </div>
          <p class="text-slate-500 mb-4">Your quote request basket is empty.</p>
          <router-link to="/catalog" class="inline-flex items-center px-4 py-2 bg-blue-50 text-blue-600 font-medium rounded-lg hover:bg-blue-100 transition-colors text-sm">
            Browse Catalog
          </router-link>
        </div>

        <div v-else class="divide-y divide-slate-100">
          <!-- Items List -->
          <ul class="p-0 m-0 list-none">
            <li v-for="item in rfqList.items" :key="item.id" class="p-6 flex flex-col sm:flex-row sm:items-center gap-4 hover:bg-slate-50/50 transition-colors">
              <div class="flex-1">
                <h4 class="font-semibold text-slate-900">{{ item.product?.name || 'Unknown Product' }}</h4>
                <p class="text-sm text-slate-500 mt-1">
                  Packaging: <span class="font-medium text-slate-700">{{ item.packaging_type?.name || 'Default' }}</span>
                </p>
              </div>
              <div class="flex items-center gap-6">
                <div class="text-right">
                  <p class="text-xs text-slate-400 font-medium uppercase tracking-wider mb-1">Quantity</p>
                  <p class="font-semibold text-slate-900">{{ item.quantity }} units</p>
                </div>
                <button @click="removeRfqItem(item.id)" class="p-2 text-slate-400 hover:text-red-500 hover:bg-red-50 rounded-lg transition-colors" title="Remove item">
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                </button>
              </div>
            </li>
          </ul>

          <!-- Submit Form -->
          <div class="p-6 bg-slate-50/50">
            <h3 class="font-semibold text-slate-800 mb-4">Delivery Details</h3>
            
            <form @submit.prevent="submitQuote" class="grid grid-cols-1 md:grid-cols-2 gap-6">
              
              <!-- Address Selection -->
              <div class="space-y-1.5 md:col-span-2">
                <label class="text-sm font-medium text-slate-700">Saved Address (Optional)</label>
                <select v-model="form.target_address_id" @change="fillZipFromAddress" class="w-full px-4 py-2.5 border border-slate-200 rounded-xl bg-white text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500 transition-all">
                  <option :value="null">-- Select an address or enter ZIP manually --</option>
                  <option v-for="address in addresses" :key="address.id" :value="address.id">
                    {{ address.address_line_1 }}, {{ address.city }}, {{ address.state }} {{ address.zip_code }}
                  </option>
                </select>
                <p v-if="errors.target_address_id" class="text-red-500 text-xs mt-1">{{ errors.target_address_id[0] }}</p>
              </div>

              <!-- ZIP Code -->
              <div class="space-y-1.5">
                <label class="text-sm font-medium text-slate-700">Delivery ZIP Code <span class="text-red-500">*</span></label>
                <input v-model="form.delivery_zip" type="text" required class="w-full px-4 py-2.5 border border-slate-200 rounded-xl bg-white text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500 transition-all" placeholder="e.g. 12345">
                 <p v-if="errors.delivery_zip" class="text-red-500 text-xs mt-1">{{ errors.delivery_zip[0] }}</p>
              </div>

              <!-- Checkboxes -->
              <div class="space-y-3 flex flex-col justify-center mt-2 md:mt-6">
                <label class="flex items-center gap-3 cursor-pointer group">
                  <div class="relative flex items-center justify-center w-5 h-5 border-2 border-slate-300 rounded group-hover:border-blue-500 transition-colors" :class="{ 'bg-blue-600 border-blue-600': form.requires_liftgate }">
                    <input type="checkbox" v-model="form.requires_liftgate" class="sr-only">
                    <svg v-if="form.requires_liftgate" class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                  </div>
                  <span class="text-sm text-slate-700 select-none">Requires Liftgate</span>
                </label>
                
                <label class="flex items-center gap-3 cursor-pointer group">
                  <div class="relative flex items-center justify-center w-5 h-5 border-2 border-slate-300 rounded group-hover:border-blue-500 transition-colors" :class="{ 'bg-blue-600 border-blue-600': form.requires_appointment }">
                    <input type="checkbox" v-model="form.requires_appointment" class="sr-only">
                    <svg v-if="form.requires_appointment" class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                  </div>
                  <span class="text-sm text-slate-700 select-none">Requires Delivery Appointment</span>
                </label>
              </div>

              <!-- General Error -->
              <div v-if="submitError" class="md:col-span-2 text-red-500 text-sm p-3 bg-red-50 rounded-lg border border-red-100">
                {{ submitError }}
              </div>

              <!-- Submit Button -->
              <div class="md:col-span-2 flex justify-end pt-4 border-t border-slate-200 mt-2">
                <button type="submit" :disabled="submitting" class="px-6 py-2.5 bg-blue-600 text-white font-medium rounded-xl hover:bg-blue-700 transition-colors shadow-sm shadow-blue-500/30 disabled:opacity-70 disabled:cursor-not-allowed flex items-center gap-2">
                  <svg v-if="submitting" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                  <span>{{ submitting ? 'Submitting...' : 'Submit Quote Request' }}</span>
                </button>
              </div>
            </form>
          </div>
        </div>
      </section>

      <!-- QUOTE HISTORY -->
      <section>
        <h2 class="text-xl font-bold text-slate-900 mb-6">Quote History</h2>
        
        <div v-if="!quotes || quotes.length === 0" class="bg-white border text-center p-8 rounded-2xl border-slate-200 shadow-sm text-slate-500">
          You don't have any past quote requests.
        </div>
        
        <div v-else class="space-y-4">
          <div v-for="quote in quotes" :key="quote.id" class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-4">
              <div>
                <div class="flex items-center gap-3 mb-1">
                  <h3 class="font-bold text-slate-900">Request #{{ quote.id }}</h3>
                  <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold capitalize" :class="statusClass(quote.status)">
                    {{ quote.status }}
                  </span>
                </div>
                <p class="text-sm text-slate-500">Delivery ZIP: <span class="text-slate-700 font-medium">{{ quote.delivery_zip }}</span></p>
              </div>
              <div class="text-left md:text-right">
                <p class="text-xs text-slate-400 font-medium uppercase tracking-wider mb-1">Estimated Total</p>
                <p class="text-xl font-bold text-slate-900">${{ quote.total_estimated_cost || '0.00' }}</p>
              </div>
            </div>

            <div class="bg-slate-50 rounded-xl p-4 border border-slate-100">
              <p class="text-xs font-bold text-slate-500 uppercase tracking-widest mb-3">Requested Items</p>
              <ul class="space-y-2">
                <li v-for="item in quote.items" :key="item.id" class="flex justify-between text-sm">
                  <span class="text-slate-700 font-medium">{{ item.product?.name || 'Item' }}</span>
                  <span class="text-slate-500">{{ item.qty }} units</span>
                </li>
              </ul>
            </div>
            
            <!-- Actions for quoted status -->
            <div v-if="quote.status === 'quoted'" class="mt-4 pt-4 border-t border-slate-100 flex justify-end gap-3">
               <button @click="rejectQuote(quote.id)" class="px-4 py-2 bg-white border border-red-200 text-red-600 font-medium rounded-lg hover:bg-red-50 transition-colors text-sm focus:outline-none focus:ring-2 focus:ring-red-500/50">
                Reject Quote
              </button>
              <button @click="acceptQuote(quote.id)" class="px-4 py-2 bg-emerald-600 text-white font-medium rounded-lg hover:bg-emerald-700 transition-colors shadow-sm shadow-emerald-500/20 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/50">
                Accept Quote
              </button>
            </div>
          </div>
        </div>
      </section>

    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue';
import api from '../../services/api';

const loading = ref(true);
const rfqList = ref(null);
const quotes = ref([]);
const addresses = ref([]);
const errors = ref({});
const submitError = ref('');
const submitting = ref(false);

const form = reactive({
  target_address_id: null,
  delivery_zip: '',
  requires_liftgate: false,
  requires_appointment: false,
});

const statusClass = (status) => {
  switch (status) {
    case 'pending': return 'bg-amber-100 text-amber-700';
    case 'quoted': return 'bg-blue-100 text-blue-700';
    case 'accepted': return 'bg-emerald-100 text-emerald-700';
    case 'rejected': return 'bg-red-100 text-red-700';
    case 'expired': return 'bg-slate-100 text-slate-700';
    case 'cancelled': return 'bg-slate-100 text-slate-700';
    default: return 'bg-slate-100 text-slate-600';
  }
};

const fetchData = async () => {
  loading.value = true;
  try {
    const [rfqRes, quotesRes, addrRes] = await Promise.all([
      api.get('/api/v1/rfq-list'),
      api.get('/api/v1/quote-requests'),
      api.get('/api/v1/addresses')
    ]);
    
    rfqList.value = rfqRes.data.data;
    quotes.value = quotesRes.data.data || quotesRes.data;
    addresses.value = addrRes.data.data || addrRes.data;
  } catch (error) {
    console.error('Error fetching dashboard data:', error);
  } finally {
    loading.value = false;
  }
};

const fillZipFromAddress = () => {
  if (form.target_address_id) {
    const addr = addresses.value.find(a => a.id === form.target_address_id);
    if (addr && addr.zip_code) {
      form.delivery_zip = addr.zip_code;
    }
  } else {
    form.delivery_zip = '';
  }
};

const removeRfqItem = async (itemId) => {
  if (!confirm('Remove this item from your request?')) return;
  
  try {
    await api.delete(`/api/v1/rfq-list/items/${itemId}`);
    // Refresh RFQ list
    const res = await api.get('/api/v1/rfq-list');
    rfqList.value = res.data.data;
  } catch (e) {
    alert('Could not remove item.');
  }
};

const submitQuote = async () => {
  if (!rfqList.value || !rfqList.value.id) return;
  
  submitting.value = true;
  errors.value = {};
  submitError.value = '';

  try {
    await api.post('/api/v1/quote-requests', {
      rfq_list_id: rfqList.value.id,
      target_address_id: form.target_address_id,
      delivery_zip: form.delivery_zip,
      requires_liftgate: form.requires_liftgate,
      requires_appointment: form.requires_appointment
    });
    
    // Reset form and reload data
    form.target_address_id = null;
    form.delivery_zip = '';
    form.requires_liftgate = false;
    form.requires_appointment = false;
    
    await fetchData();
    alert('Quote request submitted successfully!');
    
  } catch (error) {
    if (error.response?.status === 422) {
      errors.value = error.response.data.errors || {};
    } else {
      submitError.value = error.response?.data?.message || 'An error occurred while submitting.';
    }
  } finally {
    submitting.value = false;
  }
};

const acceptQuote = async (id) => {
    if(!confirm("Are you sure you want to accept this quote?")) return;
    try {
        await api.post(`/api/v1/quote-requests/${id}/accept`);
        await fetchData(); // simple refetch
    } catch {
        alert("Failed to accept quote.");
    }
}

const rejectQuote = async (id) => {
    if(!confirm("Are you sure you want to reject this quote?")) return;
    try {
        await api.post(`/api/v1/quote-requests/${id}/reject`);
        await fetchData(); // simple refetch
    } catch {
        alert("Failed to reject quote.");
    }
}

onMounted(() => {
  fetchData();
});
</script>