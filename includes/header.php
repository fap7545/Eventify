<?php 
require_once __DIR__ . '/../config/config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Eventify - Simple Event Management</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/style.css">
</head>
<body>
<nav class="navbar">
    <a href="<?php echo BASE_URL; ?>" class="logo">Eventify.</a>
    <ul class="nav-links">
        <li><a href="<?php echo BASE_URL; ?>">Home</a></li>
        <?php if(isset($_SESSION['user_id'])): ?>
            <li><a href="<?php echo BASE_URL; ?>/pages/create-event.php">Tambah Event</a></li>
            <li><a href="<?php echo BASE_URL; ?>/pages/manage-events.php">Kelola Events</a></li>
            <!-- Perbaikan link guest list -->
            <li><a href="<?php echo BASE_URL; ?>/pages/guest-list.php">Daftar Tamu</a></li>
            <li><a href="<?php echo BASE_URL; ?>/auth/logout.php">Logout</a></li>
        <?php else: ?>
            <li><a href="<?php echo BASE_URL; ?>/auth/login.php">Login</a></li>
            <li><a href="<?php echo BASE_URL; ?>/auth/register.php">Register</a></li>
        <?php endif; ?>
    </ul>
</nav>

<style>
    .navbar {
        font-family: 'Prompt', sans-serif;
    }

    .logo {
        font-family: 'Prompt', sans-serif;
        font-weight: 700;
    }
</style>

</body>