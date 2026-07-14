<?php
require_once dirname(__DIR__, 2) . '/includes/config.php';
require_once ROOT_PATH . 'includes/auth.php';
require_once ROOT_PATH . 'includes/functions.php';

$pageTitle = 'Orders';
$currentPage = 'orders';
require_once ROOT_PATH . 'includes/header.php';

$searchTerm = trim($_GET['search'] ?? '');
$page = max(1, (int) ($_GET['page'] ?? 1));
$perPage = 10;

$connection = getDbConnection();

$sql = 'SELECT COUNT(*) AS total FROM orders o LEFT JOIN customers c ON c.id = o.customer_id WHERE 1=1';
$params = [];
$types = '';

if ($searchTerm !== '') {
    $sql .= ' AND (c.full_name LIKE ? OR o.order_status LIKE ? OR CAST(o.id AS CHAR) LIKE ?)';
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
$totalOrders = (int) $countResult->fetch_assoc()['total'];
$countStatement->close();

$totalPages = max(1, (int) ceil($totalOrders / $perPage));
$offset = ($page - 1) * $perPage;

$listSql = 'SELECT o.id, o.order_date, o.total_amount, o.order_status, o.payment_status, c.full_name AS customer_name FROM orders o LEFT JOIN customers c ON c.id = o.customer_id WHERE 1=1';
$listParams = [];
$listTypes = '';

if ($searchTerm !== '') {
    $listSql .= ' AND (c.full_name LIKE ? OR o.order_status LIKE ? OR CAST(o.id AS CHAR) LIKE ?)';
    $searchValue = '%' . $searchTerm . '%';
    $listParams[] = $searchValue;
    $listParams[] = $searchValue;
    $listParams[] = $searchValue;
    $listTypes = 'sss';
}

$listSql .= ' ORDER BY o.order_date DESC LIMIT ? OFFSET ?';
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
$orders = $listResult->fetch_all(MYSQLI_ASSOC);
$listStatement->close();
$connection->close();
?>

<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
    <div>
        <h3 class="fw-semibold mb-1">Orders</h3>
        <p class="text-muted mb-0">Review and manage customer orders.</p>
    </div>
    <a href="<?php echo BASE_URL; ?>/pages/orders/create_order.php" class="btn btn-primary">Create Order</a>
</div>

<div class="card p-4 mb-4">
    <form method="GET" class="row g-3 align-items-end">
        <div class="col-12 col-md-8">
            <label class="form-label">Search Orders</label>
            <input type="text" name="search" class="form-control" placeholder="Search by order ID, customer, or status" value="<?php echo htmlspecialchars($searchTerm); ?>">
        </div>
        <div class="col-12 col-md-4">
            <button type="submit" class="btn btn-primary w-100">Search</button>
        </div>
    </form>
</div>

<div class="card p-4">
    <?php if (empty($orders)): ?>
        <?php echo renderEmptyState('No orders yet', 'Create your first order to see it here.'); ?>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr class="text-muted">
                        <th>Order ID</th>
                        <th>Customer</th>
                        <th>Date</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $order): ?>
                        <tr>
                            <td>#<?php echo (int) $order['id']; ?></td>
                            <td><?php echo htmlspecialchars($order['customer_name'] ?? 'Guest'); ?></td>
                            <td><?php echo htmlspecialchars(date('M d, Y H:i', strtotime($order['order_date']))); ?></td>
                            <td>$<?php echo number_format((float) $order['total_amount'], 2); ?></td>
                            <td>
                                <?php
                                $statusClass = 'secondary';
                                if ($order['order_status'] === 'Preparing') {
                                    $statusClass = 'warning';
                                } elseif ($order['order_status'] === 'Ready') {
                                    $statusClass = 'info';
                                } elseif ($order['order_status'] === 'Served') {
                                    $statusClass = 'success';
                                } elseif ($order['order_status'] === 'Cancelled') {
                                    $statusClass = 'danger';
                                }
                                ?>
                                <span class="badge bg-<?php echo $statusClass; ?>"><?php echo htmlspecialchars($order['order_status']); ?></span>
                            </td>
                            <td>
                                <div class="d-flex gap-2">
                                    <a href="<?php echo BASE_URL; ?>/pages/orders/order_details.php?id=<?php echo (int) $order['id']; ?>" class="btn btn-sm btn-outline-primary">View</a>
                                    <form method="POST" action="<?php echo BASE_URL; ?>/pages/orders/delete_order.php" onsubmit="return confirm('Delete this order and its items?');">
                                        <input type="hidden" name="id" value="<?php echo (int) $order['id']; ?>">
                                        <?php echo csrfInputField('delete_order'); ?>
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
                        <a class="page-link" href="<?php echo BASE_URL; ?>/pages/orders/view_orders.php?search=<?php echo urlencode($searchTerm); ?>&page=<?php echo max(1, $page - 1); ?>">Previous</a>
                    </li>
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                            <a class="page-link" href="<?php echo BASE_URL; ?>/pages/orders/view_orders.php?search=<?php echo urlencode($searchTerm); ?>&page=<?php echo $i; ?>"><?php echo $i; ?></a>
                        </li>
                    <?php endfor; ?>
                    <li class="page-item <?php echo $page >= $totalPages ? 'disabled' : ''; ?>">
                        <a class="page-link" href="<?php echo BASE_URL; ?>/pages/orders/view_orders.php?search=<?php echo urlencode($searchTerm); ?>&page=<?php echo min($totalPages, $page + 1); ?>">Next</a>
                    </li>
                </ul>
            </nav>
        <?php endif; ?>
    <?php endif; ?>
</div>

<?php require_once ROOT_PATH . 'includes/footer.php'; ?>
