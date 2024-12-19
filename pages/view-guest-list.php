<?php 
session_start();
require_once '../config/database.php';
include '../includes/header.php';

if (!isset($_SESSION['user_id']) || !isset($_GET['id'])) {
    header('Location: ' . BASE_URL);
    exit();
}

$event_id = $_GET['id'];

try {
    // Ambil detail event
    $stmt = $pdo->prepare("SELECT * FROM events WHERE id = ? AND user_id = ?");
    $stmt->execute([$event_id, $_SESSION['user_id']]);
    $event = $stmt->fetch();

    if (!$event) {
        header('Location: ' . BASE_URL);
        exit();
    }

    // Ambil daftar tamu dari guest_registrations
    $stmt = $pdo->prepare("SELECT * FROM guest_registrations WHERE event_id = ? ORDER BY created_at DESC");
    $stmt->execute([$event_id]);
    $guests = $stmt->fetchAll();

} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
    exit();
}
?>

<div class="container">
    <div class="guest-list-container">
        <h1>Daftar Tamu: <?php echo htmlspecialchars($event['title']); ?></h1>
        
        <div class="stats-container">
            <div class="stat-box">
                <h3>Total Kapasitas</h3>
                <p><?php echo $event['capacity']; ?></p>
            </div>
            <div class="stat-box">
                <h3>Tamu Terdaftar</h3>
                <p><?php echo count($guests); ?></p>
            </div>
            <div class="stat-box">
                <h3>Kuota yang Tersedia</h3>
                <p><?php echo $event['capacity'] - count($guests); ?></p>
            </div>
        </div>

        <!-- Guest List Table -->
        <div class="guest-section">
            <h2>Tamu Terdaftar</h2>
            <?php if (!empty($guests)): ?>
                <div class="table-responsive">
                    <table class="guest-table">
                        <thead>
                            <tr>
                                <th>Nama</th>
                                <th>Email</th>
                                <th>No Hp/Wa</th>
                                <th>Status</th>
                                <th>Tanggal Pendaftaran</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($guests as $guest): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($guest['name']); ?></td>
                                    <td><?php echo htmlspecialchars($guest['email']); ?></td>
                                    <td><?php echo htmlspecialchars($guest['phone']); ?></td>
                                    <td>
                                        <span class="status-badge <?php echo $guest['status']; ?>">
                                            <?php echo ucfirst($guest['status']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('d M Y H:i', strtotime($guest['created_at'])); ?></td>
                                    <td>
                                        <select onchange="updateStatus(<?php echo $guest['id']; ?>, this.value)" 
                                                class="status-select">
                                            <option value="registered" <?php echo $guest['status'] == 'registered' ? 'selected' : ''; ?>>
                                                Registered
                                            </option>
                                            <option value="attended" <?php echo $guest['status'] == 'attended' ? 'selected' : ''; ?>>
                                                Attended
                                            </option>
                                            <option value="absent" <?php echo $guest['status'] == 'absent' ? 'selected' : ''; ?>>
                                                Absent
                                            </option>
                                        </select>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p class="no-data">Belum ada tamu yang mendaftar</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
.guest-list-container {
    max-width: 1200px;
    margin: 100px auto 40px;
    padding: 20px;
    background: white;
    border-radius: 15px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.guest-list-container h1 {
    color: #333;
    text-align: center;
    margin-bottom: 30px;
    font-size: 2rem;
    padding-bottom: 15px;
    border-bottom: 2px solid #eee;
}

.stats-container {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin-bottom: 40px;
}

.stat-box {
    background: #f8f9fa;
    padding: 25px;
    border-radius: 12px;
    text-align: center;
    box-shadow: 0 2px 6px rgba(0,0,0,0.05);
    transition: transform 0.3s ease;
}

.stat-box:hover {
    transform: translateY(-5px);
}

.stat-box h3 {
    color: #666;
    font-size: 1rem;
    margin-bottom: 10px;
}

.stat-box p {
    color: #333;
    font-size: 1.8rem;
    font-weight: bold;
    margin: 0;
}

.guest-section {
    margin-top: 40px;
}

.guest-section h2 {
    color: #333;
    margin-bottom: 20px;
    font-size: 1.5rem;
    padding-bottom: 10px;
    border-bottom: 2px solid #eee;
}

.table-responsive {
    background: white;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
}

.guest-table {
    width: 100%;
    border-collapse: collapse;
    margin: 0;
}

.guest-table th {
    background: #f8f9fa;
    color: #333;
    font-weight: 600;
    text-transform: uppercase;
    font-size: 0.85rem;
    padding: 15px;
    border-bottom: 2px solid #eee;
}

.guest-table td {
    padding: 15px;
    border-bottom: 1px solid #eee;
    color: #666;
    font-size: 0.95rem;
}

.guest-table tr:hover {
    background: #f8f9fa;
}

.status-badge {
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 0.85rem;
    font-weight: 500;
    display: inline-block;
}

.status-badge.registered {
    background: #e3f2fd;
    color: #1976d2;
}

.status-badge.attended {
    background: #e8f5e9;
    color: #2e7d32;
}

.status-badge.absent {
    background: #ffebee;
    color: #c62828;
}

.status-select {
    padding: 8px 12px;
    border: 1px solid #ddd;
    border-radius: 6px;
    background: white;
    color: #333;
    font-size: 0.9rem;
    cursor: pointer;
    transition: all 0.3s ease;
}

.status-select:hover {
    border-color: #999;
}

.status-select:focus {
    outline: none;
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102,126,234,0.1);
}

.no-data {
    text-align: center;
    padding: 40px;
    background: #f8f9fa;
    border-radius: 12px;
    color: #666;
    font-size: 1.1rem;
}

/* Responsive Design */
@media (max-width: 768px) {
    .guest-list-container {
        margin: 20px;
        padding: 15px;
    }

    .stats-container {
        grid-template-columns: repeat(2, 1fr);
        gap: 15px;
    }

    .stat-box {
        padding: 15px;
    }

    .stat-box p {
        font-size: 1.4rem;
    }

    .table-responsive {
        margin: 0 -15px;
        border-radius: 0;
    }

    .guest-table th,
    .guest-table td {
        padding: 12px 10px;
        font-size: 0.85rem;
    }

    .status-badge {
        padding: 4px 8px;
        font-size: 0.8rem;
    }

    .status-select {
        padding: 6px 8px;
        font-size: 0.85rem;
    }
}

/* Print Styles */
@media print {
    .guest-list-container {
        box-shadow: none;
    }

    .status-select {
        display: none;
    }

    .guest-table th {
        background: white !important;
        color: black;
    }
}
</style>

<script>
function updateStatus(id, status) {
    fetch('<?php echo BASE_URL; ?>/api/update-guest-status.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            guest_id: id,
            status: status
        })
    })
    .then(response => response.json())
    .then(data => {
        if(data.success) {
            location.reload();
        } else {
            alert('Error updating status: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error updating status');
    });
}
</script>

<?php include '../includes/footer.php'; ?>