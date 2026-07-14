<?php
$adminName = isset($_SESSION['admin_name']) ? $_SESSION['admin_name'] : 'Admin';
?>
<header class="topbar">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
        <div>
            <h4 class="mb-1 fw-semibold">Restaurant Name</h4>
            <p class="text-muted mb-0">Welcome back, <?php echo htmlspecialchars($adminName); ?></p>
        </div>

        <div class="d-flex align-items-center gap-2 w-100 w-md-auto">
            <div class="input-group search-group">
                <span class="input-group-text bg-light border-0">
                    <i class="bi bi-search"></i>
                </span>
                <input type="text" class="form-control border-0 bg-light" placeholder="Search...">
            </div>

            <button class="btn btn-light rounded-circle position-relative">
                <i class="bi bi-bell"></i>
                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">3</span>
            </button>

            <div class="d-flex align-items-center gap-2 ms-2">
                <div class="user-avatar">
                    <i class="bi bi-person-fill"></i>
                </div>
                <div class="d-none d-md-block">
                    <div class="fw-semibold"><?php echo htmlspecialchars($adminName); ?></div>
                    <small class="text-muted">Super Admin</small>
                </div>
            </div>
        </div>
    </div>
</header>
