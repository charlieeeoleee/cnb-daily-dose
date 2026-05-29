<?php
require_once __DIR__ . '/../include/layout.php';
require_role(['admin', 'cashier']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $quantities = $_POST['qty'] ?? [];
    $paymentMethod = $_POST['payment_method'] ?? 'Cash';
    $amountPaid = (float) ($_POST['amount_paid'] ?? 0);
    $lines = [];
    $total = 0;

    foreach ($quantities as $variantId => $qty) {
        $qty = (int) $qty;
        if ($qty <= 0) {
            continue;
        }
        $variant = get_variant((int) $variantId);
        if ($variant) {
            $lineTotal = $qty * (float) $variant['price'];
            $lines[] = ['variant' => $variant, 'quantity' => $qty, 'line_total' => $lineTotal];
            $total += $lineTotal;
        }
    }

    if (!$lines) {
        flash('error', 'Select at least one product.');
        redirect('index.php');
    }
    if (!in_array($paymentMethod, ['Cash', 'GCash', 'Card', 'Mixed'], true)) {
        flash('error', 'Invalid payment method.');
        redirect('index.php');
    }
    if ($amountPaid < $total) {
        flash('error', 'Amount paid is less than total.');
        redirect('index.php');
    }

    try {
        $pdo->beginTransaction();
        $receipt = 'POS-' . date('Ymd') . '-' . strtoupper(substr(bin2hex(random_bytes(3)), 0, 6));
        $stmt = $pdo->prepare(
            'INSERT INTO pos_transactions (receipt_number, cashier_id, subtotal, total, payment_method, amount_paid, change_amount)
             VALUES (?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([$receipt, $_SESSION['user']['id'], $total, $total, $paymentMethod, $amountPaid, $amountPaid - $total]);
        $transactionId = (int) $pdo->lastInsertId();
        $itemStmt = $pdo->prepare(
            'INSERT INTO pos_transaction_items
             (pos_transaction_id, product_id, variant_id, product_name, variant_label, quantity, unit_price, line_total)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
        );
        foreach ($lines as $line) {
            $v = $line['variant'];
            $itemStmt->execute([$transactionId, $v['product_id'], $v['id'], $v['product_name'], $v['size'] . ' · ' . $v['flavor'], $line['quantity'], $v['price'], $line['line_total']]);
            deduct_recipe_inventory($v['id'], $line['quantity'], 'pos', $transactionId);
        }
        create_payment('pos', $transactionId, $paymentMethod, $total, null, $amountPaid);
        log_activity('pos_sale', 'Completed receipt ' . $receipt);
        $pdo->commit();
        redirect('receipt.php?id=' . $transactionId);
    } catch (Exception $e) {
        $pdo->rollBack();
        flash('error', 'Sale failed: ' . $e->getMessage());
        redirect('index.php');
    }
}

admin_header('POS Screen');
$products = get_products(true);
?>
<?php if ($msg = flash('error')): ?><div class="alert error"><?= e($msg) ?></div><?php endif; ?>
<form class="pos-layout" method="post">
    <section class="panel">
        <h2>Products</h2>
        <div class="pos-grid">
            <?php foreach ($products as $product): ?>
                <?php foreach (get_variants_by_product($product['id']) as $variant): ?>
                    <label class="pos-item">
                        <span><?= e($product['name']) ?></span>
                        <small><?= e($variant['size']) ?> · <?= e($variant['flavor']) ?></small>
                        <strong><?= money($variant['price']) ?></strong>
                        <input type="number" min="0" value="0" name="qty[<?= (int) $variant['id'] ?>]">
                    </label>
                <?php endforeach; ?>
            <?php endforeach; ?>
        </div>
    </section>
    <aside class="panel form-stack">
        <h2>Transaction</h2>
        <label>Payment method<select name="payment_method"><option>Cash</option><option>GCash</option><option>Card</option><option>Mixed</option></select></label>
        <label>Amount paid<input type="number" step="0.01" name="amount_paid" required></label>
        <p class="muted">The receipt page will show total and change after completing the sale.</p>
        <button class="btn primary" type="submit">Complete Sale</button>
    </aside>
</form>
<?php admin_footer(); ?>

