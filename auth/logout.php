<?php
session_start();
require_once '../config/database.php';

session_destroy();
session_unset();

header("Location: " . BASE_URL);
exit();
?>