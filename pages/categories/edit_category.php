<?php
require_once dirname(__DIR__, 2) . '/includes/config.php';
require_once ROOT_PATH . 'includes/auth.php';
require_once ROOT_PATH . 'includes/functions.php';

$pageTitle = 'Edit Category';
$currentPage = 'categories';

$categoryId = (int) ($_GET['id'] ?? 0);
$errors = [];

if ($categoryId <= 0) {
    setFlashMessage('error', 'Invalid category selected.');
    header('Location: ' . BASE_URL . '/pages/categories/view_categories.php');
    exit;
}

$connection = getDbConnection();
$statement = $connection->prepare('SELECT id, name, description, status FROM categories WHERE id = ? LIMIT 1');
$statement->bind_param('i', $categoryId);
$statement->execute();
$result = $statement->get_result();
$category = $result->fetch_assoc();
$statement->close();

if (!$category) {
    $connection->close();
    setFlashMessage('error', 'The selected category was not found.');
    header('Location: ' . BASE_URL . '/pages/categories/view_categories.php');
    exit;
}

$name = $category['name'];
$description = $category['description'];
$status = $category['status'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token and sanitize inputs
    $csrf = $_POST['csrf_token'] ?? '';
    if (!verifyCsrfToken($csrf, 'edit_category')) {
        $errors[] = 'Invalid request. Please try again.';
    }

    $name = sanitizeString($_POST['name'] ?? '');
    $description = sanitizeString($_POST['description'] ?? '');
    $status = sanitizeString($_POST['status'] ?? 'active');

    if ($name === '') {
        $errors[] = 'Category name is required.';
    }

    if (!in_array($status, ['active', 'inactive'], true)) {
        $errors[] = 'Please select a valid status.';
    }

    if (empty($errors)) {
        // Check for uniqueness of name if changed
        $checkStatement = $connection->prepare('SELECT id FROM categories WHERE name = ? AND id != ? LIMIT 1');
        $checkStatement->bind_param('si', $name, $categoryId);
        $checkStatement->execute();
        $checkResult = $checkStatement->get_result();
        $checkStatement->close();

        if ($checkResult->num_rows > 0) {
            $errors[] = 'A category with this name already exists.';
        } else {
            $updateStatement = $connection->prepare('UPDATE categories SET name = ?, description = ?, status = ? WHERE id = ?');
            $updateStatement->bind_param('sssi', $name, $description, $status, $categoryId);
            $updateStatement->execute();
            $updateStatement->close();
            $connection->close();

            setFlashMessage('success', 'Category updated successfully.');
            header('Location: ' . BASE_URL . '/pages/categories/view_categories.php');
            exit;
        }
    }
}
$connection->close();

require_once ROOT_PATH . 'includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-semibold mb-1">Edit Category</h3>
        <p class="text-muted mb-0">Update category details.</p>
    </div>
    <a href="<?php echo BASE_URL; ?>/pages/categories/view_categories.php" class="btn btn-outline-secondary">Back</a>
</div>

<div class="card p-4">
    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger rounded-3" role="alert">
            <ul class="mb-0 ps-3">
                <?php foreach ($errors as $error): ?>
                    <li><?php echo htmlspecialchars($error); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="POST">
        <?php echo csrfInputField('edit_category'); ?>
        <div class="mb-3">
            <label class="form-label">Category Name</label>
            <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($name); ?>" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Description</label>
            <textarea name="description" class="form-control" rows="4"><?php echo htmlspecialchars($description); ?></textarea>
        </div>
        <div class="mb-3">
            <label class="form-label">Status</label>
            <select name="status" class="form-select">
                <option value="active" <?php echo $status === 'active' ? 'selected' : ''; ?>>Active</option>
                <option value="inactive" <?php echo $status === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Update Category</button>
    </form>
</div>

<?php require_once ROOT_PATH . 'includes/footer.php'; ?>
