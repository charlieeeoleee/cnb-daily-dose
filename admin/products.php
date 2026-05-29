<?php
require_once __DIR__ . '/../include/layout.php';
require_role(['admin', 'staff']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    try {
        if ($action === 'add_product') {
            $stmt = $pdo->prepare('INSERT INTO products (name, category, description, image_path) VALUES (?, ?, ?, ?)');
            $stmt->execute([
                trim($_POST['name']),
                $_POST['category'],
                trim($_POST['description']),
                '../assets/img/coffee-classic.svg',
            ]);
            flash('success', 'Product added.');
        } elseif ($action === 'add_variant') {
            $stmt = $pdo->prepare('INSERT INTO product_variants (product_id, size, flavor, price) VALUES (?, ?, ?, ?)');
            $stmt->execute([(int) $_POST['product_id'], trim($_POST['size']), trim($_POST['flavor']), (float) $_POST['price']]);
            flash('success', 'Variant added.');
        } elseif ($action === 'archive') {
            $pdo->prepare('UPDATE products SET is_active = 0 WHERE id = ?')->execute([(int) $_POST['product_id']]);
            flash('success', 'Product archived.');
        } elseif ($action === 'update_product') {
            $stmt = $pdo->prepare('UPDATE products SET name = ?, category = ?, description = ?, is_active = ? WHERE id = ?');
            $stmt->execute([trim($_POST['name']), $_POST['category'], trim($_POST['description']), (int) isset($_POST['is_active']), (int) $_POST['product_id']]);
            flash('success', 'Product updated.');
        }
    } catch (Exception $e) {
        flash('error', 'Action failed: ' . $e->getMessage());
    }
    redirect('products.php');
}

admin_header('Product Management');
$products = get_products(false);
?>
<?php if ($msg = flash('success')): ?><div class="alert success"><?= e($msg) ?></div><?php endif; ?>
<?php if ($msg = flash('error')): ?><div class="alert error"><?= e($msg) ?></div><?php endif; ?>

<section class="split-grid">
    <form class="panel form-stack" method="post">
        <h2>Add Product</h2>
        <input type="hidden" name="action" value="add_product">
        <label>Name<input name="name" required></label>
        <label>Category<select name="category"><?php foreach (get_categories() as $cat): ?><option><?= e($cat) ?></option><?php endforeach; ?></select></label>
        <label>Description<textarea name="description" rows="3"></textarea></label>
        <button class="btn primary" type="submit">Save Product</button>
    </form>
    <form class="panel form-stack" method="post">
        <h2>Add Variant</h2>
        <input type="hidden" name="action" value="add_variant">
        <label>Product<select name="product_id"><?php foreach ($products as $p): ?><option value="<?= (int) $p['id'] ?>"><?= e($p['name']) ?></option><?php endforeach; ?></select></label>
        <label>Size<input name="size" placeholder="250ml" required></label>
        <label>Flavor<input name="flavor" placeholder="Caramel" required></label>
        <label>Price<input type="number" step="0.01" name="price" required></label>
        <button class="btn primary" type="submit">Save Variant</button>
    </form>
</section>

<section class="panel">
    <h2>Products</h2>
    <div class="table-wrap">
        <table>
            <thead><tr><th>Name</th><th>Category</th><th>Variants</th><th>Status</th><th>Action</th></tr></thead>
            <tbody>
            <?php foreach ($products as $product): ?>
                <tr>
                    <td><?= e($product['name']) ?></td>
                    <td><?= e($product['category']) ?></td>
                    <td>
                        <?php foreach (get_variants_by_product($product['id']) as $variant): ?>
                            <span class="mini-tag"><?= e($variant['size']) ?> <?= e($variant['flavor']) ?> <?= money($variant['price']) ?></span>
                        <?php endforeach; ?>
                    </td>
                    <td><?= $product['is_active'] ? 'Active' : 'Archived' ?></td>
                    <td>
                        <details>
                            <summary>Edit</summary>
                            <form class="mini-form" method="post">
                                <input type="hidden" name="action" value="update_product">
                                <input type="hidden" name="product_id" value="<?= (int) $product['id'] ?>">
                                <input name="name" value="<?= e($product['name']) ?>">
                                <select name="category"><?php foreach (get_categories() as $cat): ?><option <?= $cat === $product['category'] ? 'selected' : '' ?>><?= e($cat) ?></option><?php endforeach; ?></select>
                                <textarea name="description"><?= e($product['description']) ?></textarea>
                                <label class="check"><input type="checkbox" name="is_active" <?= $product['is_active'] ? 'checked' : '' ?>> Active</label>
                                <button class="btn small" type="submit">Update</button>
                            </form>
                        </details>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>
<?php admin_footer(); ?>

