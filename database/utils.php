<?php
function logAudit($userId, $action, $details = null)
{
    global $conn; // Use the existing database connection
    if (!$conn) {
        throw new Exception("Database connection not found.");
    }

    $stmt = $conn->prepare("
        INSERT INTO audit_logs (user_id, action, details) 
        VALUES (:user_id, :action, :details)
    ");
    $stmt->execute([
        'user_id' => $userId,
        'action' => $action,
        'details' => $details
    ]);
}
?>
