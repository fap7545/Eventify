<?php
session_start();
require_once '../config/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please login first']);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);
$event_id = $data['event_id'] ?? null;

if (!$event_id) {
    echo json_encode(['success' => false, 'message' => 'Event ID is required']);
    exit();
}

try {
    // Cek kapasitas event
    $stmt = $pdo->prepare("SELECT capacity, 
                          (SELECT COUNT(*) FROM event_registrations WHERE event_id = events.id) as current_registrations 
                          FROM events WHERE id = ?");
    $stmt->execute([$event_id]);
    $event = $stmt->fetch();

    if ($event['current_registrations'] >= $event['capacity']) {
        echo json_encode(['success' => false, 'message' => 'Event is fully booked']);
        exit();
    }

    // Cek apakah sudah terdaftar
    $stmt = $pdo->prepare("SELECT id FROM event_registrations WHERE event_id = ? AND user_id = ?");
    $stmt->execute([$event_id, $_SESSION['user_id']]);
    if ($stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Already registered']);
        exit();
    }

    // Daftarkan user
    $stmt = $pdo->prepare("INSERT INTO event_registrations (event_id, user_id, status) 
                          VALUES (?, ?, 'registered')");
    $stmt->execute([$event_id, $_SESSION['user_id']]);

    echo json_encode(['success' => true]);
} catch(PDOException $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>