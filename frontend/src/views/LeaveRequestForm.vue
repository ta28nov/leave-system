<script setup>
import { ref, computed } from "vue";
import { useRouter } from "vue-router";
import { useAuthStore } from "../stores/auth";
import { useLeavesStore } from "../stores/leaves";
import { ArrowLeft, Send, Calendar } from "lucide-vue-next";
import Navbar from "../components/Navbar.vue";
import LoadingSpinner from "../components/LoadingSpinner.vue";
import { useToast } from "../composables/useToast";

const router = useRouter();
const authStore = useAuthStore();
const leavesStore = useLeavesStore();
const toast = useToast();

const form = ref({
  type: "annual",
  start_date: "",
  end_date: "",
  reason: "",
});

const errors = ref({});
const isSubmitting = ref(false);

// Leave types with labels
const leaveTypes = [
  {
    value: "annual",
    label: "Annual Leave",
    description: "Regular paid leave",
  },
  { value: "sick", label: "Sick Leave", description: "Medical-related leave" },
  {
    value: "unpaid",
    label: "Unpaid Leave",
    description: "Leave without pay",
  },
];

// Calculate total days
const totalDays = computed(() => {
  if (!form.value.start_date || !form.value.end_date) return 0;
  const start = new Date(form.value.start_date);
  const end = new Date(form.value.end_date);
  if (end < start) return 0;
  const diffTime = end - start;
  const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;
  return diffDays > 0 ? diffDays : 0;
});

// Get today's date for min date
const today = computed(() => {
  return new Date().toISOString().split("T")[0];
});

// Validate form
const validateForm = () => {
  errors.value = {};

  if (!form.value.start_date) {
    errors.value.start_date = "Start date is required";
  } else if (form.value.start_date < today.value) {
    errors.value.start_date = "Start date must be today or later";
  }

  if (!form.value.end_date) {
    errors.value.end_date = "End date is required";
  } else if (
    form.value.start_date &&
    form.value.end_date < form.value.start_date
  ) {
    errors.value.end_date = "End date must be after start date";
  }

  if (!form.value.reason.trim()) {
    errors.value.reason = "Reason is required";
  } else if (form.value.reason.trim().length < 5) {
    errors.value.reason = "Reason must be at least 5 characters";
  }

  return Object.keys(errors.value).length === 0;
};

// Handle form submission
const handleSubmit = async () => {
  if (!validateForm()) return;

  isSubmitting.value = true;

  const formData = {
    type: form.value.type,
    start_date: form.value.start_date,
    end_date: form.value.end_date,
    reason: form.value.reason.trim(),
  };

  try {
    const result = await leavesStore.createApplication(formData);

    if (result.success) {
      toast.success("Leave request submitted successfully!");
      router.push("/dashboard");
    } else {
      // Handle validation errors from API
      if (result.errors) {
        Object.keys(result.errors).forEach((key) => {
          errors.value[key] = Array.isArray(result.errors[key])
            ? result.errors[key][0]
            : result.errors[key];
        });
      }
      toast.error(result.message || "Failed to submit request");
    }
  } catch (err) {
    toast.error("An error occurred while submitting");
  } finally {
    isSubmitting.value = false;
  }
};

const handleBack = () => {
  router.back();
};
</script>

