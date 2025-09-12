<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

$host = 'localhost';
$db   = 'taxif_cstaxi';
$user = 'taxif_root';
$pass = 'lcU*bQuQDEB0';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);

    // 1. إنشاء جدول knowledge_base_folders
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS knowledge_base_folders (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            description TEXT DEFAULT NULL,
            color VARCHAR(20) DEFAULT '#3B82F6',
            icon VARCHAR(50) DEFAULT 'fas fa-folder',
            parent_id INT DEFAULT NULL,
            is_active BOOLEAN DEFAULT 1,
            created_by INT DEFAULT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
            FOREIGN KEY (parent_id) REFERENCES knowledge_base_folders(id) ON DELETE CASCADE,
            INDEX idx_parent_id (parent_id),
            INDEX idx_created_by (created_by),
            INDEX idx_is_active (is_active)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");

    // 2. إضافة الفولدرات الافتراضية
    $folders = [
        ['عام', 'مقالات عامة ومتنوعة', '#6B7280', 'fas fa-folder'],
        ['دعم فني', 'مشاكل تقنية وحلولها', '#EF4444', 'fas fa-tools'],
        ['أسئلة شائعة', 'الأسئلة الأكثر تكراراً', '#10B981', 'fas fa-question-circle'],
        ['دليل المستخدم', 'إرشادات الاستخدام', '#3B82F6', 'fas fa-book'],
        ['تحديثات النظام', 'التحديثات والمميزات الجديدة', '#8B5CF6', 'fas fa-rocket'],
    ];

    $stmt = $pdo->prepare("
        INSERT INTO knowledge_base_folders (name, description, color, icon)
        VALUES (:name, :description, :color, :icon)
        ON DUPLICATE KEY UPDATE name=name
    ");
    foreach ($folders as $f) {
        $stmt->execute([
            ':name' => $f[0],
            ':description' => $f[1],
            ':color' => $f[2],
            ':icon' => $f[3],
        ]);
    }

    // 3. إضافة عمود folder_id لجدول knowledge_base إذا لم يكن موجود
    $columns = $pdo->query("SHOW COLUMNS FROM knowledge_base LIKE 'folder_id'")->fetch();
    if (!$columns) {
        $pdo->exec("ALTER TABLE knowledge_base ADD COLUMN folder_id INT DEFAULT NULL AFTER ticket_code_id;");
    }

    // 4. إضافة المفتاح الخارجي للفولدر
    try {
        $pdo->exec("
            ALTER TABLE knowledge_base
            ADD CONSTRAINT knowledge_base_folder_id_foreign
            FOREIGN KEY (folder_id) REFERENCES knowledge_base_folders(id) ON DELETE SET NULL;
        ");
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), 'Duplicate') === false) {
            throw $e;
        }
        // إذا كان المفتاح موجود مسبقًا، تجاهل الخطأ
    }

    // 5. إضافة الفهرس
    try {
        $pdo->exec("CREATE INDEX idx_folder_id ON knowledge_base(folder_id);");
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), 'Duplicate') === false) {
            throw $e;
        }
    }

    // 6. تعيين الفولدر الافتراضي "عام" للمقالات الموجودة
    $pdo->exec("
        UPDATE knowledge_base
        SET folder_id = (SELECT id FROM knowledge_base_folders WHERE name = 'عام' LIMIT 1)
        WHERE folder_id IS NULL;
    ");

    echo "تم تنفيذ كل العمليات بنجاح.";
    
} catch (PDOException $e) {
    echo "فشل التنفيذ: " . $e->getMessage();
}
?>
