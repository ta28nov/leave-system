<script setup>
import { ref, onErrorCaptured } from 'vue'
import { RefreshCw, AlertTriangle, Home } from 'lucide-vue-next'
import { logger } from '../utils/logger'

const hasError = ref(false)
const errorMessage = ref('')

const handleRetry = () => {
  hasError.value = false
  errorMessage.value = ''
  window.location.reload()
}

const handleGoHome = () => {
  hasError.value = false
  errorMessage.value = ''
  window.location.href = '/dashboard'
}

onErrorCaptured((error, instance, info) => {
  logger.error('ErrorBoundary', 'Uncaught error in component', {
    error: error.message,
    stack: error.stack,
    info
  })
  
  hasError.value = true
  errorMessage.value = error.message || 'An unknown error occurred'
  
  // Prevent error from propagating
  return false
})
</script>

<template>
  <div v-if="hasError" class="min-h-screen bg-gray-50 flex items-center justify-center p-4">
    <div class="text-center max-w-md">
      <div class="w-20 h-20 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-6">
        <AlertTriangle class="w-10 h-10 text-red-600" />
      </div>
      
      <h1 class="text-2xl font-bold text-gray-900 mb-3">Something Went Wrong</h1>
      <p class="text-gray-600 mb-2">
        Sorry, the application encountered an error. Please try again.
      </p>
      <p class="text-sm text-gray-400 mb-6 font-mono bg-gray-100 p-2 rounded">
        {{ errorMessage }}
      </p>
      
      <div class="flex flex-col sm:flex-row gap-3 justify-center">
        <button
          @click="handleRetry"
          class="inline-flex items-center justify-center gap-2 px-6 py-3 bg-indigo-600 text-white font-medium rounded-xl hover:bg-indigo-700 transition"
        >
          <RefreshCw class="w-5 h-5" />
          Try Again
        </button>
        <button
          @click="handleGoHome"
          class="inline-flex items-center justify-center gap-2 px-6 py-3 bg-white border border-gray-300 text-gray-700 font-medium rounded-xl hover:bg-gray-50 transition"
        >
          <Home class="w-5 h-5" />
          Go Home
        </button>
      </div>
    </div>
  </div>
  
  <slot v-else />
</template>
