<?php
require_once __DIR__ . '/../include/layout.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $variantId = (int) ($_POST['variant_id'] ?? 0);
    $action = $_POST['action'] ?? '';

    if ($action === 'remove') {
        unset($_SESSION['cart'][$variantId]);
    } elseif ($action === 'update') {
        $quantity = max(1, (int) ($_POST['quantity'] ?? 1));
        if (get_variant($variantId)) {
            $_SESSION['cart'][$variantId]['quantity'] = $quantity;
        }
    }
    redirect('cart.php');
}

customer_header('Cart');
$lines = cart_lines();
?>
<section class="page-title">
    <p class="eyebrow">Cart</p>
    <h1>Your order</h1>
</section>

<section class="panel">
    <?php if (!$lines): ?>
        <p>Your cart is empty.</p>
        <a class="btn primary" href="menu.php">Browse Menu</a>
    <?php else: ?>
        <div class="table-wrap">
            <table>
                <thead><tr><th>Item</th><th>Qty</th><th>Price</th><th>Total</th><th></th></tr></thead>
                <tbody>
                <?php foreach ($lines as $line): ?>
                    <tr>
                        <td>
                            <strong><?= e($line['variant']['product_name']) ?></strong><br>
                            <span><?= e($line['variant']['size']) ?> · <?= e($line['variant']['flavor']) ?></span>
                        </td>
                        <td>
                            <form method="post" class="inline-form">
                                <input type="hidden" name="variant_id" value="<?= (int) $line['variant']['id'] ?>">
                                <input type="hidden" name="action" value="update">
                                <input class="qty-input" type="number" name="quantity" min="1" value="<?= (int) $line['quantity'] ?>">
                                <button class="btn small" type="submit">Update</button>
                            </form>
                        </td>
                        <td><?= money($line['variant']['price']) ?></td>
                        <td><?= money($line['line_total']) ?></td>
                        <td>
                            <form method="post">
                                <input type="hidden" name="variant_id" value="<?= (int) $line['variant']['id'] ?>">
                                <input type="hidden" name="action" value="remove">
                                <button class="link-button" type="submit">Remove</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <div class="total-row"><span>Total</span><strong><?= money(cart_total()) ?></strong></div>
        <div class="actions-right">
            <a class="btn ghost" href="menu.php">Add More</a>
            <a class="btn primary" href="checkout.php">Checkout</a>
        </div>
    <?php endif; ?>
</section>
<?php customer_footer(); ?>

