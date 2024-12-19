<?php 
session_start();
require_once '../config/database.php';
include '../includes/header.php'; 

if (!isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_URL . '/auth/login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $event_name = $_POST['event_name'];
    $event_date = $_POST['event_date'];
    $capacity = $_POST['capacity'];
    $location = $_POST['location'];
    $description = $_POST['description'];
    $category = $_POST['category'];
    $user_id = $_SESSION['user_id'];

    try {
        $stmt = $pdo->prepare("INSERT INTO events (title, description, date, location, capacity, category, user_id) 
                              VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$event_name, $description, $event_date, $location, $capacity, $category, $user_id]);
        
        // Handle image upload
        if(isset($_FILES['event_image']) && $_FILES['event_image']['error'] == 0) {
            $image_name = time() . '_' . $_FILES['event_image']['name'];
            $target_dir = "../uploads/events/";
            $target_file = $target_dir . $image_name;
            
            if (move_uploaded_file($_FILES['event_image']['tmp_name'], $target_file)) {
                $event_id = $pdo->lastInsertId();
                $stmt = $pdo->prepare("UPDATE events SET image_url = ? WHERE id = ?");
                $stmt->execute([$image_name, $event_id]);
            }
        }

        header("Location: " . BASE_URL);
        exit();
    } catch(PDOException $e) {
        $error = "Error: " . $e->getMessage();
    }
}
?>

<div class="container">
    <div class="create-event-container">
        <h1 class="page-title">Tambah Event Baru</h1>
        <form method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="event_name">Nama Event</label>
                <input type="text" id="event_name" name="event_name" placeholder="Masukkan Nama Event" required>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="event_date">Tanggal dan Waktu Event</label>
                    <input type="datetime-local" id="event_date" name="event_date" required>
                </div>

                <div class="form-group">
                    <label for="capacity">Maksimal Kapasitas Peserta</label>
                    <input type="number" id="capacity" name="capacity" min="1" placeholder="Jumlah Peserta" required>
                </div>
            </div>

            <div class="form-group">
                <label for="location">Lokasi Event</label>
                <input type="text" id="location" name="location" placeholder="Lokasi Even" required>
            </div>

            <div class="form-group">
                <label for="description">Deskripsi Event</label>
                <textarea id="description" name="description" rows="4" placeholder="Deskripsi Event" required></textarea>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="event_image">Gambar/Pamflet Event</label>
                    <input type="file" id="event_image" name="event_image" accept="image/*">
                </div>

                <div class="form-group">
                    <label for="category">Kategori Event</label>
                    <select id="category" name="category" required>
                        <option value="">Pilih Kategori</option>
                        <option value="conference">Conference</option>
                        <option value="workshop">Workshop</option>
                        <option value="seminar">Seminar</option>
                        <option value="networking">Networking</option>
                        <option value="other">Other</option>
                    </select>
                </div>
            </div>

            <button type="submit" class="btn btn-primary">Buat Event</button>
        </form>
    </div>
</div>

<style>
.create-event-container {
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
    margin-bottom: 40px;
    font-size: 2.2rem;
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
}

.form-group {
    margin-bottom: 25px;
}

.form-group label {
    display: block;
    margin-bottom: 8px;
    color: #555;
    font-weight: 500;
}

.form-group textarea {
    width: 100%;
    padding: 12px 15px;
    border: 2px solid #eee;
    border-radius: 8px;
    background: #f8f9fa;
    color: #333;
    transition: all 0.3s ease;
    resize: vertical;
    min-height: 100px;
    max-height: 300px;
}

.form-group input,
.form-group textarea,
.form-group select {
    width: 100%;
    padding: 12px 15px;
    border: 2px solid #eee;
    border-radius: 8px;
    background: #f8f9fa;
    color: #333;
    transition: all 0.3s ease;
}

.form-group input:focus,
.form-group textarea:focus,
.form-group select:focus {
    border-color: #667eea;
    background: #fff;
    outline: none;
    box-shadow: 0 0 0 3px rgba(102,126,234,0.1);
}

.form-group input::placeholder,
.form-group textarea::placeholder {
    color: #aaa;
}

.btn-primary {
    width: 100%;
    padding: 14px;
    background: var(--gradient-1);
    color: white;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    font-size: 1.1rem;
    font-weight: 600;
    transition: all 0.3s ease;
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(102,126,234,0.4);
}
</style>

<?php include '../includes/footer.php'; ?>