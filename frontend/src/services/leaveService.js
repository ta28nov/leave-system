import api from './api'

/**
 * Leave Applications Service
 * Handles all leave-related API calls to backend
 */

/**
 * Get paginated list of leave applications
 * @param {Object} params - { page, per_page, status, user_id, month, year }
 * @returns {Promise<{data, meta}>}
 */
export const getApplications = async (params = {}) => {
  const response = await api.get('/leave-applications', { params })
  return response.data
}

/**
 * Get single leave application by ID
 * @param {string} id 
 * @returns {Promise<{data}>}
 */
export const getApplication = async (id) => {
  const response = await api.get(`/leave-applications/${id}`)
  return response.data
}

/**
 * Create new leave application
 * @param {Object} data - { start_date, end_date, type, reason }
 * @returns {Promise<{data}>}
 */
export const createApplication = async (data) => {
  const response = await api.post('/leave-applications', data)
  return response.data
}

/**
 * Update existing leave application (only status=new)
 * @param {string} id 
 * @param {Object} data - { start_date, end_date, type, reason }
 * @returns {Promise<{data}>}
 */
export const updateApplication = async (id, data) => {
  const response = await api.put(`/leave-applications/${id}`, data)
  return response.data
}

/**
 * Delete (soft delete) leave application
 * @param {string} id 
 * @returns {Promise<{message}>}
 */
export const deleteApplication = async (id) => {
  const response = await api.delete(`/leave-applications/${id}`)
  return response.data
}

/**
 * Approve leave application (Manager/Admin only)
 * @param {string} id 
 * @returns {Promise<{data}>}
 */
export const approveApplication = async (id) => {
  const response = await api.post(`/leave-applications/${id}/approve`)
  return response.data
}

/**
 * Reject leave application (Manager/Admin only)
 * @param {string} id 
 * @param {string} reason - Optional rejection reason
 * @returns {Promise<{data}>}
 */
export const rejectApplication = async (id, reason = '') => {
  const response = await api.post(`/leave-applications/${id}/reject`, { reason })
  return response.data
}

/**
 * Cancel own leave application (Employee)
 * @param {string} id 
 * @returns {Promise<{data}>}
 */
export const cancelApplication = async (id) => {
  const response = await api.post(`/leave-applications/${id}/cancel`)
  return response.data
}

export default {
  getApplications,
  getApplication,
  createApplication,
  updateApplication,
  deleteApplication,
  approveApplication,
  rejectApplication,
  cancelApplication
}
