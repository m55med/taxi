-- إنشاء جدول أنواع السيارات
CREATE TABLE IF NOT EXISTS car_types (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    name_ar VARCHAR(50) NOT NULL,
    description TEXT,
    is_active BOOLEAN DEFAULT TRUE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- إدخال البيانات الأساسية
INSERT INTO car_types (name, name_ar, description) VALUES
('Sedan', 'سيدان', 'سيارة عائلية صغيرة'),
('SUV', 'دفع رباعي', 'سيارة رياضية متعددة الاستخدامات'),
('Van', 'فان', 'سيارة كبيرة للعائلات والمجموعات'),
('Luxury', 'فاخرة', 'سيارة فاخرة للمناسبات الخاصة'),
('Economy', 'اقتصادية', 'سيارة صغيرة واقتصادية'),
('Premium', 'بريميوم', 'سيارة متميزة بمواصفات عالية'); 