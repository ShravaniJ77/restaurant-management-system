<?php
require_once dirname(__DIR__, 2) . '/includes/config.php';
require_once ROOT_PATH . 'includes/auth.php';
require_once ROOT_PATH . 'includes/functions.php';

$pageTitle = 'Dashboard';
$currentPage = 'dashboard';
require_once ROOT_PATH . 'includes/header.php';

// Connect to the database and gather summary data for the dashboard cards and charts.
$connection = getDbConnection();

// Summary cards
$revenueQuery = $connection->prepare('SELECT COALESCE(SUM(total_amount), 0) AS total_revenue FROM orders');
$revenueQuery->execute();
$revenueResult = $revenueQuery->get_result();
$revenueRow = $revenueResult->fetch_assoc();
$revenueQuery->close();

$ordersQuery = $connection->prepare('SELECT COUNT(*) AS total_orders FROM orders');
$ordersQuery->execute();
$ordersRow = $ordersQuery->get_result()->fetch_assoc();
$ordersQuery->close();

$customersQuery = $connection->prepare('SELECT COUNT(*) AS total_customers FROM customers');
$customersQuery->execute();
$customersRow = $customersQuery->get_result()->fetch_assoc();
$customersQuery->close();

$menuQuery = $connection->prepare('SELECT COUNT(*) AS total_menu_items FROM menu_items');
$menuQuery->execute();
$menuRow = $menuQuery->get_result()->fetch_assoc();
$menuQuery->close();

// Recent orders for table
$recentOrdersQuery = $connection->prepare('SELECT o.id, c.full_name, o.total_amount, o.order_status, o.created_at FROM orders o LEFT JOIN customers c ON c.id = o.customer_id ORDER BY o.created_at DESC LIMIT 5');
$recentOrdersQuery->execute();
$recentOrders = $recentOrdersQuery->get_result()->fetch_all(MYSQLI_ASSOC);
$recentOrdersQuery->close();

// Top selling items (by quantity) for sidebar table
$topItemsQuery = $connection->prepare('SELECT mi.id, mi.name, SUM(oi.quantity) AS total_quantity, SUM(oi.line_total) AS total_sales FROM order_items oi JOIN menu_items mi ON mi.id = oi.menu_item_id GROUP BY mi.id, mi.name ORDER BY total_quantity DESC LIMIT 5');
$topItemsQuery->execute();
$topItems = $topItemsQuery->get_result()->fetch_all(MYSQLI_ASSOC);
$topItemsQuery->close();

// Revenue and orders for the last 7 days for charts
$revenueLast7 = [];
$ordersLast7 = [];
$labelsLast7 = [];

$dataQuery = $connection->prepare("SELECT DATE(order_date) AS day, COALESCE(SUM(total_amount),0) AS revenue, COUNT(*) AS orders_count FROM orders WHERE order_date >= DATE_SUB(CURDATE(), INTERVAL 6 DAY) GROUP BY day ORDER BY day ASC");
$dataQuery->execute();
$rows = $dataQuery->get_result()->fetch_all(MYSQLI_ASSOC);
$dataQuery->close();

// Normalize the last 7 days (including days with zero values).
$period = new DatePeriod(new DateTimeImmutable('-6 days'), new DateInterval('P1D'), 7);
$map = [];
foreach ($rows as $r) {
    $map[$r['day']] = $r;
}

foreach ($period as $d) {
    $day = $d->format('Y-m-d');
    $labelsLast7[] = $d->format('M d');
    if (isset($map[$day])) {
        $revenueLast7[] = (float) $map[$day]['revenue'];
        $ordersLast7[] = (int) $map[$day]['orders_count'];
    } else {
        $revenueLast7[] = 0;
        $ordersLast7[] = 0;
    }
}

$connection->close();
?>

