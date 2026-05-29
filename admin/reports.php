<?php
require_once __DIR__ . '/../include/layout.php';
require_role(['admin', 'cashier', 'staff']);

admin_header('Sales Reports');
$daily = $pdo->query(
    "SELECT DATE(created_at) sale_date, SUM(total) total FROM (
        SELECT created_at, total FROM pos_transactions
        UNION ALL
        SELECT created_at, total FROM online_orders WHERE status = 'Completed'
    ) sales GROUP BY DATE(created_at) ORDER BY sale_date DESC LIMIT 14"
)->fetchAll();

$weeklyTotal = $pdo->query(
    "SELECT COALESCE(SUM(total),0) total FROM (
        SELECT total, created_at FROM pos_transactions
        UNION ALL
        SELECT total, created_at FROM online_orders WHERE status = 'Completed'
    ) sales WHERE YEARWEEK(created_at, 1) = YEARWEEK(CURDATE(), 1)"
)->fetch()['total'];

$monthlyTotal = $pdo->query(
    "SELECT COALESCE(SUM(total),0) total FROM (
        SELECT total, created_at FROM pos_transactions
        UNION ALL
        SELECT total, created_at FROM online_orders WHERE status = 'Completed'
    ) sales WHERE YEAR(created_at) = YEAR(CURDATE()) AND MONTH(created_at) = MONTH(CURDATE())"
)->fetch()['total'];

$paymentMethods = $pdo->query(
    "SELECT payment_method, SUM(amount) total FROM payments GROUP BY payment_method ORDER BY total DESC"
)->fetchAll();

$cashiers = $pdo->query(
    "SELECT u.full_name, COALESCE(SUM(pt.total),0) total
     FROM users u LEFT JOIN pos_transactions pt ON pt.cashier_id = u.id
     WHERE u.role IN ('admin','cashier')
     GROUP BY u.id ORDER BY total DESC"
)->fetchAll();

$best = $pdo->query(
    "SELECT product_name, SUM(quantity) qty, SUM(line_total) total FROM (
        SELECT product_name, quantity, line_total FROM pos_transaction_items
        UNION ALL
        SELECT oi.product_name, oi.quantity, oi.line_total FROM online_order_items oi
        JOIN online_orders o ON o.id = oi.online_order_id
        WHERE o.status = 'Completed'
    ) sold GROUP BY product_name ORDER BY qty DESC LIMIT 10"
)->fetchAll();
?>
<section class="metric-grid">
    <article class="metric"><span>This Week</span><strong><?= money($weeklyTotal) ?></strong></article>
    <article class="metric"><span>This Month</span><strong><?= money($monthlyTotal) ?></strong></article>
    <article class="metric"><span>Report Source</span><strong>POS + Online</strong></article>
    <article class="metric"><span>Completed Online Orders</span><strong>Included</strong></article>
</section>

<section class="report-grid">
    <article class="panel">
        <h2>Daily Sales</h2>
        <?php foreach ($daily as $row): ?><div class="summary-line"><span><?= e($row['sale_date']) ?></span><strong><?= money($row['total']) ?></strong></div><?php endforeach; ?>
    </article>
    <article class="panel">
        <h2>Sales by Payment Method</h2>
        <?php foreach ($paymentMethods as $row): ?><div class="summary-line"><span><?= e($row['payment_method']) ?></span><strong><?= money($row['total']) ?></strong></div><?php endforeach; ?>
    </article>
    <article class="panel">
        <h2>Sales by Cashier</h2>
        <?php foreach ($cashiers as $row): ?><div class="summary-line"><span><?= e($row['full_name']) ?></span><strong><?= money($row['total']) ?></strong></div><?php endforeach; ?>
    </article>
    <article class="panel">
        <h2>Best-Selling Products</h2>
        <?php foreach ($best as $row): ?><div class="summary-line"><span><?= e($row['product_name']) ?> x <?= (int) $row['qty'] ?></span><strong><?= money($row['total']) ?></strong></div><?php endforeach; ?>
    </article>
</section>
<?php admin_footer(); ?>
