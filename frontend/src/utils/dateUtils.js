export const formatDate = (dateString) => {
  if (!dateString) return ''
  const date = new Date(dateString)
  return date.toLocaleDateString('vi-VN', {
    year: 'numeric',
    month: '2-digit',
    day: '2-digit'
  })
}

export const formatDateTime = (dateString) => {
  if (!dateString) return ''
  const date = new Date(dateString)
  return date.toLocaleString('vi-VN', {
    year: 'numeric',
    month: '2-digit',
    day: '2-digit',
    hour: '2-digit',
    minute: '2-digit'
  })
}

export const calculateDays = (startDate, endDate) => {
  if (!startDate || !endDate) return 0
  const start = new Date(startDate)
  const end = new Date(endDate)
  if (end < start) return 0
  const diffTime = end - start
  const diffDays = Math.floor(diffTime / (1000 * 60 * 60 * 24)) + 1
  return diffDays > 0 ? diffDays : 0
}

export const getMinDate = () => {
  const today = new Date()
  return today.toISOString().split('T')[0]
}

export const isDateValid = (date) => {
  return !isNaN(Date.parse(date))
}

export const getStatusColor = (status) => {
  const colors = {
    new: 'bg-yellow-100 text-yellow-800',
    pending: 'bg-yellow-100 text-yellow-800',
    approved: 'bg-green-100 text-green-800',
    rejected: 'bg-red-100 text-red-800',
    cancelled: 'bg-gray-100 text-gray-800'
  }
  return colors[status] || 'bg-gray-100 text-gray-800'
}

export const getStatusLabel = (status) => {
  const labels = {
    new: 'Mới',
    pending: 'Chờ duyệt',
    approved: 'Chấp nhận',
    rejected: 'Từ chối',
    cancelled: 'Đã hủy'
  }
  return labels[status] || status
}

export const getLeaveTypeLabel = (type) => {
  const labels = {
    annual: 'Nghỉ phép năm',
    sick: 'Nghỉ ốm',
    unpaid: 'Nghỉ không lương'
  }
  return labels[type] || type
}
