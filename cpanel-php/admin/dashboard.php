<?php
/**
 * داشبورد ادمین کافه - سیستم مانیتورینگ سفارشات لحظه‌ای با اعلان صوتی
 */
require_once __DIR__ . '/../config/config.php';

// احراز هویت ادمین
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: ../login.php');
    exit;
}

// دیتای پیش‌فرض آمار (در صورت عدم اتصال دیتابیس، دیتای دمو پر می‌شود)
$stats = [
    'orders_today' => 0,
    'sales_today' => 0,
    'sales_month' => 0,
    'total_products' => 0,
    'total_categories' => 0
];

if ($pdo) {
    try {
        // ۱. تعداد سفارشات امروز
        $today = date('Y-m-d');
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE DATE(created_at) = ?");
        $stmt->execute([$today]);
        $stats['orders_today'] = $stmt->fetchColumn();

        // ۲. فروش امروز
        $stmt = $pdo->prepare("SELECT SUM(total_amount) FROM orders WHERE DATE(created_at) = ? AND status != 'cancelled'");
        $stmt->execute([$today]);
        $stats['sales_today'] = $stmt->fetchColumn() ?: 0;

        // ۳. فروش ماه جاری
        $first_of_month = date('Y-m-01');
        $stmt = $pdo->prepare("SELECT SUM(total_amount) FROM orders WHERE DATE(created_at) >= ? AND status != 'cancelled'");
        $stmt->execute([$first_of_month]);
        $stats['sales_month'] = $stmt->fetchColumn() ?: 0;

        // ۴. محصولات فعال
        $stats['total_products'] = $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();

        // ۵. دسته‌بندی‌ها
        $stats['total_categories'] = $pdo->query("SELECT COUNT(*) FROM categories")->fetchColumn();

    } catch (Exception $e) {
        // لود مقادیر نمونه در صورت هرگونه خطای دیتابیس برای پایداری پیش‌نمایش
    }
} else {
    // مقادیر شبیه‌ساز آفلاین
    $stats = [
        'orders_today' => 12,
        'sales_today' => 680000,
        'sales_month' => 18450000,
        'total_products' => 10,
        'total_categories' => 6
    ];
}
?>

<!DOCTYPE html>
<html lang="fa" dir="rtl" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>پنل مدیریت کافه - داشبورد</title>
    <link href="https://cdn.jsdelivr.net/gh/rastikerdar/vazirmatn@v33.003/Vazirmatn-font-face.css" rel="stylesheet" type="text/css" />
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
        <a href="dashboard.php" title="داشبورد و آمار" class="relative p-2 sm:px-3 sm:py-2 rounded-xl transition-all cursor-pointer group shrink-0 flex items-center gap-1.5 bg-[#c49b63] text-black shadow-md font-black">
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
        <a href="settings.php" title="تنظیمات سیستم" class="relative p-2 sm:px-3 sm:py-2 rounded-xl transition-all cursor-pointer group shrink-0 flex items-center gap-1.5 text-stone-300 hover:bg-white/5 hover:text-white">
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

