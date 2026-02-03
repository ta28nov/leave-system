<script setup>
import { useRouter } from 'vue-router'
import { Home, ArrowLeft } from 'lucide-vue-next'

const router = useRouter()

const goHome = () => {
  router.push('/dashboard')
}

const goBack = () => {
  router.back()
}
</script>

<template>
  <div class="min-h-screen bg-gray-50 flex items-center justify-center p-4">
    <div class="text-center">
      <!-- 404 Illustration -->
      <div class="mb-8">
        <div class="relative w-64 h-48 mx-auto">
          <div class="absolute inset-0 bg-gradient-to-br from-indigo-100 to-purple-100 rounded-3xl transform rotate-3"></div>
          <div class="absolute inset-0 flex items-center justify-center">
            <span class="text-8xl font-bold text-indigo-600">404</span>
          </div>
        </div>
      </div>

      <h1 class="text-3xl font-bold text-gray-900 mb-3">Page Not Found</h1>
      <p class="text-gray-600 mb-8 max-w-md mx-auto">
        Sorry, the page you're looking for doesn't exist or has been moved.
      </p>

      <div class="flex flex-col sm:flex-row gap-3 justify-center">
        <button
          @click="goHome"
          class="inline-flex items-center justify-center gap-2 px-6 py-3 bg-indigo-600 text-white font-medium rounded-xl hover:bg-indigo-700 transition"
        >
          <Home class="w-5 h-5" />
          Go Home
        </button>
        <button
          @click="goBack"
          class="inline-flex items-center justify-center gap-2 px-6 py-3 bg-white border border-gray-300 text-gray-700 font-medium rounded-xl hover:bg-gray-50 transition"
        >
          <ArrowLeft class="w-5 h-5" />
          Go Back
        </button>
      </div>
    </div>
  </div>
</template>
