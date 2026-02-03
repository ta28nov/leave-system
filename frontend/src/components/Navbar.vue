<script setup>
import { ref, computed } from "vue";
import { useRouter } from "vue-router";
import { useAuthStore } from "../stores/auth";
import {
  LogOut,
  Menu,
  X,
  Home,
  FileText,
  CheckSquare,
  LayoutDashboard,
} from "lucide-vue-next";

const authStore = useAuthStore();
const router = useRouter();

// Mobile menu state
const isMobileMenuOpen = ref(false);

const toggleMobileMenu = () => {
  isMobileMenuOpen.value = !isMobileMenuOpen.value;
};

const closeMobileMenu = () => {
  isMobileMenuOpen.value = false;
};

const getRoleDisplay = computed(() => {
  if (!authStore.user) return "";
  if (authStore.isAdmin) return "Admin";
  if (authStore.isManager) return "Manager";
  return "Employee";
});

const getRoleBadgeClass = computed(() => {
  if (authStore.isAdmin) return "bg-red-100 text-red-700";
  if (authStore.isManager) return "bg-blue-100 text-blue-700";
  return "bg-green-100 text-green-700";
});

const handleLogout = async () => {
  closeMobileMenu();
  await authStore.logout();
  router.push("/login");
};

const navigateTo = (path) => {
  closeMobileMenu();
  router.push(path);
};

// Navigation items based on role
const navItems = computed(() => {
  const items = [];

  if (authStore.isAdmin) {
    items.push({
      path: "/admin/dashboard",
      label: "Admin Dashboard",
      icon: LayoutDashboard,
    });
  } else {
    items.push({
      path: "/dashboard",
      label: "Dashboard",
      icon: Home,
    });
  }

  // All users can see their own leaves
  items.push({
    path: "/leave/list",
    label: "My Requests",
    icon: FileText,
  });

  if (authStore.isManager || authStore.isAdmin) {
    items.push({
      path: "/manager/approvals",
      label: "Approvals",
      icon: CheckSquare,
    });
  }

  return items;
});
</script>

<template>
  <nav class="bg-white shadow-sm sticky top-0 z-40">
    <div class="max-w-7xl mx-auto px-4">
      <div class="flex items-center justify-between h-16">
        <!-- Left Side: Logo & Hamburger -->
        <div class="flex items-center gap-4">
          <!-- Mobile Hamburger -->
          <button
            @click="toggleMobileMenu"
            class="md:hidden p-2 -ml-2 rounded-lg hover:bg-gray-100 transition"
            :aria-expanded="isMobileMenuOpen"
            aria-label="Toggle menu"
          >
            <Menu v-if="!isMobileMenuOpen" class="w-6 h-6 text-gray-700" />
            <X v-else class="w-6 h-6 text-gray-700" />
          </button>

          <!-- Logo -->
          <RouterLink to="/" class="flex items-center gap-2">
            <h1 class="text-xl font-bold text-indigo-600">LeaveSystem</h1>
          </RouterLink>

          <!-- Desktop Navigation Links -->
          <div class="hidden md:flex items-center gap-1 ml-8">
            <RouterLink
              v-for="item in navItems"
              :key="item.path"
              :to="item.path"
              class="px-4 py-2 text-gray-700 hover:text-indigo-600 hover:bg-indigo-50 font-medium transition rounded-lg"
              active-class="text-indigo-600 bg-indigo-50"
            >
              {{ item.label }}
            </RouterLink>
          </div>
        </div>

        <!-- Right Side: User Info & Logout -->
        <div class="flex items-center gap-3">
          <!-- User Info (Desktop) -->
          <div class="hidden sm:flex items-center gap-3">
            <div class="text-right">
              <p class="text-sm font-medium text-gray-900">
                {{ authStore.user?.name }}
              </p>
              <span
                class="text-xs px-2 py-0.5 rounded-full font-medium"
                :class="getRoleBadgeClass"
              >
                {{ getRoleDisplay }}
              </span>
            </div>
          </div>

          <!-- Logout Button -->
          <button
            @click="handleLogout"
            class="p-2.5 hover:bg-red-50 hover:text-red-600 rounded-lg transition text-gray-600"
            title="Logout"
          >
            <LogOut class="w-5 h-5" />
          </button>
        </div>
      </div>
    </div>

    <!-- Mobile Menu Overlay -->
    <Transition
      enter-active-class="duration-200 ease-out"
      enter-from-class="opacity-0"
      enter-to-class="opacity-100"
      leave-active-class="duration-150 ease-in"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0"
    >
      <div
        v-if="isMobileMenuOpen"
        class="fixed inset-0 bg-black/50 z-40 md:hidden"
        @click="closeMobileMenu"
      />
    </Transition>

    <!-- Mobile Menu Drawer -->
    <Transition
      enter-active-class="duration-300 ease-out"
      enter-from-class="-translate-x-full"
      enter-to-class="translate-x-0"
      leave-active-class="duration-200 ease-in"
      leave-from-class="translate-x-0"
      leave-to-class="-translate-x-full"
    >
      <div
        v-if="isMobileMenuOpen"
        class="fixed top-0 left-0 bottom-0 w-72 bg-white shadow-xl z-50 md:hidden"
      >
        <!-- Mobile Menu Header -->
        <div class="p-4 border-b border-gray-200">
          <div class="flex items-center justify-between">
            <h2 class="text-lg font-bold text-indigo-600">LeaveSystem</h2>
            <button
              @click="closeMobileMenu"
              class="p-2 hover:bg-gray-100 rounded-lg transition"
            >
              <X class="w-5 h-5 text-gray-600" />
            </button>
          </div>

          <!-- User Info -->
          <div class="mt-4 p-3 bg-gray-50 rounded-lg">
            <p class="font-medium text-gray-900">{{ authStore.user?.name }}</p>
            <span
              class="text-xs px-2 py-0.5 rounded-full font-medium mt-1 inline-block"
              :class="getRoleBadgeClass"
            >
              {{ getRoleDisplay }}
            </span>
          </div>
        </div>

        <!-- Mobile Navigation Links -->
        <div class="p-4 space-y-1">
          <button
            v-for="item in navItems"
            :key="item.path"
            @click="navigateTo(item.path)"
            class="w-full flex items-center gap-3 px-4 py-3 text-gray-700 hover:text-indigo-600 hover:bg-indigo-50 font-medium transition rounded-lg text-left"
          >
            <component :is="item.icon" class="w-5 h-5" />
            {{ item.label }}
          </button>
        </div>

        <!-- Mobile Menu Footer -->
        <div
          class="absolute bottom-0 left-0 right-0 p-4 border-t border-gray-200"
        >
          <button
            @click="handleLogout"
            class="w-full flex items-center justify-center gap-2 px-4 py-3 bg-red-50 text-red-600 hover:bg-red-100 font-medium transition rounded-lg"
          >
            <LogOut class="w-5 h-5" />
            Logout
          </button>
        </div>
      </div>
    </Transition>
  </nav>
</template>
