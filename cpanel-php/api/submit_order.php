<?php
/**
 * هاب دریافت و ثبت سفارش کلاینت به صورت کاملاً امن با AJAX
 */
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/config.php';

// فقط متد POST مجاز است
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'درخواست غیر مجاز است.']);
    exit;
}

// بررسی توکن امنیتی CSRF
$csrf_token = $_POST['csrf_token'] ?? '';
if (!verifyCSRFToken($csrf_token)) {
    echo json_encode(['status' => 'error', 'message' => 'خطای عدم تطابق امنیت نشست کاربری (CSRF)!']);
    exit;
}

// دریافت اطلاعات ارسال شده
$order_type = $_POST['order_type'] ?? 'indoor';
$cart_raw = $_POST['cart'] ?? '';

// رمزگشایی سبد خرید
$cart = json_decode($cart_raw, true);
if (empty($cart) || !is_array($cart)) {
    echo json_encode(['status' => 'error', 'message' => 'سبد خرید شما خالی است!']);
    exit;
}

// اعتبارسنجی فیلدها بر اساس نوع سفارش
if ($order_type === 'indoor') {
    // سفارش حضوری در کافه
    $first_name = trim($_POST['first_name'] ?? '');
    $table_number = trim($_POST['table_number'] ?? 'میز عمومی');
    $customer_name = !empty($first_name) ? $first_name : 'مشتری حضوری';
    $customer_phone = '09000000000';
    $address = 'تحویل حضوری - ' . $table_number;
    $plaque = $table_number;
    $floor = '';
    $unit = '';
    $description = trim($_POST['description_indoor'] ?? '');

    if (empty($first_name)) {
        echo json_encode(['status' => 'error', 'message' => 'لطفاً نام سفارش‌دهنده را وارد کنید.']);
        exit;
    }
} else {
    // سفارش غیرحضوری (ارسال با پیک)
    $customer_name = trim($_POST['outdoor_name'] ?? '');
    $customer_phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $plaque = trim($_POST['plaque'] ?? '');
    $floor = trim($_POST['floor'] ?? '');
    $unit = trim($_POST['unit'] ?? '');
    $description = trim($_POST['description_outdoor'] ?? '');

    if (empty($customer_name) || empty($customer_phone) || empty($address)) {
        echo json_encode(['status' => 'error', 'message' => 'لطفاً تمامی فیلدهای الزامی (نام، شماره تماس و آدرس) را وارد نمایید.']);
        exit;
    }

    // بررسی درستی شماره موبایل ایران
    if (!preg_match('/^09[0-9]{9}$/', $customer_phone)) {
        echo json_encode(['status' => 'error', 'message' => 'فرمت شماره موبایل نامعتبر است (مثال: 09123456789)']);
        exit;
    }
}

// بررسی اتصال فعال پایگاه داده
if (!$pdo) {
    echo json_encode([
        'status' => 'error', 
        'message' => 'خطای اتصال به پایگاه داده! اما سفارش به صورت آفلاین ذخیره موقت شد.',
        'offline_friendly' => true
    ]);
    exit;
}

try {
    // شروع تراکنش (Transaction) پایگاه داده برای حفظ یکپارچگی اطلاعات
    $pdo->beginTransaction();

    // ۱. محاسبه مجموع مبلغ سفارش
    $total_amount = 0;
    foreach ($cart as $item) {
        $total_amount += $item['price'] * $item['quantity'];
    }

    // ۲. تولید شماره سفارش یکتا و تاریخ شمسی
    $order_code = generateOrderCode();
    $jalali_date = JalaliDate::now('Y/m/d H:i:s');

    // ۳. درج فاکتور نهایی در جدول سفارشات
    $stmt_order = $pdo->prepare("
        INSERT INTO orders 
        (order_code, customer_name, customer_phone, order_type, address, plaque, floor, unit, description, total_amount, status, created_jalali) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'registered', ?)
    ");
    
    $stmt_order->execute([
        $order_code,
        $customer_name,
        $customer_phone,
        $order_type,
        $address,
        $plaque,
        $floor,
        $unit,
        $description,
        $total_amount,
        $jalali_date
    ]);
    
    $order_id = $pdo->lastInsertId();

    // ۴. ثبت تک‌تک آیتم‌های فاکتور
    $stmt_item = $pdo->prepare("
        INSERT INTO order_items 
        (order_id, product_id, product_name, quantity, price) 
        VALUES (?, ?, ?, ?, ?)
    ");

    foreach ($cart as $item) {
        $stmt_item->execute([
            $order_id,
            $item['id'],
            $item['name'],
            $item['quantity'],
            $item['price']
        ]);
    }

    // تایید و اعمال تراکنش در دیتابیس
    $pdo->commit();

    echo json_encode([
        'status' => 'success',
        'message' => 'سفارش شما با موفقیت ثبت گردید و در صف آماده‌سازی قرار گرفت.',
        'order_code' => $order_code
    ]);

} catch (Exception $e) {
    // در صورت خطا، تراکنش لغو می‌شود
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo json_encode([
        'status' => 'error',
        'message' => 'بروز خطای سروری در پردازش اطلاعات دیتابیس: ' . $e->getMessage()
    ]);
}
