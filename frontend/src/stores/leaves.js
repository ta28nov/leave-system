import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import leaveService from '../services/leaveService'

export const useLeavesStore = defineStore('leaves', () => {
  // State
  const applications = ref([])
  const currentApplication = ref(null)
  const isLoading = ref(false)
  const error = ref(null)
  
  // Pagination
  const pagination = ref({
    currentPage: 1,
    perPage: 10,
    total: 0,
    lastPage: 1
  })

  // Filters
  const filters = ref({
    status: '',
    user_id: '',
    month: '',
    year: ''
  })

  /**
   * Fetch leave applications with pagination and filters
   */
  const fetchApplications = async (params = {}) => {
    isLoading.value = true
    error.value = null

    try {
      const queryParams = {
        page: params.page || pagination.value.currentPage,
        per_page: params.per_page || pagination.value.perPage,
        ...filters.value,
        ...params
      }

      // Remove empty params
      Object.keys(queryParams).forEach(key => {
        if (!queryParams[key]) delete queryParams[key]
      })

      console.log('[Leaves Store] Fetching applications with params:', queryParams);

      const response = await leaveService.getApplications(queryParams)
      
      console.log('[Leaves Store] Backend response:', response);

      if (response.success) {
        // Backend returns: { success: true, data: { current_page, data: [...], last_page, total, ... } }
        const paginationData = response.data;
        
        // The actual applications array is in data.data
        applications.value = paginationData.data || [];
        
        // Update pagination metadata from Laravel pagination response
        pagination.value = {
          currentPage: paginationData.current_page || 1,
          perPage: paginationData.per_page || 10,
          total: paginationData.total || 0,
          lastPage: paginationData.last_page || 1
        };

        console.log('[Leaves Store] Parsed applications:', applications.value.length);
        console.log('[Leaves Store] Pagination:', pagination.value);
      }
      
      isLoading.value = false
      return applications.value
    } catch (err) {
      console.error('[Leaves Store] Fetch error:', err);
      error.value = err.message || 'Không thể tải danh sách đơn'
      isLoading.value = false
      return []
    }
  }

  /**
   * Get single application by ID
   */
  const fetchApplication = async (id) => {
    isLoading.value = true
    error.value = null

    try {
      const response = await leaveService.getApplication(id)
      if (response.success) {
        currentApplication.value = response.data
        return response.data
      }
      return null
    } catch (err) {
      error.value = err.message || 'Không tìm thấy đơn'
      return null
    } finally {
      isLoading.value = false
    }
  }

  /**
   * Create new leave application
   */
  const createApplication = async (formData) => {
    isLoading.value = true
    error.value = null

    try {
      const response = await leaveService.createApplication(formData)
      
      if (response.success) {
        // Add to local list
        applications.value.unshift(response.data)
        isLoading.value = false
        return { success: true, data: response.data }
      }
      
      isLoading.value = false
      return { success: false, message: response.message }
    } catch (err) {
      error.value = err.message || 'Không thể tạo đơn'
      isLoading.value = false
      return { success: false, message: err.message, errors: err.data }
    }
  }

  /**
   * Update existing application
   */
  const updateApplication = async (id, formData) => {
    isLoading.value = true
    error.value = null

    try {
      const response = await leaveService.updateApplication(id, formData)
      
      if (response.success) {
        // Update local list
        const index = applications.value.findIndex(app => app.id === id)
        if (index !== -1) {
          applications.value[index] = response.data
        }
        isLoading.value = false
        return { success: true, data: response.data }
      }
      
      isLoading.value = false
      return { success: false, message: response.message }
    } catch (err) {
      error.value = err.message || 'Không thể cập nhật đơn'
      isLoading.value = false
      return { success: false, message: err.message, errors: err.data }
    }
  }

  /**
   * Delete application (soft delete)
   */
  const deleteApplication = async (id) => {
    isLoading.value = true
    error.value = null

    try {
      const response = await leaveService.deleteApplication(id)
      
      if (response.success) {
        // Remove from local list
        applications.value = applications.value.filter(app => app.id !== id)
        isLoading.value = false
        return { success: true }
      }
      
      isLoading.value = false
      return { success: false, message: response.message }
    } catch (err) {
      error.value = err.message || 'Không thể xóa đơn'
      isLoading.value = false
      return { success: false, message: err.message }
    }
  }

  /**
   * Approve application (Manager/Admin)
   */
  const approveApplication = async (id) => {
    isLoading.value = true
    error.value = null

    try {
      const response = await leaveService.approveApplication(id)
      
      if (response.success) {
        // Update local list
        const index = applications.value.findIndex(app => app.id === id)
        if (index !== -1) {
          applications.value[index] = { ...applications.value[index], status: 'approved' }
        }
        isLoading.value = false
        return { success: true, data: response.data }
      }
      
      isLoading.value = false
      return { success: false, message: response.message }
    } catch (err) {
      error.value = err.message || 'Không thể duyệt đơn'
      isLoading.value = false
      return { success: false, message: err.message }
    }
  }

  /**
   * Reject application (Manager/Admin)
   */
  const rejectApplication = async (id, reason = '') => {
    isLoading.value = true
    error.value = null

    try {
      const response = await leaveService.rejectApplication(id, reason)
      
      if (response.success) {
        // Update local list
        const index = applications.value.findIndex(app => app.id === id)
        if (index !== -1) {
          applications.value[index] = { ...applications.value[index], status: 'rejected' }
        }
        isLoading.value = false
        return { success: true, data: response.data }
      }
      
      isLoading.value = false
      return { success: false, message: response.message }
    } catch (err) {
      error.value = err.message || 'Không thể từ chối đơn'
      isLoading.value = false
      return { success: false, message: err.message }
    }
  }

  /**
   * Cancel own application (Employee)
   */
  const cancelApplication = async (id) => {
    isLoading.value = true
    error.value = null

    try {
      const response = await leaveService.cancelApplication(id)
      
      if (response.success) {
        // Update local list
        const index = applications.value.findIndex(app => app.id === id)
        if (index !== -1) {
          applications.value[index] = { ...applications.value[index], status: 'cancelled' }
        }
        isLoading.value = false
        return { success: true, data: response.data }
      }
      
      isLoading.value = false
      return { success: false, message: response.message }
    } catch (err) {
      error.value = err.message || 'Không thể hủy đơn'
      isLoading.value = false
      return { success: false, message: err.message }
    }
  }

  /**
   * Update filters and refetch
   */
  const setFilters = async (newFilters) => {
    filters.value = { ...filters.value, ...newFilters }
    pagination.value.currentPage = 1
    await fetchApplications()
  }

  /**
   * Go to specific page
   */
  const goToPage = async (page) => {
    pagination.value.currentPage = page
    await fetchApplications({ page })
  }

  // Computed getters
  const getApplicationById = computed(() => (id) => {
    return applications.value.find(app => app.id === id)
  })

  const getApplicationsByUser = computed(() => (userId) => {
    return applications.value.filter(app => app.user_id === userId)
  })

  const getPendingApplications = computed(() => {
    return applications.value.filter(app => app.status === 'new' || app.status === 'pending')
  })

  const getTotalApprovedDays = computed(() => (userId) => {
    return applications.value
      .filter(app => app.user_id === userId && app.status === 'approved')
      .reduce((sum, app) => sum + (app.total_days || 0), 0)
  })

  return {
    // State
    applications,
    currentApplication,
    isLoading,
    error,
    pagination,
    filters,
    // Actions
    fetchApplications,
    fetchApplication,
    createApplication,
    updateApplication,
    deleteApplication,
    approveApplication,
    rejectApplication,
    cancelApplication,
    setFilters,
    goToPage,
    // Getters
    getApplicationById,
    getApplicationsByUser,
    getPendingApplications,
    getTotalApprovedDays
  }
})
