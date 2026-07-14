<?php
require_once dirname(__DIR__, 2) . '/includes/config.php';
require_once ROOT_PATH . 'includes/auth.php';
require_once ROOT_PATH . 'includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASE_URL . '/pages/menu/view_menu.php');
    exit;
}

$csrf = $_POST['csrf_token'] ?? '';
if (!verifyCsrfToken($csrf, 'delete_menu')) {
    logError('CSRF verification failed on delete_menu_item.php from ' . ($_SERVER['REMOTE_ADDR'] ?? 'unknown'));
    setFlashMessage('error', 'Invalid request. Please try again.');
    header('Location: ' . BASE_URL . '/pages/menu/view_menu.php');
    exit;
}

$itemId = sanitizeInt($_POST['id'] ?? 0);
if ($itemId <= 0) {
    setFlashMessage('error', 'Invalid menu item selected.');
    header('Location: ' . BASE_URL . '/pages/menu/view_menu.php');
    exit;
}

$connection = getDbConnection();
$statement = $connection->prepare('SELECT image_path FROM menu_items WHERE id = ? LIMIT 1');
$statement->bind_param('i', $itemId);
$statement->execute();
$result = $statement->get_result();
$item = $result->fetch_assoc();
$statement->close();

if (!$item) {
    $connection->close();
    setFlashMessage('error', 'The selected menu item was not found.');
    header('Location: ' . BASE_URL . '/pages/menu/view_menu.php');
    exit;
}

// Check if any order items reference this menu item before deleting, otherwise
// the DELETE below fails on the fk_order_item_menu foreign key constraint.
$checkStatement = $connection->prepare('SELECT COUNT(*) AS order_count FROM order_items WHERE menu_item_id = ?');
$checkStatement->bind_param('i', $itemId);
$checkStatement->execute();
$checkResult = $checkStatement->get_result();
$orderCount = (int) ($checkResult->fetch_assoc()['order_count'] ?? 0);
$checkStatement->close();

if ($orderCount > 0) {
    $connection->close();
    setFlashMessage('error', 'Cannot delete this menu item. It is referenced by ' . $orderCount . ' existing order(s).');
    header('Location: ' . BASE_URL . '/pages/menu/view_menu.php');
    exit;
}

if (!empty($item['image_path'])) {
    $imagePath = ROOT_PATH . 'uploads' . DIRECTORY_SEPARATOR . 'menu_images' . DIRECTORY_SEPARATOR . $item['image_path'];
    if (file_exists($imagePath)) {
        unlink($imagePath);
    }
}

$deleteStatement = $connection->prepare('DELETE FROM menu_items WHERE id = ?');
$deleteStatement->bind_param('i', $itemId);
$deleteStatement->execute();
$deleteStatement->close();

$connection->close();
setFlashMessage('success', 'Menu item deleted successfully.');
header('Location: ' . BASE_URL . '/pages/menu/view_menu.php');
exit;
