<?php

namespace App\Models\Break;

class BreakStorage
{
    private $filePath;

    public function __construct()
    {
        $this->filePath = APPROOT . '/storage/temp/active_breaks.json';
        $this->ensureFileExists();
    }

    /**
     * التأكد من وجود ملف JSON
     */
    private function ensureFileExists()
    {
        if (!file_exists($this->filePath)) {
            file_put_contents($this->filePath, json_encode([]));
        }
    }

    /**
     * إضافة بريك نشط إلى ملف JSON
     */
    public function addActiveBreak($breakData)
    {
        $breaks = $this->getAllActiveBreaks();
        $breaks[] = [
            'id' => $breakData->id,
            'user_id' => $breakData->user_id,
            'user_name' => $breakData->user_name ?? '',
            'team_name' => $breakData->team_name ?? '',
            'start_time' => $breakData->start_time,
            'timestamp' => time()
        ];

        return $this->saveBreaks($breaks);
    }

    /**
     * حذف بريك نشط من ملف JSON
     */
    public function removeActiveBreak($breakId)
    {
        $breaks = $this->getAllActiveBreaks();
        $breaks = array_filter($breaks, function($break) use ($breakId) {
            return $break['id'] != $breakId;
        });

        return $this->saveBreaks(array_values($breaks));
    }

    /**
     * الحصول على جميع البريك النشطة
     */
    public function getAllActiveBreaks()
    {
        $content = file_get_contents($this->filePath);
        $breaks = json_decode($content, true);

        if (!is_array($breaks)) {
            return [];
        }

        // إزالة البريك القديمة جداً (أكثر من 24 ساعة)
        $now = time();
        $breaks = array_filter($breaks, function($break) use ($now) {
            return ($now - $break['timestamp']) < 86400; // 24 ساعة
        });

        $this->saveBreaks(array_values($breaks));
        return $breaks;
    }

    /**
     * الحصول على عدد البريك النشطة
     */
    public function getActiveBreaksCount()
    {
        return count($this->getAllActiveBreaks());
    }

    /**
     * الحصول على البريك النشطة مع تفاصيل الوقت
     */
    public function getActiveBreaksWithTime()
    {
        $breaks = $this->getAllActiveBreaks();
        $now = time();

        foreach ($breaks as &$break) {
            $elapsed = $now - strtotime($break['start_time']);
            $break['minutes_elapsed'] = floor($elapsed / 60);
            $break['is_long_break'] = $break['minutes_elapsed'] >= 30;
        }

        return $breaks;
    }

    /**
     * حفظ البيانات في ملف JSON
     */
    private function saveBreaks($breaks)
    {
        $json = json_encode($breaks, JSON_PRETTY_PRINT);
        return file_put_contents($this->filePath, $json) !== false;
    }

    /**
     * تنظيف الملف (إزالة جميع البريك)
     */
    public function clearAll()
    {
        return $this->saveBreaks([]);
    }
}