<template>
  <div class="min-h-screen bg-gray-50">
    <Navbar />

    <div class="max-w-2xl mx-auto px-4 py-6 sm:py-8">
      <!-- Back Button -->
      <button
        @click="handleBack"
        class="flex items-center gap-2 text-gray-600 hover:text-gray-900 font-medium mb-6 transition"
      >
        <ArrowLeft class="w-5 h-5" />
        Back
      </button>

      <!-- Form Card -->
      <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
        <!-- Header -->
        <div
          class="bg-gradient-to-r from-indigo-600 to-indigo-500 px-6 py-5 sm:px-8 sm:py-6"
        >
          <h1
            class="text-xl sm:text-2xl font-bold text-white flex items-center gap-3"
          >
            <Calendar class="w-6 h-6" />
            New Leave Request
          </h1>
          <p class="text-indigo-100 mt-1 text-sm">
            Fill in the details to submit your leave request
          </p>
        </div>

        <form @submit.prevent="handleSubmit" class="p-6 sm:p-8 space-y-6">
          <!-- Leave Type -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-3">
              Leave Type
            </label>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
              <label
                v-for="type in leaveTypes"
                :key="type.value"
                class="relative flex cursor-pointer rounded-xl border p-4 focus:outline-none transition"
                :class="[
                  form.type === type.value
                    ? 'border-indigo-600 bg-indigo-50 ring-2 ring-indigo-600'
                    : 'border-gray-200 hover:border-gray-300',
                ]"
              >
                <input
                  type="radio"
                  :value="type.value"
                  v-model="form.type"
                  class="sr-only"
                />
                <div class="flex flex-col">
                  <span
                    class="block text-sm font-medium"
                    :class="
                      form.type === type.value
                        ? 'text-indigo-900'
                        : 'text-gray-900'
                    "
                  >
                    {{ type.label }}
                  </span>
                  <span
                    class="mt-1 text-xs"
                    :class="
                      form.type === type.value
                        ? 'text-indigo-600'
                        : 'text-gray-500'
                    "
                  >
                    {{ type.description }}
                  </span>
                </div>
              </label>
            </div>
          </div>

          <!-- Date Range -->
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <!-- Start Date -->
            <div>
              <label
                for="start_date"
                class="block text-sm font-medium text-gray-700 mb-2"
              >
                Start Date
              </label>
              <input
                id="start_date"
                v-model="form.start_date"
                type="date"
                :min="today"
                :class="[
                  'w-full px-4 py-3 border rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent outline-none transition',
                  errors.start_date
                    ? 'border-red-500 bg-red-50'
                    : 'border-gray-300',
                ]"
              />
              <p v-if="errors.start_date" class="text-red-600 text-sm mt-1">
                {{ errors.start_date }}
              </p>
            </div>

            <!-- End Date -->
            <div>
              <label
                for="end_date"
                class="block text-sm font-medium text-gray-700 mb-2"
              >
                End Date
              </label>
              <input
                id="end_date"
                v-model="form.end_date"
                type="date"
                :min="form.start_date || today"
                :class="[
                  'w-full px-4 py-3 border rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent outline-none transition',
                  errors.end_date
                    ? 'border-red-500 bg-red-50'
                    : 'border-gray-300',
                ]"
              />
              <p v-if="errors.end_date" class="text-red-600 text-sm mt-1">
                {{ errors.end_date }}
              </p>
            </div>
          </div>

          <!-- Total Days Display -->
          <div
            class="bg-gray-50 rounded-xl p-4 flex items-center justify-between"
          >
            <span class="text-gray-600 font-medium">Total Days</span>
            <span class="text-2xl font-bold text-indigo-600"
              >{{ totalDays }} day(s)</span
            >
          </div>

          <!-- Reason -->
          <div>
            <label
              for="reason"
              class="block text-sm font-medium text-gray-700 mb-2"
            >
              Reason
            </label>
            <textarea
              id="reason"
              v-model="form.reason"
              rows="4"
              placeholder="Describe the reason for your leave request..."
              :class="[
                'w-full px-4 py-3 border rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent outline-none transition resize-none',
                errors.reason ? 'border-red-500 bg-red-50' : 'border-gray-300',
              ]"
            ></textarea>
            <p v-if="errors.reason" class="text-red-600 text-sm mt-1">
              {{ errors.reason }}
            </p>
          </div>

          <!-- Action Buttons -->
          <div class="flex flex-col sm:flex-row gap-3 pt-4">
            <button
              type="submit"
              :disabled="isSubmitting || totalDays === 0"
              class="flex-1 bg-indigo-600 hover:bg-indigo-700 disabled:bg-indigo-400 text-white font-semibold py-3 px-6 rounded-xl transition flex items-center justify-center gap-2 order-1 sm:order-2"
            >
              <LoadingSpinner v-if="isSubmitting" size="sm" color="white" />
              <Send v-else class="w-5 h-5" />
              {{ isSubmitting ? "Submitting..." : "Submit Request" }}
            </button>
            <button
              type="button"
              @click="handleBack"
              :disabled="isSubmitting"
              class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-900 font-semibold py-3 px-6 rounded-xl transition order-2 sm:order-1"
            >
              Cancel
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>
