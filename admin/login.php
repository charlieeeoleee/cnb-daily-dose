<?php
require_once __DIR__ . '/../include/functions.php';

if (!empty($_SESSION['user'])) {
    redirect('dashboard.php');
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ? AND is_active = 1');
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password_hash'])) {
        $_SESSION['user'] = [
            'id' => $user['id'],
            'full_name' => $user['full_name'],
            'email' => $user['email'],
            'role' => $user['role'],
        ];
        log_activity('login', $email . ' logged in');
        redirect('dashboard.php');
    }
    $error = 'Invalid email or password.';
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login | C&B Daily Dose</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="login-body">
<form class="login-card" method="post">
    <span class="brand-mark">C&B</span>
    <h1>Admin / Cashier Login</h1>
    <p>C&B Daily Dose Ordering and POS System</p>
    <?php if ($error): ?><div class="alert error"><?= e($error) ?></div><?php endif; ?>
    <label>Email<input type="email" name="email" value="admin@cbdailydose.local" required></label>
    <label>Password<input type="password" name="password" value="password" required></label>
    <button class="btn primary" type="submit">Login</button>
</form>
</body>
</html>

