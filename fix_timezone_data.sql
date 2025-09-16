-- ========================================
-- إصلاح بيانات التوقيت القديمة
-- التاريخ: 2025-09-16
-- السبب: البيانات القديمة محفوظة بتوقيت خاطئ (ناقص 4 ساعات)
-- ========================================

-- تحذير: قم بعمل نسخة احتياطية من قاعدة البيانات قبل تنفيذ هذا الاستعلام!

-- إصلاح جدول التذاكر
UPDATE tickets
SET created_at = DATE_ADD(created_at, INTERVAL 4 HOUR)
WHERE created_at < '2025-09-17 00:00:00';

-- إصلاح جدول تفاصيل التذاكر
UPDATE ticket_details
SET created_at = DATE_ADD(created_at, INTERVAL 4 HOUR),
    updated_at = DATE_ADD(updated_at, INTERVAL 4 HOUR)
WHERE created_at < '2025-09-17 00:00:00';

-- إصلاح جدول الاستراحات
UPDATE breaks
SET start_time = DATE_ADD(start_time, INTERVAL 4 HOUR),
    end_time = DATE_ADD(end_time, INTERVAL 4 HOUR)
WHERE start_time < '2025-09-17 00:00:00';

-- إصلاح جدول مكالمات السائقين
UPDATE driver_calls
SET created_at = DATE_ADD(created_at, INTERVAL 4 HOUR),
    updated_at = DATE_ADD(updated_at, INTERVAL 4 HOUR)
WHERE created_at < '2025-09-17 00:00:00';

-- إصلاح جدول المراجعات
UPDATE reviews
SET reviewed_at = DATE_ADD(reviewed_at, INTERVAL 4 HOUR),
    created_at = DATE_ADD(created_at, INTERVAL 4 HOUR),
    updated_at = DATE_ADD(updated_at, INTERVAL 4 HOUR)
WHERE reviewed_at < '2025-09-17 00:00:00';

-- إصلاح جدول المكالمات الواردة
UPDATE incoming_calls
SET call_started_at = DATE_ADD(call_started_at, INTERVAL 4 HOUR),
    call_ended_at = DATE_ADD(call_ended_at, INTERVAL 4 HOUR),
    created_at = DATE_ADD(created_at, INTERVAL 4 HOUR),
    updated_at = DATE_ADD(updated_at, INTERVAL 4 HOUR)
WHERE call_started_at < '2025-09-17 00:00:00';

-- إصلاح جدول تعيينات السائقين
UPDATE driver_assignments
SET created_at = DATE_ADD(created_at, INTERVAL 4 HOUR),
    updated_at = DATE_ADD(updated_at, INTERVAL 4 HOUR)
WHERE created_at < '2025-09-17 00:00:00';

-- إصلاح جدول المناقشات
UPDATE discussions
SET created_at = DATE_ADD(created_at, INTERVAL 4 HOUR),
    updated_at = DATE_ADD(updated_at, INTERVAL 4 HOUR)
WHERE created_at < '2025-09-17 00:00:00';

-- إصلاح جدول ردود المناقشات
UPDATE discussion_replies
SET created_at = DATE_ADD(created_at, INTERVAL 4 HOUR),
    updated_at = DATE_ADD(updated_at, INTERVAL 4 HOUR)
WHERE created_at < '2025-09-17 00:00:00';

-- إصلاح جدول القسائم
UPDATE coupons
SET created_at = DATE_ADD(created_at, INTERVAL 4 HOUR),
    updated_at = DATE_ADD(updated_at, INTERVAL 4 HOUR),
    held_at = DATE_ADD(held_at, INTERVAL 4 HOUR),
    used_at = DATE_ADD(used_at, INTERVAL 4 HOUR)
WHERE created_at < '2025-09-17 00:00:00';

-- إصلاح جدول السجلات
UPDATE logs
SET created_at = DATE_ADD(created_at, INTERVAL 4 HOUR)
WHERE created_at < '2025-09-17 00:00:00';

