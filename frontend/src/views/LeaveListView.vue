<script setup>
import { useRouter } from "vue-router";
import { useAuthStore } from "../stores/auth";
import { useLeavesStore } from "../stores/leaves";
import { computed, ref, onMounted, watch } from "vue";
import {
  Plus,
  Eye,
  XCircle,
  Filter,
  ChevronLeft,
  ChevronRight,
  FileText,
  Calendar,
  User,
} from "lucide-vue-next";
import StatusBadge from "../components/StatusBadge.vue";
import Navbar from "../components/Navbar.vue";
import LoadingSpinner from "../components/LoadingSpinner.vue";
import Modal from "../components/Modal.vue";
import ConfirmModal from "../components/ConfirmModal.vue";
import { useToast } from "../composables/useToast";

const router = useRouter();
const authStore = useAuthStore();
const leavesStore = useLeavesStore();
const toast = useToast();

// State
const isInitialLoading = ref(true);
const statusFilter = ref("");
const showDetailModal = ref(false);
const showCancelModal = ref(false);
const selectedApplication = ref(null);
const cancelTargetId = ref(null);
const isCancelling = ref(false);

// Status filter options
const statusOptions = [
  { value: "", label: "All" },
  { value: "new", label: "New" },
  { value: "pending", label: "Pending" },
  { value: "approved", label: "Approved" },
  { value: "rejected", label: "Rejected" },
  { value: "cancelled", label: "Cancelled" },
];

// Leave type labels
const getLeaveTypeLabel = (type) => {
  const types = {
    annual: "Annual Leave",
    sick: "Sick Leave",
    unpaid: "Unpaid Leave",
  };
  return types[type] || type;
};

// USE BACKEND PAGINATION - Computed from store
const paginationData = computed(() => leavesStore.pagination);
const applications = computed(() => leavesStore.applications);

// Fetch applications with backend pagination
const fetchApplications = async () => {
  leavesStore.isLoading = true;
  
  try {
    await leavesStore.fetchApplications({
      status: statusFilter.value || undefined,
      page: paginationData.value.currentPage,
    });
    
    console.log('[LeaveList] Fetched applications:', {
      total: paginationData.value.total,
      page: paginationData.value.currentPage,
      lastPage: paginationData.value.lastPage,
      count: applications.value.length
    });
  } catch (error) {
    console.error('[LeaveList] Failed to fetch applications:', error);
    toast.error("Failed to load applications");
  } finally {
    leavesStore.isLoading = false;
  }
};

// Fetch data on mount
onMounted(async () => {
  isInitialLoading.value = true;
  await fetchApplications();
  isInitialLoading.value = false;
});

// Watch filter changes - refetch from backend
watch(statusFilter, async () => {
  leavesStore.pagination.currentPage = 1; // Reset to page 1
  await fetchApplications();
});

// Go to page - fetch from backend
const goToPage = async (page) => {
  if (page < 1 || page > paginationData.value.lastPage) return;
  leavesStore.pagination.currentPage = page;
  await fetchApplications();
};

// Check if can cancel
const canCancel = (app) => {
  if (!app) return false;
  return (
    (app.status === "new" || app.status === "pending") &&
    app.user_id === authStore.user?.id
  );
};

// Format date
const formatDate = (date) => {
  if (!date) return "—";
  try {
    return new Date(date).toLocaleDateString("en-US", {
      year: "numeric",
      month: "short",
      day: "numeric",
    });
  } catch {
    return date;
  }
};

// Format datetime (for created_at, updated_at)
const formatDateTime = (datetime) => {
  if (!datetime) return "—";
  try {
    return new Date(datetime).toLocaleDateString("en-US", {
      year: "numeric",
      month: "short",
      day: "numeric",
      hour: "2-digit",
      minute: "2-digit",
    });
  } catch {
    return datetime;
  }
};

