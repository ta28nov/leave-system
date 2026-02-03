<script setup>
import { toasts, removeToast } from '../composables/useToast'
import Toast from './Toast.vue'
</script>

<template>
  <Teleport to="body">
    <div class="fixed top-4 right-4 z-[100] flex flex-col gap-3 max-w-sm w-full pointer-events-none">
      <div 
        v-for="t in toasts" 
        :key="t.id"
        class="pointer-events-auto"
      >
        <Toast
          :id="t.id"
          :type="t.type"
          :title="t.title"
          :message="t.message"
          :duration="t.duration"
          @dismiss="removeToast"
        />
      </div>
    </div>
  </Teleport>
</template>
