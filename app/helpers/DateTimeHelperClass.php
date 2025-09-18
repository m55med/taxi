<?php

namespace App\Helpers {

    class DateTimeHelper
    {
        const CAIRO_TIMEZONE = 'Africa/Cairo';
        const UTC_TIMEZONE = 'UTC';

        public static function formatForDisplay($datetime, $format = 'Y-m-d H:i:s')
        {
            return \formatForDisplay($datetime, $format);
        }

        public static function getCurrentUTC($format = 'Y-m-d H:i:s')
        {
            return \getCurrentUTC($format);
        }

        public static function convertToUTC($localDatetime, $format = 'Y-m-d H:i:s')
        {
            return \convert_to_utc($localDatetime, $format);
        }

        public static function convertDataDatesForDisplay($data, $dateFields = ['created_at', 'updated_at'])
        {
            return \convert_dates_for_display($data, $dateFields);
        }

        public static function getCurrentMonthRange()
        {
            return \get_current_month_range();
        }

        public static function getDayRange($date)
        {
            try {
                $dateObj = new \DateTime($date, new \DateTimeZone(self::CAIRO_TIMEZONE));

                $startOfDay = clone $dateObj;
                $startOfDay->setTime(0, 0, 0);

                $endOfDay = clone $dateObj;
                $endOfDay->setTime(23, 59, 59);

                // تحويل إلى UTC للاستخدام في قاعدة البيانات
                $startOfDay->setTimezone(new \DateTimeZone(self::UTC_TIMEZONE));
                $endOfDay->setTimezone(new \DateTimeZone(self::UTC_TIMEZONE));

                return [
                    'start' => $startOfDay->format('Y-m-d H:i:s'),
                    'end' => $endOfDay->format('Y-m-d H:i:s')
                ];
            } catch (\Exception $e) {
                error_log("DateTimeHelper::getDayRange Error: " . $e->getMessage());
                return [
                    'start' => $date . ' 00:00:00',
                    'end' => $date . ' 23:59:59'
                ];
            }
        }

        public static function getUTCTimestampSQL()
        {
            return \get_utc_timestamp_sql();
        }
    }

}