-- إصلاح جدول الإشعارات
UPDATE notifications
SET created_at = DATE_ADD(created_at, INTERVAL 4 HOUR),
    updated_at = DATE_ADD(updated_at, INTERVAL 4 HOUR)
WHERE created_at < '2025-09-17 00:00:00';

-- إصلاح جدول إشعارات المستخدمين
UPDATE user_notifications
SET created_at = DATE_ADD(created_at, INTERVAL 4 HOUR),
    updated_at = DATE_ADD(updated_at, INTERVAL 4 HOUR),
    read_at = DATE_ADD(read_at, INTERVAL 4 HOUR)
WHERE created_at < '2025-09-17 00:00:00';

-- إصلاح جدول طلبات إعادة تعيين كلمة المرور
UPDATE password_resets
SET created_at = DATE_ADD(created_at, INTERVAL 4 HOUR),
    updated_at = DATE_ADD(updated_at, INTERVAL 4 HOUR),
    expires_at = DATE_ADD(expires_at, INTERVAL 4 HOUR)
WHERE created_at < '2025-09-17 00:00:00';

-- إصلاح جدول التقارير
UPDATE reports
SET created_at = DATE_ADD(created_at, INTERVAL 4 HOUR),
    updated_at = DATE_ADD(updated_at, INTERVAL 4 HOUR)
WHERE created_at < '2025-09-17 00:00:00';

-- إصلاح جدول الفرق
UPDATE teams
SET created_at = DATE_ADD(created_at, INTERVAL 4 HOUR),
    updated_at = DATE_ADD(updated_at, INTERVAL 4 HOUR)
WHERE created_at < '2025-09-17 00:00:00';

-- إصلاح جدول أعضاء الفرق
UPDATE team_members
SET joined_at = DATE_ADD(joined_at, INTERVAL 4 HOUR),
    created_at = DATE_ADD(created_at, INTERVAL 4 HOUR),
    updated_at = DATE_ADD(updated_at, INTERVAL 4 HOUR)
WHERE joined_at < '2025-09-17 00:00:00';

-- إصلاح جدول فئات التذاكر
UPDATE ticket_categories
SET created_at = DATE_ADD(created_at, INTERVAL 4 HOUR),
    updated_at = DATE_ADD(updated_at, INTERVAL 4 HOUR)
WHERE created_at < '2025-09-17 00:00:00';

-- إصلاح جدول فئات التذاكر الفرعية
UPDATE ticket_subcategories
SET created_at = DATE_ADD(created_at, INTERVAL 4 HOUR),
    updated_at = DATE_ADD(updated_at, INTERVAL 4 HOUR)
WHERE created_at < '2025-09-17 00:00:00';

-- إصلاح جدول رموز التذاكر
UPDATE ticket_codes
SET created_at = DATE_ADD(created_at, INTERVAL 4 HOUR),
    updated_at = DATE_ADD(updated_at, INTERVAL 4 HOUR)
WHERE created_at < '2025-09-17 00:00:00';

-- إصلاح جدول المنصات
UPDATE platforms
SET created_at = DATE_ADD(created_at, INTERVAL 4 HOUR),
    updated_at = DATE_ADD(updated_at, INTERVAL 4 HOUR)
WHERE created_at < '2025-09-17 00:00:00';

-- إصلاح جدول الدول
UPDATE countries
SET created_at = DATE_ADD(created_at, INTERVAL 4 HOUR),
    updated_at = DATE_ADD(updated_at, INTERVAL 4 HOUR)
WHERE created_at < '2025-09-17 00:00:00';

-- إصلاح جدول أنواع السيارات
UPDATE car_types
SET created_at = DATE_ADD(created_at, INTERVAL 4 HOUR),
    updated_at = DATE_ADD(updated_at, INTERVAL 4 HOUR)
WHERE created_at < '2025-09-17 00:00:00';

-- إصلاح جدول السائقين
UPDATE drivers
SET created_at = DATE_ADD(created_at, INTERVAL 4 HOUR),
    updated_at = DATE_ADD(updated_at, INTERVAL 4 HOUR),
    registered_at = DATE_ADD(registered_at, INTERVAL 4 HOUR)
