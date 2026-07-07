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

// خروجی اکسل (CSV به همراه BOM برای زبان فارسی)
if (isset($_GET['export']) && $_GET['export'] === 'excel') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="cafe_sales_report_' . $range . '.csv"');
    
    $output = fopen('php://output', 'w');
    // تزریق BOM جهت پشتیبانی از زبان فارسی در نرم‌افزار اکسل ویندوز
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
    
    fputcsv($output, ['شماره سفارش', 'نام مشتری', 'شماره موبایل', 'نوع تحویل', 'مبلغ کل سفارش (تومان)', 'وضعیت سفارش', 'تاریخ و ساعت شمسی']);
    
    foreach ($orders_report as $row) {
        fputcsv($output, [
            $row['order_code'],
            $row['customer_name'],
            $row['customer_phone'],
            $row['order_type'] === 'indoor' ? 'حضوری' : 'غیرحضوری',
            $row['total_amount'],
            $row['status'] === 'completed' ? 'تکمیل شده' : ($row['status'] === 'cancelled' ? 'لغو شده' : 'در صف آماده‌سازی'),
            $row['created_jalali']
        ]);
    }
    fclose($output);
    exit;
}
?>

<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>گزارشات مالی کافه گالری</title>
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
            <i data-lucide="cup-straw" class="w-5 h-5 text-amber-600"></i>
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
        <a href="reports.php" class="flex items-center gap-3 px-4 py-3 rounded-2xl bg-amber-600 text-white font-bold text-sm shadow-md transition-all">
            <i data-lucide="bar-chart-3" class="w-5 h-5"></i>
            <span>گزارشات و خروجی</span>
        </a>
        <a href="settings.php" class="flex items-center gap-3 px-4 py-3 rounded-2xl text-stone-600 dark:text-stone-300 hover:bg-stone-50 dark:hover:bg-stone-800 font-semibold text-sm transition-all">
            <i data-lucide="settings" class="w-5 h-5 text-amber-600"></i>
            <span>تنظیمات سیستم</span>
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
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-2xl font-black text-stone-950 dark:text-white">گزارشات فروش و فاکتورها</h2>
            <p class="text-xs text-stone-500 dark:text-stone-400 mt-1">مشاهده جمع درآمد، فاکتورهای لغو شده و خروجی مستقیم فایل اکسل (Excel)</p>
        </div>
        
        <!-- دکمه دانلود اکسل -->
        <a href="reports.php?range=<?php echo $range; ?>&export=excel" class="bg-emerald-600 hover:bg-emerald-500 text-white px-5 py-3 rounded-2xl font-bold text-sm flex items-center gap-2 shadow-lg shadow-emerald-600/20 transition-all">
            <i data-lucide="file-spreadsheet" class="w-5 h-5"></i>
            <span>خروجی فایل اکسل</span>
        </a>
    </div>

    <!-- فیلتر محدوده زمان گزارش -->
    <div class="flex gap-2">
        <a href="reports.php?range=today" class="px-4 py-2 rounded-xl text-xs font-bold <?php echo $range === 'today' ? 'bg-primary text-white shadow-md' : 'bg-white dark:bg-stone-900 border border-stone-200 text-stone-600 dark:text-stone-300'; ?> transition-all">گزارش امروز</a>
        <a href="reports.php?range=weekly" class="px-4 py-2 rounded-xl text-xs font-bold <?php echo $range === 'weekly' ? 'bg-primary text-white shadow-md' : 'bg-white dark:bg-stone-900 border border-stone-200 text-stone-600 dark:text-stone-300'; ?> transition-all">۷ روز گذشته</a>
        <a href="reports.php?range=monthly" class="px-4 py-2 rounded-xl text-xs font-bold <?php echo $range === 'monthly' ? 'bg-primary text-white shadow-md' : 'bg-white dark:bg-stone-900 border border-stone-200 text-stone-600 dark:text-stone-300'; ?> transition-all">۳۰ روز گذشته</a>
        <a href="reports.php?range=annual" class="px-4 py-2 rounded-xl text-xs font-bold <?php echo $range === 'annual' ? 'bg-primary text-white shadow-md' : 'bg-white dark:bg-stone-900 border border-stone-200 text-stone-600 dark:text-stone-300'; ?> transition-all">یک سال اخیر</a>
    </div>

    <!-- کارت‌های آمار خلاصه گزارش مالی -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
        <div class="bg-white dark:bg-stone-900 p-6 rounded-3xl border border-stone-200 dark:border-stone-800 shadow-xs flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 flex items-center justify-center">
                <i data-lucide="wallet" class="w-6 h-6"></i>
            </div>
            <div>
                <span class="text-[10px] text-stone-400 font-bold block">مجموع درآمد (فاکتورهای تایید شده)</span>
                <span class="text-xl font-black text-emerald-600 mt-1 block"><?php echo number_format($total_income); ?> <span class="text-xs font-normal">تومان</span></span>
            </div>
        </div>

        <div class="bg-white dark:bg-stone-900 p-6 rounded-3xl border border-stone-200 dark:border-stone-800 shadow-xs flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-blue-50 dark:bg-blue-950/40 text-blue-600 flex items-center justify-center">
                <i data-lucide="shopping-bag" class="w-6 h-6"></i>
            </div>
            <div>
                <span class="text-[10px] text-stone-400 font-bold block">تعداد کل سفارشات موثر</span>
                <span class="text-xl font-black text-stone-950 dark:text-white mt-1 block"><?php echo number_format($total_orders_count); ?> سفارش</span>
            </div>
        </div>

        <div class="bg-white dark:bg-stone-900 p-6 rounded-3xl border border-stone-200 dark:border-stone-800 shadow-xs flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-red-50 dark:bg-red-950/40 text-red-600 flex items-center justify-center">
                <i data-lucide="x-circle" class="w-6 h-6"></i>
            </div>
            <div>
                <span class="text-[10px] text-stone-400 font-bold block">سفارشات لغو شده ادمین</span>
                <span class="text-xl font-black text-red-500 mt-1 block"><?php echo number_format($cancelled_count); ?> لغو شده</span>
            </div>
        </div>
    </div>

    <!-- جدول تراکنش‌های مالی گزارش -->
    <div class="bg-white dark:bg-stone-900 rounded-3xl border border-stone-200 dark:border-stone-800 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-right border-collapse text-xs">
                <thead>
                    <tr class="bg-stone-50 dark:bg-stone-800/50 border-b border-stone-200 dark:border-stone-800 text-stone-500 font-bold">
                        <th class="p-4">شماره سفارش</th>
                        <th class="p-4">مشتری</th>
                        <th class="p-4">نوع تحویل</th>
                        <th class="p-4">تاریخ ثبت (شمسی)</th>
                        <th class="p-4">مبلغ کل فاکتور</th>
                        <th class="p-4">وضعیت فاکتور</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-100 dark:divide-stone-800 font-semibold text-stone-700 dark:text-stone-300">
                    <?php if (empty($orders_report)): ?>
                        <tr>
                            <td colspan="6" class="p-8 text-center text-stone-400">تراکنی در بازه زمانی مورد نظر ثبت نشده است.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($orders_report as $row): ?>
                            <tr class="hover:bg-stone-50/50 dark:hover:bg-stone-800/10">
                                <td class="p-4 font-bold text-stone-900 dark:text-white"><?php echo $row['order_code']; ?></td>
                                <td class="p-4"><?php echo sanitize($row['customer_name']); ?></td>
                                <td class="p-4"><?php echo $row['order_type'] === 'indoor' ? 'حضوری' : 'غیرحضوری'; ?></td>
                                <td class="p-4"><?php echo $row['created_jalali']; ?></td>
                                <td class="p-4 font-black text-emerald-600"><?php echo number_format($row['total_amount']); ?> تومان</td>
                                <td class="p-4">
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] <?php echo $row['status'] === 'completed' ? 'bg-green-100 text-green-700 dark:bg-green-950/40' : ($row['status'] === 'cancelled' ? 'bg-red-100 text-red-700' : 'bg-amber-100 text-amber-700'); ?> font-bold">
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
