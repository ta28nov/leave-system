<script setup>
import { ref, computed, onMounted, watch } from "vue";
import { useAuthStore } from "../stores/auth";
import { useLeavesStore } from "../stores/leaves";
import {
  CheckCircle,
  XCircle,
  Eye,
  Filter,
  ChevronLeft,
  ChevronRight,
  Inbox,
  Calendar,
} from "lucide-vue-next";
import StatusBadge from "../components/StatusBadge.vue";
import Navbar from "../components/Navbar.vue";
import LoadingSpinner from "../components/LoadingSpinner.vue";
import Modal from "../components/Modal.vue";
import ConfirmModal from "../components/ConfirmModal.vue";
import { useToast } from "../composables/useToast";

const authStore = useAuthStore();
const leavesStore = useLeavesStore();
const toast = useToast();

// State
const isInitialLoading = ref(true);
const statusFilter = ref("pending");
const showDetailModal = ref(false);
const showApproveModal = ref(false);
const showRejectModal = ref(false);
const selectedApplication = ref(null);
const rejectReason = ref("");
const isProcessing = ref(false);

// USE BACKEND PAGINATION
const paginationData = computed(() => leavesStore.pagination);
const applications = computed(() => leavesStore.applications);

// Fetch applications with backend filtering
const fetchApplications = async () => {
  leavesStore.isLoading = true;
  
  try {
    const params = {
      page: paginationData.value.currentPage,
    };
    
    // Only add filter if not "all"
    if (statusFilter.value === "pending") {
      // Backend doesn't have "pending" status exactly, it has "new" and "pending"
      // We'll fetch all and filter client-side for now, or handle in backend
      // For now, fetch all and we'll handle this properly
    }
    
    await leavesStore.fetchApplications(params);
    
    console.log('[ManagerApproval] Fetched applications:', {
      total: paginationData.value.total,
      page: paginationData.value.currentPage,
      lastPage: paginationData.value.lastPage,
      count: applications.value.length
    });
  } catch (error) {
    console.error('[ManagerApproval] Failed to fetch:', error);
    toast.error("Failed to load applications");
  } finally {
    leavesStore.isLoading = false;
  }
};

// Fetch on mount
onMounted(async () => {
  isInitialLoading.value = true;
  await fetchApplications();
  isInitialLoading.value = false;
});

// Watch filter changes
watch(statusFilter, async () => {
  leavesStore.pagination.currentPage = 1;
  await fetchApplications();
});

// Go to page
const goToPage = async (page) => {
  if (page < 1 || page > paginationData.value.lastPage) return;
  leavesStore.pagination.currentPage = page;
  await fetchApplications();
};

// Filter applications (client-side for now since backend auto-filters by user_id for Employee)
// Manager/Admin sees all, so we need to exclude own applications
const filteredApplications = computed(() => {
  let filtered = applications.value.filter(
    (app) => app.user_id !== authStore.user?.id
  );

  if (statusFilter.value === "pending") {
    filtered = filtered.filter(
      (app) => app.status === "new" || app.status === "pending"
    );
  }

  return filtered.sort(
    (a, b) =>
      new Date(b.created_at || b.start_date) -
      new Date(a.created_at || a.start_date)
  );
});

// Stats
const pendingCount = computed(() => {
  return filteredApplications.value.filter(
    (app) => app.status === "new" || app.status === "pending"
  ).length;
});

// Helper functions
const canApprove = (status) => status === "new" || status === "pending";

const getLeaveTypeLabel = (type) => {
  const types = {
    annual: "Annual Leave",
    sick: "Sick Leave",
    unpaid: "Unpaid Leave",
  };
  return types[type] || type;
};

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

// Get user name from relationship
const getUserName = (app) => {
  return app.user?.name || app.user_name || 'Employee';
};

// View detail
const handleView = (app) => {
  selectedApplication.value = app;
  showDetailModal.value = true;
};

// Open approve modal
const openApproveModal = (app) => {
  selectedApplication.value = app;
  showApproveModal.value = true;
};

// Confirm approve
const confirmApprove = async () => {
  if (!selectedApplication.value) return;
  
  const appId = selectedApplication.value.id;
  const userName = getUserName(selectedApplication.value);

  isProcessing.value = true;

  try {
    console.log('[ManagerApproval] Approving application:', appId);
    const result = await leavesStore.approveApplication(appId);

    if (result.success) {
      console.log('[ManagerApproval] Approve success');
      showApproveModal.value = false;
      showDetailModal.value = false;
      selectedApplication.value = null;
      
      toast.success(`Approved request from ${userName}`);
      
      // Refetch to get updated data
      await fetchApplications();
    } else {
      console.error('[ManagerApproval] Approve failed:', result.message);
      toast.error(result.message || "Failed to approve request");
    }
  } catch (error) {
    console.error('[ManagerApproval] Approve error:', error);
    toast.error("An error occurred while approving");
  } finally {
    isProcessing.value = false;
  }
};

