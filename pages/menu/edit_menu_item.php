<?php
require_once dirname(__DIR__, 2) . '/includes/config.php';
require_once ROOT_PATH . 'includes/auth.php';
require_once ROOT_PATH . 'includes/functions.php';

$pageTitle = 'Edit Menu Item';
$currentPage = 'menu';

$itemId = (int) ($_GET['id'] ?? 0);
$errors = [];

$connection = getDbConnection();

if ($itemId <= 0) {
    setFlashMessage('error', 'Invalid menu item selected.');
    header('Location: ' . BASE_URL . '/pages/menu/view_menu.php');
    exit;
}

$statement = $connection->prepare('SELECT id, category_id, name, description, price, image_path FROM menu_items WHERE id = ? LIMIT 1');
$statement->bind_param('i', $itemId);
$statement->execute();
$itemResult = $statement->get_result();
$item = $itemResult->fetch_assoc();
$statement->close();

if (!$item) {
    setFlashMessage('error', 'The selected menu item was not found.');
    header('Location: ' . BASE_URL . '/pages/menu/view_menu.php');
    exit;
}

$categoriesStatement = $connection->prepare('SELECT id, name FROM categories WHERE status = ? ORDER BY name ASC');
$status = 'active';
$categoriesStatement->bind_param('s', $status);
$categoriesStatement->execute();
$categoriesResult = $categoriesStatement->get_result();
$categories = $categoriesResult->fetch_all(MYSQLI_ASSOC);
$categoriesStatement->close();
$connection->close();

$name = $item['name'];
$categoryId = $item['category_id'];
$price = $item['price'];
$description = $item['description'];
$existingImagePath = $item['image_path'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf = $_POST['csrf_token'] ?? '';
    if (!verifyCsrfToken($csrf, 'edit_menu')) {
        $errors[] = 'Invalid request. Please try again.';
    }

    $name = trim($_POST['name'] ?? '');
    $categoryId = (int) ($_POST['category_id'] ?? 0);
    $price = trim($_POST['price'] ?? '');
    $description = trim($_POST['description'] ?? '');

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
        $imagePathToSave = $existingImagePath;

        if (!empty($_FILES['image']['tmp_name'])) {
            $uploadDirectory = ROOT_PATH . 'uploads' . DIRECTORY_SEPARATOR . 'menu_images';
            $uploadResult = uploadMenuImage($_FILES['image'], $uploadDirectory);

            if (!$uploadResult['success']) {
                $errors[] = $uploadResult['message'];
            } else {
                $imagePathToSave = $uploadResult['file_name'];
                if (!empty($existingImagePath)) {
                    $oldFilePath = ROOT_PATH . 'uploads' . DIRECTORY_SEPARATOR . 'menu_images' . DIRECTORY_SEPARATOR . $existingImagePath;
                    if (file_exists($oldFilePath)) {
                        unlink($oldFilePath);
                    }
                }
            }
        }

        if (empty($errors)) {
            $connection = getDbConnection();
            $updateStatement = $connection->prepare('UPDATE menu_items SET category_id = ?, name = ?, description = ?, price = ?, image_path = ? WHERE id = ?');
            $updateStatement->bind_param('issdsi', $categoryId, $name, $description, $price, $imagePathToSave, $itemId);
            $updateStatement->execute();
            $updateStatement->close();
            $connection->close();

            setFlashMessage('success', 'Menu item updated successfully.');
            header('Location: ' . BASE_URL . '/pages/menu/view_menu.php');
            exit;
        }
    }
}

require_once ROOT_PATH . 'includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-semibold mb-1">Edit Menu Item</h3>
        <p class="text-muted mb-0">Update the selected food item details.</p>
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

    <form method="POST" enctype="multipart/form-data">
        <?php echo csrfInputField('edit_menu'); ?>
        <div class="mb-3">
            <label class="form-label">Food Name</label>
            <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($name); ?>" required>
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
            <input type="number" name="price" class="form-control" step="0.01" min="0.01" value="<?php echo htmlspecialchars($price); ?>" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Description</label>
            <textarea name="description" class="form-control" rows="4" required><?php echo htmlspecialchars($description); ?></textarea>
        </div>
        <div class="mb-3">
            <label class="form-label">Replace Image</label>
            <input type="file" name="image" id="imageInput" class="form-control" accept="image/*">
            <div class="mt-3">
                <?php if (!empty($existingImagePath)): ?>
                    <img id="imagePreview" src="<?php echo BASE_URL; ?>/uploads/menu_images/<?php echo htmlspecialchars($existingImagePath); ?>" alt="Current image" class="menu-image-preview">
                <?php else: ?>
                    <img id="imagePreview" class="menu-image-preview d-none" alt="Image preview">
                <?php endif; ?>
            </div>
        </div>
        <button type="submit" class="btn btn-primary">Update Food Item</button>
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
