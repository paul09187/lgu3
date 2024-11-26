<?php
require '../../../database/connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = intval($_POST['user_id']);
    $date = $_POST['date'];

    if (empty($userId) || empty($date)) {
        echo json_encode(['success' => false, 'message' => 'Invalid input.']);
        exit;
    }

    // Check slots availability
    $slotStmt = $conn->prepare("SELECT available_slots FROM interview_slots LIMIT 1");
    $slotStmt->execute();
    $slotData = $slotStmt->fetch(PDO::FETCH_ASSOC);
    if ($slotData['available_slots'] <= 0) {
        echo json_encode(['success' => false, 'message' => 'No interview slots available.']);
        exit;
    }

    // Validate date format
    $datetime = DateTime::createFromFormat('Y-m-d H:i:s', $date);
    if (!$datetime || $datetime->format('Y-m-d H:i:s') !== $date) {
        echo json_encode(['success' => false, 'message' => 'Invalid date format.']);
        exit;
    }

    try {
        $conn->beginTransaction();

        // Insert or update interview schedule
        $stmt = $conn->prepare("
            INSERT INTO interview_schedule (user_id, interview_date, status) 
            VALUES (:user_id, :date, 'pending') 
            ON DUPLICATE KEY UPDATE interview_date = VALUES(interview_date), status = 'pending'
        ");
        $stmt->execute(['user_id' => $userId, 'date' => $date]);

        // Decrease available slots
        $updateSlotStmt = $conn->prepare("UPDATE interview_slots SET available_slots = available_slots - 1 WHERE available_slots > 0");
        $updateSlotStmt->execute();

        // Update user's scholarship status
        $updateUserStmt = $conn->prepare("UPDATE users SET scholarship_status = 'interview_scheduled' WHERE id = :user_id");
        $updateUserStmt->execute(['user_id' => $userId]);

        $conn->commit();

        echo json_encode(['success' => true, 'message' => 'Interview scheduled successfully.']);
    } catch (PDOException $e) {
        $conn->rollBack();
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}
