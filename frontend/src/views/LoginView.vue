<script setup>
import { ref, onMounted } from "vue";
import { useRouter } from "vue-router";
import { useAuthStore } from "../stores/auth";
import { AlertCircle, CheckCircle, LogIn, UserPlus, Eye, EyeOff } from "lucide-vue-next";
import LoadingSpinner from "../components/LoadingSpinner.vue";

const router = useRouter();
const authStore = useAuthStore();

// Form state
const isLoginMode = ref(true);
const showPassword = ref(false);
const successMessage = ref("");

// Login form
const loginForm = ref({
  email: "",
  password: "",
});

// Register form
const registerForm = ref({
  name: "",
  email: "",
  password: "",
  password_confirmation: "",
});

const formErrors = ref({});

// Toggle between login and register
const toggleMode = () => {
  isLoginMode.value = !isLoginMode.value;
  formErrors.value = {};
  authStore.error = null;
  successMessage.value = "";
};

// Handle Login
const handleLogin = async () => {
  formErrors.value = {};
  successMessage.value = "";

  // Basic validation
  if (!loginForm.value.email) {
    formErrors.value.email = "Email is required";
  }
  if (!loginForm.value.password) {
    formErrors.value.password = "Password is required";
  }

  if (Object.keys(formErrors.value).length > 0) return;

  console.log('Attempting login...');
  const success = await authStore.login(
    loginForm.value.email,
    loginForm.value.password
  );
  console.log('Login result:', success);
  
  if (success) {
    console.log('Login successful. Checking user role:', authStore.user);
    // Redirect based on role
    if (authStore.isAdmin) {
      console.log('Redirecting to admin dashboard');
      router.push("/admin/dashboard");
    } else {
      console.log('Redirecting to user dashboard');
      router.push("/dashboard");
    }
  } else {
    console.log('Login failed. Error:', authStore.error);
  }
};

// Handle Register
const handleRegister = async () => {
  formErrors.value = {};
  successMessage.value = "";

  // Validation
  if (!registerForm.value.name) {
    formErrors.value.name = "Full name is required";
  }
  if (!registerForm.value.email) {
    formErrors.value.email = "Email is required";
  }
  if (!registerForm.value.password) {
    formErrors.value.password = "Password is required";
  } else if (registerForm.value.password.length < 6) {
    formErrors.value.password = "Password must be at least 6 characters";
  }
  if (
    registerForm.value.password !== registerForm.value.password_confirmation
  ) {
    formErrors.value.password_confirmation = "Passwords do not match";
  }

  if (Object.keys(formErrors.value).length > 0) return;

  const result = await authStore.register(registerForm.value);
  
  if (result.success) {
    if (result.autoLogin) {
      router.push("/dashboard");
    } else {
      // Manual login required
      successMessage.value = result.message || "Registration successful! Please login.";
      isLoginMode.value = true;
      loginForm.value.email = registerForm.value.email;
      // Clear password for security
      loginForm.value.password = ""; 
    }
  }
};

// Fill test credentials
const fillTestCredentials = (role) => {
  const credentials = {
    admin: { email: "admin@test.com", password: "password" },
    manager: { email: "manager@test.com", password: "password" },
    employee: { email: "employee@test.com", password: "password" },
  };
  loginForm.value = { ...credentials[role] };
};

// Check if already logged in
onMounted(() => {
  if (authStore.isAuthenticated) {
    router.push("/dashboard");
  }
});
</script>

