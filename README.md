<<<<<<< HEAD
# SPORTZONE — Sports E-Commerce

Nền tảng thương mại điện tử bán đồ thể thao. Stack: **PHP 8.1+**, **MySQL 8**, **Vanilla JS**, **PDO**.

---

## Yêu cầu hệ thống

| Thành phần | Phiên bản |
|---|---|
| PHP | ≥ 8.1 |
| MySQL | ≥ 8.0 |
| Apache | ≥ 2.4 (với `mod_rewrite`) |
| Composer | ≥ 2 |

---

## Cài đặt

### 1. Clone và cài dependencies

```bash
git clone <repo-url> sportzone
cd sportzone
composer install
```

### 2. Cấu hình môi trường

```bash
cp .env.example .env
```

Mở `.env` và điền thông tin:

```env
APP_ENV=development
APP_DEBUG=true
APP_URL=http://localhost/sportzone/public
APP_KEY=<random-32-char-string>

DB_HOST=127.0.0.1
DB_NAME=sportzone
DB_USER=root
DB_PASS=your_password
```

> **Tạo APP_KEY:**
> ```bash
> php -r "echo bin2hex(random_bytes(16)) . PHP_EOL;"
> ```

### 3. Tạo database

```bash
mysql -u root -p -e "SOURCE database/schema.sql"
```

Hoặc import qua phpMyAdmin: chọn file `database/schema.sql`.

**Tài khoản mặc định (từ seed data):**
- **Admin:** `admin@sportzone.vn` / `Admin@1234`
- **Customer:** `nguyenvanan@gmail.com` / `Customer@1234`

> ⚠️ Đổi mật khẩu ngay sau khi cài đặt.

### 4. Cấu hình Apache Virtual Host

```apache
<VirtualHost *:80>
    ServerName sportzone.local
    DocumentRoot "C:/path/to/sportzone/public"

    <Directory "C:/path/to/sportzone/public">
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

Kích hoạt `mod_rewrite` và khởi động lại Apache.

### 5. Phân quyền thư mục

```bash
# Linux/Mac
chmod -R 755 storage/
chmod -R 755 public/assets/

# Windows: đảm bảo user Apache có quyền ghi vào storage/
```

### 6. Truy cập

| URL | Mô tả |
|---|---|
| `http://sportzone.local/` | Trang chủ |
| `http://sportzone.local/login` | Đăng nhập |
| `http://sportzone.local/admin` | Admin panel |

---

## Cấu trúc thư mục

```
sportzone/
├── app/
│   ├── Controllers/        # Request handlers
│   │   └── Admin/          # Admin controllers
│   ├── Core/               # Framework core
│   │   ├── Middleware/     # Auth, CSRF, Rate limit
│   │   ├── App.php         # Bootstrap
│   │   ├── Database.php    # PDO singleton
│   │   ├── Env.php         # .env loader
│   │   ├── Logger.php      # File logger
│   │   ├── Request.php     # HTTP request
│   │   ├── Response.php    # HTTP response
│   │   ├── Router.php      # URL router
│   │   ├── Session.php     # Session manager
│   │   └── helpers.php     # Global functions
│   ├── Helpers/
│   │   ├── CsrfToken.php   # CSRF token
│   │   ├── Sanitizer.php   # Output escaping
│   │   └── Validator.php   # Input validation
│   ├── Models/             # Data models (kế thừa BaseModel)
│   └── Views/              # PHP templates
│       ├── admin/          # Admin views
│       ├── errors/         # 404, 500
│       ├── layouts/        # main.php, admin.php
│       └── partials/       # header.php, footer.php
├── config/
│   ├── app.php             # Application config
│   └── database.php        # PDO config
├── database/
│   └── schema.sql          # DDL + seed data
├── public/                 # ← Document root
│   ├── assets/
│   │   ├── css/main.css    # Design system
│   │   └── js/             # core/ + cart.js
│   ├── .htaccess           # URL rewriting
│   └── index.php           # Front controller
├── routes/
│   ├── api.php             # JSON API routes
│   └── web.php             # HTML routes
├── storage/
│   ├── cache/
│   ├── logs/               # app-YYYY-MM-DD.log
│   └── uploads/            # User uploaded files
├── .env.example
├── .gitignore
└── composer.json
```

---

## Bảo mật

- **Credentials** không được commit (`.env` trong `.gitignore`).
- **CSRF** token xác thực mọi form POST.
- **Session fixation** ngăn bằng `session_regenerate_id()` sau login.
- **Passwords** hash bằng `password_hash(PASSWORD_BCRYPT)`.
- **SQL injection** ngăn bằng PDO prepared statements.
- **XSS** ngăn bằng `htmlspecialchars()` / `e()` ở mọi output.
- **Rate limiting** trên login và checkout.
- **Admin** phân quyền riêng qua `AdminMiddleware`.

---

## Phát triển

### Thêm Route mới

```php
// routes/web.php
$router->get('/shop', [ShopController::class, 'index']);
$router->post('/cart/items', [CartController::class, 'add'], [CsrfMiddleware::class]);
```

### Thêm Controller

```php
namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;

final class ShopController
{
    public function index(Request $request, Response $response): void
    {
        $response->view('shop/index', ['title' => 'Cửa hàng'])->send();
    }
}
```

### Thêm Model

```php
namespace App\Models;

final class Product extends BaseModel
{
    protected string $table    = 'products';
    protected array  $fillable = ['name', 'slug', 'category_id', 'brand_id', 'status'];
}
```

### JSON API Response

```php
// Success
$response->success(['products' => $data], 'Lấy dữ liệu thành công')->send();

// Error
$response->error('Validation failed', ['email' => ['Email không hợp lệ']], 422)->send();
```

---

## Logs

Logs lưu tại `storage/logs/app-YYYY-MM-DD.log`.

```bash
tail -f storage/logs/app-2025-10-14.log
```

---

## Giai đoạn phát triển

| Phase | Nội dung |
|---|---|
| ✅ Phase 1 | Skeleton, Router, PDO, Middleware |
| ⬜ Phase 2 | Product catalog, Shop, Search |
| ⬜ Phase 3 | Auth, Cart, Coupon |
| ⬜ Phase 4 | Checkout, Order, Payment |
| ⬜ Phase 5 | Admin panel đầy đủ |
| ⬜ Phase 6 | Kiểm thử, tối ưu, deploy |
=======
# Super_Sport
>>>>>>> 52d666a4e5f421c188e5a147735daaa647442164
