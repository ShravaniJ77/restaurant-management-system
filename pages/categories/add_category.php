<?php
require_once dirname(__DIR__, 2) . '/includes/config.php';
require_once ROOT_PATH . 'includes/auth.php';
require_once ROOT_PATH . 'includes/functions.php';

$pageTitle = 'Add Category';
$currentPage = 'categories';

$errors = [];
$name = '';
$description = '';
$status = 'active';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token and sanitize inputs
    $csrf = $_POST['csrf_token'] ?? '';
    if (!verifyCsrfToken($csrf, 'add_category')) {
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
        $connection = getDbConnection();

        // Check for uniqueness of category name
        $checkStatement = $connection->prepare('SELECT id FROM categories WHERE name = ? LIMIT 1');
        $checkStatement->bind_param('s', $name);
        $checkStatement->execute();
        $checkResult = $checkStatement->get_result();
        $checkStatement->close();

        if ($checkResult->num_rows > 0) {
            $errors[] = 'A category with this name already exists.';
        } else {
            $insertStatement = $connection->prepare('INSERT INTO categories (name, description, status) VALUES (?, ?, ?)');
            $insertStatement->bind_param('sss', $name, $description, $status);
            $insertStatement->execute();
            $insertStatement->close();
            $connection->close();

            setFlashMessage('success', 'Category added successfully.');
            header('Location: ' . BASE_URL . '/pages/categories/view_categories.php');
            exit;
        }
        $connection->close();
    }
}

require_once ROOT_PATH . 'includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-semibold mb-1">Add Category</h3>
        <p class="text-muted mb-0">Create a new food category for the menu.</p>
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
        <?php echo csrfInputField('add_category'); ?>
        <div class="mb-3">
            <label class="form-label">Category Name</label>
            <input type="text" name="name" class="form-control" placeholder="e.g. Starters" value="<?php echo htmlspecialchars($name); ?>" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Description</label>
            <textarea name="description" class="form-control" rows="4" placeholder="Short description"><?php echo htmlspecialchars($description); ?></textarea>
        </div>
        <div class="mb-3">
            <label class="form-label">Status</label>
            <select name="status" class="form-select">
                <option value="active" <?php echo $status === 'active' ? 'selected' : ''; ?>>Active</option>
                <option value="inactive" <?php echo $status === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Save Category</button>
    </form>
</div>

<?php require_once ROOT_PATH . 'includes/footer.php'; ?>
