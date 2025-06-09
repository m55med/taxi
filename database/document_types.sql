-- إنشاء جدول أنواع المستندات
CREATE TABLE IF NOT EXISTS document_types (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    name_ar VARCHAR(100) NOT NULL,
    is_required BOOLEAN DEFAULT TRUE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- إنشاء جدول المستندات المطلوبة للسائقين
CREATE TABLE IF NOT EXISTS driver_documents_required (
    id INT AUTO_INCREMENT PRIMARY KEY,
    driver_id INT NOT NULL,
    document_type_id INT NOT NULL,
    status ENUM('missing', 'submitted', 'rejected') DEFAULT 'missing',
    note TEXT,
    updated_by INT,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (driver_id) REFERENCES drivers(id),
    FOREIGN KEY (document_type_id) REFERENCES document_types(id),
    FOREIGN KEY (updated_by) REFERENCES users(id),
    UNIQUE KEY unique_driver_document (driver_id, document_type_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- إدخال البيانات الأساسية لأنواع المستندات
INSERT INTO document_types (name, name_ar, is_required) VALUES
('Personal ID', 'الهوية الشخصية', TRUE),
("Driver's License", 'رخصة القيادة', TRUE),
('Vehicle Registration', 'استمارة السيارة', TRUE),
('Insurance Policy', 'وثيقة التأمين', TRUE),
('Criminal Record', 'السجل الجنائي', TRUE),
('Medical Certificate', 'الشهادة الطبية', TRUE),
('Bank Account', 'الحساب البنكي', FALSE); 