WHERE created_at < '2025-09-17 00:00:00';

-- إصلاح جدول المستخدمين
UPDATE users
SET created_at = DATE_ADD(created_at, INTERVAL 4 HOUR),
    updated_at = DATE_ADD(updated_at, INTERVAL 4 HOUR)
WHERE created_at < '2025-09-17 00:00:00';

-- إصلاح جدول الوكلاء
UPDATE agents
SET created_at = DATE_ADD(created_at, INTERVAL 4 HOUR),
    updated_at = DATE_ADD(updated_at, INTERVAL 4 HOUR)
WHERE created_at < '2025-09-17 00:00:00';

-- إصلاح جدول ساعات العمل
UPDATE working_hours
SET created_at = DATE_ADD(created_at, INTERVAL 4 HOUR),
    updated_at = DATE_ADD(updated_at, INTERVAL 4 HOUR)
WHERE created_at < '2025-09-17 00:00:00';

-- إصلاح جدول الصلاحيات
UPDATE permissions
SET created_at = DATE_ADD(created_at, INTERVAL 4 HOUR),
    updated_at = DATE_ADD(updated_at, INTERVAL 4 HOUR)
WHERE created_at < '2025-09-17 00:00:00';

-- إصلاح جدول الصلاحيات المستخدمة
UPDATE user_permissions
SET created_at = DATE_ADD(created_at, INTERVAL 4 HOUR)
WHERE created_at < '2025-09-17 00:00:00';

-- إصلاح جدول الصلاحيات الدورية
UPDATE role_permissions
SET created_at = DATE_ADD(created_at, INTERVAL 4 HOUR)
WHERE created_at < '2025-09-17 00:00:00';

-- إصلاح جدول روابط التليجرام
UPDATE telegram_links
SET created_at = DATE_ADD(created_at, INTERVAL 4 HOUR),
    updated_at = DATE_ADD(updated_at, INTERVAL 4 HOUR)
WHERE created_at < '2025-09-17 00:00:00';

-- إصلاح جدول التقارير الشهرية للمستخدمين
UPDATE user_monthly_bonus
SET granted_at = DATE_ADD(granted_at, INTERVAL 4 HOUR),
    created_at = DATE_ADD(created_at, INTERVAL 4 HOUR),
    updated_at = DATE_ADD(updated_at, INTERVAL 4 HOUR)
WHERE granted_at < '2025-09-17 00:00:00';

-- إصلاح جدول إعدادات المكافآت
UPDATE bonus_settings
SET updated_at = DATE_ADD(updated_at, INTERVAL 4 HOUR),
    created_at = DATE_ADD(created_at, INTERVAL 4 HOUR)
WHERE created_at < '2025-09-17 00:00:00';

-- إصلاح جدول قاعدة المعرفة
UPDATE knowledge_base
SET created_at = DATE_ADD(created_at, INTERVAL 4 HOUR),
    updated_at = DATE_ADD(updated_at, INTERVAL 4 HOUR)
WHERE created_at < '2025-09-17 00:00:00';

-- إصلاح جدول مجلدات قاعدة المعرفة
UPDATE knowledge_base_folders
SET created_at = DATE_ADD(created_at, INTERVAL 4 HOUR),
    updated_at = DATE_ADD(updated_at, INTERVAL 4 HOUR)
WHERE created_at < '2025-09-17 00:00:00';

-- إصلاح جدول أنواع الوثائق
UPDATE document_types
SET created_at = DATE_ADD(created_at, INTERVAL 4 HOUR),
    updated_at = DATE_ADD(updated_at, INTERVAL 4 HOUR)
WHERE created_at < '2025-09-17 00:00:00';

-- إصلاح جدول وثائق السائقين المطلوبة
UPDATE driver_documents_required
SET created_at = DATE_ADD(created_at, INTERVAL 4 HOUR),
    updated_at = DATE_ADD(updated_at, INTERVAL 4 HOUR)
