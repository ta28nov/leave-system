<script setup>
import { ref, computed, onMounted } from "vue";
import { useAuthStore } from "../stores/auth";
import { useLeavesStore } from "../stores/leaves";
import { 
  Users, FileText, CheckCircle, XCircle, Clock, 
  TrendingUp, Calendar, BarChart3 
} from "lucide-vue-next";
import StatusBadge from "../components/StatusBadge.vue";
import Navbar from "../components/Navbar.vue";
import LoadingSpinner from "../components/LoadingSpinner.vue";

const authStore = useAuthStore();
const leavesStore = useLeavesStore();

const isInitialLoading = ref(true);

onMounted(async () => {
  await leavesStore.fetchApplications();
  isInitialLoading.value = false;
});

// Stats computations
const stats = computed(() => {
  const apps = leavesStore.applications;
  return {
    total: apps.length,
    approved: apps.filter(a => a.status === "approved").length,
    pending: apps.filter(a => a.status === "new" || a.status === "pending").length,
    rejected: apps.filter(a => a.status === "rejected").length
  };
});

// Recent applications (last 10)
const recentApplications = computed(() => {
  return [...leavesStore.applications]
    .sort((a, b) => new Date(b.created_at || b.start_date) - new Date(a.created_at || a.start_date))
    .slice(0, 10);
});

// Applications by type
const applicationsByType = computed(() => {
  const apps = leavesStore.applications;
  return {
    annual: apps.filter(a => a.type === "annual").length,
    sick: apps.filter(a => a.type === "sick").length,
    unpaid: apps.filter(a => a.type === "unpaid").length
  };
});

// Approval rate
const approvalRate = computed(() => {
  const decided = stats.value.approved + stats.value.rejected;
  if (decided === 0) return 0;
  return Math.round((stats.value.approved / decided) * 100);
});

const formatDate = (date) => {
  return new Date(date).toLocaleDateString("en-US", {
    year: "numeric",
    month: "short",
    day: "numeric"
  });
};

const getLeaveTypeLabel = (type) => {
  const types = { annual: "Annual", sick: "Sick", unpaid: "Unpaid" };
  return types[type] || type;
};
</script>

