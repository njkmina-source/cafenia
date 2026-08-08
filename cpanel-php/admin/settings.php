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
<html lang="fa" dir="rtl" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تنظیمات سایت - پنل ادمین</title>
    <link href="https://cdn.jsdelivr.net/gh/rastikerdar/vazirmatn@v33.003/Vazirmatn-font-face.css" rel="stylesheet" type="text/css" />
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        primary: '#c49b63',
                        secondary: '#1a1916',
                    }
                }
            }
        }
    </script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        body { font-family: 'Vazirmatn', sans-serif; }
        .glass {
            background: rgba(255, 255, 255, 0.03);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.08);
        }
        .scrollbar-none::-webkit-scrollbar { display: none; }
        .scrollbar-none { -ms-overflow-style: none; scrollbar-width: none; }
    </style>
</head>
<body class="bg-[#0f0e0c] text-stone-100 min-h-screen flex flex-col font-sans">

<!-- هدر یکپارچه جدید پنل مدیریت ادمین -->
<header class="sticky top-0 z-50 bg-[#131210]/95 backdrop-blur-md border-b border-white/10 px-3 py-2.5 flex items-center justify-between gap-2 shadow-lg">
    <!-- نام کافه و عنوان پنل مدیریت -->
    <div class="flex items-center gap-2 shrink-0">
        <div class="w-8 h-8 rounded-full bg-[#c49b63]/10 flex items-center justify-center text-[#c49b63] border border-[#c49b63]/25">
            <i data-lucide="coffee" class="w-4 h-4"></i>
        </div>
        <div class="hidden sm:block">
            <h1 class="text-xs font-black text-white"><?php echo sanitize($settings['cafe_name'] ?? 'کافه گالری'); ?></h1>
            <p class="text-[9px] text-white/40 font-bold">پنل مدیریت یکپارچه</p>
        </div>
    </div>

    <!-- دکمه‌های ناوبری تب‌های ادمین با آیکون‌های فشرده برای موبایل و دسکتاپ -->
    <div class="flex items-center gap-1 bg-white/5 border border-white/10 p-1 rounded-2xl overflow-x-auto scrollbar-none max-w-[calc(100vw-130px)] sm:max-w-none">
        <a href="dashboard.php" title="داشبورد و آمار" class="relative p-2 sm:px-3 sm:py-2 rounded-xl transition-all cursor-pointer group shrink-0 flex items-center gap-1.5 text-stone-300 hover:bg-white/5 hover:text-white">
            <i data-lucide="layout-dashboard" class="w-4 h-4 shrink-0"></i>
            <span class="text-[11px] hidden md:inline whitespace-nowrap font-bold">داشبورد و آمار</span>
        </a>
        <a href="products.php" title="مدیریت محصولات" class="relative p-2 sm:px-3 sm:py-2 rounded-xl transition-all cursor-pointer group shrink-0 flex items-center gap-1.5 text-stone-300 hover:bg-white/5 hover:text-white">
            <i data-lucide="package" class="w-4 h-4 shrink-0"></i>
            <span class="text-[11px] hidden md:inline whitespace-nowrap font-bold">مدیریت محصولات</span>
        </a>
        <a href="categories.php" title="مدیریت دسته‌ها" class="relative p-2 sm:px-3 sm:py-2 rounded-xl transition-all cursor-pointer group shrink-0 flex items-center gap-1.5 text-stone-300 hover:bg-white/5 hover:text-white">
            <i data-lucide="layers" class="w-4 h-4 shrink-0"></i>
            <span class="text-[11px] hidden md:inline whitespace-nowrap font-bold">مدیریت دسته‌ها</span>
        </a>
        <a href="orders.php" title="آرشیو سفارشات" class="relative p-2 sm:px-3 sm:py-2 rounded-xl transition-all cursor-pointer group shrink-0 flex items-center gap-1.5 text-stone-300 hover:bg-white/5 hover:text-white">
            <i data-lucide="shopping-bag" class="w-4 h-4 shrink-0"></i>
            <span class="text-[11px] hidden md:inline whitespace-nowrap font-bold">آرشیو سفارشات</span>
        </a>
        <a href="reports.php" title="گزارشات و PDF" class="relative p-2 sm:px-3 sm:py-2 rounded-xl transition-all cursor-pointer group shrink-0 flex items-center gap-1.5 text-stone-300 hover:bg-white/5 hover:text-white">
            <i data-lucide="bar-chart-3" class="w-4 h-4 shrink-0"></i>
            <span class="text-[11px] hidden md:inline whitespace-nowrap font-bold">گزارشات و PDF</span>
        </a>
        <a href="settings.php" title="تنظیمات سیستم" class="relative p-2 sm:px-3 sm:py-2 rounded-xl transition-all cursor-pointer group shrink-0 flex items-center gap-1.5 bg-[#c49b63] text-black shadow-md font-black">
            <i data-lucide="settings" class="w-4 h-4 shrink-0"></i>
            <span class="text-[11px] hidden md:inline whitespace-nowrap font-bold">تنظیمات سیستم</span>
        </a>
    </div>

    <!-- بخش دکمه‌های اکشن -->
    <div class="flex items-center gap-1.5 shrink-0">
        <a href="../index.php" target="_blank" title="📱 مشاهده منوی مشتری" class="p-2.5 rounded-xl bg-white/5 border border-white/10 text-white hover:bg-white/10 transition-all cursor-pointer group relative flex items-center justify-center">
            <i data-lucide="external-link" class="w-4.5 h-4.5"></i>
        </a>
        <a href="../logout.php" title="خروج از پنل" class="p-2.5 rounded-xl bg-red-600/20 border border-red-500/30 text-red-400 hover:bg-red-600/30 transition-all cursor-pointer group relative flex items-center justify-center">
            <i data-lucide="log-out" class="w-4.5 h-4.5"></i>
        </a>
    </div>
