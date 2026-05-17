<?php include 'includes/header.php'; ?>

<?php
// Handle Approval/Rejection
if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $action = $_GET['action'];
    $status = ($action === 'approve') ? 'approved' : 'rejected';
    $now = date('Y-m-d H:i:s');
    
    $stmt = $pdo->prepare("UPDATE loans SET status = ?, approved_at = ? WHERE id = ?");
    $stmt->execute([$status, $now, $id]);
    
    // If approved, we could trigger M-Pesa B2C here to disburse the funds
    // For now, we'll just update the status to 'disbursed' automatically if approved
    if ($status === 'approved') {
        $pdo->prepare("UPDATE loans SET status = 'disbursed', disbursed_at = ? WHERE id = ?")->execute([$now, $id]);
    }

    echo "<script>window.location.href='loans.php';</script>";
}

$loans = fetchAll("SELECT l.*, c.full_name, c.phone, p.name as product_name 
                 FROM loans l 
                 JOIN clients c ON l.client_id = c.id 
                 JOIN loan_products p ON l.product_id = p.id 
                 ORDER BY l.applied_at DESC");
?>

<div class="table-card">
    <div class="table-header">
        <h3>Loan Applications</h3>
    </div>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Client</th>
                <th>Product</th>
                <th>Amount</th>
                <th>Total Payable</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($loans as $loan): ?>
            <tr>
                <td>#<?= $loan['id'] ?></td>
                <td>
                    <strong><?= $loan['full_name'] ?></strong><br>
                    <small style="color: #64748b;"><?= $loan['phone'] ?></small>
                </td>
                <td><?= $loan['product_name'] ?></td>
                <td><?= formatCurrency($loan['amount']) ?></td>
                <td><?= formatCurrency($loan['total_payable']) ?></td>
                <td><span class="badge badge-<?= $loan['status'] ?>"><?= ucfirst($loan['status']) ?></span></td>
                <td>
                    <?php if ($loan['status'] === 'pending'): ?>
                        <a href="?action=approve&id=<?= $loan['id'] ?>" class="badge badge-approved" style="text-decoration: none;" onclick="return confirm('Approve and Disburse?')"><i class="fas fa-check"></i> Approve</a>
                        <a href="?action=reject&id=<?= $loan['id'] ?>" class="badge badge-rejected" style="text-decoration: none;" onclick="return confirm('Reject this loan?')"><i class="fas fa-times"></i> Reject</a>
                    <?php else: ?>
                        <span style="color: #94a3b8; font-size: 0.8rem;">Actioned</span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php include 'includes/footer.php'; ?>
