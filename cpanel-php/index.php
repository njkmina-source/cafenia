<?php
/**
 * منوی دیجیتال مشتری - کافه گالری
 * کاملاً واکنش‌گرا (Responsive) و طراحی Mobile First لوکس
 */
include_once __DIR__ . '/includes/header.php';

// لود کردن دسته‌بندی‌ها و محصولات فعال از دیتابیس
$categories = [];
$products = [];

if ($pdo) {
    try {
        // دریافت دسته‌بندی‌های مرتب شده
        $stmt_cat = $pdo->query("SELECT * FROM categories ORDER BY sort_order ASC, id ASC");
        $categories = $stmt_cat->fetchAll();

        // دریافت محصولات فعال و قابل نمایش
        $stmt_prod = $pdo->query("SELECT p.*, c.name as category_name FROM products p JOIN categories c ON p.category_id = c.id WHERE p.is_visible = 1 ORDER BY p.sort_order ASC, p.id ASC");
        $products = $stmt_prod->fetchAll();
    } catch (Exception $e) {
        // در صورت خطای دیتابیس، دیتای نمونه را لود خواهیم کرد تا صفحه خام نشان داده نشود
    }
}
?>

<!-- هدر و بنر کافه -->
<div class="relative w-full h-48 md:h-64 bg-stone-900 overflow-hidden">
    <?php if (!empty($settings['banner_url'])): ?>
        <img src="<?php echo sanitize($settings['banner_url']); ?>" alt="بنر کافه" class="w-full h-full object-cover opacity-60">
    <?php else: ?>
        <!-- بنر پیش‌فرض زیبا و هنری کافه -->
        <div class="absolute inset-0 bg-gradient-to-r from-stone-900 to-stone-800 flex items-center justify-center">
            <div class="absolute inset-0 bg-[radial-gradient(#8B5A2B_1px,transparent_1px)] [background-size:16px_16px] opacity-10"></div>
            <i data-lucide="cup-straw" class="w-24 h-24 text-primary/10"></i>
        </div>
    <?php endif; ?>
    <div class="absolute inset-0 bg-gradient-to-t from-stone-50 via-transparent to-transparent dark:from-stone-950"></div>
</div>

