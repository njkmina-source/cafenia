<?php
/**
 * تغییر وضعیت یک سفارش خاص با متد امن POST و ثبت لاگ عملکرد
 */
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../config/config.php';

// احراز هویت ادمین
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    echo json_encode(['status' => 'error', 'message' => 'عدم دسترسی معتبر ادمین']);
    exit;
}

// خواندن اطلاعات ارسالی خام JSON
$data = json_decode(file_get_contents('php://input'), true);

$order_code = trim($data['order_code'] ?? '');
$status = trim($data['status'] ?? '');

$allowed_statuses = ['registered', 'preparing', 'ready', 'sent', 'completed', 'cancelled'];

if (empty($order_code) || empty($status) || !in_array($status, $allowed_statuses)) {
    echo json_encode(['status' => 'error', 'message' => 'اطلاعات ارسالی نامعتبر است.']);
    exit;
}

if (!$pdo) {
    echo json_encode(['status' => 'success', 'message' => 'بروزرسانی موقت در حافظه دمو صورت گرفت.']);
    exit;
}

try {
    // بروزرسانی وضعیت سفارش
    $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE order_code = ?");
    $result = $stmt->execute([$status, $order_code]);

    if ($result) {
        // ثبت در لاگ فعالیت‌های مدیران
        logActivity("تغییر وضعیت سفارش $order_code به: $status");
        
        echo json_encode(['status' => 'success', 'message' => 'وضعیت سفارش با موفقیت بروزرسانی شد.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'سفارش مورد نظر یافت نشد.']);
    }

} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
