<?php

require_once __DIR__ . '/includes/app.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false]);
    exit;
}

$payload = json_decode(file_get_contents('php://input'), true);
$message = trim((string) ($payload['message'] ?? ''));
$reply = trim((string) ($payload['reply'] ?? ''));

if ($message === '' || $reply === '') {
    http_response_code(422);
    echo json_encode(['ok' => false]);
    exit;
}

try {
    $user = current_user();
    $userId = $user ? (int) $user['id'] : null;
    $sessionToken = session_id();
    $stmt = $conn->prepare(
        "INSERT INTO assistant_logs (user_id, session_token, user_message, bot_reply)
         VALUES (?, ?, ?, ?)"
    );
    $stmt->bind_param("isss", $userId, $sessionToken, $message, $reply);
    $stmt->execute();
    $stmt->close();

    echo json_encode(['ok' => true]);
} catch (Throwable $exception) {
    error_log($exception->getMessage());
    http_response_code(500);
    echo json_encode(['ok' => false]);
}
