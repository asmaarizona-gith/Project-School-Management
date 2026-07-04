<div class="sidebar">
    <div class="sidebar-header text-center py-3">
        <img src="https://ui-avatars.com/api/?name=School+Admin&background=2563EB&color=fff&size=60" alt="Admin" class="rounded-circle mb-2 border border-2 border-white">
        <h6 class="text-white mb-0"><?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Admin'); ?></h6>
        <small class="text-white-50">Administrator</small>
    </div>
    <ul class="nav flex-column mt-2">
        <li class="nav-item">
            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' && strpos($_SERVER['REQUEST_URI'], '/dashboard/') ? 'active' : ''; ?>" href="../dashboard/index.php">
                <i class="fas fa-tachometer-alt"></i><span>Dashboard</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo strpos($_SERVER['REQUEST_URI'], '/students/') ? 'active' : ''; ?>" href="../students/index.php">
                <i class="fas fa-user-graduate"></i><span>Students</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo strpos($_SERVER['REQUEST_URI'], '/teachers/') ? 'active' : ''; ?>" href="../teachers/index.php">
                <i class="fas fa-chalkboard-teacher"></i><span>Teachers</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo strpos($_SERVER['REQUEST_URI'], '/classes/') ? 'active' : ''; ?>" href="../classes/index.php">
                <i class="fas fa-school"></i><span>Classes</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo strpos($_SERVER['REQUEST_URI'], '/subjects/') ? 'active' : ''; ?>" href="../subjects/index.php">
                <i class="fas fa-book"></i><span>Subjects</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo strpos($_SERVER['REQUEST_URI'], '/attendance/') ? 'active' : ''; ?>" href="../attendance/index.php">
                <i class="fas fa-calendar-check"></i><span>Attendance</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo strpos($_SERVER['REQUEST_URI'], '/results/') ? 'active' : ''; ?>" href="../results/index.php">
                <i class="fas fa-chart-bar"></i><span>Results</span>
            </a>
        </li>
        <li class="nav-item mt-3">
            <a class="nav-link text-danger" href="../auth/logout.php">
                <i class="fas fa-sign-out-alt"></i><span>Logout</span>
            </a>
        </li>
    </ul>
</div>
