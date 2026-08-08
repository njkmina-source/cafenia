<?php
/**
 * گزارش‌گیری هوشمند مالی کافه همراه با خروجی اکسل (CSV UTF-8) و نسخه چاپی
 */
require_once __DIR__ . '/../config/config.php';

// احراز هویت ادمین
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: ../login.php');
    exit;
}

$orders_report = [];
$total_income = 0;
$total_orders_count = 0;
$cancelled_count = 0;

$range = $_GET['range'] ?? 'today';

// واکشی اطلاعات گزارش از دیتابیس
if ($pdo) {
    try {
        $query = "SELECT * FROM orders WHERE 1=1";
        $params = [];

        if ($range === 'today') {
            $query .= " AND DATE(created_at) = CURDATE()";
        } elseif ($range === 'weekly') {
            $query .= " AND DATE(created_at) >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)";
        } elseif ($range === 'monthly') {
            $query .= " AND DATE(created_at) >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)";
        } elseif ($range === 'annual') {
            $query .= " AND DATE(created_at) >= DATE_SUB(CURDATE(), INTERVAL 365 DAY)";
        }

        $query .= " ORDER BY id DESC";
        $stmt = $pdo->prepare($query);
        $stmt->execute();
        $orders_report = $stmt->fetchAll();

        // واکشی اقلام هر فاکتور
        foreach ($orders_report as &$order) {
            $stmt_items = $pdo->prepare("SELECT * FROM order_items WHERE order_id = ?");
            $stmt_items->execute([$order['id']]);
            $order['items'] = $stmt_items->fetchAll();

            if ($order['status'] !== 'cancelled') {
                $total_income += $order['total_amount'];
                $total_orders_count++;
            } else {
                $cancelled_count++;
            }
        }
    } catch (Exception $e) {}
} else {
    // شبیه‌ساز آفلاین گزارش
    $orders_report = [
        [
            'order_code' => "CAFE-1405-8421",
            'customer_name' => "امیرحسین رضایی",
            'customer_phone' => "09121111111",
            'order_type' => "indoor",
            'total_amount' => 107000,
            'status' => "completed",
            'created_jalali' => "۱۴۰۵/۰۴/۱۵ ۱۲:۳۰:۱۵",
            'items' => [['product_name' => 'کاپوچینو', 'quantity' => 1, 'price' => 55000]]
        ],
        [
            'order_code' => "CAFE-1405-3210",
            'customer_name' => "زهرا سادات مدنی",
            'customer_phone' => "09353333333",
            'order_type' => "outdoor",
            'total_amount' => 118000,
            'status' => "preparing",
            'created_jalali' => "۱۴۰۵/۰۴/۱۵ ۱۲:۱۵:۴۲",
            'items' => [['product_name' => 'آیس لاته', 'quantity' => 1, 'price' => 60000]]
        ],
        [
            'order_code' => "CAFE-1405-1943",
            'customer_name' => "محمد علوی",
            'customer_phone' => "09192222222",
            'order_type' => "indoor",
            'total_amount' => 45000,
            'status' => "cancelled",
            'created_jalali' => "۱۴۰۵/۰۴/۱۴ ۱۷:۲۴:۰۰",
            'items' => [['product_name' => 'اسپرسو دوبل', 'quantity' => 1, 'price' => 45000]]
        ]
    ];
    $total_income = 225000;
    $total_orders_count = 2;
    $cancelled_count = 1;
}