<div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center mb-4">
    <div>
        <h3 class="fw-semibold mb-1">Dashboard</h3>
        <p class="text-muted mb-0">Monitor restaurant activity and performance.</p>
    </div>
    <div class="d-flex flex-wrap gap-2 mt-3 mt-lg-0">
        <a href="<?php echo BASE_URL; ?>/pages/orders/create_order.php" class="btn btn-primary">Create New Order</a>
        <a href="<?php echo BASE_URL; ?>/pages/menu/add_menu_item.php" class="btn btn-outline-success">Add Menu Item</a>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-12 col-md-6 col-xl-3">
        <div class="card p-4">
            <p class="text-muted mb-1">Total Revenue</p>
            <h3 class="fw-semibold">$<?php echo number_format((float) ($revenueRow['total_revenue'] ?? 0), 2); ?></h3>
        </div>
    </div>
    <div class="col-12 col-md-6 col-xl-3">
        <div class="card p-4">
            <p class="text-muted mb-1">Total Orders</p>
            <h3 class="fw-semibold"><?php echo (int) ($ordersRow['total_orders'] ?? 0); ?></h3>
        </div>
    </div>
    <div class="col-12 col-md-6 col-xl-3">
        <div class="card p-4">
            <p class="text-muted mb-1">Customers</p>
            <h3 class="fw-semibold"><?php echo (int) ($customersRow['total_customers'] ?? 0); ?></h3>
        </div>
    </div>
    <div class="col-12 col-md-6 col-xl-3">
        <div class="card p-4">
            <p class="text-muted mb-1">Menu Items</p>
            <h3 class="fw-semibold"><?php echo (int) ($menuRow['total_menu_items'] ?? 0); ?></h3>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-12 col-xl-8">
        <div class="card p-4">
            <h5 class="fw-semibold mb-3">Revenue Overview</h5>
            <div class="border rounded-4 p-3 bg-light" style="min-height: 320px;">
                <canvas id="revenueChart" height="160"></canvas>
            </div>

            <div class="mt-4">
                <h6 class="fw-semibold mb-2">Orders Overview (Last 7 days)</h6>
                <div class="border rounded-4 p-3 bg-light">
                    <canvas id="ordersChart" height="100"></canvas>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-xl-4">
        <div class="card p-4">
            <h5 class="fw-semibold mb-3">Quick Actions</h5>
            <div class="d-grid gap-2">
                <a href="<?php echo BASE_URL; ?>/pages/orders/create_order.php" class="btn btn-primary">Create New Order</a>
                <a href="<?php echo BASE_URL; ?>/pages/menu/add_menu_item.php" class="btn btn-outline-success">Add Menu Item</a>
                <a href="<?php echo BASE_URL; ?>/pages/customers/add_customer.php" class="btn btn-outline-danger">Add Customer</a>
            </div>
        </div>
    </div>
</div>

<div class="card p-4 mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h5 class="fw-semibold mb-1">Recent Orders</h5>
            <p class="text-muted mb-0">Latest customer orders from the system.</p>
        </div>
        <a href="<?php echo BASE_URL; ?>/pages/orders/view_orders.php" class="btn btn-sm btn-outline-primary">View All</a>
    </div>

    <div class="table-responsive">
        <table class="table align-middle">
            <thead>
                <tr class="text-muted">
                    <th>Order ID</th>
                    <th>Customer</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($recentOrders)): ?>
                    <?php foreach ($recentOrders as $order): ?>
                        <tr>
                            <td>#<?php echo (int) $order['id']; ?></td>
                            <td><?php echo htmlspecialchars($order['full_name'] ?? 'Guest'); ?></td>
                            <td>$<?php echo number_format((float) $order['total_amount'], 2); ?></td>
                            <td>
                                <span class="badge bg-info text-dark"><?php echo htmlspecialchars($order['order_status']); ?></span>
                            </td>
                            <td><?php echo htmlspecialchars(date('M d, Y', strtotime($order['created_at']))); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="text-center text-muted py-4">No orders found yet.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="card p-4 mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h5 class="fw-semibold mb-1">Top Selling Items</h5>
            <p class="text-muted mb-0">Most ordered menu items by quantity.</p>
        </div>
        <a href="<?php echo BASE_URL; ?>/pages/menu/view_menu.php" class="btn btn-sm btn-outline-primary">View Menu</a>
    </div>

    <div class="table-responsive">
        <table class="table align-middle">
            <thead>
                <tr class="text-muted">
                    <th>Item</th>
                    <th>Quantity Sold</th>
                    <th>Total Sales</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($topItems)): ?>
                    <?php foreach ($topItems as $item): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($item['name']); ?></td>
                            <td><?php echo (int) $item['total_quantity']; ?></td>
                            <td>$<?php echo number_format((float) $item['total_sales'], 2); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="3" class="text-center text-muted py-4">No sales data yet.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once ROOT_PATH . 'includes/footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
// Prepare chart data from PHP arrays (inlined for simplicity)
const labels = <?php echo json_encode($labelsLast7); ?>;
const revenueData = <?php echo json_encode($revenueLast7); ?>;
const ordersData = <?php echo json_encode($ordersLast7); ?>;

// Revenue chart configuration
const revenueCtx = document.getElementById('revenueChart');
if (revenueCtx) {
    new Chart(revenueCtx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Revenue',
                data: revenueData,
                borderColor: '#1E3A8A',
                backgroundColor: 'rgba(30,58,138,0.08)',
                tension: 0.3,
                fill: true,
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false } },
            scales: { y: { beginAtZero: true } }
        }
    });
}

// Orders chart configuration
const ordersCtx = document.getElementById('ordersChart');
if (ordersCtx) {
    new Chart(ordersCtx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Orders',
                data: ordersData,
                backgroundColor: '#F97316',
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false } },
            scales: { y: { beginAtZero: true, precision: 0 } }
        }
    });
}
</script>
