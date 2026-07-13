<?php
require_once dirname(__DIR__, 2) . '/includes/config.php';
require_once ROOT_PATH . 'includes/auth.php';
require_once ROOT_PATH . 'includes/functions.php';

$pageTitle = 'Categories';
$currentPage = 'categories';
require_once ROOT_PATH . 'includes/header.php';

$searchTerm = trim($_GET['search'] ?? '');
$page = max(1, (int) ($_GET['page'] ?? 1));
$perPage = 10;

$connection = getDbConnection();

$sql = 'SELECT COUNT(*) AS total FROM categories WHERE 1=1';
$params = [];
$types = '';

if ($searchTerm !== '') {
    $sql .= ' AND (name LIKE ? OR description LIKE ?)';
    $searchValue = '%' . $searchTerm . '%';
    $params[] = $searchValue;
    $params[] = $searchValue;
    $types = 'ss';
}

$countStatement = $connection->prepare($sql);
if (!empty($params)) {
    $countStatement->bind_param($types, ...$params);
}
$countStatement->execute();
$countResult = $countStatement->get_result();
$totalCategories = (int) $countResult->fetch_assoc()['total'];
$countStatement->close();

$totalPages = max(1, (int) ceil($totalCategories / $perPage));
$offset = ($page - 1) * $perPage;

$listSql = 'SELECT id, name, description, status, created_at FROM categories WHERE 1=1';
$listParams = [];
$listTypes = '';

if ($searchTerm !== '') {
    $listSql .= ' AND (name LIKE ? OR description LIKE ?)';
    $searchValue = '%' . $searchTerm . '%';
    $listParams[] = $searchValue;
    $listParams[] = $searchValue;
    $listTypes = 'ss';
}

$listSql .= ' ORDER BY name ASC LIMIT ? OFFSET ?';
$listParams[] = $perPage;
$listParams[] = $offset;
$listTypes .= 'ii';

$listStatement = $connection->prepare($listSql);
if (!empty($listParams)) {
    $listReferences = [];
    foreach ($listParams as &$value) {
        $listReferences[] = &$value;
    }
    array_unshift($listReferences, $listTypes);
    call_user_func_array([$listStatement, 'bind_param'], $listReferences);
}
$listStatement->execute();
$listResult = $listStatement->get_result();
$categories = $listResult->fetch_all(MYSQLI_ASSOC);
$listStatement->close();
$connection->close();
?>

<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
    <div>
        <h3 class="fw-semibold mb-1">Categories</h3>
        <p class="text-muted mb-0">Manage food categories for the menu.</p>
    </div>
    <a href="<?php echo BASE_URL; ?>/pages/categories/add_category.php" class="btn btn-primary">Add Category</a>
</div>

<div class="card p-4 mb-4">
    <form method="GET" class="row g-3 align-items-end">
        <div class="col-12 col-md-8">
            <label class="form-label">Search Categories</label>
            <input type="text" name="search" class="form-control" placeholder="Search by name or description" value="<?php echo htmlspecialchars($searchTerm); ?>">
        </div>
        <div class="col-12 col-md-4">
            <button type="submit" class="btn btn-primary w-100">Search</button>
        </div>
    </form>
</div>

<div class="card p-4">
    <?php if (empty($categories)): ?>
        <?php echo renderEmptyState('No categories found', 'Add your first menu category to get started.'); ?>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr class="text-muted">
                        <th>Name</th>
                        <th>Description</th>
                        <th>Status</th>
                        <th>Created At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($categories as $category): ?>
                        <tr>
                            <td class="fw-semibold"><?php echo htmlspecialchars($category['name']); ?></td>
                            <td><?php echo htmlspecialchars($category['description'] ?? ''); ?></td>
                            <td>
                                <?php if (($category['status'] ?? 'active') === 'active'): ?>
                                    <span class="badge bg-success">Active</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Inactive</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars(date('M d, Y', strtotime($category['created_at']))); ?></td>
                            <td>
                                <div class="d-flex gap-2">
                                    <a href="<?php echo BASE_URL; ?>/pages/categories/edit_category.php?id=<?php echo (int) $category['id']; ?>" class="btn btn-sm btn-outline-primary">Edit</a>
                                    <form method="POST" action="<?php echo BASE_URL; ?>/pages/categories/delete_category.php" onsubmit="return confirm('Delete this category? Dishes referencing it will fail to load or delete.');">
                                        <input type="hidden" name="id" value="<?php echo (int) $category['id']; ?>">
                                        <?php echo csrfInputField('delete_category'); ?>
                                        <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <?php if ($totalPages > 1): ?>
            <nav class="mt-3">
                <ul class="pagination justify-content-center">
                    <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                        <a class="page-link" href="<?php echo BASE_URL; ?>/pages/categories/view_categories.php?search=<?php echo urlencode($searchTerm); ?>&page=<?php echo max(1, $page - 1); ?>">Previous</a>
                    </li>
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                            <a class="page-link" href="<?php echo BASE_URL; ?>/pages/categories/view_categories.php?search=<?php echo urlencode($searchTerm); ?>&page=<?php echo $i; ?>"><?php echo $i; ?></a>
                        </li>
                    <?php endfor; ?>
                    <li class="page-item <?php echo $page >= $totalPages ? 'disabled' : ''; ?>">
                        <a class="page-link" href="<?php echo BASE_URL; ?>/pages/categories/view_categories.php?search=<?php echo urlencode($searchTerm); ?>&page=<?php echo min($totalPages, $page + 1); ?>">Next</a>
                    </li>
                </ul>
            </nav>
        <?php endif; ?>
    <?php endif; ?>
</div>

<?php require_once ROOT_PATH . 'includes/footer.php'; ?>
