<?php
require_once dirname(__DIR__, 2) . '/includes/config.php';
require_once ROOT_PATH . 'includes/auth.php';
require_once ROOT_PATH . 'includes/functions.php';

$pageTitle = 'Create Order';
$currentPage = 'orders';

$errors = [];
$selectedCustomerId = (int) ($_POST['customer_id'] ?? 0);
$selectedStatus = sanitizeString($_POST['order_status'] ?? 'Pending');
$submittedItems = $_POST['items'] ?? [];

$statuses = ['Pending', 'Preparing', 'Ready', 'Served', 'Cancelled'];

$connection = getDbConnection();
$customersStatement = $connection->prepare('SELECT id, full_name FROM customers ORDER BY full_name ASC');
$customersStatement->execute();
$customers = $customersStatement->get_result()->fetch_all(MYSQLI_ASSOC);
$customersStatement->close();

$menuStatement = $connection->prepare('SELECT id, name, price FROM menu_items WHERE status = "active" ORDER BY name ASC');
$menuStatement->execute();
$menuItems = $menuStatement->get_result()->fetch_all(MYSQLI_ASSOC);
$menuStatement->close();
$connection->close();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token for order creation.
    $csrf = $_POST['csrf_token'] ?? '';
    if (!verifyCsrfToken($csrf, 'create_order')) {
        $errors[] = 'Invalid request. Please try again.';
    }
    // Validate the order form before creating the record.
    if ($selectedCustomerId <= 0) {
        $errors[] = 'Please select a customer.';
    }

    if (!in_array($selectedStatus, $statuses, true)) {
        $errors[] = 'Please select a valid order status.';
    }

    if (!is_array($submittedItems) || empty($submittedItems)) {
        $errors[] = 'Please add at least one food item to the order.';
    }

    $menuItemsById = [];
    foreach ($menuItems as $menuItem) {
        $menuItemsById[(int) $menuItem['id']] = $menuItem;
    }

    $validatedItems = [];
    $grandTotal = 0.0;

    if (empty($errors) && is_array($submittedItems)) {
        foreach ($submittedItems as $item) {
            $menuItemId = (int) ($item['menu_item_id'] ?? 0);
            $quantity = (int) ($item['quantity'] ?? 0);

            if ($menuItemId <= 0 || $quantity <= 0) {
                continue;
            }

            if (!isset($menuItemsById[$menuItemId])) {
                $errors[] = 'One of the selected menu items is no longer available.';
                break;
            }

            $menuItem = $menuItemsById[$menuItemId];
            $unitPrice = (float) $menuItem['price'];
            $lineTotal = $unitPrice * $quantity;
            $grandTotal += $lineTotal;

            $validatedItems[] = [
                'menu_item_id' => $menuItemId,
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'line_total' => $lineTotal,
            ];
        }
    }

    if (empty($errors) && empty($validatedItems)) {
        $errors[] = 'Please add at least one valid food item with a quantity greater than zero.';
    }

    if (empty($errors)) {
        // Save the order and its line items inside a database transaction.
        $connection = getDbConnection();
        $connection->begin_transaction();

        try {
            $orderStatement = $connection->prepare('INSERT INTO orders (customer_id, admin_id, total_amount, order_status) VALUES (?, ?, ?, ?)');
            $adminId = (int) ($_SESSION['admin_id'] ?? 0);
            $orderStatement->bind_param('iids', $selectedCustomerId, $adminId, $grandTotal, $selectedStatus);
            $orderStatement->execute();
            $orderId = $connection->insert_id;
            $orderStatement->close();

            $itemStatement = $connection->prepare('INSERT INTO order_items (order_id, menu_item_id, quantity, unit_price, line_total) VALUES (?, ?, ?, ?, ?)');
            foreach ($validatedItems as $item) {
                $itemStatement->bind_param('iiidd', $orderId, $item['menu_item_id'], $item['quantity'], $item['unit_price'], $item['line_total']);
                $itemStatement->execute();
            }
            $itemStatement->close();

            $connection->commit();
            setFlashMessage('success', 'Order created successfully.');
            header('Location: ' . BASE_URL . '/pages/orders/view_orders.php');
            exit;
        } catch (Exception $exception) {
            $connection->rollback();
            $errors[] = 'Unable to save the order right now. Please try again.';
        } finally {
            $connection->close();
        }
    }
}

