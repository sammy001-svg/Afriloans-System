<?php include 'includes/header.php'; ?>

<?php
$clients = fetchAll("SELECT * FROM clients ORDER BY created_at DESC");
?>

<div class="table-card">
    <div class="table-header">
        <h3>Client Records</h3>
    </div>
    <table>
        <thead>
            <tr>
                <th>Full Name</th>
                <th>Phone</th>
                <th>ID Number</th>
                <th>Email</th>
                <th>Status</th>
                <th>Joined</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($clients as $c): ?>
            <tr>
                <td><strong><?= $c['full_name'] ?></strong></td>
                <td><?= $c['phone'] ?></td>
                <td><?= $c['id_number'] ?></td>
                <td><?= $c['email'] ?></td>
                <td><span class="badge badge-<?= $c['status'] == 'active' ? 'approved' : 'rejected' ?>"><?= ucfirst($c['status']) ?></span></td>
                <td><?= date('M d, Y', strtotime($c['created_at'])) ?></td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($clients)): ?>
            <tr>
                <td colspan="6" style="text-align: center; padding: 3rem; color: #94a3b8;">No clients found.</td>
            </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include 'includes/footer.php'; ?>