</header>

<main class="flex-1 p-4 md:p-8 space-y-6 max-w-5xl mx-auto w-full overflow-y-auto">
    <div>
        <h2 class="text-2xl font-black text-white">تنظیمات سراسری سیستم</h2>
        <p class="text-xs text-stone-400 mt-1">تغییر لوگو، نام تجاری کافه، آدرس، تم رنگی اختصاصی و راه‌های ارتباطی با مشتری</p>
    </div>

    <!-- اعلان‌ها -->
    <?php if (!empty($message)): ?>
        <div class="bg-emerald-500/10 border border-emerald-500/30 text-emerald-300 px-5 py-3.5 rounded-2xl text-xs font-bold flex items-center gap-2 shadow-xs">
            <i data-lucide="check-circle" class="w-5 h-5 shrink-0 text-emerald-400"></i>
            <span><?php echo $message; ?></span>
        </div>
    <?php endif; ?>

    <form action="settings.php" method="POST" enctype="multipart/form-data" class="bg-[#131210] rounded-3xl p-6 border border-white/10 shadow-sm space-y-6 max-w-4xl">
        <input type="hidden" name="csrf_token" value="<?php echo getCSRFToken(); ?>">
        <input type="hidden" name="current_logo" value="<?php echo sanitize($settings['logo_url']); ?>">
        <input type="hidden" name="current_banner" value="<?php echo sanitize($settings['banner_url']); ?>">

        <!-- ۱. اطلاعات اصلی -->
        <div class="space-y-4">
            <h3 class="text-sm font-black text-[#c49b63] border-b border-white/10 pb-2">اطلاعات برندینگ کافه</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-stone-300 mb-1.5">نام تجاری کافه/رستوران *</label>
                    <input type="text" name="cafe_name" required value="<?php echo sanitize($settings['cafe_name']); ?>" class="w-full px-3 py-2.5 rounded-xl bg-white/5 border border-white/10 text-white text-xs focus:outline-none focus:border-[#c49b63] font-bold">
                </div>
                <div>
                    <label class="block text-xs font-bold text-stone-300 mb-1.5">شماره تلفن تماس *</label>
                    <input type="text" name="cafe_phone" required value="<?php echo sanitize($settings['cafe_phone']); ?>" class="w-full px-3 py-2.5 rounded-xl bg-white/5 border border-white/10 text-white text-xs focus:outline-none focus:border-[#c49b63] text-right dir-ltr">
                </div>
            </div>
            <div>
                <label class="block text-xs font-bold text-stone-300 mb-1.5">توضیح کوتاه کافه (درباره ما کوتاه) *</label>
                <textarea name="cafe_description" required rows="2" class="w-full px-3 py-2.5 rounded-xl bg-white/5 border border-white/10 text-white text-xs focus:outline-none focus:border-[#c49b63] leading-relaxed"><?php echo sanitize($settings['cafe_description']); ?></textarea>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-stone-300 mb-1.5">ساعات کاری کافه *</label>
                    <input type="text" name="working_hours" required value="<?php echo sanitize($settings['working_hours']); ?>" class="w-full px-3 py-2.5 rounded-xl bg-white/5 border border-white/10 text-white text-xs focus:outline-none focus:border-[#c49b63]">
                </div>
                <div>
                    <label class="block text-xs font-bold text-stone-300 mb-1.5">آدرس فیزیکی کافه *</label>
                    <input type="text" name="cafe_address" required value="<?php echo sanitize($settings['cafe_address']); ?>" class="w-full px-3 py-2.5 rounded-xl bg-white/5 border border-white/10 text-white text-xs focus:outline-none focus:border-[#c49b63]">
                </div>
            </div>
        </div>

        <!-- ۲. تم رنگی و آپلود لوگو -->
        <div class="space-y-4 pt-4 border-t border-white/10">
            <h3 class="text-sm font-black text-[#c49b63] pb-2">هویت بصری و تصاویر</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- لوگو و بنر -->
                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-stone-300 mb-1.5">آپلود لوگو کافه (تصویر مربعی)</label>
                        <input type="file" name="logo_file" accept="image/*" class="w-full text-xs text-stone-400 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-[#c49b63]/20 file:text-[#c49b63] hover:file:bg-[#c49b63]/30 cursor-pointer">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-stone-300 mb-1.5">آپلود بنر کافه (تصویر افقی عریض)</label>
                        <input type="file" name="banner_file" accept="image/*" class="w-full text-xs text-stone-400 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-[#c49b63]/20 file:text-[#c49b63] hover:file:bg-[#c49b63]/30 cursor-pointer">
                    </div>
                </div>

                <!-- رنگ‌ها -->
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-stone-300 mb-1.5">رنگ اصلی (پوسته کلاینت)</label>
                        <div class="flex items-center gap-2">
                            <input type="color" name="primary_color" value="<?php echo sanitize($settings['primary_color']); ?>" class="w-12 h-10 rounded-xl border border-white/20 bg-transparent cursor-pointer">
                            <input type="text" value="<?php echo sanitize($settings['primary_color']); ?>" class="w-full px-3 py-2 rounded-xl bg-white/5 border border-white/10 text-white text-xs focus:outline-none text-center" readonly>
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-stone-300 mb-1.5">رنگ ثانویه</label>
                        <div class="flex items-center gap-2">
                            <input type="color" name="secondary_color" value="<?php echo sanitize($settings['secondary_color']); ?>" class="w-12 h-10 rounded-xl border border-white/20 bg-transparent cursor-pointer">
                            <input type="text" value="<?php echo sanitize($settings['secondary_color']); ?>" class="w-full px-3 py-2 rounded-xl bg-white/5 border border-white/10 text-white text-xs focus:outline-none text-center" readonly>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ۳. شبکه‌های اجتماعی -->
        <div class="space-y-4 pt-4 border-t border-white/10">
            <h3 class="text-sm font-black text-[#c49b63] pb-2">لینک شبکه‌های اجتماعی</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-stone-300 mb-1.5">لینک اینستاگرام کافه</label>
                    <input type="url" name="instagram_link" value="<?php echo sanitize($settings['instagram_link']); ?>" placeholder="https://instagram.com/..." class="w-full px-3 py-2.5 rounded-xl bg-white/5 border border-white/10 text-white text-xs focus:outline-none focus:border-[#c49b63] text-right dir-ltr">
                </div>
                <div>
                    <label class="block text-xs font-bold text-stone-300 mb-1.5">لینک کانال تلگرام</label>
                    <input type="url" name="telegram_link" value="<?php echo sanitize($settings['telegram_link']); ?>" placeholder="https://t.me/..." class="w-full px-3 py-2.5 rounded-xl bg-white/5 border border-white/10 text-white text-xs focus:outline-none focus:border-[#c49b63] text-right dir-ltr">
                </div>
            </div>
        </div>

        <!-- ۴. هویت و رمز عبور ادمین -->
        <div class="space-y-4 pt-4 border-t border-white/10">
            <h3 class="text-sm font-black text-[#c49b63] pb-2">تنظیمات هویت و عبور امنیتی پنل مدیریت</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-stone-300 mb-1.5">نام کاربری جدید مدیریت</label>
                    <input type="text" name="admin_username" placeholder="برای عدم تغییر خالی بگذارید..." class="w-full px-3 py-2.5 rounded-xl bg-white/5 border border-white/10 text-white text-xs focus:outline-none focus:border-[#c49b63] font-mono text-center">
                </div>
                <div>
                    <label class="block text-xs font-bold text-stone-300 mb-1.5">رمز عبور جدید مدیریت</label>
                    <input type="password" name="admin_password" placeholder="برای عدم تغییر خالی بگذارید..." class="w-full px-3 py-2.5 rounded-xl bg-white/5 border border-white/10 text-white text-xs focus:outline-none focus:border-[#c49b63] font-mono text-center">
                </div>
            </div>
            <p class="text-[10px] text-stone-400 mt-1">از این اطلاعات برای احراز هویت در ورود مجدد به مسیر مدیریت استفاده می‌شود.</p>
        </div>

        <div class="pt-4">
            <button type="submit" class="w-full bg-[#c49b63] hover:bg-[#b28b58] text-black py-3.5 rounded-xl font-black text-sm flex items-center justify-center gap-2 shadow-lg transition-all cursor-pointer">
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