<template>
  <div
    class="min-h-screen bg-gradient-to-br from-indigo-50 via-white to-cyan-50 flex items-center justify-center p-4"
  >
    <div class="w-full max-w-md">
      <!-- Card -->
      <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
        <!-- Header -->
        <div
          class="bg-gradient-to-r from-indigo-600 to-indigo-500 px-8 py-6 text-center"
        >
          <h1 class="text-2xl font-bold text-white">Leave System</h1>
          <p class="text-indigo-100 mt-1">
            {{ isLoginMode ? "Sign in to continue" : "Create a new account" }}
          </p>
        </div>

        <div class="p-8">
          <!-- Success Alert -->
          <div
            v-if="successMessage"
            class="mb-6 p-4 bg-green-50 border border-green-200 rounded-xl flex items-start gap-3"
          >
            <CheckCircle class="w-5 h-5 text-green-500 mt-0.5 flex-shrink-0" />
            <p class="text-green-700 text-sm">{{ successMessage }}</p>
          </div>

          <!-- Error Alert -->
          <div
            v-if="authStore.error"
            class="mb-6 p-4 bg-red-50 border border-red-200 rounded-xl flex items-start gap-3"
          >
            <AlertCircle class="w-5 h-5 text-red-500 mt-0.5 flex-shrink-0" />
            <p class="text-red-700 text-sm">{{ authStore.error }}</p>
          </div>

          <!-- Login Form -->
          <form
            v-if="isLoginMode"
            @submit.prevent="handleLogin"
            class="space-y-5"
          >
            <!-- Email -->
            <div>
              <label
                for="email"
                class="block text-sm font-medium text-gray-700 mb-1.5"
              >
                Email
              </label>
              <input
                id="email"
                v-model="loginForm.email"
                type="email"
                placeholder="example@company.com"
                autocomplete="email"
                :class="[
                  'w-full px-4 py-3 border rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent outline-none transition',
                  formErrors.email ? 'border-red-500' : 'border-gray-300',
                ]"
              />
              <p v-if="formErrors.email" class="text-red-600 text-sm mt-1">
                {{ formErrors.email }}
              </p>
            </div>

            <!-- Password -->
            <div>
              <label
                for="password"
                class="block text-sm font-medium text-gray-700 mb-1.5"
              >
                Password
              </label>
              <div class="relative">
                <input
                  id="password"
                  v-model="loginForm.password"
                  :type="showPassword ? 'text' : 'password'"
                  placeholder="••••••••"
                  autocomplete="current-password"
                  :class="[
                    'w-full px-4 py-3 pr-12 border rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent outline-none transition',
                    formErrors.password ? 'border-red-500' : 'border-gray-300',
                  ]"
                />
                <button
                  type="button"
                  @click="showPassword = !showPassword"
                  class="absolute right-3 top-1/2 -translate-y-1/2 p-1 text-gray-400 hover:text-gray-600"
                >
                  <Eye v-if="!showPassword" class="w-5 h-5" />
                  <EyeOff v-else class="w-5 h-5" />
                </button>
              </div>
              <p v-if="formErrors.password" class="text-red-600 text-sm mt-1">
                {{ formErrors.password }}
              </p>
            </div>

            <!-- Submit Button -->
            <button
              type="submit"
              :disabled="authStore.isLoading"
              class="w-full bg-indigo-600 hover:bg-indigo-700 disabled:bg-indigo-400 text-white font-semibold py-3 px-4 rounded-xl transition flex items-center justify-center gap-2"
            >
              <LoadingSpinner
                v-if="authStore.isLoading"
                size="sm"
                color="white"
              />
              <LogIn v-else class="w-5 h-5" />
              {{ authStore.isLoading ? "Signing in..." : "Sign In" }}
            </button>
          </form>

          <!-- Register Form -->
          <form v-else @submit.prevent="handleRegister" class="space-y-5">
            <!-- Name -->
            <div>
              <label
                for="name"
                class="block text-sm font-medium text-gray-700 mb-1.5"
              >
                Full Name
              </label>
              <input
                id="name"
                v-model="registerForm.name"
                type="text"
                placeholder="John Doe"
                :class="[
                  'w-full px-4 py-3 border rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent outline-none transition',
                  formErrors.name ? 'border-red-500' : 'border-gray-300',
                ]"
              />
              <p v-if="formErrors.name" class="text-red-600 text-sm mt-1">
                {{ formErrors.name }}
              </p>
            </div>

            <!-- Email -->
            <div>
              <label
                for="reg-email"
                class="block text-sm font-medium text-gray-700 mb-1.5"
              >
                Email
              </label>
              <input
                id="reg-email"
                v-model="registerForm.email"
                type="email"
                placeholder="example@company.com"
                :class="[
                  'w-full px-4 py-3 border rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent outline-none transition',
                  formErrors.email ? 'border-red-500' : 'border-gray-300',
                ]"
              />
              <p v-if="formErrors.email" class="text-red-600 text-sm mt-1">
                {{ formErrors.email }}
              </p>
            </div>

            <!-- Password -->
            <div>
              <label
                for="reg-password"
                class="block text-sm font-medium text-gray-700 mb-1.5"
              >
                Password
              </label>
              <input
                id="reg-password"
                v-model="registerForm.password"
                type="password"
                placeholder="At least 6 characters"
                :class="[
                  'w-full px-4 py-3 border rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent outline-none transition',
                  formErrors.password ? 'border-red-500' : 'border-gray-300',
                ]"
              />
              <p v-if="formErrors.password" class="text-red-600 text-sm mt-1">
                {{ formErrors.password }}
              </p>
            </div>

            <!-- Confirm Password -->
            <div>
              <label
                for="reg-password-confirm"
                class="block text-sm font-medium text-gray-700 mb-1.5"
              >
                Confirm Password
              </label>
              <input
                id="reg-password-confirm"
                v-model="registerForm.password_confirmation"
                type="password"
                placeholder="Re-enter password"
                :class="[
                  'w-full px-4 py-3 border rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent outline-none transition',
                  formErrors.password_confirmation
                    ? 'border-red-500'
                    : 'border-gray-300',
                ]"
              />
              <p
                v-if="formErrors.password_confirmation"
                class="text-red-600 text-sm mt-1"
              >
                {{ formErrors.password_confirmation }}
              </p>
            </div>

            <!-- Submit Button -->
            <button
              type="submit"
              :disabled="authStore.isLoading"
              class="w-full bg-indigo-600 hover:bg-indigo-700 disabled:bg-indigo-400 text-white font-semibold py-3 px-4 rounded-xl transition flex items-center justify-center gap-2"
            >
              <LoadingSpinner
                v-if="authStore.isLoading"
                size="sm"
                color="white"
              />
              <UserPlus v-else class="w-5 h-5" />
              {{ authStore.isLoading ? "Creating account..." : "Sign Up" }}
            </button>
          </form>

          <!-- Toggle Mode -->
          <div class="mt-6 text-center">
            <p class="text-gray-600">
              {{ isLoginMode ? "Don't have an account?" : "Already have an account?" }}
              <button
                @click="toggleMode"
                class="text-indigo-600 hover:text-indigo-700 font-medium ml-1"
              >
                {{ isLoginMode ? "Sign up" : "Sign in" }}
              </button>
            </p>
          </div>

          <!-- Test Credentials (Only show in login mode) -->
          <div v-if="isLoginMode" class="mt-6 pt-6 border-t border-gray-200">
            <p class="text-sm text-gray-600 mb-3 font-medium">
              Test Credentials:
            </p>
            <div class="grid grid-cols-3 gap-2">
              <button
                @click="fillTestCredentials('admin')"
                class="px-3 py-2 bg-red-50 hover:bg-red-100 text-red-700 text-xs font-medium rounded-lg transition"
              >
                Admin
              </button>
              <button
                @click="fillTestCredentials('manager')"
                class="px-3 py-2 bg-blue-50 hover:bg-blue-100 text-blue-700 text-xs font-medium rounded-lg transition"
              >
                Manager
              </button>
              <button
                @click="fillTestCredentials('employee')"
                class="px-3 py-2 bg-green-50 hover:bg-green-100 text-green-700 text-xs font-medium rounded-lg transition"
              >
                Employee
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Footer -->
      <p class="text-center text-gray-500 text-sm mt-6">
       Leave Application System
      </p>
    </div>
  </div>
</template>
