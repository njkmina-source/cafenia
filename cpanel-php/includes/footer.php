<?php
/**
 * فوتر استاندارد منوی دیجیتال کافه
 */
?>
<footer class="mt-auto bg-[#131210] border-t border-white/10 py-6">
    <div class="max-w-6xl mx-auto px-4 text-center">
        <p class="text-xs text-stone-400">
            تمامی حقوق مادی و معنوی متعلق به <?php echo sanitize($settings['cafe_name'] ?? 'کافه گالری'); ?> می‌باشد. © ۱۴۰۵
        </p>
        <p class="text-[10px] text-stone-500 mt-2 flex items-center justify-center gap-1">
            <span>توسعه یافته با</span>
            <i data-lucide="heart" class="w-3 h-3 text-red-500 fill-red-500"></i>
            <span>به صورت امن و مدرن برای هاست سی‌پنل</span>
        </p>
    </div>
</footer>

<!-- اسکریپت‌های سیستمی -->
<script>
    // راه‌اندازی آیکون‌های زیبای لوساید در تمام صفحات
    document.addEventListener("DOMContentLoaded", function() {
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
        document.documentElement.classList.add('dark');
    });
</script>
</body>
</html>
