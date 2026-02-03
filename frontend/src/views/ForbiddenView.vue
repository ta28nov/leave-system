<script setup>
import { useRouter } from 'vue-router'
import { Home, ArrowLeft, ShieldX } from 'lucide-vue-next'

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
      <!-- 403 Icon -->
      <div class="w-24 h-24 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-8">
        <ShieldX class="w-12 h-12 text-red-600" />
      </div>

      <h1 class="text-3xl font-bold text-gray-900 mb-3">Access Denied</h1>
      <p class="text-gray-600 mb-8 max-w-md mx-auto">
        You don't have permission to access this page. Please contact your administrator if you believe this is an error.
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
