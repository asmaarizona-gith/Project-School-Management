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

$stmt = $db->prepare("SELECT id FROM subjects WHERE id = :id");
$stmt->execute([':id' => $id]);
if ($stmt->fetch()) {
    $delete = $db->prepare("DELETE FROM subjects WHERE id = :id");
    $delete->execute([':id' => $id]);
    $_SESSION['flash'] = ['type' => 'success', 'message' => 'Subject deleted successfully!'];
} else {
    $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Subject not found.'];
}
header('Location: index.php');
exit;
