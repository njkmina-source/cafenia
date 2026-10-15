<?php
/**
 * مدیریت تنظیمات سراسری سیستم (لوگو، بنر، رنگ‌ها، راه‌های ارتباطی و...)
 */
require_once __DIR__ . '/../config/config.php';

// احراز هویت ادمین
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: ../login.php');
    exit;
}

$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf_token = $_POST['csrf_token'] ?? '';
    if (!verifyCSRFToken($csrf_token)) {
        $message = 'خطای امنیتی عدم هماهنگی نشست (CSRF)';
        $message_type = 'error';
    } else {
        $keys_to_update = [
            'cafe_name', 'cafe_description', 'cafe_phone', 'cafe_address', 
            'working_hours', 'primary_color', 'secondary_color', 
            'instagram_link', 'telegram_link'
        ];

        // آپلود لوگوی جدید
        $logo_url = $_POST['current_logo'] ?? '';
        if (isset($_FILES['logo_file']) && $_FILES['logo_file']['error'] === UPLOAD_ERR_OK) {
            $tmp = $_FILES['logo_file']['tmp_name'];
            $name = $_FILES['logo_file']['name'];
            $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
            if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
                $upload_dir = __DIR__ . '/../uploads/';
                if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);
                $new_name = 'logo_' . time() . '.' . $ext;
                if (move_uploaded_file($tmp, $upload_dir . $new_name)) {
                    $logo_url = 'uploads/' . $new_name;
                }
            }
        }

        // آپلود بنر جدید
        $banner_url = $_POST['current_banner'] ?? '';
        if (isset($_FILES['banner_file']) && $_FILES['banner_file']['error'] === UPLOAD_ERR_OK) {
            $tmp = $_FILES['banner_file']['tmp_name'];
            $name = $_FILES['banner_file']['name'];
            $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
            if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
                $upload_dir = __DIR__ . '/../uploads/';
                if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);
                $new_name = 'banner_' . time() . '.' . $ext;
                if (move_uploaded_file($tmp, $upload_dir . $new_name)) {
                    $banner_url = 'uploads/' . $new_name;
                }
            }
        }

        if ($pdo) {
            try {
                $pdo->beginTransaction();

                // بروزرسانی دیتابیس
                $stmt = $pdo->prepare("INSERT INTO settings (key_name, key_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE key_value = ?");
                
                // بروزرسانی فیلدهای متنی ساده
                foreach ($keys_to_update as $key) {
                    $val = trim($_POST[$key] ?? '');
                    $stmt->execute([$key, $val, $val]);
                }

                // بروزرسانی تصاویر
                $stmt->execute(['logo_url', $logo_url, $logo_url]);
                $stmt->execute(['banner_url', $banner_url, $banner_url]);

                // بروزرسانی اطلاعات ادمین در صورت تغییر در فیلدها
                $new_admin_user = trim($_POST['admin_username'] ?? '');
                $new_admin_pass = $_POST['admin_password'] ?? '';
                if (!empty($new_admin_user) || !empty($new_admin_pass)) {
                    if (!empty($new_admin_user)) {
                        $stmt_admin = $pdo->prepare("UPDATE admins SET username = ? WHERE id = ?");
                        $stmt_admin->execute([$new_admin_user, $_SESSION['admin_id'] ?? 1]);
                        $_SESSION['admin_user'] = $new_admin_user;
                    }
                    if (!empty($new_admin_pass)) {
                        $hashed_pass = password_hash($new_admin_pass, PASSWORD_BCRYPT);
                        $stmt_admin = $pdo->prepare("UPDATE admins SET password = ? WHERE id = ?");
                        $stmt_admin->execute([$hashed_pass, $_SESSION['admin_id'] ?? 1]);
                    }
                }

                $pdo->commit();
                logActivity("بروزرسانی کلی تنظیمات سیستم");
                
                $message = 'تنظیمات با موفقیت ذخیره گردید و در کل منو اعمال شد.';
                $message_type = 'success';
                
                // بروزرسانی متغیر لوکال سشن برای مشاهده آنی نتایج
                $settings = getSettings();
            } catch (Exception $e) {
                if ($pdo->inTransaction()) $pdo->rollBack();
                $message = 'بروز خطا در ثبت اطلاعات دیتابیس.';
                $message_type = 'error';
            }
        } else {
            $message = '[دمو] تنظیمات با موفقیت به صورت موقت در مرورگر ذخیره گردید.';
            $message_type = 'success';
        }
    }
}

// خواندن آخرین تنظیمات سیستم
$settings = getSettings();
?>

