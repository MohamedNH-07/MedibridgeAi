<?php

require_once __DIR__ . '/includes/app.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: index.php#contact");
    exit;
}

$fullname = trim($_POST['fullname'] ?? '');
$email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
$message = trim($_POST['message'] ?? '');

if ($fullname === '' || !$email || $message === '') {
    header("Location: index.php?contact=failed#contact");
    exit;
}

try {
    $user = current_user();
    $userId = null;
    if ($user) {
        $userId = (int) $user['id'];
    }

    $stmt = $conn->prepare(
        "INSERT INTO contact_messages (user_id, fullname, email, message)
         VALUES (?, ?, ?, ?)"
    );
    $stmt->bind_param("isss", $userId, $fullname, $email, $message);
    $stmt->execute();
    $stmt->close();

    header("Location: index.php?contact=saved#contact");
    exit;
} catch (Throwable $exception) {
    error_log($exception->getMessage());
    header("Location: index.php?contact=failed#contact");
    exit;
}
