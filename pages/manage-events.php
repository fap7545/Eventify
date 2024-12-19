<?php
session_start();
require_once '../config/database.php';
include '../includes/header.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_URL . '/auth/login.php');
    exit();
}

try {
    $stmt = $pdo->prepare("SELECT * FROM events WHERE user_id = ? ORDER BY created_at DESC");
    $stmt->execute([$_SESSION['user_id']]);
    $events = $stmt->fetchAll();
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
    $events = [];
}
?>

<div class="container">
    <h1>Manage Events</h1>
    
    <div class="events-grid">
        <?php if(!empty($events)): ?>
            <?php foreach($events as $event): ?>
                <div class="event-card">
                    <div class="event-image">
                        <?php if($event['image_url']): ?>
                            <img src="<?php echo BASE_URL; ?>/uploads/events/<?php echo htmlspecialchars($event['image_url']); ?>" 
                                 alt="<?php echo htmlspecialchars($event['title']); ?>">
                        <?php else: ?>
                            <img src="<?php echo BASE_URL; ?>/assets/img/event-placeholder.jpg" 
                                 alt="Default Event Image">
                        <?php endif; ?>
                    </div>
                    <div class="event-info">
                        <h3><?php echo htmlspecialchars($event['title']); ?></h3>
                        <div class="event-meta">
                            <span><i class="fas fa-calendar"></i> <?php echo date('d M Y', strtotime($event['date'])); ?></span>
                            <span><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($event['location']); ?></span>
                        </div>
                        <div class="event-actions">
                            <a href="<?php echo BASE_URL; ?>/pages/guest-list.php?id=<?php echo $event['id']; ?>" 
                               class="btn btn-guest">
                                <i class="fas fa-users"></i> Daftar Tamu
                            </a>
                            <a href="<?php echo BASE_URL; ?>/pages/edit-event.php?id=<?php echo $event['id']; ?>" 
                               class="btn btn-edit">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                            <button onclick="deleteEvent(<?php echo $event['id']; ?>)" 
                                    class="btn btn-delete">
                                <i class="fas fa-trash"></i> Delete
                            </button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="no-events">You haven't created any events yet.</p>
        <?php endif; ?>
    </div>
</div>

<style>
.container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
}

.events-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 30px;
    margin-top: 20px;
}

.event-card {
    background: white;
    border-radius: 10px;
    overflow: hidden;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.event-image img {
    width: 100%;
    height: 200px;
    object-fit: cover;
}

.event-info {
    padding: 20px;
}

.event-meta {
    margin: 10px 0;
    color: #666;
}

.event-actions {
    display: flex;
    gap: 10px;
    margin-top: 15px;
}

.btn {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 8px 15px;
    border-radius: 5px;
    text-decoration: none;
    font-weight: 500;
    cursor: pointer;
    border: none;
    transition: all 0.3s ease;
}

.btn-guest {
    background: #17a2b8;
    color: white;
}

.btn-edit {
    background: #28a745;
    color: white;
}

.btn-delete {
    background: #dc3545;
    color: white;
}

.btn:hover {
    opacity: 0.9;
    transform: translateY(-2px);
}

.no-events {
    grid-column: 1 / -1;
    text-align: center;
    padding: 50px;
    background: #f8f9fa;
    border-radius: 10px;
    color: #666;
}

@media (max-width: 768px) {
    .event-actions {
        flex-direction: column;
    }
    
    .btn {
        width: 100%;
        justify-content: center;
    }
}
</style>

<script>
function deleteEvent(eventId) {
    if(confirm('Are you sure you want to delete this event?')) {
        fetch('<?php echo BASE_URL; ?>/api/delete-event.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                event_id: eventId
            })
        })
        .then(response => response.json())
        .then(data => {
            if(data.success) {
                location.reload();
            } else {
                alert('Error deleting event: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error deleting event');
        });
    }
}
</script>

<?php include '../includes/footer.php'; ?>