<?php
/**
 * مدیریت جامع محصولات کافه گالری - افزودن، ویرایش، حذف و آپلود تصویر
 */
require_once __DIR__ . '/../config/config.php';

// احراز هویت ادمین
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: ../login.php');
    exit;
}

$message = '';
$message_type = '';

// هندل کردن عملیات حذف محصول
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $delete_id = intval($_GET['id']);
    if ($pdo) {
        try {
            $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
            $stmt->execute([$delete_id]);
            logActivity("حذف محصول شناسه: $delete_id");
            $message = 'محصول با موفقیت حذف گردید.';
            $message_type = 'success';
        } catch (Exception $e) {
            $message = 'خطا در حذف محصول.';
            $message_type = 'error';
        }
    } else {
        $message = '[دمو] حذف محصول با موفقیت شبیه‌سازی شد.';
        $message_type = 'success';
    }
}

// هندل کردن فرم افزودن / ویرایش محصول
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf_token = $_POST['csrf_token'] ?? '';
    if (!verifyCSRFToken($csrf_token)) {
        $message = 'خطای عدم تطابق امنیت نشست کاربری (CSRF)!';
        $message_type = 'error';
    } else {
        $product_id = isset($_POST['product_id']) && !empty($_POST['product_id']) ? intval($_POST['product_id']) : null;
        $category_id = intval($_POST['category_id']);
        $name = trim($_POST['name']);
        $price = floatval($_POST['price']);
        $discount = intval($_POST['discount'] ?? 0);
        $description = trim($_POST['description']);
        $ingredients = trim($_POST['ingredients']);
        $is_available = isset($_POST['is_available']) ? 1 : 0;
        $is_popular = isset($_POST['is_popular']) ? 1 : 0;
        $is_new = isset($_POST['is_new']) ? 1 : 0;
        $is_visible = isset($_POST['is_visible']) ? 1 : 0;
        $sort_order = intval($_POST['sort_order'] ?? 0);

        // آپلود تصویر محصول
        $image_path = $_POST['current_image'] ?? '';
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $file_tmp = $_FILES['image']['tmp_name'];
            $file_name = $_FILES['image']['name'];
            $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
            
            // پسوندهای مجاز
            $allowed_exts = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
            if (in_array($file_ext, $allowed_exts)) {
                $upload_dir = __DIR__ . '/../uploads/';
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0755, true);
                }
                $new_file_name = uniqid('prod_', true) . '.' . $file_ext;
                $dest_path = $upload_dir . $new_file_name;
                
                if (move_uploaded_file($file_tmp, $dest_path)) {
                    $image_path = 'uploads/' . $new_file_name;
                }
            } else {
                $message = 'فرمت تصویر انتخاب شده مجاز نمی‌باشد (مجاز: JPG, PNG, WEBP).';
                $message_type = 'error';
            }
        }

        if (empty($message) && !empty($name)) {
            if ($pdo) {
                try {
                    if ($product_id) {
                        // عملیات ویرایش محصول
                        $stmt = $pdo->prepare("
                            UPDATE products SET 
                            category_id = ?, name = ?, price = ?, discount = ?, description = ?, 
                            ingredients = ?, image = ?, is_available = ?, is_popular = ?, 
                            is_new = ?, is_visible = ?, sort_order = ? 
                            WHERE id = ?
                        ");
                        $stmt->execute([
                            $category_id, $name, $price, $discount, $description, 
                            $ingredients, $image_path, $is_available, $is_popular, 
                            $is_new, $is_visible, $sort_order, $product_id
                        ]);
                        logActivity("ویرایش محصول شناسه: $product_id - نام: $name");
                        $message = 'محصول با موفقیت بروزرسانی شد.';
                        $message_type = 'success';
                    } else {
                        // عملیات افزودن محصول جدید
                        $stmt = $pdo->prepare("
                            INSERT INTO products 
                            (category_id, name, price, discount, description, ingredients, image, is_available, is_popular, is_new, is_visible, sort_order) 
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                        ");
                        $stmt->execute([
                            $category_id, $name, $price, $discount, $description, $ingredients, $image_path, $is_available, $is_popular, $is_new, $is_visible, $sort_order
                        ]);
                        logActivity("افزودن محصول جدید: $name");
                        $message = 'محصول جدید با موفقیت اضافه شد.';
                        $message_type = 'success';
                    }
                } catch (Exception $e) {
                    $message = 'خطای سروری دیتابیس در ثبت اطلاعات محصول: ' . $e->getMessage();
                    $message_type = 'error';
                }
            } else {
                $message = '[دمو] افزودن/ویرایش محصول به صورت کلاینت ساید شبیه‌سازی گردید.';
                $message_type = 'success';
            }
        }
    }
}

