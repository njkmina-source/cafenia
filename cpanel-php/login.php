<?php
/**
 * صفحه ورود امن مدیریت کافه
 * همراه با کنترل تلاش‌های ناموفق (Rate Limiting) و هولد نشست امن
 */
require_once __DIR__ . '/config/config.php';

// در صورتی که قبلاً لاگین شده باشد هدایت به داشبورد
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header('Location: admin/dashboard.php');
    exit;
}

$error_message = '';

// بررسی ارسال فرم ورود
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // بررسی توکن امنیت نشست
    $csrf_token = $_POST['csrf_token'] ?? '';
    if (!verifyCSRFToken($csrf_token)) {
        $error_message = 'نشست امنیتی نامعتبر است. مجدد تلاش کنید.';
    } else {
        $username = trim($_POST['username'] ?? '');
        $password = trim($_POST['password'] ?? '');

        // کنترل محدودیت دفعات ورود (Rate Limiting)
        if (isset($_SESSION['login_attempts']) && $_SESSION['login_attempts'] >= ADMIN_MAX_ATTEMPTS) {
            $lockout_elapsed = time() - ($_SESSION['last_login_attempt'] ?? 0);
            if ($lockout_elapsed < ADMIN_LOCKOUT_TIME) {
                $wait_minutes = ceil((ADMIN_LOCKOUT_TIME - $lockout_elapsed) / 60);
                $error_message = "به دلیل تلاش‌های ناموفق مکرر، ورود شما مسدود است. لطفاً " . $wait_minutes . " دقیقه دیگر تلاش فرمایید.";
            } else {
                // ریست کردن زمان محدودیت پس از اتمام تایم اوت
                $_SESSION['login_attempts'] = 0;
            }
        }

        if (empty($error_message)) {
            if (empty($username) || empty($password)) {
                $error_message = 'وارد کردن نام کاربری و رمز عبور الزامی است.';
            } else {
                if ($pdo) {
                    try {
                        // دریافت رکورد مدیر از جدول با استفاده از Prepared Statement
                        $stmt = $pdo->prepare("SELECT * FROM admins WHERE username = ?");
                        $stmt->execute([$username]);
                        $admin = $stmt->fetch();

                        if ($admin && password_verify($password, $admin['password'])) {
                            // ریست شمارش تلاش‌های اشتباه
                            $_SESSION['login_attempts'] = 0;
                            
                            // راه‌اندازی نشست ادمین
                            $_SESSION['admin_logged_in'] = true;
                            $_SESSION['admin_id'] = $admin['id'];
                            $_SESSION['admin_user'] = $admin['username'];
                            $_SESSION['admin_fullname'] = $admin['fullname'];
                            
                            // بازنویسی آی‌دی نشست جهت جلوگیری از Session Hijacking
                            session_regenerate_id(true);

                            // ثبت لاگ ورود موفق
                            logActivity("ورود موفقیت‌آمیز به پنل ادمین");

                            header('Location: admin/dashboard.php');
                            exit;
                        } else {
                            // ثبت تلاش ناموفق
                            $_SESSION['login_attempts'] = ($_SESSION['login_attempts'] ?? 0) + 1;
                            $_SESSION['last_login_attempt'] = time();
                            
                            logActivity("تلاش ناموفق برای ورود با نام کاربری: " . sanitize($username));
                            
                            $remaining = ADMIN_MAX_ATTEMPTS - $_SESSION['login_attempts'];
                            if ($remaining > 0) {
                                $error_message = "نام کاربری یا رمز عبور اشتباه است. ($remaining تلاش باقیمانده)";
                            } else {
                                $error_message = "اکانت شما به مدت ۱۵ دقیقه قفل شد.";
                            }
                        }
                    } catch (Exception $e) {
                        $error_message = 'بروز خطا در برقراری ارتباط با دیتابیس ادمین.';
                    }
                } else {
                    // شبیه‌سازی برای دموی آسان در صورتی که دیتابیس هنوز روی cPanel ست نشده باشد
                    // دریافت اطلاعات تغییریافته در تنظیمات از طریق لوکال استوریج کلاینت
                    $expected_username = !empty($_POST['local_admin_username']) ? trim($_POST['local_admin_username']) : 'admin';
                    $expected_password = !empty($_POST['local_admin_password']) ? trim($_POST['local_admin_password']) : 'admin1234';

                    if ($username === $expected_username && $password === $expected_password) {
                        $_SESSION['admin_logged_in'] = true;
                        $_SESSION['admin_id'] = 1;
                        $_SESSION['admin_user'] = $expected_username;
                        $_SESSION['admin_fullname'] = 'مدیر پیش‌فرض کافه';
                        
                        header('Location: admin/dashboard.php');
                        exit;
                    } else {
                        $error_message = "[دموی آفلاین] نام کاربری یا رمز عبور اشتباه است.";
                    }
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ورود به مدیریت کافه گالری</title>
    <link href="https://cdn.jsdelivr.net/gh/rastikerdar/vazirmatn@v33.003/Vazirmatn-font-face.css" rel="stylesheet" type="text/css" />
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        body { font-family: 'Vazirmatn', sans-serif; }
    </style>
</head>
<body class="bg-stone-100 dark:bg-stone-950 text-stone-800 dark:text-stone-100 min-h-screen flex flex-col justify-center items-center p-4">

<div class="w-full max-w-md bg-white dark:bg-stone-900 rounded-3xl p-8 border border-stone-200 dark:border-stone-800 shadow-2xl relative">
    <div class="text-center mb-8">
        <div class="w-16 h-16 rounded-2xl bg-amber-600/10 text-amber-600 flex items-center justify-center mx-auto mb-4">
            <i data-lucide="shield-alert" class="w-8 h-8"></i>
        </div>
        <h2 class="text-xl font-black text-stone-900 dark:text-white">ورود به پنل مدیریت کافه</h2>
        <p class="text-xs text-stone-500 dark:text-stone-400 mt-2">جهت دسترسی به سفارشات، تنظیمات و گزارش‌ها</p>
    </div>

    <?php if (!empty($error_message)): ?>
        <div class="bg-red-50 dark:bg-red-950/50 border border-red-200 dark:border-red-900 text-red-800 dark:text-red-300 px-4 py-3 rounded-2xl text-xs font-semibold mb-6 flex items-center gap-2">
            <i data-lucide="alert-octagon" class="w-4 h-4 shrink-0"></i>
            <span><?php echo $error_message; ?></span>
        </div>
    <?php endif; ?>

    <form action="login.php" method="POST" class="space-y-5">
        <input type="hidden" name="csrf_token" value="<?php echo getCSRFToken(); ?>">

        <div>
            <label class="block text-xs font-bold text-stone-500 dark:text-stone-400 mb-2">نام کاربری ادمین</label>
            <div class="relative">
                <span class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-stone-400">
                    <i data-lucide="user" class="w-4 h-4"></i>
                </span>
                <input type="text" name="username" required placeholder="admin" class="w-full pl-4 pr-10 py-3 rounded-xl bg-stone-50 dark:bg-stone-800 border border-stone-200 dark:border-stone-700 text-stone-900 dark:text-white text-sm focus:outline-none focus:border-amber-600">
            </div>
        </div>

        <div>
            <label class="block text-xs font-bold text-stone-500 dark:text-stone-400 mb-2">رمز عبور عبور</label>
            <div class="relative">
                <span class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-stone-400">
                    <i data-lucide="key" class="w-4 h-4"></i>
                </span>
                <input type="password" name="password" required placeholder="••••••••" class="w-full pl-4 pr-10 py-3 rounded-xl bg-stone-50 dark:bg-stone-800 border border-stone-200 dark:border-stone-700 text-stone-900 dark:text-white text-sm focus:outline-none focus:border-amber-600">
            </div>
        </div>

        <button type="submit" class="w-full bg-amber-600 hover:bg-amber-500 text-white py-3.5 rounded-xl font-bold flex items-center justify-center gap-2 shadow-lg shadow-amber-600/20 transition-all">
            <i data-lucide="log-in" class="w-5 h-5"></i>
            <span>ورود امن به سیستم</span>
        </button>
    </form>

    <div class="mt-8 text-center border-t border-stone-100 dark:border-stone-800 pt-5">
        <a href="index.php" class="text-xs font-semibold text-stone-500 hover:text-stone-800 dark:hover:text-white flex items-center justify-center gap-1">
            <i data-lucide="chevron-right" class="w-4 h-4"></i>
            <span>بازگشت به منوی کافه</span>
        </a>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }

        // تزریق مقادیر تغییریافته در تنظیمات دمو از لوکال استوریج کلاینت به سرور شبیه‌ساز
        const form = document.querySelector('form');
        if (form) {
            const userHidden = document.createElement('input');
            userHidden.type = 'hidden';
            userHidden.name = 'local_admin_username';
            userHidden.value = localStorage.getItem('admin_username') || '';
            form.appendChild(userHidden);

            const passHidden = document.createElement('input');
            passHidden.type = 'hidden';
            passHidden.name = 'local_admin_password';
            passHidden.value = localStorage.getItem('admin_password') || '';
            form.appendChild(passHidden);
        }
    });
</script>
</body>
</html>
