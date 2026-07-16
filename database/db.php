<?php

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$servername = "localhost";
$username = "root";
$password = "";
$database = "medibridgeai";

try {
    $conn = new mysqli($servername, $username, $password);
    $conn->set_charset("utf8mb4");
    $conn->query(
        "CREATE DATABASE IF NOT EXISTS `$database`
         CHARACTER SET utf8mb4
         COLLATE utf8mb4_unicode_ci"
    );
    $conn->select_db($database);

    $conn->query(
        "CREATE TABLE IF NOT EXISTS users (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            fullname VARCHAR(120) NOT NULL,
            email VARCHAR(190) NOT NULL UNIQUE,
            phone VARCHAR(32) NOT NULL,
            password VARCHAR(255) NOT NULL,
            is_admin TINYINT(1) NOT NULL DEFAULT 0,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
    );

    $existingColumns = [];
    $columns = $conn->query("SHOW COLUMNS FROM users");
    while ($column = $columns->fetch_assoc()) {
        $existingColumns[$column['Field']] = true;
    }

    if (!isset($existingColumns['created_at'])) {
        $conn->query(
            "ALTER TABLE users
             ADD COLUMN created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP"
        );
    }

    if (!isset($existingColumns['is_admin'])) {
        $conn->query(
            "ALTER TABLE users
             ADD COLUMN is_admin TINYINT(1) NOT NULL DEFAULT 0"
        );
    }

    $conn->query(
        "CREATE TABLE IF NOT EXISTS doctors (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            fullname VARCHAR(120) NOT NULL,
            email VARCHAR(190) NOT NULL UNIQUE,
            phone VARCHAR(32) NOT NULL,
            specialty VARCHAR(100) NOT NULL,
            available_days VARCHAR(120) NOT NULL,
            password VARCHAR(255) NOT NULL,
            is_active TINYINT(1) NOT NULL DEFAULT 1,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
    );

    $doctorCount = $conn->query("SELECT COUNT(*) AS total FROM doctors")->fetch_assoc();
    if ((int) ($doctorCount['total'] ?? 0) === 0) {
        $defaultPassword = password_hash('Doctor@123', PASSWORD_DEFAULT);
        $defaultDoctors = [
            ['Dr. Aisha Perera', 'aisha.perera@medibridgeai.local', '+94771000001', 'General Physician', 'Mon, Wed, Fri'],
            ['Dr. Rajan Kumar', 'rajan.kumar@medibridgeai.local', '+94771000002', 'Cardiologist', 'Tue, Thu'],
            ['Dr. Sameera Dias', 'sameera.dias@medibridgeai.local', '+94771000003', 'Dermatologist', 'Mon, Sat'],
            ['Dr. Nimal Fernando', 'nimal.fernando@medibridgeai.local', '+94771000004', 'Pediatrician', 'Wed, Fri, Sun'],
        ];

        $stmt = $conn->prepare(
            "INSERT IGNORE INTO doctors (fullname, email, phone, specialty, available_days, password)
             VALUES (?, ?, ?, ?, ?, ?)"
        );

        foreach ($defaultDoctors as $doctor) {
            $fullname = $doctor[0];
            $email = $doctor[1];
            $phone = $doctor[2];
            $specialty = $doctor[3];
            $availableDays = $doctor[4];

            $stmt->bind_param("ssssss", $fullname, $email, $phone, $specialty, $availableDays, $defaultPassword);
            $stmt->execute();
        }

        $stmt->close();
    }

    $conn->query(
        "CREATE TABLE IF NOT EXISTS appointments (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            booking_code VARCHAR(24) NOT NULL UNIQUE,
            user_id INT UNSIGNED NOT NULL,
            patient_name VARCHAR(120) NOT NULL,
            email VARCHAR(190) NOT NULL,
            phone VARCHAR(32) NOT NULL,
            nic VARCHAR(20) NOT NULL,
            doctor VARCHAR(160) NOT NULL,
            appointment_date DATE NOT NULL,
            appointment_time TIME NOT NULL,
            visit_type VARCHAR(60) NOT NULL,
            reason TEXT NOT NULL,
            status VARCHAR(40) NOT NULL DEFAULT 'Pending confirmation',
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_appointments_user_date (user_id, appointment_date),
            INDEX idx_appointments_nic (nic)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
    );

    $appointmentColumns = [];
    $columns = $conn->query("SHOW COLUMNS FROM appointments");
    while ($column = $columns->fetch_assoc()) {
        $appointmentColumns[$column['Field']] = true;
    }

    if (!isset($appointmentColumns['nic'])) {
        $conn->query(
            "ALTER TABLE appointments
             ADD COLUMN nic VARCHAR(20) NOT NULL DEFAULT '' AFTER phone"
        );
    }

    $nicIndex = $conn->query("SHOW INDEX FROM appointments WHERE Key_name = 'idx_appointments_nic'");
    if ($nicIndex->num_rows === 0) {
        $conn->query("ALTER TABLE appointments ADD INDEX idx_appointments_nic (nic)");
    }

    $conn->query(
        "CREATE TABLE IF NOT EXISTS contact_messages (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            user_id INT UNSIGNED NULL,
            fullname VARCHAR(120) NOT NULL,
            email VARCHAR(190) NOT NULL,
            message TEXT NOT NULL,
            is_read TINYINT(1) NOT NULL DEFAULT 0,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_contact_user (user_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
    );

    $contactColumns = [];
    $columns = $conn->query("SHOW COLUMNS FROM contact_messages");
    while ($column = $columns->fetch_assoc()) {
        $contactColumns[$column['Field']] = true;
    }

    if (!isset($contactColumns['is_read'])) {
        $conn->query(
            "ALTER TABLE contact_messages
             ADD COLUMN is_read TINYINT(1) NOT NULL DEFAULT 0"
        );
    }

    $conn->query(
        "CREATE TABLE IF NOT EXISTS assistant_logs (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            user_id INT UNSIGNED NULL,
            session_token VARCHAR(128) NOT NULL,
            user_message TEXT NOT NULL,
            bot_reply TEXT NOT NULL,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_assistant_user (user_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
    );
} catch (mysqli_sql_exception $exception) {
    error_log($exception->getMessage());
    http_response_code(500);
    exit("Database connection failed. Please try again later.");
}
