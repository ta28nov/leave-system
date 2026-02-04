import axios from 'axios'
import { logger } from '../utils/logger'

const API_BASE_URL = import.meta.env.VITE_API_URL 
  ? `${import.meta.env.VITE_API_URL}/api`
  : 'http://leave-system.test/api'

// Store for active request controllers
const activeControllers = new Map()

const api = axios.create({
  baseURL: API_BASE_URL,
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json'
  },
  timeout: 30000 // 30 seconds
})

/**
 * Cancel all pending requests
 */
export const cancelAllRequests = () => {
  activeControllers.forEach((controller, key) => {
    controller.abort()
    activeControllers.delete(key)
  })
}

// Request Interceptor - đính kèm Token
api.interceptors.request.use(
  config => {
    const token = localStorage.getItem('authToken')
    if (token) {
      config.headers.Authorization = `Bearer ${token}`
    }
    
    // Log request in dev mode
    logger.debug('API', `${config.method?.toUpperCase()} ${config.url}`, {
      params: config.params,
      data: config.data
    })
    
    return config
  },
  error => {
    logger.error('API', 'Request error', error)
    return Promise.reject(error)
  }
)

// Response Interceptor - xử lý response và lỗi
api.interceptors.response.use(
  response => {
    // Log success response in dev mode
    logger.debug('API', `Response ${response.status}`, {
      url: response.config.url,
      data: response.data
    })
    
    // Return full axios response - services sẽ access .data
    return response
  },
  async error => {
    const status = error.response?.status
    const originalRequest = error.config
    const url = originalRequest?.url || 'unknown'
    const errorData = error.response?.data

    // Log all errors
    logger.apiError(url, status, errorData?.message || error.message, errorData)

    // 401 - Unauthorized: token expired or invalid
    if (status === 401 && !originalRequest._retry) {
      originalRequest._retry = true
      
      // Clear auth and redirect to login
      localStorage.removeItem('authToken')
      localStorage.removeItem('user')
      
      // Only redirect if not already on login page
      if (!window.location.pathname.includes('/login')) {
        window.location.href = '/login'
      }
    }

    // 403 - Forbidden: access denied
    if (status === 403) {
      logger.warn('API', 'Access denied', { url, message: errorData?.message })
    }

    // 422 - Validation Error
    if (status === 422) {
      logger.warn('API', 'Validation failed', { url, errors: errorData?.data })
    }

    // 500+ - Server Errors
    if (status >= 500) {
      logger.error('API', 'Server error', { url, status, message: errorData?.message })
    }

    // Network errors - only log if not cancelled
    if (!status && !axios.isCancel(error)) {
      logger.error('API', 'Network error', { url, message: error.message })
    }

    // Don't reject cancelled requests
    if (axios.isCancel(error)) {
      return Promise.reject({
        success: false,
        cancelled: true,
        message: 'Request cancelled'
      })
    }

    // Return structured error for better handling in components
    return Promise.reject({
      success: false,
      status: status || 0,
      message: errorData?.message || error.message || 'Đã xảy ra lỗi',
      data: errorData?.data || null,
      errors: errorData?.errors || null
    })
  }
)

export default api
