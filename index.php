<?php 
session_start();
require_once 'config/database.php';
include 'includes/header.php'; 

// Ambil semua event dari database termasuk gambar
try {
    $stmt = $pdo->query("SELECT events.*, users.username, events.image_url 
                         FROM events 
                         JOIN users ON events.user_id = users.id 
                         ORDER BY events.created_at DESC");
    $events = $stmt->fetchAll();
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>

<div class="main-content">
    <section class="hero">
        <div class="hero-content">
            <h1>Buat Event Yang Berkesan</h1>
            <p>Promosikan Eventmu dan Buat Eventmu Menjadi Berkesan</p>
            <?php if(isset($_SESSION['user_id'])): ?>
                <a href="<?php echo BASE_URL; ?>/pages/create-event.php" class="btn btn-primary">Tamah Event</a>
            <?php else: ?>
                <a href="<?php echo BASE_URL; ?>/auth/login.php" class="btn btn-primary">Get Started</a>
            <?php endif; ?>
        </div>
    </section>

    <section class="events-section">
        <div class="container">
            <h2 class="section-title">Event-Event</h2>
            <div class="events-grid">
                <?php foreach($events as $event): ?>
                    <div class="event-card">
                        <?php if($event['image_url']): ?>
                            <img src="<?php echo BASE_URL; ?>/uploads/events/<?php echo $event['image_url']; ?>" alt="<?php echo htmlspecialchars($event['title']); ?>" class="event-image">
                        <?php else: ?>
                            <img src="<?php echo BASE_URL; ?>/assets/img/event-placeholder.jpg" alt="Event" class="event-image">
                        <?php endif; ?>
                        <div class="event-content">
                            <h3><?php echo htmlspecialchars($event['title']); ?></h3>
                            <p class="event-details">
                                <span><i class="fas fa-calendar"></i> <?php echo date('d M Y', strtotime($event['date'])); ?></span>
                                <span><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($event['location']); ?></span>
                            </p>
                            <p class="event-description"><?php echo substr(htmlspecialchars($event['description']), 0, 100); ?>...</p>
                            <div class="event-footer">
                                <span class="event-category"><?php echo htmlspecialchars($event['category']); ?></span>
                                <a href="<?php echo BASE_URL; ?>/pages/event-detail.php?id=<?php echo $event['id']; ?>" class="btn btn-secondary">Info Lebih Lanjut</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
</div>

<style>
.hero-content h1 {
    font-family: 'Prompt', sans-serif;
    font-size: 3rem;
    font-weight: 700;
    color: #fff;
    margin-bottom: 20px;
    line-height: 1.2;
}

.hero-content p {
    font-family: 'Prompt', sans-serif;
    font-size: 1.2rem;
    font-weight: 300;
    color: rgba(255, 255, 255, 0.9);
    margin-bottom: 30px;
    line-height: 1.6;
}
.events-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 30px;
    padding: 20px 0;
}

.event-card {
    background: #fff;
    border-radius: 15px;
    overflow: hidden;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    transition: transform 0.3s ease;
}

.event-card:hover {
    transform: translateY(-5px);
}

.event-image {
    width: 100%;
    height: 200px;
    object-fit: cover;
}

.event-content {
    padding: 20px;
}

.event-content h3 {
    margin: 0 0 10px 0;
    color: #333;
    font-size: 1.4rem;
}

.event-details {
    display: flex;
    gap: 15px;
    margin-bottom: 15px;
    color: #666;
    font-size: 0.9rem;
}

.event-details span {
    display: flex;
    align-items: center;
    gap: 5px;
}

.event-description {
    color: #666;
    margin-bottom: 15px;
    line-height: 1.5;
}

.event-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.event-category {
    background: #f0f0f0;
    padding: 5px 10px;
    border-radius: 15px;
    font-size: 0.9rem;
    color: #555;
}

.btn-secondary {
    background: var(--gradient-2);
    color: white;
    padding: 8px 15px;
    border-radius: 5px;
    text-decoration: none;
    font-size: 0.9rem;
    transition: all 0.3s ease;
}

.btn-secondary:hover {
    opacity: 0.9;
    transform: translateY(-2px);
}
</style>

<?php include 'includes/footer.php'; ?>