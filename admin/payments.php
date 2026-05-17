<?php include 'includes/header.php'; ?>

<?php
// Handle Manual Payment Entry
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $loan_id = intval($_POST['loan_id']);
    $amount = floatval($_POST['amount']);
    $ref = sanitize($_POST['transaction_ref']);
    $method = sanitize($_POST['payment_method']);

    $stmt = $pdo->prepare("INSERT INTO payments (loan_id, amount, transaction_ref, payment_method) VALUES (?, ?, ?, ?)");
    $stmt->execute([$loan_id, $amount, $ref, $method]);

    // Check if loan is fully paid
    $loan = fetch("SELECT total_payable, (SELECT SUM(amount) FROM payments WHERE loan_id = ?) as total_paid FROM loans WHERE id = ?", [$loan_id, $loan_id]);
    if ($loan['total_paid'] >= $loan['total_payable']) {
        $pdo->prepare("UPDATE loans SET status = 'paid' WHERE id = ?")->execute([$loan_id]);
    }

    echo "<script>window.location.href='payments.php';</script>";
}

$payments = fetchAll("SELECT p.*, c.full_name, l.amount as loan_amount 
                    FROM payments p 
                    JOIN loans l ON p.loan_id = l.id 
                    JOIN clients c ON l.client_id = c.id 
                    ORDER BY p.paid_at DESC");

$active_loans = fetchAll("SELECT l.id, c.full_name, l.total_payable, (SELECT SUM(amount) FROM payments WHERE loan_id = l.id) as total_paid 
                        FROM loans l 
                        JOIN clients c ON l.client_id = c.id 
                        WHERE l.status IN ('disbursed', 'pending')");
?>

<div style="display: flex; gap: 2rem; align-items: flex-start;">

    <!-- Payment Logs -->
    <div class="table-card" style="flex: 1;">
        <div class="table-header">
            <h3>Payment History</h3>
        </div>
        <table>
            <thead>
                <tr>
                    <th>Ref</th>
                    <th>Client</th>
                    <th>Amount</th>
                    <th>Method</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($payments as $p): ?>
                <tr>
                    <td><code style="background: #f1f5f9; padding: 0.2rem 0.5rem; border-radius: 4px;"><?= $p['transaction_ref'] ?></code></td>
                    <td><strong><?= $p['full_name'] ?></strong></td>
                    <td><?= formatCurrency($p['amount']) ?></td>
                    <td><span class="badge" style="background: #e0f2fe; color: #0369a1;"><?= strtoupper($p['payment_method']) ?></span></td>
                    <td><?= date('M d, H:i', strtotime($p['paid_at'])) ?></td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($payments)): ?>
                <tr>
                    <td colspan="5" style="text-align: center; padding: 3rem; color: #94a3b8;">No payments recorded yet.</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Record Payment Form -->
    <div class="table-card" style="width: 350px; padding: 2rem;">
        <h3>Record Payment</h3>
        <form method="POST" action="" style="margin-top: 1.5rem;">
            <div class="form-group">
                <label>Select Loan</label>
                <select name="loan_id" required>
                    <?php foreach ($active_loans as $l): ?>
                        <?php $balance = $l['total_payable'] - $l['total_paid']; ?>
                        <option value="<?= $l['id'] ?>"><?= $l['full_name'] ?> (Bal: <?= formatCurrency($balance) ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Amount (KES)</label>
                <input type="number" name="amount" required>
            </div>
            <div class="form-group">
                <label>Transaction Ref (ID)</label>
                <input type="text" name="transaction_ref" required placeholder="e.g. RDR5T6Y7U8">
            </div>
            <div class="form-group">
                <label>Payment Method</label>
                <select name="payment_method">
                    <option value="mpesa">M-Pesa</option>
                    <option value="bank">Bank Transfer</option>
                    <option value="cash">Cash</option>
                </select>
            </div>
            <button type="submit" class="btn-apply">Submit Payment</button>
        </form>
    </div>

</div>

<?php include 'includes/footer.php'; ?>
