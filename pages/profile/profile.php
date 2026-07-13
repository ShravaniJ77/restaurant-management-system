<?php
require_once dirname(__DIR__, 2) . '/includes/config.php';
require_once ROOT_PATH . 'includes/auth.php';
require_once ROOT_PATH . 'includes/functions.php';

$pageTitle = 'Profile';
$currentPage = 'profile';
require_once ROOT_PATH . 'includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-semibold mb-1">Profile</h3>
        <p class="text-muted mb-0">Manage your personal account settings.</p>
    </div>
</div>

<div class="card p-4">
    <?php echo renderEmptyState('Profile module', 'Profile editing features will be added here.'); ?>
</div>

<?php require_once ROOT_PATH . 'includes/footer.php'; ?>
