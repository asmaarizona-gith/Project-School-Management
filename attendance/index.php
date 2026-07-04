<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit;
}
require_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 20;
$offset = ($page - 1) * $limit;
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$date_filter = isset($_GET['date']) ? trim($_GET['date']) : '';

$where = [];
$params = [];

if ($search) {
    $where[] = "(s.firstname LIKE :search OR s.lastname LIKE :search2 OR s.admission_no LIKE :search3)";
    $params[':search'] = "%$search%";
    $params[':search2'] = "%$search%";
    $params[':search3'] = "%$search%";
}

if ($date_filter) {
    $where[] = "a.date = :date_filter";
    $params[':date_filter'] = $date_filter;
}

$where_clause = $where ? 'WHERE ' . implode(' AND ', $where) : '';

$count = $db->prepare("SELECT COUNT(*) FROM attendance a JOIN students s ON a.student_id = s.id $where_clause");
$count->execute($params);
$total = $count->fetchColumn();
$total_pages = ceil($total / $limit);

$stmt = $db->prepare("SELECT a.*, s.firstname, s.lastname, s.admission_no, s.class_id, c.name as class_name FROM attendance a JOIN students s ON a.student_id = s.id LEFT JOIN classes c ON a.class_id = c.id $where_clause ORDER BY a.date DESC, s.firstname ASC LIMIT $limit OFFSET $offset");
$stmt->execute($params);
$attendances = $stmt->fetchAll();

include '../includes/header.php';
include '../includes/navbar.php';
include '../includes/sidebar.php';
?>
<div class="main-content">
    <div class="content-header">
        <h4 class="fw-bold"><i class="fas fa-calendar-check me-2"></i>Attendance</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="../dashboard/index.php">Home</a></li>
                <li class="breadcrumb-item active">Attendance</li>
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

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-0 d-flex flex-wrap justify-content-between align-items-center gap-2">
            <form method="GET" class="d-flex gap-2 flex-wrap flex-grow-1">
                <div class="input-group" style="max-width: 300px;">
                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                    <input type="text" name="search" class="form-control" placeholder="Search student..." value="<?php echo htmlspecialchars($search); ?>">
                </div>
                <div class="input-group" style="max-width: 220px;">
                    <span class="input-group-text"><i class="fas fa-calendar"></i></span>
                    <input type="date" name="date" class="form-control" value="<?php echo htmlspecialchars($date_filter); ?>">
                </div>
                <button class="btn btn-primary" type="submit"><i class="fas fa-filter"></i> Filter</button>
                <?php if ($search || $date_filter): ?>
                <a href="index.php" class="btn btn-secondary"><i class="fas fa-times"></i> Clear</a>
                <?php endif; ?>
            </form>
            <a href="create.php" class="btn btn-danger"><i class="fas fa-plus"></i> Take Attendance</a>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Student</th>
                            <th>Admission No</th>
                            <th>Class</th>
                            <th>Date</th>
                            <th>Status</th>
                            <th>Remark</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($attendances)): ?>
                        <tr><td colspan="7" class="text-center py-4 text-muted">No attendance records found.</td></tr>
                        <?php else: ?>
                        <?php foreach ($attendances as $a): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($a['firstname'] . ' ' . $a['lastname']); ?></td>
                            <td><?php echo htmlspecialchars($a['admission_no']); ?></td>
                            <td><?php echo htmlspecialchars($a['class_name'] ?? 'N/A'); ?></td>
                            <td><?php echo date('d M Y', strtotime($a['date'])); ?></td>
                            <td>
                                <?php
                                $badge = 'secondary';
                                if ($a['status'] == 'Present') $badge = 'success';
                                elseif ($a['status'] == 'Absent') $badge = 'danger';
                                elseif ($a['status'] == 'Late') $badge = 'warning';
                                elseif ($a['status'] == 'Half Day') $badge = 'info';
                                ?>
                                <span class="badge bg-<?php echo $badge; ?>"><?php echo htmlspecialchars($a['status']); ?></span>
                            </td>
                            <td><?php echo htmlspecialchars($a['remark'] ?? 'N/A'); ?></td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="edit.php?id=<?php echo $a['id']; ?>" class="btn btn-warning text-white" title="Edit"><i class="fas fa-edit"></i></a>
                                    <a href="delete.php?id=<?php echo $a['id']; ?>" class="btn btn-danger" title="Delete" onclick="return confirm('Are you sure?')"><i class="fas fa-trash"></i></a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php if ($total_pages > 1): ?>
        <div class="card-footer bg-white border-0">
            <nav>
                <ul class="pagination justify-content-center mb-0">
                    <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                        <a class="page-link" href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>&date=<?php echo urlencode($date_filter); ?>">Previous</a>
                    </li>
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                        <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&date=<?php echo urlencode($date_filter); ?>"><?php echo $i; ?></a>
                    </li>
                    <?php endfor; ?>
                    <li class="page-item <?php echo $page >= $total_pages ? 'disabled' : ''; ?>">
                        <a class="page-link" href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>&date=<?php echo urlencode($date_filter); ?>">Next</a>
                    </li>
                </ul>
            </nav>
        </div>
        <?php endif; ?>
    </div>
</div>
<?php include '../includes/footer.php'; ?>
