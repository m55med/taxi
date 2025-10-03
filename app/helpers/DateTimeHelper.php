<?php

/**
 * DateTime Helper - مركز إدارة التوقيت الزمني
 *
 * هذا الملف يحتوي على دوال مركزية للتعامل مع التوقيت الزمني
 * - حفظ البيانات بالتوقيت العالمي الموحد (UTC)
 * - عرض البيانات بالتوقيت المحلي لمدينة القاهرة (EET)
 * - معالجة التواريخ للإحصائيات والتقارير
 */

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
function formatForDisplay($datetime, $format = 'Y-m-d H:i:s')
    {
        if (empty($datetime)) {
            return null;
        }

        try {
            error_log("DateTimeHelper::formatForDisplay - Input: $datetime, Format: $format");
        // Database timestamps should be stored in UTC, so treat them as UTC
        $dateObj = new \DateTime($datetime, new \DateTimeZone(UTC_TIMEZONE));
        $dateObj->setTimezone(new \DateTimeZone(CAIRO_TIMEZONE));
            $result = $dateObj->format($format);
        error_log("DateTimeHelper::formatForDisplay - UTC to Cairo, Result: $result");
            return $result;
    } catch (\Exception $e) {
            error_log("DateTimeHelper::formatForDisplay Error: " . $e->getMessage());
        return $datetime;
        }
    }

    /**
 * الحصول على التوقيت الحالي بصيغة UTC للحفظ في قاعدة البيانات
 * @param string $format تنسيق التاريخ والوقت المطلوب
 * @return string التوقيت الحالي بصيغة UTC
 */
function getCurrentUTC($format = 'Y-m-d H:i:s')
    {
        try {
        $now = new \DateTime('now', new \DateTimeZone(UTC_TIMEZONE));
            return $now->format($format);
    } catch (\Exception $e) {
            error_log("DateTimeHelper::getCurrentUTC Error: " . $e->getMessage());
        // استخدام gmdate() بدلاً من date() للحصول على UTC
        return gmdate($format);
    }
}

/**
 * تحويل تاريخ للعرض بالتوقيت المحلي
 */
function format_datetime_display($datetime, $format = 'Y-m-d H:i:s') {
    return formatForDisplay($datetime, $format);
}

/**
 * الحصول على الوقت الحالي بتوقيت UTC للحفظ
 */
function current_utc_timestamp() {
    return getCurrentUTC();
}

/**
 * تحويل تاريخ محلي إلى UTC
 */
function convert_to_utc($localDatetime, $format = 'Y-m-d H:i:s') {
    try {
        $localObj = new \DateTime($localDatetime, new \DateTimeZone(CAIRO_TIMEZONE));
        $localObj->setTimezone(new \DateTimeZone(UTC_TIMEZONE));
        return $localObj->format($format);
    } catch (\Exception $e) {
        error_log("convert_to_utc Error: " . $e->getMessage());
        return $localDatetime;
    }
}

/**
 * تحويل تاريخ للعرض بتنسيق 12 ساعة
 */
function format_datetime_display_12h($datetime, $format = 'Y-m-d h:i:s A') {
    return formatForDisplay($datetime, $format);
}

/**
 * تحويل تاريخ في مصفوفة للعرض بتنسيق 12 ساعة فقط (بدون تغيير التوقيت)
 */
function convert_dates_to_12h_format($data, $dateFields) {
    if (empty($data)) {
        return $data;
    }

    // Check if it's a single row or multiple rows
    $isMultiDimensional = is_array($data) && count($data) > 0 && is_array($data[array_key_first($data)]);

    if ($isMultiDimensional) {
        foreach ($data as &$row) {
            foreach ($dateFields as $field) {
                if (isset($row[$field]) && !empty($row[$field])) {
                    // تحويل من تنسيق 24 ساعة إلى 12 ساعة فقط
                    $dateTime = new \DateTime($row[$field]);
                    $row[$field] = $dateTime->format('Y-m-d h:i:s A');
                }
            }
        }
    } else {
        foreach ($dateFields as $field) {
            if (isset($data[$field]) && !empty($data[$field])) {
                // تحويل من تنسيق 24 ساعة إلى 12 ساعة فقط
                $dateTime = new \DateTime($data[$field]);
                $data[$field] = $dateTime->format('Y-m-d h:i:s A');
            }
        }
    }

    return $data;
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
    try {
        $now = new \DateTime('now', new \DateTimeZone(CAIRO_TIMEZONE));

        $startOfMonth = clone $now;
        $startOfMonth->modify('first day of this month')->setTime(0, 0, 0);

        $endOfMonth = clone $now;
        $endOfMonth->modify('last day of this month')->setTime(23, 59, 59);

        // تحويل إلى UTC للاستخدام في قاعدة البيانات
        $startOfMonth->setTimezone(new \DateTimeZone(UTC_TIMEZONE));
        $endOfMonth->setTimezone(new \DateTimeZone(UTC_TIMEZONE));

        return [
            'start' => $startOfMonth->format('Y-m-d H:i:s'),
            'end' => $endOfMonth->format('Y-m-d H:i:s')
        ];
    } catch (\Exception $e) {
        error_log("get_current_month_range Error: " . $e->getMessage());
        return [
            'start' => date('Y-m-01 00:00:00'),
            'end' => date('Y-m-t 23:59:59')
        ];
    }
}

/**
 * تحويل تواريخ البيانات للعرض
 */
function convert_dates_for_display($data, $dateFields = ['created_at', 'updated_at']) {
    if (empty($data)) {
        return $data;
    }

    // Check if it's a single row or multiple rows
    $isMultiDimensional = is_array($data) && count($data) > 0 && is_array($data[array_key_first($data)]);

    if ($isMultiDimensional) {
        foreach ($data as &$row) {
            foreach ($dateFields as $field) {
                if (isset($row[$field])) {
                    $row[$field] = formatForDisplay($row[$field]);
                }
            }
        }
    } else {
        foreach ($dateFields as $field) {
            if (isset($data[$field])) {
                $data[$field] = formatForDisplay($data[$field]);
            }
        }
    }

    return $data;
}

/**
 * الحصول على UTC timestamp SQL expression للاستخدام في الاستعلامات
 */
function get_utc_timestamp_sql() {
    try {
        $utcTime = getCurrentUTC();
        return "'" . $utcTime . "'";
    } catch (\Exception $e) {
        error_log("get_utc_timestamp_sql Error: " . $e->getMessage());
        // كبديل، استخدم CONVERT_TZ للتحويل من توقيت الخادم إلى UTC
        return "CONVERT_TZ(NOW(), @@session.time_zone, '+00:00')";
    }
}

// Class for backward compatibility - moved to separate namespace file
// Include the class file if needed
if (!class_exists('App\\Helpers\\DateTimeHelper')) {
    require_once __DIR__ . '/DateTimeHelperClass.php';
}