// خواندن محصولات و دسته‌بندی‌ها
$products = [];
$categories = [];

if ($pdo) {
    try {
        $categories = $pdo->query("SELECT * FROM categories ORDER BY sort_order ASC")->fetchAll();
        $products = $pdo->query("SELECT p.*, c.name as category_name FROM products p JOIN categories c ON p.category_id = c.id ORDER BY p.category_id, p.sort_order")->fetchAll();
    } catch (Exception $e) {}
} else {
    // مقادیر شبیه‌ساز آفلاین
    $categories = [
        ['id' => 1, 'name' => 'قهوه گرم'],
        ['id' => 2, 'name' => 'قهوه سرد'],
        ['id' => 4, 'name' => 'کیک و دسر']
    ];
    $products = [
        ['id' => 1, 'category_id' => 1, 'category_name' => 'قهوه گرم', 'name' => 'اسپرسو دوبل', 'price' => 45000, 'discount' => 0, 'description' => 'اسپرسو ۱۰۰٪ عربیکا', 'ingredients' => 'قهوه عربیکا', 'image' => '', 'is_available' => 1, 'is_popular' => 1, 'is_new' => 0, 'is_visible' => 1, 'sort_order' => 1],
        ['id' => 2, 'category_id' => 1, 'category_name' => 'قهوه گرم', 'name' => 'کاپوچینو', 'price' => 55000, 'discount' => 10, 'description' => 'اسپرسو با فوم شیر', 'ingredients' => 'اسپرسو، شیر', 'image' => '', 'is_available' => 1, 'is_popular' => 0, 'is_new' => 1, 'is_visible' => 1, 'sort_order' => 2]
    ];
}
?>

<!DOCTYPE html>
<html lang="fa" dir="rtl" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>مدیریت محصولات - پنل ادمین</title>
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
        <a href="products.php" title="مدیریت محصولات" class="relative p-2 sm:px-3 sm:py-2 rounded-xl transition-all cursor-pointer group shrink-0 flex items-center gap-1.5 bg-[#c49b63] text-black shadow-md font-black">
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
        <a href="reports.php" title="گزارشات و PDF" class="relative p-2 sm:px-3 sm:py-2 rounded-xl transition-all cursor-pointer group shrink-0 flex items-center gap-1.5 text-stone-300 hover:bg-white/5 hover:text-white">
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

