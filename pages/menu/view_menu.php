<?php
require_once dirname(__DIR__, 2) . '/includes/config.php';
require_once ROOT_PATH . 'includes/auth.php';
require_once ROOT_PATH . 'includes/functions.php';

$pageTitle = 'Menu';
$currentPage = 'menu';
require_once ROOT_PATH . 'includes/header.php';

// Read search and category filter values from the query string.
$searchTerm = trim($_GET['search'] ?? '');
$categoryId = (int) ($_GET['category'] ?? 0);

$connection = getDbConnection();

// Load active categories so the filter dropdown can be populated.
$categoriesStatement = $connection->prepare('SELECT id, name FROM categories WHERE status = ? ORDER BY name ASC');
$status = 'active';
$categoriesStatement->bind_param('s', $status);
$categoriesStatement->execute();
$categoriesResult = $categoriesStatement->get_result();
$categories = $categoriesResult->fetch_all(MYSQLI_ASSOC);
$categoriesStatement->close();

// Build the menu query dynamically with prepared statements for security.
$sql = 'SELECT m.id, m.name, m.description, m.price, m.image_path, c.name AS category_name FROM menu_items m LEFT JOIN categories c ON c.id = m.category_id WHERE 1=1';
$params = [];
$types = '';

if ($searchTerm !== '') {
    $sql .= ' AND m.name LIKE ?';
    $params[] = '%' . $searchTerm . '%';
    $types .= 's';
}

if ($categoryId > 0) {
    $sql .= ' AND m.category_id = ?';
    $params[] = $categoryId;
    $types .= 'i';
}

$sql .= ' ORDER BY m.created_at DESC';

$menuStatement = $connection->prepare($sql);
if (!empty($params)) {
    $references = [];
    foreach ($params as &$value) {
        $references[] = &$value;
    }

    array_unshift($references, $types);
    call_user_func_array([$menuStatement, 'bind_param'], $references);
}
$menuStatement->execute();
$menuResult = $menuStatement->get_result();
$menuItems = $menuResult->fetch_all(MYSQLI_ASSOC);
$menuStatement->close();
$connection->close();
?>

<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
    <div>
        <h3 class="fw-semibold mb-1">Menu Items</h3>
        <p class="text-muted mb-0">Manage dishes, beverages, and image-based menu entries.</p>
    </div>
    <a href="<?php echo BASE_URL; ?>/pages/menu/add_menu_item.php" class="btn btn-primary">Add Food Item</a>
</div>

<div class="card p-4 mb-4">
    <form method="GET" class="row g-3 align-items-end">
        <div class="col-12 col-md-6">
            <label class="form-label">Search by Name</label>
            <input type="text" name="search" class="form-control" placeholder="e.g. Burger" value="<?php echo htmlspecialchars($searchTerm); ?>">
        </div>
        <div class="col-12 col-md-4">
            <label class="form-label">Filter by Category</label>
            <select name="category" class="form-select">
                <option value="0">All Categories</option>
                <?php foreach ($categories as $category): ?>
                    <option value="<?php echo (int) $category['id']; ?>" <?php echo $categoryId === (int) $category['id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($category['name']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-12 col-md-2">
            <button type="submit" class="btn btn-primary w-100">Filter</button>
        </div>
    </form>
</div>

<div class="card p-4">
    <?php if (empty($menuItems)): ?>
        <?php echo renderEmptyState('No menu items yet', 'Create your first dish and it will appear here.'); ?>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr class="text-muted">
                        <th>Image</th>
                        <th>Name</th>
                        <th>Category</th>
                        <th>Price</th>
                        <th>Description</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($menuItems as $item): ?>
                        <tr>
                            <td>
                                <?php if (!empty($item['image_path'])): ?>
                                    <img src="<?php echo BASE_URL; ?>/uploads/menu_images/<?php echo htmlspecialchars($item['image_path']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" class="menu-image-preview">
                                <?php else: ?>
                                    <span class="text-muted">No image</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars($item['name']); ?></td>
                            <td><?php echo htmlspecialchars($item['category_name'] ?? 'Uncategorized'); ?></td>
                            <td>$<?php echo number_format((float) $item['price'], 2); ?></td>
                            <td><?php echo htmlspecialchars($item['description'] ?? ''); ?></td>
                            <td>
                                <div class="d-flex gap-2">
                                    <a href="<?php echo BASE_URL; ?>/pages/menu/edit_menu_item.php?id=<?php echo (int) $item['id']; ?>" class="btn btn-sm btn-outline-primary">Edit</a>
                                    <form method="POST" action="<?php echo BASE_URL; ?>/pages/menu/delete_menu_item.php" onsubmit="return confirm('Delete this menu item?');">
                                        <input type="hidden" name="id" value="<?php echo (int) $item['id']; ?>">
                                        <?php echo csrfInputField('delete_menu'); ?>
                                        <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php require_once ROOT_PATH . 'includes/footer.php'; ?>
