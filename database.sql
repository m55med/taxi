CREATE TABLE roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE
);


INSERT INTO roles (name) VALUES
('admin'),
('employee'),
('agent'),
('marketer'),
('quality_manager'),
('developer'),
('Team_leader');


CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    is_online TINYINT(1) DEFAULT 0, -- 0 = offline, 1 = online
    status ENUM('pending', 'active', 'banned') DEFAULT 'pending',
    role_id INT NOT NULL DEFAULT 3, -- default role is 'agent'
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE RESTRICT ON UPDATE CASCADE
);

-- Description: Overhauls the permission system from role-based to user-based.

-- Step 1: Create a new `permissions` table to store all available permission keys.
-- These keys will typically be controller paths.
CREATE TABLE `permissions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `permission_key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `permission_key` (`permission_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- Step 2: Create a new `user_permissions` table to link users directly to permissions.
-- This is the core of the new user-based system.
CREATE TABLE `user_permissions` (
  `user_id` int(11) NOT NULL,
  `permission_id` int(11) NOT NULL,
  PRIMARY KEY (`user_id`,`permission_id`),
  KEY `permission_id` (`permission_id`),
  CONSTRAINT `user_permissions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `user_permissions_ibfk_2` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


--جدول لربط بيانات التي
CREATE TABLE telegram_links (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    telegram_user_id BIGINT NOT NULL,
    telegram_chat_id BIGINT NOT NULL, -- هذا هو معرف الجروب
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    UNIQUE (user_id), -- كل user_id يمكن ربطه فقط بتليجرام واحد
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE ON UPDATE CASCADE
);



--جدول لتخزين بيانات أنواع السيارات
CREATE TABLE car_types (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL
);
--جدول لتخزين بيانات الدول
CREATE TABLE countries (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE
);
--جدول لتخزين بيانات السائقين
CREATE TABLE drivers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    phone VARCHAR(20) UNIQUE NOT NULL,
    email VARCHAR(100),
    gender ENUM('male', 'female') DEFAULT NULL,
    country_id INT DEFAULT NULL,           -- مفتاح خارجي لجدول countries
    car_type_id INT DEFAULT 1,             -- نوع السيارة
    rating DECIMAL(3,2) DEFAULT 0.0,       -- تقييم السائق
    app_status ENUM('active', 'inactive', 'banned') DEFAULT 'inactive',
    main_system_status ENUM(
        'pending', 
        'waiting_chat', 
        'no_answer', 
        'rescheduled', 
        'completed', 
        'blocked', 
        'reconsider',
        'needs_documents'
    ) DEFAULT 'pending',
    registered_at TEXT,
    data_source ENUM('form', 'referral', 'telegram', 'staff', 'excel') NOT NULL,
    added_by INT DEFAULT NULL,             -- المستخدم الذي أضاف السائق
    hold BOOLEAN DEFAULT 0,                -- هل السائق محجوز الآن
    hold_by INT(11) DEFAULT NULL,          -- معرف المستخدم الذي حجز السائق
    has_missing_documents BOOLEAN DEFAULT 0,
    notes TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    -- المفاتيح الخارجية
    FOREIGN KEY (car_type_id) REFERENCES car_types(id),
    FOREIGN KEY (added_by) REFERENCES users(id),
    FOREIGN KEY (country_id) REFERENCES countries(id),
    FOREIGN KEY (hold_by) REFERENCES users(id) 
        ON DELETE SET NULL 
        ON UPDATE CASCADE,

    -- الفهارس (Indexes)
    INDEX idx_hold (hold),
    INDEX idx_status_hold (main_system_status, hold)
);


