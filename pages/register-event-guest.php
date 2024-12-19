<?php
session_start();
require_once '../config/database.php';
include '../includes/header.php';

if (!isset($_GET['id'])) {
    header('Location: ' . BASE_URL);
    exit();
}

$event_id = $_GET['id'];

try {
    // Ambil detail event
    $stmt = $pdo->prepare("SELECT e.*, u.username 
                          FROM events e 
                          JOIN users u ON e.user_id = u.id 
                          WHERE e.id = ?");
    $stmt->execute([$event_id]);
    $event = $stmt->fetch();

    if (!$event) {
        header('Location: ' . BASE_URL);
        exit();
    }

    // Cek jumlah pendaftar saat ini
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM event_registrations WHERE event_id = ?");
    $stmt->execute([$event_id]);
    $currentRegistrations = $stmt->fetch()['total'];

    // Handle form submission
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $name = $_POST['name'];
        $email = $_POST['email'];
        $phone = $_POST['phone'];

        // Validasi kapasitas
        if ($currentRegistrations >= $event['capacity']) {
            $error = "Sorry, this event is fully booked.";
        } else {
            // Insert guest registration
            $stmt = $pdo->prepare("INSERT INTO guest_registrations (event_id, name, email, phone, status, created_at) 
                                 VALUES (?, ?, ?, ?, 'registered', NOW())");
            if ($stmt->execute([$event_id, $name, $email, $phone])) {
                $_SESSION['success_message'] = "Registration successful! We'll send you the details via email.";
                header("Location: " . BASE_URL . "/pages/event-detail.php?id=" . $event_id);
                exit();
            } else {
                $error = "Registration failed. Please try again.";
            }
        }
    }

} catch(PDOException $e) {
    $error = "Error: " . $e->getMessage();
}
?>

<div class="container">
    <div class="registration-container">
        <h1 class="page-title">Pendaftaran Event</h1>
        
        <div class="event-summary">
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

            <div class="event-details">
                <h2><?php echo htmlspecialchars($event['title']); ?></h2>
                <div class="event-info">
                    <p><i class="fas fa-calendar"></i> <?php echo date('l, d F Y - H:i', strtotime($event['date'])); ?></p>
                    <p><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($event['location']); ?></p>
                    <p><i class="fas fa-user"></i> Organized by <?php echo htmlspecialchars($event['username']); ?></p>
                    <p><i class="fas fa-users"></i> Kuota yang tersedia: <?php echo $event['capacity'] - $currentRegistrations; ?> dari <?php echo $event['capacity']; ?></p>
                </div>
            </div>
        </div>

        <?php if(isset($error)): ?>
            <div class="alert alert-error">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <div class="registration-form">
            <h3>Pendaftaran Tamu</h3>
            <form method="POST" class="guest-form">
                <div class="form-group">
                    <label for="name">Nama Lengkap *</label>
                    <input type="text" id="name" name="name" required 
                           value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>">
                </div>

                <div class="form-group">
                    <label for="email">Alamat Email *</label>
                    <input type="email" id="email" name="email" required 
                           value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                </div>

                <div class="form-group">
                    <label for="phone">Nomor Hp/WA *</label>
                    <input type="tel" id="phone" name="phone" required 
                           value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>">
                </div>

                <button type="submit" class="btn btn-register">Daftar untuk Event</button>
            </form>
        </div>
    </div>
</div>

<style>
.registration-container {
    max-width: 800px;
    margin: 100px auto 40px;
    padding: 40px;
    background: #ffffff;
    border-radius: 15px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.1);
}

.page-title {
    text-align: center;
    color: #333;
    margin-bottom: 30px;
    font-size: 2.2rem;
}

.event-summary {
    display: grid;
    grid-template-columns: 300px 1fr;
    gap: 30px;
    margin-bottom: 40px;
    padding-bottom: 30px;
    border-bottom: 1px solid #eee;
}

.event-image-container {
    width: 100%;
    border-radius: 10px;
    overflow: hidden;
}

.event-image {
    width: 100%;
    height: auto;
    display: block;
}

.event-details h2 {
    color: #333;
    margin-bottom: 20px;
}

.event-info p {
    margin: 10px 0;
    color: #666;
}

.guest-form {
    max-width: 500px;
    margin: 0 auto;
}

.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    margin-bottom: 8px;
    color: #555;
    font-weight: 500;
}

.form-group input {
    width: 100%;
    padding: 12px;
    border: 2px solid #eee;
    border-radius: 8px;
    font-size: 1rem;
}

.form-group input:focus {
    border-color: #667eea;
    outline: none;
}

.btn-register {
    width: 100%;
    padding: 15px;
    background: var(--gradient-1);
    color: white;
    border: none;
    border-radius: 8px;
    font-size: 1.1rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
}

.btn-register:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(102,126,234,0.4);
}

.alert {
    padding: 15px;
    border-radius: 8px;
    margin-bottom: 20px;
    text-align: center;
}

.alert-error {
    background: #fee2e2;
    color: #dc2626;
    border: 1px solid #fecaca;
}

@media (max-width: 768px) {
    .registration-container {
        margin: 20px;
        padding: 20px;
    }

    .event-summary {
        grid-template-columns: 1fr;
    }
}
</style>

<?php include '../includes/footer.php'; ?>