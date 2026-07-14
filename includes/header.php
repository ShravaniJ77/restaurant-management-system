<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/functions.php';

$pageTitle = isset($pageTitle) ? $pageTitle : 'Restaurant Admin';
$currentPage = isset($currentPage) ? $currentPage : 'dashboard';
$showLayout = isset($showLayout) ? $showLayout : true;

$publicPages = ['login', 'logout'];
if (!in_array($currentPage, $publicPages, true)) {
    requireAdminAuth();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars(getPageTitle($pageTitle)); ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/styles.css">
</head>
<body>
<?php if ($showLayout): ?>
<div class="app-shell">
    <aside class="sidebar-wrapper">
        <?php require_once __DIR__ . '/sidebar.php'; ?>
    </aside>
    <div class="main-panel">
        <?php require_once __DIR__ . '/navbar.php'; ?>
        <main class="page-content">
            <?php echo displayFlashMessages(); ?>
<?php else: ?>
<div class="container py-5">
    <?php echo displayFlashMessages(); ?>
<?php endif; ?>
