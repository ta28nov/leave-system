import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '../stores/auth'
import LoginView from '../views/LoginView.vue'
import DashboardView from '../views/DashboardView.vue'
import LeaveRequestForm from '../views/LeaveRequestForm.vue'
import LeaveListView from '../views/LeaveListView.vue'
import ManagerApprovalView from '../views/ManagerApprovalView.vue'
import AdminDashboardView from '../views/AdminDashboardView.vue'
import NotFoundView from '../views/NotFoundView.vue'
import ForbiddenView from '../views/ForbiddenView.vue'

const routes = [
  {
    path: '/login',
    name: 'Login',
    component: LoginView,
    meta: { requiresAuth: false }
  },
  {
    path: '/',
    redirect: '/dashboard'
  },
  {
    path: '/dashboard',
    name: 'Dashboard',
    component: DashboardView,
    meta: { requiresAuth: true }
  },
  {
    path: '/admin/dashboard',
    name: 'AdminDashboard',
    component: AdminDashboardView,
    meta: { requiresAuth: true, requiresAdmin: true }
  },
  {
    path: '/leave/list',
    name: 'LeaveList',
    component: LeaveListView,
    meta: { requiresAuth: true }
  },
  {
    path: '/leave/create',
    name: 'LeaveCreate',
    component: LeaveRequestForm,
    meta: { requiresAuth: true }
  },
  {
    path: '/manager/approvals',
    name: 'ManagerApprovals',
    component: ManagerApprovalView,
    meta: { requiresAuth: true, requiresManager: true }
  },
  {
    path: '/403',
    name: 'Forbidden',
    component: ForbiddenView
  },
  {
    path: '/:pathMatch(.*)*',
    name: 'NotFound',
    component: NotFoundView
  }
]

const router = createRouter({
  history: createWebHistory(),
  routes
})

router.beforeEach((to, from, next) => {
  const authStore = useAuthStore()
  
  console.log(`Router: Navigating from ${from.path} to ${to.path}`);
  console.log(`Auth state: token=${!!authStore.token}, user=${!!authStore.user}, isAuthenticated=${authStore.isAuthenticated}`);

  // Check if auth is initialized
  if (!authStore.token) {
    authStore.initializeAuth()
  }

  // Public routes
  if (!to.meta.requiresAuth) {
    // If logged in and trying to access login, redirect to dashboard
    if (to.path === '/login' && authStore.isAuthenticated) {
      console.log('Router: Already logged in, redirecting to dashboard');
      return next(authStore.isAdmin ? '/admin/dashboard' : '/dashboard')
    }
    return next()
  }

  // Protected routes - check authentication
  if (to.meta.requiresAuth && !authStore.isAuthenticated) {
    console.log('Router: Protected route, not authenticated. Redirecting to login.');
    return next('/login')
  }

  // Role-based access control
  if (to.meta.requiresAdmin && !authStore.isAdmin) {
    console.log('Router: Admin required. Access denied.');
    return next('/403')
  }

  if (to.meta.requiresManager && !authStore.isManager && !authStore.isAdmin) {
    console.log('Router: Manager required. Access denied.');
    return next('/403')
  }

  console.log('Router: Access granted.');
  next()
})

export default router
