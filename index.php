<?php
session_start();
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard/index.php');
    // Kani waa nidaamka maamulka dugsiga (School Management System)
} else {
    header('Location: auth/login.php');
}
exit;
