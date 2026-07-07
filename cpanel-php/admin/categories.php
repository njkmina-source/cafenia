<?php
/**
 * مدیریت دسته‌بندی‌های منوی دیجیتال کافه
 */
require_once __DIR__ . '/../config/config.php';

// احراز هویت ادمین
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: ../login.php');
    exit;
}

$message = '';
$message_type = '';

// حذف دسته‌بندی
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $delete_id = intval($_GET['id']);
    if ($pdo) {
        try {
            $stmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
            $stmt->execute([$delete_id]);
            logActivity("حذف دسته‌بندی با شناسه: $delete_id");
            $message = 'دسته‌بندی با موفقیت حذف گردید.';
            $message_type = 'success';
        } catch (Exception $e) {
            $message = 'امکان حذف نیست (احتمال وجود محصول فعال در این دسته وجود دارد).';
            $message_type = 'error';
        }
    } else {
        $message = '[دمو] حذف دسته‌بندی با موفقیت انجام شد.';
        $message_type = 'success';
    }
}

// ثبت / ویرایش دسته
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf_token = $_POST['csrf_token'] ?? '';
    if (!verifyCSRFToken($csrf_token)) {
        $message = 'خطای امنیتی CSRF رخ داده است.';
        $message_type = 'error';
    } else {
        $cat_id = isset($_POST['cat_id']) && !empty($_POST['cat_id']) ? intval($_POST['cat_id']) : null;
        $name = trim($_POST['name'] ?? '');
        $icon = trim($_POST['icon'] ?? 'coffee');
        $sort_order = intval($_POST['sort_order'] ?? 0);

        if (!empty($name)) {
            if ($pdo) {
                try {
                    if ($cat_id) {
                        $stmt = $pdo->prepare("UPDATE categories SET name = ?, icon = ?, sort_order = ? WHERE id = ?");
                        $stmt->execute([$name, $icon, $sort_order, $cat_id]);
                        logActivity("ویرایش دسته‌بندی شناسه: $cat_id - نام: $name");
                        $message = 'تغییرات دسته‌بندی ذخیره شد.';
                        $message_type = 'success';
                    } else {
                        $stmt = $pdo->prepare("INSERT INTO categories (name, icon, sort_order) VALUES (?, ?, ?)");
                        $stmt->execute([$name, $icon, $sort_order]);
                        logActivity("ساخت دسته‌بندی جدید: $name");
                        $message = 'دسته‌بندی جدید با موفقیت ایجاد گردید.';
                        $message_type = 'success';
                    }
                } catch (Exception $e) {
                    $message = 'خطا در عملیات دیتابیس.';
                    $message_type = 'error';
                }
            } else {
                $message = '[دمو] تغییرات دسته‌بندی در شبیه‌ساز ذخیره موقت شد.';
                $message_type = 'success';
            }
        }
    }
}

$categories = [];
if ($pdo) {
    try {
        $categories = $pdo->query("SELECT * FROM categories ORDER BY sort_order ASC, id ASC")->fetchAll();
    } catch (Exception $e) {}
} else {
    $categories = [
        ['id' => 1, 'name' => 'قهوه گرم', 'icon' => 'coffee', 'sort_order' => 1],
        ['id' => 2, 'name' => 'قهوه سرد', 'icon' => 'ice-cream', 'sort_order' => 2],
        ['id' => 3, 'name' => 'کیک و دسر', 'icon' => 'cake', 'sort_order' => 3]
    ];
}
?>

<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>مدیریت دسته‌بندی‌ها - پنل مدیریت</title>
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
        <a href="categories.php" class="flex items-center gap-3 px-4 py-3 rounded-2xl bg-amber-600 text-white font-bold text-sm shadow-md transition-all">
            <i data-lucide="layers" class="w-5 h-5"></i>
            <span>مدیریت دسته‌بندی‌ها</span>
        </a>
        <a href="orders.php" class="flex items-center gap-3 px-4 py-3 rounded-2xl text-stone-600 dark:text-stone-300 hover:bg-stone-50 dark:hover:bg-stone-800 font-semibold text-sm transition-all">
            <i data-lucide="shopping-bag" class="w-5 h-5 text-amber-600"></i>
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

