# Leave System - Frontend

Ứng dụng quản lý nghỉ phép cho công ty. Xây dựng bằng Vue.js 3 + Vite.

## Yêu cầu hệ thống

- Node.js 16+
- npm hoặc yarn
- Backend API đang chạy tại `http://localhost:8000`

## Cài đặt

```bash
# Clone project
git clone <https://github.com/ta28nov/leave-system>
cd frontend

# Cài đặt dependencies
npm install
```

## Cấu hình

Tạo file `.env` trong thư mục `frontend`:

```env
VITE_API_URL=http://localhost:8000/api
```

## Chạy ứng dụng

### Development

```bash
npm run dev
```

Mở trình duyệt tại: `http://localhost:5173`

### Production Build

```bash
npm run build
```

File build sẽ nằm trong thư mục `dist/`

### Preview Production Build

```bash
npm run preview
```

## Tài khoản test

Backend cung cấp các tài khoản test sau:

**Admin:**
- Email: `admin@example.com`
- Password: `password123`

**Manager:**
- Email: `manager1@example.com`
- Password: `password123`
**Employee:**
- Email: `employee1@test.com`
- Password: `password123`

## Cấu trúc thư mục

```
frontend/
├── public/              # Static files
├── src/
│   ├── assets/          # Images, fonts, styles
│   ├── components/      # Reusable components
│   │   ├── ConfirmModal.vue
│   │   ├── ErrorBoundary.vue
│   │   ├── LoadingSpinner.vue
│   │   ├── Modal.vue
│   │   ├── Navbar.vue
│   │   ├── StatusBadge.vue
│   │   ├── Toast.vue
│   │   └── ToastContainer.vue
│   ├── composables/     # Vue composables
│   │   └── useToast.js
│   ├── router/          # Vue Router config
│   │   └── index.js
│   ├── services/        # API services
│   │   ├── api.js
│   │   ├── authService.js
│   │   └── leaveService.js
│   ├── stores/          # Pinia stores
│   │   ├── auth.js
│   │   └── leaves.js
│   ├── views/           # Page components
│   │   ├── AdminDashboardView.vue
│   │   ├── DashboardView.vue
│   │   ├── ForbiddenView.vue
│   │   ├── LeaveListView.vue
│   │   ├── LeaveRequestForm.vue
│   │   ├── LoginView.vue
│   │   ├── ManagerApprovalView.vue
│   │   └── NotFoundView.vue
│   ├── App.vue
│   └── main.js
├── .env                 # Environment variables
├── index.html
├── package.json
└── vite.config.js
```

## Công nghệ sử dụng

### Core
- Vue.js 3 - Progressive JavaScript framework
- Vite - Build tool
- Vue Router - Routing
- Pinia - State management

### UI/UX
- Tailwind CSS - Utility-first CSS framework
- Lucide Vue Next - Icon library
- Custom toast notifications

### HTTP Client
- Axios - Promise based HTTP client

## Tính năng chính

### Cho tất cả người dùng
- Đăng nhập / Đăng ký
- Xem dashboard cá nhân
- Tạo đơn xin nghỉ phép
- Xem danh sách đơn của mình
- Hủy đơn đang chờ duyệt
- Lọc theo trạng thái
- Phân trang

### Cho Manager/Admin
- Xem tất cả đơn nghỉ phép
- Duyệt đơn
- Từ chối đơn (có lý do)
- Lọc theo nhân viên/trạng thái

### Cho Admin
- Dashboard quản trị
- Quản lý người dùng
- Thống kê tổng quan

## API Integration

Ứng dụng kết nối với backend Laravel qua REST API:

**Authentication:**
- POST `/api/auth/login` - Đăng nhập
- POST `/api/auth/register` - Đăng ký
- POST `/api/auth/logout` - Đăng xuất
- GET `/api/auth/me` - Lấy thông tin user

**Leave Applications:**
- GET `/api/leave-applications` - Danh sách đơn (có phân trang)
- POST `/api/leave-applications` - Tạo đơn mới
- PUT `/api/leave-applications/{id}` - Cập nhật đơn
- DELETE `/api/leave-applications/{id}` - Xóa đơn
- POST `/api/leave-applications/{id}/cancel` - Hủy đơn
- POST `/api/leave-applications/{id}/approve` - Duyệt đơn
- POST `/api/leave-applications/{id}/reject` - Từ chối đơn

## State Management

Sử dụng Pinia với 2 stores chính:

**Auth Store** (`stores/auth.js`):
- Quản lý authentication state
- Lưu token và user info
- Auto-refresh token
- Role-based access

**Leaves Store** (`stores/leaves.js`):
- Quản lý leave applications
- Pagination metadata
- CRUD operations
- Approve/Reject/Cancel

## Routing

**Public routes:**
- `/login` - Trang đăng nhập

**Protected routes:**
- `/dashboard` - Dashboard cá nhân
- `/leave/list` - Danh sách đơn của tôi
- `/leave/create` - Tạo đơn mới
- `/manager/approvals` - Duyệt đơn (Manager/Admin only)
- `/admin/dashboard` - Dashboard admin (Admin only)

**Error routes:**
- `/403` - Forbidden
- `/404` - Not Found

## Features kỹ thuật

### Security
- JWT token authentication
- Auto token refresh
- Protected routes với navigation guards
- Role-based access control (RBAC)

### UX/UI
- Toast notifications cho tất cả actions
- Loading states
- Error handling
- Empty states
- Mobile responsive
- Field-level validation errors

### Performance
- Backend pagination (không load hết data)
- Lazy loading routes
- Optimized re-renders với computed properties
- Axios interceptors cho token refresh

### Developer Experience
- Console logging cho debugging
- TypeScript-ready structure
- Reusable components
- Composables pattern
- Centralized API services

## Xử lý lỗi

Ứng dụng xử lý các HTTP status codes:

- `200/201` - Success
- `401` - Unauthorized (redirect về login)
- `403` - Forbidden (hiện trang 403)
- `422` - Validation errors (hiện errors dưới từng field)
- `500` - Server error (hiện toast error)

## Responsive Design

Ứng dụng responsive với breakpoints:

- Mobile: < 640px (card view)
- Tablet: 640px - 1024px
- Desktop: > 1024px (table view)

## Build & Deploy

### Development
```bash
npm run dev
```

### Production
```bash
# Build
npm run build

# Files sẽ nằm trong dist/
# Upload dist/ lên web server (Nginx, Apache, etc.)
```

### Environment Variables

**Development** (`.env`):
```env
VITE_API_URL=http://localhost:8000/api
```

**Production** (`.env.production`):
```env
VITE_API_URL=https://api.yourdomain.com/api
```

## Lưu ý khi deploy

1. Cập nhật `VITE_API_URL` trong `.env.production`
2. Đảm bảo backend CORS cho phép domain của frontend
3. Build project: `npm run build`
4. Upload thư mục `dist/` lên server
5. Configure server để redirect tất cả routes về `index.html` (SPA mode)

