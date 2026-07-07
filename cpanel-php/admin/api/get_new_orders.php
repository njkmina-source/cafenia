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

if (!$pdo) {
    echo json_encode([]);
    exit;
}

try {
    // دریافت سفارشات ثبت شده اخیر (مثلاً ۱۰۰ سفارش آخر)
    $stmt = $pdo->query("SELECT * FROM orders ORDER BY id DESC LIMIT 100");
    $orders = $stmt->fetchAll();

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
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