<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تنظیمات سایت - پنل ادمین</title>
    <link href="https://cdn.jsdelivr.net/gh/rastikerdar/vazirmatn@v33.003/Vazirmatn-font-face.css" rel="stylesheet" type="text/css" />
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        body { font-family: 'Vazirmatn', sans-serif; }
    </style>
</head>
<body class="bg-stone-100 dark:bg-stone-950 text-stone-800 dark:text-stone-100 min-h-screen flex flex-col md:flex-row">

<aside class="w-full md:w-64 bg-white dark:bg-stone-900 border-b md:border-b-0 md:border-l border-stone-200 dark:border-stone-800 flex flex-col shrink-0">
    <div class="p-6 border-b border-stone-100 dark:border-stone-800">
        <h3 class="text-lg font-black text-stone-900 dark:text-white flex items-center gap-2">
            <i data-lucide="coffee" class="text-amber-600"></i>
            <span>مدیریت کافه گالری</span>
        </h3>
        <p class="text-[10px] text-stone-500 mt-1">خوش آمدید، ‏<?php echo sanitize($_SESSION['admin_fullname'] ?? 'مدیر سیستم'); ?></p>
    </div>
    <nav class="flex-1 p-4 space-y-1">
        <a href="dashboard.php" class="flex items-center gap-3 px-4 py-3 rounded-2xl text-stone-600 dark:text-stone-300 hover:bg-stone-50 dark:hover:bg-stone-800 font-semibold text-sm transition-all">
            <i data-lucide="layout-dashboard" class="w-5 h-5 text-amber-600"></i>
            <span>داشبورد و سفارشات</span>
        </a>
        <a href="products.php" class="flex items-center gap-3 px-4 py-3 rounded-2xl text-stone-600 dark:text-stone-300 hover:bg-stone-50 dark:hover:bg-stone-800 font-semibold text-sm transition-all">
            <i data-lucide="package" class="w-5 h-5 text-amber-600"></i>
            <span>مدیریت محصولات</span>
        </a>
        <a href="categories.php" class="flex items-center gap-3 px-4 py-3 rounded-2xl text-stone-600 dark:text-stone-300 hover:bg-stone-50 dark:hover:bg-stone-800 font-semibold text-sm transition-all">
            <i data-lucide="layers" class="w-5 h-5 text-amber-600"></i>
            <span>مدیریت دسته‌بندی‌ها</span>
        </a>
        <a href="orders.php" class="flex items-center gap-3 px-4 py-3 rounded-2xl text-stone-600 dark:text-stone-300 hover:bg-stone-50 dark:hover:bg-stone-800 font-semibold text-sm transition-all">
            <i data-lucide="shopping-bag" class="w-5 h-5 text-amber-600"></i>
            <span>آرشیو سفارشات</span>
        </a>
        <a href="reports.php" class="flex items-center gap-3 px-4 py-3 rounded-2xl text-stone-600 dark:text-stone-300 hover:bg-stone-50 dark:hover:bg-stone-800 font-semibold text-sm transition-all">
            <i data-lucide="bar-chart-3" class="w-5 h-5 text-amber-600"></i>
            <span>گزارشات و خروجی</span>
        </a>
        <a href="settings.php" class="flex items-center gap-3 px-4 py-3 rounded-2xl bg-amber-600 text-white font-bold text-sm shadow-md transition-all">
            <i data-lucide="settings" class="w-5 h-5"></i>
            <span>تنظیمات سیستم</span>
        </a>
        <a href="../index.php" target="_blank" class="flex items-center gap-3 px-4 py-3 rounded-2xl text-amber-600 dark:text-amber-400 hover:bg-amber-50 dark:hover:bg-amber-950/20 font-bold text-sm transition-all">
            <i data-lucide="external-link" class="w-5 h-5"></i>
            <span>مشاهده منوی مشتری</span>
        </a>
    </nav>
    <div class="p-4 border-t border-stone-100 dark:border-stone-800">
        <a href="../logout.php" class="flex items-center gap-3 px-4 py-3 rounded-2xl text-red-500 hover:bg-red-50 dark:hover:bg-red-950/20 font-bold text-sm transition-all">
            <i data-lucide="log-out" class="w-5 h-5"></i>
            <span>خروج از پنل</span>
        </a>
    </div>
</aside>

