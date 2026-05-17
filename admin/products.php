<?php include 'includes/header.php'; ?>

<?php
// Handle Add/Edit/Delete
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name']);
    $desc = sanitize($_POST['description']);
    $rate = floatval($_POST['interest_rate']);
    $min = floatval($_POST['min_amount']);
    $max = floatval($_POST['max_amount']);
    $dur = intval($_POST['max_duration_months']);

    if (isset($_POST['id']) && !empty($_POST['id'])) {
        // Update
        $stmt = $pdo->prepare("UPDATE loan_products SET name=?, description=?, interest_rate=?, min_amount=?, max_amount=?, max_duration_months=? WHERE id=?");
        $stmt->execute([$name, $desc, $rate, $min, $max, $dur, $_POST['id']]);
    } else {
        // Insert
        $stmt = $pdo->prepare("INSERT INTO loan_products (name, description, interest_rate, min_amount, max_amount, max_duration_months) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$name, $desc, $rate, $min, $max, $dur]);
    }
    echo "<script>window.location.href='products.php';</script>";
}

if (isset($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM loan_products WHERE id = ?");
    $stmt->execute([$_GET['delete']]);
    echo "<script>window.location.href='products.php';</script>";
}

$products = fetchAll("SELECT * FROM loan_products ORDER BY created_at DESC");
?>

<div style="display: flex; gap: 2rem; align-items: flex-start;">
    
    <!-- Product List -->
    <div class="table-card" style="flex: 1;">
        <div class="table-header">
            <h3>Loan Products</h3>
        </div>
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Rate</th>
                    <th>Range</th>
                    <th>Duration</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($products as $p): ?>
                <tr>
                    <td><strong><?= $p['name'] ?></strong></td>
                    <td><?= $p['interest_rate'] ?>%</td>
                    <td><?= formatCurrency($p['min_amount']) ?> - <?= formatCurrency($p['max_amount']) ?></td>
                    <td>Up to <?= $p['max_duration_months'] ?> mo</td>
                    <td>
                        <a href="?edit=<?= $p['id'] ?>" style="color: var(--primary); margin-right: 1rem;"><i class="fas fa-edit"></i></a>
                        <a href="?delete=<?= $p['id'] ?>" style="color: #ef4444;" onclick="return confirm('Delete this product?')"><i class="fas fa-trash"></i></a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Add/Edit Form -->
    <div class="table-card" style="width: 350px; padding: 2rem;">
        <?php
        $edit_p = ['id' => '', 'name' => '', 'description' => '', 'interest_rate' => '', 'min_amount' => '', 'max_amount' => '', 'max_duration_months' => ''];
        if (isset($_GET['edit'])) {
            $edit_p = fetch("SELECT * FROM loan_products WHERE id = ?", [$_GET['edit']]);
        }
        ?>
        <h3><?= $edit_p['id'] ? 'Edit' : 'Add New' ?> Product</h3>
        <form method="POST" action="" style="margin-top: 1.5rem;">
            <input type="hidden" name="id" value="<?= $edit_p['id'] ?>">
            <div class="form-group">
                <label>Product Name</label>
                <input type="text" name="name" value="<?= $edit_p['name'] ?>" required>
            </div>
            <div class="form-group">
                <label>Interest Rate (%)</label>
                <input type="number" step="0.1" name="interest_rate" value="<?= $edit_p['interest_rate'] ?>" required>
            </div>
            <div class="form-group">
                <label>Min Amount</label>
                <input type="number" name="min_amount" value="<?= $edit_p['min_amount'] ?>" required>
            </div>
            <div class="form-group">
                <label>Max Amount</label>
                <input type="number" name="max_amount" value="<?= $edit_p['max_amount'] ?>" required>
            </div>
            <div class="form-group">
                <label>Max Duration (Months)</label>
                <input type="number" name="max_duration_months" value="<?= $edit_p['max_duration_months'] ?>" required>
            </div>
            <div class="form-group">
                <label>Description</label>
                <textarea name="description" rows="2" style="width: 100%; padding: 0.8rem; border-radius: var(--radius); border: 1px solid #e2e8f0;"><?= $edit_p['description'] ?></textarea>
            </div>
            <button type="submit" class="btn-apply"><?= $edit_p['id'] ? 'Update' : 'Create' ?> Product</button>
            <?php if ($edit_p['id']): ?>
                <a href="products.php" style="display: block; text-align: center; margin-top: 1rem; color: var(--secondary); text-decoration: none;">Cancel</a>
            <?php endif; ?>
        </form>
    </div>

</div>

<?php include 'includes/footer.php'; ?>
