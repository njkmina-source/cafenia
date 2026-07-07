-- --------------------------------------------------------
-- پایگاه داده منوی دیجیتال کافه (کافه گالری)
-- توسعه داده شده برای آپلود آسان روی cPanel
-- --------------------------------------------------------

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- 1. جدول دسته‌بندی‌ها
CREATE TABLE IF NOT EXISTS `categories` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `icon` VARCHAR(50) DEFAULT 'coffee', -- آیکون لوکال یا کلاس لوساید/فونت‌آوسم
  `image` VARCHAR(255) DEFAULT NULL,
  `sort_order` INT DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;

-- 2. جدول محصولات
CREATE TABLE IF NOT EXISTS `products` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `category_id` INT NOT NULL,
  `name` VARCHAR(150) NOT NULL,
  `price` DECIMAL(12, 0) NOT NULL, -- قیمت به ریال یا تومان (پیش‌فرض تومان)
  `discount` INT DEFAULT 0, -- درصد تخفیف (0 تا 100)
  `description` TEXT DEFAULT NULL,
  `ingredients` TEXT DEFAULT NULL, -- مواد تشکیل دهنده
  `image` VARCHAR(255) DEFAULT NULL,
  `is_available` TINYINT(1) DEFAULT 1, -- وضعیت موجودی
  `is_popular` TINYINT(1) DEFAULT 0, -- محبوب‌ترین
  `is_new` TINYINT(1) DEFAULT 0, -- محصول جدید
  `is_visible` TINYINT(1) DEFAULT 1, -- امکان مخفی یا نمایش
  `sort_order` INT DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;

-- 3. جدول ادمین‌ها
CREATE TABLE IF NOT EXISTS `admins` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(50) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `fullname` VARCHAR(100) DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;

-- 4. جدول سفارشات
CREATE TABLE IF NOT EXISTS `orders` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `order_code` VARCHAR(20) NOT NULL UNIQUE, -- شماره سفارش یکتا مثل CAFE-1405-XXXX
  `customer_name` VARCHAR(100) NOT NULL,
  `customer_phone` VARCHAR(15) NOT NULL,
  `order_type` ENUM('indoor', 'outdoor') NOT NULL, -- حضوری (indoor) یا غیرحضوری (outdoor)
  `address` TEXT DEFAULT NULL, -- اطلاعات آدرس کامل برای غیرحضوری
  `plaque` VARCHAR(20) DEFAULT NULL,
  `floor` VARCHAR(20) DEFAULT NULL,
  `unit` VARCHAR(20) DEFAULT NULL,
  `description` TEXT DEFAULT NULL,
  `total_amount` DECIMAL(12,0) NOT NULL,
  `status` ENUM('registered', 'preparing', 'ready', 'sent', 'completed', 'cancelled') DEFAULT 'registered',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `created_jalali` VARCHAR(50) DEFAULT NULL -- تاریخ شمسی متنی ذخیره شده
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;

-- 5. جدول آیتم‌های سفارش
CREATE TABLE IF NOT EXISTS `order_items` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `order_id` INT NOT NULL,
  `product_id` INT NOT NULL,
  `product_name` VARCHAR(150) NOT NULL,
  `quantity` INT NOT NULL DEFAULT 1,
  `price` DECIMAL(12,0) NOT NULL, -- قیمت خرید نهایی
  FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;

-- 6. جدول تنظیمات سایت
CREATE TABLE IF NOT EXISTS `settings` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `key_name` VARCHAR(50) NOT NULL UNIQUE,
  `key_value` TEXT DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;

-- 7. جدول لاگ ادمین‌ها
CREATE TABLE IF NOT EXISTS `activity_logs` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `admin_id` INT DEFAULT NULL,
  `username` VARCHAR(50) DEFAULT NULL,
  `action` VARCHAR(255) NOT NULL,
  `ip_address` VARCHAR(45) DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;

-- --------------------------------------------------------
-- داده‌های پیش‌فرض
-- --------------------------------------------------------

-- رمز عبور پیش‌فرض: admin1234
-- هش رمز عبور تولید شده با: password_hash('admin1234', PASSWORD_DEFAULT)
INSERT INTO `admins` (`id`, `username`, `password`, `fullname`) VALUES
(1, 'admin', '$2y$10$95Xm61XkLdE7/8e.eY66/O5m8q9g3V1mN9l92U2Hsh9bC2T21mveG', 'مدیر کافه گالری');

