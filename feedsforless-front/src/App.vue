<template>
  <router-view />
  <ToastContainer />
  <ConfirmModal />
</template>

<script setup>
import { onMounted } from 'vue';
import ToastContainer from './components/ui/ToastContainer.vue';
import ConfirmModal from './components/ui/ConfirmModal.vue';
import { useThemeStore } from './stores/theme';

onMounted(() => {
  useThemeStore().init();
});
</script>