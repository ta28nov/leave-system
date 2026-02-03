<script setup>
import { ref, watch } from 'vue'
import { AlertTriangle, CheckCircle, Info, XCircle, X } from 'lucide-vue-next'
import LoadingSpinner from './LoadingSpinner.vue'

const props = defineProps({
  modelValue: {
    type: Boolean,
    default: false
  },
  title: {
    type: String,
    default: 'Confirm'
  },
  message: {
    type: String,
    default: 'Are you sure you want to proceed?'
  },
  type: {
    type: String,
    default: 'warning',
    validator: (v) => ['warning', 'danger', 'success', 'info'].includes(v)
  },
  confirmText: {
    type: String,
    default: 'Confirm'
  },
  cancelText: {
    type: String,
    default: 'Cancel'
  },
  loading: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['update:modelValue', 'confirm', 'cancel'])

const isVisible = ref(props.modelValue)

watch(() => props.modelValue, (val) => {
  isVisible.value = val
  if (val) {
    document.body.style.overflow = 'hidden'
  } else {
    document.body.style.overflow = ''
  }
})

const typeConfig = {
  warning: {
    icon: AlertTriangle,
    iconBg: 'bg-amber-100',
    iconColor: 'text-amber-600',
    confirmBg: 'bg-amber-600 hover:bg-amber-700',
    confirmText: 'text-white'
  },
  danger: {
    icon: XCircle,
    iconBg: 'bg-red-100',
    iconColor: 'text-red-600',
    confirmBg: 'bg-red-600 hover:bg-red-700',
    confirmText: 'text-white'
  },
  success: {
    icon: CheckCircle,
    iconBg: 'bg-green-100',
    iconColor: 'text-green-600',
    confirmBg: 'bg-green-600 hover:bg-green-700',
    confirmText: 'text-white'
  },
  info: {
    icon: Info,
    iconBg: 'bg-blue-100',
    iconColor: 'text-blue-600',
    confirmBg: 'bg-blue-600 hover:bg-blue-700',
    confirmText: 'text-white'
  }
}

const config = () => typeConfig[props.type] || typeConfig.warning

const handleConfirm = () => {
  if (!props.loading) {
    emit('confirm')
  }
}

const handleCancel = () => {
  if (!props.loading) {
    isVisible.value = false
    emit('update:modelValue', false)
    emit('cancel')
  }
}
</script>

<template>
  <Teleport to="body">
    <Transition
      enter-active-class="duration-200 ease-out"
      enter-from-class="opacity-0"
      enter-to-class="opacity-100"
      leave-active-class="duration-150 ease-in"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0"
    >
      <div 
        v-if="isVisible"
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm"
        @click.self="handleCancel"
      >
        <div 
          class="w-full max-w-md bg-white rounded-2xl shadow-2xl overflow-hidden transform transition-all duration-200"
          :class="isVisible ? 'opacity-100 scale-100' : 'opacity-0 scale-95'"
        >
            <!-- Content -->
            <div class="p-6 text-center">
              <!-- Icon -->
              <div 
                class="w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4"
                :class="config().iconBg"
              >
                <component :is="config().icon" class="w-8 h-8" :class="config().iconColor" />
              </div>

              <!-- Title -->
              <h3 class="text-xl font-semibold text-gray-900 mb-2">{{ title }}</h3>

              <!-- Message -->
              <p class="text-gray-600">{{ message }}</p>

              <!-- Extra content slot -->
              <div v-if="$slots.default" class="mt-4">
                <slot />
              </div>
            </div>

            <!-- Actions -->
            <div class="px-6 pb-6 flex gap-3">
              <button
                @click="handleCancel"
                :disabled="loading"
                class="flex-1 px-4 py-3 bg-gray-100 hover:bg-gray-200 disabled:opacity-50 text-gray-900 font-medium rounded-xl transition"
              >
                {{ cancelText }}
              </button>
              <button
                @click="handleConfirm"
                :disabled="loading"
                class="flex-1 px-4 py-3 font-medium rounded-xl transition flex items-center justify-center gap-2 disabled:opacity-70"
                :class="[config().confirmBg, config().confirmText]"
              >
              <LoadingSpinner v-if="loading" size="sm" color="white" />
                {{ confirmText }}
              </button>
            </div>
          </div>
        </div>
    </Transition>
  </Teleport>
</template>


