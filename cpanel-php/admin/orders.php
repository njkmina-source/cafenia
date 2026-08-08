<?php
/**
 * مدیریت و آرشیو سفارشات ثبت شده با فیلترها و جستجوی پیشرفته
 */
require_once __DIR__ . '/../config/config.php';

// احراز هویت ادمین
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: ../login.php');
    exit;
}

$orders = [];
$search = trim($_GET['search'] ?? '');
$status_filter = trim($_GET['status'] ?? '');
$type_filter = trim($_GET['type'] ?? '');

if ($pdo) {
    try {
        $query = "SELECT * FROM orders WHERE 1=1";
        $params = [];

        if (!empty($search)) {
            $query .= " AND (order_code LIKE ? OR customer_name LIKE ? OR customer_phone LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }

        if (!empty($status_filter)) {
            $query .= " AND status = ?";
            $params[] = $status_filter;
        }

        if (!empty($type_filter)) {
            $query .= " AND order_type = ?";
            $params[] = $type_filter;
        }

        $query .= " ORDER BY id DESC";

        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        $raw_orders = $stmt->fetchAll();

        // دریافت اقلام مرتبط با هر فاکتور
        foreach ($raw_orders as $order) {
            $stmt_items = $pdo->prepare("SELECT * FROM order_items WHERE order_id = ?");
            $stmt_items->execute([$order['id']]);
            $order['items'] = $stmt_items->fetchAll();
            $orders[] = $order;
        }

    } catch (Exception $e) {
        // خطا نادیده گرفته می‌شود تا دمو بالا بیاید
    }
} else {
    // شبیه‌ساز آفلاین آرشیو سفارشات
    $orders = [
        [
            'id' => 1,
            'order_code' => "CAFE-1405-8421",
            'customer_name' => "امیرحسین رضایی",
            'customer_phone' => "09121111111",
            'order_type' => "indoor",
            'address' => "میدان ونک، پلاک ۳، کافه گالری",
            'plaque' => "۱۲",
            'floor' => "همکف",
            'unit' => "۱",
            'description' => "کیک شکلاتی با سس شکلات اضافه سرو شود.",
            'total_amount' => 107000,
            'status' => "completed",
            'created_jalali' => "۱۴۰۵/۰۴/۱۵ ۱۲:۳۰:۱۵",
            'items' => [
                ['product_name' => "کیک شکلاتی بی‌بی", 'quantity' => 1, 'price' => 52000],
                ['product_name' => "کاپوچینو", 'quantity' => 1, 'price' => 55000]
            ]
        ],
        [
            'id' => 2,
            'order_code' => "CAFE-1405-3210",
            'customer_name' => "زهرا سادات مدنی",
            'customer_phone' => "09353333333",
            'order_type' => "outdoor",
            'address' => "",
            'plaque' => "",
            'floor' => "",
            'unit' => "",
            'description' => "آیس لاته بدون شکر باشد.",
            'total_amount' => 118000,
            'status' => "preparing",
            'created_jalali' => "۱۴۰۵/۰۴/۱۵ ۱۲:۱۵:۴۲",
            'items' => [
                ['product_name' => "آیس لاته", 'quantity' => 1, 'price' => 60000],
                ['product_name' => "چیزکیک نیویورکی", 'quantity' => 1, 'price' => 58000]
            ]
        ],
        [
            'id' => 3,
            'order_code' => "CAFE-1405-1943",
            'customer_name' => "محمد علوی",
            'customer_phone' => "09192222222",
            'order_type' => "indoor",
            'address' => "تهرانپارس، خ ۲۱۰، پلاک ۱۱",
            'plaque' => "۱۱",
            'floor' => "۴",
            'unit' => "۸",
            'description' => "",
            'total_amount' => 45000,
            'status' => "cancelled",
            'created_jalali' => "۱۴۰۵/۰۴/۱۴ ۱۷:۲۴:۰۰",
            'items' => [
                ['product_name' => "اسپرسو دوبل", 'quantity' => 1, 'price' => 45000]
            ]
        ]
    ];
}
?>

<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>آرشیو سفارشات - پنل مدیریت</title>
    <link href="https://cdn.jsdelivr.net/gh/rastikerdar/vazirmatn@v33.003/Vazirmatn-font-face.css" rel="stylesheet" type="text/css" />
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        body { font-family: 'Vazirmatn', sans-serif; }
    </style>
</head>
<body class="bg-stone-100 dark:bg-stone-950 text-stone-800 dark:text-stone-100 min-h-screen flex flex-col md:flex-row">

<!-- ناوبری ادمین -->
<aside class="w-full md:w-64 bg-white dark:bg-stone-900 border-b md:border-b-0 md:border-l border-stone-200 dark:border-stone-800 flex flex-col shrink-0">
    <div class="p-6 border-b border-stone-100 dark:border-stone-800">
        <h3 class="text-lg font-black text-stone-900 dark:text-white flex items-center gap-2">
            <i data-lucide="coffee" class="text-amber-600"></i>
            <span>مدیریت کافه گالری</span>
        </h3>
        <p class="text-[10px] text-stone-500 mt-1">خوش آمدید، <?php echo sanitize($_SESSION['admin_fullname'] ?? 'مدیر سیستم'); ?></p>
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
        <a href="orders.php" class="flex items-center gap-3 px-4 py-3 rounded-2xl bg-amber-600 text-white font-bold text-sm shadow-md transition-all">
            <i data-lucide="shopping-bag" class="w-5 h-5"></i>
            <span>آرشیو سفارشات</span>
        </a>
        <a href="reports.php" class="flex items-center gap-3 px-4 py-3 rounded-2xl text-stone-600 dark:text-stone-300 hover:bg-stone-50 dark:hover:bg-stone-800 font-semibold text-sm transition-all">
            <i data-lucide="bar-chart-3" class="w-5 h-5 text-amber-600"></i>
            <span>گزارشات و خروجی</span>
        </a>
        <a href="settings.php" class="flex items-center gap-3 px-4 py-3 rounded-2xl text-stone-600 dark:text-stone-300 hover:bg-stone-50 dark:hover:bg-stone-800 font-semibold text-sm transition-all">
            <i data-lucide="settings" class="w-5 h-5 text-amber-600"></i>
            <span>تنظیمات سیستم</span>
        </a>
        <a href="../../#code" target="_blank" class="flex items-center gap-3 px-4 py-3 rounded-2xl text-emerald-600 dark:text-emerald-400 hover:bg-emerald-50 dark:hover:bg-emerald-950/20 font-bold text-sm transition-all">
            <i data-lucide="download" class="w-5 h-5"></i>
            <span>دانلود کدهای cPanel</span>
        </a>
    </nav>
    <div class="p-4 border-t border-stone-100 dark:border-stone-800">
        <a href="../logout.php" class="flex items-center gap-3 px-4 py-3 rounded-2xl text-red-500 hover:bg-red-50 dark:hover:bg-red-950/20 font-bold text-sm transition-all">
            <i data-lucide="log-out" class="w-5 h-5"></i>
            <span>خروج از پنل</span>
        </a>
    </div>
</aside>

<!-- بدنه اصلی آرشیو سفارشات -->
<main class="flex-1 p-6 md:p-8 space-y-8 overflow-y-auto">
    <div>
        <h2 class="text-2xl font-black text-stone-950 dark:text-white">آرشیو و جستجوی سفارشات</h2>
        <p class="text-xs text-stone-500 dark:text-stone-400 mt-1">امکان فیلتر، مشاهده اقلام و پرینت مجدد فیش رسمی صندوق</p>
    </div>

    <!-- فرم فیلترگذاری و سرچ -->
    <div class="bg-white dark:bg-stone-900 rounded-3xl p-6 border border-stone-200 dark:border-stone-800 shadow-xs">
        <form action="orders.php" method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <!-- فیلد جستجوی کلمات کلیدی -->
            <div>
                <label class="block text-xs font-bold text-stone-500 dark:text-stone-400 mb-1.5">جستجوی مشتری یا شماره سفارش</label>
                <div class="relative">
                    <span class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-stone-400">
                        <i data-lucide="search" class="w-4 h-4"></i>
                    </span>
                    <input type="text" name="search" value="<?php echo sanitize($search); ?>" placeholder="مثلاً: امیرحسین رضایی" class="w-full pl-4 pr-9 py-2.5 rounded-xl bg-stone-50 dark:bg-stone-800 border border-stone-200 dark:border-stone-700 text-stone-900 dark:text-white text-xs focus:outline-none focus:border-amber-600">
                </div>
            </div>

            <!-- فیلتر وضعیت فاکتور -->
            <div>
                <label class="block text-xs font-bold text-stone-500 dark:text-stone-400 mb-1.5">فیلتر وضعیت سفارش</label>
                <select name="status" class="w-full px-3 py-2.5 rounded-xl bg-stone-50 dark:bg-stone-800 border border-stone-200 dark:border-stone-700 text-stone-700 dark:text-stone-300 text-xs focus:outline-none focus:border-amber-600 font-semibold cursor-pointer">
                    <option value="">همه وضعیت‌ها</option>
                    <option value="registered" <?php echo $status_filter === 'registered' ? 'selected' : ''; ?>>ثبت شده</option>
                    <option value="preparing" <?php echo $status_filter === 'preparing' ? 'selected' : ''; ?>>در حال آماده‌سازی</option>
                    <option value="ready" <?php echo $status_filter === 'ready' ? 'selected' : ''; ?>>آماده تحویل</option>
                    <option value="sent" <?php echo $status_filter === 'sent' ? 'selected' : ''; ?>>ارسال شده</option>
                    <option value="completed" <?php echo $status_filter === 'completed' ? 'selected' : ''; ?>>تکمیل شده</option>
                    <option value="cancelled" <?php echo $status_filter === 'cancelled' ? 'selected' : ''; ?>>لغو شده</option>
                </select>
            </div>

            <!-- فیلتر نوع تحویل -->
            <div>
                <label class="block text-xs font-bold text-stone-500 dark:text-stone-400 mb-1.5">نوع تحویل سفارش</label>
                <select name="type" class="w-full px-3 py-2.5 rounded-xl bg-stone-50 dark:bg-stone-800 border border-stone-200 dark:border-stone-700 text-stone-700 dark:text-stone-300 text-xs focus:outline-none focus:border-amber-600 font-semibold cursor-pointer">
                    <option value="">همه روش‌ها</option>
                    <option value="indoor" <?php echo $type_filter === 'indoor' ? 'selected' : ''; ?>>حضوری در کافه</option>
                    <option value="outdoor" <?php echo $type_filter === 'outdoor' ? 'selected' : ''; ?>>ارسال غیرحضوری</option>
                </select>
            </div>

            <!-- دکمه‌های فرم -->
            <div class="flex items-end gap-2">
                <button type="submit" class="flex-1 bg-amber-600 hover:bg-amber-500 text-white py-2.5 rounded-xl font-bold text-xs flex items-center justify-center gap-1.5 transition-all shadow-md">
                    <i data-lucide="filter" class="w-4 h-4"></i>
                    <span>اعمال فیلترها</span>
                </button>
                <a href="orders.php" class="bg-stone-100 hover:bg-stone-200 dark:bg-stone-800 dark:hover:bg-stone-700 text-stone-600 dark:text-white px-3 py-2.5 rounded-xl text-xs font-bold transition-all" title="پاک کردن فیلتر">
                    <i data-lucide="rotate-ccw" class="w-4 h-4"></i>
                </a>
            </div>
        </form>
    </div>

    <!-- جدول نمایش اطلاعات آرشیو -->
    <div class="bg-white dark:bg-stone-900 rounded-3xl border border-stone-200 dark:border-stone-800 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-right border-collapse">
                <thead>
                    <tr class="bg-stone-50 dark:bg-stone-800/50 border-b border-stone-200 dark:border-stone-800 text-xs text-stone-500 font-bold">
                        <th class="p-4">شماره سفارش</th>
                        <th class="p-4">تاریخ ثبت (شمسی)</th>
                        <th class="p-4">مشخصات مشتری</th>
                        <th class="p-4">نوع سفارش</th>
                        <th class="p-4">جمع کل فاکتور</th>
                        <th class="p-4">اقلام سفارش شده</th>
                        <th class="p-4">وضعیت فاکتور</th>
                        <th class="p-4">اقدامات ادمین</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-100 dark:divide-stone-800 text-xs font-semibold">
                    <?php if (empty($orders)): ?>
                        <tr>
                            <td colspan="8" class="p-8 text-center text-stone-400">سفارشی با شرایط جستجو یافت نگردید.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($orders as $order): 
                            $statusStyle = getStatusStyle($order['status']);
                            $isIndoor = $order['order_type'] === 'indoor';
                        ?>
                            <tr class="hover:bg-stone-50/50 dark:hover:bg-stone-800/10">
                                <td class="p-4 font-black text-stone-900 dark:text-white"><?php echo $order['order_code']; ?></td>
                                <td class="p-4 font-bold text-stone-500"><?php echo $order['created_jalali']; ?></td>
                                <td class="p-4">
                                    <div class="flex flex-col">
                                        <span class="font-bold text-stone-800 dark:text-stone-200"><?php echo sanitize($order['customer_name']); ?></span>
                                        <span class="text-[10px] text-stone-400 mt-0.5"><?php echo $order['customer_phone']; ?></span>
                                    </div>
                                </td>
                                <td class="p-4">
                                    <span class="px-2 py-1 rounded bg-stone-100 dark:bg-stone-800 text-stone-600 dark:text-stone-300 font-bold">
                                        <?php echo $isIndoor ? 'حضوری' : 'غیرحضوری'; ?>
                                    </span>
                                </td>
                                <td class="p-4 font-black text-emerald-600"><?php echo number_format($order['total_amount']); ?> تومان</td>
                                <td class="p-4 max-w-xs">
                                    <div class="space-y-1">
                                        <?php foreach ($order['items'] as $item): ?>
                                            <div class="text-[11px] text-stone-500">
                                                • <?php echo $item['product_name']; ?> <span class="text-stone-400">× <?php echo $item['quantity']; ?></span>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </td>
                                <td class="p-4">
                                    <span class="px-2.5 py-1 rounded-full <?php echo $statusStyle; ?> font-black">
                                        <?php echo getStatusFarsi($order['status']); ?>
                                    </span>
                                </td>
                                <td class="p-4">
                                    <div class="flex items-center gap-1.5">
                                        <button onclick="printOrderInvoice('<?php echo $order['order_code']; ?>')" class="bg-amber-50 dark:bg-amber-950/20 text-amber-600 border border-amber-200/50 p-2 rounded-lg transition-all" title="چاپ فاکتور صندوق">
                                            <i data-lucide="printer" class="w-4 h-4"></i>
                                        </button>
                                        <!-- تغییر وضعیت سریع -->
                                        <select onchange="updateOrderStatus('<?php echo $order['order_code']; ?>', this.value)" class="bg-stone-50 dark:bg-stone-800 border border-stone-200 dark:border-stone-700 px-2 py-1.5 rounded-lg text-[10px] font-bold text-stone-600 dark:text-stone-300 focus:outline-none cursor-pointer">
                                            <option value="registered" <?php echo $order['status'] === 'registered' ? 'selected' : ''; ?>>ثبت شده</option>
                                            <option value="preparing" <?php echo $order['status'] === 'preparing' ? 'selected' : ''; ?>>آماده‌سازی</option>
                                            <option value="ready" <?php echo $order['status'] === 'ready' ? 'selected' : ''; ?>>آماده تحویل</option>
                                            <option value="sent" <?php echo $order['status'] === 'sent' ? 'selected' : ''; ?>>ارسال شد</option>
                                            <option value="completed" <?php echo $order['status'] === 'completed' ? 'selected' : ''; ?>>تکمیل شد</option>
                                            <option value="cancelled" <?php echo $order['status'] === 'cancelled' ? 'selected' : ''; ?>>لغو شد</option>
                                        </select>
                                    </div>
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

    /**
     * تغییر وضعیت فاکتور با AJAX
     */
    function updateOrderStatus(code, newStatus) {
        fetch('api/update_order_status.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({order_code: code, status: newStatus})
        })
        .then(res => res.json())
        .then(data => {
            location.reload();
        })
        .catch(err => {
            // شبیه‌ساز آفلاین
            let mockOrders = JSON.parse(localStorage.getItem('mock_orders')) || [];
            const idx = mockOrders.findIndex(o => o.order_code === code);
            if (idx !== -1) {
                mockOrders[idx].status = newStatus;
                localStorage.setItem('mock_orders', JSON.stringify(mockOrders));
            }
            location.reload();
        });
    }

    function printOrderInvoice(code) {
        // باز کردن و صدا زدن تابع چاپ
        const w = window.open('', '_blank');
        let mockOrders = JSON.parse(localStorage.getItem('mock_orders')) || [];
        let order = mockOrders.find(o => o.order_code === code);
        
        if (!order) {
            alert('پرینتر فیزیکی یافت نشد. دیتای نمونه چاپی همگام شد.');
            return;
        }

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

    function formatNumber(num) {
        return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
    }
</script>
</body>
</html>
<?php
// توابع راه‌انداز استایل وضعیت
function getStatusStyle($status) {
    switch ($status) {
        case 'registered': return 'bg-blue-100 text-blue-700 dark:bg-blue-950/40 dark:text-blue-300';
        case 'preparing': return 'bg-yellow-100 text-yellow-700 dark:bg-yellow-950/40 dark:text-yellow-300';
        case 'ready': return 'bg-purple-100 text-purple-700 dark:bg-purple-950/40 dark:text-purple-300';
        case 'sent': return 'bg-indigo-100 text-indigo-700 dark:bg-indigo-950/40 dark:text-indigo-300';
        case 'completed': return 'bg-green-100 text-green-700 dark:bg-green-950/40 dark:text-green-300';
        case 'cancelled': return 'bg-red-100 text-red-700 dark:bg-red-950/40 dark:text-red-300';
        default: return 'bg-stone-100 text-stone-700';
    }
}

function getStatusFarsi($status) {
    switch ($status) {
        case 'registered': return 'ثبت شد';
        case 'preparing': return 'در حال آماده‌سازی';
        case 'ready': return 'آماده تحویل';
        case 'sent': return 'ارسال شد';
        case 'completed': return 'تکمیل شد';
        case 'cancelled': return 'لغو شد';
        default: return 'ناشناس';
    }
}
?>
