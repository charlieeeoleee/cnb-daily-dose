<?php
require_once __DIR__ . '/../include/layout.php';

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['customer_name'] ?? '');
    $contact = trim($_POST['contact_number'] ?? '');
    $orderType = $_POST['order_type'] ?? 'Pickup';
    $address = trim($_POST['delivery_address'] ?? '');
    $paymentMethod = $_POST['payment_method'] ?? 'Cash';
    $reference = trim($_POST['payment_reference'] ?? '');
    $lines = cart_lines();

    if ($name === '') { $errors[] = 'Customer name is required.'; }
    if ($contact === '') { $errors[] = 'Contact number is required.'; }
    if (!in_array($orderType, ['Pickup', 'Delivery'], true)) { $errors[] = 'Invalid order type.'; }
    if ($orderType === 'Delivery' && $address === '') { $errors[] = 'Delivery address is required.'; }
    if (!in_array($paymentMethod, ['Cash', 'GCash', 'Bank Transfer'], true)) { $errors[] = 'Invalid payment method.'; }
    if (!$lines) { $errors[] = 'Your cart is empty.'; }

    if (!$errors) {
        try {
            $pdo->beginTransaction();
            $total = cart_total();
            $orderNumber = next_order_number();
            $stmt = $pdo->prepare(
                'INSERT INTO online_orders
                 (order_number, customer_name, contact_number, order_type, delivery_address, payment_method, payment_reference, subtotal, total)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)'
            );
            $stmt->execute([$orderNumber, $name, $contact, $orderType, $address, $paymentMethod, $reference, $total, $total]);
            $orderId = (int) $pdo->lastInsertId();

            $itemStmt = $pdo->prepare(
                'INSERT INTO online_order_items
                 (online_order_id, product_id, variant_id, product_name, variant_label, quantity, unit_price, line_total)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
            );
            foreach ($lines as $line) {
                $variant = $line['variant'];
                $itemStmt->execute([
                    $orderId,
                    $variant['product_id'],
                    $variant['id'],
                    $variant['product_name'],
                    $variant['size'] . ' · ' . $variant['flavor'],
                    $line['quantity'],
                    $variant['price'],
                    $line['line_total'],
                ]);
            }
            create_payment('online_order', $orderId, $paymentMethod, $total, $reference);
            $pdo->commit();
            $_SESSION['cart'] = [];
            redirect('confirmation.php?order=' . urlencode($orderNumber));
        } catch (Exception $e) {
            $pdo->rollBack();
            $errors[] = 'Checkout failed: ' . $e->getMessage();
        }
    }
}

customer_header('Checkout');
?>
<section class="page-title">
    <p class="eyebrow">Checkout</p>
    <h1>Customer details</h1>
</section>

<section class="checkout-grid">
    <form class="panel form-stack" method="post">
        <?php foreach ($errors as $error): ?><div class="alert error"><?= e($error) ?></div><?php endforeach; ?>
        <label>Customer name<input name="customer_name" required></label>
        <label>Contact number<input name="contact_number" required></label>
        <label>Order type
            <select name="order_type" data-delivery-toggle>
                <option>Pickup</option>
                <option>Delivery</option>
            </select>
        </label>
        <label data-delivery-address>Delivery address<textarea name="delivery_address" rows="3"></textarea></label>
        <label>Payment method
            <select name="payment_method">
                <option>Cash</option>
                <option>GCash</option>
                <option>Bank Transfer</option>
            </select>
        </label>
        <label>Payment reference number <span class="muted">(optional)</span><input name="payment_reference"></label>
        <button class="btn primary" type="submit">Place Order</button>
    </form>
    <aside class="panel">
        <h2>Summary</h2>
        <?php foreach (cart_lines() as $line): ?>
            <div class="summary-line">
                <span><?= e($line['variant']['product_name']) ?> x <?= (int) $line['quantity'] ?></span>
                <strong><?= money($line['line_total']) ?></strong>
            </div>
        <?php endforeach; ?>
        <div class="total-row"><span>Total</span><strong><?= money(cart_total()) ?></strong></div>
    </aside>
</section>
<?php customer_footer(); ?>

