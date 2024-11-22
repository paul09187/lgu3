<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

require '../../database/connection.php';

$thread_id = intval($_GET['thread_id']); // Thread ID

try {
    $stmt = $conn->prepare("
        SELECT c.*, u.name, u.role 
        FROM chats c 
        JOIN users u ON c.sender_id = u.id 
        WHERE c.thread_id = :thread_id
        ORDER BY c.created_at ASC
    ");
    $stmt->execute(['thread_id' => $thread_id]);

    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['status' => 'success', 'messages' => $messages]);
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
