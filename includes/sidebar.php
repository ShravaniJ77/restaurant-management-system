<div class="sidebar p-4 h-100">
    <div class="d-flex align-items-center gap-2 mb-4">
        <div class="brand-icon">
            <i class="bi bi-shop"></i>
        </div>
        <div>
            <h5 class="mb-0 fw-semibold">Restaurant</h5>
            <small class="text-muted">Admin Panel</small>
        </div>
    </div>

    <nav class="nav flex-column gap-2">
        <a href="<?php echo BASE_URL; ?>/pages/dashboard/dashboard.php" class="nav-link rounded-3 px-3 py-2 <?php echo $currentPage === 'dashboard' ? 'active' : ''; ?>">
            <i class="bi bi-grid me-2"></i> Dashboard
        </a>
        <a href="<?php echo BASE_URL; ?>/pages/categories/view_categories.php" class="nav-link rounded-3 px-3 py-2 <?php echo $currentPage === 'categories' ? 'active' : ''; ?>">
            <i class="bi bi-tags me-2"></i> Categories
        </a>
        <a href="<?php echo BASE_URL; ?>/pages/menu/view_menu.php" class="nav-link rounded-3 px-3 py-2 <?php echo $currentPage === 'menu' ? 'active' : ''; ?>">
            <i class="bi bi-cup-straw me-2"></i> Menu
        </a>
        <a href="<?php echo BASE_URL; ?>/pages/customers/view_customers.php" class="nav-link rounded-3 px-3 py-2 <?php echo $currentPage === 'customers' ? 'active' : ''; ?>">
            <i class="bi bi-people me-2"></i> Customers
        </a>
        <a href="<?php echo BASE_URL; ?>/pages/orders/view_orders.php" class="nav-link rounded-3 px-3 py-2 <?php echo $currentPage === 'orders' ? 'active' : ''; ?>">
            <i class="bi bi-cart3 me-2"></i> Orders
        </a>
        <a href="<?php echo BASE_URL; ?>/pages/billing/invoice.php" class="nav-link rounded-3 px-3 py-2 <?php echo $currentPage === 'billing' ? 'active' : ''; ?>">
            <i class="bi bi-receipt me-2"></i> Billing
        </a>
    </nav>

    <div class="mt-4">
        <a href="<?php echo BASE_URL; ?>/pages/auth/logout.php" class="btn btn-outline-danger w-100 rounded-3">
            <i class="bi bi-box-arrow-right me-2"></i> Logout
        </a>
    </div>
</div>
