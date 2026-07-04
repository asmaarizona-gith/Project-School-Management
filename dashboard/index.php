<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit;
}
require_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

$total_students = $db->query("SELECT COUNT(*) FROM students")->fetchColumn();
$total_teachers = $db->query("SELECT COUNT(*) FROM teachers")->fetchColumn();
$total_classes = $db->query("SELECT COUNT(*) FROM classes")->fetchColumn();
$total_subjects = $db->query("SELECT COUNT(*) FROM subjects")->fetchColumn();
$total_attendance = $db->query("SELECT COUNT(*) FROM attendance WHERE date = CURDATE()")->fetchColumn();
$total_results = $db->query("SELECT COUNT(*) FROM results")->fetchColumn();

$recent_students = $db->query("SELECT s.*, c.name as class_name FROM students s LEFT JOIN classes c ON s.class_id = c.id ORDER BY s.created_at DESC LIMIT 5")->fetchAll();
$recent_teachers = $db->query("SELECT t.*, s.name as subject_name FROM teachers t LEFT JOIN subjects s ON t.subject_id = s.id ORDER BY t.created_at DESC LIMIT 5")->fetchAll();

include '../includes/header.php';
include '../includes/navbar.php';
include '../includes/sidebar.php';
?>
<div class="main-content">
    <div class="content-header">
        <h4 class="fw-bold"><i class="fas fa-tachometer-alt me-2"></i>Dashboard</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="#">Home</a></li>
                <li class="breadcrumb-item active">Dashboard</li>
            </ol>
        </nav>
    </div>

    <?php if (isset($_SESSION['flash'])): ?>
    <div class="alert alert-<?php echo $_SESSION['flash']['type']; ?> alert-dismissible fade show animate__animated animate__fadeIn">
        <i class="fas fa-<?php echo $_SESSION['flash']['type'] == 'success' ? 'check-circle' : 'info-circle'; ?> me-2"></i>
        <?php echo htmlspecialchars($_SESSION['flash']['message']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php unset($_SESSION['flash']); endif; ?>

    <div class="row g-4 mb-4">
        <div class="col-xl-2 col-lg-4 col-md-6">
            <div class="card stat-card bg-primary text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white-50 mb-1">Students</h6>
                            <h2 class="mb-0 fw-bold"><?php echo $total_students; ?></h2>
                        </div>
                        <i class="fas fa-user-graduate fa-3x text-white-50"></i>
                    </div>
                    <a href="../students/index.php" class="text-white-50 text-decoration-none small mt-2 d-block">View Details <i class="fas fa-arrow-right ms-1"></i></a>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-lg-4 col-md-6">
            <div class="card stat-card bg-success text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white-50 mb-1">Teachers</h6>
                            <h2 class="mb-0 fw-bold"><?php echo $total_teachers; ?></h2>
                        </div>
                        <i class="fas fa-chalkboard-teacher fa-3x text-white-50"></i>
                    </div>
                    <a href="../teachers/index.php" class="text-white-50 text-decoration-none small mt-2 d-block">View Details <i class="fas fa-arrow-right ms-1"></i></a>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-lg-4 col-md-6">
            <div class="card stat-card bg-warning text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white-50 mb-1">Classes</h6>
                            <h2 class="mb-0 fw-bold"><?php echo $total_classes; ?></h2>
                        </div>
                        <i class="fas fa-school fa-3x text-white-50"></i>
                    </div>
                    <a href="../classes/index.php" class="text-white-50 text-decoration-none small mt-2 d-block">View Details <i class="fas fa-arrow-right ms-1"></i></a>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-lg-4 col-md-6">
            <div class="card stat-card bg-info text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white-50 mb-1">Subjects</h6>
                            <h2 class="mb-0 fw-bold"><?php echo $total_subjects; ?></h2>
                        </div>
                        <i class="fas fa-book fa-3x text-white-50"></i>
                    </div>
                    <a href="../subjects/index.php" class="text-white-50 text-decoration-none small mt-2 d-block">View Details <i class="fas fa-arrow-right ms-1"></i></a>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-lg-4 col-md-6">
            <div class="card stat-card bg-danger text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white-50 mb-1">Today Attendance</h6>
                            <h2 class="mb-0 fw-bold"><?php echo $total_attendance; ?></h2>
                        </div>
                        <i class="fas fa-calendar-check fa-3x text-white-50"></i>
                    </div>
                    <a href="../attendance/index.php" class="text-white-50 text-decoration-none small mt-2 d-block">View Details <i class="fas fa-arrow-right ms-1"></i></a>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-lg-4 col-md-6">
            <div class="card stat-card bg-secondary text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white-50 mb-1">Results</h6>
                            <h2 class="mb-0 fw-bold"><?php echo $total_results; ?></h2>
                        </div>
                        <i class="fas fa-chart-bar fa-3x text-white-50"></i>
                    </div>
                    <a href="../results/index.php" class="text-white-50 text-decoration-none small mt-2 d-block">View Details <i class="fas fa-arrow-right ms-1"></i></a>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold"><i class="fas fa-user-graduate text-primary me-2"></i>Recent Students</h5>
                    <a href="../students/create.php" class="btn btn-sm btn-primary"><i class="fas fa-plus"></i> Add New</a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Name</th>
                                    <th>Admission No</th>
                                    <th>Class</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recent_students as $student): ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($student['firstname'].'+'.$student['lastname']); ?>&background=2563EB&color=fff&size=30" class="rounded-circle me-2" width="30" height="30">
                                            <?php echo htmlspecialchars($student['firstname'] . ' ' . $student['lastname']); ?>
                                        </div>
                                    </td>
                                    <td><?php echo htmlspecialchars($student['admission_no']); ?></td>
                                    <td><?php echo htmlspecialchars($student['class_name'] ?? 'N/A'); ?></td>
                                </tr>
                                <?php endforeach; ?>
                                <?php if (empty($recent_students)): ?>
                                <tr><td colspan="3" class="text-center py-3 text-muted">No students found.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold"><i class="fas fa-chalkboard-teacher text-success me-2"></i>Recent Teachers</h5>
                    <a href="../teachers/create.php" class="btn btn-sm btn-success"><i class="fas fa-plus"></i> Add New</a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Name</th>
                                    <th>Subject</th>
                                    <th>Phone</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recent_teachers as $teacher): ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($teacher['firstname'].'+'.$teacher['lastname']); ?>&background=059669&color=fff&size=30" class="rounded-circle me-2" width="30" height="30">
                                            <?php echo htmlspecialchars($teacher['firstname'] . ' ' . $teacher['lastname']); ?>
                                        </div>
                                    </td>
                                    <td><?php echo htmlspecialchars($teacher['subject_name'] ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($teacher['phone'] ?? 'N/A'); ?></td>
                                </tr>
                                <?php endforeach; ?>
                                <?php if (empty($recent_teachers)): ?>
                                <tr><td colspan="3" class="text-center py-3 text-muted">No teachers found.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include '../includes/footer.php'; ?>
