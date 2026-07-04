<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit;
}
require_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

$students = $db->query("SELECT s.*, c.name as class_name FROM students s LEFT JOIN classes c ON s.class_id = c.id ORDER BY s.firstname")->fetchAll();
$subjects = $db->query("SELECT * FROM subjects ORDER BY name")->fetchAll();

$errors = [];
$form_data = [
    'student_id' => '',
    'subject_id' => '',
    'marks' => '',
    'total_marks' => 100,
    'exam_term' => 'Term 1',
    'exam_year' => date('Y'),
];

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
        'student_id' => $_POST['student_id'] ?? '',
        'subject_id' => $_POST['subject_id'] ?? '',
        'marks' => (float)($_POST['marks'] ?? 0),
        'total_marks' => (float)($_POST['total_marks'] ?? 100),
        'exam_term' => $_POST['exam_term'] ?? 'Term 1',
        'exam_year' => (int)($_POST['exam_year'] ?? date('Y')),
    ];

    if (empty($form_data['student_id'])) $errors[] = 'Please select a student.';
    if (empty($form_data['subject_id'])) $errors[] = 'Please select a subject.';
    if ($form_data['marks'] < 0) $errors[] = 'Marks cannot be negative.';
    if ($form_data['total_marks'] <= 0) $errors[] = 'Total marks must be greater than zero.';

    if (empty($errors)) {
        $check = $db->prepare("SELECT id FROM results WHERE student_id = :student_id AND subject_id = :subject_id AND exam_term = :exam_term AND exam_year = :exam_year");
        $check->execute([
            ':student_id' => $form_data['student_id'],
            ':subject_id' => $form_data['subject_id'],
            ':exam_term' => $form_data['exam_term'],
            ':exam_year' => $form_data['exam_year'],
        ]);
        if ($check->fetch()) {
            $errors[] = 'Result already exists for this student, subject, term, and year.';
        }
    }

    if (empty($errors)) {
        $grade = calculateGrade($form_data['marks'], $form_data['total_marks']);

        $stmt = $db->prepare("SELECT class_id FROM students WHERE id = :id");
        $stmt->execute([':id' => $form_data['student_id']]);
        $student = $stmt->fetch();

        $insert = $db->prepare("INSERT INTO results (student_id, class_id, subject_id, marks, total_marks, grade, exam_term, exam_year) VALUES (:student_id, :class_id, :subject_id, :marks, :total_marks, :grade, :exam_term, :exam_year)");
        $insert->execute([
            ':student_id' => $form_data['student_id'],
            ':class_id' => $student ? $student['class_id'] : null,
            ':subject_id' => $form_data['subject_id'],
            ':marks' => $form_data['marks'],
            ':total_marks' => $form_data['total_marks'],
            ':grade' => $grade,
            ':exam_term' => $form_data['exam_term'],
            ':exam_year' => $form_data['exam_year'],
        ]);
        $_SESSION['flash'] = ['type' => 'success', 'message' => 'Result added successfully!'];
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
        <h4 class="fw-bold"><i class="fas fa-chart-bar me-2"></i>Add New Result</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="../dashboard/index.php">Home</a></li>
                <li class="breadcrumb-item"><a href="index.php">Results</a></li>
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
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Student <span class="text-danger">*</span></label>
                        <select name="student_id" class="form-select" required>
                            <option value="">Select Student</option>
                            <?php foreach ($students as $s): ?>
                            <option value="<?php echo $s['id']; ?>" <?php echo $form_data['student_id'] == $s['id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($s['firstname'] . ' ' . $s['lastname'] . ' (' . $s['admission_no'] . ') - ' . ($s['class_name'] ?? 'N/A')); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Subject <span class="text-danger">*</span></label>
                        <select name="subject_id" class="form-select" required>
                            <option value="">Select Subject</option>
                            <?php foreach ($subjects as $sub): ?>
                            <option value="<?php echo $sub['id']; ?>" <?php echo $form_data['subject_id'] == $sub['id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($sub['name'] . ' (' . $sub['code'] . ')'); ?></option>
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
                    <button type="submit" class="btn btn-secondary"><i class="fas fa-save me-2"></i>Save Result</button>
                    <a href="index.php" class="btn btn-primary"><i class="fas fa-times me-2"></i>Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
<?php include '../includes/footer.php'; ?>
