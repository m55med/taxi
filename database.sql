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




CREATE TABLE car_types (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL
);

CREATE TABLE countries (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE
);

CREATE TABLE drivers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    phone VARCHAR(20) UNIQUE NOT NULL,
    email VARCHAR(100),
    gender ENUM('male', 'female') DEFAULT NULL,
    country_id INT DEFAULT NULL,  -- ربط الدولة بجدول countries
    car_type_id INT DEFAULT 1,
    rating DECIMAL(3,2) DEFAULT 0.0,
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
    added_by INT DEFAULT NULL,
    hold BOOLEAN DEFAULT 0,
    has_missing_documents BOOLEAN DEFAULT 0,
    notes TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (car_type_id) REFERENCES car_types(id),
    FOREIGN KEY (added_by) REFERENCES users(id),
    FOREIGN KEY (country_id) REFERENCES countries(id)
);




CREATE TABLE driver_calls (
    id INT AUTO_INCREMENT PRIMARY KEY,
    driver_id INT NOT NULL,
    call_by INT NOT NULL,
    call_status ENUM('no_answer', 'answered', 'busy', 'not_available', 'wrong_number', 'rescheduled') DEFAULT 'no_answer',
    notes TEXT,
    next_call_at DATETIME,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (driver_id) REFERENCES drivers(id),
    FOREIGN KEY (call_by) REFERENCES users(id)
);

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
    is_vip BOOLEAN DEFAULT 0,                  -- هل VIP أم لا
    platform_id INT NOT NULL,                  -- المنصة القادمة منها التذكرة
    phone VARCHAR(20),                         -- رقم الهاتف (اختياري)
    
    category_id INT NOT NULL,                  -- التصنيف الرئيسي
    subcategory_id INT NOT NULL,               -- التصنيف الفرعي
    code_id INT NOT NULL,                      -- الكود المختار بناء على التصنيف

    notes TEXT,                                -- ملاحظات (اختياري)
    
    country_id INT DEFAULT NULL,               -- الدولة (اختياري)

    created_by INT NOT NULL,                   -- الموظف الذي أنشأ التذكرة
    assigned_team_leader_id INT NOT NULL,      -- التيم ليدر المسؤول عن الموظف
    
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,

    -- العلاقات (Foreign Keys)
    FOREIGN KEY (platform_id) REFERENCES platforms(id),
    FOREIGN KEY (category_id) REFERENCES ticket_categories(id),
    FOREIGN KEY (subcategory_id) REFERENCES ticket_subcategories(id),
    FOREIGN KEY (code_id) REFERENCES ticket_codes(id),
    FOREIGN KEY (created_by) REFERENCES users(id),
    FOREIGN KEY (assigned_team_leader_id) REFERENCES users(id),
    FOREIGN KEY (country_id) REFERENCES countries(id)
);


--جدول مراجعات التذاكر
CREATE TABLE ticket_reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ticket_id INT NOT NULL,
    reviewed_by INT NOT NULL,
    review_result ENUM('return_to_agent', 'accepted', 'rejected') NOT NULL,
    review_notes TEXT,
    reviewed_at DATETIME DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (ticket_id) REFERENCES tickets(id),
    FOREIGN KEY (reviewed_by) REFERENCES users(id)
);

--المناقشات
CREATE TABLE ticket_discussions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ticket_id INT NOT NULL,
    opened_by INT NOT NULL,
    reason VARCHAR(255) NOT NULL,
    notes TEXT,
    status ENUM('open', 'closed') NOT NULL DEFAULT 'open',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (ticket_id) REFERENCES tickets(id) ON DELETE CASCADE,
    FOREIGN KEY (opened_by) REFERENCES users(id)
);

--جدول الاعتراضات
CREATE TABLE ticket_objections (
    id INT AUTO_INCREMENT PRIMARY KEY,
    discussion_id INT NOT NULL,
    objection_text TEXT NOT NULL,
    replied_to_user_id INT NOT NULL,
    replied_by_agent_id INT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (discussion_id) REFERENCES ticket_discussions(id),
    FOREIGN KEY (replied_to_user_id) REFERENCES users(id),
    FOREIGN KEY (replied_by_agent_id) REFERENCES users(id)
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
    coupon_id INT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (ticket_id) REFERENCES tickets(id),
    FOREIGN KEY (coupon_id) REFERENCES coupons(id),
    
    UNIQUE(ticket_id, coupon_id) -- يمنع تكرار نفس الكوبون لتذكرة واحدة
);

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