// خروجی فایل PDF رسمی (A4 فارسی به همراه فونت وزیر و استایل شیک چاپی)
if (isset($_GET['export']) && $_GET['export'] === 'pdf') {
    ?>
    <!DOCTYPE html>
    <html lang="fa" dir="rtl">
    <head>
        <meta charset="UTF-8">
        <title>گزارش فروش رسمی کافه گالری - بازه <?php echo $range; ?></title>
        <style>
            @import url('https://fonts.googleapis.com/css2?family=Vazirmatn:wght@400;700;900&display=swap');
            body {
                font-family: 'Vazirmatn', sans-serif;
                direction: rtl;
                padding: 40px;
                background-color: #ffffff;
                color: #1c1917;
            }
            .header {
                display: flex;
                justify-content: space-between;
                align-items: center;
                border-bottom: 2px solid #c49b63;
                padding-bottom: 20px;
                margin-bottom: 30px;
            }
            .logo-title {
                display: flex;
                align-items: center;
                gap: 12px;
            }
            .title {
                font-size: 20px;
                font-weight: 900;
                color: #1c1917;
            }
            .subtitle {
                font-size: 11px;
                color: #78716c;
                margin-top: 4px;
            }
            .date-info {
                text-align: left;
                font-size: 11px;
                color: #444;
            }
            .stats-grid {
                display: grid;
                grid-template-columns: repeat(3, 1fr);
                gap: 20px;
                margin-bottom: 30px;
            }
            .stat-box {
                border: 1px solid #e7e5e4;
                border-radius: 16px;
                padding: 16px;
                text-align: center;
                background: #fafaf9;
            }
            .stat-label {
                font-size: 10px;
                color: #78716c;
                font-weight: bold;
            }
            .stat-value {
                font-size: 16px;
                font-weight: 900;
                color: #1c1917;
                margin-top: 6px;
            }
            .table-title {
                font-size: 13px;
                font-weight: 900;
                color: #1c1917;
                margin-bottom: 12px;
            }
            table {
                width: 100%;
                border-collapse: collapse;
                font-size: 11px;
                text-align: right;
            }
            th {
                background-color: #fafaf9;
                border-bottom: 2px solid #e7e5e4;
                padding: 12px;
                font-weight: bold;
                color: #78716c;
            }
            td {
                padding: 12px;
                border-bottom: 1px solid #f5f5f4;
                color: #444;
            }
            tr:hover {
                background-color: #fafaf9;
            }
            .footer {
                margin-top: 50px;
                border-top: 1px dashed #e7e5e4;
                padding-top: 20px;
                text-align: center;
                font-size: 10px;
                color: #a8a29e;
            }
            @media print {
                body { padding: 0; }
                .no-print { display: none; }
            }
        </style>
    </head>
    <body onload="window.print()">
        <div class="header">
            <div class="logo-title">
                <div>
                    <div class="title">گزارش مالی رسمی کافه گالری</div>
                    <div class="subtitle">فهرست کل تراکنش‌ها و درآمدهای حاصله صندوق ادمین</div>
                </div>
            </div>
            <div class="date-info">
                <div>بازه گزارش: <?php 
                    if ($range === 'today') echo 'امروز';
                    elseif ($range === 'weekly') echo '۷ روز گذشته';
                    else echo '۳۰ روز گذشته';
                ?></div>
                <div style="margin-top: 4px;">تاریخ چاپ گزارش: <?php echo date('Y/m/d'); ?></div>
            </div>
        </div>

        <div class="stats-grid">
            <div class="stat-box">
                <div class="stat-label">مجموع درآمد تایید شده</div>
                <div class="stat-value" style="color: #15803d;"><?php echo number_format($total_income); ?> تومان</div>
            </div>
            <div class="stat-box">
                <div class="stat-label">تعداد کل سفارشات موثر</div>
                <div class="stat-value"><?php echo number_format($total_orders_count); ?> سفارش</div>
            </div>
            <div class="stat-box">
                <div class="stat-label">سفارشات لغو شده ادمین</div>
                <div class="stat-value" style="color: #b91c1c;"><?php echo number_format($cancelled_count); ?> لغو</div>
            </div>
        </div>

        <div class="table-title">فهرست ریز تراکنش‌ها و فاکتورها</div>
        <table>
            <thead>
                <tr>
                    <th>شماره سفارش</th>
                    <th>مشتری</th>
                    <th>نوع تحویل</th>
                    <th>تاریخ ثبت</th>
                    <th>مبلغ فاکتور</th>
                    <th>وضعیت</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orders_report as $row): ?>
                    <tr>
                        <td style="font-weight: 700;"><?php echo $row['order_code']; ?></td>
                        <td><?php echo htmlspecialchars($row['customer_name']); ?></td>
                        <td><?php echo $row['order_type'] === 'indoor' ? 'حضوری' : 'غیرحضوری'; ?></td>
                        <td><?php echo $row['created_jalali']; ?></td>
                        <td style="font-weight: 700; color: #15803d;"><?php echo number_format($row['total_amount']); ?> تومان</td>
                        <td><?php echo $row['status'] === 'completed' ? 'تکمیل شده' : ($row['status'] === 'cancelled' ? 'لغو شده' : 'فعال'); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="footer">
            این گزارش به صورت رسمی از پنل مدیریت صندوق کافه صادر شده است و دارای اعتبار قانونی است.
        </div>
    </body>
    </html>
    <?php
    exit;
}
?>

<!DOCTYPE html>
<html lang="fa" dir="rtl" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>گزارشات مالی کافه</title>
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
        <a href="reports.php" title="گزارشات و PDF" class="relative p-2 sm:px-3 sm:py-2 rounded-xl transition-all cursor-pointer group shrink-0 flex items-center gap-1.5 bg-[#c49b63] text-black shadow-md font-black">
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

