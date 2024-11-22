<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

require '../../database/connection.php';

$sender_id = $_SESSION['user_id'];
$thread_id = intval($_POST['thread_id']);
$message = htmlspecialchars($_POST['message']);

try {
    $stmt = $conn->prepare("INSERT INTO chats (thread_id, sender_id, message) VALUES (:thread_id, :sender_id, :message)");
    $stmt->execute([
        'thread_id' => $thread_id,
        'sender_id' => $sender_id,
        'message' => $message
    ]);

    echo json_encode(['status' => 'success', 'message' => 'Message sent']);
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
