<?php
// Store PHP sessions inside the project so XAMPP temp folder permissions do not block login/cart usage.
$sessionPath = __DIR__ . '/../storage/sessions';
if (!is_dir($sessionPath)) {
    mkdir($sessionPath, 0777, true);
}
if (session_status() === PHP_SESSION_NONE) {
    session_save_path($sessionPath);
    session_start();
}

require_once __DIR__ . '/../config/database.php';

function e($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function money($amount)
{
    return 'PHP ' . number_format((float) $amount, 2);
}

function redirect($path)
{
    header("Location: {$path}");
    exit;
}

function flash($key, $message = null)
{
    if ($message !== null) {
        $_SESSION['flash'][$key] = $message;
        return null;
    }

    if (!empty($_SESSION['flash'][$key])) {
        $message = $_SESSION['flash'][$key];
        unset($_SESSION['flash'][$key]);
        return $message;
    }

    return null;
}

function log_activity($action, $details = '')
{
    global $pdo;
    $userId = $_SESSION['user']['id'] ?? null;
    $stmt = $pdo->prepare('INSERT INTO activity_logs (user_id, action, details) VALUES (?, ?, ?)');
    $stmt->execute([$userId, $action, $details]);
}

function get_categories()
{
    return ['Bottled Coffee', 'Snacks', 'Bundles'];
}

function get_products($activeOnly = true)
{
    global $pdo;
    $sql = 'SELECT * FROM products';
    if ($activeOnly) {
        $sql .= ' WHERE is_active = 1';
    }
    $sql .= ' ORDER BY category, name';
    return $pdo->query($sql)->fetchAll();
}

function get_variants_by_product($productId)
{
    global $pdo;
    $stmt = $pdo->prepare('SELECT * FROM product_variants WHERE product_id = ? AND is_active = 1 ORDER BY price');
    $stmt->execute([$productId]);
    return $stmt->fetchAll();
}

function get_variant($variantId)
{
    global $pdo;
    $stmt = $pdo->prepare(
        'SELECT pv.*, p.name AS product_name, p.category, p.image_path
         FROM product_variants pv
         JOIN products p ON p.id = pv.product_id
         WHERE pv.id = ? AND pv.is_active = 1 AND p.is_active = 1'
    );
    $stmt->execute([$variantId]);
    return $stmt->fetch();
}

function get_cart()
{
    return $_SESSION['cart'] ?? [];
}

function cart_count()
{
    return array_sum(array_column(get_cart(), 'quantity'));
}

function cart_lines()
{
    $lines = [];
    foreach (get_cart() as $variantId => $item) {
        $variant = get_variant($variantId);
        if (!$variant) {
            continue;
        }
        $quantity = (int) $item['quantity'];
        $lineTotal = $quantity * (float) $variant['price'];
        $lines[] = [
            'variant' => $variant,
            'quantity' => $quantity,
            'line_total' => $lineTotal,
        ];
    }
    return $lines;
}

function cart_total()
{
    return array_sum(array_column(cart_lines(), 'line_total'));
}

function next_order_number()
{
    return 'CB-' . date('Ymd') . '-' . strtoupper(substr(bin2hex(random_bytes(3)), 0, 6));
}

function low_stock_items()
{
    global $pdo;
    return $pdo->query(
        'SELECT * FROM inventory_items
         WHERE current_stock <= low_stock_level
         ORDER BY current_stock ASC, name ASC'
    )->fetchAll();
}

function deduct_recipe_inventory($variantId, $quantity, $sourceType, $sourceId)
{
    global $pdo;
    $stmt = $pdo->prepare(
        'SELECT r.inventory_item_id, r.quantity_required, i.name, i.current_stock, i.unit
         FROM recipes r
         JOIN inventory_items i ON i.id = r.inventory_item_id
         WHERE r.variant_id = ?'
    );
    $stmt->execute([$variantId]);
    $recipes = $stmt->fetchAll();

    foreach ($recipes as $recipe) {
        $needed = (float) $recipe['quantity_required'] * (int) $quantity;
        if ((float) $recipe['current_stock'] < $needed) {
            throw new Exception('Insufficient stock for ' . $recipe['name']);
        }
    }

    foreach ($recipes as $recipe) {
        $deduct = (float) $recipe['quantity_required'] * (int) $quantity;
        $pdo->prepare('UPDATE inventory_items SET current_stock = current_stock - ? WHERE id = ?')
            ->execute([$deduct, $recipe['inventory_item_id']]);
        $pdo->prepare(
            'INSERT INTO stock_movements (inventory_item_id, movement_type, quantity, source_type, source_id, notes, user_id)
             VALUES (?, "deduct", ?, ?, ?, ?, ?)'
        )->execute([
            $recipe['inventory_item_id'],
            $deduct,
            $sourceType,
            $sourceId,
            'Auto deduction after sale',
            $_SESSION['user']['id'] ?? null,
        ]);
    }
}

function create_payment($sourceType, $sourceId, $method, $amount, $reference = null, $amountPaid = null)
{
    global $pdo;
    $stmt = $pdo->prepare(
        'INSERT INTO payments (source_type, source_id, payment_method, amount, amount_paid, reference_number)
         VALUES (?, ?, ?, ?, ?, ?)'
    );
    $stmt->execute([$sourceType, $sourceId, $method, $amount, $amountPaid, $reference]);
}

function require_login()
{
    if (empty($_SESSION['user'])) {
        redirect('../admin/login.php');
    }
}

function require_role(array $roles)
{
    require_login();
    if (!in_array($_SESSION['user']['role'], $roles, true)) {
        http_response_code(403);
        die('You do not have permission to access this page.');
    }
}

function current_user_name()
{
    return $_SESSION['user']['full_name'] ?? 'Guest';
}