<main class="flex-1 p-6 md:p-8 space-y-8 overflow-y-auto">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-2xl font-black text-stone-950 dark:text-white">دسته‌بندی‌های منوی کافه</h2>
            <p class="text-xs text-stone-500 dark:text-stone-400 mt-1">تغییر آیکون‌ها، ایجاد و مرتب‌سازی ترتیب رندر دسته‌ها در صفحه اول</p>
        </div>
        <button onclick="openCategoryModal()" class="bg-amber-600 hover:bg-amber-500 text-white px-5 py-3 rounded-2xl font-bold text-sm flex items-center gap-2 shadow-lg shadow-amber-600/20 transition-all">
            <i data-lucide="plus" class="w-5 h-5"></i>
            <span>ایجاد دسته جدید</span>
        </button>
    </div>

    <!-- اعلان‌ها -->
    <?php if (!empty($message)): ?>
        <div class="<?php echo $message_type === 'success' ? 'bg-emerald-50 border-emerald-200 text-emerald-800' : 'bg-red-50 border-red-200 text-red-800'; ?> border px-5 py-3.5 rounded-2xl text-xs font-bold flex items-center gap-2 shadow-xs">
            <i data-lucide="<?php echo $message_type === 'success' ? 'check-circle' : 'alert-octagon'; ?>" class="w-5 h-5 shrink-0"></i>
            <span><?php echo $message; ?></span>
        </div>
    <?php endif; ?>

    <div class="bg-white dark:bg-stone-900 rounded-3xl border border-stone-200 dark:border-stone-800 shadow-sm overflow-hidden max-w-3xl">
        <table class="w-full text-right border-collapse text-xs">
            <thead>
                <tr class="bg-stone-50 dark:bg-stone-800/50 border-b border-stone-200 dark:border-stone-800 text-stone-500 font-bold">
                    <th class="p-4 w-16">آیکون</th>
                    <th class="p-4">نام دسته‌بندی</th>
                    <th class="p-4 w-28">ترتیب نمایش</th>
                    <th class="p-4 w-32">عملیات ادمین</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-stone-100 dark:divide-stone-800 font-semibold text-stone-700 dark:text-stone-300">
                <?php foreach ($categories as $cat): ?>
                    <tr class="hover:bg-stone-50/50 dark:hover:bg-stone-800/10">
                        <td class="p-4">
                            <div class="w-9 h-9 bg-amber-50 dark:bg-amber-950/20 text-amber-600 rounded-lg flex items-center justify-center">
                                <i data-lucide="<?php echo sanitize($cat['icon'] ?: 'coffee'); ?>" class="w-5 h-5"></i>
                            </div>
                        </td>
                        <td class="p-4 font-bold text-stone-900 dark:text-white"><?php echo sanitize($cat['name']); ?></td>
                        <td class="p-4"><?php echo $cat['sort_order']; ?></td>
                        <td class="p-4">
                            <div class="flex items-center gap-1">
                                <button onclick="editCategory(<?php echo htmlspecialchars(json_encode($cat)); ?>)" class="bg-blue-50 hover:bg-blue-100 dark:bg-blue-950/30 text-blue-600 p-2 rounded-lg transition-all" title="ویرایش">
                                    <i data-lucide="pencil" class="w-4 h-4"></i>
                                </button>
                                <a href="categories.php?action=delete&id=<?php echo $cat['id']; ?>" onclick="return confirm('آیا از حذف این دسته مطمئن هستید؟')" class="bg-red-50 hover:bg-red-100 dark:bg-red-950/30 text-red-600 p-2 rounded-lg transition-all" title="حذف">
                                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</main>