require_once ROOT_PATH . 'includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-semibold mb-1">Create Order</h3>
        <p class="text-muted mb-0">Add a new customer order with multiple items.</p>
    </div>
    <a href="<?php echo BASE_URL; ?>/pages/orders/view_orders.php" class="btn btn-outline-secondary">Back</a>
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
        <?php echo csrfInputField('create_order'); ?>
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Select Customer</label>
                <select name="customer_id" class="form-select" required>
                    <option value="">Select customer</option>
                    <?php foreach ($customers as $customer): ?>
                        <option value="<?php echo (int) $customer['id']; ?>" <?php echo $selectedCustomerId === (int) $customer['id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($customer['full_name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label">Order Status</label>
                <select name="order_status" class="form-select">
                    <?php foreach ($statuses as $status): ?>
                        <option value="<?php echo htmlspecialchars($status); ?>" <?php echo $selectedStatus === $status ? 'selected' : ''; ?>><?php echo htmlspecialchars($status); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="mt-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <label class="form-label mb-0">Add Food Items</label>
                <button type="button" class="btn btn-outline-secondary btn-sm" id="add-item-btn">Add Item</button>
            </div>
            <div id="order-items">
                <div class="order-item-row row g-3 align-items-end mb-3">
                    <div class="col-md-7">
                        <label class="form-label">Food Item</label>
                        <select name="items[0][menu_item_id]" class="form-select item-select" required>
                            <option value="">Select food item</option>
                            <?php foreach ($menuItems as $menuItem): ?>
                                <option value="<?php echo (int) $menuItem['id']; ?>" data-price="<?php echo (float) $menuItem['price']; ?>"><?php echo htmlspecialchars($menuItem['name']); ?> - $<?php echo number_format((float) $menuItem['price'], 2); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Quantity</label>
                        <input type="number" name="items[0][quantity]" class="form-control quantity-input" min="1" value="1" required>
                    </div>
                    <div class="col-md-1">
                        <label class="form-label">Line Total</label>
                        <div class="line-total fw-semibold">$0.00</div>
                    </div>
                    <div class="col-md-1">
                        <button type="button" class="btn btn-outline-danger remove-item-btn">Remove</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-4">
            <label class="form-label">Order Total</label>
            <input type="text" id="grand-total" class="form-control" value="$0.00" readonly>
        </div>

        <button type="submit" class="btn btn-primary mt-4">Save Order</button>
    </form>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const container = document.getElementById('order-items');
        const addButton = document.getElementById('add-item-btn');
        const grandTotalInput = document.getElementById('grand-total');
        const templateRow = container.querySelector('.order-item-row');

        const updateGrandTotal = function () {
            let total = 0;
            container.querySelectorAll('.order-item-row').forEach(function (row) {
                const select = row.querySelector('.item-select');
                const quantityInput = row.querySelector('.quantity-input');
                const lineTotalElement = row.querySelector('.line-total');
                const selectedOption = select.options[select.selectedIndex];
                const price = selectedOption && selectedOption.dataset.price ? parseFloat(selectedOption.dataset.price) : 0;
                const quantity = parseInt(quantityInput.value, 10) || 0;
                const lineTotal = price * quantity;
                total += lineTotal;
                lineTotalElement.textContent = '$' + lineTotal.toFixed(2);
            });
            grandTotalInput.value = '$' + total.toFixed(2);
        };

        const attachEvents = function (row) {
            row.querySelector('.item-select').addEventListener('change', updateGrandTotal);
            row.querySelector('.quantity-input').addEventListener('input', updateGrandTotal);
            row.querySelector('.remove-item-btn').addEventListener('click', function () {
                const rows = container.querySelectorAll('.order-item-row');
                if (rows.length > 1) {
                    row.remove();
                    updateGrandTotal();
                }
            });
        };

        attachEvents(templateRow);

        addButton.addEventListener('click', function () {
            const newIndex = container.querySelectorAll('.order-item-row').length;
            const newRow = templateRow.cloneNode(true);
            newRow.querySelector('.item-select').name = 'items[' + newIndex + '][menu_item_id]';
            newRow.querySelector('.quantity-input').name = 'items[' + newIndex + '][quantity]';
            newRow.querySelector('.item-select').value = '';
            newRow.querySelector('.quantity-input').value = '1';
            newRow.querySelector('.line-total').textContent = '$0.00';
            container.appendChild(newRow);
            attachEvents(newRow);
            updateGrandTotal();
        });

        container.querySelectorAll('.quantity-input').forEach(function (input) {
            input.addEventListener('input', updateGrandTotal);
        });

        updateGrandTotal();
    });
</script>

<?php require_once ROOT_PATH . 'includes/footer.php'; ?>