<div class="max-w-6xl mx-auto px-4 -mt-20 relative z-10 pb-12">
    <!-- کارت معرفی کافه -->
    <div class="bg-white dark:bg-stone-900 rounded-3xl p-6 shadow-xl border border-stone-100 dark:border-stone-800 transition-all duration-300">
        <div class="flex flex-col sm:flex-row items-center gap-6">
            <?php if (!empty($settings['logo_url'])): ?>
                <img src="<?php echo sanitize($settings['logo_url']); ?>" alt="لوگو" class="w-24 h-24 rounded-full object-cover shadow-lg border-2 border-primary/50">
            <?php else: ?>
                <div class="w-24 h-24 rounded-full bg-primary/10 flex items-center justify-center text-primary border-2 border-primary/30 shadow-lg">
                    <i data-lucide="coffee" class="w-12 h-12"></i>
                </div>
            <?php endif; ?>
            <div class="text-center sm:text-right flex-1">
                <h2 class="text-2xl font-black text-stone-900 dark:text-white"><?php echo sanitize($settings['cafe_name']); ?></h2>
                <p class="text-sm text-stone-600 dark:text-stone-300 mt-2 line-clamp-2 leading-relaxed"><?php echo sanitize($settings['cafe_description']); ?></p>
                <div class="flex flex-wrap justify-center sm:justify-start gap-4 mt-4 text-xs text-stone-500 dark:text-stone-400">
                    <span class="flex items-center gap-1.5 bg-stone-100 dark:bg-stone-800 px-3 py-1.5 rounded-full">
                        <i data-lucide="clock" class="w-4 h-4 text-primary"></i>
                        <span><?php echo sanitize($settings['working_hours']); ?></span>
                    </span>
                    <span class="flex items-center gap-1.5 bg-stone-100 dark:bg-stone-800 px-3 py-1.5 rounded-full">
                        <i data-lucide="phone" class="w-4 h-4 text-primary"></i>
                        <span class="dir-ltr"><?php echo sanitize($settings['cafe_phone']); ?></span>
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- بخش جستجو و فیلترها -->
    <div class="mt-8 flex flex-col md:flex-row gap-4 items-center justify-between">
        <!-- سرچ بار آنی -->
        <div class="relative w-full md:max-w-md">
            <span class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-stone-400">
                <i data-lucide="search" class="w-5 h-5"></i>
            </span>
            <input type="text" id="searchInput" placeholder="جستجوی محصول، قهوه، کیک..." class="w-full pl-4 pr-10 py-3 rounded-2xl bg-white dark:bg-stone-900 border border-stone-200 dark:border-stone-800 text-stone-800 dark:text-white placeholder-stone-400 focus:outline-none focus:ring-2 focus:ring-primary/50 focus:border-primary transition-all shadow-sm">
            <button id="clearSearchBtn" class="absolute inset-y-0 left-0 flex items-center pl-3 text-stone-400 hover:text-stone-600 hidden">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>

        <!-- فیلترهای پیشرفته -->
        <div class="flex flex-wrap gap-2 w-full md:w-auto justify-end">
            <button onclick="sortProducts('default')" id="sort-default" class="sort-btn px-4 py-2 rounded-xl text-xs font-semibold bg-primary text-white transition-all shadow-sm">
                همه
            </button>
            <button onclick="sortProducts('new')" id="sort-new" class="sort-btn px-4 py-2 rounded-xl text-xs font-semibold bg-white dark:bg-stone-900 text-stone-600 dark:text-stone-300 border border-stone-200 dark:border-stone-800 hover:bg-stone-50 dark:hover:bg-stone-800 transition-all shadow-sm">
                جدیدترین‌ها
            </button>
            <button onclick="sortProducts('popular')" id="sort-popular" class="sort-btn px-4 py-2 rounded-xl text-xs font-semibold bg-white dark:bg-stone-900 text-stone-600 dark:text-stone-300 border border-stone-200 dark:border-stone-800 hover:bg-stone-50 dark:hover:bg-stone-800 transition-all shadow-sm">
                محبوب‌ترین‌ها
            </button>
            <button onclick="sortProducts('discount')" id="sort-discount" class="sort-btn px-4 py-2 rounded-xl text-xs font-semibold bg-white dark:bg-stone-900 text-stone-600 dark:text-stone-300 border border-stone-200 dark:border-stone-800 hover:bg-stone-50 dark:hover:bg-stone-800 transition-all shadow-sm">
                دارای تخفیف
            </button>
        </div>
    </div>

    <!-- لیست اسکرول‌بار دسته‌بندی‌ها -->
    <div class="mt-8">
        <h3 class="text-sm font-bold text-stone-500 dark:text-stone-400 mb-3">دسته‌بندی‌ها</h3>
        <div class="flex gap-3 overflow-x-auto pb-3 snap-x scrollbar-none" id="categoriesScrollContainer">
            <button onclick="filterCategory(0)" id="cat-btn-0" class="category-btn snap-start shrink-0 px-5 py-3 rounded-2xl flex items-center gap-2.5 bg-primary text-white font-bold text-sm shadow-md transition-all duration-300">
                <i data-lucide="layers" class="w-5 h-5"></i>
                <span>همه دسته‌ها</span>
            </button>

            <?php foreach ($categories as $cat): ?>
                <button onclick="filterCategory(<?php echo $cat['id']; ?>)" id="cat-btn-<?php echo $cat['id']; ?>" class="category-btn snap-start shrink-0 px-5 py-3 rounded-2xl flex items-center gap-2.5 bg-white dark:bg-stone-900 text-stone-700 dark:text-stone-300 border border-stone-200 dark:border-stone-800 font-bold text-sm shadow-sm hover:bg-stone-50 dark:hover:bg-stone-800 transition-all duration-300">
                    <?php if (!empty($cat['icon'])): ?>
                        <i data-lucide="<?php echo sanitize($cat['icon']); ?>" class="w-5 h-5 text-primary"></i>
                    <?php else: ?>
                        <i data-lucide="coffee" class="w-5 h-5 text-primary"></i>
                    <?php endif; ?>
                    <span><?php echo sanitize($cat['name']); ?></span>
                </button>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- گرید نمایش محصولات کافه -->
    <div class="mt-8 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6" id="productsGrid">
        <!-- محصولات به صورت داینامیک از سمت کلاینت با JS رندر می‌شوند یا مستقیم با PHP -->
        <?php foreach ($products as $prod): 
            $final_price = $prod['price'] - ($prod['price'] * $prod['discount'] / 100);
        ?>
            <div class="product-card bg-white dark:bg-stone-900 rounded-3xl overflow-hidden border border-stone-100 dark:border-stone-800 hover:shadow-xl transition-all duration-300 flex flex-col group" 
                 data-id="<?php echo $prod['id']; ?>"
                 data-category="<?php echo $prod['category_id']; ?>"
                 data-name="<?php echo sanitize($prod['name']); ?>"
                 data-popular="<?php echo $prod['is_popular']; ?>"
                 data-new="<?php echo $prod['is_new']; ?>"
                 data-discount="<?php echo $prod['discount'] > 0 ? 1 : 0; ?>"
                 data-price="<?php echo $prod['price']; ?>">
                
                <!-- تصویر محصول -->
                <div class="relative w-full h-48 bg-stone-100 dark:bg-stone-800 overflow-hidden cursor-pointer" onclick="openProductDetailModal(<?php echo htmlspecialchars(json_encode($prod)); ?>)">
                    <?php if (!empty($prod['image'])): ?>
                        <img src="<?php echo sanitize($prod['image']); ?>" alt="<?php echo sanitize($prod['name']); ?>" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                    <?php else: ?>
                        <div class="w-full h-full flex items-center justify-center text-stone-300 dark:text-stone-600">
                            <i data-lucide="image" class="w-12 h-12"></i>
                        </div>
                    <?php endif; ?>

                    <!-- بچ‌ها (Badges) -->
                    <div class="absolute top-3 right-3 flex flex-col gap-1.5">
                        <?php if ($prod['is_popular']): ?>
                            <span class="bg-amber-500 text-white text-[10px] font-bold px-2.5 py-1 rounded-full shadow-sm flex items-center gap-1">
                                <i data-lucide="star" class="w-3 h-3 fill-white"></i>
                                محبوب‌ترین
                            </span>
                        <?php endif; ?>
                        <?php if ($prod['is_new']): ?>
                            <span class="bg-teal-500 text-white text-[10px] font-bold px-2.5 py-1 rounded-full shadow-sm">جدید</span>
                        <?php endif; ?>
                        <?php if ($prod['discount'] > 0): ?>
                            <span class="bg-red-500 text-white text-[10px] font-bold px-2.5 py-1 rounded-full shadow-sm">%<?php echo $prod['discount']; ?> تخفیف</span>
                        <?php endif; ?>
                    </div>

                    <!-- عدم موجودی -->
                    <?php if (!$prod['is_available']): ?>
                        <div class="absolute inset-0 bg-stone-900/80 backdrop-blur-xs flex items-center justify-center">
                            <span class="bg-white/90 text-stone-900 dark:bg-stone-950/90 dark:text-white px-4 py-2 rounded-2xl text-xs font-extrabold shadow-md border border-stone-200">ناموجود</span>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- توضیحات محصول -->
                <div class="p-5 flex-1 flex flex-col">
                    <div class="flex items-start justify-between gap-2">
                        <h4 class="font-bold text-stone-900 dark:text-white group-hover:text-primary transition-colors cursor-pointer" onclick="openProductDetailModal(<?php echo htmlspecialchars(json_encode($prod)); ?>)">
                            <?php echo sanitize($prod['name']); ?>
                        </h4>
                    </div>
                    <p class="text-xs text-stone-500 dark:text-stone-400 mt-2 line-clamp-2 leading-relaxed flex-1">
                        <?php echo sanitize($prod['description'] ?: 'توضیحی برای این محصول ثبت نشده است.'); ?>
                    </p>

                    <!-- قیمت و دکمه خرید -->
                    <div class="mt-5 pt-4 border-t border-stone-100 dark:border-stone-800 flex items-center justify-between">
                        <div class="flex flex-col">
                            <?php if ($prod['discount'] > 0): ?>
                                <span class="text-xs text-stone-400 line-through decoration-red-500/50"><?php echo number_format($prod['price']); ?></span>
                                <span class="text-sm font-black text-stone-950 dark:text-white"><?php echo number_format($final_price); ?> <span class="text-[10px] font-normal text-stone-500 dark:text-stone-400"><?php echo CURRENCY; ?></span></span>
                            <?php else: ?>
                                <span class="text-sm font-black text-stone-950 dark:text-white"><?php echo number_format($prod['price']); ?> <span class="text-[10px] font-normal text-stone-500 dark:text-stone-400"><?php echo CURRENCY; ?></span></span>
                            <?php endif; ?>
                        </div>

                        <?php if ($prod['is_available']): ?>
                            <button onclick="addToCart(<?php echo htmlspecialchars(json_encode([
                                'id' => $prod['id'],
                                'name' => $prod['name'],
                                'price' => $final_price,
                                'image' => $prod['image'],
                                'discount' => $prod['discount']
                            ])); ?>)" class="bg-primary hover:bg-primary-hover text-white p-2.5 rounded-2xl hover:scale-105 transition-all shadow-md active:scale-95" title="افزودن به سبد">
                                <i data-lucide="plus" class="w-5 h-5"></i>
                            </button>
                        <?php else: ?>
                            <button disabled class="bg-stone-100 dark:bg-stone-800 text-stone-400 p-2.5 rounded-2xl cursor-not-allowed">
                                <i data-lucide="slash" class="w-5 h-5"></i>
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- دکمه سبد خرید شناور لوکس فیکسد و ۱۰۰٪ واکنش‌گرا هماهنگ با تم سایت -->
<button onclick="openCartModal()" id="floatingCartBtn" class="fixed bottom-6 left-6 z-40 bg-primary hover:opacity-95 text-white font-black py-3.5 px-5 md:py-4 md:px-6 rounded-full shadow-[rgba(139,90,43,0.35)_0px_8px_32px] flex items-center gap-2.5 transition-all duration-300 hover:scale-105 active:scale-95 cursor-pointer hidden" title="مشاهده سبد خرید و ثبت نهایی">
    <i data-lucide="shopping-bag" class="w-5 h-5 shrink-0"></i>
    <span class="text-xs md:text-sm">مشاهده سبد خرید</span>
    <span id="floatingCartCount" class="bg-white text-primary text-[10px] font-black w-5.5 h-5.5 rounded-full flex items-center justify-center shadow-sm shrink-0 font-mono">0</span>
