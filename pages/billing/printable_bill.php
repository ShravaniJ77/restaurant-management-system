<?php
require_once dirname(__DIR__, 2) . '/includes/config.php';
require_once ROOT_PATH . 'includes/auth.php';
require_once ROOT_PATH . 'includes/functions.php';

$pageTitle = 'Printable Bill';
$currentPage = 'billing';
$showLayout = false;

$selectedOrderId = (int) ($_GET['order_id'] ?? 0);
$selectedOrder = null;
$selectedOrderItems = [];

if ($selectedOrderId <= 0) {
    $selectedOrderId = 1;
}

$connection = getDbConnection();
$orderStatement = $connection->prepare('SELECT o.id, o.order_date, o.total_amount, o.payment_status, o.order_status, c.full_name AS customer_name, c.phone, c.email, c.address, a.full_name AS admin_name FROM orders o LEFT JOIN customers c ON c.id = o.customer_id LEFT JOIN admins a ON a.id = o.admin_id WHERE o.id = ? LIMIT 1');
$orderStatement->bind_param('i', $selectedOrderId);
$orderStatement->execute();
$selectedOrder = $orderStatement->get_result()->fetch_assoc();
$orderStatement->close();

if ($selectedOrder) {
    $itemStatement = $connection->prepare('SELECT oi.quantity, oi.unit_price, oi.line_total, mi.name AS menu_item_name FROM order_items oi LEFT JOIN menu_items mi ON mi.id = oi.menu_item_id WHERE oi.order_id = ? ORDER BY oi.id ASC');
    $itemStatement->bind_param('i', $selectedOrderId);
    $itemStatement->execute();
    $selectedOrderItems = $itemStatement->get_result()->fetch_all(MYSQLI_ASSOC);
    $itemStatement->close();
}

$connection->close();

$invoiceNumber = $selectedOrder ? 'INV-' . str_pad((string) $selectedOrder['id'], 4, '0', STR_PAD_LEFT) : '';
$invoiceDate = $selectedOrder ? date('d M Y, H:i', strtotime($selectedOrder['order_date'])) : '';

require_once ROOT_PATH . 'includes/header.php';
?>

<div class="container py-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
        <div>
            <h3 class="fw-semibold mb-1">Printable Invoice</h3>
            <p class="text-muted mb-0">A customer-ready invoice layout for printing.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="<?php echo BASE_URL; ?>/pages/billing/invoice.php?order_id=<?php echo (int) $selectedOrderId; ?>" class="btn btn-outline-secondary">Back</a>
            <button type="button" class="btn btn-primary" onclick="window.print()">Print Invoice</button>
        </div>
    </div>

    <?php if (!$selectedOrder): ?>
        <div class="card p-4">
            <?php echo renderEmptyState('No invoice found', 'The selected order does not exist or has no billing data.'); ?>
        </div>
    <?php else: ?>
        <div class="card p-4 invoice-printable">
            <div class="row g-3 align-items-start">
                <div class="col-md-8">
                    <h2 class="fw-bold mb-1">The Golden Spoon</h2>
                    <p class="text-muted mb-1">123 Restaurant Street, Downtown</p>
                    <p class="text-muted mb-0">Phone: +1 555 123 456 | Email: billing@thegoldenspoon.com</p>
                </div>
                <div class="col-md-4 text-md-end">
                    <h4 class="fw-semibold mb-1">Invoice <?php echo htmlspecialchars($invoiceNumber); ?></h4>
                    <p class="text-muted mb-1">Date: <?php echo htmlspecialchars($invoiceDate); ?></p>
                    <span class="badge bg-success"><?php echo htmlspecialchars($selectedOrder['order_status']); ?></span>
                </div>
            </div>

            <hr class="my-4">

            <div class="row g-4">
                <div class="col-md-6">
                    <h6 class="fw-semibold">Bill To</h6>
                    <p class="mb-1 fw-semibold"><?php echo htmlspecialchars($selectedOrder['customer_name'] ?? 'Guest'); ?></p>
                    <p class="mb-1 text-muted"><?php echo htmlspecialchars($selectedOrder['address'] ?? 'No address provided'); ?></p>
                    <p class="mb-1 text-muted">Phone: <?php echo htmlspecialchars($selectedOrder['phone'] ?? 'N/A'); ?></p>
                    <p class="mb-0 text-muted">Email: <?php echo htmlspecialchars($selectedOrder['email'] ?? 'N/A'); ?></p>
                </div>
                <div class="col-md-6">
                    <h6 class="fw-semibold">Order Summary</h6>
                    <p class="mb-1 text-muted">Order ID: #<?php echo (int) $selectedOrder['id']; ?></p>
                    <p class="mb-1 text-muted">Payment Status: <?php echo htmlspecialchars(ucfirst($selectedOrder['payment_status'] ?? 'unpaid')); ?></p>
                    <p class="mb-1 text-muted">Served By: <?php echo htmlspecialchars($selectedOrder['admin_name'] ?? 'System'); ?></p>
                    <p class="mb-0 text-muted">Order Time: <?php echo htmlspecialchars($invoiceDate); ?></p>
                </div>
            </div>

            <div class="table-responsive mt-4">
                <table class="table table-bordered align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Item</th>
                            <th>Price</th>
                            <th>Quantity</th>
                            <th>Line Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($selectedOrderItems as $item): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($item['menu_item_name'] ?? 'Unknown item'); ?></td>
                                <td>$<?php echo number_format((float) $item['unit_price'], 2); ?></td>
                                <td><?php echo (int) $item['quantity']; ?></td>
                                <td>$<?php echo number_format((float) $item['line_total'], 2); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="row justify-content-end mt-3">
                <div class="col-md-4">
                    <div class="d-flex justify-content-between border-top pt-3">
                        <span class="fw-semibold">Grand Total</span>
                        <span class="fw-semibold">$<?php echo number_format((float) $selectedOrder['total_amount'], 2); ?></span>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php require_once ROOT_PATH . 'includes/footer.php'; ?>
