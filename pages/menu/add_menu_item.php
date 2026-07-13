<?php
require_once dirname(__DIR__, 2) . '/includes/config.php';
require_once ROOT_PATH . 'includes/auth.php';
require_once ROOT_PATH . 'includes/functions.php';

$pageTitle = 'Add Menu Item';
$currentPage = 'menu';

$errors = [];
$name = '';
$categoryId = '';
$price = '';
$description = '';

// Load active categories so the form can show a dropdown.
$connection = getDbConnection();
$categoriesStatement = $connection->prepare('SELECT id, name FROM categories WHERE status = ? ORDER BY name ASC');
$status = 'active';
$categoriesStatement->bind_param('s', $status);
$categoriesStatement->execute();
$categoriesResult = $categoriesStatement->get_result();
$categories = $categoriesResult->fetch_all(MYSQLI_ASSOC);
$categoriesStatement->close();
$connection->close();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token and sanitize inputs
    $csrf = $_POST['csrf_token'] ?? '';
    if (!verifyCsrfToken($csrf, 'add_menu')) {
        $errors[] = 'Invalid request. Please try again.';
    }

    $name = sanitizeString($_POST['name'] ?? '');
    $categoryId = (int) ($_POST['category_id'] ?? 0);
    $price = trim($_POST['price'] ?? '');
    $description = sanitizeString($_POST['description'] ?? '');

    // Backend validation for beginner-friendly error handling.
    if ($name === '') {
        $errors[] = 'Menu item name is required.';
    }

    if ($categoryId <= 0) {
        $errors[] = 'Please select a valid category.';
    }

    if ($price === '' || !is_numeric($price) || (float) $price <= 0) {
        $errors[] = 'Price must be a positive number.';
    }

    if ($description === '') {
        $errors[] = 'Please add a short description.';
    }

    if (empty($errors)) {
        $uploadDirectory = ROOT_PATH . 'uploads' . DIRECTORY_SEPARATOR . 'menu_images';
        $uploadResult = uploadMenuImage($_FILES['image'] ?? [], $uploadDirectory);

        if (!$uploadResult['success']) {
            $errors[] = $uploadResult['message'];
        } else {
            $connection = getDbConnection();
            $sql = 'INSERT INTO menu_items (category_id, name, description, price, image_path, status) VALUES (?, ?, ?, ?, ?, ?)';
            $statement = $connection->prepare($sql);
            $statusValue = 'active';
            $imagePath = $uploadResult['file_name'];
            $statement->bind_param('issdss', $categoryId, $name, $description, $price, $imagePath, $statusValue);
            $statement->execute();
            $statement->close();
            $connection->close();

            setFlashMessage('success', 'Menu item added successfully.');
            header('Location: ' . BASE_URL . '/pages/menu/view_menu.php');
            exit;
        }
    }
}

require_once ROOT_PATH . 'includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-semibold mb-1">Add Menu Item</h3>
        <p class="text-muted mb-0">Create a new dish or beverage entry.</p>
    </div>
    <a href="<?php echo BASE_URL; ?>/pages/menu/view_menu.php" class="btn btn-outline-secondary">Back</a>
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

    <?php if (empty($categories)): ?>
        <div class="alert alert-warning rounded-3">No active categories are available yet. Please add a category first.</div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
        <?php echo csrfInputField('add_menu'); ?>
        <div class="mb-3">
            <label class="form-label">Food Name</label>
            <input type="text" name="name" class="form-control" placeholder="e.g. Grilled Chicken" value="<?php echo htmlspecialchars($name); ?>" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Category</label>
            <select name="category_id" class="form-select" required>
                <option value="">Select category</option>
                <?php foreach ($categories as $category): ?>
                    <option value="<?php echo (int) $category['id']; ?>" <?php echo $categoryId == (int) $category['id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($category['name']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="mb-3">
            <label class="form-label">Price</label>
            <input type="number" name="price" class="form-control" step="0.01" min="0.01" placeholder="0.00" value="<?php echo htmlspecialchars($price); ?>" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Description</label>
            <textarea name="description" class="form-control" rows="4" placeholder="Describe the item" required><?php echo htmlspecialchars($description); ?></textarea>
        </div>
        <div class="mb-3">
            <label class="form-label">Food Image</label>
            <input type="file" name="image" id="imageInput" class="form-control" accept="image/*" required>
            <div class="mt-3">
                <img id="imagePreview" class="menu-image-preview d-none" alt="Image preview">
            </div>
        </div>
        <button type="submit" class="btn btn-primary">Save Food Item</button>
    </form>
</div>

<script>
    const imageInput = document.getElementById('imageInput');
    const imagePreview = document.getElementById('imagePreview');

    if (imageInput && imagePreview) {
        imageInput.addEventListener('change', function () {
            const file = this.files[0];
            if (!file) {
                imagePreview.classList.add('d-none');
                return;
            }

            const reader = new FileReader();
            reader.onload = function (event) {
                imagePreview.src = event.target.result;
                imagePreview.classList.remove('d-none');
            };
            reader.readAsDataURL(file);
        });
    }
</script>

<?php require_once ROOT_PATH . 'includes/footer.php'; ?>
