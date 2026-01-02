<?php

const CAIRO_TIMEZONE = 'Africa/Cairo';
const UTC_TIMEZONE = 'UTC';

function formatForDisplay($datetime, $format = 'Y-m-d H:i:s')
{
    try {
        $utcDateTime = new DateTime($datetime, new DateTimeZone(UTC_TIMEZONE));
        $utcDateTime->setTimezone(new DateTimeZone(CAIRO_TIMEZONE));
        return $utcDateTime->format($format);
    } catch (Exception $e) {
        error_log("formatForDisplay Error: " . $e->getMessage());
        return $datetime;
    }
}

function getCurrentUTC($format = 'Y-m-d H:i:s')
{
    try {
        $now = new DateTime('now', new DateTimeZone(UTC_TIMEZONE));
        return $now->format($format);
    } catch (Exception $e) {
        error_log("getCurrentUTC Error: " . $e->getMessage());
        return date($format);
    }
}

function convert_to_utc($localDatetime, $format = 'Y-m-d H:i:s')
{
    try {
        $localDateTime = new DateTime($localDatetime, new DateTimeZone(CAIRO_TIMEZONE));
        $localDateTime->setTimezone(new DateTimeZone(UTC_TIMEZONE));
        return $localDateTime->format($format);
    } catch (Exception $e) {
        error_log("convert_to_utc Error: " . $e->getMessage());
        return $localDatetime;
    }
}

function convert_dates_for_display($data, $dateFields = ['created_at', 'updated_at'])
{
    if (!is_array($data)) {
        return $data;
    }

    $result = $data;

    if (isset($data[0]) && is_array($data[0])) {
        // Array of arrays
        foreach ($result as &$row) {
            foreach ($dateFields as $field) {
                if (isset($row[$field])) {
                    $row[$field] = formatForDisplay($row[$field]);
                }
            }
        }
    } else {
        // Single array
        foreach ($dateFields as $field) {
            if (isset($result[$field])) {
                $result[$field] = formatForDisplay($result[$field]);
            }
        }
    }

    return $result;
}

function formatForDisplay12h($datetime, $format = 'Y-m-d h:i:s A')
{
    try {
        $utcDateTime = new DateTime($datetime, new DateTimeZone(UTC_TIMEZONE));
        $utcDateTime->setTimezone(new DateTimeZone(CAIRO_TIMEZONE));
        return $utcDateTime->format($format);
    } catch (Exception $e) {
        error_log("formatForDisplay12h Error: " . $e->getMessage());
        return $datetime;
    }
}

function convert_dates_for_display_12h($data, $dateFields = ['created_at', 'updated_at'])
{
    if (!is_array($data)) {
        return $data;
    }

    $result = $data;

    if (isset($data[0]) && is_array($data[0])) {
        // Array of arrays
        foreach ($result as &$row) {
            foreach ($dateFields as $field) {
                if (isset($row[$field])) {
                    $row[$field] = formatForDisplay12h($row[$field]);
                }
            }
        }
    } else {
        // Single array
        foreach ($dateFields as $field) {
            if (isset($result[$field])) {
                $result[$field] = formatForDisplay12h($result[$field]);
            }
        }
    }

    return $result;
}

function get_current_month_range()
{
    try {
        $cairoTZ = new DateTimeZone(CAIRO_TIMEZONE);
        $now = new DateTime('now', $cairoTZ);

        $startOfMonth = new DateTime($now->format('Y-m-01 00:00:00'), $cairoTZ);
        $endOfMonth = new DateTime($now->format('Y-m-t 23:59:59'), $cairoTZ);

        // Convert to UTC for database queries
        $startOfMonth->setTimezone(new DateTimeZone(UTC_TIMEZONE));
        $endOfMonth->setTimezone(new DateTimeZone(UTC_TIMEZONE));

        return [
            'start' => $startOfMonth->format('Y-m-d H:i:s'),
            'end' => $endOfMonth->format('Y-m-d H:i:s')
        ];
    } catch (Exception $e) {
        error_log("get_current_month_range Error: " . $e->getMessage());
        $year = date('Y');
        $month = date('m');
        return [
            'start' => "{$year}-{$month}-01 00:00:00",
            'end' => date('Y-m-t 23:59:59')
        ];
    }
}

function get_utc_timestamp_sql()
{
    return "UTC_TIMESTAMP()";
}

?>