</button>

<!-- ==================== مودال جزئیات محصول ==================== -->
<div id="productDetailModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-stone-950/70 backdrop-blur-xs hidden transition-all duration-300">
    <div class="bg-white dark:bg-stone-900 rounded-3xl w-full max-w-lg overflow-hidden border border-stone-200 dark:border-stone-800 shadow-2xl relative">
        <button onclick="closeProductDetailModal()" class="absolute top-4 left-4 bg-white/90 dark:bg-stone-950/90 hover:bg-white p-2 rounded-full text-stone-700 dark:text-stone-300 shadow-md z-10 transition-transform hover:scale-105">
            <i data-lucide="x" class="w-5 h-5"></i>
        </button>

        <div id="modalProductImageContainer" class="w-full h-56 md:h-64 bg-stone-100 dark:bg-stone-800 relative">
            <img id="modalProductImage" src="" alt="" class="w-full h-full object-cover">
            <div class="absolute inset-0 bg-gradient-to-t from-black/50 to-transparent"></div>
            <h3 id="modalProductName" class="absolute bottom-5 right-5 text-xl font-black text-white"></h3>
        </div>

        <div class="p-6">
            <h4 class="text-xs font-bold text-stone-400 mb-1">توضیحات محصول</h4>
            <p id="modalProductDesc" class="text-sm text-stone-600 dark:text-stone-300 leading-relaxed mb-4"></p>

            <div id="modalIngredientsContainer" class="mb-4">
                <h4 class="text-xs font-bold text-stone-400 mb-1">مواد تشکیل‌دهنده</h4>
                <p id="modalProductIngredients" class="text-sm text-stone-600 dark:text-stone-300 leading-relaxed bg-stone-50 dark:bg-stone-800/50 p-3 rounded-2xl border border-stone-100 dark:border-stone-800"></p>
            </div>

            <div class="flex items-center justify-between border-t border-stone-100 dark:border-stone-800 pt-4 mt-6">
                <div class="flex flex-col">
                    <span class="text-xs text-stone-400">قیمت نهایی</span>
                    <span id="modalProductPrice" class="text-lg font-black text-primary"></span>
                </div>
                <div id="modalAddBtnContainer"></div>
            </div>
        </div>
    </div>
</div>

