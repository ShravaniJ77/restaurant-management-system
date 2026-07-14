<?php
require_once dirname(__DIR__, 2) . '/includes/config.php';
require_once ROOT_PATH . 'includes/auth.php';
require_once ROOT_PATH . 'includes/functions.php';

$pageTitle = 'Customers';
$currentPage = 'customers';
require_once ROOT_PATH . 'includes/header.php';

$searchTerm = trim($_GET['search'] ?? '');
$page = max(1, (int) ($_GET['page'] ?? 1));
$perPage = 10;

$connection = getDbConnection();

$sql = 'SELECT COUNT(*) AS total FROM customers WHERE 1=1';
$params = [];
$types = '';

if ($searchTerm !== '') {
    $sql .= ' AND (full_name LIKE ? OR phone LIKE ? OR email LIKE ?)';
    $searchValue = '%' . $searchTerm . '%';
    $params[] = $searchValue;
    $params[] = $searchValue;
    $params[] = $searchValue;
    $types = 'sss';
}

$countStatement = $connection->prepare($sql);
if (!empty($params)) {
    $countStatement->bind_param($types, ...$params);
}
$countStatement->execute();
$countResult = $countStatement->get_result();
$totalCustomers = (int) $countResult->fetch_assoc()['total'];
$countStatement->close();

$totalPages = max(1, (int) ceil($totalCustomers / $perPage));
$offset = ($page - 1) * $perPage;

$listSql = 'SELECT id, full_name, phone, email, address, created_at FROM customers WHERE 1=1';
$listParams = [];
$listTypes = '';

if ($searchTerm !== '') {
    $listSql .= ' AND (full_name LIKE ? OR phone LIKE ? OR email LIKE ?)';
    $searchValue = '%' . $searchTerm . '%';
    $listParams[] = $searchValue;
    $listParams[] = $searchValue;
    $listParams[] = $searchValue;
    $listTypes = 'sss';
}

$listSql .= ' ORDER BY created_at DESC LIMIT ? OFFSET ?';
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
$customers = $listResult->fetch_all(MYSQLI_ASSOC);
$listStatement->close();
$connection->close();
?>

<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
    <div>
        <h3 class="fw-semibold mb-1">Customers</h3>
        <p class="text-muted mb-0">Manage customer records and contact details.</p>
    </div>
    <a href="<?php echo BASE_URL; ?>/pages/customers/add_customer.php" class="btn btn-primary">Add Customer</a>
</div>

<div class="card p-4 mb-4">
    <form method="GET" class="row g-3 align-items-end">
        <div class="col-12 col-md-8">
            <label class="form-label">Search Customers</label>
            <input type="text" name="search" class="form-control" placeholder="Search by name, phone, or email" value="<?php echo htmlspecialchars($searchTerm); ?>">
        </div>
        <div class="col-12 col-md-4">
            <button type="submit" class="btn btn-primary w-100">Search</button>
        </div>
    </form>
</div>

<div class="card p-4">
    <?php if (empty($customers)): ?>
        <?php echo renderEmptyState('No customers yet', 'Add your first customer record to get started.'); ?>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr class="text-muted">
                        <th>Name</th>
                        <th>Phone</th>
                        <th>Email</th>
                        <th>Address</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($customers as $customer): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($customer['full_name']); ?></td>
                            <td><?php echo htmlspecialchars($customer['phone'] ?? ''); ?></td>
                            <td><?php echo htmlspecialchars($customer['email'] ?? ''); ?></td>
                            <td><?php echo htmlspecialchars($customer['address'] ?? ''); ?></td>
                            <td>
                                <div class="d-flex gap-2">
                                    <a href="<?php echo BASE_URL; ?>/pages/customers/edit_customer.php?id=<?php echo (int) $customer['id']; ?>" class="btn btn-sm btn-outline-primary">Edit</a>
                                    <form method="POST" action="<?php echo BASE_URL; ?>/pages/customers/delete_customer.php" onsubmit="return confirm('Delete this customer?');">
                                        <input type="hidden" name="id" value="<?php echo (int) $customer['id']; ?>">
                                        <?php echo csrfInputField('delete_customer'); ?>
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
                        <a class="page-link" href="<?php echo BASE_URL; ?>/pages/customers/view_customers.php?search=<?php echo urlencode($searchTerm); ?>&page=<?php echo max(1, $page - 1); ?>">Previous</a>
                    </li>
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                            <a class="page-link" href="<?php echo BASE_URL; ?>/pages/customers/view_customers.php?search=<?php echo urlencode($searchTerm); ?>&page=<?php echo $i; ?>"><?php echo $i; ?></a>
                        </li>
                    <?php endfor; ?>
                    <li class="page-item <?php echo $page >= $totalPages ? 'disabled' : ''; ?>">
                        <a class="page-link" href="<?php echo BASE_URL; ?>/pages/customers/view_customers.php?search=<?php echo urlencode($searchTerm); ?>&page=<?php echo min($totalPages, $page + 1); ?>">Next</a>
                    </li>
                </ul>
            </nav>
        <?php endif; ?>
    <?php endif; ?>
</div>

<?php require_once ROOT_PATH . 'includes/footer.php'; ?>
