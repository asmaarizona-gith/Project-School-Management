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
$stmt = $db->prepare("SELECT s.*, c.name as class_name, c.section FROM students s LEFT JOIN classes c ON s.class_id = c.id WHERE s.id = :id");
$stmt->execute([':id' => $id]);
$student = $stmt->fetch();

if (!$student) {
    $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Student not found.'];
    header('Location: index.php');
    exit;
}

include '../includes/header.php';
include '../includes/navbar.php';
include '../includes/sidebar.php';
?>
<div class="main-content">
    <div class="content-header">
        <h4 class="fw-bold"><i class="fas fa-user-graduate me-2"></i>Student Details</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="../dashboard/index.php">Home</a></li>
                <li class="breadcrumb-item"><a href="index.php">Students</a></li>
                <li class="breadcrumb-item active">View</li>
            </ol>
        </nav>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <div class="row">
                <div class="col-md-3 text-center mb-4">
                    <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($student['firstname'].'+'.$student['lastname']); ?>&background=2563EB&color=fff&size=150" class="rounded-circle img-thumbnail mb-3" width="150" height="150">
                    <h5 class="fw-bold"><?php echo htmlspecialchars($student['firstname'] . ' ' . $student['lastname']); ?></h5>
                    <span class="badge bg-primary"><?php echo htmlspecialchars($student['admission_no']); ?></span>
                </div>
                <div class="col-md-9">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="border-bottom pb-2 mb-2">
                                <small class="text-muted">First Name</small>
                                <p class="mb-0 fw-semibold"><?php echo htmlspecialchars($student['firstname']); ?></p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="border-bottom pb-2 mb-2">
                                <small class="text-muted">Last Name</small>
                                <p class="mb-0 fw-semibold"><?php echo htmlspecialchars($student['lastname']); ?></p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="border-bottom pb-2 mb-2">
                                <small class="text-muted">Email</small>
                                <p class="mb-0 fw-semibold"><?php echo htmlspecialchars($student['email']); ?></p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="border-bottom pb-2 mb-2">
                                <small class="text-muted">Phone</small>
                                <p class="mb-0 fw-semibold"><?php echo htmlspecialchars($student['phone'] ?? 'N/A'); ?></p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="border-bottom pb-2 mb-2">
                                <small class="text-muted">Gender</small>
                                <p class="mb-0 fw-semibold"><?php echo htmlspecialchars($student['gender']); ?></p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="border-bottom pb-2 mb-2">
                                <small class="text-muted">Date of Birth</small>
                                <p class="mb-0 fw-semibold"><?php echo htmlspecialchars($student['date_of_birth'] ?? 'N/A'); ?></p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="border-bottom pb-2 mb-2">
                                <small class="text-muted">Class</small>
                                <p class="mb-0 fw-semibold"><?php echo htmlspecialchars(($student['class_name'] ?? 'N/A') . ($student['section'] ? ' - ' . $student['section'] : '')); ?></p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="border-bottom pb-2 mb-2">
                                <small class="text-muted">Address</small>
                                <p class="mb-0 fw-semibold"><?php echo htmlspecialchars($student['address'] ?? 'N/A'); ?></p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="border-bottom pb-2 mb-2">
                                <small class="text-muted">Registered On</small>
                                <p class="mb-0 fw-semibold"><?php echo date('d M Y, h:i A', strtotime($student['created_at'])); ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="mt-4 d-flex gap-2">
                        <a href="edit.php?id=<?php echo $student['id']; ?>" class="btn btn-warning"><i class="fas fa-edit me-2"></i>Edit</a>
                        <a href="index.php" class="btn btn-secondary"><i class="fas fa-arrow-left me-2"></i>Back to List</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include '../includes/footer.php'; ?>
