<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit;
}
require_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$stmt = $db->prepare("SELECT id FROM results WHERE id = :id");
$stmt->execute([':id' => $id]);
if ($stmt->fetch()) {
    $delete = $db->prepare("DELETE FROM results WHERE id = :id");
    $delete->execute([':id' => $id]);
    $_SESSION['flash'] = ['type' => 'success', 'message' => 'Result deleted successfully!'];
} else {
    $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Result not found.'];
}
header('Location: index.php');
exit;
