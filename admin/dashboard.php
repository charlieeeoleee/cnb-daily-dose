<?php
require_once __DIR__ . '/../include/layout.php';
admin_header('Dashboard');

$todaySales = $pdo->query(
    "SELECT COALESCE(SUM(total), 0) total FROM (
        SELECT total FROM pos_transactions WHERE DATE(created_at) = CURDATE()
        UNION ALL
        SELECT total FROM online_orders WHERE status = 'Completed' AND DATE(created_at) = CURDATE()
    ) sales"
)->fetch()['total'];

$pendingOrders = $pdo->query("SELECT COUNT(*) count FROM online_orders WHERE status = 'Pending'")->fetch()['count'];
$bestSeller = $pdo->query(
    "SELECT product_name, SUM(quantity) qty FROM (
        SELECT product_name, quantity FROM pos_transaction_items
        UNION ALL
        SELECT product_name, quantity FROM online_order_items oi
        JOIN online_orders o ON o.id = oi.online_order_id
        WHERE o.status = 'Completed'
    ) sold GROUP BY product_name ORDER BY qty DESC LIMIT 1"
)->fetch();
$lowStock = low_stock_items();
?>
<section class="metric-grid">
    <article class="metric"><span>Today’s Sales</span><strong><?= money($todaySales) ?></strong></article>
    <article class="metric"><span>Pending Online Orders</span><strong><?= (int) $pendingOrders ?></strong></article>
    <article class="metric"><span>Low Stock Items</span><strong><?= count($lowStock) ?></strong></article>
    <article class="metric"><span>Best Seller</span><strong><?= e($bestSeller['product_name'] ?? 'No sales yet') ?></strong></article>
</section>

<section class="panel">
    <h2>Low Stock Alerts</h2>
    <?php if (!$lowStock): ?>
        <p>No low stock alerts.</p>
    <?php else: ?>
        <div class="table-wrap">
            <table>
                <thead><tr><th>Item</th><th>Stock</th><th>Alert Level</th></tr></thead>
                <tbody>
                <?php foreach ($lowStock as $item): ?>
                    <tr><td><?= e($item['name']) ?></td><td><?= e($item['current_stock']) ?> <?= e($item['unit']) ?></td><td><?= e($item['low_stock_level']) ?></td></tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>
<?php admin_footer(); ?>