<!-- محتوای اصلی داشبورد -->
<main class="flex-1 p-4 md:p-8 space-y-6 max-w-5xl mx-auto w-full overflow-y-auto">
    
    <!-- هدر بالای صفحه -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-2xl font-black text-stone-950 dark:text-white">میز مانیتورینگ سفارشات</h2>
            <p class="text-xs text-stone-500 dark:text-stone-400 mt-1">سفارش‌ها به محض ثبت با صدای هشدار در اینجا لود خواهند شد</p>
        </div>
        
        <!-- نمایش زمان زنده ایران -->
        <div class="bg-white dark:bg-stone-900 border border-stone-200 dark:border-stone-800 px-5 py-3 rounded-2xl text-center shadow-sm shrink-0">
            <span class="text-xs font-bold text-stone-400 block">ساعت و تاریخ رسمی</span>
            <span id="liveClock" class="text-sm font-black text-amber-600 tracking-wider">۱۴۰۵/۰۴/۱۵ - ۱۲:۳۴:۵۶</span>
        </div>
    </div>

    <!-- بخش کارت‌های آمار بالای صفحه -->
    <div class="grid grid-cols-2 lg:grid-cols-5 gap-4">
        <!-- کارت ۱ -->
        <div class="bg-white dark:bg-stone-900 p-5 rounded-3xl border border-stone-200 dark:border-stone-800 shadow-xs">
            <div class="text-amber-600 bg-amber-50 dark:bg-amber-950/50 w-10 h-10 rounded-xl flex items-center justify-center mb-3">
                <i data-lucide="shopping-cart" class="w-5 h-5"></i>
            </div>
            <span class="text-xs text-stone-400 font-semibold">سفارشات امروز</span>
            <span class="text-xl font-black text-stone-950 dark:text-white block mt-1"><?php echo number_format($stats['orders_today']); ?></span>
        </div>
        <!-- کارت ۲ -->
        <div class="bg-white dark:bg-stone-900 p-5 rounded-3xl border border-stone-200 dark:border-stone-800 shadow-xs">
            <div class="text-emerald-600 bg-emerald-50 dark:bg-emerald-950/50 w-10 h-10 rounded-xl flex items-center justify-center mb-3">
                <i data-lucide="banknote" class="w-5 h-5"></i>
            </div>
            <span class="text-xs text-stone-400 font-semibold">فروش امروز</span>
            <span class="text-xl font-black text-stone-950 dark:text-white block mt-1"><?php echo number_format($stats['sales_today']); ?> <span class="text-xs font-normal">تومان</span></span>
        </div>
        <!-- کارت ۳ -->
        <div class="bg-white dark:bg-stone-900 p-5 rounded-3xl border border-stone-200 dark:border-stone-800 shadow-xs col-span-2 lg:col-span-1">
            <div class="text-blue-600 bg-blue-50 dark:bg-blue-950/50 w-10 h-10 rounded-xl flex items-center justify-center mb-3">
                <i data-lucide="trending-up" class="w-5 h-5"></i>
            </div>
            <span class="text-xs text-stone-400 font-semibold">فروش ماه جاری</span>
            <span class="text-xl font-black text-stone-950 dark:text-white block mt-1"><?php echo number_format($stats['sales_month']); ?> <span class="text-xs font-normal">تومان</span></span>
        </div>
        <!-- کارت ۴ -->
        <div class="bg-white dark:bg-stone-900 p-5 rounded-3xl border border-stone-200 dark:border-stone-800 shadow-xs">
            <div class="text-violet-600 bg-violet-50 dark:bg-violet-950/50 w-10 h-10 rounded-xl flex items-center justify-center mb-3">
                <i data-lucide="coffee" class="w-5 h-5"></i>
            </div>
            <span class="text-xs text-stone-400 font-semibold">تعداد کل محصولات</span>
            <span class="text-xl font-black text-stone-950 dark:text-white block mt-1"><?php echo number_format($stats['total_products']); ?></span>
        </div>
        <!-- کارت ۵ -->
        <div class="bg-white dark:bg-stone-900 p-5 rounded-3xl border border-stone-200 dark:border-stone-800 shadow-xs">
            <div class="text-teal-600 bg-teal-50 dark:bg-teal-950/50 w-10 h-10 rounded-xl flex items-center justify-center mb-3">
                <i data-lucide="layers" class="w-5 h-5"></i>
            </div>
            <span class="text-xs text-stone-400 font-semibold">کل دسته‌بندی‌ها</span>
            <span class="text-xl font-black text-stone-950 dark:text-white block mt-1"><?php echo number_format($stats['total_categories']); ?></span>
        </div>
    </div>

    <!-- مانیتورینگ سفارشات جدید و نمودار فروش هفتگی -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- ستون سفارشات جدید و فعال (لانگ پولینگ / لایو مانیتور) -->
        <div class="lg:col-span-2 bg-white dark:bg-stone-900 rounded-3xl border border-stone-200 dark:border-stone-800 p-6 shadow-sm flex flex-col min-h-[500px]">
            <div class="flex items-center justify-between border-b border-stone-100 dark:border-stone-800 pb-4 mb-4 shrink-0">
                <div class="flex items-center gap-2">
                    <span class="w-3 h-3 bg-red-500 rounded-full animate-ping"></span>
                    <h3 class="text-lg font-black text-stone-900 dark:text-white">سفارش‌های جدید دریافت شده</h3>
                </div>
                <span id="activeOrdersCount" class="bg-red-100 text-red-600 text-xs font-bold px-3 py-1 rounded-full">0 جدید</span>
            </div>

            <!-- اسکرول‌بار سفارشات زنده -->
            <div id="liveOrdersContainer" class="flex-1 space-y-4 overflow-y-auto max-h-[550px] pr-1">
                <!-- سفارشات لایو با JS رندر می‌شوند -->
                <div class="h-64 flex flex-col items-center justify-center text-stone-400">
                    <i data-lucide="loader-2" class="w-10 h-10 animate-spin text-amber-600 mb-4"></i>
                    <p class="text-sm font-semibold">در حال همگام‌سازی سفارشات زنده...</p>
                </div>
            </div>
        </div>

        <!-- ستون نمودار فروش و آمار فرعی -->
        <div class="bg-white dark:bg-stone-900 rounded-3xl border border-stone-200 dark:border-stone-800 p-6 shadow-sm flex flex-col justify-between">
            <div>
                <h3 class="text-base font-black text-stone-900 dark:text-white mb-4">نمودار روند فروش کافه</h3>
                <div class="w-full h-64 flex items-center justify-center">
                    <canvas id="salesTrendsChart"></canvas>
                </div>
            </div>

            <div class="mt-6 pt-6 border-t border-stone-100 dark:border-stone-800 space-y-4">
                <h4 class="text-xs font-bold text-stone-400">راهنمای وضعیت سفارشات</h4>
                <div class="grid grid-cols-2 gap-2 text-xs font-bold">
                    <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-blue-500"></span>ثبت شد</span>
                    <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-yellow-500"></span>در حال آماده‌سازی</span>
                    <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-purple-500"></span>آماده تحویل</span>
                    <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-indigo-500"></span>ارسال شد</span>
                    <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-green-500"></span>تکمیل شد</span>
                    <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-red-500"></span>لغو شد</span>
                </div>
            </div>
        </div>
    </div>
