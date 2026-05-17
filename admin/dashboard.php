<?php include 'includes/header.php'; ?>

<?php
// Fetch Stats
$total_disbursed = fetch("SELECT SUM(amount) as total FROM loans WHERE status = 'disbursed' OR status = 'paid'")['total'] ?? 0;
$total_collected = fetch("SELECT SUM(amount) as total FROM payments")['total'] ?? 0;
$pending_apps = fetch("SELECT COUNT(*) as count FROM loans WHERE status = 'pending'")['count'];
$total_clients = fetch("SELECT COUNT(*) as count FROM clients")['count'];

$recent_loans = fetchAll("SELECT l.*, c.full_name, p.name as product_name 
                        FROM loans l 
                        JOIN clients c ON l.client_id = c.id 
                        JOIN loan_products p ON l.product_id = p.id 
                        ORDER BY l.applied_at DESC LIMIT 5");
?>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon" style="background: #e0f2fe; color: #0369a1;"><i class="fas fa-money-bill-wave"></i></div>
        <div>
            <p style="color: #64748b; font-size: 0.9rem;">Total Disbursed</p>
            <h3><?= formatCurrency($total_disbursed) ?></h3>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background: #dcfce7; color: #15803d;"><i class="fas fa-coins"></i></div>
        <div>
            <p style="color: #64748b; font-size: 0.9rem;">Total Collected</p>
            <h3><?= formatCurrency($total_collected) ?></h3>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background: #fef3c7; color: #a16207;"><i class="fas fa-clock"></i></div>
        <div>
            <p style="color: #64748b; font-size: 0.9rem;">Pending Apps</p>
            <h3><?= $pending_apps ?></h3>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background: #f1f5f9; color: #475569;"><i class="fas fa-users"></i></div>
        <div>
            <p style="color: #64748b; font-size: 0.9rem;">Active Clients</p>
            <h3><?= $total_clients ?></h3>
        </div>
    </div>
</div>

<div class="table-card">
    <div class="table-header">
        <h3>Recent Loan Applications</h3>
        <a href="loans.php" class="btn-admin" style="font-size: 0.8rem; padding: 0.4rem 1rem;">View All</a>
    </div>
    <table>
        <thead>
            <tr>
                <th>Client</th>
                <th>Product</th>
                <th>Amount</th>
                <th>Date</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($recent_loans as $loan): ?>
            <tr>
                <td><strong><?= $loan['full_name'] ?></strong></td>
                <td><?= $loan['product_name'] ?></td>
                <td><?= formatCurrency($loan['amount']) ?></td>
                <td><?= date('M d, Y', strtotime($loan['applied_at'])) ?></td>
                <td><span class="badge badge-<?= $loan['status'] ?>"><?= ucfirst($loan['status']) ?></span></td>
                <td>
                    <a href="loans_detail.php?id=<?= $loan['id'] ?>" style="color: var(--primary); text-decoration: none;"><i class="fas fa-eye"></i></a>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($recent_loans)): ?>
            <tr>
                <td colspan="6" style="text-align: center; padding: 3rem; color: #94a3b8;">No applications found.</td>
            </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include 'includes/footer.php'; ?>
