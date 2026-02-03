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

// Fetch all applications
onMounted(async () => {
  try {
    await leavesStore.fetchApplications();
  } catch (error) {
    toast.error("Failed to load applications");
  } finally {
    isInitialLoading.value = false;
  }
});

// Filtered applications (exclude own applications)
const filteredApplications = computed(() => {
  let filtered = leavesStore.applications.filter(
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

// Pagination
const currentPage = ref(1);
const itemsPerPage = 10;

const paginatedApplications = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage;
  const end = start + itemsPerPage;
  return filteredApplications.value.slice(start, end);
});

const totalPages = computed(() => {
  return Math.ceil(filteredApplications.value.length / itemsPerPage) || 1;
});

// Reset page when filter changes
watch(statusFilter, () => {
  currentPage.value = 1;
});

// Stats
const pendingCount = computed(() => {
  return leavesStore.applications.filter(
    (app) =>
      app.user_id !== authStore.user?.id &&
      (app.status === "new" || app.status === "pending")
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
  
  // Store the ID and name before closing modals
  const appId = selectedApplication.value.id;
  const userName = selectedApplication.value.user_name || 'employee';

  isProcessing.value = true;

  try {
    const result = await leavesStore.approveApplication(appId);

    if (result.success) {
      // Close modals first, then show toast
      showApproveModal.value = false;
      showDetailModal.value = false;
      selectedApplication.value = null;
      
      // Use nextTick to ensure DOM is updated before toast
      await new Promise(resolve => setTimeout(resolve, 100));
      toast.success(`Approved request from ${userName}`);
    } else {
      toast.error(result.message || "Failed to approve request");
    }
  } catch (error) {
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
  
  // Store the ID and name before closing modals
  const appId = selectedApplication.value.id;
  const userName = selectedApplication.value.user_name || 'employee';
  const reason = rejectReason.value;

  isProcessing.value = true;

  try {
    const result = await leavesStore.rejectApplication(appId, reason);

    if (result.success) {
      // Close modals first, then show toast
      showRejectModal.value = false;
      showDetailModal.value = false;
      selectedApplication.value = null;
      rejectReason.value = "";
      
      // Use nextTick to ensure DOM is updated before toast
      await new Promise(resolve => setTimeout(resolve, 100));
      toast.success(`Rejected request from ${userName}`);
    } else {
      toast.error(result.message || "Failed to reject request");
    }
  } catch (error) {
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
      <!-- Filters -->
      <div class="bg-white rounded-xl shadow-sm p-4 mb-6">
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
        v-if="isInitialLoading"
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
            v-for="app in paginatedApplications"
            :key="app.id"
            class="p-4"
          >
            <div class="flex items-start justify-between mb-3">
              <div>
                <p class="font-medium text-gray-900">{{ app.user_name || 'Employee' }}</p>
                <p class="text-sm text-gray-500">{{ getLeaveTypeLabel(app.type) }}</p>
              </div>
              <StatusBadge :status="app.status" />
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
                  class="p-2 text-green-600 hover:bg-green-50 rounded-lg transition"
                  title="Approve"
                >
                  <CheckCircle class="w-5 h-5" />
                </button>
                <button
                  @click="openRejectModal(app)"
                  class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition"
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
                v-for="app in paginatedApplications"
                :key="app.id"
                class="hover:bg-gray-50 transition"
              >
                <td class="px-6 py-4 text-sm font-medium text-gray-900">
                  {{ app.user_name || 'Employee' }}
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
                        class="p-2 text-green-600 hover:bg-green-50 rounded-lg transition"
                        title="Approve"
                      >
                        <CheckCircle class="w-5 h-5" />
                      </button>
                      <button
                        @click="openRejectModal(app)"
                        class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition"
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

        <!-- Pagination -->
        <div
          v-if="totalPages > 1"
          class="px-4 py-4 border-t border-gray-100 flex items-center justify-between"
        >
          <p class="text-sm text-gray-600">
            Page {{ currentPage }} of {{ totalPages }}
          </p>
          <div class="flex items-center gap-2">
            <button
              @click="currentPage = Math.max(1, currentPage - 1)"
              :disabled="currentPage === 1"
              class="p-2 border border-gray-300 rounded-lg hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed transition"
            >
              <ChevronLeft class="w-5 h-5" />
            </button>
            <button
              @click="currentPage = Math.min(totalPages, currentPage + 1)"
              :disabled="currentPage === totalPages"
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
          <span class="text-gray-600">Employee</span>
          <span class="font-medium">{{ selectedApplication.user_name || 'Employee' }}</span>
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
              class="px-4 py-2 bg-red-50 hover:bg-red-100 text-red-700 font-medium rounded-lg transition"
            >
              Reject
            </button>
            <button
              @click="openApproveModal(selectedApplication)"
              class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg transition"
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
      :message="`Are you sure you want to approve the leave request from ${selectedApplication?.user_name || 'this employee'}?`"
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
          <span class="font-medium">{{ selectedApplication?.user_name || 'this employee' }}</span>?
        </p>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2"
            >Rejection reason (optional)</label
          >
          <textarea
            v-model="rejectReason"
            rows="3"
            placeholder="Enter reason for rejection..."
            class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-red-500 focus:border-transparent outline-none transition resize-none"
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
