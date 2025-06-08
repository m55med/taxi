<?php
// Database Configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'taxi');
define('DB_USER', 'root');
define('DB_PASS', '');

// URL Configuration
define('URLROOT', 'http://localhost/taxi');
define('SITENAME', 'Taxi Service');

// Directory Configuration
define('APPROOT', dirname(dirname(__FILE__)));
define('BASE_PATH', '/taxi');

// Other Configuration
define('UPLOAD_PATH', APPROOT . '/public/uploads');
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'pdf']);
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB 