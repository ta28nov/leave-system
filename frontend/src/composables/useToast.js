import { ref } from 'vue'

// Global toast state - shared across all components
const toasts = ref([])
let toastId = 0

/**
 * Add new toast notification
 */
const addToast = (options) => {
  const id = ++toastId
  const toast = {
    id,
    type: options.type || 'info',
    title: options.title || '',
    message: options.message,
    duration: options.duration ?? 5000
  }
  toasts.value.push(toast)
  return id
}

/**
 * Remove toast by id
 */
const removeToast = (id) => {
  const index = toasts.value.findIndex(t => t.id === id)
  if (index !== -1) {
    toasts.value.splice(index, 1)
  }
}

/**
 * Toast composable for showing notifications
 */
export function useToast() {
  const success = (message, title = '') => addToast({ type: 'success', message, title })
  const error = (message, title = '') => addToast({ type: 'error', message, title })
  const warning = (message, title = '') => addToast({ type: 'warning', message, title })
  const info = (message, title = '') => addToast({ type: 'info', message, title })

  return {
    toasts,
    addToast,
    removeToast,
    success,
    error,
    warning,
    info
  }
}

// Export for direct import
export { toasts, addToast, removeToast }

export default useToast