CREATE TABLE `driver_attributes` (
  `driver_id` int(11) NOT NULL,
  `has_many_trips` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`driver_id`),
  CONSTRAINT `driver_attributes_ibfk_1` FOREIGN KEY (`driver_id`) REFERENCES `drivers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


--جدول لتخزين مكالمات السائقين
CREATE TABLE driver_calls (
    id INT AUTO_INCREMENT PRIMARY KEY,
    driver_id INT NOT NULL,
    call_by INT NOT NULL,
    call_status ENUM('no_answer', 'answered', 'busy', 'not_available', 'wrong_number', 'rescheduled') DEFAULT 'no_answer',
    notes TEXT,
    next_call_at DATETIME,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (driver_id) REFERENCES drivers(id),
    FOREIGN KEY (call_by) REFERENCES users(id),
    INDEX idx_next_call_at (next_call_at) -- Index for filtering by call time
);
--جدول لتخزين تحويلات المكالمات بين الموظفين
CREATE TABLE driver_assignments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    driver_id INT NOT NULL,
    from_user_id INT NOT NULL,
    to_user_id INT NOT NULL,
    note TEXT,
    is_seen BOOLEAN DEFAULT 0, -- لما يشوف السائق دا خلاص ميظهرلوش تاني
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (driver_id) REFERENCES drivers(id),
    FOREIGN KEY (from_user_id) REFERENCES users(id),
    FOREIGN KEY (to_user_id) REFERENCES users(id)
);
--جدول لتخزين بيانات المستندات المطلوبة للسائقين
CREATE TABLE document_types (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    is_required BOOLEAN DEFAULT 1 -- لو في مستندات اختيارية لاحقًا
);
INSERT INTO document_types (name) VALUES
('Driver\'s licence'),
('Vehicle\'s licence'),
('Operating licence'),
('Captain\'s Pic'),
('Vehicle\'s pic'),
('Personal ID');

--جدول لتخزين بيانات المستندات المطلوبة للسائقين
CREATE TABLE driver_documents_required (
    id INT AUTO_INCREMENT PRIMARY KEY,
    driver_id INT NOT NULL,
    document_type_id INT NOT NULL,
    status ENUM('missing', 'submitted', 'rejected') DEFAULT 'missing',
    note TEXT,
    updated_by INT DEFAULT NULL,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (driver_id) REFERENCES drivers(id),
    FOREIGN KEY (document_type_id) REFERENCES document_types(id),
    FOREIGN KEY (updated_by) REFERENCES users(id),
    
    UNIQUE(driver_id, document_type_id) -- يمنع التكرار لنفس المستند لنفس السائق
);

-- جدول المنصات
CREATE TABLE platforms (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE
);

-- جدول المكالمات الواردة
CREATE TABLE incoming_calls (
    id INT AUTO_INCREMENT PRIMARY KEY,
    caller_phone_number VARCHAR(20) NOT NULL,
    call_received_by INT NOT NULL,
    call_started_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    call_ended_at DATETIME DEFAULT NULL,
    status ENUM('answered', 'missed') DEFAULT 'answered',
    linked_ticket_detail_id INT DEFAULT NULL,
    
    FOREIGN KEY (call_received_by) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (linked_ticket_detail_id) REFERENCES ticket_details(id) ON DELETE SET NULL,
    INDEX idx_caller_phone (caller_phone_number)
);

-- جدول الفرق
CREATE TABLE teams (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    team_leader_id INT NOT NULL,
    FOREIGN KEY (team_leader_id) REFERENCES users(id)
);
-- اعضاء الفرق
CREATE TABLE team_members (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    team_id INT NOT NULL,
    joined_at DATETIME DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (team_id) REFERENCES teams(id)
);
-- التصنيفات
CREATE TABLE ticket_categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE
);
--التصنيفات الفرعيه
CREATE TABLE ticket_subcategories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,

    FOREIGN KEY (category_id) REFERENCES ticket_categories(id) ON DELETE CASCADE,
    UNIQUE(category_id, name)
);

--جدول الاكواد
CREATE TABLE ticket_codes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    subcategory_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,

    FOREIGN KEY (subcategory_id) REFERENCES ticket_subcategories(id) ON DELETE CASCADE,
    UNIQUE(subcategory_id, name)
);

--جدول التيكتات
CREATE TABLE tickets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ticket_number VARCHAR(50) NOT NULL UNIQUE, -- رقم التذكرة
    created_by INT NOT NULL,                   -- الموظف الذي أنشأ التذكرة
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,

    -- العلاقات (Foreign Keys)
    FOREIGN KEY (created_by) REFERENCES users(id)
);

-- جدول تفاصيل التذاكر (للسجل والتغييرات)
CREATE TABLE ticket_details (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ticket_id INT NOT NULL,
    
    is_vip BOOLEAN DEFAULT 0,
    platform_id INT NOT NULL,
    phone VARCHAR(20),
    category_id INT NOT NULL,
    subcategory_id INT NOT NULL,
    code_id INT NOT NULL,
    notes TEXT,
    country_id INT DEFAULT NULL,
    
    assigned_team_leader_id INT NOT NULL,
    edited_by INT NOT NULL, -- الموظف الذي أجرى هذا التعديل (أو أنشأ الإصدار الأول)
    
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (ticket_id) REFERENCES tickets(id) ON DELETE CASCADE,
    FOREIGN KEY (platform_id) REFERENCES platforms(id),
    FOREIGN KEY (category_id) REFERENCES ticket_categories(id),
    FOREIGN KEY (subcategory_id) REFERENCES ticket_subcategories(id),
    FOREIGN KEY (code_id) REFERENCES ticket_codes(id),
    FOREIGN KEY (country_id) REFERENCES countries(id),
    FOREIGN KEY (assigned_team_leader_id) REFERENCES users(id),
    FOREIGN KEY (edited_by) REFERENCES users(id)
);


--جدول مراجعات التذاكر
CREATE TABLE reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    reviewable_id INT(11) NOT NULL,
    reviewable_type VARCHAR(50) NOT NULL,
    reviewed_by INT NOT NULL,
    rating TINYINT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Review rating from 0 to 100',
    review_notes TEXT,
    reviewed_at DATETIME DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_reviewable (reviewable_id, reviewable_type),
    FOREIGN KEY (reviewed_by) REFERENCES users(id)
);

--المناقشات
CREATE TABLE discussions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    discussable_id INT NOT NULL,
    discussable_type VARCHAR(50) NOT NULL,
    opened_by INT NOT NULL,
    reason VARCHAR(255) NOT NULL,
    notes TEXT,
    status ENUM('open', 'closed') NOT NULL DEFAULT 'open',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_discussable (discussable_id, discussable_type),
    FOREIGN KEY (opened_by) REFERENCES users(id)
);


--جدول الردود
CREATE TABLE discussion_replies (
    id INT AUTO_INCREMENT PRIMARY KEY,
    discussion_id INT NOT NULL,
    user_id INT NOT NULL,
    message TEXT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (discussion_id) REFERENCES discussions(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);


--الكوبونات
CREATE TABLE `coupons` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `code` VARCHAR(50) NOT NULL UNIQUE,
    `value` DECIMAL(10, 2) NOT NULL, -- قيمة الكوبون
    `country_id` INT, -- الدولة المرتبط بها الكوبون
    `is_used` BOOLEAN DEFAULT 0, -- هل تم استخدام الكوبون
    `held_by` INT(11) NULL DEFAULT NULL, -- الموظف الذي يحتفظ بالكوبون حاليًا
    `held_at` DATETIME NULL DEFAULT NULL, -- توقيت إمساك الموظف بالكوبون
    `used_by` INT, -- الموظف الذي استخدم الكوبون (user_id)
    `used_in_ticket` INT, -- رقم التذكرة التي استُخدم فيها
    `used_at` DATETIME, -- توقيت الاستخدام
    `used_for_phone` VARCHAR(20), -- رقم العميل الذي استخدم لأجله الكوبون (اختياري)
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (`country_id`) REFERENCES `countries`(`id`),
    FOREIGN KEY (`used_by`) REFERENCES `users`(`id`),
    FOREIGN KEY (`used_in_ticket`) REFERENCES `tickets`(`id`),
    FOREIGN KEY (`held_by`) REFERENCES `users`(`id`) ON DELETE SET NULL
);


--تيكتات الكوبون الي تم استخدامها لان ممكن تيكت واحدة نستخدم معاها اكثر من كوبون
CREATE TABLE ticket_coupons (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ticket_id INT NOT NULL,
    ticket_detail_id INT NULL DEFAULT NULL,
    coupon_id INT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (ticket_id) REFERENCES tickets(id) ON DELETE CASCADE,
    FOREIGN KEY (ticket_detail_id) REFERENCES ticket_details(id) ON DELETE SET NULL,
    FOREIGN KEY (coupon_id) REFERENCES coupons(id),
    
    UNIQUE(ticket_id, coupon_id)
);

--جدول لربط تفاصيل التذاكر VIP بالمسوقين
CREATE TABLE ticket_vip_assignments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ticket_detail_id INT NOT NULL,
    marketer_id INT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (ticket_detail_id) REFERENCES ticket_details(id) ON DELETE CASCADE,
    FOREIGN KEY (marketer_id) REFERENCES users(id) ON DELETE CASCADE,

    UNIQUE(ticket_detail_id) --  نفترض أن كل تذكرة VIP يمكن أن تُسند إلى مسوق واحد فقط
);

--جدول لتخزين بيانات الزيارات المرجعية
CREATE TABLE referral_visits (
    id INT AUTO_INCREMENT PRIMARY KEY,

    --  معرّف المسوّق (المستخدم الذي يملك role = 'marketer')
    affiliate_user_id INT NULL DEFAULT NULL, 

    -- بيانات الزيارة الأساسية
    visit_recorded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ip_address VARCHAR(45) NOT NULL,
    user_agent TEXT DEFAULT NULL,
    referer_url TEXT DEFAULT NULL,
    query_params TEXT DEFAULT NULL, -- لتخزين متغيرات مثل utm_source, utm_medium, etc.
    referer_source VARCHAR(100) NULL DEFAULT NULL, -- مثال: google.com, facebook.com

    -- بيانات الموقع الجغرافي (من خدمة مثل ipinfo)
    country VARCHAR(100) DEFAULT NULL,
    region VARCHAR(100) DEFAULT NULL,
    city VARCHAR(100) DEFAULT NULL,
    isp VARCHAR(255) DEFAULT NULL,

    -- بيانات جهاز الزائر
    device_type VARCHAR(50) DEFAULT NULL,
    browser_name VARCHAR(50) DEFAULT NULL,
    operating_system VARCHAR(50) DEFAULT NULL,

    -- بيانات تتبع عملية التسجيل
    registration_status ENUM(
        'visit_only',       -- مجرد زيارة للرابط
        'form_opened',      -- فتح صفحة التسجيل
        'attempted',        -- محاولة التسجيل (تم إرسال الفورم)
        'successful',       -- تم التسجيل بنجاح وإنشاء سائق
        'duplicate_phone',  -- فشل بسبب رقم هاتف مكرر
        'failed_other'      -- فشل لأسباب أخرى
    ) DEFAULT 'visit_only',
    registration_attempted_at TIMESTAMP NULL DEFAULT NULL,
    
    -- الربط مع السائق الذي تم تسجيله بنجاح
    -- هذا هو التعديل الأهم: الربط مع جدول `drivers`
    registered_driver_id INT NULL DEFAULT NULL,

    -- عمود محسوب لتسهيل الاستعلامات اليومية
    visit_date DATE AS (DATE(visit_recorded_at)) STORED,

    -- مفتاح فريد لمنع تسجيل نفس الزيارة (نفس الـ IP لنفس المسوق) أكثر من مرة في اليوم الواحد
    UNIQUE KEY uq_affiliate_ip_date (affiliate_user_id, ip_address, visit_date),
    
    -- الفهارس لتحسين أداء الاستعلامات
    INDEX idx_affiliate_user_id (affiliate_user_id),
    INDEX idx_registered_driver_id (registered_driver_id),
    INDEX idx_ip_address (ip_address),

    -- الربط مع جدول المسوقين (المستخدمين)
    FOREIGN KEY (affiliate_user_id) REFERENCES users(id) ON DELETE SET NULL ON UPDATE CASCADE,
    
    -- الربط مع جدول السائقين
    FOREIGN KEY (registered_driver_id) REFERENCES drivers(id) ON DELETE SET NULL ON UPDATE CASCADE

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
--
--جدول لتخزين بيانات الرحلات
CREATE TABLE trips (
    order_id CHAR(36) PRIMARY KEY COMMENT 'معرّف الطلب الفريد (UUID)',

    created_at DATETIME COMMENT 'تاريخ ووقت إنشاء الطلب',
    author_id CHAR(36) COMMENT 'معرّف المستخدم الذي أنشأ الطلب',
    order_source VARCHAR(100) COMMENT 'مصدر إنشاء الطلب (تطبيق، نظام خارجي، إلخ)',
    bundle VARCHAR(100) COMMENT 'اسم الباقة المستخدمة في الطلب',
    requested_vehicle_type VARCHAR(50) COMMENT 'نوع المركبة المطلوبة',
    requested_pickup_time VARCHAR(50) COMMENT 'وقت الاستلام المطلوب (فوري / مجدول)',

    origin_type VARCHAR(50) COMMENT 'نوع موقع الانطلاق (نقطة على الخريطة، عنوان، إلخ)',
    origin_location VARCHAR(100) COMMENT 'إحداثيات الانطلاق (خط العرض، خط الطول)',
    origin_address TEXT COMMENT 'العنوان النصي لموقع الانطلاق',

    destination_type VARCHAR(50) COMMENT 'نوع موقع الوجهة',
    destination_location VARCHAR(100) COMMENT 'إحداثيات الوجهة',
    destination_address TEXT COMMENT 'العنوان النصي للوجهة',

    dropoff_type VARCHAR(50) COMMENT 'نوع موقع التسليم الأخير (drop-off)',
    dropoff_location VARCHAR(100) COMMENT 'إحداثيات نقطة التسليم',
    dropoff_address TEXT COMMENT 'العنوان النصي للتسليم',

    dropoffs_count INT COMMENT 'عدد نقاط الإنزال في الرحلة',
    order_notes TEXT COMMENT 'ملاحظات إضافية من العميل',
    passengers_number INT COMMENT 'عدد الركاب في الرحلة',

    client_documents TEXT COMMENT 'مستندات العميل - إن وُجدت',
    driver_payment_documents TEXT COMMENT 'مستندات الدفع الخاصة بالسائق',

    passenger_id CHAR(36) COMMENT 'معرّف الراكب',
    passenger_name VARCHAR(100) COMMENT 'اسم الراكب',
    passenger_email VARCHAR(100) COMMENT 'البريد الإلكتروني للراكب',
    passenger_phone VARCHAR(20) COMMENT 'رقم هاتف الراكب',

    passenger_operator_id CHAR(36) COMMENT 'معرّف مشغل الراكب - إن وجد',
    passenger_operator_name VARCHAR(100) COMMENT 'اسم مشغل الراكب',
    passenger_operator_email VARCHAR(100) COMMENT 'بريد مشغل الراكب',

    driver_operator_id CHAR(36) COMMENT 'معرّف مشغل السائق',
    driver_operator_name VARCHAR(100) COMMENT 'اسم مشغل السائق',
    driver_operator_email VARCHAR(100) COMMENT 'بريد مشغل السائق',

    driver_id CHAR(36) COMMENT 'معرّف السائق',
    driver_custom_key VARCHAR(100) COMMENT 'مفتاح مخصص للسائق (إن وُجد)',
    driver_name VARCHAR(100) COMMENT 'اسم السائق',
    driver_email VARCHAR(100) COMMENT 'البريد الإلكتروني للسائق',
    driver_phone VARCHAR(20) COMMENT 'رقم هاتف السائق',

    vehicle_type VARCHAR(50) COMMENT 'نوع السيارة المستخدمة',
    vehicle_plate_number VARCHAR(50) COMMENT 'رقم لوحة المركبة',
    vehicle_board_number VARCHAR(50) COMMENT 'رقم تسجيل المركبة',

    estimation_time VARCHAR(50) COMMENT 'المدة المتوقعة للرحلة',
    estimation_distance VARCHAR(50) COMMENT 'المسافة المتوقعة للرحلة',
    driver_rate_plan VARCHAR(50) COMMENT 'خطة التسعير الخاصة بالسائق',

    offer_count INT COMMENT 'عدد العروض المقدمة من السائقين',
    reject_count INT COMMENT 'عدد مرات رفض الطلب',
    total_bid_count INT COMMENT 'عدد عروض التسعير (مزايدات)',
    driver_bid_count INT COMMENT 'عدد عروض التسعير من السائقين',
    dispatcher_bid_count INT COMMENT 'عدد عروض التسعير من الموزعين',

    order_status VARCHAR(50) COMMENT 'حالة الطلب النهائية (مكتمل، ملغى، قيد التنفيذ...)',
    unpaid_reason TEXT COMMENT 'سبب عدم الدفع - إن وجد',
    cancellation_reason TEXT COMMENT 'سبب الإلغاء - إن وُجد',
    cancellation_comment TEXT COMMENT 'تعليق الإلغاء من المستخدم أو النظام',

    trip_distance_km VARCHAR(50) COMMENT 'المسافة الفعلية للرحلة (كم)',
    trip_time VARCHAR(50) COMMENT 'مدة الرحلة الفعلية',
    intermediate_driver_ids TEXT COMMENT 'معرّفات السائقين الوسطاء - إن وُجدوا',

    passenger_cancellation_fee_omr DECIMAL(10,3) COMMENT 'رسوم إلغاء الراكب (ريال عماني)',
    driver_cancellation_fee_omr DECIMAL(10,3) COMMENT 'رسوم إلغاء السائق',
    trip_cost_omr DECIMAL(10,3) COMMENT 'تكلفة الرحلة الأساسية',
    extra_cost_omr DECIMAL(10,3) COMMENT 'تكاليف إضافية',
    total_cost_omr DECIMAL(10,3) COMMENT 'التكلفة الكلية قبل الخصومات والضرائب',
    coupon_discount_omr DECIMAL(10,3) COMMENT 'قيمة الخصم من القسيمة',
    tips_omr DECIMAL(10,3) COMMENT 'الإكرامية (Tips)',
    bonus_amount_omr DECIMAL(10,3) COMMENT 'مكافآت إضافية',
    including_tax_omr DECIMAL(10,3) COMMENT 'المبلغ متضمناً الضرائب',
    tax_omr DECIMAL(10,3) COMMENT 'قيمة الضريبة',
    transactional_fee_omr DECIMAL(10,3) COMMENT 'رسوم المعاملات',
    final_cost_omr DECIMAL(10,3) COMMENT 'التكلفة النهائية بعد كل العمليات',
    unpaid_cost_omr DECIMAL(10,3) COMMENT 'المبلغ غير المدفوع',
    rounding_correction_value_omr DECIMAL(10,3) COMMENT 'قيمة التصحيح الناتجة عن التقريب',
    excess_payment_omr DECIMAL(10,3) COMMENT 'المبلغ الزائد عن الفاتورة',

    payment_method VARCHAR(50) COMMENT 'طريقة الدفع المستخدمة',
    payment_card VARCHAR(100) COMMENT 'معلومات البطاقة إن وُجدت',
    corporate_account VARCHAR(100) COMMENT 'حساب الشركات - إن وُجد',
    payment_errors TEXT COMMENT 'أخطاء الدفع إن حصلت',

    rating_by_driver INT COMMENT 'تقييم الراكب من قبل السائق',
    rating_by_passenger INT COMMENT 'تقييم السائق من قبل الراكب',

    started_at DATETIME COMMENT 'وقت بدء الرحلة',
    started_location VARCHAR(100) COMMENT 'إحداثيات بداية الرحلة',
    arrived_at DATETIME COMMENT 'وقت وصول السائق لموقع الراكب',
    arrived_location VARCHAR(100) COMMENT 'إحداثيات وصول السائق',
    loaded_at DATETIME COMMENT 'وقت تحميل الراكب',
    loaded_location VARCHAR(100) COMMENT 'إحداثيات التحميل',
    finished_at DATETIME COMMENT 'وقت إنهاء الرحلة',
    finished_location VARCHAR(100) COMMENT 'إحداثيات نهاية الرحلة',
    closed_at DATETIME COMMENT 'وقت إغلاق الطلب (نهائي)',
    closed_location VARCHAR(100) COMMENT 'إحداثيات إغلاق الطلب',

    service_space VARCHAR(50) COMMENT 'بيئة التشغيل (مثلاً: production)',
    active BOOLEAN COMMENT 'هل الطلب نشط حاليًا؟',
    linked_order CHAR(36) COMMENT 'طلب مرتبط - إن وُجد',
    price_multiplier DECIMAL(4,2) COMMENT 'عامل ضرب السعر (الزيادة الديناميكية)',
    coupon_code VARCHAR(50) COMMENT 'رمز القسيمة المستخدمة',
    promo_campaign_name VARCHAR(100) COMMENT 'اسم الحملة الترويجية - إن وُجدت'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


--جدول لتخزين إجازات السائقين
CREATE TABLE driver_snoozes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    driver_id INT NOT NULL,
    user_id INT NOT NULL,
    snoozed_until DATETIME NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (driver_id) REFERENCES drivers(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_snooze (user_id, snoozed_until)
);


--جدول لربط المندوبين بحسابتهم في الموقع
CREATE TABLE agents (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    state VARCHAR(100) NOT NULL,
    is_online_only TINYINT(1) DEFAULT 1,
    phone VARCHAR(20) NOT NULL,

    latitude DECIMAL(10, 7) DEFAULT NULL,
    longitude DECIMAL(10, 7) DEFAULT NULL,
    map_url VARCHAR(500) DEFAULT NULL,

    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

--جدول لتخزين ساعات العمل للمندوبين
CREATE TABLE working_hours (
    id INT AUTO_INCREMENT PRIMARY KEY,
    agent_id INT NOT NULL,
    day_of_week ENUM('Saturday', 'Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday') NOT NULL,
    start_time TIME DEFAULT NULL,
    end_time TIME DEFAULT NULL,
    is_closed TINYINT(1) DEFAULT 0, -- 1 = إجازة في هذا اليوم

    FOREIGN KEY (agent_id) REFERENCES agents(id) ON DELETE CASCADE,
    UNIQUE (agent_id, day_of_week) -- كل مندوب لا يمكن أن يُكرر نفس اليوم
);


--جدول الاشعارات
CREATE TABLE notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    link VARCHAR(512) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
--جدول لتخزين بيانات الاشعارات
CREATE TABLE user_notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    notification_id INT NOT NULL,
    is_read TINYINT(1) DEFAULT 0, -- 0 = لم يتم الفتح، 1 = تم الفتح
    read_at TIMESTAMP NULL DEFAULT NULL,

    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (notification_id) REFERENCES notifications(id) ON DELETE CASCADE
);




CREATE TABLE ticket_code_points (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code_id INT NOT NULL,
    is_vip BOOLEAN NOT NULL DEFAULT 0,
    points DECIMAL(10, 2) NOT NULL,
    valid_from DATE NOT NULL,
    valid_to DATE DEFAULT NULL,
    KEY (code_id, is_vip, valid_from, valid_to),
    FOREIGN KEY (code_id) REFERENCES ticket_codes(id) ON DELETE CASCADE
);




CREATE TABLE call_points (
    id INT AUTO_INCREMENT PRIMARY KEY,
    points DECIMAL(10, 2) NOT NULL,
    call_type ENUM('incoming', 'outgoing') NOT NULL DEFAULT 'outgoing',
    valid_from DATE NOT NULL,
    valid_to DATE DEFAULT NULL,
    KEY (valid_from, valid_to),
    KEY (call_type, valid_from, valid_to)
);



CREATE TABLE user_monthly_bonus (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    bonus_percent DECIMAL(5,2) NOT NULL,
    bonus_year SMALLINT NOT NULL,
    bonus_month TINYINT NOT NULL,
    reason VARCHAR(255) DEFAULT NULL,
    granted_by INT DEFAULT NULL,
    granted_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE (user_id, bonus_year, bonus_month),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (granted_by) REFERENCES users(id) ON DELETE SET NULL
);

CREATE TABLE bonus_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    min_bonus_percent DECIMAL(5, 2) NOT NULL DEFAULT 0.00,
    max_bonus_percent DECIMAL(5, 2) NOT NULL DEFAULT 100.00,
    predefined_bonus_1 DECIMAL(5, 2) NOT NULL DEFAULT 5.00,
    predefined_bonus_2 DECIMAL(5, 2) NOT NULL DEFAULT 10.00,
    predefined_bonus_3 DECIMAL(5, 2) NOT NULL DEFAULT 15.00,
    updated_by INT NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (updated_by) REFERENCES users(id) ON DELETE SET NULL
);

-- Insert a default row so we always have settings to work with
INSERT INTO bonus_settings (id) VALUES (1);


INSERT INTO ticket_details (ticket_id, is_vip, platform_id, phone, category_id, subcategory_id, code_id, notes, country_id, assigned_team_leader_id, edited_by, created_at)
SELECT 
    id, is_vip, platform_id, phone, category_id, subcategory_id, code_id, notes, country_id, assigned_team_leader_id, created_by, created_at
FROM tickets;

INSERT INTO `platforms` (`name`) VALUES
('Phone'),
('Facebook'),
('WhatsApp'),
('Telegram'),
('Email'),
('Incoming Call');



CREATE TABLE `knowledge_base` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ticket_code_id` int(11) DEFAULT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `content` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_by` int(11) DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `knowledge_base_ticket_code_id_foreign` (`ticket_code_id`),
  KEY `knowledge_base_created_by_foreign` (`created_by`),
  KEY `knowledge_base_updated_by_foreign` (`updated_by`),
  FULLTEXT KEY `title_content_fulltext` (`title`,`content`),
  CONSTRAINT `knowledge_base_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `knowledge_base_ticket_code_id_foreign` FOREIGN KEY (`ticket_code_id`) REFERENCES `ticket_codes` (`id`) ON DELETE SET NULL,
  CONSTRAINT `knowledge_base_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;




CREATE TABLE `role_permissions` (
  `id` int(11) NOT NULL,
  `role_id` int(11) NOT NULL,
  `permission_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


CREATE TABLE user_discussion_read_status (
    user_id INT NOT NULL,
    discussion_id INT NOT NULL,
    last_read_reply_id INT NOT NULL,
    last_read_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (user_id, discussion_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (discussion_id) REFERENCES discussions(id) ON DELETE CASCADE
);