// Open reject modal
const openRejectModal = (app) => {
  selectedApplication.value = app;
  rejectReason.value = "";
  showRejectModal.value = true;
};

// Confirm reject
const confirmReject = async () => {
  if (!selectedApplication.value) return;
  
  const appId = selectedApplication.value.id;
  const userName = getUserName(selectedApplication.value);
  const reason = rejectReason.value;

  isProcessing.value = true;

  try {
    console.log('[ManagerApproval] Rejecting application:', appId, 'Reason:', reason);
    const result = await leavesStore.rejectApplication(appId, reason);

    if (result.success) {
      console.log('[ManagerApproval] Reject success');
      showRejectModal.value = false;
      showDetailModal.value = false;
      selectedApplication.value = null;
      rejectReason.value = "";
      
      toast.success(`Rejected request from ${userName}`);
      
      // Refetch to get updated data
      await fetchApplications();
    } else {
      console.error('[ManagerApproval] Reject failed:', result.message);
      toast.error(result.message || "Failed to reject request");
    }
  } catch (error) {
    console.error('[ManagerApproval] Reject error:', error);
    toast.error("An error occurred while rejecting");
  } finally {
    isProcessing.value = false;
  }
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
              Approve Leave Requests
            </h1>
            <p class="text-gray-600 text-sm mt-1">
              <span class="font-medium text-indigo-600">{{ pendingCount }}</span>
              pending request(s)
            </p>
          </div>
        </div>
      </div>
    </div>

    <!-- Content -->
    <div class="max-w-7xl mx-auto px-4 py-6">
      <!-- Filters -->\n      <div class="bg-white rounded-xl shadow-sm p-4 mb-6">
        <div class="flex flex-col sm:flex-row sm:items-center gap-4">
          <div class="flex items-center gap-3">
            <Filter class="w-5 h-5 text-gray-400" />
            <span class="text-sm font-medium text-gray-700">Show:</span>
          </div>
          <div class="flex gap-2">
            <button
              @click="statusFilter = 'pending'"
              :class="[
                'px-4 py-2 rounded-lg font-medium text-sm transition',
                statusFilter === 'pending'
                  ? 'bg-indigo-600 text-white'
                  : 'bg-gray-100 text-gray-700 hover:bg-gray-200',
              ]"
            >
              Pending
            </button>
            <button
              @click="statusFilter = 'all'"
              :class="[
                'px-4 py-2 rounded-lg font-medium text-sm transition',
                statusFilter === 'all'
                  ? 'bg-indigo-600 text-white'
                 : 'bg-gray-100 text-gray-700 hover:bg-gray-200',
              ]"
            >
              All
            </button>
          </div>
          <span class="text-sm text-gray-600 sm:ml-auto">
            {{ filteredApplications.length }} request(s)
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
        v-else-if="filteredApplications.length === 0"
        class="bg-white rounded-xl shadow-sm p-12 text-center"
      >
        <div
          class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4"
        >
          <Inbox class="w-8 h-8 text-green-600" />
        </div>
        <h3 class="text-lg font-medium text-gray-900 mb-2">No requests found</h3>
        <p class="text-gray-500">
          {{
            statusFilter === "pending"
              ? "All requests have been processed"
              : "No leave requests yet"
          }}
        </p>
      </div>

      <!-- Applications List -->
      <div v-else class="bg-white rounded-xl shadow-sm overflow-hidden">
        <!-- Mobile Card View -->
        <div class="block sm:hidden divide-y divide-gray-100">
          <div
            v-for="app in filteredApplications"
            :key="app.id"
            class="p-4"
          >
            <div class="flex items-start justify-between mb-3">
              <div>
                <p class="font-medium text-gray-900">{{ getUserName(app) }}</p>
                <p class="text-sm text-gray-500">{{ getLeaveTypeLabel(app.type) }}</p>
              </div>
              <StatusBadge :status="app.status" />
            </div>
            <div class="text-xs text-gray-500 mb-2 flex items-center gap-1">
              <Calendar class="w-3 h-3" />
              <span>Created {{ getRelativeTime(app.created_at) }}</span>
            </div>
            <div class="text-sm text-gray-600 mb-3">
              <p>
                {{ formatDate(app.start_date) }} - {{ formatDate(app.end_date) }}
                ({{ app.total_days }} day(s))
              </p>
              <p class="mt-1 line-clamp-2">{{ app.reason }}</p>
            </div>
            <div class="flex items-center justify-end gap-2">
              <button
                @click="handleView(app)"
                class="p-2 text-gray-600 hover:bg-gray-100 rounded-lg transition"
                title="View details"
              >
                <Eye class="w-5 h-5" />
              </button>
              <template v-if="canApprove(app.status)">
                <button
                  @click="openApproveModal(app)"
                  :disabled="isProcessing"
                  class="p-2 text-green-600 hover:bg-green-50 rounded-lg transition disabled:opacity-50"
                  title="Approve"
                >
                  <CheckCircle class="w-5 h-5" />
                </button>
                <button
                  @click="openRejectModal(app)"
                  :disabled="isProcessing"
                  class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition disabled:opacity-50"
                  title="Reject"
                >
                  <XCircle class="w-5 h-5" />
                </button>
              </template>
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
                  Employee
                </th>
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
                v-for="app in filteredApplications"
                :key="app.id"
                class="hover:bg-gray-50 transition"
              >
                <td class="px-6 py-4 text-sm font-medium text-gray-900">
                  {{ getUserName(app) }}
                </td>
                <td class="px-6 py-4 text-sm text-gray-600">
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
                    <template v-if="canApprove(app.status)">
                      <button
                        @click="openApproveModal(app)"
                        :disabled="isProcessing"
                        class="p-2 text-green-600 hover:bg-green-50 rounded-lg transition disabled:opacity-50"
                        title="Approve"
                      >
                        <CheckCircle class="w-5 h-5" />
                      </button>
                      <button
                        @click="openRejectModal(app)"
                        :disabled="isProcessing"
                        class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition disabled:opacity-50"
                        title="Reject"
                      >
                        <XCircle class="w-5 h-5" />
                      </button>
                    </template>
                    <span v-else class="text-gray-400 text-xs px-2">—</span>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Detail Modal -->
    <Modal v-model="showDetailModal" title="Request Details" size="md">
      <div v-if="selectedApplication" class="space-y-4">
        <div class="flex items-center justify-between">
          <span class="text-gray-600">Employee</span>
          <span class="font-medium">{{ getUserName(selectedApplication) }}</span>
        </div>
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
        <div class="flex items-center justify-between">
          <span class="text-gray-600">Created</span>
          <span class="text-sm">{{ formatDateTime(selectedApplication.created_at) }}</span>
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
          <template
            v-if="selectedApplication && canApprove(selectedApplication.status)"
          >
            <button
              @click="openRejectModal(selectedApplication)"
              :disabled="isProcessing"
              class="px-4 py-2 bg-red-50 hover:bg-red-100 text-red-700 font-medium rounded-lg transition disabled:opacity-50"
            >
              Reject
            </button>
            <button
              @click="openApproveModal(selectedApplication)"
              :disabled="isProcessing"
              class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg transition disabled:opacity-50"
            >
              Approve
            </button>
          </template>
          <button
            @click="showDetailModal = false"
            class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-900 font-medium rounded-lg transition"
          >
            Close
          </button>
        </div>
      </template>
    </Modal>

    <!-- Approve Confirmation Modal -->
    <ConfirmModal
      v-model="showApproveModal"
      type="success"
      title="Approve Request?"
      :message="`Are you sure you want to approve the leave request from ${selectedApplication ? getUserName(selectedApplication) : 'this employee'}?`"
      confirmText="Yes, Approve"
      cancelText="Cancel"
      :loading="isProcessing"
      @confirm="confirmApprove"
      @cancel="showApproveModal = false"
    />

    <!-- Reject Modal with Reason -->
    <Modal v-model="showRejectModal" title="Reject Request" size="sm">
      <div class="space-y-4">
        <p class="text-gray-600">
          Are you sure you want to reject the request from
          <span class="font-medium">{{ selectedApplication ? getUserName(selectedApplication) : 'this employee' }}</span>?
        </p>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2"
            >Rejection reason (optional)</label
          >
          <textarea
            v-model="rejectReason"
            rows="3"
            :disabled="isProcessing"
            placeholder="Enter reason for rejection..."
            class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-red-500 focus:border-transparent outline-none transition resize-none disabled:bg-gray-100"
          ></textarea>
        </div>
      </div>
      <template #footer>
        <div class="flex justify-end gap-3">
          <button
            @click="showRejectModal = false"
            :disabled="isProcessing"
            class="px-4 py-2 bg-gray-100 hover:bg-gray-200 disabled:opacity-50 text-gray-900 font-medium rounded-lg transition"
          >
            Cancel
          </button>
          <button
            @click="confirmReject"
            :disabled="isProcessing"
            class="px-4 py-2 bg-red-600 hover:bg-red-700 disabled:bg-red-400 text-white font-medium rounded-lg transition flex items-center gap-2"
          >
            <LoadingSpinner v-if="isProcessing" size="sm" color="white" />
            Confirm Reject
          </button>
        </div>
      </template>
    </Modal>
  </div>
</template>
