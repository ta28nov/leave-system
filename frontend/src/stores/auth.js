import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import authService from '../services/authService'
import { logger } from '../utils/logger'

export const useAuthStore = defineStore('auth', () => {
  const user = ref(null)
  const token = ref(localStorage.getItem('authToken') || null)
  const isLoading = ref(false)
  const error = ref(null)

  // Computed properties
  const isAuthenticated = computed(() => !!token.value && !!user.value)
  const isAdmin = computed(() => user.value?.type === 0)
  const isManager = computed(() => user.value?.type === 1)
  const isEmployee = computed(() => user.value?.type === 2)
  const userType = computed(() => {
    if (user.value?.type === 0) return 'admin'
    if (user.value?.type === 1) return 'manager'
    return 'employee'
  })

  /**
   * Login with email and password
   */
  const login = async (email, password) => {
    isLoading.value = true
    error.value = null

    try {
      const response = await authService.login(email, password)
      
      logger.debug('Auth', 'Login response', response)

      // Handle different response formats from backend
      // Format 1: { success: true, data: { access_token, user } }
      // Format 2: { access_token, user }
      // Format 3: { data: { access_token, user } }
      
      let accessToken = null
      let userData = null

      if (response.success && response.data) {
        // Format 1: wrapped with success
        accessToken = response.data.access_token || response.data.token
        userData = response.data.user
      } else if (response.data && response.data.access_token) {
        // Format 3: wrapped with data only
        accessToken = response.data.access_token || response.data.token
        userData = response.data.user
      } else if (response.access_token || response.token) {
        // Format 2: direct response
        accessToken = response.access_token || response.token
        userData = response.user
      }

      if (accessToken) {
        // We have a token
        token.value = accessToken
        localStorage.setItem('authToken', accessToken)

        // If userData is missing, try to fetch it
        if (!userData) {
           logger.info('Auth', 'User data missing in login response, fetching /me...')
           try {
             // Pass the token explicitly or rely on interceptor (token.value is set)
             const meResponse = await authService.me()
             if (meResponse.success && meResponse.data) {
               userData = meResponse.data
             } else if (meResponse.data) {
               userData = meResponse.data
             } else if (meResponse.id) {
               userData = meResponse
             }
           } catch (meError) {
             logger.error('Auth', 'Failed to fetch user info after login', meError)
           }
        }
      }

      if (accessToken && userData) {
        token.value = accessToken
        user.value = userData
        
        // Save to localStorage
        localStorage.setItem('authToken', accessToken)
        localStorage.setItem('user', JSON.stringify(userData))
        
        logger.info('Auth', 'Login successful', { userId: userData.id })
        
        isLoading.value = false
        return true
      } else {
        // No valid token/user found
        let msg = 'Login failed'
        if (!accessToken) msg += ' - No access token'
        if (accessToken && !userData) msg += ' - Failed to retrieve user info'

        error.value = response.message || msg
        logger.error('Auth', msg, response)
        
        // Cleanup if partial success
        if (accessToken) {
          token.value = null
          localStorage.removeItem('authToken')
        }

        isLoading.value = false
        return false
      }
    } catch (err) {
      logger.error('Auth', 'Login error', err)
      error.value = err.message || 'Invalid email or password'
      isLoading.value = false
      return false
    }
  }

  /**
   * Register new user
   */
  const register = async (userData) => {
    isLoading.value = true
    error.value = null

    try {
      const response = await authService.register(userData)
      
      logger.debug('Auth', 'Register response', response)

      // Handle different response formats
      let accessToken = null
      let userInfo = null
      
      // Check for token in different structures
      if (response.success && response.data) {
        accessToken = response.data.access_token || response.data.token
        userInfo = response.data.user
      } else if (response.data && response.data.access_token) {
        accessToken = response.data.access_token || response.data.token
        userInfo = response.data.user
      } else if (response.access_token || response.token) {
        accessToken = response.access_token || response.token
        userInfo = response.user
      }

      // Case 1: Registration with Auto-Login (Token returned)
      if (accessToken && userInfo) {
        token.value = accessToken
        user.value = userInfo
        
        localStorage.setItem('authToken', accessToken)
        localStorage.setItem('user', JSON.stringify(userInfo))
        
        isLoading.value = false
        return { success: true, autoLogin: true }
      } 
      
      // Case 2: Registration Successful but NO Token (Require Manual Login)
      // If we got a user object or a success message/status, consider it a success
      if (userInfo || response.message || response.success) {
         isLoading.value = false
         return { 
           success: true, 
           autoLogin: false, 
           message: response.message || 'Registration successful. Please login.' 
         }
      }

      // Case 3: Failed
      error.value = response.message || 'Registration failed'
      isLoading.value = false
      return { success: false, message: error.value }

    } catch (err) {
      logger.error('Auth', 'Register error', err)
      error.value = err.message || 'Registration failed'
      isLoading.value = false
      return { success: false, message: error.value }
    }
  }

  /**
   * Logout current user
   */
  const logout = async () => {
    try {
      if (token.value) {
        await authService.logout()
      }
    } catch (err) {
      logger.error('Auth', 'Logout error', err)
    } finally {
      // Always clear local state
      token.value = null
      user.value = null
      localStorage.removeItem('authToken')
      localStorage.removeItem('user')
    }
  }

  /**
   * Fetch current user info from API
   */
  const fetchMe = async () => {
    if (!token.value) return false

    try {
      const response = await authService.me()
      
      // Handle different response formats
      let userData = null
      if (response.success && response.data) {
        userData = response.data
      } else if (response.data) {
        userData = response.data
      } else if (response.user) {
        userData = response.user
      } else if (response.id) {
        // Response is the user object directly
        userData = response
      }

      if (userData) {
        user.value = userData
        localStorage.setItem('user', JSON.stringify(userData))
        return true
      }
      return false
    } catch (err) {
      // Token invalid, clear auth
      logger.error('Auth', 'fetchMe error', err)
      logout()
      return false
    }
  }

  /**
   * Initialize auth state from localStorage
   */
  const initializeAuth = async () => {
    const savedToken = localStorage.getItem('authToken')
    const savedUser = localStorage.getItem('user')
    
    if (savedToken) {
      token.value = savedToken
      
      if (savedUser) {
        try {
          user.value = JSON.parse(savedUser)
        } catch (e) {
          user.value = null
        }
      }
      
      // Optionally verify token with backend
      // await fetchMe()
    }
  }

  return {
    // State
    user,
    token,
    isLoading,
    error,
    // Computed
    isAuthenticated,
    isAdmin,
    isManager,
    isEmployee,
    userType,
    // Actions
    login,
    register,
    logout,
    fetchMe,
    initializeAuth
  }
})
