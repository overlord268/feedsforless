<template>
  <router-view />
  <ToastContainer />
  <ConfirmModal />
</template>

<script setup>
import ToastContainer from './components/ui/ToastContainer.vue';
import ConfirmModal from './components/ui/ConfirmModal.vue';
</script>