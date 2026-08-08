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
<html lang="fa" dir="rtl" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>مدیریت دسته‌بندی‌ها - پنل مدیریت</title>
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
        <a href="products.php" title="مدیریت محصولات" class="relative p-2 sm:px-3 sm:py-2 rounded-xl transition-all cursor-pointer group shrink-0 flex items-center gap-1.5 text-stone-300 hover:bg-white/5 hover:text-white">
            <i data-lucide="package" class="w-4 h-4 shrink-0"></i>
            <span class="text-[11px] hidden md:inline whitespace-nowrap font-bold">مدیریت محصولات</span>
        </a>
        <a href="categories.php" title="مدیریت دسته‌ها" class="relative p-2 sm:px-3 sm:py-2 rounded-xl transition-all cursor-pointer group shrink-0 flex items-center gap-1.5 bg-[#c49b63] text-black shadow-md font-black">
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

<main class="flex-1 p-4 md:p-8 space-y-6 max-w-5xl mx-auto w-full overflow-y-auto">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-2xl font-black text-white">دسته‌بندی‌های منوی کافه</h2>
            <p class="text-xs text-stone-400 mt-1">تغییر آیکون‌ها، ایجاد و مرتب‌سازی ترتیب رندر دسته‌ها در صفحه اول</p>
        </div>
        <button onclick="openCategoryModal()" class="bg-[#c49b63] hover:bg-[#b28b58] text-black px-5 py-3 rounded-2xl font-black text-sm flex items-center gap-2 shadow-lg transition-all cursor-pointer">
            <i data-lucide="plus" class="w-5 h-5"></i>
            <span>ایجاد دسته جدید</span>
        </button>
    </div>

    <!-- اعلان‌ها -->
    <?php if (!empty($message)): ?>
        <div class="<?php echo $message_type === 'success' ? 'bg-emerald-950/50 border-emerald-500/30 text-emerald-300' : 'bg-red-950/50 border-red-500/30 text-red-300'; ?> border px-5 py-3.5 rounded-2xl text-xs font-bold flex items-center gap-2 shadow-xs">
            <i data-lucide="<?php echo $message_type === 'success' ? 'check-circle' : 'alert-octagon'; ?>" class="w-5 h-5 shrink-0"></i>
            <span><?php echo $message; ?></span>
        </div>
    <?php endif; ?>

    <div class="bg-[#131210] rounded-3xl border border-white/10 shadow-sm overflow-hidden max-w-3xl">
        <table class="w-full text-right border-collapse text-xs">
            <thead>
                <tr class="bg-white/5 border-b border-white/10 text-stone-300 font-bold">
                    <th class="p-4 w-16">آیکون</th>
                    <th class="p-4">نام دسته‌بندی</th>
                    <th class="p-4 w-28">ترتیب نمایش</th>
                    <th class="p-4 w-32">عملیات ادمین</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-white/10 font-semibold text-stone-200">
                <?php foreach ($categories as $cat): ?>
                    <tr class="hover:bg-white/5">
                        <td class="p-4">
                            <div class="w-9 h-9 bg-[#c49b63]/20 border border-[#c49b63]/30 text-[#c49b63] rounded-lg flex items-center justify-center">
                                <i data-lucide="<?php echo sanitize($cat['icon'] ?: 'coffee'); ?>" class="w-5 h-5"></i>
                            </div>
                        </td>
                        <td class="p-4 font-bold text-white"><?php echo sanitize($cat['name']); ?></td>
                        <td class="p-4 text-stone-300"><?php echo $cat['sort_order']; ?></td>
                        <td class="p-4">
                            <div class="flex items-center gap-1">
                                <button onclick="editCategory(<?php echo htmlspecialchars(json_encode($cat)); ?>)" class="bg-blue-500/20 hover:bg-blue-500/30 text-blue-300 border border-blue-500/30 p-2 rounded-lg transition-all" title="ویرایش">
                                    <i data-lucide="pencil" class="w-4 h-4"></i>
                                </button>
                                <a href="categories.php?action=delete&id=<?php echo $cat['id']; ?>" onclick="return confirm('آیا از حذف این دسته مطمئن هستید؟')" class="bg-red-500/20 hover:bg-red-500/30 text-red-300 border border-red-500/30 p-2 rounded-lg transition-all" title="حذف">
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
<div id="categoryModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-md hidden transition-all duration-300">
    <div class="bg-[#131210] rounded-3xl w-full max-w-md overflow-hidden border border-white/10 shadow-2xl relative text-stone-200">
        <button onclick="closeCategoryModal()" class="absolute top-4 left-4 p-2 rounded-full hover:bg-white/10 text-stone-400 hover:text-white transition-colors">
            <i data-lucide="x" class="w-5 h-5"></i>
        </button>

        <div class="p-6 border-b border-white/10">
            <h3 id="modalTitle" class="text-lg font-black text-white">ایجاد دسته‌بندی جدید</h3>
        </div>

        <form action="categories.php" method="POST" class="p-6 space-y-4">
            <input type="hidden" name="csrf_token" value="<?php echo getCSRFToken(); ?>">
            <input type="hidden" name="cat_id" id="form_cat_id" value="">

            <div>
                <label class="block text-xs font-bold text-stone-300 mb-1.5">نام دسته‌بندی *</label>
                <input type="text" name="name" id="form_name" required class="w-full px-3 py-2 rounded-xl bg-white/5 border border-white/10 text-white text-xs focus:outline-none focus:border-[#c49b63]">
            </div>

            <div>
                <label class="block text-xs font-bold text-stone-300 mb-1.5">کلاس یا نام آیکون لوساید (Lucide Icon)</label>
                <select name="icon" id="form_icon" class="w-full px-3 py-2 rounded-xl bg-[#1a1916] border border-white/10 text-white text-xs focus:outline-none focus:border-[#c49b63] cursor-pointer">
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
                <label class="block text-xs font-bold text-stone-300 mb-1.5">ترتیب مرتب‌سازی (Sort Order)</label>
                <input type="number" name="sort_order" id="form_sort_order" class="w-full px-3 py-2 rounded-xl bg-white/5 border border-white/10 text-white text-xs focus:outline-none focus:border-[#c49b63] text-center">
            </div>

            <div class="pt-4">
                <button type="submit" class="w-full bg-[#c49b63] hover:bg-[#b28b58] text-black font-black py-3 rounded-xl text-sm flex items-center justify-center gap-1.5 shadow-lg transition-all cursor-pointer">
                    <i data-lucide="check" class="w-5 h-5"></i>
                    <span>ذخیره دسته‌بندی</span>
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
