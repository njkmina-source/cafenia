<?php
/**
 * پیکربندی اصلی سامانه منوی دیجیتال کافه
 * توسعه داده شده برای پایداری و امنیت بالا بر روی هاست اشتراکی cPanel
 */

// شروع امن نشست (Session)
if (session_status() === PHP_SESSION_NONE) {
    // افزایش امنیت کوکی‌های نشست
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
        ini_set('session.cookie_secure', 1);
    }
    session_start();
}

// تنظیمات اتصال به دیتابیس MySQL
define('DB_HOST', 'localhost');
define('DB_NAME', 'my_cafe_db'); // نام دیتابیس در cPanel
define('DB_USER', 'my_cafe_user'); // نام کاربری دیتابیس در cPanel
define('DB_PASS', 'my_secure_password_123'); // رمز عبور دیتابیس

// تنظیمات کلی سیستم
define('CURRENCY', 'تومان');
define('ADMIN_MAX_ATTEMPTS', 5); // حداکثر دفعات تلاش ناموفق برای ورود ادمین
define('ADMIN_LOCKOUT_TIME', 900); // زمان مسدود شدن ادمین (۱۵ دقیقه به ثانیه)

// وارد کردن کتابخانه تقویم جلالی
require_once __DIR__ . '/../functions/jdf.php';

// برقراری ارتباط با دیتابیس با استفاده از PDO
try {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
} catch (PDOException $e) {
    // در صورتی که دیتابیس هنوز ساخته نشده باشد یا ارور بدهد، سیستم به کاربری عادی لطمه نمی‌زند
    // جهت راحتی خریدار در بارگذاری اولیه در سی‌پنل، ارور به صورت مناسب هندل می‌شود
    $pdo = null;
}

/**
 * جلوگیری از حملات XSS با پاکسازی خروجی‌ها
 */
function sanitize($data) {
    if (is_array($data)) {
        return array_map('sanitize', $data);
    }
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

/**
 * تولید کد سفارش منحصر به فرد
 */
function generateOrderCode() {
    $jalali_year = JalaliDate::now('Y');
    $rand = str_pad(rand(1000, 9999), 4, '0', STR_PAD_LEFT);
    return "CAFE-" . $jalali_year . "-" . $rand;
}

/**
 * مدیریت توکن امنیتی CSRF جهت جلوگیری از حملات جعل درخواست
 */
function getCSRFToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCSRFToken($token) {
    if (empty($_SESSION['csrf_token']) || empty($token)) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * ثبت فعالیت‌های مدیران در پایگاه داده
 */
function logActivity($action) {
    global $pdo;
    if (!$pdo) return;
    
    $admin_id = isset($_SESSION['admin_id']) ? $_SESSION['admin_id'] : null;
    $username = isset($_SESSION['admin_user']) ? $_SESSION['admin_user'] : 'unknown';
    $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    
    try {
        $stmt = $pdo->prepare("INSERT INTO activity_logs (admin_id, username, action, ip_address) VALUES (?, ?, ?, ?)");
        $stmt->execute([$admin_id, $username, $action, $ip]);
    } catch (Exception $e) {
        // خطا نادیده گرفته می‌شود تا اجرای برنامه اصلی متوقف نگردد
    }
}

/**
 * لود کردن تنظیمات سایت از دیتابیس با قابلیت لود مقادیر پیش‌فرض
 */
function getSettings() {
    global $pdo;
    $default_settings = [
        'cafe_name' => 'کافه گالری',
        'cafe_description' => 'فضایی آرام و دلنشین همراه با بهترین طعم‌های قهوه تخصصی و دسرهای دست‌ساز',
        'cafe_phone' => '021-88888888',
        'cafe_address' => 'تهران، خیابان ولیعصر، نرسیده به میدان ونک، بن‌بست کافه، پلاک ۱۲',
        'working_hours' => 'همه‌روزه از ساعت ۸:۰۰ صبح الی ۲۳:۳۰ شب',
        'primary_color' => '#c49b63',
        'secondary_color' => '#b28b58',
        'instagram_link' => 'https://instagram.com/cafegallery',
        'telegram_link' => 'https://t.me/cafegallery',
        'logo_url' => '',
        'banner_url' => ''
    ];

    if (!$pdo) {
        return $default_settings;
    }

    try {
        $stmt = $pdo->query("SELECT key_name, key_value FROM settings");
        $results = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
        return array_merge($default_settings, $results);
    } catch (Exception $e) {
        return $default_settings;
    }
}

// لود کردن تمامی تنظیمات سیستم
$settings = getSettings();
