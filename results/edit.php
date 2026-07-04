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
$stmt = $db->prepare("SELECT r.*, s.firstname, s.lastname, s.admission_no, sub.name as subject_name FROM results r JOIN students s ON r.student_id = s.id JOIN subjects sub ON r.subject_id = sub.id WHERE r.id = :id");
$stmt->execute([':id' => $id]);
$result = $stmt->fetch();

if (!$result) {
    $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Result not found.'];
    header('Location: index.php');
    exit;
}

$subjects = $db->query("SELECT * FROM subjects ORDER BY name")->fetchAll();

$errors = [];
$form_data = $result;

function calculateGrade($marks, $total) {
    if ($total <= 0) return 'N/A';
    $percentage = ($marks / $total) * 100;
    if ($percentage >= 80) return 'A';
    if ($percentage >= 75) return 'A-';
    if ($percentage >= 70) return 'B+';
    if ($percentage >= 65) return 'B';
    if ($percentage >= 60) return 'B-';
    if ($percentage >= 55) return 'C+';
    if ($percentage >= 50) return 'C';
    if ($percentage >= 45) return 'C-';
    if ($percentage >= 40) return 'D+';
    if ($percentage >= 35) return 'D';
    return 'E';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $form_data = [
        'subject_id' => $_POST['subject_id'] ?? '',
        'marks' => (float)($_POST['marks'] ?? 0),
        'total_marks' => (float)($_POST['total_marks'] ?? 100),
        'exam_term' => $_POST['exam_term'] ?? 'Term 1',
        'exam_year' => (int)($_POST['exam_year'] ?? date('Y')),
    ];

    if (empty($form_data['subject_id'])) $errors[] = 'Please select a subject.';
    if ($form_data['marks'] < 0) $errors[] = 'Marks cannot be negative.';
    if ($form_data['total_marks'] <= 0) $errors[] = 'Total marks must be greater than zero.';

    if (empty($errors)) {
        $check = $db->prepare("SELECT id FROM results WHERE student_id = :student_id AND subject_id = :subject_id AND exam_term = :exam_term AND exam_year = :exam_year AND id != :id");
        $check->execute([
            ':student_id' => $result['student_id'],
            ':subject_id' => $form_data['subject_id'],
            ':exam_term' => $form_data['exam_term'],
            ':exam_year' => $form_data['exam_year'],
            ':id' => $id,
        ]);
        if ($check->fetch()) {
            $errors[] = 'Result already exists for this student, subject, term, and year.';
        }
    }

    if (empty($errors)) {
        $grade = calculateGrade($form_data['marks'], $form_data['total_marks']);
        $update = $db->prepare("UPDATE results SET subject_id = :subject_id, marks = :marks, total_marks = :total_marks, grade = :grade, exam_term = :exam_term, exam_year = :exam_year WHERE id = :id");
        $update->execute([
            ':subject_id' => $form_data['subject_id'],
            ':marks' => $form_data['marks'],
            ':total_marks' => $form_data['total_marks'],
            ':grade' => $grade,
            ':exam_term' => $form_data['exam_term'],
            ':exam_year' => $form_data['exam_year'],
            ':id' => $id,
        ]);
        $_SESSION['flash'] = ['type' => 'success', 'message' => 'Result updated successfully!'];
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
        <h4 class="fw-bold"><i class="fas fa-edit me-2"></i>Edit Result</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="../dashboard/index.php">Home</a></li>
                <li class="breadcrumb-item"><a href="index.php">Results</a></li>
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
            <div class="mb-4">
                <p><strong>Student:</strong> <?php echo htmlspecialchars($result['firstname'] . ' ' . $result['lastname']); ?> (<?php echo htmlspecialchars($result['admission_no']); ?>)</p>
            </div>
            <form method="POST" action="">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Subject <span class="text-danger">*</span></label>
                        <select name="subject_id" class="form-select" required>
                            <option value="">Select Subject</option>
                            <?php foreach ($subjects as $sub): ?>
                            <option value="<?php echo $sub['id']; ?>" <?php echo $form_data['subject_id'] == $sub['id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($sub['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-semibold">Marks</label>
                        <input type="number" name="marks" class="form-control" value="<?php echo htmlspecialchars($form_data['marks']); ?>" step="0.01" required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-semibold">Total Marks</label>
                        <input type="number" name="total_marks" class="form-control" value="<?php echo htmlspecialchars($form_data['total_marks']); ?>" step="0.01" required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-semibold">Term</label>
                        <select name="exam_term" class="form-select">
                            <option value="Term 1" <?php echo $form_data['exam_term'] == 'Term 1' ? 'selected' : ''; ?>>Term 1</option>
                            <option value="Term 2" <?php echo $form_data['exam_term'] == 'Term 2' ? 'selected' : ''; ?>>Term 2</option>
                            <option value="Term 3" <?php echo $form_data['exam_term'] == 'Term 3' ? 'selected' : ''; ?>>Term 3</option>
                            <option value="Final" <?php echo $form_data['exam_term'] == 'Final' ? 'selected' : ''; ?>>Final</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-semibold">Year</label>
                        <select name="exam_year" class="form-select">
                            <?php for ($y = date('Y'); $y >= 2024; $y--): ?>
                            <option value="<?php echo $y; ?>" <?php echo $form_data['exam_year'] == $y ? 'selected' : ''; ?>><?php echo $y; ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                </div>
                <div class="mt-4 d-flex gap-2">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save me-2"></i>Update Result</button>
                    <a href="index.php" class="btn btn-secondary"><i class="fas fa-times me-2"></i>Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
<?php include '../includes/footer.php'; ?>