-- داده‌های پیش‌فرض دسته‌بندی‌ها
INSERT INTO `categories` (`id`, `name`, `icon`, `sort_order`) VALUES
(1, 'قهوه گرم', 'coffee', 1),
(2, 'قهوه سرد', 'ice-cream', 2),
(3, 'دمنوش و چای', 'leaf', 3),
(4, 'کیک و دسر', 'cake', 4),
(5, 'نوشیدنی خنک', 'glass-water', 5),
(6, 'صبحانه و غذا', 'egg', 6);

-- داده‌های پیش‌فرض محصولات
INSERT INTO `products` (`id`, `category_id`, `name`, `price`, `discount`, `description`, `ingredients`, `is_available`, `is_popular`, `is_new`, `is_visible`, `sort_order`) VALUES
(1, 1, 'اسپرسو دوبل', 45000, 0, 'اسپرسو ۱۰۰٪ عربیکا با طعم عمیق و بادی قوی', 'دبل شات قهوه اسپرسو عربیکا', 1, 1, 0, 1, 1),
(2, 1, 'کاپوچینو', 55000, 10, 'اسپرسو به همراه شیر گرم و فوم غلیظ شیر', 'اسپرسو، شیر، فوم شیر، پودر کاکائو', 1, 0, 0, 1, 2),
(3, 1, 'لاته آرت', 58000, 0, 'ترکیب بی‌نظیر اسپرسو و شیر مخملی با طراحی‌های زیبا', 'اسپرسو، شیر، فوم ریز شیر', 1, 1, 1, 1, 3),
(4, 2, 'آیس لاته', 60000, 0, 'نسخه خنک قهوه لاته به همراه تکه‌های یخ', 'اسپرسو، شیر سرد، یخ', 1, 0, 0, 1, 1),
(5, 2, 'اسپرسو تانیک', 65000, 15, 'نوشیدنی گازدار خنک و انرژی‌بخش ترکیبی', 'اسپرسو، آب تونیک، لیمو، یخ', 1, 1, 1, 1, 2),
(6, 3, 'دمنوش آرامش', 48000, 0, 'ترکیبی معطر و آرامش‌بخش برای کاهش استرس روزانه', 'بابونه، بهارنارنج، اسطوخودوس، گل‌گاوزبان', 1, 0, 0, 1, 1),
(7, 4, 'کیک شکلاتی بی‌بی', 52000, 0, 'کیک شکلاتی کلاسیک فوق‌العاده مرطوب با سس شکلات داغ', 'آرد، پودر کاکائو غنی، خامه، سس شکلات', 1, 1, 0, 1, 1),
(8, 4, 'چیزکیک نیویورکی', 58000, 5, 'چیزکیک پخته غلیظ با کراست بیسکویت و سس تمشک وحشی', 'پنیر خامه‌ای، بیسکویت دایجستیو، خامه ترش، تمشک', 1, 0, 1, 1, 2),
(9, 6, 'املت گوجه‌فرنگی سنتی', 75000, 0, 'املت سنتی ایرانی با گوجه‌فرنگی تازه و نان داغ سنتی', 'گوجه‌فرنگی تازه، تخم‌مرغ رسمی، کره، ادویه مخصوص', 1, 1, 0, 1, 1),
(10, 6, 'اسنک گوشت و قارچ', 95000, 0, 'اسنک گریل شده پر پنیر و داغ به همراه سس مخصوص', 'گوشت چرخ‌کرده، قارچ، پنیر موزارلا، فلفل دلمه‌ای', 1, 0, 0, 1, 2);

-- داده‌های پیش‌فرض تنظیمات سایت
INSERT INTO `settings` (`key_name`, `key_value`) VALUES
('cafe_name', 'کافه گالری'),
('cafe_description', 'فضایی آرام و دلنشین همراه با بهترین طعم‌های قهوه تخصصی و دسرهای دست‌ساز'),
('cafe_phone', '021-88888888'),
('cafe_address', 'تهران، خیابان ولیعصر، نرسیده به میدان ونک، بن‌بست کافه، پلاک ۱۲'),
('working_hours', 'همه‌روزه از ساعت ۸:۰۰ صبح الی ۲۳:۳۰ شب'),
('primary_color', '#8B5A2B'), -- قهوه‌ای لوکس
('secondary_color', '#D2B48C'), -- کرم/نسکافه‌ای
('instagram_link', 'https://instagram.com/cafegallery'),
('telegram_link', 'https://t.me/cafegallery'),
('banner_url', ''),
('logo_url', '');

SET FOREIGN_KEY_CHECKS = 1;
