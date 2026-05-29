<?php
require_once __DIR__ . '/../include/layout.php';
customer_header('Home');
?>
<section class="hero">
    <div class="hero-copy">
        <p class="eyebrow">Homemade bottled coffee and snacks</p>
        <h1>C&B Daily Dose</h1>
        <p>Fresh, chilled coffee bottles and snack pairings made for everyday pickup and local delivery.</p>
        <div class="hero-actions">
            <a class="btn primary" href="menu.php">Order Now</a>
            <a class="btn ghost" href="#highlights">View Highlights</a>
        </div>
    </div>
    <div class="hero-visual">
        <img src="../assets/img/hero-coffee.svg" alt="Bottled iced coffee display">
    </div>
</section>

<section id="highlights" class="section">
    <div class="section-heading">
        <p class="eyebrow">Menu highlights</p>
        <h2>Small-batch favorites</h2>
    </div>
    <div class="highlight-grid">
        <?php foreach (array_slice(get_products(true), 0, 3) as $product): ?>
            <article class="product-card">
                <img src="<?= e($product['image_path']) ?>" alt="<?= e($product['name']) ?>">
                <div>
                    <span class="pill"><?= e($product['category']) ?></span>
                    <h3><?= e($product['name']) ?></h3>
                    <p><?= e($product['description']) ?></p>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
</section>
<?php customer_footer(); ?>

