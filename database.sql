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
('developer');


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



CREATE TABLE drivers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    phone VARCHAR(20) UNIQUE NOT NULL,
    email VARCHAR(100),
    gender ENUM('male', 'female') DEFAULT NULL,
    nationality VARCHAR(100),
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
    FOREIGN KEY (added_by) REFERENCES users(id)
);


CREATE TABLE car_types (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL
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