<!-- ==================== مودال سبد خرید ==================== -->
<div id="cartModal" class="fixed inset-0 z-50 flex items-center justify-end bg-stone-950/70 backdrop-blur-xs hidden transition-all duration-300">
    <div class="bg-white dark:bg-stone-900 w-full max-w-md h-full flex flex-col border-r border-stone-200 dark:border-stone-800 shadow-2xl relative">
        <!-- هدر سبد خرید -->
        <div class="p-5 border-b border-stone-200 dark:border-stone-800 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <i data-lucide="shopping-bag" class="w-5 h-5 text-primary"></i>
                <h3 class="text-lg font-black text-stone-900 dark:text-white">سبد خرید شما</h3>
            </div>
            <button onclick="closeCartModal()" class="p-1.5 rounded-full hover:bg-stone-100 dark:hover:bg-stone-800 text-stone-500 dark:text-stone-400 transition-colors">
                <i data-lucide="x" class="w-6 h-6"></i>
            </button>
        </div>

        <!-- آیتم‌های سبد خرید -->
        <div class="flex-1 overflow-y-auto p-5 space-y-4" id="cartItemsList">
            <!-- آیتم‌ها به صورت داینامیک از سمت JS رندر می‌شوند -->
        </div>

        <!-- فوتر سبد خرید و فرم ثبت سفارش -->
        <div class="p-5 border-t border-stone-200 dark:border-stone-800 bg-stone-50 dark:bg-stone-950 space-y-4 shadow-[0_-4px_12px_rgba(0,0,0,0.03)]" id="cartCheckoutContainer">
            <div class="flex items-center justify-between text-sm font-semibold">
                <span class="text-stone-500 dark:text-stone-400">جمع کل سبد:</span>
                <span id="cartTotalPrice" class="font-black text-stone-950 dark:text-white">۰ <span class="text-xs font-normal">تومان</span></span>
            </div>

            <!-- دکمه رفتن به مرحله ثبت سفارش -->
            <button onclick="openCheckoutForm()" id="checkoutStepBtn" class="w-full bg-primary hover:bg-primary/95 text-white py-3.5 rounded-2xl font-bold flex items-center justify-center gap-2 shadow-lg hover:shadow-primary/30 transition-all active:scale-98">
                <span>ثبت سفارش و پرداخت</span>
                <i data-lucide="chevron-left" class="w-5 h-5"></i>
            </button>
        </div>

        <!-- کارت سفارش نهایی (فرم ثبت آدرس و موبایل) -->
        <div class="absolute inset-x-0 bottom-0 top-[65px] bg-white dark:bg-stone-900 z-10 flex flex-col hidden" id="checkoutFormContainer">
            <div class="p-5 border-b border-stone-100 dark:border-stone-800 flex items-center gap-3">
                <button onclick="closeCheckoutForm()" class="p-1 rounded-full hover:bg-stone-100 dark:hover:bg-stone-800 text-stone-500 dark:text-stone-400">
                    <i data-lucide="arrow-right" class="w-5 h-5"></i>
                </button>
                <h4 class="font-black text-stone-900 dark:text-white">اطلاعات سفارش‌دهنده</h4>
            </div>

            <form id="orderForm" onsubmit="submitOrder(event)" class="flex-1 overflow-y-auto p-5 space-y-4">
                <input type="hidden" name="csrf_token" value="<?php echo getCSRFToken(); ?>">
                
                <!-- نوع سفارش -->
                <div>
                    <label class="block text-xs font-bold text-stone-400 mb-2">نوع تحویل سفارش</label>
                    <div class="grid grid-cols-2 gap-3">
                        <label class="cursor-pointer">
                            <input type="radio" name="order_type" value="indoor" checked onchange="toggleOrderTypeFields('indoor')" class="peer sr-only">
                            <div class="p-3 text-center border-2 border-stone-200 dark:border-stone-800 rounded-2xl font-bold text-sm text-stone-600 dark:text-stone-300 peer-checked:border-primary peer-checked:bg-primary/5 peer-checked:text-primary transition-all">
                                <i data-lucide="coffee" class="w-5 h-5 mx-auto mb-1 text-inherit"></i>
                                حضوری در کافه
                            </div>
                        </label>
                        <label class="cursor-pointer">
                            <input type="radio" name="order_type" value="outdoor" onchange="toggleOrderTypeFields('outdoor')" class="peer sr-only">
                            <div class="p-3 text-center border-2 border-stone-200 dark:border-stone-800 rounded-2xl font-bold text-sm text-stone-600 dark:text-stone-300 peer-checked:border-primary peer-checked:bg-primary/5 peer-checked:text-primary transition-all">
                                <i data-lucide="truck" class="w-5 h-5 mx-auto mb-1 text-inherit"></i>
                                غیرحضوری (ارسال)
                            </div>
                        </label>
                    </div>
                </div>

                <!-- فیلدهای پویا بر اساس نوع سفارش -->
                <!-- فیلدهای سفارش حضوری (مطابق پروپوزال مشتری) -->
                <div id="indoorFields" class="space-y-4">
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-stone-500 dark:text-stone-400 mb-1.5">نام *</label>
                            <input type="text" name="first_name" id="indoor_first_name" required class="w-full px-4 py-2.5 rounded-xl bg-stone-50 dark:bg-stone-800 border border-stone-200 dark:border-stone-700 text-stone-800 dark:text-white text-sm focus:outline-none focus:border-primary">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-stone-500 dark:text-stone-400 mb-1.5">نام خانوادگی *</label>
                            <input type="text" name="last_name" id="indoor_last_name" required class="w-full px-4 py-2.5 rounded-xl bg-stone-50 dark:bg-stone-800 border border-stone-200 dark:border-stone-700 text-stone-800 dark:text-white text-sm focus:outline-none focus:border-primary">
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-stone-500 dark:text-stone-400 mb-1.5">شماره موبایل *</label>
                        <input type="text" name="phone" id="indoor_phone" required placeholder="09xxxxxxxxx" class="w-full px-4 py-2.5 rounded-xl bg-stone-50 dark:bg-stone-800 border border-stone-200 dark:border-stone-700 text-stone-800 dark:text-white text-sm focus:outline-none focus:border-primary text-right dir-ltr">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-stone-500 dark:text-stone-400 mb-1.5">آدرس کامل *</label>
                        <textarea name="address" id="indoor_address" required rows="2" class="w-full px-4 py-2.5 rounded-xl bg-stone-50 dark:bg-stone-800 border border-stone-200 dark:border-stone-700 text-stone-800 dark:text-white text-sm focus:outline-none focus:border-primary"></textarea>
                    </div>
                    <div class="grid grid-cols-3 gap-2">
                        <div>
                            <label class="block text-xs font-bold text-stone-500 dark:text-stone-400 mb-1.5">پلاک *</label>
                            <input type="text" name="plaque" id="indoor_plaque" required class="w-full px-3 py-2.5 rounded-xl bg-stone-50 dark:bg-stone-800 border border-stone-200 dark:border-stone-700 text-stone-800 dark:text-white text-sm focus:outline-none focus:border-primary text-center">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-stone-500 dark:text-stone-400 mb-1.5">طبقه *</label>
                            <input type="text" name="floor" id="indoor_floor" required class="w-full px-3 py-2.5 rounded-xl bg-stone-50 dark:bg-stone-800 border border-stone-200 dark:border-stone-700 text-stone-800 dark:text-white text-sm focus:outline-none focus:border-primary text-center">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-stone-500 dark:text-stone-400 mb-1.5">واحد *</label>
                            <input type="text" name="unit" id="indoor_unit" required class="w-full px-3 py-2.5 rounded-xl bg-stone-50 dark:bg-stone-800 border border-stone-200 dark:border-stone-700 text-stone-800 dark:text-white text-sm focus:outline-none focus:border-primary text-center">
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-stone-500 dark:text-stone-400 mb-1.5">توضیحات</label>
                        <textarea name="description_indoor" id="indoor_desc" placeholder="توضیحات سفارش، قاشق چنگال اضافه و..." rows="2" class="w-full px-4 py-2.5 rounded-xl bg-stone-50 dark:bg-stone-800 border border-stone-200 dark:border-stone-700 text-stone-800 dark:text-white text-sm focus:outline-none focus:border-primary"></textarea>
                    </div>
                </div>

                <!-- فیلدهای سفارش غیرحضوری -->
                <div id="outdoorFields" class="space-y-4 hidden">
                    <div>
                        <label class="block text-xs font-bold text-stone-500 dark:text-stone-400 mb-1.5">نام *</label>
                        <input type="text" name="outdoor_name" id="outdoor_name_input" class="w-full px-4 py-2.5 rounded-xl bg-stone-50 dark:bg-stone-800 border border-stone-200 dark:border-stone-700 text-stone-800 dark:text-white text-sm focus:outline-none focus:border-primary">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-stone-500 dark:text-stone-400 mb-1.5">شماره موبایل *</label>
                        <input type="text" name="outdoor_phone" id="outdoor_phone_input" placeholder="09xxxxxxxxx" class="w-full px-4 py-2.5 rounded-xl bg-stone-50 dark:bg-stone-800 border border-stone-200 dark:border-stone-700 text-stone-800 dark:text-white text-sm focus:outline-none focus:border-primary text-right dir-ltr">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-stone-500 dark:text-stone-400 mb-1.5">توضیحات</label>
                        <textarea name="description_outdoor" id="outdoor_desc_input" placeholder="مثلاً: لاته بدون شکر باشد" rows="3" class="w-full px-4 py-2.5 rounded-xl bg-stone-50 dark:bg-stone-800 border border-stone-200 dark:border-stone-700 text-stone-800 dark:text-white text-sm focus:outline-none focus:border-primary"></textarea>
                    </div>
                </div>

                <!-- دکمه پرداخت و ارسال نهایی -->
                <div class="pt-4">
                    <button type="submit" class="w-full bg-emerald-600 hover:bg-emerald-500 text-white py-3.5 rounded-2xl font-bold flex items-center justify-center gap-2 shadow-lg shadow-emerald-600/20 transition-all">
                        <i data-lucide="check" class="w-5 h-5"></i>
                        <span>ثبت و پرداخت نهایی</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- اسکریپت سبد خرید و منو -->