<main class="flex-1 p-4 md:p-8 space-y-6 max-w-5xl mx-auto w-full overflow-y-auto">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-2xl font-black text-white">گزارشات فروش و فاکتورها</h2>
            <p class="text-xs text-stone-400 mt-1">مشاهده جمع درآمد، فاکتورهای لغو شده و خروجی مستقیم فایل رسمی گزارشات به صورت PDF فارسی</p>
        </div>
        
        <!-- دکمه دانلود PDF -->
        <a href="reports.php?range=<?php echo $range; ?>&export=pdf" target="_blank" class="bg-red-600/80 hover:bg-red-600 text-white px-5 py-3 rounded-2xl font-bold text-sm flex items-center gap-2 shadow-lg transition-all border border-red-500/30">
            <i data-lucide="printer" class="w-5 h-5"></i>
            <span>خروجی فایل PDF رسمی (A4)</span>
        </a>
    </div>

    <!-- فیلتر محدوده زمان گزارش -->
    <div class="flex gap-2">
        <a href="reports.php?range=today" class="px-4 py-2 rounded-xl text-xs font-bold <?php echo $range === 'today' ? 'bg-[#c49b63] text-black shadow-md font-black' : 'bg-white/5 border border-white/10 text-stone-300 hover:text-white'; ?> transition-all">گزارش امروز</a>
        <a href="reports.php?range=weekly" class="px-4 py-2 rounded-xl text-xs font-bold <?php echo $range === 'weekly' ? 'bg-[#c49b63] text-black shadow-md font-black' : 'bg-white/5 border border-white/10 text-stone-300 hover:text-white'; ?> transition-all">۷ روز گذشته</a>
        <a href="reports.php?range=monthly" class="px-4 py-2 rounded-xl text-xs font-bold <?php echo $range === 'monthly' ? 'bg-[#c49b63] text-black shadow-md font-black' : 'bg-white/5 border border-white/10 text-stone-300 hover:text-white'; ?> transition-all">۳۰ روز گذشته</a>
        <a href="reports.php?range=annual" class="px-4 py-2 rounded-xl text-xs font-bold <?php echo $range === 'annual' ? 'bg-[#c49b63] text-black shadow-md font-black' : 'bg-white/5 border border-white/10 text-stone-300 hover:text-white'; ?> transition-all">یک سال اخیر</a>
    </div>

    <!-- کارت‌های آمار خلاصه گزارش مالی -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
        <div class="bg-[#131210] p-6 rounded-3xl border border-white/10 shadow-xs flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 flex items-center justify-center">
                <i data-lucide="wallet" class="w-6 h-6"></i>
            </div>
            <div>
                <span class="text-[10px] text-stone-400 font-bold block">مجموع درآمد (فاکتورهای تایید شده)</span>
                <span class="text-xl font-black text-emerald-400 mt-1 block"><?php echo number_format($total_income); ?> <span class="text-xs font-normal">تومان</span></span>
            </div>
        </div>

        <div class="bg-[#131210] p-6 rounded-3xl border border-white/10 shadow-xs flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-[#c49b63]/20 text-[#c49b63] border border-[#c49b63]/30 flex items-center justify-center">
                <i data-lucide="shopping-bag" class="w-6 h-6"></i>
            </div>
            <div>
                <span class="text-[10px] text-stone-400 font-bold block">تعداد کل سفارشات موثر</span>
                <span class="text-xl font-black text-white mt-1 block"><?php echo number_format($total_orders_count); ?> سفارش</span>
            </div>
        </div>

        <div class="bg-[#131210] p-6 rounded-3xl border border-white/10 shadow-xs flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-red-500/20 text-red-400 border border-red-500/30 flex items-center justify-center">
                <i data-lucide="x-circle" class="w-6 h-6"></i>
            </div>
            <div>
                <span class="text-[10px] text-stone-400 font-bold block">سفارشات لغو شده ادمین</span>
                <span class="text-xl font-black text-red-400 mt-1 block"><?php echo number_format($cancelled_count); ?> لغو شده</span>
            </div>
        </div>
    </div>

    <!-- جدول تراکنش‌های مالی گزارش -->
    <div class="bg-[#131210] rounded-3xl border border-white/10 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-right border-collapse text-xs">
                <thead>
                    <tr class="bg-white/5 border-b border-white/10 text-stone-300 font-bold">
                        <th class="p-4">شماره سفارش</th>
                        <th class="p-4">مشتری</th>
                        <th class="p-4">نوع تحویل</th>
                        <th class="p-4">تاریخ ثبت (شمسی)</th>
                        <th class="p-4">مبلغ کل فاکتور</th>
                        <th class="p-4">وضعیت فاکتور</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/10 font-semibold text-stone-200">
                    <?php if (empty($orders_report)): ?>
                        <tr>
                            <td colspan="6" class="p-8 text-center text-stone-400">تراکنی در بازه زمانی مورد نظر ثبت نشده است.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($orders_report as $row): ?>
                            <tr class="hover:bg-white/5">
                                <td class="p-4 font-bold text-white"><?php echo $row['order_code']; ?></td>
                                <td class="p-4 text-stone-200"><?php echo sanitize($row['customer_name']); ?></td>
                                <td class="p-4 text-stone-300"><?php echo $row['order_type'] === 'indoor' ? 'حضوری' : 'غیرحضوری'; ?></td>
                                <td class="p-4 text-stone-400"><?php echo $row['created_jalali']; ?></td>
                                <td class="p-4 font-black text-emerald-400"><?php echo number_format($row['total_amount']); ?> تومان</td>
                                <td class="p-4">
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold border <?php echo $row['status'] === 'completed' ? 'bg-emerald-500/20 text-emerald-300 border-emerald-500/30' : ($row['status'] === 'cancelled' ? 'bg-red-500/20 text-red-300 border-red-500/30' : 'bg-amber-500/20 text-amber-300 border-amber-500/30'); ?>">
                                        <?php echo $row['status'] === 'completed' ? 'تکمیل شده' : ($row['status'] === 'cancelled' ? 'لغو شده' : 'فعال'); ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
    });
</script>
</body>
</html>
