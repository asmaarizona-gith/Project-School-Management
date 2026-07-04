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
$term_filter = isset($_GET['term']) ? trim($_GET['term']) : '';
$year_filter = isset($_GET['year']) ? trim($_GET['year']) : '';

$where = [];
$params = [];

if ($search) {
    $where[] = "(s.firstname LIKE :search OR s.lastname LIKE :search2 OR s.admission_no LIKE :search3)";
    $params[':search'] = "%$search%";
    $params[':search2'] = "%$search%";
    $params[':search3'] = "%$search%";
}
if ($term_filter) {
    $where[] = "r.exam_term = :term";
    $params[':term'] = $term_filter;
}
if ($year_filter) {
    $where[] = "r.exam_year = :year";
    $params[':year'] = (int)$year_filter;
}

$where_clause = $where ? 'WHERE ' . implode(' AND ', $where) : '';

$count = $db->prepare("SELECT COUNT(*) FROM results r JOIN students s ON r.student_id = s.id $where_clause");
$count->execute($params);
$total = $count->fetchColumn();
$total_pages = ceil($total / $limit);

$stmt = $db->prepare("SELECT r.*, s.firstname, s.lastname, s.admission_no, sub.name as subject_name, c.name as class_name FROM results r JOIN students s ON r.student_id = s.id JOIN subjects sub ON r.subject_id = sub.id LEFT JOIN classes c ON r.class_id = c.id $where_clause ORDER BY r.exam_year DESC, r.exam_term, s.firstname LIMIT $limit OFFSET $offset");
$stmt->execute($params);
$results = $stmt->fetchAll();

include '../includes/header.php';
include '../includes/navbar.php';
include '../includes/sidebar.php';
?>
<div class="main-content">
    <div class="content-header">
        <h4 class="fw-bold"><i class="fas fa-chart-bar me-2"></i>Results</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="../dashboard/index.php">Home</a></li>
                <li class="breadcrumb-item active">Results</li>
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
                <div class="input-group" style="max-width: 250px;">
                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                    <input type="text" name="search" class="form-control" placeholder="Search student..." value="<?php echo htmlspecialchars($search); ?>">
                </div>
                <select name="term" class="form-select" style="width: 140px;">
                    <option value="">All Terms</option>
                    <option value="Term 1" <?php echo $term_filter == 'Term 1' ? 'selected' : ''; ?>>Term 1</option>
                    <option value="Term 2" <?php echo $term_filter == 'Term 2' ? 'selected' : ''; ?>>Term 2</option>
                    <option value="Term 3" <?php echo $term_filter == 'Term 3' ? 'selected' : ''; ?>>Term 3</option>
                    <option value="Final" <?php echo $term_filter == 'Final' ? 'selected' : ''; ?>>Final</option>
                </select>
                <select name="year" class="form-select" style="width: 120px;">
                    <option value="">All Years</option>
                    <?php for ($y = date('Y'); $y >= 2024; $y--): ?>
                    <option value="<?php echo $y; ?>" <?php echo $year_filter == $y ? 'selected' : ''; ?>><?php echo $y; ?></option>
                    <?php endfor; ?>
                </select>
                <button class="btn btn-primary" type="submit"><i class="fas fa-filter"></i></button>
                <?php if ($search || $term_filter || $year_filter): ?>
                <a href="index.php" class="btn btn-secondary"><i class="fas fa-times"></i></a>
                <?php endif; ?>
            </form>
            <a href="create.php" class="btn btn-secondary"><i class="fas fa-plus"></i> Add Result</a>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Student</th>
                            <th>Admission No</th>
                            <th>Class</th>
                            <th>Subject</th>
                            <th>Marks</th>
                            <th>Total</th>
                            <th>Grade</th>
                            <th>Term</th>
                            <th>Year</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($results)): ?>
                        <tr><td colspan="10" class="text-center py-4 text-muted">No results found.</td></tr>
                        <?php else: ?>
                        <?php foreach ($results as $r): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($r['firstname'] . ' ' . $r['lastname']); ?></td>
                            <td><?php echo htmlspecialchars($r['admission_no']); ?></td>
                            <td><?php echo htmlspecialchars($r['class_name'] ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($r['subject_name']); ?></td>
                            <td class="fw-semibold"><?php echo number_format($r['marks'], 2); ?></td>
                            <td><?php echo number_format($r['total_marks'], 2); ?></td>
                            <td>
                                <?php
                                $gbadge = 'secondary';
                                if ($r['grade'] == 'A') $gbadge = 'success';
                                elseif ($r['grade'] == 'A-') $gbadge = 'success';
                                elseif ($r['grade'] == 'B+') $gbadge = 'info';
                                elseif ($r['grade'] == 'B') $gbadge = 'info';
                                elseif ($r['grade'] == 'B-') $gbadge = 'warning';
                                elseif ($r['grade'] == 'C') $gbadge = 'warning';
                                elseif ($r['grade'] == 'C-') $gbadge = 'danger';
                                elseif ($r['grade'] == 'D') $gbadge = 'danger';
                                ?>
                                <span class="badge bg-<?php echo $gbadge; ?>"><?php echo htmlspecialchars($r['grade'] ?? 'N/A'); ?></span>
                            </td>
                            <td><?php echo htmlspecialchars($r['exam_term']); ?></td>
                            <td><?php echo htmlspecialchars($r['exam_year']); ?></td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="edit.php?id=<?php echo $r['id']; ?>" class="btn btn-warning text-white" title="Edit"><i class="fas fa-edit"></i></a>
                                    <a href="delete.php?id=<?php echo $r['id']; ?>" class="btn btn-danger" title="Delete" onclick="return confirm('Are you sure?')"><i class="fas fa-trash"></i></a>
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
                        <a class="page-link" href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>&term=<?php echo urlencode($term_filter); ?>&year=<?php echo urlencode($year_filter); ?>">Previous</a>
                    </li>
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                        <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&term=<?php echo urlencode($term_filter); ?>&year=<?php echo urlencode($year_filter); ?>"><?php echo $i; ?></a>
                    </li>
                    <?php endfor; ?>
                    <li class="page-item <?php echo $page >= $total_pages ? 'disabled' : ''; ?>">
                        <a class="page-link" href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>&term=<?php echo urlencode($term_filter); ?>&year=<?php echo urlencode($year_filter); ?>">Next</a>
                    </li>
                </ul>
            </nav>
        </div>
        <?php endif; ?>
    </div>
</div>
<?php include '../includes/footer.php'; ?>
