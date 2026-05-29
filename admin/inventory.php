<?php
require_once __DIR__ . '/../include/layout.php';
require_role(['admin', 'staff']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    try {
        if ($action === 'add_item') {
            $stmt = $pdo->prepare('INSERT INTO inventory_items (name, category, unit, current_stock, low_stock_level) VALUES (?, ?, ?, ?, ?)');
            $stmt->execute([trim($_POST['name']), trim($_POST['category']), trim($_POST['unit']), (float) $_POST['current_stock'], (float) $_POST['low_stock_level']]);
            flash('success', 'Inventory item added.');
        } elseif ($action === 'movement') {
            $itemId = (int) $_POST['inventory_item_id'];
            $type = $_POST['movement_type'];
            $qty = max(0, (float) $_POST['quantity']);
            if (!in_array($type, ['add', 'deduct', 'adjust'], true)) {
                throw new Exception('Invalid movement type.');
            }
            if ($type === 'add') {
                $pdo->prepare('UPDATE inventory_items SET current_stock = current_stock + ? WHERE id = ?')->execute([$qty, $itemId]);
            } elseif ($type === 'deduct') {
                $pdo->prepare('UPDATE inventory_items SET current_stock = GREATEST(current_stock - ?, 0) WHERE id = ?')->execute([$qty, $itemId]);
            } else {
                $pdo->prepare('UPDATE inventory_items SET current_stock = ? WHERE id = ?')->execute([$qty, $itemId]);
            }
            $stmt = $pdo->prepare('INSERT INTO stock_movements (inventory_item_id, movement_type, quantity, source_type, notes, user_id) VALUES (?, ?, ?, "manual", ?, ?)');
            $stmt->execute([$itemId, $type, $qty, trim($_POST['notes']), $_SESSION['user']['id']]);
            flash('success', 'Stock movement saved.');
        } elseif ($action === 'recipe') {
            $stmt = $pdo->prepare('INSERT INTO recipes (variant_id, inventory_item_id, quantity_required) VALUES (?, ?, ?)');
            $stmt->execute([(int) $_POST['variant_id'], (int) $_POST['inventory_item_id'], (float) $_POST['quantity_required']]);
            flash('success', 'Recipe ingredient added.');
        }
    } catch (Exception $e) {
        flash('error', 'Action failed: ' . $e->getMessage());
    }
    redirect('inventory.php');
}

admin_header('Inventory Management');
$items = $pdo->query('SELECT * FROM inventory_items ORDER BY category, name')->fetchAll();
$variants = $pdo->query(
    'SELECT pv.*, p.name product_name FROM product_variants pv JOIN products p ON p.id = pv.product_id ORDER BY p.name, pv.size'
)->fetchAll();
?>
<?php if ($msg = flash('success')): ?><div class="alert success"><?= e($msg) ?></div><?php endif; ?>
<?php if ($msg = flash('error')): ?><div class="alert error"><?= e($msg) ?></div><?php endif; ?>

<section class="split-grid">
    <form class="panel form-stack" method="post">
        <h2>Add Inventory Item</h2>
        <input type="hidden" name="action" value="add_item">
        <label>Name<input name="name" required></label>
        <label>Category<input name="category" placeholder="Raw Materials" required></label>
        <label>Unit<input name="unit" placeholder="ml, pcs, g" required></label>
        <label>Current stock<input type="number" step="0.01" name="current_stock" required></label>
        <label>Low stock level<input type="number" step="0.01" name="low_stock_level" required></label>
        <button class="btn primary">Save Item</button>
    </form>
    <form class="panel form-stack" method="post">
        <h2>Stock Movement</h2>
        <input type="hidden" name="action" value="movement">
        <label>Item<select name="inventory_item_id"><?php foreach ($items as $item): ?><option value="<?= (int) $item['id'] ?>"><?= e($item['name']) ?></option><?php endforeach; ?></select></label>
        <label>Type<select name="movement_type"><option value="add">Add stock</option><option value="deduct">Deduct stock</option><option value="adjust">Manual adjustment</option></select></label>
        <label>Quantity<input type="number" step="0.01" name="quantity" required></label>
        <label>Notes<textarea name="notes" rows="2"></textarea></label>
        <button class="btn primary">Save Movement</button>
    </form>
</section>

<section class="panel">
    <h2>Recipe / Product Ingredient Setup</h2>
    <form class="toolbar-form" method="post">
        <input type="hidden" name="action" value="recipe">
        <select name="variant_id"><?php foreach ($variants as $v): ?><option value="<?= (int) $v['id'] ?>"><?= e($v['product_name']) ?> · <?= e($v['size']) ?> · <?= e($v['flavor']) ?></option><?php endforeach; ?></select>
        <select name="inventory_item_id"><?php foreach ($items as $item): ?><option value="<?= (int) $item['id'] ?>"><?= e($item['name']) ?> (<?= e($item['unit']) ?>)</option><?php endforeach; ?></select>
        <input type="number" step="0.01" name="quantity_required" placeholder="Qty per sale" required>
        <button class="btn small">Connect</button>
    </form>
</section>

<section class="panel">
    <h2>Inventory Items</h2>
    <div class="table-wrap">
        <table>
            <thead><tr><th>Item</th><th>Category</th><th>Stock</th><th>Low Alert</th><th>Status</th></tr></thead>
            <tbody>
            <?php foreach ($items as $item): ?>
                <tr>
                    <td><?= e($item['name']) ?></td>
                    <td><?= e($item['category']) ?></td>
                    <td><?= e($item['current_stock']) ?> <?= e($item['unit']) ?></td>
                    <td><?= e($item['low_stock_level']) ?> <?= e($item['unit']) ?></td>
                    <td><?= $item['current_stock'] <= $item['low_stock_level'] ? '<span class="status danger">Low</span>' : '<span class="status ok">OK</span>' ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>
<?php admin_footer(); ?>

