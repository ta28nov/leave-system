# HUONG DAN DEPLOY LARAVEL + VUE.JS LEN RENDER VOI AIVEN MYSQL

## TONG QUAN

He thong Leave System duoc deploy voi kien truc:
- Frontend: Vue.js (build tinh trong /dist)
- Backend: Laravel API (PHP 8.2 + PHP-FPM)
- Database: Aiven MySQL voi SSL  
- Web Server: Nginx (phuc vu frontend + proxy API)
- Platform: Render (Docker container)

---

## CHUAN BI

Truoc khi bat dau, dam bao ban da co:

1. Tai khoan Render.com (Free tier OK)
2. Tai khoan Aiven MySQL (da setup)
3. Repository GitHub/GitLab (code da push len)
4. CA Certificate tu Aiven da luu tai backend/storage/certs/aiven-ca.pem

---

## BUOC 1: CHUAN BI TREN LOCAL

### 1.1. Build Frontend

```bash
cd frontend
npm install
npm run build
```

Kiem tra: Thu muc frontend/dist da duoc tao va chua cac file index.html, assets/, etc.

### 1.2. Kiem Tra CA Certificate

```bash
cat backend/storage/certs/aiven-ca.pem
```

Kiem tra: File phai bat dau bang -----BEGIN CERTIFICATE-----

### 1.3. Test Ket Noi Database Local (Tuy chon)

Cap nhat backend/.env:

```env
DB_CONNECTION=mysql
DB_HOST=leave-system-leave-system.e.aivencloud.com
DB_PORT=14788
DB_DATABASE=defaultdb
DB_USERNAME=avnadmin
DB_PASSWORD=YOUR_AIVEN_PASSWORD
DB_SSL_MODE=REQUIRED
DB_SSL_CA=storage/certs/aiven-ca.pem
```

Chay migration de test:

```bash
cd backend
php artisan migrate
```
// buộc phải set được ip trong aiven console allow network.
Mong doi: Migrations chay thanh cong, khong co loi SSL.

### 1.4. Push Code Len Repository

```bash
git add .
git commit -m "Them cau hinh deployment cho Render"
git push origin main
```

---

## BUOC 2: DEPLOY LEN RENDER

### 2.1. Tao Web Service Moi

