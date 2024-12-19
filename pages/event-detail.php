<?php 
session_start();
require_once '../config/database.php';
include '../includes/header.php';

if(isset($_GET['id'])) {
    $event_id = $_GET['id'];
    try {
        $stmt = $pdo->prepare("SELECT e.*, u.username 
                              FROM events e 
                              JOIN users u ON e.user_id = u.id 
                              WHERE e.id = ?");
        $stmt->execute([$event_id]);
        $event = $stmt->fetch();

        // Get current registrations count
        $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM event_registrations WHERE event_id = ?");
        $stmt->execute([$event_id]);
        $registeredUsers = $stmt->fetch()['total'];

        // Get guest registrations count
        $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM guest_registrations WHERE event_id = ?");
        $stmt->execute([$event_id]);
        $registeredGuests = $stmt->fetch()['total'];

        $totalRegistrations = $registeredUsers + $registeredGuests;

        if(!$event) {
            header('Location: ' . BASE_URL);
            exit();
        }
    } catch(PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
} else {
    header('Location: ' . BASE_URL);
    exit();
}
?>

<div class="container">
    <div class="event-detail-container">
        <div class="event-header">
            <h1><?php echo htmlspecialchars($event['title']); ?></h1>
            <div class="event-meta">
                <span><i class="fas fa-user"></i> Organized by <?php echo htmlspecialchars($event['username']); ?></span>
                <span><i class="fas fa-calendar"></i> <?php echo date('l, d F Y - H:i', strtotime($event['date'])); ?></span>
                <span><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($event['location']); ?></span>
                <span><i class="fas fa-users"></i> Kuota yang tersedia: <?php echo $event['capacity'] - $totalRegistrations; ?> dari <?php echo $event['capacity']; ?></span>
                <span class="event-category"><i class="fas fa-tag"></i> <?php echo htmlspecialchars($event['category']); ?></span>
            </div>
        </div>

        <div class="event-content">
            <div class="event-image-container">
                <?php if($event['image_url']): ?>
                    <img src="<?php echo BASE_URL; ?>/uploads/events/<?php echo htmlspecialchars($event['image_url']); ?>" 
                         alt="<?php echo htmlspecialchars($event['title']); ?>" 
                         class="event-image">
                <?php else: ?>
                    <img src="<?php echo BASE_URL; ?>/assets/img/event-placeholder.jpg" 
                         alt="Default Event Image" 
                         class="event-image">
                <?php endif; ?>
            </div>

            <div class="event-description">
                <h2>Deskripsi Event</h2>
                <p><?php echo nl2br(htmlspecialchars($event['description'])); ?></p>
            </div>

            <div class="event-actions">
                <?php if(isset($_SESSION['user_id'])): ?>
                    <?php 
                    // Cek apakah user sudah mendaftar
                    $stmt = $pdo->prepare("SELECT * FROM event_registrations WHERE event_id = ? AND user_id = ?");
                    $stmt->execute([$event_id, $_SESSION['user_id']]);
                    $isRegistered = $stmt->fetch();
                    ?>
                    
                    <?php if($isRegistered): ?>
                        <button class="btn btn-registered" disabled>
                            <i class="fas fa-check"></i> Terdaftar
                        </button>
                    <?php elseif($totalRegistrations >= $event['capacity']): ?>
                        <button class="btn btn-full" disabled>
                            Event Full
                        </button>
                    <?php else: ?>
                        <a href="<?php echo BASE_URL; ?>/pages/register-event-guest.php?id=<?php echo $event_id; ?>" 
                           class="btn btn-register">Daftar Sekarang</a>
                    <?php endif; ?>
                    
                    <?php if($_SESSION['user_id'] == $event['user_id']): ?>
                        <a href="<?php echo BASE_URL; ?>/pages/guest-list.php?id=<?php echo $event['id']; ?>" 
                           class="btn btn-guest-list">Kelola Daftar Tamu</a>
                        <a href="<?php echo BASE_URL; ?>/pages/edit-event.php?id=<?php echo $event['id']; ?>" 
                           class="btn btn-edit">Edit Event</a>
                        <button onclick="deleteEvent(<?php echo $event['id']; ?>)" 
                                class="btn btn-delete">Delete Event</button>
                    <?php endif; ?>
                <?php else: ?>
                    <?php if($totalRegistrations >= $event['capacity']): ?>
                        <button class="btn btn-full" disabled>Event Full</button>
                    <?php else: ?>
                        <a href="<?php echo BASE_URL; ?>/pages/register-event-guest.php?id=<?php echo $event_id; ?>" 
                           class="btn btn-register">Daftar Sebagai Tamu</a>
                        <a href="<?php echo BASE_URL; ?>/auth/login.php" 
                           class="btn btn-login">Masuk untuk Mendaftar</a>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<style>
.event-detail-container {
    max-width: 1000px;
    margin: 100px auto 40px;
    padding: 40px;
    background: #ffffff;
    border-radius: 15px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.1);
}

.event-header {
    margin-bottom: 30px;
    text-align: center;
}

.event-header h1 {
    font-size: 2.5rem;
    color: #333;
    margin-bottom: 20px;
}

.event-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 20px;
    justify-content: center;
    color: #666;
    font-size: 1.1rem;
}

.event-meta span {
    display: flex;
    align-items: center;
    gap: 8px;
}

.event-meta i {
    color: #667eea;
}

.event-category {
    background: #f0f0f0;
    padding: 5px 15px;
    border-radius: 20px;
}

.event-content {
    margin-top: 30px;
}

.event-image-container {
    margin-bottom: 30px;
    border-radius: 15px;
    overflow: hidden;
    box-shadow: 0 3px 10px rgba(0,0,0,0.1);
    text-align: center;
    background: #f8f9fa;
    padding: 20px;
}

.event-image {
    max-width: 100%;
    height: auto;
    display: inline-block;
    border-radius: 10px;
    object-fit: contain;
}

.event-description {
    color: #444;
    line-height: 1.8;
    margin-bottom: 30px;
}

.event-description h2 {
    color: #333;
    margin-bottom: 20px;
    font-size: 1.8rem;
}

.event-actions {
    display: flex;
    gap: 15px;
    justify-content: center;
    flex-wrap: wrap;
}

.btn {
    padding: 12px 25px;
    border-radius: 8px;
    border: none;
    cursor: pointer;
    font-size: 1rem;
    font-weight: 600;
    transition: all 0.3s ease;
    text-decoration: none;
}

.btn-register {
    background: var(--gradient-1);
    color: white;
}

.btn-login {
    background: #6c757d;
    color: white;
}

.btn-registered {
    background: #28a745;
    color: white;
    cursor: default;
}

.btn-full {
    background: #dc3545;
    color: white;
    cursor: not-allowed;
}

.btn-guest-list {
    background: #17a2b8;
    color: white;
}

.btn-edit {
    background: #4CAF50;
    color: white;
}

.btn-delete {
    background: #f44336;
    color: white;
}

.btn:hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.2);
}

@media (max-width: 768px) {
    .event-detail-container {
        padding: 20px;
        margin: 20px;
    }

    .event-header h1 {
        font-size: 2rem;
    }

    .event-meta {
        font-size: 1rem;
    }

    .event-image-container {
        padding: 10px;
    }

    .event-actions {
        flex-direction: column;
    }

    .btn {
        width: 100%;
        text-align: center;
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
                window.location.href = '<?php echo BASE_URL; ?>/pages/manage-events.php';
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