<!-- ==================== مودال فرم دسته‌بندی ==================== -->
<div id="categoryModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-stone-950/70 backdrop-blur-xs hidden transition-all duration-300">
    <div class="bg-white dark:bg-stone-900 rounded-3xl w-full max-w-md overflow-hidden border border-stone-200 dark:border-stone-800 shadow-2xl relative">
        <button onclick="closeCategoryModal()" class="absolute top-4 left-4 p-2 rounded-full hover:bg-stone-100 dark:hover:bg-stone-800 text-stone-500">
            <i data-lucide="x" class="w-5 h-5"></i>
        </button>

        <div class="p-6 border-b border-stone-100 dark:border-stone-800">
            <h3 id="modalTitle" class="text-lg font-black text-stone-900 dark:text-white">ایجاد دسته‌بندی جدید</h3>
        </div>

        <form action="categories.php" method="POST" class="p-6 space-y-4">
            <input type="hidden" name="csrf_token" value="<?php echo getCSRFToken(); ?>">
            <input type="hidden" name="cat_id" id="form_cat_id" value="">

            <div>
                <label class="block text-xs font-bold text-stone-500 mb-1.5">نام دسته‌بندی *</label>
                <input type="text" name="name" id="form_name" required class="w-full px-3 py-2 rounded-xl bg-stone-50 dark:bg-stone-800 border border-stone-200 dark:border-stone-700 text-xs focus:outline-none focus:border-amber-600">
            </div>

            <div>
                <label class="block text-xs font-bold text-stone-500 mb-1.5">کلاس یا نام آیکون لوساید (Lucide Icon)</label>
                <select name="icon" id="form_icon" class="w-full px-3 py-2 rounded-xl bg-stone-50 dark:bg-stone-800 border border-stone-200 dark:border-stone-700 text-xs focus:outline-none focus:border-amber-600 cursor-pointer">
                    <option value="coffee">Farsi Coffee (قهوه)</option>
                    <option value="ice-cream">Ice Cream (بستنی و قهوه سرد)</option>
                    <option value="leaf">Leaf (دمنوش و چای)</option>
                    <option value="cake">Cake (کیک و دسر)</option>
                    <option value="glass-water">Glass Water (نوشیدنی خنک)</option>
                    <option value="egg">Egg (صبحانه و غذا)</option>
                    <option value="soup">Soup (سوپ و پیش‌غذا)</option>
                    <option value="cookie">Cookie (کوکی و بیسکویت)</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold text-stone-500 mb-1.5">ترتیب مرتب‌سازی (Sort Order)</label>
                <input type="number" name="sort_order" id="form_sort_order" class="w-full px-3 py-2 rounded-xl bg-stone-50 dark:bg-stone-800 border border-stone-200 dark:border-stone-700 text-xs focus:outline-none focus:border-amber-600 text-center">
            </div>

            <div class="pt-4">
                <button type="submit" class="w-full bg-amber-600 hover:bg-amber-500 text-white py-3 rounded-xl font-bold text-sm flex items-center justify-center gap-1.5 shadow-lg transition-all">
                    <i data-lucide="check" class="w-5 h-5"></i>
                    <span>ذخیره تغییرات دسته</span>
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

    function openCategoryModal() {
        document.getElementById('categoryModal').classList.remove('hidden');
        document.getElementById('modalTitle').innerText = 'ایجاد دسته‌بندی جدید';
        document.getElementById('form_cat_id').value = '';
        document.getElementById('form_name').value = '';
        document.getElementById('form_icon').value = 'coffee';
        document.getElementById('form_sort_order').value = '0';
    }

    function closeCategoryModal() {
        document.getElementById('categoryModal').classList.add('hidden');
    }

    function editCategory(cat) {
        openCategoryModal();
        document.getElementById('modalTitle').innerText = 'ویرایش دسته‌بندی';
        document.getElementById('form_cat_id').value = cat.id;
        document.getElementById('form_name').value = cat.name;
        document.getElementById('form_icon').value = cat.icon || 'coffee';
        document.getElementById('form_sort_order').value = cat.sort_order || '0';
    }
</script>
</body>
</html>
