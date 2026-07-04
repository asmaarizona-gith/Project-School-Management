<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit;
}
require_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

$classes = $db->query("SELECT * FROM classes ORDER BY name")->fetchAll();

$errors = [];
$selected_class = isset($_POST['class_id']) ? $_POST['class_id'] : (isset($_GET['class_id']) ? $_GET['class_id'] : '');
$selected_date = isset($_POST['attendance_date']) ? $_POST['attendance_date'] : date('Y-m-d');
$students = [];
$records = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_attendance'])) {
    $selected_class = $_POST['class_id'] ?? '';
    $selected_date = $_POST['attendance_date'] ?? date('Y-m-d');
    $statuses = $_POST['status'] ?? [];
    $remarks = $_POST['remark'] ?? [];

    if (empty($selected_class)) $errors[] = 'Please select a class.';
    if (empty($selected_date)) $errors[] = 'Please select a date.';

    if (empty($errors)) {
        $stmt_check = $db->prepare("SELECT student_id FROM attendance WHERE date = :date AND class_id = :class_id");
        $stmt_check->execute([':date' => $selected_date, ':class_id' => $selected_class]);
        $existing = $stmt_check->fetchAll(PDO::FETCH_COLUMN);

        foreach ($statuses as $student_id => $status) {
            $remark = $remarks[$student_id] ?? '';
            if (in_array($student_id, $existing)) {
                $stmt = $db->prepare("UPDATE attendance SET status = :status, remark = :remark WHERE student_id = :student_id AND date = :date");
            } else {
                $stmt = $db->prepare("INSERT INTO attendance (student_id, class_id, date, status, remark) VALUES (:student_id, :class_id, :date, :status, :remark)");
                $stmt->bindValue(':class_id', $selected_class, PDO::PARAM_INT);
            }
            $stmt->bindValue(':student_id', $student_id, PDO::PARAM_INT);
            $stmt->bindValue(':date', $selected_date);
            $stmt->bindValue(':status', $status);
            $stmt->bindValue(':remark', $remark ?: null, PDO::PARAM_STR);
            $stmt->execute();
        }

        $_SESSION['flash'] = ['type' => 'success', 'message' => 'Attendance saved successfully!'];
        header('Location: index.php');
        exit;
    }
}

if ($selected_class) {
    $stmt = $db->prepare("SELECT id, firstname, lastname, admission_no FROM students WHERE class_id = :class_id ORDER BY firstname");
    $stmt->execute([':class_id' => $selected_class]);
    $students = $stmt->fetchAll();

    $stmt2 = $db->prepare("SELECT student_id, status, remark FROM attendance WHERE date = :date AND class_id = :class_id");
    $stmt2->execute([':date' => $selected_date, ':class_id' => $selected_class]);
    $existing_records = $stmt2->fetchAll(PDO::FETCH_ASSOC);
    foreach ($existing_records as $r) {
        $records[$r['student_id']] = $r;
    }
}

include '../includes/header.php';
include '../includes/navbar.php';
include '../includes/sidebar.php';
?>
<div class="main-content">
    <div class="content-header">
        <h4 class="fw-bold"><i class="fas fa-calendar-check me-2"></i>Take Attendance</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="../dashboard/index.php">Home</a></li>
                <li class="breadcrumb-item"><a href="index.php">Attendance</a></li>
                <li class="breadcrumb-item active">Take Attendance</li>
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

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-4">
            <form method="GET" action="" class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Select Class</label>
                    <select name="class_id" class="form-select" required>
                        <option value="">Choose Class</option>
                        <?php foreach ($classes as $c): ?>
                        <option value="<?php echo $c['id']; ?>" <?php echo $selected_class == $c['id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($c['name'] . ($c['section'] ? ' - ' . $c['section'] : '')); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Date</label>
                    <input type="date" name="attendance_date" class="form-control" value="<?php echo htmlspecialchars($selected_date); ?>" required>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100"><i class="fas fa-search"></i> Load</button>
                </div>
            </form>
        </div>
    </div>

    <?php if (!empty($students)): ?>
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-0">
            <h5 class="mb-0 fw-bold"><i class="fas fa-list me-2"></i>Student List - <?php echo date('d M Y', strtotime($selected_date)); ?></h5>
        </div>
        <div class="card-body p-0">
            <form method="POST" action="">
                <input type="hidden" name="class_id" value="<?php echo $selected_class; ?>">
                <input type="hidden" name="attendance_date" value="<?php echo htmlspecialchars($selected_date); ?>">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Student Name</th>
                                <th>Admission No</th>
                                <th>Status</th>
                                <th>Remark</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($students as $i => $s): 
                                $current_status = $records[$s['id']]['status'] ?? 'Present';
                                $current_remark = $records[$s['id']]['remark'] ?? '';
                            ?>
                            <tr>
                                <td><?php echo $i + 1; ?></td>
                                <td><?php echo htmlspecialchars($s['firstname'] . ' ' . $s['lastname']); ?></td>
                                <td><?php echo htmlspecialchars($s['admission_no']); ?></td>
                                <td>
                                    <select name="status[<?php echo $s['id']; ?>]" class="form-select form-select-sm" style="width: 140px;">
                                        <option value="Present" <?php echo $current_status == 'Present' ? 'selected' : ''; ?>>Present</option>
                                        <option value="Absent" <?php echo $current_status == 'Absent' ? 'selected' : ''; ?>>Absent</option>
                                        <option value="Late" <?php echo $current_status == 'Late' ? 'selected' : ''; ?>>Late</option>
                                        <option value="Half Day" <?php echo $current_status == 'Half Day' ? 'selected' : ''; ?>>Half Day</option>
                                    </select>
                                </td>
                                <td>
                                    <input type="text" name="remark[<?php echo $s['id']; ?>]" class="form-control form-control-sm" value="<?php echo htmlspecialchars($current_remark); ?>" placeholder="Optional remark...">
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div class="card-footer bg-white border-0">
                    <button type="submit" name="save_attendance" class="btn btn-danger"><i class="fas fa-save me-2"></i>Save Attendance</button>
                    <a href="index.php" class="btn btn-secondary"><i class="fas fa-times me-2"></i>Cancel</a>
                </div>
            </form>
        </div>
    </div>
    <?php elseif ($selected_class): ?>
    <div class="alert alert-info">No students found in this class.</div>
    <?php endif; ?>
</div>
<?php include '../includes/footer.php'; ?>