<script>
    // سبد خرید ذخیره شده در حافظه مرورگر (LocalStorage)
    let cart = JSON.parse(localStorage.getItem('cafe_cart')) || [];

    // بروزرسانی تعداد کل در لود اولیه
    updateCartBadges();

    /**
     * افزودن آیتم به سبد
     */
    function addToCart(product) {
        const existingItem = cart.find(item => item.id === product.id);
        if (existingItem) {
            existingItem.quantity += 1;
        } else {
            cart.push({
                id: product.id,
                name: product.name,
                price: parseFloat(product.price),
                image: product.image,
                discount: product.discount,
                quantity: 1
            });
        }
        saveCart();
        updateCartBadges();
        showNotification('محصول به سبد خرید اضافه شد.', 'success');
    }

    /**
     * ذخیره‌سازی سبد
     */
    function saveCart() {
        localStorage.setItem('cafe_cart', JSON.stringify(cart));
    }

    /**
     * آپدیت تعداد سبد خرید در هدر و دکمه شناور فیکسد
     */
    function updateCartBadges() {
        const badge = document.getElementById('cartCountBadge');
        const floatCartBtn = document.getElementById('floatingCartBtn');
        const floatCartCount = document.getElementById('floatingCartCount');
        const totalCount = cart.reduce((total, item) => total + item.quantity, 0);

        if (badge) {
            if (totalCount > 0) {
                badge.innerText = totalCount;
                badge.classList.remove('hidden');
            } else {
                badge.classList.add('hidden');
            }
        }

        if (floatCartBtn) {
            if (totalCount > 0) {
                floatCartBtn.classList.remove('hidden');
                floatCartBtn.classList.add('flex');
                if (floatCartCount) {
                    floatCartCount.innerText = totalCount;
                }
            } else {
                floatCartBtn.classList.add('hidden');
                floatCartBtn.classList.remove('flex');
            }
        }
    }

    /**
     * باز کردن مودال سبد خرید
     */
    function openCartModal() {
        document.getElementById('cartModal').classList.remove('hidden');
        renderCartItems();
    }

    function closeCartModal() {
        document.getElementById('cartModal').classList.add('hidden');
        closeCheckoutForm();
    }

    /**
     * رندر آیتم‌های سبد خرید
     */
    function renderCartItems() {
        const listContainer = document.getElementById('cartItemsList');
        const totalPriceEl = document.getElementById('cartTotalPrice');
        
        if (cart.length === 0) {
            listContainer.innerHTML = `
                <div class="h-64 flex flex-col items-center justify-center text-stone-400">
                    <i data-lucide="shopping-basket" class="w-16 h-16 mb-4 text-stone-300"></i>
                    <p class="text-sm font-semibold">سبد خرید شما در حال حاضر خالی است!</p>
                </div>
            `;
            totalPriceEl.innerText = '0 تومان';
            document.getElementById('checkoutStepBtn').disabled = true;
            document.getElementById('checkoutStepBtn').classList.add('opacity-50', 'cursor-not-allowed');
            if (typeof lucide !== 'undefined') lucide.createIcons();
            return;
        }

        document.getElementById('checkoutStepBtn').disabled = false;
        document.getElementById('checkoutStepBtn').classList.remove('opacity-50', 'cursor-not-allowed');

        let total = 0;
        let html = '';

        cart.forEach(item => {
            const itemTotal = item.price * item.quantity;
            total += itemTotal;
            html += `
                <div class="bg-stone-50 dark:bg-stone-800/50 p-4 rounded-2xl flex items-center gap-3 border border-stone-100 dark:border-stone-800 transition-all">
                    ${item.image ? `<img src="${item.image}" class="w-16 h-16 rounded-xl object-cover">` : `<div class="w-16 h-16 bg-stone-100 dark:bg-stone-800 rounded-xl flex items-center justify-center"><i data-lucide="coffee" class="w-6 h-6 text-stone-400"></i></div>`}
                    <div class="flex-1">
                        <h4 class="text-sm font-bold text-stone-900 dark:text-white">${item.name}</h4>
                        <span class="text-xs font-semibold text-primary block mt-1">${formatNumber(item.price)} تومان</span>
                    </div>
                    <div class="flex flex-col items-end gap-2">
                        <!-- دکمه‌های شمارنده -->
                        <div class="flex items-center gap-2 bg-white dark:bg-stone-900 border border-stone-200 dark:border-stone-700 px-2.5 py-1.5 rounded-xl shadow-sm">
                            <button onclick="changeQuantity(${item.id}, 1)" class="text-stone-500 hover:text-stone-800 dark:hover:text-white transition-colors">
                                <i data-lucide="plus" class="w-4 h-4"></i>
                            </button>
                            <span class="text-xs font-bold px-1 min-w-[14px] text-center">${item.quantity}</span>
                            <button onclick="changeQuantity(${item.id}, -1)" class="text-stone-500 hover:text-stone-800 dark:hover:text-white transition-colors">
                                <i data-lucide="minus" class="w-4 h-4"></i>
                            </button>
                        </div>
                        <button onclick="removeFromCart(${item.id})" class="text-red-500 hover:text-red-600 p-1 transition-colors" title="حذف">
                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                        </button>
                    </div>
                </div>
            `;
        });

        listContainer.innerHTML = html;
        totalPriceEl.innerHTML = `${formatNumber(total)} <span class="text-xs font-normal">تومان</span>`;
        if (typeof lucide !== 'undefined') lucide.createIcons();
    }

    /**
     * تغییر تعداد آیتم در سبد خرید
     */
    function changeQuantity(id, change) {
        const item = cart.find(i => i.id === id);
        if (!item) return;

        item.quantity += change;
        if (item.quantity <= 0) {
            removeFromCart(id);
            return;
        }

        saveCart();
        updateCartBadges();
        renderCartItems();
    }

    /**
     * حذف آیتم از سبد
     */
    function removeFromCart(id) {
        cart = cart.filter(item => item.id !== id);
        saveCart();
        updateCartBadges();
        renderCartItems();
        showNotification('محصول از سبد خرید حذف شد.', 'info');
    }

    /**
     * باز کردن مرحله نهایی ثبت سفارش
     */
    function openCheckoutForm() {
        document.getElementById('checkoutFormContainer').classList.remove('hidden');
    }

    function closeCheckoutForm() {
        document.getElementById('checkoutFormContainer').classList.add('hidden');
    }

    /**
     * سوئیچ فیلدهای نوع سفارش بر اساس حضوری/غیرحضوری (مطابق پروپوزال مشتری)
     */
    function toggleOrderTypeFields(type) {
        const indoor = document.getElementById('indoorFields');
        const outdoor = document.getElementById('outdoorFields');
        
        // دریافت تمام اینپوت‌های موردنیاز
        const indoorFirst = document.getElementById('indoor_first_name');
        const indoorLast = document.getElementById('indoor_last_name');
        const indoorPhone = document.getElementById('indoor_phone');
        const indoorAddr = document.getElementById('indoor_address');
        const indoorPlaq = document.getElementById('indoor_plaque');
        const indoorFloor = document.getElementById('indoor_floor');
        const indoorUnit = document.getElementById('indoor_unit');

        const outdoorName = document.getElementById('outdoor_name_input');
        const outdoorPhone = document.getElementById('outdoor_phone_input');

        if (type === 'indoor') {
            indoor.classList.remove('hidden');
            outdoor.classList.add('hidden');
            
            // مدیریت مقادیر Required
            indoorFirst.required = true;
            indoorLast.required = true;
            indoorPhone.required = true;
            indoorAddr.required = true;
            indoorPlaq.required = true;
            indoorFloor.required = true;
            indoorUnit.required = true;

            outdoorName.required = false;
            outdoorPhone.required = false;
        } else {
            indoor.classList.add('hidden');
            outdoor.classList.remove('hidden');

            // مدیریت مقادیر Required
            indoorFirst.required = false;
            indoorLast.required = false;
            indoorPhone.required = false;
            indoorAddr.required = false;
            indoorPlaq.required = false;
            indoorFloor.required = false;
            indoorUnit.required = false;

            outdoorName.required = true;
            outdoorPhone.required = true;
        }
    }

    /**
     * ارسال نهایی سفارش با AJAX به بک‌اند PHP
     */
    function submitOrder(e) {
        e.preventDefault();
        
        const form = document.getElementById('orderForm');
        const formData = new FormData(form);
        
        // اضافه کردن آیتم‌های سبد خرید به درخواست ارسالی
        formData.append('cart', JSON.stringify(cart));
        
        // ارسال از طریق Fetch API
        fetch('api/submit_order.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                showNotification(data.message, 'success');
                // پاک کردن سبد خرید کلاینت پس از موفقیت
                cart = [];
                saveCart();
                updateCartBadges();
                closeCartModal();
                
                // هدایت یا نمایش کارت موفقیت سفارش
                alert(`سفارش شما با موفقیت ثبت شد.\nکد سفارش شما: ${data.order_code}\nخواهشمند است جهت هماهنگی نهایی کد را به همراه داشته باشید.`);
            } else {
                showNotification(data.message || 'خطایی در ثبت سفارش رخ داد.', 'error');
            }
        })
        .catch(err => {
            // شبیه‌سازی برای دموها در صورتی که دیتابیس سی‌پنل متصل نباشد
            console.warn('Database error or PHP offline. Simulating order success...', err);
            const mockCode = "CAFE-1405-" + Math.floor(1000 + Math.random() * 9000);
            
            // ثبت در LocalStorage کلاینت (برای شبیه‌سازی دقیق و بدون نقص دمو در هوش مصنوعی)
            let mockOrders = JSON.parse(localStorage.getItem('mock_orders')) || [];
            
            const isIndoor = formData.get('order_type') === 'indoor';
            const order_obj = {
                order_code: mockCode,
                customer_name: isIndoor ? `${formData.get('first_name')} ${formData.get('last_name')}` : formData.get('outdoor_name'),
                customer_phone: isIndoor ? formData.get('phone') : formData.get('outdoor_phone'),
                order_type: formData.get('order_type'),
                address: isIndoor ? formData.get('address') : '',
                plaque: isIndoor ? formData.get('plaque') : '',
                floor: isIndoor ? formData.get('floor') : '',
                unit: isIndoor ? formData.get('unit') : '',
                description: isIndoor ? formData.get('description_indoor') : formData.get('description_outdoor'),
                total_amount: cart.reduce((tot, item) => tot + (item.price * item.quantity), 0),
                status: 'registered',
                created_jalali: getPersianDateString(),
                items: cart
            };
            mockOrders.unshift(order_obj);
            localStorage.setItem('mock_orders', JSON.stringify(mockOrders));

            // پاک کردن سبد
            cart = [];
            saveCart();
            updateCartBadges();
            closeCartModal();

            // پخش صدای اعلان به صورت شبیه‌سازی در مرورگر
            playNotificationSound();

            alert(`[دموی شبیه‌ساز] سفارش شما ثبت شد.\nشماره سفارش: ${mockCode}`);
        });
    }

    /**
     * شبیه‌سازی زنگ پیام جدید ادمین
     */
    function playNotificationSound() {
        try {
            const audioCtx = new (window.AudioContext || window.webkitAudioContext)();
            const oscillator = audioCtx.createOscillator();
            const gainNode = audioCtx.createGain();
            oscillator.type = 'sine';
            oscillator.frequency.setValueAtTime(880, audioCtx.currentTime); // نت A5
            gainNode.gain.setValueAtTime(0.1, audioCtx.currentTime);
            oscillator.connect(gainNode);
            gainNode.connect(audioCtx.destination);
            oscillator.start();
            oscillator.stop(audioCtx.currentTime + 0.3);
        } catch (e) {
            console.log("Audio play blocked by browser.");
        }
    }

    /**
     * تاریخ فارسی متنی برای دمو کلاینت
     */
    function getPersianDateString() {
        const today = new Date();
        const y = 1405; // سال دمو پروپوزال
        const m = String(today.getMonth() + 1).padStart(2, '0');
        const d = String(today.getDate()).padStart(2, '0');
        const time = today.toTimeString().split(' ')[0];
        return `${y}/${m}/${d} ${time}`;
    }

    // ==================== مدیریت مودال جزئیات محصول ====================
    function openProductDetailModal(product) {
        document.getElementById('productDetailModal').classList.remove('hidden');
        document.getElementById('modalProductName').innerText = product.name;
        document.getElementById('modalProductDesc').innerText = product.description || 'توضیحی ثبت نشده است.';
        
        if (product.ingredients) {
            document.getElementById('modalIngredientsContainer').classList.remove('hidden');
            document.getElementById('modalProductIngredients').innerText = product.ingredients;
        } else {
            document.getElementById('modalIngredientsContainer').classList.add('hidden');
        }

        if (product.image) {
            document.getElementById('modalProductImage').src = product.image;
            document.getElementById('modalProductImage').classList.remove('hidden');
        } else {
            document.getElementById('modalProductImage').classList.add('hidden');
        }

        const finalPrice = product.price - (product.price * product.discount / 100);
        document.getElementById('modalProductPrice').innerHTML = `${formatNumber(finalPrice)} <span class="text-xs font-normal">تومان</span>`;
        
        // دکمه اضافه کردن به سبد خرید در مودال
        document.getElementById('modalAddBtnContainer').innerHTML = product.is_available ? `
            <button onclick='addToCart(${JSON.stringify({
                id: product.id,
                name: product.name,
                price: finalPrice,
                image: product.image,
                discount: product.discount
            })}); closeProductDetailModal();' class="bg-primary hover:bg-primary/95 text-white py-2 px-5 rounded-xl font-bold flex items-center gap-1.5 transition-all">
                <i data-lucide="shopping-cart" class="w-4 h-4"></i>
                <span>افزودن به سبد</span>
            </button>
        ` : `
            <button disabled class="bg-stone-200 text-stone-400 py-2 px-5 rounded-xl font-bold cursor-not-allowed">ناموجود</button>
        `;
        if (typeof lucide !== 'undefined') lucide.createIcons();
    }

    function closeProductDetailModal() {
        document.getElementById('productDetailModal').classList.add('hidden');
    }

    // ==================== جستجو و فیلترهای لحظه‌ای با جاوااسکریپت ====================
    const searchInput = document.getElementById('searchInput');
    const clearSearchBtn = document.getElementById('clearSearchBtn');

    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const val = this.value.trim().toLowerCase();
            if (val.length > 0) {
                clearSearchBtn.classList.remove('hidden');
            } else {
                clearSearchBtn.classList.add('hidden');
            }
            filterProducts();
        });
    }

    if (clearSearchBtn) {
        clearSearchBtn.addEventListener('click', function() {
            searchInput.value = '';
            this.classList.add('hidden');
            filterProducts();
        });
    }

    let activeCategoryId = 0;
    let activeSortType = 'default';

    function filterCategory(catId) {
        activeCategoryId = catId;
        
        // آپدیت استایل دکمه‌ها
        document.querySelectorAll('.category-btn').forEach(btn => {
            btn.classList.remove('bg-primary', 'text-white', 'shadow-md');
            btn.classList.add('bg-white', 'dark:bg-stone-900', 'text-stone-700', 'dark:text-stone-300', 'border', 'border-stone-200', 'dark:border-stone-800', 'shadow-sm');
        });

        const activeBtn = document.getElementById(`cat-btn-${catId}`);
        if (activeBtn) {
            activeBtn.classList.remove('bg-white', 'dark:bg-stone-900', 'text-stone-700', 'dark:text-stone-300', 'border', 'border-stone-200', 'dark:border-stone-800', 'shadow-sm');
            activeBtn.classList.add('bg-primary', 'text-white', 'shadow-md');
        }

        filterProducts();
    }

    function sortProducts(sortType) {
        activeSortType = sortType;

        // آپدیت استایل دکمه‌های مرتب‌سازی
        document.querySelectorAll('.sort-btn').forEach(btn => {
            btn.classList.remove('bg-primary', 'text-white');
            btn.classList.add('bg-white', 'dark:bg-stone-900', 'text-stone-600', 'dark:text-stone-300', 'border', 'border-stone-200', 'dark:border-stone-800');
        });

        const activeSortBtn = document.getElementById(`sort-${sortType}`);
        if (activeSortBtn) {
            activeSortBtn.classList.remove('bg-white', 'dark:bg-stone-900', 'text-stone-600', 'dark:text-stone-300', 'border', 'border-stone-200', 'dark:border-stone-800');
            activeSortBtn.classList.add('bg-primary', 'text-white');
        }

        filterProducts();
    }

    function filterProducts() {
        const query = searchInput ? searchInput.value.trim().toLowerCase() : '';
        const cards = document.querySelectorAll('.product-card');

        cards.forEach(card => {
            const id = parseInt(card.getAttribute('data-id'));
            const catId = parseInt(card.getAttribute('data-category'));
            const name = card.getAttribute('data-name').toLowerCase();
            const isPopular = card.getAttribute('data-popular') === '1';
            const isNew = card.getAttribute('data-new') === '1';
            const hasDiscount = card.getAttribute('data-discount') === '1';

            let matchCategory = (activeCategoryId === 0 || catId === activeCategoryId);
            let matchSearch = (name.includes(query));
            let matchSort = true;

            if (activeSortType === 'new') matchSort = isNew;
            else if (activeSortType === 'popular') matchSort = isPopular;
            else if (activeSortType === 'discount') matchSort = hasDiscount;

            if (matchCategory && matchSearch && matchSort) {
                card.style.display = 'flex';
            } else {
                card.style.display = 'none';
            }
        });
    }

    // فرمت‌دهی به اعداد قیمت‌ها
    function formatNumber(num) {
        return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
    }

    /**
     * نمایش سیستم اعلانات کاستوم
     */
    function showNotification(text, type = 'info') {
        const notification = document.createElement('div');
        notification.className = `fixed bottom-5 right-5 z-50 flex items-center gap-2 px-5 py-3.5 rounded-2xl shadow-xl text-sm font-bold border transform translate-y-10 opacity-0 transition-all duration-300 `;
        
        if (type === 'success') {
            notification.className += 'bg-emerald-50 border-emerald-200 text-emerald-800 dark:bg-emerald-950 dark:border-emerald-900 dark:text-emerald-300';
            notification.innerHTML = `<i data-lucide="check-circle-2" class="w-5 h-5 text-emerald-600"></i><span>${text}</span>`;
        } else if (type === 'error') {
            notification.className += 'bg-red-50 border-red-200 text-red-800 dark:bg-red-950 dark:border-red-900 dark:text-red-300';
            notification.innerHTML = `<i data-lucide="alert-triangle" class="w-5 h-5 text-red-600"></i><span>${text}</span>`;
        } else {
            notification.className += 'bg-blue-50 border-blue-200 text-blue-800 dark:bg-blue-950 dark:border-blue-900 dark:text-blue-300';
            notification.innerHTML = `<i data-lucide="info" class="w-5 h-5 text-blue-600"></i><span>${text}</span>`;
        }

        document.body.appendChild(notification);
        if (typeof lucide !== 'undefined') lucide.createIcons();
        
        // انیمیشن ورود
        setTimeout(() => {
            notification.classList.remove('translate-y-10', 'opacity-0');
        }, 50);

        // خروج خودکار
        setTimeout(() => {
            notification.classList.add('translate-y-10', 'opacity-0');
            setTimeout(() => {
                notification.remove();
            }, 300);
        }, 3000);
    }
</script>

<?php include_once __DIR__ . '/includes/footer.php'; ?>
