# TÀI LIỆU HỆ THỐNG BACKEND - LEAVE APPLICATION SYSTEM

Hệ thống quản lý nghỉ phép nội bộ - RESTful API

Phiên bản: 1.0  
Framework: Laravel 11.x  
Ngôn ngữ: PHP 8.3  
Database: MySQL 8.0
dùng laragon
---

## MỤC LỤC

1. [Tổng quan hệ thống](#1-tổng-quan-hệ-thống)
2. [Cài đặt và cấu hình](#2-cài-đặt-và-cấu-hình)
3. [Cấu trúc thư mục](#3-cấu-trúc-thư-mục)
4. [Database Schema](#4-database-schema)
5. [API Endpoints](#5-api-endpoints)
6. [Authentication Flow](#6-authentication-flow)
7. [Authorization Flow](#7-authorization-flow)
8. [Service Layer Architecture](#8-service-layer-architecture)
9. [Validation Rules](#9-validation-rules)
10. [Error Handling](#10-error-handling)
11. [Testing Guide](#11-testing-guide)
12. [Deployment Guide](#12-deployment-guide)
13. [Troubleshooting](#13-troubleshooting)

---

## 1. TỔNG QUAN HỆ THỐNG

### 1.1. Giới thiệu

Hệ thống quản lý nghỉ phép giúp:
- Nhân viên tạo đơn xin nghỉ phép
- Quản lý duyệt hoặc từ chối đơn
- Admin quản trị toàn hệ thống

### 1.2. Modules chính

**Module Authentication**
- Đăng ký tài khoản
- Đăng nhập với JWT
- Đăng xuất
- Làm mới token
- Lấy thông tin user

**Module Leave Applications**
- Tạo đơn nghỉ phép
- Xem danh sách và chi tiết
- Cập nhật đơn
- Duyệt, từ chối, hủy đơn
- Kiểm tra trùng ngày nghỉ

### 1.3. Công nghệ sử dụng

```
PHP: 8.3.x
Laravel: 11.x
MySQL: 8.0/8.4
JWT Auth: php-open-source-saver/jwt-auth
Composer: 2.x
```

---

## 2. CÀI ĐẶT VÀ CẤU HÌNH

### 2.1. Yêu cầu hệ thống

- PHP >= 8.3
- Composer >= 2.x
- MySQL >= 8.0
- Extensions: OpenSSL, PDO, Mbstring, Tokenizer, XML

### 2.2. Cài đặt

```bash
# Clone repository
git clone <repository-url>
cd leave-system/backend

# Cài đặt dependencies
composer install

# Tạo file môi trường
cp .env.example .env

# Generate app key
php artisan key:generate

# Generate JWT secret
php artisan jwt:secret
```

### 2.3. Cấu hình Database

Mở file `.env` và cấu hình:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=leave_system
DB_USERNAME=root
DB_PASSWORD=
```

### 2.4. Chạy Migrations và Seeders

```bash
# Chạy migrations
php artisan migrate

# Chạy seeders
php artisan db:seed

# Hoặc chạy một lần
php artisan migrate --seed
```

### 2.5. Khởi động Server

```bash
# Development server
php artisan serve

# Server sẽ chạy tại http://127.0.0.1:8000
```

### 2.6. Tài khoản mẫu

Sau khi seed, các tài khoản sau sẽ được tạo:

| Role | Email | Password |
|------|-------|----------|
| Admin | admin@example.com | password123 |
| Manager | manager1@example.com | password123 |
| Manager | manager2@example.com | password123 |
| Employee | employee1@example.com | password123 |
| Employee | employee2@example.com | password123 |

---

## 3. CẤU TRÚC THƯ MỤC

```
backend/
├── app/
│   ├── Enums/
│   │   ├── LeaveApplicationStatus.php  # Enum trạng thái đơn
│   │   ├── LeaveApplicationType.php    # Enum loại nghỉ phép
│   │   └── UserType.php                # Enum quyền người dùng
│   ├── Helpers/
│   │   └── ResponseHelper.php          # Helper format API response
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── AuthController.php      # Controller Authentication
│   │   │   └── LeaveApplicationController.php
│   │   ├── Middleware/
│   │   │   └── CheckRole.php           # Middleware check role
│   │   └── Requests/
│   │       ├── CreateLeaveApplicationRequest.php
│   │       ├── UpdateLeaveApplicationRequest.php
│   │       ├── RejectLeaveRequest.php
│   │       ├── RegisterRequest.php
│   │       └── LoginRequest.php
│   ├── Models/
│   │   ├── User.php                    # Model User
│   │   └── LeaveApplication.php        # Model LeaveApplication
│   ├── Policies/
│   │   └── LeaveApplicationPolicy.php  # Policy phân quyền
│   ├── Providers/
│   │   ├── AppServiceProvider.php      # Service Provider chính
│   │   └── AuthServiceProvider.php     # Auth Service Provider
│   ├── Rules/
│   │   └── NoOverlapDates.php          # Custom validation rule
│   └── Services/
│       ├── AuthService.php             # Service xử lý auth logic
│       └── LeaveApplicationService.php # Service xử lý leave logic
├── bootstrap/
│   └── app.php                         # Application bootstrap
├── config/
│   ├── auth.php                        # JWT guard config
│   ├── cors.php                        # CORS config
│   └── jwt.php                         # JWT config
├── database/
│   ├── migrations/
│   │   ├── 2024_01_01_000001_create_users_table.php
│   │   └── 2024_01_01_000002_create_leave_applications_table.php
│   └── seeders/
│       └── UserSeeder.php              # Seeder users mẫu
├── routes/
│   └── apis/
│       ├── auth.php                    # Auth routes
│       └── leaveApplication.php        # Leave application routes
├── storage/
│   └── logs/
│       └── laravel.log                 # Application logs
└── tests/
    └── Feature/
        ├── AuthenticationTest.php
        ├── LeaveApplicationWorkflowTest.php
        └── LeaveApplicationAuthorizationTest.php
```

---

## 4. DATABASE SCHEMA

### 4.1. Bảng `users`

Lưu trữ thông tin người dùng

| Column | Type | Attributes | Mô tả |
|--------|------|------------|-------|
| id | CHAR(10) | PRIMARY KEY | ID người dùng |
| name | VARCHAR(255) | NOT NULL | Họ tên |
| email | VARCHAR(255) | UNIQUE | Email đăng nhập |
| password | VARCHAR(255) | NOT NULL | Mật khẩu đã mã hóa |
| type | TINYINT(1) | DEFAULT 2 | 0=Admin, 1=Manager, 2=Employee |
| created_at | TIMESTAMP | NULLABLE | Thời gian tạo |
| updated_at | TIMESTAMP | NULLABLE | Thời gian cập nhật |
| deleted_at | TIMESTAMP | NULLABLE | Thời gian xóa mềm |
| created_by | CHAR(10) | NULLABLE | ID người tạo |
| updated_by | CHAR(10) | NULLABLE | ID người cập nhật |
| deleted_by | CHAR(10) | NULLABLE | ID người xóa |

**Indexes:**
- PRIMARY KEY: id
- UNIQUE: email
- INDEX: type, deleted_at

### 4.2. Bảng `leave_applications`

Lưu trữ đơn xin nghỉ phép

| Column | Type | Attributes | Mô tả |
|--------|------|------------|-------|
| id | CHAR(10) | PRIMARY KEY | Mã đơn |
| user_id | CHAR(10) | FOREIGN KEY | ID người tạo đơn |
| start_date | DATE | NOT NULL | Ngày bắt đầu |
| end_date | DATE | NOT NULL | Ngày kết thúc |
| total_days | FLOAT | NOT NULL | Tổng số ngày nghỉ |
| reason | TEXT | NULLABLE | Lý do nghỉ |
| type | VARCHAR(50) | NOT NULL | annual/sick/unpaid |
| status | VARCHAR(50) | DEFAULT 'new' | Trạng thái đơn |
| created_at | TIMESTAMP | NULLABLE | Thời gian tạo |
| updated_at | TIMESTAMP | NULLABLE | Thời gian cập nhật |
| deleted_at | TIMESTAMP | NULLABLE | Thời gian xóa mềm |
| created_by | CHAR(10) | NULLABLE | ID người tạo |
| updated_by | CHAR(10) | NULLABLE | ID người cập nhật |
| deleted_by | CHAR(10) | NULLABLE | ID người xóa |

**Foreign Keys:**
- user_id REFERENCES users(id) ON DELETE CASCADE

**Indexes:**
- PRIMARY KEY: id
- INDEX: user_id, status, start_date, end_date, deleted_at
- COMPOSITE INDEX: (user_id, start_date, end_date)

### 4.3. Enum Values

**users.type:**
- 0: Admin (Quản trị viên)
- 1: Manager (Quản lý)
- 2: Employee (Nhân viên)

**leave_applications.type:**
- annual: Nghỉ phép năm
- sick: Nghỉ ốm
- unpaid: Nghỉ không lương

**leave_applications.status:**
- new: Đơn mới tạo
- pending: Đang chờ duyệt
- approved: Đã duyệt
- rejected: Bị từ chối
- cancelled: Đã hủy

### 4.4. Relationships

```
User (1) ----< (N) LeaveApplication
- User hasMany LeaveApplication
- LeaveApplication belongsTo User
```

---

## 5. API ENDPOINTS

### 5.1. Base URL

```
Development: http://127.0.0.1:8000/api
Production: https://your-domain.com/api
```

### 5.2. Authentication Endpoints

**POST /auth/register**
- Đăng ký tài khoản mới
- Public endpoint
- Request body:
  ```json
  {
    "name": "Nguyễn Văn A",
    "email": "example@email.com",
    "password": "password123",
    "password_confirmation": "password123",
    "type": 2
  }
  ```
- Response: Token + User info

**POST /auth/login**
- Đăng nhập
- Public endpoint
- Request body:
  ```json
  {
    "email": "example@email.com",
    "password": "password123"
  }
  ```
- Response: JWT Token

**POST /auth/logout**
- Đăng xuất
- Protected endpoint (cần token)
- Response: Success message

**POST /auth/refresh**
- Làm mới token
- Protected endpoint (cần token)
- Response: Token mới

**GET /auth/me**
- Lấy thông tin user hiện tại
- Protected endpoint (cần token)
- Response: User info

### 5.3. Leave Application Endpoints

Tất cả endpoints dưới đây đều yêu cầu token (Protected)

**GET /leave-applications**
- Lấy danh sách đơn nghỉ phép
- Supports: Pagination, Filtering
- Query params:
  - status: Lọc theo trạng thái
  - user_id: Lọc theo user
  - month: Lọc theo tháng
  - year: Lọc theo năm
- Response: Paginated list

**GET /leave-applications/{id}**
- Xem chi tiết một đơn
- Response: Leave application with user info

**POST /leave-applications**
- Tạo đơn mới
- Request body:
  ```json
  {
    "start_date": "2024-02-10",
    "end_date": "2024-02-12",
    "reason": "Nghỉ phép năm",
    "type": "annual"
  }
  ```
- Response: Created leave application

**PUT /leave-applications/{id}**
- Cập nhật đơn
- Chỉ được update khi status = 'new'
- Request body: Giống POST
- Response: Updated leave application

**DELETE /leave-applications/{id}**
- Xóa đơn (soft delete)
- Admin only
- Response: Success message

**POST /leave-applications/{id}/approve**
- Duyệt đơn
- Manager/Admin only
- Response: Approved leave application

**POST /leave-applications/{id}/reject**
- Từ chối đơn
- Manager/Admin only
- Request body:
  ```json
  {
    "reason": "Không đủ ngày phép"
  }
  ```
- Response: Rejected leave application

**POST /leave-applications/{id}/cancel**
- Hủy đơn
- Owner/Admin only
- Không được hủy đơn đã approved/rejected
- Response: Cancelled leave application

### 5.4. Response Format

**Success Response:**
```json
{
  "success": true,
  "message": "Thao tác thành công. / Operation successful.",
  "data": { ... }
}
```

**Error Response:**
```json
{
  "success": false,
  "message": "Lỗi xảy ra. / Error occurred.",
  "data": null
}
```

### 5.5. HTTP Status Codes

| Code | Ý nghĩa |
|------|---------|
| 200 | Success |
| 201 | Created |
| 400 | Bad Request |
| 401 | Unauthorized (chưa đăng nhập) |
| 403 | Forbidden (không có quyền) |
| 404 | Not Found |
| 422 | Validation Error |
| 500 | Server Error |

---

## 6. AUTHENTICATION FLOW

### 6.1. Quy trình đăng nhập

```
1. Client gửi email + password
   POST /api/auth/login

2. AuthController nhận request
   → Validate qua LoginRequest

3. AuthService kiểm tra credentials
   → Dùng auth()->attempt()

4. JWT tạo token
   → Trả về access_token

5. Client lưu token (localStorage/cookie)

6. Requests tiếp theo gửi kèm token
   Header: Authorization: Bearer {token}

7. Middleware auth:api verify token
   → Inject user vào request
```

### 6.2. Làm mới token

```
1. Token sắp hết hạn (expires_in seconds)

2. Client gọi POST /api/auth/refresh
   Header: Authorization: Bearer {old_token}

3. AuthService tạo token mới
   → Dùng auth()->refresh()

4. Trả về access_token mới

5. Client thay thế token cũ bằng token mới
```

### 6.3. Đăng xuất

```
1. Client gọi POST /api/auth/logout
   Header: Authorization: Bearer {token}

2. AuthService invalidate token
   → Dùng auth()->logout()

3. Token bị vô hiệu hóa

4. Client xóa token khỏi storage
```

---

## 7. AUTHORIZATION FLOW

### 7.1. Policy System

Laravel Policy là cơ chế phân quyền dựa trên Model.

**Flow kiểm tra quyền:**

```
1. Request đến route có middleware can
   →middleware('can:approve,leaveApplication')

2. SubstituteBindings middleware resolve Model
   → {leaveApplication} → LeaveApplication instance

3. Middleware can kiểm tra quyền
   → Gọi Gate::check('approve', $leaveApplication)

4. Gate tìm Policy tương ứng
   → LeaveApplicationPolicy

5. Chạy before() method trước
   → Admin bypass tất cả checks

6. Nếu before() return null
   → Chạy method tương ứng (approve())

7. Policy method return true/false
   → true: Cho phép
   → false: 403 Forbidden
```

### 7.2. Phân quyền theo Role

| Action | Employee | Manager | Admin |
|--------|----------|---------|-------|
| List (own) | Có | Có | Có |
| List (all) | Không | Có | Có |
| View (own) | Có | Có | Có |
| View (other) | Không | Có | Có |
| Create | Có | Có | Có |
| Update (own, new) | Có | Không | Có |
| Update (other) | Không | Không | Có |
| Delete | Không | Không | Có |
| Approve | Không | Có | Có |
| Reject | Không | Có | Có |
| Cancel (own) | Có | Không | Có |

### 7.3. Policy Methods

**before()**
- Chạy trước tất cả methods
- Admin bypass toàn bộ
- Return true/false/null

**viewAny()**
- Kiểm tra quyền xem list
- Tất cả user đều được phép
- Service filter theo role

**view()**
- Kiểm tra quyền xem chi tiết
- Manager xem tất cả
- Employee chỉ xem của mình

**create()**
- Kiểm tra quyền tạo đơn
- Tất cả user đều được phép

**update()**
- Kiểm tra quyền cập nhật
- Owner + status='new'
- Manager không được update

**delete()**
- Kiểm tra quyền xóa
- Chỉ Admin

**approve()**
- Kiểm tra quyền duyệt
- Manager và Admin

**reject()**
- Kiểm tra quyền từ chối
- Manager và Admin

**cancel()**
- Kiểm tra quyền hủy
- Owner (chưa approved/rejected)
- Manager không được hủy

---

## 8. SERVICE LAYER ARCHITECTURE


### 8.2. LeaveApplicationService

**Methods chính:**

**getList($filters)**
- Lấy danh sách với pagination
- Filter theo status, user_id, month, year
- Eager load relationships
- Filter theo role user

**getDetail($id)**
- Lấy chi tiết một đơn
- Eager load user information

**create($data)**
- Tạo đơn mới
- Validate overlap dates
- Tính total_days
- Set created_by

**update($id, $data)**
- Cập nhật đơn
- Chỉ update khi status='new'
- Tính lại total_days

**delete($id)**
- Soft delete đơn
- Set deleted_by

**approve($id)**
- Duyệt đơn
- Chuyển status thành 'approved'

**reject($id, $reason)**
- Từ chối đơn
- Lưu lý do từ chối

**cancel($id)**
- Hủy đơn
- Chuyển status thành 'cancelled'

### 8.3. AuthService

**Methods chính:**

**register($data)**
- Tạo user mới
- Hash password
- Tạo token và return

**login($credentials)**
- Attempt đăng nhập
- Return token nếu thành công

**logout()**
- Invalidate token hiện tại

**refresh()**
- Tạo token mới từ token cũ

**me()**
- Return user hiện tại

---

## 9. VALIDATION RULES

### 9.1. Form Request Validation

Laravel Form Requests tự động validate input trước khi vào Controller.



**UpdateLeaveApplicationRequest:**

Tương tự CreateLeaveApplicationRequest nhưng tất cả fields đều optional.





### 9.2. Custom Validation Rules

**NoOverlapDates:**

Kiểm tra đơn mới không trùng với đơn đã có (status != rejected/cancelled).



### 9.3. Validation Messages

Tất cả validation messages đều bilingual (VI/EN):



---

## 10. ERROR HANDLING

### 10.1. Exception Handlers

File `bootstrap/app.php` định nghĩa how to handle exceptions.

**Authentication Errors (401):**
- AuthenticationException: Chưa đăng nhập
- TokenExpiredException: Token hết hạn
- TokenInvalidException: Token không hợp lệ
- JWTException: Lỗi JWT chung

**Authorization Errors (403):**
- AccessDeniedHttpException: Không có quyền

**Not Found Errors (404):**
- ModelNotFoundException: Model không tồn tại
- NotFoundHttpException: Route/Resource không tồn tại

**Validation Errors (422):**
- ValidationException: Dữ liệu không hợp lệ
- UnprocessableEntityHttpException: Không thể xử lý



## 11. TESTING GUIDE


### 11.2. Test Files

**AuthenticationTest.php:**
- Test đăng ký
- Test đăng nhập thành công/thất bại
- Test đăng xuất
- Test refresh token
- Test lấy thông tin user

**LeaveApplicationWorkflowTest.php:**
- Test tạo đơn
- Test xem danh sách
- Test cập nhật đơn
- Test workflow approve/reject/cancel

**LeaveApplicationAuthorizationTest.php:**
- Test phân quyền approve (Manager có thể, Employee không)
- Test phân quyền reject
- Test phân quyền cancel
- Test phân quyền update


---



## PHỤ LỤC

### A. Naming Conventions

- Class: PascalCase (UserController)
- Method: camelCase (getUserById)
- Variable: snake_case ($user_id)
- Route: kebab-case (/leave-applications)
- Database: snake_case (leave_applications)

### B. Useful Commands

```bash
# Tạo controller
php artisan make:controller NameController

# Tạo model với migration
php artisan make:model Name -m

# Tạo request
php artisan make:request NameRequest

# Tạo policy
php artisan make:policy NamePolicy --model=Name

# Tạo test
php artisan make:test NameTest

# Rollback migrations
php artisan migrate:rollback

# Reset database
php artisan migrate:fresh --seed
```

### C. Postman Collection

Import file `leave-system-postman.json` vào Postman để test APIs.

Environment variables:
- base_url: http://127.0.0.1:8000/api
- token: Auto-set sau khi login
- leave_id: Auto-set sau khi create

---

