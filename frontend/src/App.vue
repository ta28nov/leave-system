<script setup>
import { onMounted } from "vue";
import { useAuthStore } from "./stores/auth";
import ToastContainer from "./components/ToastContainer.vue";
import ErrorBoundary from "./components/ErrorBoundary.vue";

const authStore = useAuthStore();

onMounted(() => {
  authStore.initializeAuth();
});
</script>

<template>
  <ErrorBoundary>
    <RouterView v-slot="{ Component }">
      <Transition name="fade" mode="out-in">
        <component :is="Component" />
      </Transition>
    </RouterView>
  </ErrorBoundary>
  <ToastContainer />
</template>

<style>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.15s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>
