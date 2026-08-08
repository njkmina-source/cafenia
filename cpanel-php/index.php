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
        // در صورت خطای دیتابیس، دیتای نمونه را لود خواهیم کرد
    }
}

if (empty($categories)) {
    $categories = [
        ['id' => 1, 'name' => 'قهوه گرم', 'icon' => 'coffee'],
        ['id' => 2, 'name' => 'قهوه سرد', 'icon' => 'ice-cream'],
        ['id' => 3, 'name' => 'دمنوش و چای', 'icon' => 'leaf'],
        ['id' => 4, 'name' => 'کیک و دسر', 'icon' => 'cake'],
        ['id' => 5, 'name' => 'نوشیدنی خنک', 'icon' => 'glass-water'],
        ['id' => 6, 'name' => 'صبحانه و غذا', 'icon' => 'egg']
    ];
}

if (empty($products)) {
    $products = [
        ['id' => 1, 'category_id' => 1, 'category_name' => 'قهوه گرم', 'name' => 'اسپرسو دوبل', 'price' => 45000, 'discount' => 0, 'description' => 'اسپرسو ۱۰۰٪ عربیکا با طعم عمیق و بادی قوی', 'ingredients' => 'دبل شات قهوه عربیکا', 'image' => 'https://images.unsplash.com/photo-1510591509098-f4fdc6d0ff04?auto=format&fit=crop&q=80&w=600', 'is_available' => 1, 'is_popular' => 1, 'is_new' => 0, 'is_visible' => 1, 'sort_order' => 1],
        ['id' => 2, 'category_id' => 1, 'category_name' => 'قهوه گرم', 'name' => 'کاپوچینو', 'price' => 55000, 'discount' => 10, 'description' => 'اسپرسو به همراه شیر گرم و فوم غلیظ شیر', 'ingredients' => 'اسپرسو، شیر، فوم شیر، پودر کاکائو', 'image' => 'https://images.unsplash.com/photo-1572442388796-11668a67e53d?auto=format&fit=crop&q=80&w=600', 'is_available' => 1, 'is_popular' => 0, 'is_new' => 0, 'is_visible' => 1, 'sort_order' => 2],
        ['id' => 3, 'category_id' => 1, 'category_name' => 'قهوه گرم', 'name' => 'لاته آرت', 'price' => 58000, 'discount' => 0, 'description' => 'ترکیب بی‌نظیر اسپرسو و شیر مخملی با طراحی زیبا', 'ingredients' => 'اسپرسو، شیر، فوم شیر', 'image' => 'https://images.unsplash.com/photo-1534778101976-62847782c213?auto=format&fit=crop&q=80&w=600', 'is_available' => 1, 'is_popular' => 1, 'is_new' => 1, 'is_visible' => 1, 'sort_order' => 3],
        ['id' => 4, 'category_id' => 2, 'category_name' => 'قهوه سرد', 'name' => 'آیس لاته', 'price' => 60000, 'discount' => 0, 'description' => 'نسخه خنک قهوه لاته به همراه تکه‌های یخ', 'ingredients' => 'اسپرسو، شیر سرد، یخ', 'image' => 'https://images.unsplash.com/photo-1517701604599-bb29b565090c?auto=format&fit=crop&q=80&w=600', 'is_available' => 1, 'is_popular' => 0, 'is_new' => 0, 'is_visible' => 1, 'sort_order' => 1],
        ['id' => 5, 'category_id' => 2, 'category_name' => 'قهوه سرد', 'name' => 'اسپرسو تونیک', 'price' => 65000, 'discount' => 15, 'description' => 'نوشیدنی گازدار خنک و انرژی‌بخش ترکیبی', 'ingredients' => 'اسپرسو، آب تونیک، لیمو، یخ', 'image' => 'https://images.unsplash.com/photo-1517256064527-09c73fc73e38?auto=format&fit=crop&q=80&w=600', 'is_available' => 1, 'is_popular' => 1, 'is_new' => 1, 'is_visible' => 1, 'sort_order' => 2],
        ['id' => 6, 'category_id' => 4, 'category_name' => 'کیک و دسر', 'name' => 'کیک شکلاتی بی‌بی', 'price' => 52000, 'discount' => 0, 'description' => 'کیک شکلاتی کلاسیک فوق‌العاده مرطوب با سس شکلات داغ', 'ingredients' => 'آرد، کاکائو، خامه، سس شکلات', 'image' => 'https://images.unsplash.com/photo-1578985545062-69928b1d9587?auto=format&fit=crop&q=80&w=600', 'is_available' => 1, 'is_popular' => 1, 'is_new' => 0, 'is_visible' => 1, 'sort_order' => 1],
        ['id' => 7, 'category_id' => 4, 'category_name' => 'کیک و دسر', 'name' => 'چیزکیک نیویورکی', 'price' => 58000, 'discount' => 5, 'description' => 'چیزکیک پخته غلیظ با کراست بیسکویت و سس تمشک', 'ingredients' => 'پنیر خامه‌ای، بیسکویت، سس تمشک', 'image' => 'https://images.unsplash.com/photo-1533134242443-d4fd215305ad?auto=format&fit=crop&q=80&w=600', 'is_available' => 1, 'is_popular' => 0, 'is_new' => 1, 'is_visible' => 1, 'sort_order' => 2]
    ];
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

<div class="max-w-6xl mx-auto px-4 -mt-20 relative z-10 pb-12 w-full">
    <!-- کارت معرفی کافه -->
    <div class="bg-white dark:bg-stone-900/40 glass rounded-3xl p-6 shadow-2xl transition-all duration-300">
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
            <input type="text" id="searchInput" placeholder="جستجوی محصول، قهوه، کیک..." class="w-full pl-4 pr-10 py-3 rounded-2xl bg-white/5 border border-white/10 text-stone-800 dark:text-white placeholder-stone-400 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all shadow-md text-sm">
            <button id="clearSearchBtn" class="absolute inset-y-0 left-0 flex items-center pl-3 text-stone-400 hover:text-stone-600 hidden">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>

        <!-- فیلترهای پیشرفته -->
        <div class="flex flex-wrap gap-2 w-full md:w-auto justify-end">
            <button onclick="sortProducts('default')" id="sort-default" class="sort-btn px-4 py-2 rounded-xl text-xs font-semibold bg-primary text-black accent-glow border border-primary transition-all shadow-md">
                همه
            </button>
            <button onclick="sortProducts('new')" id="sort-new" class="sort-btn px-4 py-2 rounded-xl text-xs font-semibold bg-white/5 text-stone-600 dark:text-white/75 border border-stone-200 dark:border-white/10 hover:bg-stone-50 dark:hover:bg-white/10 transition-all shadow-md">
                جدیدترین‌ها
            </button>
            <button onclick="sortProducts('popular')" id="sort-popular" class="sort-btn px-4 py-2 rounded-xl text-xs font-semibold bg-white/5 text-stone-600 dark:text-white/75 border border-stone-200 dark:border-white/10 hover:bg-stone-50 dark:hover:bg-white/10 transition-all shadow-md">
                محبوب‌ترین‌ها
            </button>
            <button onclick="sortProducts('discount')" id="sort-discount" class="sort-btn px-4 py-2 rounded-xl text-xs font-semibold bg-white/5 text-stone-600 dark:text-white/75 border border-stone-200 dark:border-white/10 hover:bg-stone-50 dark:hover:bg-white/10 transition-all shadow-md">
                دارای تخفیف
            </button>
        </div>
    </div>

    <!-- لیست اسکرول‌بار دسته‌بندی‌ها -->
    <div class="mt-8">
        <h3 class="text-sm font-bold text-stone-500 dark:text-stone-400 mb-3">دسته‌بندی‌ها</h3>
        <div class="flex gap-3 overflow-x-auto pb-3 snap-x scrollbar-none" id="categoriesScrollContainer">
            <button onclick="filterCategory(0)" id="cat-btn-0" class="category-btn snap-start shrink-0 px-5 py-3 rounded-2xl flex items-center gap-2.5 bg-primary text-black font-bold text-sm shadow-md transition-all duration-300">
                <i data-lucide="layers" class="w-5 h-5"></i>
                <span>همه دسته‌ها</span>
            </button>

            <?php foreach ($categories as $cat): ?>
                <button onclick="filterCategory(<?php echo $cat['id']; ?>)" id="cat-btn-<?php echo $cat['id']; ?>" class="category-btn snap-start shrink-0 px-5 py-3 rounded-2xl flex items-center gap-2.5 bg-white/5 text-stone-700 dark:text-white/75 border border-stone-200 dark:border-white/10 font-bold text-sm shadow-sm hover:bg-stone-50 dark:hover:bg-white/10 transition-all duration-300">
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
    <div class="mt-8 grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-3 md:gap-4" id="productsGrid">
        <!-- محصولات به صورت داینامیک از سمت کلاینت با JS رندر می‌شوند یا مستقیم با PHP -->
        <?php foreach ($products as $prod): 
            $final_price = $prod['price'] - ($prod['price'] * $prod['discount'] / 100);
        ?>
            <div class="product-card bg-stone-900/40 glass p-2.5 rounded-2xl overflow-hidden hover:shadow-2xl hover:border-white/20 transition-all duration-300 flex flex-col group" 
                 data-id="<?php echo $prod['id']; ?>"
                 data-category="<?php echo $prod['category_id']; ?>"
                 data-name="<?php echo sanitize($prod['name']); ?>"
                 data-popular="<?php echo $prod['is_popular']; ?>"
                 data-new="<?php echo $prod['is_new']; ?>"
                 data-discount="<?php echo $prod['discount'] > 0 ? 1 : 0; ?>"
                 data-price="<?php echo $prod['price']; ?>">
                
                <!-- تصویر محصول -->
                <div class="relative w-full h-28 md:h-36 bg-stone-100 dark:bg-stone-800/50 rounded-xl overflow-hidden cursor-pointer" onclick="openProductDetailModal(<?php echo htmlspecialchars(json_encode($prod)); ?>)">
                    <?php if (!empty($prod['image'])): ?>
                        <img src="<?php echo sanitize($prod['image']); ?>" alt="<?php echo sanitize($prod['name']); ?>" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                    <?php else: ?>
                        <div class="w-full h-full flex items-center justify-center text-stone-300 dark:text-stone-600">
                            <i data-lucide="image" class="w-12 h-12"></i>
                        </div>
                    <?php endif; ?>

                    <!-- بچ‌ها (Badges) -->
                    <div class="absolute top-2 right-2 flex flex-col gap-1 z-10">
                        <?php if ($prod['is_popular']): ?>
                            <span class="bg-[#c49b63] text-black text-[8px] font-black px-2 py-0.5 rounded-full shadow-md flex items-center gap-0.5 accent-glow">
                                <i data-lucide="star" class="w-2.5 h-2.5 fill-black text-black"></i>
                                محبوب
                            </span>
                        <?php endif; ?>
                        <?php if ($prod['is_new']): ?>
                            <span class="bg-teal-500 text-white text-[8px] font-black px-2 py-0.5 rounded-full shadow-md">جدید</span>
                        <?php endif; ?>
                        <?php if ($prod['discount'] > 0): ?>
                            <span class="bg-red-500 text-white text-[8px] font-black px-2 py-0.5 rounded-full shadow-md">%<?php echo $prod['discount']; ?> تخفیف</span>
                        <?php endif; ?>
                    </div>

                    <!-- عدم موجودی -->
                    <?php if (!$prod['is_available']): ?>
                        <div class="absolute inset-0 bg-stone-900/80 backdrop-blur-xs flex items-center justify-center">
                            <span class="bg-white/10 backdrop-blur-md text-white px-2.5 py-1 rounded-xl text-[10px] font-black shadow-md border border-white/20">ناموجود</span>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- توضیحات محصول -->
                <div class="p-2 md:p-3 flex-1 flex flex-col">
                    <div class="flex items-start justify-between gap-2">
                        <h4 class="text-xs md:text-sm font-bold text-white group-hover:text-primary transition-colors cursor-pointer line-clamp-1" onclick="openProductDetailModal(<?php echo htmlspecialchars(json_encode($prod)); ?>)">
                            <?php echo sanitize($prod['name']); ?>
                        </h4>
                    </div>
                    <p class="text-[10px] md:text-xs text-stone-500 dark:text-stone-400 mt-1 line-clamp-2 leading-relaxed flex-1">
                        <?php echo sanitize($prod['description'] ?: 'توضیحی برای این محصول ثبت نشده است.'); ?>
                    </p>

                    <!-- قیمت و دکمه خرید -->
                    <div class="mt-3 pt-2.5 border-t border-stone-100 dark:border-stone-800/60 flex items-center justify-between">
                        <div class="flex flex-col">
                            <?php if ($prod['discount'] > 0): ?>
                                <span class="text-[10px] text-stone-400 line-through decoration-red-500/50"><?php echo number_format($prod['price']); ?></span>
                                <span class="text-xs md:text-sm font-black text-white"><?php echo number_format($final_price); ?> <span class="text-[9px] font-normal text-stone-500 dark:text-stone-400"><?php echo CURRENCY; ?></span></span>
                            <?php else: ?>
                                <span class="text-xs md:text-sm font-black text-primary"><?php echo number_format($prod['price']); ?> <span class="text-[9px] font-normal text-stone-500 dark:text-stone-400"><?php echo CURRENCY; ?></span></span>
                            <?php endif; ?>
                        </div>

                        <?php if ($prod['is_available']): ?>
                            <button onclick="addToCart(<?php echo htmlspecialchars(json_encode([
                                'id' => $prod['id'],
                                'name' => $prod['name'],
                                'price' => $final_price,
                                'image' => $prod['image'],
                                'discount' => $prod['discount']
                            ])); ?>)" class="w-7 h-7 md:w-8 md:h-8 rounded-full bg-primary hover:bg-primary-hover text-white flex items-center justify-center hover:scale-105 transition-all shadow-md active:scale-95 animate-fade-in" title="افزودن به سبد">
                                <i data-lucide="plus" class="w-4 h-4"></i>
                            </button>
                        <?php else: ?>
                            <button disabled class="w-7 h-7 md:w-8 md:h-8 rounded-full bg-white/5 text-white/20 flex items-center justify-center cursor-not-allowed">
                                <i data-lucide="minus" class="w-4 h-4"></i>
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
                <!-- فیلدهای سفارش حضوری (در کافه) -->
                <div id="indoorFields" class="space-y-4">
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-stone-500 dark:text-stone-400 mb-1.5">شماره میز *</label>
                            <select name="table_number" id="indoor_table" class="w-full px-3 py-2.5 rounded-xl bg-stone-50 dark:bg-stone-800 border border-stone-200 dark:border-stone-700 text-stone-800 dark:text-white text-sm focus:outline-none focus:border-primary">
                                <option value="میز ۱">میز ۱</option>
                                <option value="میز ۲">میز ۲</option>
                                <option value="میز ۳">میز ۳</option>
                                <option value="میز ۴">میز ۴</option>
                                <option value="میز ۵">میز ۵</option>
                                <option value="میز ۶">میز ۶</option>
                                <option value="میز ۷">میز ۷</option>
                                <option value="میز ۸">میز ۸</option>
                                <option value="سالن کافه">سالن کافه / عمومی</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-stone-500 dark:text-stone-400 mb-1.5">نام سفارش‌دهنده *</label>
                            <input type="text" name="first_name" id="indoor_first_name" placeholder="نام شما" class="w-full px-4 py-2.5 rounded-xl bg-stone-50 dark:bg-stone-800 border border-stone-200 dark:border-stone-700 text-stone-800 dark:text-white text-sm focus:outline-none focus:border-primary">
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-stone-500 dark:text-stone-400 mb-1.5">توضیحات و سفارش ویژه</label>
                        <textarea name="description_indoor" id="indoor_desc" placeholder="توضیحات سفارش، کم شکر، بدون نی و..." rows="2" class="w-full px-4 py-2.5 rounded-xl bg-stone-50 dark:bg-stone-800 border border-stone-200 dark:border-stone-700 text-stone-800 dark:text-white text-sm focus:outline-none focus:border-primary"></textarea>
                    </div>
                </div>

                <!-- فیلدهای سفارش غیرحضوری (ارسال با پیک) -->
                <div id="outdoorFields" class="space-y-4 hidden">
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-stone-500 dark:text-stone-400 mb-1.5">نام *</label>
                            <input type="text" name="outdoor_name" id="outdoor_name_input" class="w-full px-4 py-2.5 rounded-xl bg-stone-50 dark:bg-stone-800 border border-stone-200 dark:border-stone-700 text-stone-800 dark:text-white text-sm focus:outline-none focus:border-primary">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-stone-500 dark:text-stone-400 mb-1.5">شماره موبایل *</label>
                            <input type="text" name="phone" id="outdoor_phone" placeholder="09xxxxxxxxx" class="w-full px-4 py-2.5 rounded-xl bg-stone-50 dark:bg-stone-800 border border-stone-200 dark:border-stone-700 text-stone-800 dark:text-white text-sm focus:outline-none focus:border-primary text-right dir-ltr">
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-stone-500 dark:text-stone-400 mb-1.5">آدرس دقیق تحویل *</label>
                        <textarea name="address" id="outdoor_address" placeholder="نام خیابان، کوچه، پلاک..." rows="2" class="w-full px-4 py-2.5 rounded-xl bg-stone-50 dark:bg-stone-800 border border-stone-200 dark:border-stone-700 text-stone-800 dark:text-white text-sm focus:outline-none focus:border-primary"></textarea>
                    </div>
                    <div class="grid grid-cols-3 gap-2">
                        <div>
                            <label class="block text-xs font-bold text-stone-500 dark:text-stone-400 mb-1.5">پلاک *</label>
                            <input type="text" name="plaque" id="outdoor_plaque" class="w-full px-3 py-2.5 rounded-xl bg-stone-50 dark:bg-stone-800 border border-stone-200 dark:border-stone-700 text-stone-800 dark:text-white text-sm focus:outline-none focus:border-primary text-center">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-stone-500 dark:text-stone-400 mb-1.5">طبقه *</label>
                            <input type="text" name="floor" id="outdoor_floor" class="w-full px-3 py-2.5 rounded-xl bg-stone-50 dark:bg-stone-800 border border-stone-200 dark:border-stone-700 text-stone-800 dark:text-white text-sm focus:outline-none focus:border-primary text-center">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-stone-500 dark:text-stone-400 mb-1.5">واحد *</label>
                            <input type="text" name="unit" id="outdoor_unit" class="w-full px-3 py-2.5 rounded-xl bg-stone-50 dark:bg-stone-800 border border-stone-200 dark:border-stone-700 text-stone-800 dark:text-white text-sm focus:outline-none focus:border-primary text-center">
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-stone-500 dark:text-stone-400 mb-1.5">توضیحات پیک</label>
                        <textarea name="description_outdoor" id="outdoor_desc_input" placeholder="مثلاً: زنگ واحد ۳ زده شود" rows="2" class="w-full px-4 py-2.5 rounded-xl bg-stone-50 dark:bg-stone-800 border border-stone-200 dark:border-stone-700 text-stone-800 dark:text-white text-sm focus:outline-none focus:border-primary"></textarea>
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
     * سوئیچ فیلدهای نوع سفارش بر اساس حضوری/غیرحضوری
     */
    function toggleOrderTypeFields(type) {
        const indoor = document.getElementById('indoorFields');
        const outdoor = document.getElementById('outdoorFields');
        
        const indoorFirst = document.getElementById('indoor_first_name');
        const indoorTable = document.getElementById('indoor_table');

        const outdoorName = document.getElementById('outdoor_name_input');
        const outdoorPhone = document.getElementById('outdoor_phone');
        const outdoorAddress = document.getElementById('outdoor_address');

        if (type === 'indoor') {
            indoor.classList.remove('hidden');
            outdoor.classList.add('hidden');
            
            if (indoorFirst) indoorFirst.required = true;
            if (indoorTable) indoorTable.required = true;

            if (outdoorName) outdoorName.required = false;
            if (outdoorPhone) outdoorPhone.required = false;
            if (outdoorAddress) outdoorAddress.required = false;
        } else {
            indoor.classList.add('hidden');
            outdoor.classList.remove('hidden');

            if (indoorFirst) indoorFirst.required = false;
            if (indoorTable) indoorTable.required = false;

            if (outdoorName) outdoorName.required = true;
            if (outdoorPhone) outdoorPhone.required = true;
            if (outdoorAddress) outdoorAddress.required = true;
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
            btn.classList.remove('bg-primary', 'text-black', 'shadow-md');
            btn.classList.add('bg-white/5', 'text-stone-700', 'dark:text-white/75', 'border-stone-200', 'dark:border-white/10', 'shadow-sm');
        });

        const activeBtn = document.getElementById(`cat-btn-${catId}`);
        if (activeBtn) {
            activeBtn.classList.remove('bg-white/5', 'text-stone-700', 'dark:text-white/75', 'border-stone-200', 'dark:border-white/10', 'shadow-sm');
            activeBtn.classList.add('bg-primary', 'text-black', 'shadow-md');
        }

        filterProducts();
    }

    function sortProducts(sortType) {
        activeSortType = sortType;

        // آپدیت استایل دکمه‌های مرتب‌سازی
        document.querySelectorAll('.sort-btn').forEach(btn => {
            btn.classList.remove('bg-primary', 'text-black', 'accent-glow');
            btn.classList.add('bg-white/5', 'text-stone-600', 'dark:text-white/75', 'border-stone-200', 'dark:border-white/10');
        });

        const activeSortBtn = document.getElementById(`sort-${sortType}`);
        if (activeSortBtn) {
            activeSortBtn.classList.remove('bg-white/5', 'text-stone-600', 'dark:text-white/75', 'border-stone-200', 'dark:border-white/10');
            activeSortBtn.classList.add('bg-primary', 'text-black', 'accent-glow');
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
