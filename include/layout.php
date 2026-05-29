<?php
require_once __DIR__ . '/functions.php';

function customer_header($title = 'C&B Daily Dose')
{
    ?>
    <!doctype html>
    <html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title><?= e($title) ?> | C&B Daily Dose</title>
        <link rel="stylesheet" href="../assets/css/style.css">
    </head>
    <body>
    <header class="site-header">
        <a class="brand" href="index.php">
            <span class="brand-mark">C&B</span>
            <span>Daily Dose</span>
        </a>
        <nav class="nav">
            <a href="index.php">Home</a>
            <a href="menu.php">Menu</a>
            <a href="cart.php">Cart (<?= cart_count() ?>)</a>
            <a href="../admin/login.php">Admin</a>
        </nav>
    </header>
    <main>
    <?php
}

function customer_footer()
{
    ?>
    </main>
    <footer class="footer">C&B Daily Dose Ordering and POS System</footer>
    <script src="../assets/js/app.js"></script>
    </body>
    </html>
    <?php
}

function admin_header($title = 'Dashboard')
{
    require_login();
    ?>
    <!doctype html>
    <html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title><?= e($title) ?> | C&B Daily Dose</title>
        <link rel="stylesheet" href="../assets/css/style.css">
    </head>
    <body class="admin-body">
    <aside class="sidebar">
        <a class="brand admin-brand" href="../admin/dashboard.php">
            <span class="brand-mark">C&B</span>
            <span>POS System</span>
        </a>
        <nav class="side-nav">
            <a href="../admin/dashboard.php">Dashboard</a>
            <a href="../pos/index.php">POS</a>
            <a href="../admin/orders.php">Online Orders</a>
            <a href="../admin/products.php">Products</a>
            <a href="../admin/inventory.php">Inventory</a>
            <a href="../admin/reports.php">Reports</a>
            <a href="../admin/expenses.php">Expenses</a>
            <?php if ($_SESSION['user']['role'] === 'admin'): ?>
                <a href="../admin/users.php">Users</a>
            <?php endif; ?>
            <a href="../admin/logout.php">Logout</a>
        </nav>
    </aside>
    <main class="admin-main">
        <div class="admin-topbar">
            <div>
                <p class="eyebrow">C&B Daily Dose</p>
                <h1><?= e($title) ?></h1>
            </div>
            <div class="user-chip"><?= e(current_user_name()) ?> · <?= e($_SESSION['user']['role']) ?></div>
        </div>
    <?php
}

function admin_footer()
{
    ?>
    </main>
    <script src="../assets/js/app.js"></script>
    </body>
    </html>
    <?php
}