<!-- بدنه اصلی مدیریت محصولات -->
<main class="flex-1 p-4 md:p-8 space-y-6 max-w-5xl mx-auto w-full overflow-y-auto">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-2xl font-black text-white">مدیریت محصولات منو</h2>
            <p class="text-xs text-stone-400 mt-1">افزودن محصولات جدید، قیمت‌گذاری، تخفیف‌ها و آپلود عکس محصول</p>
        </div>
        <button onclick="openProductFormModal()" class="bg-[#c49b63] hover:bg-[#b28b58] text-black px-5 py-3 rounded-2xl font-black text-sm flex items-center gap-2 shadow-lg transition-all cursor-pointer">
            <i data-lucide="plus" class="w-5 h-5"></i>
            <span>افزودن محصول جدید</span>
        </button>
    </div>

    <!-- اعلان‌ها -->
    <?php if (!empty($message)): ?>
        <div class="<?php echo $message_type === 'success' ? 'bg-emerald-950/50 border-emerald-500/30 text-emerald-300' : 'bg-red-950/50 border-red-500/30 text-red-300'; ?> border px-5 py-3.5 rounded-2xl text-xs font-bold flex items-center gap-2 shadow-xs">
            <i data-lucide="<?php echo $message_type === 'success' ? 'check-circle' : 'alert-octagon'; ?>" class="w-5 h-5 shrink-0"></i>
            <span><?php echo $message; ?></span>
        </div>
    <?php endif; ?>

    <!-- جدول محصولات موجود -->
    <div class="bg-[#131210] rounded-3xl border border-white/10 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-right border-collapse text-xs table-auto md:table-fixed">
                <thead>
                    <tr class="bg-white/5 border-b border-white/10 text-stone-300 font-bold">
                        <th class="p-4 w-12 md:w-16">عکس</th>
                        <th class="p-4">نام محصول</th>
                        <th class="p-4 hidden md:table-cell md:w-28">دسته‌بندی</th>
                        <th class="p-4">قیمت پایه</th>
                        <th class="p-4 hidden sm:table-cell md:w-16 text-center">تخفیف</th>
                        <th class="p-4 md:w-32">قیمت نهایی</th>
                        <th class="p-4 md:w-20 text-center">موجود</th>
                        <th class="p-4 hidden lg:table-cell md:w-24">برچسب‌ها</th>
                        <th class="p-4 hidden xl:table-cell md:w-16 text-center">نمایش</th>
                        <th class="p-4 w-24 md:w-28 text-center">عملیات</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/10 font-semibold text-stone-200">
                    <?php if (empty($products)): ?>
                        <tr>
                            <td colspan="10" class="p-8 text-center text-stone-400">هیچ محصولی در دیتابیس ثبت نشده است.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($products as $prod): 
                            $final_price = $prod['price'] - ($prod['price'] * $prod['discount'] / 100);
                        ?>
                            <tr class="hover:bg-white/5">
                                <td class="p-4">
                                    <?php if (!empty($prod['image'])): ?>
                                        <img src="../<?php echo sanitize($prod['image']); ?>" class="w-8 h-8 md:w-10 md:h-10 rounded-lg object-cover">
                                    <?php else: ?>
                                        <div class="w-8 h-8 md:w-10 md:h-10 bg-white/5 rounded-lg flex items-center justify-center text-stone-400"><i data-lucide="image" class="w-4 h-4"></i></div>
                                    <?php endif; ?>
                                </td>
                                <td class="p-4 font-bold text-white truncate max-w-[120px]" title="<?php echo sanitize($prod['name']); ?>"><?php echo sanitize($prod['name']); ?></td>
                                <td class="p-4 hidden md:table-cell text-stone-300"><?php echo sanitize($prod['category_name']); ?></td>
                                <td class="p-4 text-stone-300"><?php echo number_format($prod['price']); ?></td>
                                <td class="p-4 hidden sm:table-cell text-red-400 text-center">%<?php echo $prod['discount']; ?></td>
                                <td class="p-4 font-black text-emerald-400"><?php echo number_format($final_price); ?> تومان</td>
                                <td class="p-4 text-center">
                                    <span class="px-2 py-0.5 rounded text-[9px] font-bold <?php echo $prod['is_available'] ? 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/30' : 'bg-red-500/20 text-red-300 border border-red-500/30'; ?>">
                                        <?php echo $prod['is_available'] ? 'بله' : 'خیر'; ?>
                                    </span>
                                </td>
                                <td class="p-4 hidden lg:table-cell">
                                    <div class="flex gap-1">
                                        <?php if ($prod['is_popular']): ?><span class="bg-[#c49b63]/20 text-[#c49b63] border border-[#c49b63]/30 text-[9px] px-1.5 py-0.5 rounded font-bold">محبوب</span><?php endif; ?>
                                        <?php if ($prod['is_new']): ?><span class="bg-teal-500/20 text-teal-300 border border-teal-500/30 text-[9px] px-1.5 py-0.5 rounded font-bold">جدید</span><?php endif; ?>
                                    </div>
                                </td>
                                <td class="p-4 hidden xl:table-cell text-center">
                                    <i data-lucide="<?php echo $prod['is_visible'] ? 'eye' : 'eye-off'; ?>" class="w-4 h-4 mx-auto <?php echo $prod['is_visible'] ? 'text-emerald-400' : 'text-stone-500'; ?>"></i>
                                </td>
                                <td class="p-4 text-center">
                                    <div class="flex items-center justify-center gap-1">
                                        <button onclick="editProduct(<?php echo htmlspecialchars(json_encode($prod)); ?>)" class="bg-blue-500/20 hover:bg-blue-500/30 text-blue-300 border border-blue-500/30 p-1.5 rounded-lg transition-all" title="ویرایش">
                                            <i data-lucide="pencil" class="w-4 h-4"></i>
                                        </button>
                                        <a href="products.php?action=delete&id=<?php echo $prod['id']; ?>" onclick="return confirm('آیا از حذف این محصول اطمینان دارید؟')" class="bg-red-500/20 hover:bg-red-500/30 text-red-300 border border-red-500/30 p-1.5 rounded-lg transition-all" title="حذف">
                                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                                        </a>
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

