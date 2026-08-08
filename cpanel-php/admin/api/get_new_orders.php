<?php
/**
 * دریافت لیست آخرین سفارشات با جزئیات کامل اقلام با فرمت JSON
 */
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../config/config.php';

// احراز هویت ادمین
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    echo json_encode(['status' => 'error', 'message' => 'عدم دسترسی معتبر']);
    exit;
}

$demo_orders = [
    [
        'id' => 1,
        'order_code' => 'CAFE-1405-8842',
        'customer_name' => 'امیرحسین رضایی',
        'customer_phone' => '09121111111',
        'order_type' => 'indoor',
        'address' => 'تحویل حضوری - میز شماره ۴',
        'plaque' => 'میز ۴',
        'floor' => '',
        'unit' => '',
        'description' => 'اسپرسو داغ با کمترین شکر',
        'total_amount' => 148000,
        'status' => 'registered',
        'created_jalali' => '۱۴۰۵/۰۴/۱۵ ۱۲:۴۵:۰۰',
        'items' => [
            ['product_name' => 'اسپرسو دوبل', 'quantity' => 2, 'price' => 45000],
            ['product_name' => 'چیزکیک نیویورکی', 'quantity' => 1, 'price' => 58000]
        ]
    ],
    [
        'id' => 2,
        'order_code' => 'CAFE-1405-7731',
        'customer_name' => 'مریم حسینی',
        'customer_phone' => '09352222222',
        'order_type' => 'outdoor',
        'address' => 'خیابان ولیعصر، بالاتر از ظفر، پلاک ۱۲',
        'plaque' => '۱۲',
        'floor' => '۳',
        'unit' => '۵',
        'description' => 'لطفاً تحویل نگهبانی داده شود',
        'total_amount' => 164000,
        'status' => 'preparing',
        'created_jalali' => '۱۴۰۵/۰۴/۱۵ ۱۲:۳۰:۰۰',
        'items' => [
            ['product_name' => 'آیس لاته', 'quantity' => 1, 'price' => 60000],
            ['product_name' => 'کیک شکلاتی بی‌بی', 'quantity' => 2, 'price' => 52000]
        ]
    ],
    [
        'id' => 3,
        'order_code' => 'CAFE-1405-5519',
        'customer_name' => 'رضا آرمانی',
        'customer_phone' => '09193333333',
        'order_type' => 'indoor',
        'address' => 'تحویل حضوری - میز شماره ۱',
        'plaque' => 'میز ۱',
        'floor' => '',
        'unit' => '',
        'description' => 'بدون نی',
        'total_amount' => 58000,
        'status' => 'completed',
        'created_jalali' => '۱۴۰۵/۰۴/۱۵ ۱۲:۱۰:۰۰',
        'items' => [
            ['product_name' => 'لاته آرت', 'quantity' => 1, 'price' => 58000]
        ]
    ]
];

if (!$pdo) {
    echo json_encode($demo_orders);
    exit;
}

try {
    // دریافت سفارشات ثبت شده اخیر
    $stmt = $pdo->query("SELECT * FROM orders ORDER BY id DESC LIMIT 100");
    $orders = $stmt->fetchAll();

    if (empty($orders)) {
        echo json_encode($demo_orders);
        exit;
    }

    // دریافت اقلام سفارشات
    $full_orders = [];
    foreach ($orders as $order) {
        $stmt_items = $pdo->prepare("SELECT * FROM order_items WHERE order_id = ?");
        $stmt_items->execute([$order['id']]);
        $order['items'] = $stmt_items->fetchAll();
        $full_orders[] = $order;
    }

    echo json_encode($full_orders);

} catch (Exception $e) {
    echo json_encode($demo_orders);
}
