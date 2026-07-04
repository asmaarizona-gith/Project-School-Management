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
$stmt = $db->prepare("SELECT a.*, s.firstname, s.lastname, s.admission_no FROM attendance a JOIN students s ON a.student_id = s.id WHERE a.id = :id");
$stmt->execute([':id' => $id]);
$attendance = $stmt->fetch();

if (!$attendance) {
    $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Attendance record not found.'];
    header('Location: index.php');
    exit;
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $status = $_POST['status'] ?? 'Present';
    $remark = trim($_POST['remark'] ?? '');

    $update = $db->prepare("UPDATE attendance SET status = :status, remark = :remark WHERE id = :id");
    $update->execute([
        ':status' => $status,
        ':remark' => $remark ?: null,
        ':id' => $id,
    ]);

    $_SESSION['flash'] = ['type' => 'success', 'message' => 'Attendance updated successfully!'];
    header('Location: index.php');
    exit;
}

include '../includes/header.php';
include '../includes/navbar.php';
include '../includes/sidebar.php';
?>
<div class="main-content">
    <div class="content-header">
        <h4 class="fw-bold"><i class="fas fa-edit me-2"></i>Edit Attendance</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="../dashboard/index.php">Home</a></li>
                <li class="breadcrumb-item"><a href="index.php">Attendance</a></li>
                <li class="breadcrumb-item active">Edit</li>
            </ol>
        </nav>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <div class="mb-4">
                <p><strong>Student:</strong> <?php echo htmlspecialchars($attendance['firstname'] . ' ' . $attendance['lastname']); ?> (<?php echo htmlspecialchars($attendance['admission_no']); ?>)</p>
                <p><strong>Date:</strong> <?php echo date('d M Y', strtotime($attendance['date'])); ?></p>
            </div>
            <form method="POST" action="">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Status</label>
                        <select name="status" class="form-select">
                            <option value="Present" <?php echo $attendance['status'] == 'Present' ? 'selected' : ''; ?>>Present</option>
                            <option value="Absent" <?php echo $attendance['status'] == 'Absent' ? 'selected' : ''; ?>>Absent</option>
                            <option value="Late" <?php echo $attendance['status'] == 'Late' ? 'selected' : ''; ?>>Late</option>
                            <option value="Half Day" <?php echo $attendance['status'] == 'Half Day' ? 'selected' : ''; ?>>Half Day</option>
                        </select>
                    </div>
                    <div class="col-md-8">
                        <label class="form-label fw-semibold">Remark</label>
                        <input type="text" name="remark" class="form-control" value="<?php echo htmlspecialchars($attendance['remark'] ?? ''); ?>">
                    </div>
                </div>
                <div class="mt-4 d-flex gap-2">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save me-2"></i>Update</button>
                    <a href="index.php" class="btn btn-secondary"><i class="fas fa-times me-2"></i>Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
<?php include '../includes/footer.php'; ?>
