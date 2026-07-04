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
$teacher = $db->prepare("SELECT * FROM teachers WHERE id = :id");
$teacher->execute([':id' => $id]);
$teacher = $teacher->fetch();

if (!$teacher) {
    $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Teacher not found.'];
    header('Location: index.php');
    exit;
}

$subjects = $db->query("SELECT * FROM subjects ORDER BY name")->fetchAll();
$classes = $db->query("SELECT * FROM classes ORDER BY name")->fetchAll();

$errors = [];
$form_data = $teacher;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $form_data = [
        'firstname' => trim($_POST['firstname'] ?? ''),
        'lastname' => trim($_POST['lastname'] ?? ''),
        'email' => trim($_POST['email'] ?? ''),
        'phone' => trim($_POST['phone'] ?? ''),
        'address' => trim($_POST['address'] ?? ''),
        'gender' => $_POST['gender'] ?? 'Male',
        'qualification' => trim($_POST['qualification'] ?? ''),
        'subject_id' => $_POST['subject_id'] ?? '',
        'class_id' => $_POST['class_id'] ?? '',
    ];

    if (empty($form_data['firstname'])) $errors[] = 'First name is required.';
    if (empty($form_data['lastname'])) $errors[] = 'Last name is required.';
    if (empty($form_data['email'])) $errors[] = 'Email is required.';
    if (!filter_var($form_data['email'], FILTER_VALIDATE_EMAIL)) $errors[] = 'Invalid email format.';

    if (empty($errors)) {
        $check = $db->prepare("SELECT id FROM teachers WHERE email = :email AND id != :id");
        $check->execute([':email' => $form_data['email'], ':id' => $id]);
        if ($check->fetch()) {
            $errors[] = 'Email already exists.';
        }
    }

    if (empty($errors)) {
        $stmt = $db->prepare("UPDATE teachers SET firstname = :firstname, lastname = :lastname, email = :email, phone = :phone, address = :address, gender = :gender, qualification = :qualification, subject_id = :subject_id, class_id = :class_id WHERE id = :id");
        $stmt->execute([
            ':firstname' => $form_data['firstname'],
            ':lastname' => $form_data['lastname'],
            ':email' => $form_data['email'],
            ':phone' => $form_data['phone'] ?: null,
            ':address' => $form_data['address'] ?: null,
            ':gender' => $form_data['gender'],
            ':qualification' => $form_data['qualification'] ?: null,
            ':subject_id' => $form_data['subject_id'] ?: null,
            ':class_id' => $form_data['class_id'] ?: null,
            ':id' => $id,
        ]);
        $_SESSION['flash'] = ['type' => 'success', 'message' => 'Teacher updated successfully!'];
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
        <h4 class="fw-bold"><i class="fas fa-edit me-2"></i>Edit Teacher</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="../dashboard/index.php">Home</a></li>
                <li class="breadcrumb-item"><a href="index.php">Teachers</a></li>
                <li class="breadcrumb-item active">Edit</li>
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
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">First Name <span class="text-danger">*</span></label>
                        <input type="text" name="firstname" class="form-control" value="<?php echo htmlspecialchars($form_data['firstname']); ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Last Name <span class="text-danger">*</span></label>
                        <input type="text" name="lastname" class="form-control" value="<?php echo htmlspecialchars($form_data['lastname']); ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Gender</label>
                        <select name="gender" class="form-select">
                            <option value="Male" <?php echo $form_data['gender'] == 'Male' ? 'selected' : ''; ?>>Male</option>
                            <option value="Female" <?php echo $form_data['gender'] == 'Female' ? 'selected' : ''; ?>>Female</option>
                            <option value="Other" <?php echo $form_data['gender'] == 'Other' ? 'selected' : ''; ?>>Other</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Email <span class="text-danger">*</span></label>
                        <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($form_data['email']); ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Phone</label>
                        <input type="text" name="phone" class="form-control" value="<?php echo htmlspecialchars($form_data['phone']); ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Qualification</label>
                        <input type="text" name="qualification" class="form-control" value="<?php echo htmlspecialchars($form_data['qualification']); ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Subject</label>
                        <select name="subject_id" class="form-select">
                            <option value="">Select Subject</option>
                            <?php foreach ($subjects as $sub): ?>
                            <option value="<?php echo $sub['id']; ?>" <?php echo $form_data['subject_id'] == $sub['id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($sub['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Class</label>
                        <select name="class_id" class="form-select">
                            <option value="">Select Class</option>
                            <?php foreach ($classes as $c): ?>
                            <option value="<?php echo $c['id']; ?>" <?php echo $form_data['class_id'] == $c['id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($c['name'] . ($c['section'] ? ' - ' . $c['section'] : '')); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-12">
                        <label class="form-label fw-semibold">Address</label>
                        <textarea name="address" class="form-control" rows="2"><?php echo htmlspecialchars($form_data['address']); ?></textarea>
                    </div>
                </div>
                <div class="mt-4 d-flex gap-2">
                    <button type="submit" class="btn btn-success"><i class="fas fa-save me-2"></i>Update Teacher</button>
                    <a href="index.php" class="btn btn-secondary"><i class="fas fa-times me-2"></i>Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
<?php include '../includes/footer.php'; ?>
