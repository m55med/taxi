<?php

/**
 * DateTime Helper - مركز إدارة التوقيت الزمني
 *
 * هذا الملف يحتوي على دوال مركزية للتعامل مع التوقيت الزمني
 * - حفظ البيانات بالتوقيت العالمي الموحد (UTC)
 * - عرض البيانات بالتوقيت المحلي لمدينة القاهرة (EET)
 * - معالجة التواريخ للإحصائيات والتقارير
 */

namespace App\Helpers {

use DateTime;
use DateTimeZone;
use Exception;

class DateTimeHelper
{
    // التوقيت المحلي لمدينة القاهرة (Eastern European Time)
    const CAIRO_TIMEZONE = 'Africa/Cairo';

    // التوقيت العالمي الموحد
    const UTC_TIMEZONE = 'UTC';

    /**
     * تحويل التاريخ والوقت للعرض بتوقيت القاهرة
     * @param string $datetime التاريخ والوقت المحفوظ في قاعدة البيانات (UTC)
     * @param string $format تنسيق العرض المطلوب
     * @return string التاريخ والوقت بتوقيت القاهرة
     */
    public static function formatForDisplay($datetime, $format = 'Y-m-d H:i:s')
    {
        if (empty($datetime)) {
            return null;
        }

        try {
            error_log("DateTimeHelper::formatForDisplay - Input: $datetime, Format: $format");
            // Database timestamps should be stored in UTC, so treat them as UTC
            $dateObj = new DateTime($datetime, new DateTimeZone(self::UTC_TIMEZONE));
            $dateObj->setTimezone(new DateTimeZone(self::CAIRO_TIMEZONE));
            $result = $dateObj->format($format);
            error_log("DateTimeHelper::formatForDisplay - UTC to Cairo, Result: $result");
            return $result;
        } catch (Exception $e) {
            error_log("DateTimeHelper::formatForDisplay Error: " . $e->getMessage());
            return $datetime;
        }
    }

