<?php
/**
 * خروج امن مدیر و انهدام کامل نشست (Session)
 */
require_once __DIR__ . '/config/config.php';

if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    logActivity("خروج موفقیت‌آمیز از پنل ادمین");
}

// ریست نشست‌ها
$_SESSION = array();

// انهدام کوکی نشست
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// انهدام کامل سشن
session_destroy();

header('Location: index.php');
exit;
