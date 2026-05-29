<?php
require_once __DIR__ . '/../include/layout.php';
require_role(['admin', 'cashier', 'staff']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $orderId = (int) ($_POST['order_id'] ?? 0);
    $status = $_POST['status'] ?? '';
    if (in_array($status, ['Accepted', 'Preparing', 'Ready', 'Completed', 'Cancelled'], true)) {
        try {
            $pdo->beginTransaction();
            $stmt = $pdo->prepare('SELECT * FROM online_orders WHERE id = ? FOR UPDATE');
            $stmt->execute([$orderId]);
            $order = $stmt->fetch();
            if (!$order) {
                throw new Exception('Order not found.');
            }
            if ($status === 'Completed' && !$order['inventory_deducted']) {
                $items = $pdo->prepare('SELECT * FROM online_order_items WHERE online_order_id = ?');
                $items->execute([$orderId]);
                foreach ($items->fetchAll() as $item) {
                    deduct_recipe_inventory($item['variant_id'], $item['quantity'], 'online_order', $orderId);
                }
                $pdo->prepare('UPDATE online_orders SET inventory_deducted = 1 WHERE id = ?')->execute([$orderId]);
            }
            $pdo->prepare('UPDATE online_orders SET status = ? WHERE id = ?')->execute([$status, $orderId]);
            log_activity('online_order_status', 'Order #' . $order['order_number'] . ' changed to ' . $status);
            $pdo->commit();
            flash('success', 'Order updated.');
        } catch (Exception $e) {
            $pdo->rollBack();
            flash('error', 'Update failed: ' . $e->getMessage());
        }
    }
    redirect('orders.php');
}

admin_header('Online Order Management');
$orders = $pdo->query('SELECT * FROM online_orders ORDER BY created_at DESC LIMIT 100')->fetchAll();
?>
<?php if ($msg = flash('success')): ?><div class="alert success"><?= e($msg) ?></div><?php endif; ?>
<?php if ($msg = flash('error')): ?><div class="alert error"><?= e($msg) ?></div><?php endif; ?>

<section class="panel">
    <h2>Online Orders</h2>
    <div class="table-wrap">
        <table>
            <thead><tr><th>Order</th><th>Customer</th><th>Type</th><th>Payment</th><th>Total</th><th>Status</th><th>Items</th><th>Action</th></tr></thead>
            <tbody>
            <?php foreach ($orders as $order): ?>
                <?php
                $itemStmt = $pdo->prepare('SELECT * FROM online_order_items WHERE online_order_id = ?');
                $itemStmt->execute([$order['id']]);
                $items = $itemStmt->fetchAll();
                ?>
                <tr>
                    <td><strong><?= e($order['order_number']) ?></strong><br><span><?= e($order['created_at']) ?></span></td>
                    <td><?= e($order['customer_name']) ?><br><span><?= e($order['contact_number']) ?></span></td>
                    <td><?= e($order['order_type']) ?><br><span><?= e($order['delivery_address']) ?></span></td>
                    <td><?= e($order['payment_method']) ?><br><span><?= e($order['payment_reference']) ?></span></td>
                    <td><?= money($order['total']) ?></td>
                    <td><span class="status"><?= e($order['status']) ?></span></td>
                    <td><?php foreach ($items as $item): ?><div><?= e($item['product_name']) ?> x <?= (int) $item['quantity'] ?></div><?php endforeach; ?></td>
                    <td>
                        <form method="post" class="inline-form">
                            <input type="hidden" name="order_id" value="<?= (int) $order['id'] ?>">
                            <select name="status">
                                <?php foreach (['Accepted', 'Preparing', 'Ready', 'Completed', 'Cancelled'] as $status): ?>
                                    <option <?= $status === $order['status'] ? 'selected' : '' ?>><?= e($status) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <button class="btn small">Update</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>
<?php admin_footer(); ?>

