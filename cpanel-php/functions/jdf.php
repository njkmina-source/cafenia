<?php
/**
 * کتابخانه سبک و کارآمد تبدیل تاریخ میلادی به شمسی
 * مخصوص استفاده در پروژه‌های PHP بدون وابستگی‌های خارجی
 */

class JalaliDate {
    /**
     * تبدیل تاریخ میلادی به شمسی
     */
    public static function gregorian_to_jalali($gy, $gm, $gd) {
        $g_d_m = array(0, 31, 59, 90, 120, 151, 181, 212, 243, 273, 304, 335);
        $gy2 = ($gm > 2) ? ($gy + 1) : $gy;
        $days = 365 * $gy + (int)(($gy2 + 3) / 4) - (int)(($gy2 + 99) / 100) + (int)(($gy2 + 399) / 400) - 80 + $gd + $g_d_m[$gm - 1];
        $jy = 979 + 33 * (int)($days / 12053);
        $days %= 12053;
        $jy += 4 * (int)($days / 1461);
        $days %= 1461;
        if ($days >= 366) {
            $jy += (int)(($days - 1) / 365);
            $days = ($days - 1) % 365;
        }
        $jm = ($days < 186) ? 1 + (int)($days / 31) : 7 + (int)(($days - 186) / 30);
        $jd = 1 + (($days < 186) ? ($days % 31) : (($days - 186) % 30));
        return array($jy, $jm, $jd);
    }

    /**
     * دریافت تاریخ شمسی فعلی با فرمت درخواستی (مثال: 1405/04/15)
     */
    public static function now($format = 'Y/m/d H:i:s') {
        // تنظیم منطقه زمانی روی ایران
        date_default_timezone_set('Asia/Tehran');
        
        $timestamp = time();
        $g_date = date('Y-m-d', $timestamp);
        $g_parts = explode('-', $g_date);
        
        $j_parts = self::gregorian_to_jalali((int)$g_parts[0], (int)$g_parts[1], (int)$g_parts[2]);
        
        $jy = $j_parts[0];
        $jm = str_pad($j_parts[1], 2, '0', STR_PAD_LEFT);
        $jd = str_pad($j_parts[2], 2, '0', STR_PAD_LEFT);
        
        $time = date('H:i:s', $timestamp);
        
        $result = $format;
        $result = str_replace('Y', $jy, $result);
        $result = str_replace('m', $jm, $result);
        $result = str_replace('d', $jd, $result);
        $result = str_replace('H:i:s', $time, $result);
        
        return $result;
    }

    /**
     * تبدیل یک تاریخ میلادی یا برچسب زمان به شمسی
     */
    public static function from_timestamp($timestamp, $format = 'Y/m/d H:i:s') {
        if (!$timestamp) return '';
        
        $g_date = date('Y-m-d', $timestamp);
        $g_parts = explode('-', $g_date);
        
        $j_parts = self::gregorian_to_jalali((int)$g_parts[0], (int)$g_parts[1], (int)$g_parts[2]);
        
        $jy = $j_parts[0];
        $jm = str_pad($j_parts[1], 2, '0', STR_PAD_LEFT);
        $jd = str_pad($j_parts[2], 2, '0', STR_PAD_LEFT);
        
        $time = date('H:i:s', $timestamp);
        
        $result = $format;
        $result = str_replace('Y', $jy, $result);
        $result = str_replace('m', $jm, $result);
        $result = str_replace('d', $jd, $result);
        $result = str_replace('H:i:s', $time, $result);
        
        return $result;
    }
}
