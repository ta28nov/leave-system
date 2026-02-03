
## WORKFLOW TEST HOÀN CHỈNH

### Bước 1: Import Collection vào Postman
1. Mở Postman
2. Click **Import** (góc trên trái)
3. Chọn file `leave-system-postman.json`
4. Click **Import**

### Bước 2: Kiểm tra Variables
1. Click vào collection name (Leave System API)
2. Chọn tab **Variables**
3. Xác nhận `base_url` = `http://127.0.0.1:8000/api`

### Bước 3: Test với Employee

**3.1. Login Employee**
- Request: Auth → Login (Employee)
- Click **Send**
- Kiểm tra:
  - Status: 200 OK
  - Response có `access_token`
  - Console log: "Token updated for Employee"
  - Tab Variables: `token` đã được cập nhật

**3.2. Xem Profile**
- Request: Auth → Get Me Profile
- Click **Send**
- Kiểm tra:
  - Status: 200 OK
  - Response có thông tin user (type=2)

**3.3. Tạo Đơn Nghỉ**
- Request: Leave Applications → Create Request (Annual)
- Click **Send**
- Kiểm tra:
  - Status: 201 Created
  - Response có `id` (ví dụ: "VL0CEXTPMH")
  - Console log: "Created Leave ID: VL0CEXTPMH"
  - Tab Variables: `leave_id` đã được cập nhật

**3.4. Xem Chi Tiết Đơn**
- Request: Leave Applications → Get Detail
- URL tự động sử dụng `{{leave_id}}`
- Click **Send**
- Kiểm tra:
  - Status: 200 OK
  - Response có thông tin đơn vừa tạo

**3.5. Cập Nhật Đơn**
- Request: Leave Applications → Update Request
- Click **Send**
- Kiểm tra:
  - Status: 200 OK (nếu status="new")
  - Hoặc 403 (nếu đã approved/rejected)

**3.6. Hủy Đơn**
- Request: Leave Applications → Cancel Request (Employee)
- Click **Send**
- Kiểm tra:
  - Status: 200 OK
  - Response status="cancelled"

### Bước 4: Test với Manager

**4.1. Login Manager**
- Request: Auth → Login (Manager)
- Click **Send**
- Token tự động cập nhật

**4.2. Tạo Đơn mới (với Employee account trước)**
- Login lại Employee
- Tạo đơn mới (để có đơn để Manager approve)

**4.3. Login Manager lại**
- Request: Auth → Login (Manager)
- Token được cập nhật

**4.4. Duyệt Đơn**
- Request: Leave Applications → Approve Request (Manager)
- URL sử dụng `{{leave_id}}` từ đơn vừa tạo
- Click **Send**
- Kiểm tra:
  - Status: 200 OK
  - Response status="approved"

**4.5. Từ Chối Đơn**
- Tạo đơn mới trước
- Request: Leave Applications → Reject Request (Manager)
- Body có `reason`: "Từ chối do nhân sự thiếu hụt"
- Click **Send**
- Kiểm tra:
  - Status: 200 OK
  - Response status="rejected"

### Bước 5: Test với Admin

**5.1. Login Admin**
- Request: Auth → Login (Admin)
- Click **Send**

**5.2. Xóa Đơn**
- Request: Leave Applications → Delete Request (Admin)
- Click **Send**
- Kiểm tra:
  - Status: 200 OK
  - Đơn bị soft delete

---

## TEST PHÂN QUYỀN (Authorization)

### Test 1: Employee không được Approve
1. Login Employee
2. Tạo đơn mới
3. Thử Approve Request (Manager)
4. Kết quả mong đợi: **403 Forbidden**

### Test 2: Manager không được Update
1. Login Manager
2. Tạo đơn (bằng Employee trước, rồi login Manager)
3. Thử Update Request
4. Kết quả mong đợi: **403 Forbidden**

### Test 3: Employee không được Delete
1. Login Employee
2. Thử Delete Request (Admin)
3. Kết quả mong đợi: **403 Forbidden**

---

