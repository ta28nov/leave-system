import api from './api'

/**
 * Authentication Service
 * Handles all auth-related API calls to backend
 */

/**
 * Login user with email and password
 * @param {string} email 
 * @param {string} password 
 * @returns {Promise<{access_token, token_type, expires_in, user}>}
 */
export const login = async (email, password) => {
  const response = await api.post('/auth/login', { email, password })
  return response.data
}

/**
 * Register new user
 * @param {Object} userData - { name, email, password, password_confirmation }
 * @returns {Promise<{access_token, token_type, expires_in, user}>}
 */
export const register = async (userData) => {
  const response = await api.post('/auth/register', userData)
  return response.data
}

/**
 * Logout current user (invalidate token)
 * @returns {Promise<{message}>}
 */
export const logout = async () => {
  const response = await api.post('/auth/logout')
  return response.data
}

/**
 * Refresh JWT token
 * @returns {Promise<{access_token, token_type, expires_in}>}
 */
export const refreshToken = async () => {
  const response = await api.post('/auth/refresh')
  return response.data
}

/**
 * Get current authenticated user info
 * @returns {Promise<{user}>}
 */
export const me = async () => {
  const response = await api.get('/auth/me')
  return response.data
}

export default {
  login,
  register,
  logout,
  refreshToken,
  me
}