<!-- ==================== مودال فرم محصول (افزودن و ویرایش) ==================== -->
<div id="productFormModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-md hidden transition-all duration-300">
    <div class="bg-[#131210] rounded-3xl w-full max-w-xl overflow-hidden border border-white/10 shadow-2xl relative text-stone-200">
        <button onclick="closeProductFormModal()" class="absolute top-4 left-4 p-2 rounded-full hover:bg-white/10 text-stone-400 hover:text-white transition-colors">
            <i data-lucide="x" class="w-5 h-5"></i>
        </button>

        <div class="p-6 border-b border-white/10">
            <h3 id="modalTitle" class="text-lg font-black text-white">افزودن محصول جدید</h3>
        </div>

        <form action="products.php" method="POST" enctype="multipart/form-data" class="p-6 space-y-4 max-h-[500px] overflow-y-auto">
            <input type="hidden" name="csrf_token" value="<?php echo getCSRFToken(); ?>">
            <input type="hidden" name="product_id" id="form_product_id" value="">
            <input type="hidden" name="current_image" id="form_current_image" value="">

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-stone-300 mb-1.5">نام محصول *</label>
                    <input type="text" name="name" id="form_name" required class="w-full px-3 py-2 rounded-xl bg-white/5 border border-white/10 text-white text-xs focus:outline-none focus:border-[#c49b63]">
                </div>
                <div>
                    <label class="block text-xs font-bold text-stone-300 mb-1.5">دسته‌بندی *</label>
                    <select name="category_id" id="form_category_id" required class="w-full px-3 py-2 rounded-xl bg-[#1a1916] border border-white/10 text-white text-xs focus:outline-none focus:border-[#c49b63] cursor-pointer">
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo $cat['id']; ?>"><?php echo sanitize($cat['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-3 gap-3">
                <div>
                    <label class="block text-xs font-bold text-stone-300 mb-1.5">قیمت (تومان) *</label>
                    <input type="number" name="price" id="form_price" required class="w-full px-3 py-2 rounded-xl bg-white/5 border border-white/10 text-white text-xs focus:outline-none focus:border-[#c49b63] text-center">
                </div>
                <div>
                    <label class="block text-xs font-bold text-stone-300 mb-1.5">درصد تخفیف</label>
                    <input type="number" name="discount" id="form_discount" min="0" max="100" class="w-full px-3 py-2 rounded-xl bg-white/5 border border-white/10 text-white text-xs focus:outline-none focus:border-[#c49b63] text-center">
                </div>
                <div>
                    <label class="block text-xs font-bold text-stone-300 mb-1.5">ترتیب نمایش</label>
                    <input type="number" name="sort_order" id="form_sort_order" class="w-full px-3 py-2 rounded-xl bg-white/5 border border-white/10 text-white text-xs focus:outline-none focus:border-[#c49b63] text-center">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-stone-300 mb-1.5">توضیحات کوتاه</label>
                <textarea name="description" id="form_description" rows="2" class="w-full px-3 py-2 rounded-xl bg-white/5 border border-white/10 text-white text-xs focus:outline-none focus:border-[#c49b63]"></textarea>
            </div>

            <div>
                <label class="block text-xs font-bold text-stone-300 mb-1.5">مواد تشکیل دهنده (با ویرگول جدا کنید)</label>
                <input type="text" name="ingredients" id="form_ingredients" placeholder="مثال: لیمو، نعنا، سیروپ نعنا، آب گازدار" class="w-full px-3 py-2 rounded-xl bg-white/5 border border-white/10 text-white text-xs focus:outline-none focus:border-[#c49b63]">
            </div>

            <!-- آپلود تصویر -->
            <div>
                <label class="block text-xs font-bold text-stone-300 mb-1.5">آپلود تصویر محصول</label>
                <input type="file" name="image" accept="image/*" class="w-full text-xs text-stone-400 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-[#c49b63]/20 file:text-[#c49b63] hover:file:bg-[#c49b63]/30 cursor-pointer">
            </div>

            <!-- چک‌باکس‌های برچسب‌ها و موجودی -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 bg-white/5 p-4 rounded-2xl border border-white/10 text-stone-200">
                <label class="flex items-center gap-2 cursor-pointer font-bold text-xs">
                    <input type="checkbox" name="is_available" id="form_is_available" value="1" checked class="rounded border-white/20 bg-stone-900 text-[#c49b63] focus:ring-[#c49b63]">
                    <span>موجودی فعال</span>
                </label>
                <label class="flex items-center gap-2 cursor-pointer font-bold text-xs">
                    <input type="checkbox" name="is_popular" id="form_is_popular" value="1" class="rounded border-white/20 bg-stone-900 text-[#c49b63] focus:ring-[#c49b63]">
                    <span>محبوب‌ترین</span>
                </label>
                <label class="flex items-center gap-2 cursor-pointer font-bold text-xs">
                    <input type="checkbox" name="is_new" id="form_is_new" value="1" class="rounded border-white/20 bg-stone-900 text-[#c49b63] focus:ring-[#c49b63]">
                    <span>محصول جدید</span>
                </label>
                <label class="flex items-center gap-2 cursor-pointer font-bold text-xs">
                    <input type="checkbox" name="is_visible" id="form_is_visible" value="1" checked class="rounded border-white/20 bg-stone-900 text-[#c49b63] focus:ring-[#c49b63]">
                    <span>نمایش در منو</span>
                </label>
            </div>

            <div class="pt-4">
                <button type="submit" class="w-full bg-[#c49b63] hover:bg-[#b28b58] text-black font-black py-3 rounded-xl text-sm flex items-center justify-center gap-1.5 shadow-lg transition-all cursor-pointer">
                    <i data-lucide="check" class="w-5 h-5"></i>
                    <span>ذخیره تغییرات محصول</span>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
    });

    function openProductFormModal() {
        document.getElementById('productFormModal').classList.remove('hidden');
        document.getElementById('modalTitle').innerText = 'افزودن محصول جدید';
        
        // ریست فرم
        document.getElementById('form_product_id').value = '';
        document.getElementById('form_current_image').value = '';
        document.getElementById('form_name').value = '';
        document.getElementById('form_price').value = '';
        document.getElementById('form_discount').value = '0';
        document.getElementById('form_sort_order').value = '0';
        document.getElementById('form_description').value = '';
        document.getElementById('form_ingredients').value = '';
        document.getElementById('form_is_available').checked = true;
        document.getElementById('form_is_popular').checked = false;
        document.getElementById('form_is_new').checked = false;
        document.getElementById('form_is_visible').checked = true;
    }

    function closeProductFormModal() {
        document.getElementById('productFormModal').classList.add('hidden');
    }

    function editProduct(prod) {
        openProductFormModal();
        document.getElementById('modalTitle').innerText = 'ویرایش اطلاعات محصول';
        
        // پرکردن مقادیر ویرایش
        document.getElementById('form_product_id').value = prod.id;
        document.getElementById('form_current_image').value = prod.image || '';
        document.getElementById('form_name').value = prod.name;
        document.getElementById('form_category_id').value = prod.category_id;
        document.getElementById('form_price').value = prod.price;
        document.getElementById('form_discount').value = prod.discount;
        document.getElementById('form_sort_order').value = prod.sort_order;
        document.getElementById('form_description').value = prod.description || '';
        document.getElementById('form_ingredients').value = prod.ingredients || '';
        
        document.getElementById('form_is_available').checked = prod.is_available == 1;
        document.getElementById('form_is_popular').checked = prod.is_popular == 1;
        document.getElementById('form_is_new').checked = prod.is_new == 1;
        document.getElementById('form_is_visible').checked = prod.is_visible == 1;
    }
</script>
</body>
</html>
