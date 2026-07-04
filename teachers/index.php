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
    $where = "WHERE (t.firstname LIKE :search OR t.lastname LIKE :search2 OR t.email LIKE :search3)";
    $params[':search'] = "%$search%";
    $params[':search2'] = "%$search%";
    $params[':search3'] = "%$search%";
}

$count = $db->prepare("SELECT COUNT(*) FROM teachers t $where");
$count->execute($params);
$total = $count->fetchColumn();
$total_pages = ceil($total / $limit);

$stmt = $db->prepare("SELECT t.*, s.name as subject_name, c.name as class_name FROM teachers t LEFT JOIN subjects s ON t.subject_id = s.id LEFT JOIN classes c ON t.class_id = c.id $where ORDER BY t.created_at DESC LIMIT $limit OFFSET $offset");
$stmt->execute($params);
$teachers = $stmt->fetchAll();

include '../includes/header.php';
include '../includes/navbar.php';
include '../includes/sidebar.php';
?>
<div class="main-content">
    <div class="content-header">
        <h4 class="fw-bold"><i class="fas fa-chalkboard-teacher me-2"></i>Teachers</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="../dashboard/index.php">Home</a></li>
                <li class="breadcrumb-item active">Teachers</li>
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
                    <input type="text" name="search" class="form-control" placeholder="Search by name, email..." value="<?php echo htmlspecialchars($search); ?>">
                    <button class="btn btn-primary" type="submit"><i class="fas fa-search"></i></button>
                </div>
            </form>
            <a href="create.php" class="btn btn-success"><i class="fas fa-plus"></i> Add New Teacher</a>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Photo</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Subject</th>
                            <th>Class</th>
                            <th>Gender</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($teachers)): ?>
                        <tr><td colspan="8" class="text-center py-4 text-muted">No teachers found.</td></tr>
                        <?php else: ?>
                        <?php foreach ($teachers as $t): ?>
                        <tr>
                            <td>
                                <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($t['firstname'].'+'.$t['lastname']); ?>&background=059669&color=fff&size=40" class="rounded-circle" width="40" height="40">
                            </td>
                            <td class="fw-semibold"><?php echo htmlspecialchars($t['firstname'] . ' ' . $t['lastname']); ?></td>
                            <td><?php echo htmlspecialchars($t['email']); ?></td>
                            <td><?php echo htmlspecialchars($t['phone'] ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($t['subject_name'] ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($t['class_name'] ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($t['gender']); ?></td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="view.php?id=<?php echo $t['id']; ?>" class="btn btn-info text-white" title="View"><i class="fas fa-eye"></i></a>
                                    <a href="edit.php?id=<?php echo $t['id']; ?>" class="btn btn-warning text-white" title="Edit"><i class="fas fa-edit"></i></a>
                                    <a href="delete.php?id=<?php echo $t['id']; ?>" class="btn btn-danger" title="Delete" onclick="return confirm('Are you sure you want to delete this teacher?')"><i class="fas fa-trash"></i></a>
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
