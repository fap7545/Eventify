<?php
session_start();
require_once '../config/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);
$guest_id = $data['guest_id'] ?? null;
$status = $data['status'] ?? null;

if (!$guest_id || !$status) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit();
}

try {
    // Verifikasi kepemilikan event
    $stmt = $pdo->prepare("SELECT e.user_id 
                          FROM guest_registrations g 
                          JOIN events e ON g.event_id = e.id 
                          WHERE g.id = ?");
    $stmt->execute([$guest_id]);
    $event = $stmt->fetch();

    if ($event['user_id'] != $_SESSION['user_id']) {
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
        exit();
    }

    // Update status
    $stmt = $pdo->prepare("UPDATE guest_registrations SET status = ? WHERE id = ?");
    $stmt->execute([$status, $guest_id]);

    echo json_encode(['success' => true]);
} catch(PDOException $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>