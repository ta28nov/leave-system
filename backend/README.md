# LEAVE APPLICATION SYSTEM - BACKEND API

Hệ thống quản lý nghỉ phép nội bộ - RESTful API  
Training Project - Backend Developer với Laravel Framework

---

## Mục lục

- [Tổng quan](#tổng-quan)
- [Tính năng](#tính-năng)
- [Tech Stack](#tech-stack)
- [Cấu trúc thư mục](#cấu-trúc-thư-mục)
- [Cài đặt](#cài-đặt)
- [API Endpoints](#api-endpoints)
- [Phân quyền](#phân-quyền)
- [Testing với Postman](#testing-với-postman)
- [Tài liệu tham khảo](#tài-liệu-tham-khảo)

---

## Tổng quan

Hệ thống quản lý nghỉ phép nội bộ, cho phép:
- Nhân viên tạo đơn xin nghỉ phép
- Quản lý duyệt/từ chối đơn
- Admin quản trị toàn hệ thống

### Modules chính

| Module | Mô tả |
|--------|-------|
| Authentication | Đăng ký, đăng nhập, JWT token |
| Leave Applications | CRUD đơn nghỉ + Approve/Reject/Cancel |

---

## Tính năng

### Authentication và Authorization
- JWT Authentication (php-open-source-saver/jwt-auth)
- Role-based Access Control (Admin/Manager/Employee)
- Policy-based Authorization
- Token Refresh

### Leave Application Management
- Tạo đơn nghỉ phép (Annual/Sick/Unpaid)
- Kiểm tra trùng ngày nghỉ (Overlap Validation)
- Tự động tính số ngày nghỉ
- Phân quyền theo role
- Soft Delete

### API Features
- RESTful Design
- Pagination
- Filtering (status, user_id, month, year)
- Eager Loading (N+1 Prevention)
- Bilingual Messages (VI/EN)

---

## Tech Stack

| Công nghệ | Phiên bản | Mô tả |
|-----------|-----------|-------|
| PHP | 8.3.x | Ngôn ngữ lập trình |
| Laravel | 11.x | PHP Framework |
| MySQL | 8.0/8.4 | Database |
| JWT Auth | Latest | Authentication |
| Composer | 2.x | Package Manager |

---

## Cấu trúc thư mục

```
backend/
├── app/
│   ├── Enums/                    # Enum definitions
│   │   ├── LeaveApplicationStatus.php
│   │   ├── LeaveApplicationType.php
│   │   └── UserType.php
│   ├── Helpers/
│   │   └── ResponseHelper.php    # API Response format
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── AuthController.php
│   │   │   └── LeaveApplicationController.php
│   │   ├── Middleware/
│   │   │   └── CheckRole.php     # Role middleware
│   │   └── Requests/             # Form validation
│   │       ├── CreateLeaveApplicationRequest.php
│   │       ├── UpdateLeaveApplicationRequest.php
│   │       └── ...
│   ├── Models/
│   │   ├── User.php
│   │   └── LeaveApplication.php
│   ├── Policies/
│   │   └── LeaveApplicationPolicy.php
│   ├── Rules/
│   │   └── NoOverlapDates.php    # Custom validation
│   └── Services/
│       ├── AuthService.php
│       └── LeaveApplicationService.php
├── config/
│   ├── auth.php                  # JWT Guard config
│   ├── cors.php                  # CORS config
│   └── jwt.php                   # JWT config
├── database/
│   ├── migrations/
│   └── seeders/
│       └── UserSeeder.php        # Sample users
├── routes/
│   └── apis/
│       ├── auth.php
│       └── leaveApplication.php
└── leave-system-postman.json     # Postman collection
```

---

## Cài đặt

### Yêu cầu
- PHP >= 8.3
- Composer >= 2.x
- MySQL >= 8.0
- Laragon (recommended)

### Các bước cài đặt

```bash
# 1. Clone repository
git clone <repository-url>
cd leave-system/backend

# 2. Cài đặt dependencies
composer install

# 3. Tạo file môi trường
cp .env.example .env

# 4. Generate app key
php artisan key:generate

# 5. Generate JWT secret
php artisan jwt:secret

# 6. Cấu hình database trong .env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=leave_system
DB_USERNAME=root
DB_PASSWORD=

# 7. Chạy migrations
php artisan migrate

# 8. Seed sample data
php artisan db:seed

# 9. Cache config
php artisan config:cache

# 10. Start server
php artisan serve
```

### Tài khoản mẫu

| Role | Email | Password |
|------|-------|----------|
| Admin | admin@example.com | password123 |
| Manager | manager1@example.com | password123 |
| Manager | manager2@example.com | password123 |
| Employee | employee1@example.com | password123 |
| Employee | employee2@example.com | password123 |

---

## API Endpoints

### Authentication (/api/auth)

| Method | Endpoint | Mô tả | Auth |
|--------|----------|-------|------|
| POST | /register | Đăng ký tài khoản | Public |
| POST | /login | Đăng nhập | Public |
| POST | /logout | Đăng xuất | Required |
| POST | /refresh | Làm mới token | Required |
| GET | /me | Thông tin user | Required |

### Leave Applications (/api/leave-applications)

| Method | Endpoint | Mô tả | Auth | Role |
|--------|----------|-------|------|------|
| GET | / | Danh sách đơn | Required | All |
| GET | /{id} | Chi tiết đơn | Required | All* |
| POST | / | Tạo đơn mới | Required | All |
| PUT | /{id} | Cập nhật đơn | Required | Owner/Admin |
| DELETE | /{id} | Xóa đơn | Required | Admin |
| POST | /{id}/approve | Duyệt đơn | Required | Manager/Admin |
| POST | /{id}/reject | Từ chối đơn | Required | Manager/Admin |
| POST | /{id}/cancel | Hủy đơn | Required | Owner/Admin |

*Employee chỉ xem được đơn của mình

---

## Phân quyền

### User Types

| Type | Value | Quyền hạn |
|------|-------|-----------|
| Admin | 0 | Toàn quyền |
| Manager | 1 | Xem tất cả, Approve/Reject |
| Employee | 2 | CRUD đơn của mình |

### Authorization Matrix

| Action | Employee | Manager | Admin |
|--------|----------|---------|-------|
| List (own) | Có | Có | Có |
| List (all) | Không | Có | Có |
| View (own) | Có | Có | Có |
| View (other) | Không | Có | Có |
| Create | Có | Có | Có |
| Update (own)* | Có | Không | Có |
| Delete | Không | Không | Có |
| Approve | Không | Có | Có |
| Reject | Không | Có | Có |
| Cancel (own)** | Có | Không | Có |

*Chỉ khi status = 'new'  
**Chỉ khi chưa approved/rejected

---

## Testing với Postman

### Import Collection

1. Mở Postman
2. Click Import
3. Chọn file leave-system-postman.json

### Workflow Test

```
1. Login (Admin/Manager/Employee)
   → Token tự động lưu vào biến {{token}}

2. Create Leave Application
   → ID tự động lưu vào biến {{leave_id}}

3. Update/Approve/Reject/Cancel
   → Sử dụng {{leave_id}} đã lưu
```

### Environment Variables

| Variable | Value |
|----------|-------|
| base_url | http://127.0.0.1:8000/api |
| token | Auto-set after login |
| leave_id | Auto-set after create |

---

## Tài liệu tham khảo

- [DOCUMENTATION.md](DOCUMENTATION.md) - Tài liệu chi tiết đầy đủ
- [ERD.md](../ERD.md) - Entity Relationship Diagram
- [Laravel Documentation](https://laravel.com/docs/11.x)
- [JWT Auth Documentation](https://jwt-auth.readthedocs.io/)

---

## API Response Format

### Success Response
```json
{
    "success": true,
    "message": "Thao tác thành công. / Operation successful.",
    "data": { ... }
}
```

### Error Response
```json
{
    "success": false,
    "message": "Lỗi xảy ra. / Error occurred.",
    "data": null
}
```

### HTTP Status Codes

| Code | Ý nghĩa |
|------|---------|
| 200 | Success |
| 201 | Created |
| 400 | Bad Request |
| 401 | Unauthorized |
| 403 | Forbidden |
| 404 | Not Found |
| 422 | Validation Error |
| 500 | Server Error |

---

## Troubleshooting

### Lỗi thường gặp

**"No application encryption key has been specified"**
```bash
php artisan key:generate
```

**"The JWT secret key is not set"**
```bash
php artisan jwt:secret
```

**403 Forbidden khi approve/reject**
- Kiểm tra user có đúng role Manager/Admin
- Kiểm tra token còn hợp lệ
- Xem logs trong storage/logs/laravel.log

**Clear all caches**
```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
```

---

Made with Laravel 11.x | PHP 8.3 | MySQL 8.0