WHERE created_at < '2025-09-17 00:00:00';

-- إصلاح جدول تعيينات التذاكر VIP
UPDATE ticket_vip_assignments
SET created_at = DATE_ADD(created_at, INTERVAL 4 HOUR),
    updated_at = DATE_ADD(updated_at, INTERVAL 4 HOUR)
WHERE created_at < '2025-09-17 00:00:00';

-- إصلاح جدول زيارات الإحالة
UPDATE referral_visits
SET visit_recorded_at = DATE_ADD(visit_recorded_at, INTERVAL 4 HOUR),
    registration_attempted_at = DATE_ADD(registration_attempted_at, INTERVAL 4 HOUR),
    created_at = DATE_ADD(created_at, INTERVAL 4 HOUR),
    updated_at = DATE_ADD(updated_at, INTERVAL 4 HOUR)
WHERE visit_recorded_at < '2025-09-17 00:00:00';

-- إصلاح جدول تعليق السائقين
UPDATE driver_snoozes
SET snoozed_until = DATE_ADD(snoozed_until, INTERVAL 4 HOUR),
    created_at = DATE_ADD(created_at, INTERVAL 4 HOUR),
    updated_at = DATE_ADD(updated_at, INTERVAL 4 HOUR)
WHERE snoozed_until < '2025-09-17 00:00:00';

-- إصلاح جدول المندوبين
UPDATE delegations
SET applicable_month = applicable_month,
    applicable_year = applicable_year,
    created_at = DATE_ADD(created_at, INTERVAL 4 HOUR),
    updated_at = DATE_ADD(updated_at, INTERVAL 4 HOUR)
WHERE created_at < '2025-09-17 00:00:00';

-- إصلاح جدول تقييمات الموظفين
UPDATE employee_evaluations
SET applicable_month = applicable_month,
    applicable_year = applicable_year,
    created_at = DATE_ADD(created_at, INTERVAL 4 HOUR),
    updated_at = DATE_ADD(updated_at, INTERVAL 4 HOUR)
WHERE created_at < '2025-09-17 00:00:00';

-- إصلاح جدول المؤسسات
UPDATE establishments
SET created_at = DATE_ADD(created_at, INTERVAL 4 HOUR),
    updated_at = DATE_ADD(updated_at, INTERVAL 4 HOUR)
WHERE created_at < '2025-09-17 00:00:00';

-- إصلاح جدول سجلات تعديل التذاكر
UPDATE ticket_edit_logs
SET created_at = DATE_ADD(created_at, INTERVAL 4 HOUR),
    updated_at = DATE_ADD(updated_at, INTERVAL 4 HOUR)
WHERE created_at < '2025-09-17 00:00:00';

-- إصلاح جدول حالة قراءة المناقشات
UPDATE user_discussion_read_status
SET last_read_at = DATE_ADD(last_read_at, INTERVAL 4 HOUR),
    created_at = DATE_ADD(created_at, INTERVAL 4 HOUR),
    updated_at = DATE_ADD(updated_at, INTERVAL 4 HOUR)
WHERE last_read_at < '2025-09-17 00:00:00';

-- إصلاح جدول المطاعم
UPDATE restaurants
SET created_at = DATE_ADD(created_at, INTERVAL 4 HOUR),
    updated_at = DATE_ADD(updated_at, INTERVAL 4 HOUR)
WHERE created_at < '2025-09-17 00:00:00';

-- إصلاح جدول زيارات إحالة المطاعم
UPDATE restaurant_referral_visits
SET visit_recorded_at = DATE_ADD(visit_recorded_at, INTERVAL 4 HOUR),
    created_at = DATE_ADD(created_at, INTERVAL 4 HOUR),
    updated_at = DATE_ADD(updated_at, INTERVAL 4 HOUR)
WHERE visit_recorded_at < '2025-09-17 00:00:00';

-- ========================================
-- نهاية الإصلاح
-- ========================================

-- التحقق من الإصلاح
SELECT 'تم إصلاح البيانات بنجاح' as status;
