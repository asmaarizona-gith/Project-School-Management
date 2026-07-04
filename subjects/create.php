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
    'code' => '',
    'description' => '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $form_data = [
        'name' => trim($_POST['name'] ?? ''),
        'code' => strtoupper(trim($_POST['code'] ?? '')),
        'description' => trim($_POST['description'] ?? ''),
    ];

    if (empty($form_data['name'])) $errors[] = 'Subject name is required.';
    if (empty($form_data['code'])) $errors[] = 'Subject code is required.';

    if (empty($errors)) {
        $check = $db->prepare("SELECT id FROM subjects WHERE code = :code");
        $check->execute([':code' => $form_data['code']]);
        if ($check->fetch()) {
            $errors[] = 'Subject code already exists.';
        }
    }

    if (empty($errors)) {
        $stmt = $db->prepare("INSERT INTO subjects (name, code, description) VALUES (:name, :code, :description)");
        $stmt->execute([
            ':name' => $form_data['name'],
            ':code' => $form_data['code'],
            ':description' => $form_data['description'] ?: null,
        ]);
        $_SESSION['flash'] = ['type' => 'success', 'message' => 'Subject added successfully!'];
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
        <h4 class="fw-bold"><i class="fas fa-book me-2"></i>Add New Subject</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="../dashboard/index.php">Home</a></li>
                <li class="breadcrumb-item"><a href="index.php">Subjects</a></li>
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
                        <label class="form-label fw-semibold">Subject Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($form_data['name']); ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Subject Code <span class="text-danger">*</span></label>
                        <input type="text" name="code" class="form-control" value="<?php echo htmlspecialchars($form_data['code']); ?>" placeholder="e.g. MATH01" required>
                    </div>
                    <div class="col-md-12">
                        <label class="form-label fw-semibold">Description</label>
                        <textarea name="description" class="form-control" rows="3"><?php echo htmlspecialchars($form_data['description']); ?></textarea>
                    </div>
                </div>
                <div class="mt-4 d-flex gap-2">
                    <button type="submit" class="btn btn-info text-white"><i class="fas fa-save me-2"></i>Save Subject</button>
                    <a href="index.php" class="btn btn-secondary"><i class="fas fa-times me-2"></i>Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
<?php include '../includes/footer.php'; ?>
