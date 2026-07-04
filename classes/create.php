<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit;
}
require_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

$errors = [];
$form_data = [
    'name' => '',
    'section' => '',
    'room' => '',
    'capacity' => 30,
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $form_data = [
        'name' => trim($_POST['name'] ?? ''),
        'section' => trim($_POST['section'] ?? ''),
        'room' => trim($_POST['room'] ?? ''),
        'capacity' => (int)($_POST['capacity'] ?? 30),
    ];

    if (empty($form_data['name'])) $errors[] = 'Class name is required.';

    if (empty($errors)) {
        $stmt = $db->prepare("INSERT INTO classes (name, section, room, capacity) VALUES (:name, :section, :room, :capacity)");
        $stmt->execute([
            ':name' => $form_data['name'],
            ':section' => $form_data['section'] ?: null,
            ':room' => $form_data['room'] ?: null,
            ':capacity' => $form_data['capacity'],
        ]);
        $_SESSION['flash'] = ['type' => 'success', 'message' => 'Class added successfully!'];
        header('Location: index.php');
        exit;
    }
}

include '../includes/header.php';
include '../includes/navbar.php';
include '../includes/sidebar.php';
?>
<div class="main-content">
    <div class="content-header">
        <h4 class="fw-bold"><i class="fas fa-school me-2"></i>Add New Class</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="../dashboard/index.php">Home</a></li>
                <li class="breadcrumb-item"><a href="index.php">Classes</a></li>
                <li class="breadcrumb-item active">Add New</li>
            </ol>
        </nav>
    </div>

    <?php if (!empty($errors)): ?>
    <div class="alert alert-danger alert-dismissible fade show">
        <i class="fas fa-exclamation-circle me-2"></i>
        <ul class="mb-0">
            <?php foreach ($errors as $e): ?>
            <li><?php echo htmlspecialchars($e); ?></li>
            <?php endforeach; ?>
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <form method="POST" action="">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Class Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($form_data['name']); ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Section</label>
                        <input type="text" name="section" class="form-control" value="<?php echo htmlspecialchars($form_data['section']); ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Room</label>
                        <input type="text" name="room" class="form-control" value="<?php echo htmlspecialchars($form_data['room']); ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Capacity</label>
                        <input type="number" name="capacity" class="form-control" value="<?php echo htmlspecialchars($form_data['capacity']); ?>" min="1">
                    </div>
                </div>
                <div class="mt-4 d-flex gap-2">
                    <button type="submit" class="btn btn-warning"><i class="fas fa-save me-2"></i>Save Class</button>
                    <a href="index.php" class="btn btn-secondary"><i class="fas fa-times me-2"></i>Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
<?php include '../includes/footer.php'; ?>
