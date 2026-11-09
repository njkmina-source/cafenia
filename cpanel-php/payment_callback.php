<?php
/**
 * هندلر بازگشت از درگاه پرداخت اینترنتی شاپرک (آقای پرداخت)
 * پس از انجام عملیات پرداخت یا انصراف توسط مشتری
 */
require_once __DIR__ . '/config/config.php';

$transid = trim($_REQUEST['transid'] ?? '');
$status = trim((string)($_REQUEST['status'] ?? ''));
$tracking_number = trim($_REQUEST['tracking_number'] ?? '');
$cardnumber = trim($_REQUEST['cardnumber'] ?? '');
$order_code = trim($_REQUEST['order_code'] ?? '');

$settings = getSettings();
$pin = trim($settings['gateway_aqayepardakht_pin'] ?? '');

// اگر پین درگاه یا شناسه تراکنش ناموجود باشد
if (empty($transid)) {
    header("Location: index.php?payment_error=" . urlencode("شناسه تراکنش بانکی یافت نشد."));
    exit;
}

if (!$pdo) {
    header("Location: index.php?payment_error=" . urlencode("خطای ارتباط با پایگاه داده"));
    exit;
}

try {
    // یافتن سفارش با order_code یا transid
    if (!empty($order_code)) {
        $stmt = $pdo->prepare("SELECT * FROM orders WHERE order_code = ? LIMIT 1");
        $stmt->execute([$order_code]);
    } else {
        $stmt = $pdo->prepare("SELECT * FROM orders WHERE payment_transid = ? LIMIT 1");
        $stmt->execute([$transid]);
    }
    
    $order = $stmt->fetch();

    if (!$order) {
        header("Location: index.php?payment_error=" . urlencode("سفارش مرتبط با این تراکنش یافت نشد."));
        exit;
    }

    // اگر کاربر در درگاه بانک انصراف زده یا پرداخت ناموفق بوده است (status != 1)
    if ($status !== '1') {
        $stmt_failed = $pdo->prepare("UPDATE orders SET payment_status = 'failed' WHERE id = ?");
        $stmt_failed->execute([$order['id']]);
        
        header("Location: index.php?payment_cancelled=1&order_code=" . urlencode($order['order_code']));
        exit;
    }

    // ارسال درخواست تاییدیه (Verify) به وب‌سرویس آقای پرداخت
    $verify_res = aqayepardakht_verify_payment($pin, (int)$order['total_amount'], $transid);

    // بررسی کد نتیجه تایید (1: موفق و تایید شده، 2: قبلاً تایید شده)
    $verify_code = $verify_res['code'] ?? 0;

    if ($verify_code == 1 || $verify_code == 2) {
        // پرداخت با موفقیت تایید گردید
        $actual_tracking = !empty($tracking_number) ? $tracking_number : ($verify_res['tracking_number'] ?? $transid);
        $actual_card = !empty($cardnumber) ? $cardnumber : ($verify_res['cardnumber'] ?? '');

        $stmt_success = $pdo->prepare("
            UPDATE orders SET 
            status = 'registered', 
            payment_status = 'paid', 
            payment_tracking_number = ?, 
            payment_card_number = ? 
            WHERE id = ?
        ");
        $stmt_success->execute([$actual_tracking, $actual_card, $order['id']]);

        // ارسال سفارش به وب‌هوک API خارجی (در صورت فعال بودن)
        // دریافت اقلام سفارش
        $stmt_items = $pdo->prepare("SELECT product_name, quantity, price FROM order_items WHERE order_id = ?");
        $stmt_items->execute([$order['id']]);
        $items = $stmt_items->fetchAll();

        $webhook_payload = [
            'order_code' => $order['order_code'],
            'customer_name' => $order['customer_name'],
            'customer_phone' => $order['customer_phone'],
            'order_type' => $order['order_type'],
            'address' => $order['address'],
            'plaque' => $order['plaque'],
            'floor' => $order['floor'],
            'unit' => $order['unit'],
            'description' => $order['description'],
            'total_amount' => (float)$order['total_amount'],
            'status' => 'registered',
            'payment_status' => 'paid',
            'payment_tracking_number' => $actual_tracking,
            'created_jalali' => $order['created_jalali'],
            'items' => $items
        ];
        @dispatchOrderWebhook($webhook_payload);

        // هدایت کاربر به صفحه اصلی همراه با اطلاعات موفقیت پرداخت
        header("Location: index.php?payment_success=1&order_code=" . urlencode($order['order_code']) . "&tracking=" . urlencode($actual_tracking) . "&amount=" . urlencode((string)$order['total_amount']));
        exit;

    } else {
        // تاییدیه پرداخت ناموفق بود
        $stmt_failed = $pdo->prepare("UPDATE orders SET payment_status = 'failed' WHERE id = ?");
        $stmt_failed->execute([$order['id']]);

        $err_text = $verify_res['message'] ?? 'تراکنش توسط درگاه بانکی تایید نشد.';
        header("Location: index.php?payment_error=" . urlencode($err_text) . "&order_code=" . urlencode($order['order_code']));
        exit;
    }

} catch (Exception $e) {
    header("Location: index.php?payment_error=" . urlencode("خطا در پردازش تاییدیه پرداخت: " . $e->getMessage()));
    exit;
}
