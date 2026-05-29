<?php
require_once __DIR__ . '/../include/layout.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $variantId = (int) ($_POST['variant_id'] ?? 0);
    $quantity = max(1, (int) ($_POST['quantity'] ?? 1));
    if (get_variant($variantId)) {
        $_SESSION['cart'][$variantId]['quantity'] = ($_SESSION['cart'][$variantId]['quantity'] ?? 0) + $quantity;
        flash('success', 'Product added to cart.');
    }
    redirect('menu.php');
}

$products = get_products(true);
customer_header('Online Menu');
?>
<section class="page-title">
    <p class="eyebrow">Online menu</p>
    <h1>Choose your daily dose</h1>
    <?php if ($msg = flash('success')): ?><div class="alert success"><?= e($msg) ?></div><?php endif; ?>
</section>

<?php foreach (get_categories() as $category): ?>
    <section class="section compact">
        <div class="section-heading inline">
            <h2><?= e($category) ?></h2>
        </div>
        <div class="menu-grid">
            <?php foreach ($products as $product): ?>
                <?php if ($product['category'] !== $category) { continue; } ?>
                <article class="product-card">
                    <img src="<?= e($product['image_path']) ?>" alt="<?= e($product['name']) ?>">
                    <div class="product-info">
                        <span class="pill"><?= e($product['category']) ?></span>
                        <h3><?= e($product['name']) ?></h3>
                        <p><?= e($product['description']) ?></p>
                        <?php foreach (get_variants_by_product($product['id']) as $variant): ?>
                            <form class="variant-row" method="post">
                                <div>
                                    <strong><?= e($variant['size']) ?> · <?= e($variant['flavor']) ?></strong>
                                    <span><?= money($variant['price']) ?></span>
                                </div>
                                <input type="hidden" name="variant_id" value="<?= (int) $variant['id'] ?>">
                                <input class="qty-input" type="number" name="quantity" value="1" min="1" aria-label="Quantity">
                                <button class="btn small" type="submit">Add</button>
                            </form>
                        <?php endforeach; ?>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    </section>
<?php endforeach; ?>
<?php customer_footer(); ?>