<template>
  <div class="min-h-screen bg-gray-50">
    <Navbar />

    <!-- Loading -->
    <div v-if="isInitialLoading" class="flex items-center justify-center py-20">
      <LoadingSpinner size="lg" />
    </div>

    <div v-else class="max-w-7xl mx-auto px-4 py-6 sm:py-8">
      <!-- Page Header -->
      <div class="mb-6 sm:mb-8">
        <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Admin Dashboard</h1>
        <p class="text-gray-600 mt-1">Leave management system overview</p>
      </div>

      <!-- Stats Grid -->
      <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <!-- Total Applications -->
        <div class="bg-white rounded-xl shadow-sm p-5 hover:shadow-md transition">
          <div class="flex items-start justify-between">
            <div>
              <p class="text-gray-600 text-sm font-medium">Total Requests</p>
              <p class="text-3xl font-bold text-gray-900 mt-2">{{ stats.total }}</p>
            </div>
            <div class="w-12 h-12 bg-indigo-50 rounded-xl flex items-center justify-center">
              <FileText class="w-6 h-6 text-indigo-600" />
            </div>
          </div>
        </div>

        <!-- Approved -->
        <div class="bg-white rounded-xl shadow-sm p-5 hover:shadow-md transition">
          <div class="flex items-start justify-between">
            <div>
              <p class="text-gray-600 text-sm font-medium">Approved</p>
              <p class="text-3xl font-bold text-green-600 mt-2">{{ stats.approved }}</p>
            </div>
            <div class="w-12 h-12 bg-green-50 rounded-xl flex items-center justify-center">
              <CheckCircle class="w-6 h-6 text-green-600" />
            </div>
          </div>
        </div>

        <!-- Pending -->
        <div class="bg-white rounded-xl shadow-sm p-5 hover:shadow-md transition">
          <div class="flex items-start justify-between">
            <div>
              <p class="text-gray-600 text-sm font-medium">Pending</p>
              <p class="text-3xl font-bold text-amber-600 mt-2">{{ stats.pending }}</p>
            </div>
            <div class="w-12 h-12 bg-amber-50 rounded-xl flex items-center justify-center">
              <Clock class="w-6 h-6 text-amber-600" />
            </div>
          </div>
        </div>

        <!-- Rejected -->
        <div class="bg-white rounded-xl shadow-sm p-5 hover:shadow-md transition">
          <div class="flex items-start justify-between">
            <div>
              <p class="text-gray-600 text-sm font-medium">Rejected</p>
              <p class="text-3xl font-bold text-red-600 mt-2">{{ stats.rejected }}</p>
            </div>
            <div class="w-12 h-12 bg-red-50 rounded-xl flex items-center justify-center">
              <XCircle class="w-6 h-6 text-red-600" />
            </div>
          </div>
        </div>
      </div>

      <!-- Charts Row -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
        <!-- Approval Rate -->
        <div class="bg-white rounded-xl shadow-sm p-6">
          <div class="flex items-center gap-3 mb-4">
            <TrendingUp class="w-5 h-5 text-gray-400" />
            <h3 class="font-semibold text-gray-900">Approval Rate</h3>
          </div>
          <div class="flex items-center gap-4">
            <div class="relative w-24 h-24">
              <svg class="w-24 h-24 transform -rotate-90">
                <circle
                  cx="48" cy="48" r="40"
                  stroke-width="8"
                  fill="none"
                  class="stroke-gray-200"
                />
                <circle
                  cx="48" cy="48" r="40"
                  stroke-width="8"
                  fill="none"
                  class="stroke-green-500"
                  :stroke-dasharray="251.2"
                  :stroke-dashoffset="251.2 - (251.2 * approvalRate / 100)"
                  stroke-linecap="round"
                />
              </svg>
              <span class="absolute inset-0 flex items-center justify-center text-2xl font-bold text-gray-900">
                {{ approvalRate }}%
              </span>
            </div>
            <div class="text-sm text-gray-600">
              <p><span class="font-medium text-green-600">{{ stats.approved }}</span> approved</p>
              <p><span class="font-medium text-red-600">{{ stats.rejected }}</span> rejected</p>
            </div>
          </div>
        </div>

        <!-- By Type -->
        <div class="bg-white rounded-xl shadow-sm p-6">
          <div class="flex items-center gap-3 mb-4">
            <BarChart3 class="w-5 h-5 text-gray-400" />
            <h3 class="font-semibold text-gray-900">By Leave Type</h3>
          </div>
          <div class="space-y-4">
            <div>
              <div class="flex items-center justify-between mb-1">
                <span class="text-sm text-gray-600">Annual Leave</span>
                <span class="text-sm font-medium">{{ applicationsByType.annual }}</span>
              </div>
              <div class="w-full bg-gray-200 rounded-full h-2">
                <div 
                  class="bg-indigo-500 h-2 rounded-full" 
                  :style="{ width: stats.total ? `${(applicationsByType.annual / stats.total) * 100}%` : '0%' }"
                />
              </div>
            </div>
            <div>
              <div class="flex items-center justify-between mb-1">
                <span class="text-sm text-gray-600">Sick Leave</span>
                <span class="text-sm font-medium">{{ applicationsByType.sick }}</span>
              </div>
              <div class="w-full bg-gray-200 rounded-full h-2">
                <div 
                  class="bg-amber-500 h-2 rounded-full" 
                  :style="{ width: stats.total ? `${(applicationsByType.sick / stats.total) * 100}%` : '0%' }"
                />
              </div>
            </div>
            <div>
              <div class="flex items-center justify-between mb-1">
                <span class="text-sm text-gray-600">Unpaid Leave</span>
                <span class="text-sm font-medium">{{ applicationsByType.unpaid }}</span>
              </div>
              <div class="w-full bg-gray-200 rounded-full h-2">
                <div 
                  class="bg-gray-500 h-2 rounded-full" 
                  :style="{ width: stats.total ? `${(applicationsByType.unpaid / stats.total) * 100}%` : '0%' }"
                />
              </div>
            </div>
          </div>
        </div>

        <!-- Quick Info -->
        <div class="bg-white rounded-xl shadow-sm p-6">
          <div class="flex items-center gap-3 mb-4">
            <Calendar class="w-5 h-5 text-gray-400" />
            <h3 class="font-semibold text-gray-900">Quick Stats</h3>
          </div>
          <div class="space-y-3">
            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
              <span class="text-gray-600">Total approved days</span>
              <span class="font-bold text-gray-900">
                {{ leavesStore.applications
                  .filter(a => a.status === "approved")
                  .reduce((sum, a) => sum + (a.total_days || 0), 0) }} days
              </span>
            </div>
            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
              <span class="text-gray-600">This week</span>
              <span class="font-bold text-indigo-600">
                {{ leavesStore.applications.filter(a => {
                  const created = new Date(a.created_at || a.start_date)
                  const now = new Date()
                  const weekAgo = new Date(now.getTime() - 7 * 24 * 60 * 60 * 1000)
                  return created >= weekAgo
                }).length }}
              </span>
            </div>
          </div>
        </div>
      </div>

      <!-- Recent Applications -->
      <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="p-4 sm:p-6 border-b border-gray-100">
          <h2 class="text-lg font-bold text-gray-900">Recent Requests</h2>
        </div>

        <!-- Empty -->
        <div v-if="recentApplications.length === 0" class="p-8 text-center">
          <FileText class="w-12 h-12 text-gray-300 mx-auto mb-3" />
          <p class="text-gray-500">No leave requests yet</p>
        </div>

        <!-- Mobile Cards -->
        <div class="block sm:hidden divide-y divide-gray-100">
          <div v-for="app in recentApplications" :key="app.id" class="p-4">
            <div class="flex items-start justify-between mb-2">
              <div>
                <p class="font-medium text-gray-900">{{ app.user_name }}</p>
                <p class="text-sm text-gray-500">{{ getLeaveTypeLabel(app.type) }}</p>
              </div>
              <StatusBadge :status="app.status" />
            </div>
            <p class="text-sm text-gray-600">
              {{ formatDate(app.start_date) }} - {{ formatDate(app.end_date) }} ({{ app.total_days }} day(s))
            </p>
          </div>
        </div>

        <!-- Desktop Table -->
        <div class="hidden sm:block overflow-x-auto">
          <table class="w-full">
            <thead class="bg-gray-50 border-b border-gray-100">
              <tr>
                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Employee</th>
                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Type</th>
                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Period</th>
                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Days</th>
                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Status</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
              <tr v-for="app in recentApplications" :key="app.id" class="hover:bg-gray-50 transition">
                <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ app.user_name }}</td>
                <td class="px-6 py-4 text-sm text-gray-600">{{ getLeaveTypeLabel(app.type) }}</td>
                <td class="px-6 py-4 text-sm text-gray-600">
                  {{ formatDate(app.start_date) }} - {{ formatDate(app.end_date) }}
                </td>
                <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ app.total_days }}</td>
                <td class="px-6 py-4">
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
