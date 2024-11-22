<?php
require '../../../database/connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = intval($_POST['user_id']);
    $date = $_POST['date'];

    // Validate input
    if (empty($userId) || empty($date)) {
        echo json_encode(['success' => false, 'message' => 'Invalid input.']);
        exit;
    }

    // Validate date format (ensure it's a valid DATETIME)
    $datetime = DateTime::createFromFormat('Y-m-d H:i:s', $date);
    if (!$datetime || $datetime->format('Y-m-d H:i:s') !== $date) {
        echo json_encode(['success' => false, 'message' => 'Invalid date format.']);
        exit;
    }

    try {
        // Begin transaction
        $conn->beginTransaction();

        // Insert or update interview schedule
        $stmt = $conn->prepare("
            INSERT INTO interview_schedule (user_id, interview_date, status) 
            VALUES (:user_id, :date, 'pending') 
            ON DUPLICATE KEY UPDATE interview_date = VALUES(interview_date), status = 'pending'
        ");
        $stmt->execute(['user_id' => $userId, 'date' => $date]);

        // Update user's scholarship status
        $stmt = $conn->prepare("
            UPDATE users 
            SET scholarship_status = 'interview_scheduled' 
            WHERE id = :user_id
        ");
        $stmt->execute(['user_id' => $userId]);

        // Commit transaction
        $conn->commit();

        echo json_encode(['success' => true, 'message' => 'Interview scheduled successfully.']);
    } catch (PDOException $e) {
        // Rollback transaction on failure
        $conn->rollBack();
        error_log("Database error: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}