// Get relative time (e.g., "2 hours ago")
const getRelativeTime = (datetime) => {
  if (!datetime) return "—";
  try {
    const date = new Date(datetime);
    const now = new Date();
    const diffMs = now - date;
    const diffMins = Math.floor(diffMs / 60000);
    const diffHours = Math.floor(diffMs / 3600000);
    const diffDays = Math.floor(diffMs / 86400000);
    
    if (diffMins < 1) return "Just now";
    if (diffMins < 60) return `${diffMins}m ago`;
    if (diffHours < 24) return `${diffHours}h ago`;
    if (diffDays < 7) return `${diffDays}d ago`;
    return formatDate(datetime);
  } catch {
    return datetime;
  }
};

// View application detail
const handleView = (app) => {
  selectedApplication.value = app;
  showDetailModal.value = true;
};

// Open cancel confirmation modal
const openCancelModal = (appId) => {
  cancelTargetId.value = appId;
  showCancelModal.value = true;
};

// Confirm cancel application
const confirmCancel = async () => {
  if (!cancelTargetId.value) return;

  isCancelling.value = true;

  try {
    const result = await leavesStore.cancelApplication(cancelTargetId.value);

    if (result.success) {
      toast.success("Request cancelled successfully");
      showCancelModal.value = false;
      showDetailModal.value = false;
      // Refetch to get updated data
      await fetchApplications();
    } else {
      toast.error(result.message || "Failed to cancel request");
    }
  } catch (error) {
    console.error('[LeaveList] Cancel error:', error);
    toast.error("An error occurred while cancelling");
  } finally {
    isCancelling.value = false;
    cancelTargetId.value = null;
  }
};

// Cancel from detail modal
const handleCancelFromDetail = () => {
  if (selectedApplication.value) {
    openCancelModal(selectedApplication.value.id);
  }
};

// Create new application
const handleCreateNew = () => {
  router.push("/leave/create");
};
</script>