1. Dang nhap vao Render Dashboard (https://dashboard.render.com/)
2. Click "New +" → "Web Service"
3. Ket noi repository GitHub/GitLab cua ban
4. Chon repository leave-system

### 2.2. Cau Hinh Service

| Cai Dat | Gia Tri |
|---------|---------|
| Name | leave-system (hoac ten ban muon) |
| Region | Singapore (gan Aiven server nhat) |
| Branch | main |
| Root Directory | (de trong) |
| Runtime | Docker |
| Dockerfile Path | Dockerfile |
| Docker Build Context | . |
| Instance Type | Free |

### 2.3. Cau Hinh Bien Moi Truong

Click "Advanced" → "Add Environment Variable", them cac bien sau:

#### Cai Dat Ung Dung Laravel

```
APP_NAME=LeaveSystem
APP_ENV=production
APP_DEBUG=false
APP_URL=https://leave-system.onrender.com
APP_KEY=base64:YOUR_APP_KEY_HERE
```

Tao APP_KEY: Chay `php artisan key:generate --show` tren local va copy ket qua.

#### Cau Hinh Database (Aiven)

```
DB_CONNECTION=mysql
DB_HOST=leave-system-leave-system.e.aivencloud.com
DB_PORT=14788
DB_DATABASE=defaultdb
DB_USERNAME=avnadmin
DB_PASSWORD=YOUR_AIVEN_PASSWORD
DB_SSL_MODE=REQUIRED
DB_SSL_CA=storage/certs/aiven-ca.pem
```

#### Session va Cache

```
SESSION_DRIVER=database
SESSION_LIFETIME=120
CACHE_STORE=database
QUEUE_CONNECTION=database
```

#### Logging

```
LOG_CHANNEL=stack
LOG_LEVEL=error
```

### 2.4. Bat Dau Deploy

Click "Create Web Service" va doi Render build + deploy (khoang 5-10 phut lan dau).

---

## BUOC 3: KIEM TRA

### 3.1. Kiem Tra Logs

Trong Render Dashboard → Logs:

```
[KHOI DONG] Dang khoi dong ung dung Laravel...
[DATABASE] Dang doi ket noi database...
[MIGRATION] Dang chay migrations...
[OPTIMIZE] Dang toi uu hoa Laravel...
[NGINX] Dang khoi dong Nginx...
[PHP-FPM] Dang khoi dong PHP-FPM...
```

Mong doi: Khong co loi, tat ca migrations chay thanh cong.

### 3.2. Test Frontend

Truy cap URL Render cua ban (vi du: https://leave-system.onrender.com):

- Trang login hien thi dung
- CSS/JS load khong loi
- Console khong co loi 404

### 3.3. Test API Backend

```bash
curl https://leave-system.onrender.com/api/health
```

Mong doi: Response tu Laravel backend.

### 3.4. Test Ket Noi Database

Thu login vao he thong:

- Login thanh cong → Ket noi database OK
- Du lieu hien thi dung → Migrations da chay

---

## CACH SUA LOI

### Loi: exec format error

Nguyen nhan: File docker-entrypoint.sh co CRLF line endings (Windows).

Giai phap:
1. Mo file docker-entrypoint.sh bang VSCode
2. Nhin goc duoi ben phai, click vao "CRLF"
3. Chon "LF"
4. Save va push lai

### Loi: SQLSTATE[HY000] [2002] Connection refused

Nguyen nhan: Database chua san sang khi container khoi dong.

Giai phap: Tang thoi gian sleep trong docker-entrypoint.sh (dong 11):

```bash
sleep 10  # Tang tu 5 len 10
```

### Loi: SSL connection error

Nguyen nhan: CA certificate khong dung hoac khong ton tai.

Giai phap:
1. Kiem tra file backend/storage/certs/aiven-ca.pem da duoc commit vao Git
2. Tai lai certificate tu Aiven console
3. Xac nhan bien moi truong DB_SSL_CA=storage/certs/aiven-ca.pem

### Loi: Permission denied khi ghi log

Nguyen nhan: Thu muc storage/ khong co quyen ghi.

Giai phap: Da duoc fix trong docker-entrypoint.sh:

```bash
chown -R www-data:www-data /var/www/html/storage
```

Neu van loi, kiem tra Dockerfile co chay lenh nay khong.

### Loi: Frontend hien thi nhung API 404

Nguyen nhan: Nginx routing khong dung.

Giai phap: Kiem tra file nginx.conf:

```nginx
location /api {
    try_files $uri $uri/ /index.php?$query_string;
}
```

---

## DEPLOY LAI SAU KHI CAP NHAT CODE

Moi khi ban push code moi len Git:

1. Frontend co thay doi? → Nho build lai:
   ```bash
   cd frontend
   npm run build
   git add dist/
   git commit -m "Cap nhat frontend"
   ```

2. Backend co migration moi? → Khong can lam gi, docker-entrypoint.sh tu dong chay migrations.

3. Push len Git:
   ```bash
   git push origin main
   ```

4. Render se tu dong detect va deploy lai (khoang 3-5 phut).

---

## LUU Y VE HIEU SUAT

### 1. Bat OPcache (Production)

Them vao Dockerfile sau dong docker-php-ext-install:

```dockerfile
RUN docker-php-ext-install opcache
```

Tao file backend/php.ini:

```ini
opcache.enable=1
opcache.memory_consumption=128
opcache.max_accelerated_files=10000
```

### 2. CDN cho Static Assets (Tuy chon)

Frontend da build, neu muon toc do tot hon, host dist/ len Cloudflare Pages hoac Vercel.

---

## DANH SACH KIEM TRA BAO MAT

- [x] APP_DEBUG=false trong production
- [x] APP_ENV=production
- [x] SSL enabled cho database
- [x] Source map disabled (sourcemap: false)
- [ ] Can nhac them rate limiting (Laravel Throttle)
- [ ] Can nhac them CORS configuration neu frontend tach domain

---

## HO TRO

Neu gap van de khac, kiem tra:

1. Render Logs: Dashboard → Logs → Xem error message
2. Laravel Logs: Khong truy cap truc tiep duoc, nhung co the config log qua external service (Papertrail, Sentry)

---

DEPLOYMENT HOAN THANH! He thong Leave System da live tren Render voi Aiven MySQL.
