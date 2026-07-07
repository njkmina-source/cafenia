<?php
/**
 * فوتر استاندارد منوی دیجیتال کافه
 */
?>
<footer class="mt-auto bg-stone-100 dark:bg-stone-900 border-t border-stone-200 dark:border-stone-800 transition-colors duration-300 py-6">
    <div class="max-w-6xl mx-auto px-4 text-center">
        <p class="text-xs text-stone-500 dark:text-stone-400">
            تمامی حقوق مادی و معنوی متعلق به <?php echo sanitize($settings['cafe_name'] ?? 'کافه گالری'); ?> می‌باشد. © ۱۴۰۵
        </p>
        <p class="text-[10px] text-stone-400 dark:text-stone-500 mt-2 flex items-center justify-center gap-1">
            <span>توسعه یافته با</span>
            <i data-lucide="heart" class="w-3 h-3 text-red-500 fill-red-500"></i>
            <span>به صورت امن و مدرن برای هاست سی‌پنل</span>
        </p>
    </div>
</footer>

<!-- اسکریپت‌های سیستمی و تغییر تم -->
<script>
    // راه‌اندازی آیکون‌های زیبای لوساید در تمام صفحات
    document.addEventListener("DOMContentLoaded", function() {
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
    });

    // مدیریت تم تاریک و روشن (Dark Mode)
    const themeToggleBtn = document.getElementById('themeToggleBtn');
    
    // تابع تنظیم تم
    function setTheme(mode) {
        if (mode === 'dark') {
            document.documentElement.classList.add('dark');
            localStorage.setItem('theme', 'dark');
        } else {
            document.documentElement.classList.remove('dark');
            localStorage.setItem('theme', 'light');
        }
    }

    // بررسی تم ترجیحی کاربر هنگام بارگذاری اولیه صفحه
    const savedTheme = localStorage.getItem('theme');
    const systemPrefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;

    if (savedTheme === 'dark' || (!savedTheme && systemPrefersDark)) {
        setTheme('dark');
    } else {
        setTheme('light');
    }

    // شنود کلیک دکمه سوئیچر تم
    if (themeToggleBtn) {
        themeToggleBtn.addEventListener('click', () => {
            const isDarkNow = document.documentElement.classList.contains('dark');
            setTheme(isDarkNow ? 'light' : 'dark');
        });
    }
</script>
</body>
</html>
