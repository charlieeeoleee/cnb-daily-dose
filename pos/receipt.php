<?php
require_once __DIR__ . '/../include/layout.php';
require_role(['admin', 'cashier']);

$id = (int) ($_GET['id'] ?? 0);
$stmt = $pdo->prepare(
    'SELECT pt.*, u.full_name cashier_name
     FROM pos_transactions pt
     JOIN users u ON u.id = pt.cashier_id
     WHERE pt.id = ?'
);
$stmt->execute([$id]);
$transaction = $stmt->fetch();

admin_header('Receipt');
?>
<?php if (!$transaction): ?>
    <section class="panel"><p>Receipt not found.</p></section>
<?php else: ?>
    <?php
    $items = $pdo->prepare('SELECT * FROM pos_transaction_items WHERE pos_transaction_id = ?');
    $items->execute([$id]);
    ?>
    <section class="panel receipt">
        <div class="receipt-head">
            <span class="brand-mark">C&B</span>
            <h2>C&B Daily Dose</h2>
            <p>Receipt #<?= e($transaction['receipt_number']) ?></p>
            <p><?= e($transaction['created_at']) ?> · Cashier: <?= e($transaction['cashier_name']) ?></p>
        </div>
        <?php foreach ($items->fetchAll() as $item): ?>
            <div class="summary-line">
                <span><?= e($item['product_name']) ?><br><?= e($item['variant_label']) ?> x <?= (int) $item['quantity'] ?></span>
                <strong><?= money($item['line_total']) ?></strong>
            </div>
        <?php endforeach; ?>
        <div class="total-row"><span>Total</span><strong><?= money($transaction['total']) ?></strong></div>
        <div class="summary-line"><span>Payment</span><strong><?= e($transaction['payment_method']) ?></strong></div>
        <div class="summary-line"><span>Amount Paid</span><strong><?= money($transaction['amount_paid']) ?></strong></div>
        <div class="summary-line"><span>Change</span><strong><?= money($transaction['change_amount']) ?></strong></div>
        <div class="actions-right no-print">
            <button class="btn ghost" onclick="window.print()">Print</button>
            <a class="btn primary" href="index.php">New Sale</a>
        </div>
    </section>
<?php endif; ?>
<?php admin_footer(); ?>

