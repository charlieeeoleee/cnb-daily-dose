<?php
require_once __DIR__ . '/../include/layout.php';
require_role(['admin']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $stmt = $pdo->prepare('INSERT INTO users (full_name, email, password_hash, role) VALUES (?, ?, ?, ?)');
        $stmt->execute([
            trim($_POST['full_name']),
            trim($_POST['email']),
            password_hash($_POST['password'], PASSWORD_DEFAULT),
            $_POST['role'],
        ]);
        flash('success', 'User account created.');
    } catch (Exception $e) {
        flash('error', 'Could not create user: ' . $e->getMessage());
    }
    redirect('users.php');
}

admin_header('User Management');
$users = $pdo->query('SELECT * FROM users ORDER BY role, full_name')->fetchAll();
?>
<?php if ($msg = flash('success')): ?><div class="alert success"><?= e($msg) ?></div><?php endif; ?>
<?php if ($msg = flash('error')): ?><div class="alert error"><?= e($msg) ?></div><?php endif; ?>

<section class="split-grid">
    <form class="panel form-stack" method="post">
        <h2>Create Account</h2>
        <label>Full name<input name="full_name" required></label>
        <label>Email<input type="email" name="email" required></label>
        <label>Password<input type="password" name="password" required></label>
        <label>Role<select name="role"><option value="cashier">Cashier</option><option value="staff">Staff</option><option value="admin">Owner/Admin</option></select></label>
        <button class="btn primary">Create User</button>
    </form>
    <section class="panel">
        <h2>Accounts</h2>
        <?php foreach ($users as $user): ?>
            <div class="summary-line"><span><?= e($user['full_name']) ?><br><?= e($user['email']) ?></span><strong><?= e($user['role']) ?></strong></div>
        <?php endforeach; ?>
    </section>
</section>
<?php admin_footer(); ?>

