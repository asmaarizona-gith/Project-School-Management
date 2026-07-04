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
$limit = 10;
$offset = ($page - 1) * $limit;
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

$where = '';
$params = [];
if ($search) {
    $where = "WHERE (c.name LIKE :search OR c.section LIKE :search2)";
    $params[':search'] = "%$search%";
    $params[':search2'] = "%$search%";
}

$count = $db->prepare("SELECT COUNT(*) FROM classes c $where");
$count->execute($params);
$total = $count->fetchColumn();
$total_pages = ceil($total / $limit);

$stmt = $db->prepare("SELECT c.*, (SELECT COUNT(*) FROM students WHERE class_id = c.id) as student_count, (SELECT COUNT(*) FROM teachers WHERE class_id = c.id) as teacher_count FROM classes c $where ORDER BY c.name LIMIT $limit OFFSET $offset");
$stmt->execute($params);
$classes = $stmt->fetchAll();

include '../includes/header.php';
include '../includes/navbar.php';
include '../includes/sidebar.php';
?>
<div class="main-content">
    <div class="content-header">
        <h4 class="fw-bold"><i class="fas fa-school me-2"></i>Classes</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="../dashboard/index.php">Home</a></li>
                <li class="breadcrumb-item active">Classes</li>
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
            <form method="GET" class="d-flex gap-2 flex-grow-1" style="max-width: 400px;">
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                    <input type="text" name="search" class="form-control" placeholder="Search by name or section..." value="<?php echo htmlspecialchars($search); ?>">
                    <button class="btn btn-primary" type="submit"><i class="fas fa-search"></i></button>
                </div>
            </form>
            <a href="create.php" class="btn btn-warning"><i class="fas fa-plus"></i> Add New Class</a>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Class Name</th>
                            <th>Section</th>
                            <th>Room</th>
                            <th>Capacity</th>
                            <th>Students</th>
                            <th>Teachers</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($classes)): ?>
                        <tr><td colspan="8" class="text-center py-4 text-muted">No classes found.</td></tr>
                        <?php else: ?>
                        <?php foreach ($classes as $i => $c): ?>
                        <tr>
                            <td><?php echo $offset + $i + 1; ?></td>
                            <td class="fw-semibold"><?php echo htmlspecialchars($c['name']); ?></td>
                            <td><?php echo htmlspecialchars($c['section'] ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($c['room'] ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($c['capacity']); ?></td>
                            <td><span class="badge bg-info"><?php echo $c['student_count']; ?></span></td>
                            <td><span class="badge bg-success"><?php echo $c['teacher_count']; ?></span></td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="edit.php?id=<?php echo $c['id']; ?>" class="btn btn-warning text-white" title="Edit"><i class="fas fa-edit"></i></a>
                                    <a href="delete.php?id=<?php echo $c['id']; ?>" class="btn btn-danger" title="Delete" onclick="return confirm('Are you sure you want to delete this class?')"><i class="fas fa-trash"></i></a>
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
                        <a class="page-link" href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>">Previous</a>
                    </li>
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                        <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>"><?php echo $i; ?></a>
                    </li>
                    <?php endfor; ?>
                    <li class="page-item <?php echo $page >= $total_pages ? 'disabled' : ''; ?>">
                        <a class="page-link" href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>">Next</a>
                    </li>
                </ul>
            </nav>
        </div>
        <?php endif; ?>
    </div>
</div>
<?php include '../includes/footer.php'; ?>
