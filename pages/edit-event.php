<?php 
session_start();
require_once '../config/database.php';
include '../includes/header.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_URL . '/auth/login.php');
    exit();
}

// Ambil data event
if(isset($_GET['id'])) {
    $event_id = $_GET['id'];
    try {
        $stmt = $pdo->prepare("SELECT * FROM events WHERE id = ? AND user_id = ?");
        $stmt->execute([$event_id, $_SESSION['user_id']]);
        $event = $stmt->fetch();

        if(!$event) {
            header('Location: ' . BASE_URL . '/pages/manage-events.php');
            exit();
        }
    } catch(PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
} else {
    header('Location: ' . BASE_URL . '/pages/manage-events.php');
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $_POST['event_name'];
    $description = $_POST['description'];
    $date = $_POST['event_date'];
    $location = $_POST['location'];
    $capacity = $_POST['capacity'];
    $category = $_POST['category'];

    try {
        // Handle image upload if new image is selected
        if(isset($_FILES['event_image']) && $_FILES['event_image']['error'] == 0) {
            $image_name = time() . '_' . $_FILES['event_image']['name'];
            $target_dir = "../uploads/events/";
            $target_file = $target_dir . $image_name;
            
            if (move_uploaded_file($_FILES['event_image']['tmp_name'], $target_file)) {
                // Delete old image if exists
                if($event['image_url']) {
                    $old_image = $target_dir . $event['image_url'];
                    if(file_exists($old_image)) {
                        unlink($old_image);
                    }
                }
                
                // Update with new image
                $stmt = $pdo->prepare("UPDATE events SET title = ?, description = ?, date = ?, 
                                     location = ?, capacity = ?, category = ?, image_url = ? 
                                     WHERE id = ? AND user_id = ?");
                $stmt->execute([$title, $description, $date, $location, $capacity, 
                              $category, $image_name, $event_id, $_SESSION['user_id']]);
            }
        } else {
            // Update without changing image
            $stmt = $pdo->prepare("UPDATE events SET title = ?, description = ?, date = ?, 
                                 location = ?, capacity = ?, category = ? 
                                 WHERE id = ? AND user_id = ?");
            $stmt->execute([$title, $description, $date, $location, $capacity, 
                          $category, $event_id, $_SESSION['user_id']]);
        }

        header('Location: ' . BASE_URL . '/pages/manage-events.php');
        exit();
    } catch(PDOException $e) {
        $error = "Error: " . $e->getMessage();
    }
}
?>

<div class="container">
    <div class="edit-event-container">
        <h1 class="page-title">Edit Event</h1>
        <form method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="event_name">Event Name</label>
                <input type="text" id="event_name" name="event_name" 
                       value="<?php echo htmlspecialchars($event['title']); ?>" required>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="event_date">Event Date</label>
                    <input type="datetime-local" id="event_date" name="event_date" 
                           value="<?php echo date('Y-m-d\TH:i', strtotime($event['date'])); ?>" required>
                </div>

                <div class="form-group">
                    <label for="capacity">Maximum Capacity</label>
                    <input type="number" id="capacity" name="capacity" 
                           value="<?php echo htmlspecialchars($event['capacity']); ?>" required>
                </div>
            </div>

            <div class="form-group">
                <label for="location">Location</label>
                <input type="text" id="location" name="location" 
                       value="<?php echo htmlspecialchars($event['location']); ?>" required>
            </div>

            <div class="form-group">
                <label for="description">Description</label>
                <textarea id="description" name="description" rows="4" required><?php echo htmlspecialchars($event['description']); ?></textarea>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="event_image">Event Image</label>
                    <input type="file" id="event_image" name="event_image" accept="image/*">
                    <?php if($event['image_url']): ?>
                        <div class="current-image">
                            <img src="<?php echo BASE_URL; ?>/uploads/events/<?php echo $event['image_url']; ?>" 
                                 alt="Current event image" style="max-width: 200px;">
                        </div>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="category">Category</label>
                    <select id="category" name="category" required>
                        <option value="">Select Category</option>
                        <?php
                        $categories = ['conference', 'workshop', 'seminar', 'networking', 'other'];
                        foreach($categories as $cat):
                            $selected = ($event['category'] == $cat) ? 'selected' : '';
                        ?>
                            <option value="<?php echo $cat; ?>" <?php echo $selected; ?>>
                                <?php echo ucfirst($cat); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <button type="submit" class="btn btn-primary">Update Event</button>
        </form>
    </div>
</div>

<style>
.edit-event-container {
    max-width: 800px;
    margin: 40px auto;
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

.form-group textarea {
    resize: vertical;
    min-height: 100px;
    max-height: 300px;
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

.current-image {
    margin-top: 10px;
    padding: 10px;
    border: 1px solid #eee;
    border-radius: 8px;
    background: #f8f9fa;
}

.current-image img {
    max-width: 100%;
    height: auto;
    border-radius: 4px;
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

/* Style untuk input file */
input[type="file"] {
    padding: 10px;
    background: #f8f9fa;
    border: 2px dashed #ddd;
    border-radius: 8px;
    cursor: pointer;
}

input[type="file"]:hover {
    border-color: #667eea;
}

/* Style untuk datetime-local */
input[type="datetime-local"] {
    padding: 12px 15px;
    width: 100%;
}

/* Style untuk select */
select {
    appearance: none;
    -webkit-appearance: none;
    -moz-appearance: none;
    background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e");
    background-repeat: no-repeat;
    background-position: right 1rem center;
    background-size: 1em;
}
</style>

<?php include '../includes/footer.php'; ?>