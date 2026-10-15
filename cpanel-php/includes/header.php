<?php
/**
 * هدر استاندارد منوی دیجیتال کافه
 */
if (file_exists(__DIR__ . '/../config/config.php')) {
    require_once __DIR__ . '/../config/config.php';
} else {
    require_once __DIR__ . '/../config/config.example.php';
}
$settings = getSettings();
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl" class="scroll-smooth dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo sanitize($settings['cafe_name']); ?> - منوی دیجیتال کافه</title>
    <!-- بارگذاری فونت زیبای وزیر متن -->
    <link href="https://cdn.jsdelivr.net/gh/rastikerdar/vazirmatn@v33.003/Vazirmatn-font-face.css" rel="stylesheet" type="text/css" />
    <!-- بارگذاری فریم‌ورک قدرتمند Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        primary: '<?php echo $settings['primary_color'] ?: "#c49b63"; ?>',
                        secondary: '<?php echo $settings['secondary_color'] ?: "#b28b58"; ?>',
                        accent: '#c49b63',
                    },
                    fontFamily: {
                        sans: ['Vazirmatn', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    <!-- آیکون‌های لوساید -->
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        body {
            font-family: 'Vazirmatn', sans-serif;
        }
        /* کاستوم اسکرول‌بار مدرن */
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        ::-webkit-scrollbar-track {
            background: transparent;
        }
        ::-webkit-scrollbar-thumb {
            background: #88888844;
            border-radius: 4px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #88888888;
        }
        /* افکت‌های شیشه ای (Frosted Glass) برای یکسان‌سازی با پیش‌نمایش */
        .glass {
            background: rgba(255, 255, 255, 0.03);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.08);
            transition: background 0.3s ease, border-color 0.3s ease;
        }
        .glass-light {
            background: rgba(255, 255, 255, 0.06);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.12);
        }
        .glass-dark {
            background: rgba(15, 14, 12, 0.85);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.05);
        }
        .accent-glow {
            box-shadow: 0 0 20px rgba(196, 155, 99, 0.3);
        }
        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        .animate-fade-in {
            animation: slideUp 0.3s ease forwards;
        }
        /* هید کردن اسکرول‌بار در دکمه‌ها */
        .scrollbar-none::-webkit-scrollbar {
            display: none;
        }
        .scrollbar-none {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
    </style>
</head>
<body class="bg-stone-50 text-stone-800 dark:bg-[#0f0e0c] dark:text-stone-100 min-h-screen flex flex-col transition-colors duration-300">

<!-- ناوبری هدر همراه با حالت شب و روز -->
<header class="sticky top-0 z-40 bg-white/80 dark:bg-stone-900/80 backdrop-blur-md border-b border-stone-200 dark:border-stone-800 transition-colors duration-300">
    <div class="max-w-6xl mx-auto px-4 py-3 flex items-center justify-between">
        <!-- لوگو و نام کافه -->
        <a href="index.php" class="flex items-center gap-3">
            <?php if (!empty($settings['logo_url'])): ?>
                <img src="<?php echo sanitize($settings['logo_url']); ?>" alt="لوگو" class="w-10 h-10 rounded-full object-cover border border-stone-200 dark:border-stone-700">
            <?php else: ?>
                <div class="w-10 h-10 rounded-full bg-primary/10 flex items-center justify-center text-primary">
                    <i data-lucide="coffee" class="w-6 h-6"></i>
                </div>
            <?php endif; ?>
            <div>
                <h1 class="text-lg font-bold text-stone-900 dark:text-white leading-tight"><?php echo sanitize($settings['cafe_name']); ?></h1>
                <p class="text-xs text-stone-500 dark:text-stone-400">سفارش آسان و هوشمند</p>
            </div>
        </a>

        <!-- دکمه‌های کنترل هدر -->
        <div class="flex items-center gap-2">
            <!-- سوئیچر تم تاریک/روشن -->
            <button id="themeToggleBtn" class="p-2 rounded-full hover:bg-stone-100 dark:hover:bg-stone-800 text-stone-600 dark:text-stone-300 transition-colors" title="تغییر تم">
                <i data-lucide="sun" class="w-5 h-5 hidden dark:block"></i>
                <i data-lucide="moon" class="w-5 h-5 block dark:hidden"></i>
            </button>

            <!-- دکمه سبد خرید در هدر (مخصوص مشتری) -->
            <?php if(basename($_SERVER['PHP_SELF']) == 'index.php'): ?>
            <button onclick="openCartModal()" class="relative p-2 rounded-full hover:bg-stone-100 dark:hover:bg-stone-800 text-stone-600 dark:text-stone-300 transition-colors">
                <i data-lucide="shopping-bag" class="w-5 h-5"></i>
                <span id="cartCountBadge" class="absolute -top-1 -right-1 bg-primary text-white text-[10px] font-bold w-5 h-5 rounded-full flex items-center justify-center hidden">0</span>
            </button>
            <?php endif; ?>

            <!-- پنل ادمین سریع -->
            <?php if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true): ?>
                <a href="admin/dashboard.php" class="flex items-center gap-1.5 text-xs bg-primary/10 hover:bg-primary/20 text-primary py-1.5 px-3 rounded-full transition-colors font-medium">
                    <i data-lucide="layout-dashboard" class="w-4 h-4"></i>
                    <span>داشبورد ادمین</span>
                </a>
            <?php else: ?>
                <a href="login.php" class="p-2 rounded-full hover:bg-stone-100 dark:hover:bg-stone-800 text-stone-500 dark:text-stone-400 transition-colors" title="ورود مدیریت">
                    <i data-lucide="user-cog" class="w-5 h-5"></i>
                </a>
            <?php endif; ?>
        </div>
    </div>
</header>