    /**
     * تحويل التاريخ والوقت للعرض التفصيلي بتوقيت القاهرة
     * @param string $datetime التاريخ والوقت المحفوظ في قاعدة البيانات (UTC)
     * @return string التاريخ والوقت بتوقيت القاهرة مع معلومات إضافية
     */
    public static function formatDetailedForDisplay($datetime)
    {
        if (empty($datetime)) {
            return null;
        }

        try {
            // Database timestamps should be stored in UTC, so treat them as UTC
            $cairo = new DateTime($datetime, new DateTimeZone(self::UTC_TIMEZONE));
            $cairo->setTimezone(new DateTimeZone(self::CAIRO_TIMEZONE));
            
            $now = new DateTime('now', new DateTimeZone(self::CAIRO_TIMEZONE));
            $diff = $now->diff($cairo);
            
            $cairoFormatted = $cairo->format('Y-m-d h:i:s A');
            
            if ($diff->days == 0) {
                return $cairoFormatted . ' (اليوم)';
            } elseif ($diff->days == 1) {
                return $cairoFormatted . ' (أمس)';
            } elseif ($diff->days <= 7) {
                return $cairoFormatted . ' (منذ ' . $diff->days . ' أيام)';
            } else {
                return $cairoFormatted;
            }
        } catch (Exception $e) {
            error_log("DateTimeHelper::formatDetailedForDisplay Error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * الحصول على التوقيت الحالي بصيغة UTC للحفظ في قاعدة البيانات
     * @param string $format تنسيق التاريخ والوقت المطلوب
     * @return string التوقيت الحالي بصيغة UTC
     */
    public static function getCurrentUTC($format = 'Y-m-d H:i:s')
    {
        try {
            $now = new DateTime('now', new DateTimeZone(self::UTC_TIMEZONE));
            return $now->format($format);
        } catch (Exception $e) {
            error_log("DateTimeHelper::getCurrentUTC Error: " . $e->getMessage());
            // استخدام gmdate() بدلاً من date() للحصول على UTC
            return gmdate($format);
        }
    }

    /**
     * الحصول على التوقيت الحالي بتوقيت القاهرة
     * @param string $format تنسيق التاريخ والوقت المطلوب
     * @return string التوقيت الحالي بتوقيت القاهرة
     */
    public static function getCurrentLocal($format = 'Y-m-d H:i:s')
    {
        try {
            $now = new DateTime('now', new DateTimeZone(self::CAIRO_TIMEZONE));
            return $now->format($format);
        } catch (Exception $e) {
            error_log("DateTimeHelper::getCurrentLocal Error: " . $e->getMessage());
            return date($format);
        }
    }

    /**
     * تحويل تاريخ من التوقيت المحلي إلى UTC للحفظ
     * @param string $localDatetime التاريخ والوقت بالتوقيت المحلي
     * @param string $format تنسيق العرض المطلوب
     * @return string التاريخ والوقت بصيغة UTC
     */
    public static function convertToUTC($localDatetime, $format = 'Y-m-d H:i:s')
    {
        try {
            $localObj = new DateTime($localDatetime, new DateTimeZone(self::CAIRO_TIMEZONE));
            $localObj->setTimezone(new DateTimeZone(self::UTC_TIMEZONE));
            return $localObj->format($format);
        } catch (Exception $e) {
            error_log("DateTimeHelper::convertToUTC Error: " . $e->getMessage());
            return $localDatetime;
        }
    }

    /**
     * الحصول على UTC timestamp SQL expression للاستخدام في الاستعلامات
     * @return string SQL expression للحصول على UTC timestamp
     */
    public static function getUTCTimestampSQL()
    {
        try {
            $utcTime = self::getCurrentUTC();
            return "'" . $utcTime . "'";
        } catch (Exception $e) {
            error_log("DateTimeHelper::getUTCTimestampSQL Error: " . $e->getMessage());
            // كبديل، استخدم CONVERT_TZ للتحويل من توقيت الخادم إلى UTC
            return "CONVERT_TZ(NOW(), @@session.time_zone, '+00:00')";
        }
    }

    /**
     * الحصول على الوقت النسبي (منذ كم من الوقت)
     * @param string $datetime التاريخ والوقت المحفوظ في قاعدة البيانات (UTC)
     * @return string الوقت النسبي بالعربية
     */
    public static function getRelativeTime($datetime)
    {
        if (empty($datetime)) {
            return null;
        }

        try {
            $dateObj = new DateTime($datetime, new DateTimeZone(self::UTC_TIMEZONE));
            $dateObj->setTimezone(new DateTimeZone(self::CAIRO_TIMEZONE));
            
            $now = new DateTime('now', new DateTimeZone(self::CAIRO_TIMEZONE));
            $diff = $now->diff($dateObj);
            
            if ($diff->y > 0) {
                return 'منذ ' . $diff->y . ' سنة' . ($diff->y > 1 ? '' : '');
            } elseif ($diff->m > 0) {
                return 'منذ ' . $diff->m . ' شهر' . ($diff->m > 1 ? '' : '');
            } elseif ($diff->d > 0) {
                return 'منذ ' . $diff->d . ' يوم' . ($diff->d > 1 ? '' : '');
            } elseif ($diff->h > 0) {
                return 'منذ ' . $diff->h . ' ساعة' . ($diff->h > 1 ? '' : '');
            } elseif ($diff->i > 0) {
                return 'منذ ' . $diff->i . ' دقيقة' . ($diff->i > 1 ? '' : '');
            } else {
                return 'الآن';
            }
        } catch (Exception $e) {
            error_log("DateTimeHelper::getRelativeTime Error: " . $e->getMessage());
            return $datetime;
        }
    }

    /**
     * تحويل مصفوفة من البيانات لتحويل التواريخ فيها للعرض
     * @param array $data البيانات التي تحتوي على تواريخ
     * @param array $dateFields أسماء الحقول التي تحتوي على تواريخ
     * @return array البيانات مع التواريخ محولة للعرض
     */
    public static function convertDataDatesForDisplay($data, $dateFields = ['created_at', 'updated_at'])
    {
        if (empty($data)) {
            return $data;
        }

        // Check if it's a single row or multiple rows
        $isMultiDimensional = is_array($data) && count($data) > 0 && is_array($data[array_key_first($data)]);

        if ($isMultiDimensional) {
            foreach ($data as &$row) {
                foreach ($dateFields as $field) {
                    if (isset($row[$field])) {
                        $row[$field] = self::formatForDisplay($row[$field]);
                    }
                }
            }
        } else {
            foreach ($dateFields as $field) {
                if (isset($data[$field])) {
                    $data[$field] = self::formatForDisplay($data[$field]);
                }
            }
        }

        return $data;
    }

    /**
     * الحصول على نطاق الشهر الحالي (بداية ونهاية الشهر)
     * @return array مصفوفة تحتوي على بداية ونهاية الشهر بصيغة UTC
     */
    public static function getCurrentMonthRange()
    {
        try {
            $now = new DateTime('now', new DateTimeZone(self::CAIRO_TIMEZONE));
            
            $startOfMonth = clone $now;
            $startOfMonth->modify('first day of this month')->setTime(0, 0, 0);
            
            $endOfMonth = clone $now;
            $endOfMonth->modify('last day of this month')->setTime(23, 59, 59);
            
            // تحويل إلى UTC للاستخدام في قاعدة البيانات
            $startOfMonth->setTimezone(new DateTimeZone(self::UTC_TIMEZONE));
            $endOfMonth->setTimezone(new DateTimeZone(self::UTC_TIMEZONE));
            
            return [
                'start' => $startOfMonth->format('Y-m-d H:i:s'),
                'end' => $endOfMonth->format('Y-m-d H:i:s')
            ];
        } catch (Exception $e) {
            error_log("DateTimeHelper::getCurrentMonthRange Error: " . $e->getMessage());
            return [
                'start' => date('Y-m-01 00:00:00'),
                'end' => date('Y-m-t 23:59:59')
            ];
        }
    }

    /**
     * الحصول على نطاق يوم محدد (بداية ونهاية اليوم)
     * @param string $date التاريخ المطلوب الحصول على نطاقه
     * @return array مصفوفة تحتوي على بداية ونهاية اليوم بصيغة UTC
     */
    public static function getDayRange($date)
    {
        try {
            $dateObj = new DateTime($date, new DateTimeZone(self::CAIRO_TIMEZONE));
            
            $startOfDay = clone $dateObj;
            $startOfDay->setTime(0, 0, 0);
            
            $endOfDay = clone $dateObj;
            $endOfDay->setTime(23, 59, 59);
            
            // تحويل إلى UTC للاستخدام في قاعدة البيانات
            $startOfDay->setTimezone(new DateTimeZone(self::UTC_TIMEZONE));
            $endOfDay->setTimezone(new DateTimeZone(self::UTC_TIMEZONE));
            
            return [
                'start' => $startOfDay->format('Y-m-d H:i:s'),
                'end' => $endOfDay->format('Y-m-d H:i:s')
            ];
        } catch (Exception $e) {
            error_log("DateTimeHelper::getDayRange Error: " . $e->getMessage());
            return [
                'start' => $date . ' 00:00:00',
                'end' => $date . ' 23:59:59'
            ];
        }
    }
}

}

// الدوال العامة خارج namespace
namespace {

/**
 * تحويل تاريخ للعرض بالتوقيت المحلي
 */
function format_datetime_display($datetime, $format = 'Y-m-d H:i:s') {
    return \App\Helpers\DateTimeHelper::formatForDisplay($datetime, $format);
}

/**
 * الحصول على الوقت الحالي بتوقيت UTC للحفظ
 */
function current_utc_timestamp() {
    return \App\Helpers\DateTimeHelper::getCurrentUTC();
}

/**
 * تحويل تاريخ محلي إلى UTC
 */
function convert_to_utc($localDatetime, $format = 'Y-m-d H:i:s') {
    return \App\Helpers\DateTimeHelper::convertToUTC($localDatetime, $format);
}

/**
 * تحويل تاريخ للعرض بتنسيق 12 ساعة
 */
function format_datetime_display_12h($datetime, $format = 'Y-m-d h:i:s A') {
    return \App\Helpers\DateTimeHelper::formatForDisplay($datetime, $format);
}

/**
 * تحويل تاريخ في مصفوفة للعرض بتنسيق 12 ساعة
 */
function convert_dates_for_display_12h($data, $dateFields) {
    if (empty($data)) {
        return $data;
    }

    // DEBUG: Log the conversion process
    error_log("convert_dates_for_display_12h - Starting conversion for fields: " . implode(', ', $dateFields));

    // Check if it's a single row or multiple rows
    $isMultiDimensional = count($data) > 0 && is_array($data[array_key_first($data)]);

    error_log("convert_dates_for_display_12h - Is multi-dimensional: " . ($isMultiDimensional ? 'YES' : 'NO'));

    if ($isMultiDimensional) {
        foreach ($data as &$row) {
            foreach ($dateFields as $field) {
                if (isset($row[$field])) {
                    error_log("convert_dates_for_display_12h - Converting field '$field' from: " . $row[$field]);
                    $row[$field] = format_datetime_display_12h($row[$field]);
                    error_log("convert_dates_for_display_12h - Converted field '$field' to: " . $row[$field]);
                }
            }
        }
    } else {
        foreach ($dateFields as $field) {
            if (isset($data[$field])) {
                error_log("convert_dates_for_display_12h - Converting field '$field' from: " . $data[$field]);
                $data[$field] = format_datetime_display_12h($data[$field]);
                error_log("convert_dates_for_display_12h - Converted field '$field' to: " . $data[$field]);
            }
        }
    }

    return $data;
}

/**
 * الحصول على نطاق الشهر الحالي
 */
function get_current_month_range() {
    return \App\Helpers\DateTimeHelper::getCurrentMonthRange();
}

/**
 * تحويل تواريخ البيانات للعرض
 */
function convert_dates_for_display($data, $dateFields = ['created_at', 'updated_at']) {
    return \App\Helpers\DateTimeHelper::convertDataDatesForDisplay($data, $dateFields);
}

/**
 * الحصول على UTC timestamp SQL expression للاستخدام في الاستعلامات
 */
function get_utc_timestamp_sql() {
    return \App\Helpers\DateTimeHelper::getUTCTimestampSQL();
}

}