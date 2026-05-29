<?php
require_once __DIR__ . '/../include/layout.php';
$orderNumber = $_GET['order'] ?? '';
$stmt = $pdo->prepare('SELECT * FROM online_orders WHERE order_number = ?');
$stmt->execute([$orderNumber]);
$order = $stmt->fetch();

customer_header('Order Confirmation');
?>
<section class="page-title">
    <p class="eyebrow">Order confirmation</p>
    <h1><?= $order ? 'Thank you for ordering' : 'Order not found' ?></h1>
</section>

<?php if ($order): ?>
    <?php
    $items = $pdo->prepare('SELECT * FROM online_order_items WHERE online_order_id = ?');
    $items->execute([$order['id']]);
    ?>
    <section class="panel receipt">
        <h2>Order #<?= e($order['order_number']) ?></h2>
        <p>Status: <strong><?= e($order['status']) ?></strong></p>
        <p><?= e($order['customer_name']) ?> · <?= e($order['contact_number']) ?></p>
        <?php foreach ($items->fetchAll() as $item): ?>
            <div class="summary-line">
                <span><?= e($item['product_name']) ?> (<?= e($item['variant_label']) ?>) x <?= (int) $item['quantity'] ?></span>
                <strong><?= money($item['line_total']) ?></strong>
            </div>
        <?php endforeach; ?>
        <div class="total-row"><span>Total</span><strong><?= money($order['total']) ?></strong></div>
        <a class="btn primary" href="menu.php">Order Again</a>
    </section>
<?php else: ?>
    <section class="panel"><a class="btn primary" href="menu.php">Return to Menu</a></section>
<?php endif; ?>
<?php customer_footer(); ?>

