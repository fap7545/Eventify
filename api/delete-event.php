<?php
session_start();
require_once '../config/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);
$event_id = $data['event_id'] ?? null;

if (!$event_id) {
    echo json_encode(['success' => false, 'message' => 'Event ID is required']);
    exit();
}

try {
    // Get event details first (for image deletion)
    $stmt = $pdo->prepare("SELECT image_url FROM events WHERE id = ? AND user_id = ?");
    $stmt->execute([$event_id, $_SESSION['user_id']]);
    $event = $stmt->fetch();

    if ($event) {
        // Delete image if exists
        if ($event['image_url']) {
            $image_path = "../uploads/events/" . $event['image_url'];
            if (file_exists($image_path)) {
                unlink($image_path);
            }
        }

        // Delete event from database
        $stmt = $pdo->prepare("DELETE FROM events WHERE id = ? AND user_id = ?");
        $stmt->execute([$event_id, $_SESSION['user_id']]);

        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Event not found or unauthorized']);
    }
} catch(PDOException $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>