<template>
  <div class="w-full min-h-[calc(100vh-6rem)] animate-in fade-in duration-500">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <div class="flex flex-wrap items-center justify-between gap-4 mb-8">
        <div>
          <h1 class="text-3xl sm:text-4xl font-bold tracking-tight text-slate-900">My Addresses</h1>
          <p class="text-slate-500 mt-1.5 text-sm sm:text-base">Delivery addresses for your quote requests.</p>
        </div>
        <button
          type="button"
          @click="openAdd"
          class="inline-flex items-center gap-2.5 px-5 py-3 rounded-xl bg-blue-600 text-white font-semibold shadow-lg shadow-blue-500/25 hover:bg-blue-700 hover:shadow-xl hover:shadow-blue-500/30 transition-all duration-200 active:scale-[0.98]"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
          </svg>
          Add address
        </button>
      </div>

      <div v-if="loading" class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
        <div v-for="i in 6" :key="i" class="bg-white rounded-2xl border border-slate-200 p-8 animate-pulse">
          <div class="h-6 bg-slate-200 rounded w-3/4 mb-4"></div>
          <div class="h-4 bg-slate-100 rounded w-full mb-3"></div>
          <div class="h-4 bg-slate-100 rounded w-1/2"></div>
        </div>
      </div>

      <div
        v-else-if="!addresses || addresses.length === 0"
        class="flex flex-col items-center justify-center min-h-[420px] bg-white rounded-2xl border-2 border-dashed border-slate-200 px-6 py-16 sm:py-24 text-center"
      >
        <div class="w-24 h-24 sm:w-28 sm:h-28 bg-slate-100 rounded-2xl flex items-center justify-center mb-8">
          <svg class="w-12 h-12 sm:w-14 sm:h-14 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
          </svg>
        </div>
        <h2 class="text-xl sm:text-2xl font-bold text-slate-800 mb-2">No addresses yet</h2>
        <p class="text-slate-500 text-base sm:text-lg max-w-md mb-10">Add a delivery address to use when requesting quotes. You can also add one from the quote request basket.</p>
        <button
          type="button"
          @click="openAdd"
          class="inline-flex items-center gap-2.5 px-6 py-3.5 rounded-xl bg-blue-600 text-white font-semibold hover:bg-blue-700 transition-all shadow-lg shadow-blue-500/25 hover:shadow-xl"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
          Add address
        </button>
      </div>

      <div v-else class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
        <div
          v-for="address in addresses"
          :key="address.id"
          class="bg-white rounded-2xl border border-slate-200 p-6 sm:p-8 shadow-sm hover:shadow-lg hover:border-slate-300 transition-all duration-200 flex flex-col"
        >
          <div class="flex items-start justify-between gap-4 mb-4">
            <p class="font-semibold text-slate-900 text-base sm:text-lg leading-snug break-words flex-1 min-w-0">{{ address.address_line_1 }}</p>
            <div class="flex items-center gap-1 shrink-0">
              <button
                type="button"
                @click="openEdit(address)"
                class="p-2.5 text-slate-400 hover:text-blue-600 hover:bg-blue-50 rounded-xl transition-colors"
                title="Edit"
              >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
              </button>
              <button
                type="button"
                @click="confirmDelete(address)"
                class="p-2.5 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-xl transition-colors"
                title="Delete"
              >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
              </button>
            </div>
          </div>
          <p class="text-slate-600 text-sm sm:text-base mt-auto">{{ address.city }}, {{ address.state }} {{ address.zip_code }}</p>
        </div>
      </div>
    </div>

    <AddressFormModal
      :show="modalOpen"
      :address="editingAddress"
      @close="modalOpen = false; editingAddress = null"
      @saved="onAddressSaved"
    />
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import api from '../../services/api';
import AddressFormModal from '../../components/customer/AddressFormModal.vue';
import { useToast } from '../../composables/useToast';
import { useConfirm } from '../../composables/useConfirm';

const addresses = ref([]);
const loading = ref(true);
const modalOpen = ref(false);
const editingAddress = ref(null);

function openAdd() {
  editingAddress.value = null;
  modalOpen.value = true;
}

function openEdit(address) {
  editingAddress.value = { ...address };
  modalOpen.value = true;
}

function onAddressSaved(saved) {
  modalOpen.value = false;
  editingAddress.value = null;
  fetchAddresses();
}

async function confirmDelete(address) {
  const line = address.address_line_1 + ', ' + address.city;
  const ok = await useConfirm().show({ title: 'Delete address', message: `Delete this address? ${line}`, confirmLabel: 'Delete', cancelLabel: 'Cancel', variant: 'danger' });
  if (!ok) return;
  await deleteAddress(address.id);
}

async function fetchAddresses() {
  try {
    const response = await api.get('/api/v1/addresses');
    addresses.value = response.data.data || response.data;
  } catch (err) {
    console.error(err);
  } finally {
    loading.value = false;
  }
}

async function deleteAddress(id) {
  try {
    await api.delete(`/api/v1/addresses/${id}`);
    fetchAddresses();
  } catch (err) {
    useToast().error('Could not delete address.');
  }
}

onMounted(() => {
  fetchAddresses();
});
</script>
