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
$student = $db->prepare("SELECT * FROM students WHERE id = :id");
$student->execute([':id' => $id]);
$student = $student->fetch();

if (!$student) {
    $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Student not found.'];
    header('Location: index.php');
    exit;
}

$classes = $db->query("SELECT * FROM classes ORDER BY name")->fetchAll();

$errors = [];
$form_data = $student;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $form_data = [
        'firstname' => trim($_POST['firstname'] ?? ''),
        'lastname' => trim($_POST['lastname'] ?? ''),
        'email' => trim($_POST['email'] ?? ''),
        'phone' => trim($_POST['phone'] ?? ''),
        'address' => trim($_POST['address'] ?? ''),
        'gender' => $_POST['gender'] ?? 'Male',
        'date_of_birth' => trim($_POST['date_of_birth'] ?? ''),
        'admission_no' => trim($_POST['admission_no'] ?? ''),
        'class_id' => $_POST['class_id'] ?? '',
    ];

    if (empty($form_data['firstname'])) $errors[] = 'First name is required.';
    if (empty($form_data['lastname'])) $errors[] = 'Last name is required.';
    if (empty($form_data['email'])) $errors[] = 'Email is required.';
    if (!filter_var($form_data['email'], FILTER_VALIDATE_EMAIL)) $errors[] = 'Invalid email format.';
    if (empty($form_data['admission_no'])) $errors[] = 'Admission number is required.';

    if (empty($errors)) {
        $check = $db->prepare("SELECT id FROM students WHERE (email = :email OR admission_no = :admission_no) AND id != :id");
        $check->execute([':email' => $form_data['email'], ':admission_no' => $form_data['admission_no'], ':id' => $id]);
        if ($check->fetch()) {
            $errors[] = 'Email or Admission number already exists.';
        }
    }

    if (empty($errors)) {
        $stmt = $db->prepare("UPDATE students SET firstname = :firstname, lastname = :lastname, email = :email, phone = :phone, address = :address, gender = :gender, date_of_birth = :date_of_birth, admission_no = :admission_no, class_id = :class_id WHERE id = :id");
        $stmt->execute([
            ':firstname' => $form_data['firstname'],
            ':lastname' => $form_data['lastname'],
            ':email' => $form_data['email'],
            ':phone' => $form_data['phone'] ?: null,
            ':address' => $form_data['address'] ?: null,
            ':gender' => $form_data['gender'],
            ':date_of_birth' => $form_data['date_of_birth'] ?: null,
            ':admission_no' => $form_data['admission_no'],
            ':class_id' => $form_data['class_id'] ?: null,
            ':id' => $id,
        ]);
        $_SESSION['flash'] = ['type' => 'success', 'message' => 'Student updated successfully!'];
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
        <h4 class="fw-bold"><i class="fas fa-edit me-2"></i>Edit Student</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="../dashboard/index.php">Home</a></li>
                <li class="breadcrumb-item"><a href="index.php">Students</a></li>
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
                        <label class="form-label fw-semibold">Date of Birth</label>
                        <input type="date" name="date_of_birth" class="form-control" value="<?php echo htmlspecialchars($form_data['date_of_birth']); ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Admission No <span class="text-danger">*</span></label>
                        <input type="text" name="admission_no" class="form-control" value="<?php echo htmlspecialchars($form_data['admission_no']); ?>" required>
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
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save me-2"></i>Update Student</button>
                    <a href="index.php" class="btn btn-secondary"><i class="fas fa-times me-2"></i>Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
<?php include '../includes/footer.php'; ?>
