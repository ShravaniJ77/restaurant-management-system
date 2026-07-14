<?php
require_once dirname(__DIR__, 2) . '/includes/config.php';
require_once ROOT_PATH . 'includes/auth.php';
require_once ROOT_PATH . 'includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASE_URL . '/pages/categories/view_categories.php');
    exit;
}

$csrf = $_POST['csrf_token'] ?? '';
if (!verifyCsrfToken($csrf, 'delete_category')) {
    logError('CSRF verification failed on delete_category.php from ' . ($_SERVER['REMOTE_ADDR'] ?? 'unknown'));
    setFlashMessage('error', 'Invalid request. Please try again.');
    header('Location: ' . BASE_URL . '/pages/categories/view_categories.php');
    exit;
}

$categoryId = sanitizeInt($_POST['id'] ?? 0);
if ($categoryId <= 0) {
    setFlashMessage('error', 'Invalid category selected.');
    header('Location: ' . BASE_URL . '/pages/categories/view_categories.php');
    exit;
}

$connection = getDbConnection();

// Check if any menu items are linked to this category
$checkStatement = $connection->prepare('SELECT COUNT(*) AS menu_count FROM menu_items WHERE category_id = ?');
$checkStatement->bind_param('i', $categoryId);
$checkStatement->execute();
$checkResult = $checkStatement->get_result();
$row = $checkResult->fetch_assoc();
$menuCount = (int) ($row['menu_count'] ?? 0);
$checkStatement->close();

if ($menuCount > 0) {
    $connection->close();
    setFlashMessage('error', 'Cannot delete category. There are ' . $menuCount . ' menu item(s) referencing it.');
    header('Location: ' . BASE_URL . '/pages/categories/view_categories.php');
    exit;
}

// Proceed with deletion
$deleteStatement = $connection->prepare('DELETE FROM categories WHERE id = ?');
$deleteStatement->bind_param('i', $categoryId);
$deleteStatement->execute();
$deleteStatement->close();
$connection->close();

setFlashMessage('success', 'Category deleted successfully.');
header('Location: ' . BASE_URL . '/pages/categories/view_categories.php');
exit;
