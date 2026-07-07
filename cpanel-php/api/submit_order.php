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

// اعتبارسنجی فیلدها بر اساس نوع سفارش (مطابق پروپوزال مشتری)
if ($order_type === 'indoor') {
    // حضوری در کافه
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $customer_name = $first_name . ' ' . $last_name;
    $customer_phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $plaque = trim($_POST['plaque'] ?? '');
    $floor = trim($_POST['floor'] ?? '');
    $unit = trim($_POST['unit'] ?? '');
    $description = trim($_POST['description_indoor'] ?? '');

    if (empty($first_name) || empty($last_name) || empty($customer_phone) || empty($address) || empty($plaque) || empty($floor) || empty($unit)) {
        echo json_encode(['status' => 'error', 'message' => 'خواهشمند است تمامی فیلدهای الزامی سفارش حضوری را تکمیل کنید.']);
        exit;
    }
} else {
    // غیرحضوری
    $customer_name = trim($_POST['outdoor_name'] ?? '');
    $customer_phone = trim($_POST['outdoor_phone'] ?? '');
    $description = trim($_POST['description_outdoor'] ?? '');
    $address = '';
    $plaque = '';
    $floor = '';
    $unit = '';

    if (empty($customer_name) || empty($customer_phone)) {
        echo json_encode(['status' => 'error', 'message' => 'لطفاً نام و شماره موبایل خود را وارد نمایید.']);
        exit;
    }
}

// بررسی درستی شماره موبایل ایران
if (!preg_match('/^09[0-9]{9}$/', $customer_phone)) {
    echo json_encode(['status' => 'error', 'message' => 'فرمت شماره موبایل نامعتبر است (مثال: 09123456789)']);
    exit;
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