<main class="flex-1 p-6 md:p-8 space-y-8 overflow-y-auto">
    <div>
        <h2 class="text-2xl font-black text-stone-950 dark:text-white">تنظیمات سراسری سیستم</h2>
        <p class="text-xs text-stone-500 dark:text-stone-400 mt-1">تغییر لوگو، نام تجاری کافه، آدرس، تم رنگی اختصاصی و راه‌های ارتباطی با مشتری</p>
    </div>

    <!-- اعلان‌ها -->
    <?php if (!empty($message)): ?>
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 px-5 py-3.5 rounded-2xl text-xs font-bold flex items-center gap-2 shadow-xs">
            <i data-lucide="check-circle" class="w-5 h-5 shrink-0"></i>
            <span><?php echo $message; ?></span>
        </div>
    <?php endif; ?>

    <form action="settings.php" method="POST" enctype="multipart/form-data" class="bg-white dark:bg-stone-900 rounded-3xl p-6 border border-stone-200 dark:border-stone-800 shadow-sm space-y-6 max-w-4xl">
        <input type="hidden" name="csrf_token" value="<?php echo getCSRFToken(); ?>">
        <input type="hidden" name="current_logo" value="<?php echo sanitize($settings['logo_url']); ?>">
        <input type="hidden" name="current_banner" value="<?php echo sanitize($settings['banner_url']); ?>">

        <!-- ۱. اطلاعات اصلی -->
        <div class="space-y-4">
            <h3 class="text-sm font-black text-amber-600 border-b border-stone-100 dark:border-stone-800 pb-2">اطلاعات برندینگ کافه</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-stone-500 mb-1.5">نام تجاری کافه/رستوران *</label>
                    <input type="text" name="cafe_name" required value="<?php echo sanitize($settings['cafe_name']); ?>" class="w-full px-3 py-2.5 rounded-xl bg-stone-50 dark:bg-stone-800 border border-stone-200 dark:border-stone-700 text-xs focus:outline-none focus:border-amber-600 font-bold">
                </div>
                <div>
                    <label class="block text-xs font-bold text-stone-500 mb-1.5">شماره تلفن تماس *</label>
                    <input type="text" name="cafe_phone" required value="<?php echo sanitize($settings['cafe_phone']); ?>" class="w-full px-3 py-2.5 rounded-xl bg-stone-50 dark:bg-stone-800 border border-stone-200 dark:border-stone-700 text-xs focus:outline-none focus:border-amber-600 text-right dir-ltr">
                </div>
            </div>
            <div>
                <label class="block text-xs font-bold text-stone-500 mb-1.5">توضیح کوتاه کافه (درباره ما کوتاه) *</label>
                <textarea name="cafe_description" required rows="2" class="w-full px-3 py-2.5 rounded-xl bg-stone-50 dark:bg-stone-800 border border-stone-200 dark:border-stone-700 text-xs focus:outline-none focus:border-amber-600 leading-relaxed"><?php echo sanitize($settings['cafe_description']); ?></textarea>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-stone-500 mb-1.5">ساعات کاری کافه *</label>
                    <input type="text" name="working_hours" required value="<?php echo sanitize($settings['working_hours']); ?>" class="w-full px-3 py-2.5 rounded-xl bg-stone-50 dark:bg-stone-800 border border-stone-200 dark:border-stone-700 text-xs focus:outline-none focus:border-amber-600">
                </div>
                <div>
                    <label class="block text-xs font-bold text-stone-500 mb-1.5">آدرس فیزیکی کافه *</label>
                    <input type="text" name="cafe_address" required value="<?php echo sanitize($settings['cafe_address']); ?>" class="w-full px-3 py-2.5 rounded-xl bg-stone-50 dark:bg-stone-800 border border-stone-200 dark:border-stone-700 text-xs focus:outline-none focus:border-amber-600">
                </div>
            </div>
        </div>

        <!-- ۲. تم رنگی و آپلود لوگو -->
        <div class="space-y-4 pt-4 border-t border-stone-100 dark:border-stone-800">
            <h3 class="text-sm font-black text-amber-600 pb-2">هویت بصری و تصاویر</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- لوگو و بنر -->
                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-stone-500 mb-1.5">آپلود لوگو کافه (تصویر مربعی)</label>
                        <input type="file" name="logo_file" accept="image/*" class="w-full text-xs text-stone-500 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-amber-50 file:text-amber-700 hover:file:bg-amber-100 cursor-pointer">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-stone-500 mb-1.5">آپلود بنر کافه (تصویر افقی عریض)</label>
                        <input type="file" name="banner_file" accept="image/*" class="w-full text-xs text-stone-500 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-amber-50 file:text-amber-700 hover:file:bg-amber-100 cursor-pointer">
                    </div>
                </div>

                <!-- رنگ‌ها -->
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-stone-500 mb-1.5">رنگ اصلی (پوسته کلاینت)</label>
                        <div class="flex items-center gap-2">
                            <input type="color" name="primary_color" value="<?php echo sanitize($settings['primary_color']); ?>" class="w-12 h-10 rounded-xl border border-stone-300 dark:border-stone-700 cursor-pointer">
                            <input type="text" value="<?php echo sanitize($settings['primary_color']); ?>" class="w-full px-3 py-2 rounded-xl bg-stone-50 dark:bg-stone-800 border border-stone-200 dark:border-stone-700 text-xs focus:outline-none text-center" readonly>
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-stone-500 mb-1.5">رنگ ثانویه</label>
                        <div class="flex items-center gap-2">
                            <input type="color" name="secondary_color" value="<?php echo sanitize($settings['secondary_color']); ?>" class="w-12 h-10 rounded-xl border border-stone-300 dark:border-stone-700 cursor-pointer">
                            <input type="text" value="<?php echo sanitize($settings['secondary_color']); ?>" class="w-full px-3 py-2 rounded-xl bg-stone-50 dark:bg-stone-800 border border-stone-200 dark:border-stone-700 text-xs focus:outline-none text-center" readonly>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ۳. شبکه‌های اجتماعی -->
        <div class="space-y-4 pt-4 border-t border-stone-100 dark:border-stone-800">
            <h3 class="text-sm font-black text-amber-600 pb-2">لینک شبکه‌های اجتماعی</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-stone-500 mb-1.5">لینک اینستاگرام کافه</label>
                    <input type="url" name="instagram_link" value="<?php echo sanitize($settings['instagram_link']); ?>" placeholder="https://instagram.com/..." class="w-full px-3 py-2.5 rounded-xl bg-stone-50 dark:bg-stone-800 border border-stone-200 dark:border-stone-700 text-xs focus:outline-none focus:border-amber-600 text-right dir-ltr">
                </div>
                <div>
                    <label class="block text-xs font-bold text-stone-500 mb-1.5">لینک کانال تلگرام</label>
                    <input type="url" name="telegram_link" value="<?php echo sanitize($settings['telegram_link']); ?>" placeholder="https://t.me/..." class="w-full px-3 py-2.5 rounded-xl bg-stone-50 dark:bg-stone-800 border border-stone-200 dark:border-stone-700 text-xs focus:outline-none focus:border-amber-600 text-right dir-ltr">
                </div>
            </div>
        </div>

        <!-- ۴. هویت و رمز عبور ادمین -->
        <div class="space-y-4 pt-4 border-t border-stone-100 dark:border-stone-800">
            <h3 class="text-sm font-black text-amber-600 pb-2">تنظیمات هویت و عبور امنیتی پنل مدیریت</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-stone-500 mb-1.5">نام کاربری جدید مدیریت</label>
                    <input type="text" name="admin_username" placeholder="برای عدم تغییر خالی بگذارید..." class="w-full px-3 py-2.5 rounded-xl bg-stone-50 dark:bg-stone-800 border border-stone-200 dark:border-stone-700 text-xs focus:outline-none focus:border-amber-600 font-mono text-center">
                </div>
                <div>
                    <label class="block text-xs font-bold text-stone-500 mb-1.5">رمز عبور جدید مدیریت</label>
                    <input type="password" name="admin_password" placeholder="برای عدم تغییر خالی بگذارید..." class="w-full px-3 py-2.5 rounded-xl bg-stone-50 dark:bg-stone-800 border border-stone-200 dark:border-stone-700 text-xs focus:outline-none focus:border-amber-600 font-mono text-center">
                </div>
            </div>
            <p class="text-[10px] text-stone-400 mt-1">از این اطلاعات برای احراز هویت در ورود مجدد به مسیر مدیریت استفاده می‌شود.</p>
        </div>

        <div class="pt-4">
            <button type="submit" class="w-full bg-amber-600 hover:bg-amber-500 text-white py-3.5 rounded-xl font-bold text-sm flex items-center justify-center gap-2 shadow-lg transition-all">
                <i data-lucide="check" class="w-5 h-5"></i>
                <span>ذخیره نهایی تنظیمات و تصاویر</span>
            </button>
        </div>
    </form>
</main>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }

        // سینک کردن مشخصات ادمین در لوکال استوریج برای هماهنگی با فرانت‌اند React و شبیه‌سازها
        const form = document.querySelector('form');
        if (form) {
            form.addEventListener('submit', function() {
                const userVal = document.querySelector('input[name="admin_username"]').value.trim();
                const passVal = document.querySelector('input[name="admin_password"]').value.trim();
                if (userVal) {
                    localStorage.setItem('admin_username', userVal);
                }
                if (passVal) {
                    localStorage.setItem('admin_password', passVal);
                }
            });
        }
    });
</script>
</body>
</html>
