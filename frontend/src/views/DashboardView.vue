<script setup>
import { computed, onMounted, ref } from "vue";
import { useRouter } from "vue-router";
import { useAuthStore } from "../stores/auth";
import { useLeavesStore } from "../stores/leaves";
import {
  Plus,
  Calendar,
  CheckCircle,
  Clock,
  AlertCircle,
  ArrowRight,
} from "lucide-vue-next";
import StatusBadge from "../components/StatusBadge.vue";
import Navbar from "../components/Navbar.vue";
import LoadingSpinner from "../components/LoadingSpinner.vue";
import { useToast } from "../composables/useToast";

const router = useRouter();
const authStore = useAuthStore();
const leavesStore = useLeavesStore();
const toast = useToast();

const isInitialLoading = ref(true);

// Fetch data on mount
onMounted(async () => {
  try {
    await leavesStore.fetchApplications({ user_id: authStore.user?.id });
  } catch (error) {
    toast.error("Failed to load data. Please try again.");
  } finally {
    isInitialLoading.value = false;
  }
});

// User's applications
const userApplications = computed(() => {
  if (!authStore.user) return [];
  return leavesStore.applications
    .filter((app) => app.user_id === authStore.user.id)
    .sort(
      (a, b) =>
        new Date(b.created_at || b.start_date) -
        new Date(a.created_at || a.start_date)
    )
    .slice(0, 5);
});

// Stats
const totalAllowance = 12;
const usedDays = computed(() => {
  if (!authStore.user) return 0;
  return leavesStore.applications
    .filter(
      (app) => app.user_id === authStore.user.id && app.status === "approved"
    )
    .reduce((sum, app) => sum + (app.total_days || 0), 0);
});
const remainingDays = computed(() => totalAllowance - usedDays.value);
const pendingCount = computed(() => {
  if (!authStore.user) return 0;
  return leavesStore.applications.filter(
    (app) =>
      app.user_id === authStore.user.id &&
      (app.status === "new" || app.status === "pending")
  ).length;
});

const stats = computed(() => [
  {
    label: "Annual Leave",
    value: totalAllowance,
    icon: Calendar,
    color: "indigo",
    bgColor: "bg-indigo-50",
    textColor: "text-indigo-600",
  },
  {
    label: "Used",
    value: usedDays.value,
    icon: CheckCircle,
    color: "green",
    bgColor: "bg-green-50",
    textColor: "text-green-600",
  },
  {
    label: "Remaining",
    value: remainingDays.value,
    icon: Clock,
    color: "amber",
    bgColor: "bg-amber-50",
    textColor: "text-amber-600",
  },
  {
    label: "Pending",
    value: pendingCount.value,
    icon: AlertCircle,
    color: "rose",
    bgColor: "bg-rose-50",
    textColor: "text-rose-600",
  },
]);

const formatDate = (date) => {
  return new Date(date).toLocaleDateString("en-US", {
    year: "numeric",
    month: "short",
    day: "numeric",
  });
};

const getLeaveTypeLabel = (type) => {
  const types = {
    annual: "Annual Leave",
    sick: "Sick Leave",
    unpaid: "Unpaid Leave",
  };
  return types[type] || type;
};

const handleCreateRequest = () => {
  router.push("/leave/create");
};

const handleViewAll = () => {
  router.push("/leave/list");
};
</script>

<template>
  <div class="min-h-screen bg-gray-50">
    <Navbar />

    <!-- Loading State -->
    <div v-if="isInitialLoading" class="flex items-center justify-center py-20">
      <LoadingSpinner size="lg" />
    </div>

    <div v-else class="max-w-7xl mx-auto px-4 py-6 sm:py-8">
      <!-- Page Header -->
      <div class="mb-6 sm:mb-8">
        <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Dashboard</h1>
        <p class="text-gray-600 mt-1">
          Welcome back, <span class="font-medium">{{ authStore.user?.name }}</span>
        </p>
      </div>

      <!-- Stats Grid - Responsive -->
      <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 mb-6 sm:mb-8">
        <div
          v-for="stat in stats"
          :key="stat.label"
          class="bg-white rounded-xl shadow-sm p-4 sm:p-6 hover:shadow-md transition"
        >
          <div class="flex items-start justify-between">
            <div>
              <p class="text-gray-600 text-xs sm:text-sm font-medium truncate">
                {{ stat.label }}
              </p>
              <p
                class="text-2xl sm:text-3xl font-bold mt-1 sm:mt-2"
                :class="stat.textColor"
              >
                {{ stat.value }}
              </p>
            </div>
            <div
              class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl flex items-center justify-center flex-shrink-0"
              :class="stat.bgColor"
            >
              <component
                :is="stat.icon"
                class="w-5 h-5 sm:w-6 sm:h-6"
                :class="stat.textColor"
              />
            </div>
          </div>
        </div>
      </div>

      <!-- Quick Actions -->
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6 sm:mb-8">
        <button
          @click="handleCreateRequest"
          class="flex items-center justify-center gap-3 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-4 px-6 rounded-xl transition shadow-lg shadow-indigo-200"
        >
          <Plus class="w-5 h-5" />
          New Leave Request
        </button>
        <button
          @click="handleViewAll"
          class="flex items-center justify-center gap-3 bg-white hover:bg-gray-50 text-gray-900 font-semibold py-4 px-6 rounded-xl transition border border-gray-200"
        >
          View All Requests
          <ArrowRight class="w-5 h-5" />
        </button>
      </div>

      <!-- Recent Requests Section -->
      <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <!-- Section Header -->
        <div
          class="flex items-center justify-between p-4 sm:p-6 border-b border-gray-100"
        >
          <h2 class="text-lg sm:text-xl font-bold text-gray-900">
            Recent Requests
          </h2>
          <button
            @click="handleViewAll"
            class="text-indigo-600 hover:text-indigo-700 text-sm font-medium flex items-center gap-1"
          >
            View all
            <ArrowRight class="w-4 h-4" />
          </button>
        </div>

        <!-- Loading -->
        <div v-if="leavesStore.isLoading" class="p-8 flex justify-center">
          <LoadingSpinner />
        </div>

        <!-- Empty State -->
        <div v-else-if="userApplications.length === 0" class="p-8 text-center">
          <Calendar class="w-12 h-12 text-gray-300 mx-auto mb-3" />
          <p class="text-gray-500">You don't have any leave requests yet</p>
          <button
            @click="handleCreateRequest"
            class="mt-4 text-indigo-600 hover:text-indigo-700 font-medium"
          >
            Create your first request →
          </button>
        </div>

        <!-- Mobile Card View -->
        <div class="block sm:hidden divide-y divide-gray-100">
          <div v-for="app in userApplications" :key="app.id" class="p-4">
            <div class="flex items-start justify-between mb-2">
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
            <div class="flex items-center justify-between text-sm">
              <span class="text-gray-600">{{ app.total_days }} day(s)</span>
              <span class="text-gray-500 truncate max-w-[150px]">{{
                app.reason
              }}</span>
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
                  Period
                </th>
                <th
                  class="px-6 py-3 text-left text-sm font-semibold text-gray-900"
                >
                  Type
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
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
              <tr
                v-for="app in userApplications"
                :key="app.id"
                class="hover:bg-gray-50 transition"
              >
                <td class="px-6 py-4 text-sm text-gray-900">
                  {{ formatDate(app.start_date) }} -
                  {{ formatDate(app.end_date) }}
                </td>
                <td class="px-6 py-4 text-sm text-gray-600">
                  {{ getLeaveTypeLabel(app.type) }}
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
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</template>
