<?php
require_once __DIR__ . '/../include/layout.php';
require_role(['admin', 'staff']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $stmt = $pdo->prepare('INSERT INTO expenses (expense_category, amount, expense_date, notes, created_by) VALUES (?, ?, ?, ?, ?)');
        $stmt->execute([
            trim($_POST['expense_category']),
            (float) $_POST['amount'],
            $_POST['expense_date'],
            trim($_POST['notes']),
            $_SESSION['user']['id'],
        ]);
        flash('success', 'Expense saved.');
    } catch (Exception $e) {
        flash('error', 'Could not save expense: ' . $e->getMessage());
    }
    redirect('expenses.php');
}

admin_header('Expense Tracking');
$expenses = $pdo->query('SELECT e.*, u.full_name FROM expenses e LEFT JOIN users u ON u.id = e.created_by ORDER BY expense_date DESC, e.id DESC LIMIT 100')->fetchAll();
$monthTotal = $pdo->query("SELECT COALESCE(SUM(amount),0) total FROM expenses WHERE YEAR(expense_date)=YEAR(CURDATE()) AND MONTH(expense_date)=MONTH(CURDATE())")->fetch()['total'];
?>
<?php if ($msg = flash('success')): ?><div class="alert success"><?= e($msg) ?></div><?php endif; ?>
<?php if ($msg = flash('error')): ?><div class="alert error"><?= e($msg) ?></div><?php endif; ?>
<section class="split-grid">
    <form class="panel form-stack" method="post">
        <h2>Add Expense</h2>
        <label>Expense category<input name="expense_category" placeholder="Ingredients, packaging, delivery" required></label>
        <label>Amount<input type="number" step="0.01" name="amount" required></label>
        <label>Date<input type="date" name="expense_date" value="<?= date('Y-m-d') ?>" required></label>
        <label>Notes<textarea name="notes" rows="3"></textarea></label>
        <button class="btn primary">Save Expense</button>
    </form>
    <section class="metric">
        <span>This Month’s Expenses</span>
        <strong><?= money($monthTotal) ?></strong>
    </section>
</section>
<section class="panel">
    <h2>Recent Expenses</h2>
    <div class="table-wrap">
        <table>
            <thead><tr><th>Date</th><th>Category</th><th>Amount</th><th>Notes</th><th>Encoded By</th></tr></thead>
            <tbody>
            <?php foreach ($expenses as $expense): ?>
                <tr>
                    <td><?= e($expense['expense_date']) ?></td>
                    <td><?= e($expense['expense_category']) ?></td>
                    <td><?= money($expense['amount']) ?></td>
                    <td><?= e($expense['notes']) ?></td>
                    <td><?= e($expense['full_name'] ?? '') ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>
<?php admin_footer(); ?>
