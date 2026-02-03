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

// Fetch data on mount
onMounted(async () => {
  try {
    await leavesStore.fetchApplications({ user_id: authStore.user?.id });
  } catch (error) {
    toast.error("Failed to load applications");
  } finally {
    isInitialLoading.value = false;
  }
});

// Filtered and sorted applications
const filteredApplications = computed(() => {
  if (!authStore.user?.id) return [];
  
  let filtered = leavesStore.applications.filter(
    (app) => app.user_id === authStore.user.id
  );

  if (statusFilter.value) {
    filtered = filtered.filter((app) => app.status === statusFilter.value);
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
    } else {
      toast.error(result.message || "Failed to cancel request");
    }
  } catch (error) {
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
          <div v-for="app in paginatedApplications" :key="app.id" class="p-4">
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