<template>
  <div class="min-h-screen bg-gray-50">
    <Navbar />

    <!-- Header -->
    <div class="bg-white shadow-sm">
      <div class="max-w-7xl mx-auto px-4 py-4 sm:py-6">
        <div
          class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4"
        >
          <div>
            <h1 class="text-xl sm:text-2xl font-bold text-gray-900">
              My Leave Requests
            </h1>
            <p class="text-gray-600 text-sm mt-1">
              Manage your leave applications
            </p>
          </div>
          <button
            @click="handleCreateNew"
            class="inline-flex items-center justify-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2.5 px-5 rounded-xl transition"
          >
            <Plus class="w-5 h-5" />
            New Request
          </button>
        </div>
      </div>
    </div>

    <!-- Content -->
    <div class="max-w-7xl mx-auto px-4 py-6">
      <!-- Filters -->
      <div class="bg-white rounded-xl shadow-sm p-4 mb-6">
        <div class="flex flex-col sm:flex-row sm:items-center gap-4">
          <div class="flex items-center gap-3">
            <Filter class="w-5 h-5 text-gray-400" />
            <label class="text-sm font-medium text-gray-700">Status:</label>
          </div>
          <select
            v-model="statusFilter"
            class="flex-1 sm:flex-none sm:w-48 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent outline-none transition"
          >
            <option
              v-for="opt in statusOptions"
              :key="opt.value"
              :value="opt.value"
            >
              {{ opt.label }}
            </option>
          </select>
          <span class="text-sm text-gray-600">
            {{ paginationData.total }} request(s) total
          </span>
        </div>
      </div>

      <!-- Loading -->
      <div
        v-if="isInitialLoading || leavesStore.isLoading"
        class="bg-white rounded-xl shadow-sm p-12 flex justify-center"
      >
        <LoadingSpinner size="lg" />
      </div>

      <!-- Empty State -->
      <div
        v-else-if="applications.length === 0"
        class="bg-white rounded-xl shadow-sm p-12 text-center"
      >
        <div
          class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4"
        >
          <FileText class="w-8 h-8 text-gray-400" />
        </div>
        <h3 class="text-lg font-medium text-gray-900 mb-2">No requests found</h3>
        <p class="text-gray-500 mb-6">
          {{ statusFilter ? 'No requests with this status' : 'Start by creating your first leave request' }}
        </p>
        <button
          v-if="!statusFilter"
          @click="handleCreateNew"
          class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2.5 px-5 rounded-xl transition"
        >
          <Plus class="w-5 h-5" />
          New Request
        </button>
      </div>

      <!-- Applications List -->
      <div v-else class="bg-white rounded-xl shadow-sm overflow-hidden">
        <!-- Mobile Card View -->
        <div class="block sm:hidden divide-y divide-gray-100">
          <div v-for="app in applications" :key="app.id" class="p-4">
            <div class="flex items-start justify-between mb-3">
              <div>
                <p class="font-medium text-gray-900">
                  {{ getLeaveTypeLabel(app.type) }}
                </p>
                <p class="text-sm text-gray-500">
                  {{ formatDate(app.start_date) }} -
                  {{ formatDate(app.end_date) }}
                </p>
              </div>
              <StatusBadge :status="app.status" />
            </div>
            <div class="text-xs text-gray-500 mb-2 flex items-center gap-1">
              <Calendar class="w-3 h-3" />
              <span>Created {{ getRelativeTime(app.created_at) }}</span>
            </div>
            <p class="text-sm text-gray-600 mb-3 line-clamp-2">
              {{ app.reason }}
            </p>
            <div class="flex items-center justify-between">
              <span class="text-sm font-medium text-gray-900"
                >{{ app.total_days }} day(s)</span
              >
              <div class="flex items-center gap-2">
                <button
                  @click="handleView(app)"
                  class="p-2 text-gray-600 hover:bg-gray-100 rounded-lg transition"
                  title="View details"
                >
                  <Eye class="w-5 h-5" />
                </button>
                <button
                  v-if="canCancel(app)"
                  @click="openCancelModal(app.id)"
                  class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition"
                  title="Cancel"
                >
                  <XCircle class="w-5 h-5" />
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Desktop Table View -->
        <div class="hidden sm:block overflow-x-auto">
          <table class="w-full">
            <thead class="bg-gray-50 border-b border-gray-100">
              <tr>
                <th
                  class="px-6 py-3 text-left text-sm font-semibold text-gray-900"
                >
                  Type
                </th>
                <th
                  class="px-6 py-3 text-left text-sm font-semibold text-gray-900"
                >
                  Period
                </th>
                <th
                  class="px-6 py-3 text-left text-sm font-semibold text-gray-900"
                >
                  Days
                </th>
                <th
                  class="px-6 py-3 text-left text-sm font-semibold text-gray-900"
                >
                  Created
                </th>
                <th
                  class="px-6 py-3 text-left text-sm font-semibold text-gray-900"
                >
                  Reason
                </th>
                <th
                  class="px-6 py-3 text-left text-sm font-semibold text-gray-900"
                >
                  Status
                </th>
                <th
                  class="px-6 py-3 text-left text-sm font-semibold text-gray-900"
                >
                  Actions
                </th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
              <tr
                v-for="app in applications"
                :key="app.id"
                class="hover:bg-gray-50 transition"
              >
                <td class="px-6 py-4 text-sm font-medium text-gray-900">
                  {{ getLeaveTypeLabel(app.type) }}
                </td>
                <td class="px-6 py-4 text-sm text-gray-600">
                  {{ formatDate(app.start_date) }} -
                  {{ formatDate(app.end_date) }}
                </td>
                <td class="px-6 py-4 text-sm font-medium text-gray-900">
                  {{ app.total_days }}
                </td>
                <td class="px-6 py-4 text-sm text-gray-600">
                  <div>{{ formatDate(app.created_at) }}</div>
                  <div class="text-xs text-gray-400">{{ getRelativeTime(app.created_at) }}</div>
                </td>
                <td class="px-6 py-4 text-sm text-gray-600 max-w-xs truncate">
                  {{ app.reason }}
                </td>
                <td class="px-6 py-4 text-sm">
                  <StatusBadge :status="app.status" />
                </td>
                <td class="px-6 py-4 text-sm">
                  <div class="flex items-center gap-1">
                    <button
                      @click="handleView(app)"
                      class="p-2 text-gray-600 hover:bg-gray-100 rounded-lg transition"
                      title="View details"
                    >
                      <Eye class="w-5 h-5" />
                    </button>
                    <button
                      v-if="canCancel(app)"
                      @click="openCancelModal(app.id)"
                      class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition"
                      title="Cancel"
                    >
                      <XCircle class="w-5 h-5" />
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination - BACKEND PAGINATION -->
        <div
          v-if="paginationData.lastPage > 1"
          class="px-4 py-4 border-t border-gray-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4"
        >
          <p class="text-sm text-gray-600">
            Page {{ paginationData.currentPage }} of {{ paginationData.lastPage }}
            <span class="text-gray-400 ml-2">({{ paginationData.total }} total)</span>
          </p>
          <div class="flex items-center gap-2">
            <button
              @click="goToPage(paginationData.currentPage - 1)"
              :disabled="paginationData.currentPage === 1 || leavesStore.isLoading"
              class="p-2 border border-gray-300 rounded-lg hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed transition"
            >
              <ChevronLeft class="w-5 h-5" />
            </button>
            
            <!-- Page numbers -->
            <div class="flex items-center gap-1">
              <button
                v-for="page in Math.min(5, paginationData.lastPage)"
                :key="page"
                @click="goToPage(page)"
                :disabled="leavesStore.isLoading"
                :class="[
                  'px-3 py-1 rounded-lg text-sm font-medium transition',
                  page === paginationData.currentPage
                    ? 'bg-indigo-600 text-white'
                    : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
                ]"
              >
                {{ page }}
              </button>
            </div>
            
            <button
              @click="goToPage(paginationData.currentPage + 1)"
              :disabled="paginationData.currentPage === paginationData.lastPage || leavesStore.isLoading"
              class="p-2 border border-gray-300 rounded-lg hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed transition"
            >
              <ChevronRight class="w-5 h-5" />
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Detail Modal -->
    <Modal v-model="showDetailModal" title="Request Details" size="md">
      <div v-if="selectedApplication" class="space-y-4">
        <div class="flex items-center justify-between">
          <span class="text-gray-600">Status</span>
          <StatusBadge :status="selectedApplication.status" />
        </div>
        <div class="flex items-center justify-between">
          <span class="text-gray-600">Type</span>
          <span class="font-medium">{{
            getLeaveTypeLabel(selectedApplication.type)
          }}</span>
        </div>
        <div class="flex items-center justify-between">
          <span class="text-gray-600">Period</span>
          <span class="font-medium">
            {{ formatDate(selectedApplication.start_date) }} -
            {{ formatDate(selectedApplication.end_date) }}
          </span>
        </div>
        <div class="flex items-center justify-between">
          <span class="text-gray-600">Days</span>
          <span class="font-medium"
            >{{ selectedApplication.total_days }} day(s)</span
          >
        </div>
        <div>
          <span class="text-gray-600 block mb-2">Reason</span>
          <p class="bg-gray-50 p-3 rounded-lg text-gray-900">
            {{ selectedApplication.reason }}
          </p>
        </div>
      </div>
      <template #footer>
        <div class="flex justify-end gap-3">
          <button
            v-if="selectedApplication && canCancel(selectedApplication)"
            @click="handleCancelFromDetail"
            class="px-4 py-2 bg-red-50 hover:bg-red-100 text-red-700 font-medium rounded-lg transition"
          >
            Cancel Request
          </button>
          <button
            @click="showDetailModal = false"
            class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-900 font-medium rounded-lg transition"
          >
            Close
          </button>
        </div>
      </template>
    </Modal>

    <!-- Cancel Confirmation Modal -->
    <ConfirmModal
      v-model="showCancelModal"
      type="danger"
      title="Cancel Leave Request?"
      message="Are you sure you want to cancel this request? This action cannot be undone."
      confirmText="Yes, Cancel"
      cancelText="No, Keep"
      :loading="isCancelling"
      @confirm="confirmCancel"
      @cancel="showCancelModal = false"
    />
  </div>
</template>
