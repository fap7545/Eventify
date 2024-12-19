<?php 
session_start();
require_once '../config/database.php';
include '../includes/header.php';

// Cek login
if (!isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_URL . '/auth/login.php');
    exit();
}

try {
    // Ambil semua event yang dimiliki user
    $stmt = $pdo->prepare("SELECT * FROM events WHERE user_id = ? ORDER BY date DESC");
    $stmt->execute([$_SESSION['user_id']]);
    $events = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
    $events = [];
}
?>

<div class="container">
    <div class="guest-list-container">
        <h1>Daftar Tamu Event Saya</h1>
        
        <?php if (!empty($events)): ?>
            <div class="events-list">
                <?php foreach ($events as $event): ?>
                    <div class="event-card">
                        <!-- Tambahkan gambar event -->
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
                            <p class="event-date">
                                <i class="fas fa-calendar"></i> 
                                <?php echo date('d M Y H:i', strtotime($event['date'])); ?>
                            </p>
                            <p class="event-location">
                                <i class="fas fa-map-marker-alt"></i> 
                                <?php echo htmlspecialchars($event['location']); ?>
                            </p>
                            
                            <?php
                            // Hitung total registrasi
                            $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM event_registrations WHERE event_id = ?");
                            $stmt->execute([$event['id']]);
                            $registeredUsers = $stmt->fetch()['total'];
                            
                            $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM guest_registrations WHERE event_id = ?");
                            $stmt->execute([$event['id']]);
                            $guestUsers = $stmt->fetch()['total'];
                            ?>
                            
                            <div class="registration-stats">
                                <p>Pengguna Terdaftar : <?php echo $registeredUsers; ?></p>
                                <p>Pendaftaran Tamu : <?php echo $guestUsers; ?></p>
                                <p>Kuota yang Tersisa : <?php echo $event['capacity'] - ($registeredUsers + $guestUsers); ?></p>
                            </div>
                            
                            <a href="<?php echo BASE_URL; ?>/pages/view-guest-list.php?id=<?php echo $event['id']; ?>" 
                               class="btn btn-view">Lihat Daftar Tamu</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="no-events">
                <p>Anda Belum Membuat Event Apapun</p>
                <a href="<?php echo BASE_URL; ?>/pages/create-event.php" class="btn btn-create">Create Event</a>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
.guest-list-container {
    max-width: 1200px;
    margin: 100px auto 40px;
    padding: 20px;
    background: #222; /* Ubah warna background menjadi hitam */
    border-radius: 15px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.3);
}

.guest-list-container h1 {
    color: #fff; /* Ubah warna text menjadi putih */
    text-align: center;
    margin-bottom: 30px;
}

.events-list {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 20px;
}

.event-card {
    background: #333; /* Warna card lebih terang dari container */
    border-radius: 10px;
    overflow: hidden;
    box-shadow: 0 2px 10px rgba(0,0,0,0.2);
}

.event-image {
    width: 100%;
    height: 200px;
    overflow: hidden;
}

.event-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s ease;
}

.event-card:hover .event-image img {
    transform: scale(1.05);
}

.event-info {
    padding: 20px;
}

.event-info h3 {
    color: #fff;
    margin: 0 0 15px 0;
}

.event-date, .event-location {
    color: #ccc;
    margin: 5px 0;
    display: flex;
    align-items: center;
    gap: 8px;
}

.registration-stats {
    background: #444;
    padding: 15px;
    border-radius: 8px;
    margin: 15px 0;
}

.registration-stats p {
    color: #fff;
    margin: 5px 0;
}

.btn {
    display: inline-block;
    padding: 10px 20px;
    border-radius: 5px;
    text-decoration: none;
    font-weight: 500;
    text-align: center;
    transition: all 0.3s ease;
}

.btn-view {
    background: var(--gradient-1);
    color: white;
    width: 100%;
    margin-top: 15px;
}

.btn-create {
    background: var(--gradient-1);
    color: white;
}

.no-events {
    text-align: center;
    padding: 50px;
    background: #333;
    border-radius: 10px;
    color: #fff;
}

@media (max-width: 768px) {
    .guest-list-container {
        margin: 80px 20px 40px;
    }

    .events-list {
        grid-template-columns: 1fr;
    }
}
</style>

<?php include '../includes/footer.php'; ?>