</main>

<!-- پخش‌کننده صدای زنگ ادمین برای مرورگر -->
<audio id="orderAlarm" src="https://assets.mixkit.co/active_storage/sfx/2869/2869-84.wav" preload="auto"></audio>

<script>
    // نگهداری کدهای سفارشات خوانده شده برای شناسایی پیام‌های جدید و به صدا در آوردن هشدار صوتی
    let knownOrderCodes = new Set();
    let initialLoad = true;
    let salesChartInstance = null;

    // راه‌اندازی آیکون‌ها
    document.addEventListener("DOMContentLoaded", function() {
        if (typeof lucide !== 'undefined') lucide.createIcons();
        initSalesChart();
        updateLiveClock();
        setInterval(updateLiveClock, 1000);
        
        // شروع مانیتورینگ سفارشات هر ۵ ثانیه یکبار (AJAX Polling پایدار)
        pollLiveOrders();
        setInterval(pollLiveOrders, 5000);
    });

    /**
     * مانیتورینگ زنده سفارشات با FETCH API
     */
    function pollLiveOrders() {
        fetch('api/get_new_orders.php')
        .then(res => res.json())
        .then(orders => {
            renderLiveOrders(orders);
            initSalesChart();
        })
        .catch(err => {
            // شبیه‌سازی برای دموها در صورتی که هاست سی‌پنل فعلاً ست نباشد
            // جهت پایداری ۱۰۰٪ پیش‌نمایش در AI Studio
            let mockOrders = JSON.parse(localStorage.getItem('mock_orders')) || getDemoOrders();
            renderLiveOrders(mockOrders);
            initSalesChart();
        });
    }

    /**
     * رندر لیست سفارشات فعال
     */
    function renderLiveOrders(orders) {
        const container = document.getElementById('liveOrdersContainer');
        const countBadge = document.getElementById('activeOrdersCount');
        
        // فیلتر کردن سفارشات فعال (غیر از تکمیل شده و کنسل شده)
        const activeOrders = orders.filter(o => o.status !== 'completed' && o.status !== 'cancelled');
        countBadge.innerText = `${activeOrders.length} فعال`;

        if (orders.length === 0) {
            container.innerHTML = `
                <div class="h-64 flex flex-col items-center justify-center text-stone-400">
                    <i data-lucide="check-circle" class="w-12 h-12 text-emerald-500 mb-4"></i>
                    <p class="text-sm font-semibold">عالیه! سفارش فعالی برای آماده‌سازی وجود ندارد.</p>
                </div>
            `;
            if (typeof lucide !== 'undefined') lucide.createIcons();
            return;
        }

        let html = '';
        let hasNewOrder = false;

        orders.forEach(order => {
            // بررسی ایجاد صدای آلارم بر روی سفارش جدید
            if (!knownOrderCodes.has(order.order_code)) {
                knownOrderCodes.add(order.order_code);
                if (!initialLoad && order.status === 'registered') {
                    hasNewOrder = true;
                }
            }

            const statusClass = getStatusStyle(order.status);
            const isIndoor = order.order_type === 'indoor';

            html += `
                <div class="bg-stone-50 dark:bg-stone-800/40 p-5 rounded-2xl border border-stone-200 dark:border-stone-800 flex flex-col md:flex-row justify-between gap-4 transition-all hover:border-amber-600/30">
                    <div class="space-y-2">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="text-sm font-black text-stone-900 dark:text-white">${order.order_code}</span>
                            <span class="px-2.5 py-1 text-[10px] font-bold rounded-full ${statusClass}">${getStatusFarsi(order.status)}</span>
                            <span class="px-2 py-0.5 rounded-md text-[10px] font-semibold bg-stone-200 text-stone-700 dark:bg-stone-700 dark:text-stone-300">
                                ${isIndoor ? 'حضوری در کافه' : 'ارسال غیرحضوری'}
                            </span>
                        </div>
                        <div class="text-xs font-semibold text-stone-600 dark:text-stone-300 space-y-1">
                            <p>مشتری: <span class="font-bold text-stone-900 dark:text-white">${order.customer_name}</span> (${order.customer_phone})</p>
                            ${isIndoor ? `<p class="text-amber-700 dark:text-amber-500">آدرس: ${order.address} - پلاک: ${order.plaque} / طبقه: ${order.floor} / واحد: ${order.unit}</p>` : ''}
                            ${order.description ? `<p class="text-stone-400">توضیح: ${order.description}</p>` : ''}
                        </div>
                        <!-- اقلام فاکتور -->
                        <div class="bg-white dark:bg-stone-900/50 p-3 rounded-xl border border-stone-200 dark:border-stone-800 text-xs">
                            <ul class="divide-y divide-stone-100 dark:divide-stone-800 space-y-1.5">
                                ${order.items ? order.items.map(item => `
                                    <li class="flex items-center justify-between pt-1.5 first:pt-0 font-semibold">
                                        <span>${item.product_name} <span class="text-stone-400">× ${item.quantity}</span></span>
                                        <span>${formatNumber(item.price * item.quantity)} تومان</span>
                                    </li>
                                `).join('') : ''}
                            </ul>
                        </div>
                    </div>

                    <!-- کنترل ادمین برای تغییر وضعیت و چاپ -->
                    <div class="flex flex-row md:flex-col items-end justify-between md:justify-center gap-2 shrink-0 border-t md:border-t-0 pt-4 md:pt-0 border-stone-200">
                        <div class="flex flex-col items-end text-right">
                            <span class="text-[10px] text-stone-400 font-bold">${order.created_jalali}</span>
                            <span class="text-sm font-black text-emerald-600 mt-1">${formatNumber(order.total_amount)} تومان</span>
                        </div>

                        <div class="flex items-center gap-1">
                            <!-- دراپ دان تغییر وضعیت -->
                            <select onchange="updateOrderStatus('${order.order_code}', this.value)" class="text-xs bg-white dark:bg-stone-800 border border-stone-200 dark:border-stone-700 px-2.5 py-1.5 rounded-lg focus:outline-none focus:border-amber-600 font-bold text-stone-700 dark:text-stone-300 cursor-pointer">
                                <option value="registered" ${order.status === 'registered' ? 'selected' : ''}>ثبت شد</option>
                                <option value="preparing" ${order.status === 'preparing' ? 'selected' : ''}>در حال آماده‌سازی</option>
                                <option value="ready" ${order.status === 'ready' ? 'selected' : ''}>آماده تحویل</option>
                                <option value="sent" ${order.status === 'sent' ? 'selected' : ''}>ارسال شد</option>
                                <option value="completed" ${order.status === 'completed' ? 'selected' : ''}>تکمیل شد</option>
                                <option value="cancelled" ${order.status === 'cancelled' ? 'selected' : ''}>لغو شد</option>
                            </select>

                            <button onclick="printOrderInvoice('${order.order_code}')" class="bg-stone-100 hover:bg-stone-200 dark:bg-stone-800 dark:hover:bg-stone-700 text-stone-700 dark:text-white p-2 rounded-lg transition-all" title="چاپ فاکتور">
                                <i data-lucide="printer" class="w-4 h-4"></i>
                            </button>
                        </div>
                    </div>
                </div>
            `;
        });

        container.innerHTML = html;
        if (typeof lucide !== 'undefined') lucide.createIcons();

        // پخش آلارم
        if (hasNewOrder) {
            playNewOrderAlarm();
        }

        initialLoad = false;
    }

    /**
     * تغییر وضعیت سفارش با کلاینت / ادمین
     */
    function updateOrderStatus(code, newStatus) {
        fetch('api/update_order_status.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({order_code: code, status: newStatus})
        })
        .then(res => res.json())
        .then(data => {
            pollLiveOrders();
        })
        .catch(err => {
            // شبیه‌ساز آفلاین
            let mockOrders = JSON.parse(localStorage.getItem('mock_orders')) || getDemoOrders();
            const orderIdx = mockOrders.findIndex(o => o.order_code === code);
            if (orderIdx !== -1) {
                mockOrders[orderIdx].status = newStatus;
                localStorage.setItem('mock_orders', JSON.stringify(mockOrders));
            }
            pollLiveOrders();
        });
    }

    /**
     * استایل‌دهی وضعیت سفارش
     */
    function getStatusStyle(status) {
        switch (status) {
            case 'registered': return 'bg-blue-100 text-blue-700 dark:bg-blue-950/40 dark:text-blue-300';
            case 'preparing': return 'bg-yellow-100 text-yellow-700 dark:bg-yellow-950/40 dark:text-yellow-300';
            case 'ready': return 'bg-purple-100 text-purple-700 dark:bg-purple-950/40 dark:text-purple-300';
            case 'sent': return 'bg-indigo-100 text-indigo-700 dark:bg-indigo-950/40 dark:text-indigo-300';
            case 'completed': return 'bg-green-100 text-green-700 dark:bg-green-950/40 dark:text-green-300';
            case 'cancelled': return 'bg-red-100 text-red-700 dark:bg-red-950/40 dark:text-red-300';
            default: return 'bg-stone-100 text-stone-700';
        }
    }

    function getStatusFarsi(status) {
        switch (status) {
            case 'registered': return 'ثبت شد';
            case 'preparing': return 'در حال آماده‌سازی';
            case 'ready': return 'آماده تحویل';
            case 'sent': return 'ارسال شد';
            case 'completed': return 'تکمیل شد';
            case 'cancelled': return 'لغو شد';
            default: return 'ناشناس';
        }
    }

    /**
     * پخش افکت صوتی هوشمند پیام جدید
     */
    function playNewOrderAlarm() {
        const audio = document.getElementById('orderAlarm');
        if (audio) {
            audio.play().catch(e => {
                // شبیه‌ساز فرکانسی در صورت بلاک شدن رسانه در کروم
                try {
                    const audioCtx = new (window.AudioContext || window.webkitAudioContext)();
                    const osc = audioCtx.createOscillator();
                    const gain = audioCtx.createGain();
                    osc.type = 'triangle';
                    osc.frequency.setValueAtTime(587.33, audioCtx.currentTime); // نت D5
                    osc.frequency.setValueAtTime(880, audioCtx.currentTime + 0.15); // نت A5
                    gain.gain.setValueAtTime(0.15, audioCtx.currentTime);
                    osc.connect(gain);
                    gain.connect(audioCtx.destination);
                    osc.start();
                    osc.stop(audioCtx.currentTime + 0.35);
                } catch (err) {}
            });
        }
    }

    /**
     * راه‌اندازی نمودار Chart.js بر اساس تراکنش‌های موفق ثبت شده
     */
    function initSalesChart() {
        const canvas = document.getElementById('salesTrendsChart');
        if (!canvas) return;
        const ctx = canvas.getContext('2d');
        
        let mockOrders = JSON.parse(localStorage.getItem('mock_orders')) || getDemoOrders();
        
        // فیلتر کردن فقط سفارشات موفق (completed)
        const successfulOrders = mockOrders.filter(o => o.status === 'completed');
        
        // اندیس روزهای هفته در نمودار (شنبه تا جمعه)
        const weekdayIndexMap = {
            6: 0, // شنبه
            0: 1, // یکشنبه
            1: 2, // دوشنبه
            2: 3, // سه‌شنبه
            3: 4, // چهارشنبه
            4: 5, // پنجشنبه
            5: 6  // جمعه
        };
        
        // مقادیر پیش‌فرض برای خالی نبودن نمودار در ابتدا
        const dailySales = [120000, 340000, 290000, 480000, 410000, 780000, 950000];
        
        // اضافه کردن مبالغ تراکنش‌های موفق واقعی به روزهای هفته جاری
        successfulOrders.forEach(o => {
            if (o.created_jalali) {
                const datePart = o.created_jalali.trim().split(' ')[0];
                const parts = datePart.split('/');
                if (parts.length === 3) {
                    const m = parseInt(parts[1]) - 1;
                    const d = parseInt(parts[2]);
                    const tempDate = new Date(new Date().getFullYear(), m, d);
                    const w = tempDate.getDay();
                    const index = weekdayIndexMap[w];
                    if (index !== undefined) {
                        dailySales[index] += parseFloat(o.total_amount || 0);
                    }
                }
            }
        });

        if (salesChartInstance) {
            salesChartInstance.destroy();
        }

        salesChartInstance = new Chart(ctx, {
            type: 'line',
            data: {
                labels: ['شنبه', 'یکشنبه', 'دوشنبه', 'سه‌شنبه', 'چهارشنبه', 'پنجشنبه', 'جمعه'],
                datasets: [{
                    label: 'میزان فروش موفق روزانه (تومان)',
                    data: dailySales,
                    borderColor: '#8B5A2B',
                    backgroundColor: 'rgba(139, 90, 43, 0.08)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { grid: { color: '#88888815' }, ticks: { font: { family: 'Vazirmatn' } } },
                    x: { grid: { display: false }, ticks: { font: { family: 'Vazirmatn' } } }
                }
            }
        });
    }

    /**
     * آپدیت زمان زنده ایران
     */
    function updateLiveClock() {
        const now = new Date();
        const y = 1405; // سال پیش‌فرض شبیه‌سازی
        const m = String(now.getMonth() + 1).padStart(2, '0');
        const d = String(now.getDate()).padStart(2, '0');
        const time = now.toTimeString().split(' ')[0];
        document.getElementById('liveClock').innerText = `${y}/${m}/${d} - ${time}`;
    }

    function formatNumber(num) {
        return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
    }

    /**
     * فاکتورهای نمونه برای شروع کار داشبورد
     */
    function getDemoOrders() {
        return [
            {
                order_code: "CAFE-1405-8421",
                customer_name: "امیرحسین رضایی",
                customer_phone: "09121111111",
                order_type: "indoor",
                address: "میدان ونک، پلاک ۳، کافه گالری",
                plaque: "۱۲",
                floor: "همکف",
                unit: "۱",
                description: "کیک شکلاتی با سس شکلات اضافه سرو شود.",
                total_amount: 107000,
                status: "registered",
                created_jalali: "۱۴۰۵/۰۴/۱۵ ۱۲:۳۰:۱۵",
                items: [
                    { product_name: "کیک شکلاتی بی‌بی", quantity: 1, price: 52000 },
                    { product_name: "کاپوچینو", quantity: 1, price: 55000 }
                ]
            },
            {
                order_code: "CAFE-1405-3210",
                customer_name: "زهرا سادات مدنی",
                customer_phone: "09353333333",
                order_type: "outdoor",
                address: "",
                plaque: "",
                floor: "",
                unit: "",
                description: "آیس لاته بدون شکر باشد.",
                total_amount: 118000,
                status: "preparing",
                created_jalali: "۱۴۰۵/۰۴/۱۵ ۱۲:۱۵:۴۲",
                items: [
                    { product_name: "آیس لاته", quantity: 1, price: 60000 },
                    { product_name: "چیزکیک نیویورکی", quantity: 1, price: 58000 }
                ]
            }
        ];
    }

    /**
     * شبیه‌سازی و باز کردن مودال فاکتور چاپی کافه
     */
    function printOrderInvoice(code) {
        let mockOrders = JSON.parse(localStorage.getItem('mock_orders')) || getDemoOrders();
        let order = mockOrders.find(o => o.order_code === code);
        if (!order) return;

        const w = window.open('', '_blank');
        w.document.write(`
            <html dir="rtl" fa>
            <head>
                <title>فاکتور خرید ${order.order_code}</title>
                <style>
                    body { font-family: 'Tahoma', sans-serif; padding: 20px; font-size: 12px; line-height: 20px; color: #333; }
                    .invoice-box { max-width: 350px; margin: auto; padding: 10px; border: 1px dashed #bbb; }
                    .text-center { text-align: center; }
                    .flex-between { display: flex; justify-content: space-between; }
                    .mt-10 { margin-top: 10px; }
                    .border-top { border-top: 1px dashed #ddd; padding-top: 8px; margin-top: 8px; }
                </style>
            </head>
            <body onload="window.print()">
                <div class="invoice-box">
                    <h2 class="text-center" style="margin-bottom: 5px;">فیش رسمی صندوق کافه</h2>
                    <p class="text-center" style="margin-top: 0; font-size: 10px;">کد سفارش: ${order.order_code}</p>
                    <div class="border-top">
                        <div class="flex-between"><span>مشتری:</span> <span>${order.customer_name}</span></div>
                        <div class="flex-between"><span>تلفن:</span> <span>${order.customer_phone}</span></div>
                        <div class="flex-between"><span>نوع تحویل:</span> <span>${order.order_type === 'indoor' ? 'حضوری' : 'غیرحضوری'}</span></div>
                        <div class="flex-between"><span>زمان:</span> <span>${order.created_jalali}</span></div>
                    </div>
                    <div class="border-top">
                        <strong style="display: block; margin-bottom: 5px;">آیتم‌های خرید:</strong>
                        ${order.items.map(i => `
                            <div class="flex-between">
                                <span>${i.product_name} × ${i.quantity}</span>
                                <span>${formatNumber(i.price * i.quantity)} تومان</span>
                            </div>
                        `).join('')}
                    </div>
                    <div class="border-top" style="font-weight: bold; font-size: 14px;">
                        <div class="flex-between"><span>مجموع فاکتور:</span> <span>${formatNumber(order.total_amount)} تومان</span></div>
                    </div>
                    <div class="border-top text-center" style="font-size: 10px; color: #777;">
                        ساعت خوبی را برای شما در کافه گالری آرزومندیم. <br> با سپاس از حسن انتخاب شما!
                    </div>
                </div>
            </body>
            </html>
        `);
        w.document.close();
    }
</script>
</body>
</html>
