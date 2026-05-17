<?php 
require_once 'includes/db.php';
require_once 'includes/functions.php';
require_once 'includes/mpesa_helper.php';

$products = fetchAll("SELECT * FROM loan_products");
$success = false;
$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = sanitize($_POST['full_name']);
    $email = sanitize($_POST['email']);
    $phone = sanitize($_POST['phone']);
    $id_number = sanitize($_POST['id_number']);
    $address = sanitize($_POST['address']);
    $product_id = intval($_POST['product_id']);
    $amount = floatval($_POST['amount']);
    $duration = intval($_POST['duration']);
    
    $is_ajax = isset($_POST['ajax']) && $_POST['ajax'] == 1;
    $mpesa_phone = isset($_POST['mpesa_phone']) ? sanitize($_POST['mpesa_phone']) : $phone;

    try {
        $pdo->beginTransaction();

        // 1. Check if client exists or create
        $client = fetch("SELECT id FROM clients WHERE id_number = ? OR phone = ?", [$id_number, $phone]);
        if (!$client) {
            $stmt = $pdo->prepare("INSERT INTO clients (full_name, email, phone, id_number, address) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$full_name, $email, $phone, $id_number, $address]);
            $client_id = $pdo->lastInsertId();
        } else {
            $client_id = $client['id'];
        }

        // 2. Get product details for interest calculation
        $product = fetch("SELECT interest_rate FROM loan_products WHERE id = ?", [$product_id]);
        $interest_amount = ($amount * ($product['interest_rate'] / 100) * $duration);
        $total_payable = $amount + $interest_amount;

        // 3. Insert Loan Application
        $stmt = $pdo->prepare("INSERT INTO loans (client_id, product_id, amount, interest_amount, total_payable, duration_months, status) VALUES (?, ?, ?, ?, ?, ?, 'pending')");
        $stmt->execute([$client_id, $product_id, $amount, $interest_amount, $total_payable, $duration]);
        $loan_id = $pdo->lastInsertId();

        // 4. Initiate M-Pesa STK Push
        $formatted_phone = formatMpesaPhone($mpesa_phone);
        $processing_fee = 50; // KES 50.00
        $reference = "LOAN" . $loan_id;
        $description = "Processing Fee";
        
        $stk_response = initiateStkPush($formatted_phone, $processing_fee, $reference, $description);
        
        if (isset($stk_response['ResponseCode']) && $stk_response['ResponseCode'] == '0') {
            $checkoutRequestID = $stk_response['CheckoutRequestID'];
            
            // Update loan with checkoutRequestID
            $stmt = $pdo->prepare("UPDATE loans SET checkout_request_id = ? WHERE id = ?");
            $stmt->execute([$checkoutRequestID, $loan_id]);
        } else {
            throw new Exception("M-Pesa Error: " . ($stk_response['errorMessage'] ?? 'Failed to initiate STK Push'));
        }

        $pdo->commit();
        
        if ($is_ajax) {
            header('Content-Type: application/json');
            echo json_encode(['status' => 'success', 'message' => 'STK Push Initiated Successfully']);
            exit;
        }
        
        $success = true;
    } catch (Exception $e) {
        $pdo->rollBack();
        if ($is_ajax) {
            header('Content-Type: application/json');
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
            exit;
        }
        $error = "Error: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Apply for Loan | ZETA CREDIT</title>
    
    <!-- SEO Meta Tags -->
    <meta name="description" content="Secure your future with fast, low-interest loans from ZETA CREDIT. Simple application, instant approval.">
    <meta name="keywords" content="apply loan, ZETA CREDIT, fast credit, mobile loans">
    
    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="http://localhost:8000/apply.php">
    <meta property="og:title" content="Apply for Loan | ZETA CREDIT">
    <meta property="og:description" content="Quick, flexible, and affordable loans at your fingertips.">
    <meta property="og:image" content="assets/images/seo-share.png">

    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: url('assets/images/apply-bg.webp') no-repeat center center fixed;
            background-size: cover;
            min-height: 100vh;
            position: relative;
        }

        body::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            background: linear-gradient(135deg, rgba(15, 23, 42, 0.7) 0%, rgba(15, 23, 42, 0.5) 100%);
            z-index: 0;
        }

        nav, .apply-container {
            position: relative;
            z-index: 1;
        }

        .apply-container {
            max-width: 1000px;
            margin: 2rem auto;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            padding: 2rem;
            border-radius: 20px;
            box-shadow: var(--shadow-lg);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }
        .form-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1rem;
        }
        input, select, textarea {
            width: 100%;
            padding: 0.6rem;
            border: 1px solid #e2e8f0;
            border-radius: var(--radius);
            font-size: 0.95rem;
            outline: none;
        }
        label {
            font-size: 0.85rem;
            margin-bottom: 0.3rem;
            display: block;
            color: #64748b;
        }
        input:focus { border-color: var(--primary); }
        .full-width { grid-column: span 3; }
        .span-2 { grid-column: span 2; }
        .alert { padding: 1rem; border-radius: var(--radius); margin-bottom: 2rem; }
        .alert-success { background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
        .alert-error { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(15, 23, 42, 0.8);
            backdrop-filter: blur(8px);
            z-index: 2000;
            align-items: center;
            justify-content: center;
        }

        .modal-content {
            background: white;
            padding: 2.5rem;
            border-radius: 24px;
            max-width: 500px;
            width: 90%;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            animation: modalSlide 0.3s ease-out;
        }

        @keyframes modalSlide {
            from { transform: translateY(20px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        .modal-header {
            margin-bottom: 1.5rem;
            text-align: center;
        }

        .modal-header h3 {
            font-size: 1.5rem;
            color: var(--dark);
        }

        .summary-item {
            display: flex;
            justify-content: space-between;
            padding: 0.8rem 0;
            border-bottom: 1px solid #f1f5f9;
        }

        .summary-item span:first-child {
            color: #64748b;
            font-weight: 500;
        }

        .summary-item span:last-child {
            color: var(--dark);
            font-weight: 600;
        }

        .modal-footer {
            margin-top: 2rem;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }

        .btn-modal {
            padding: 0.8rem;
            border-radius: 12px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            text-align: center;
        }

        .btn-cancel {
            background: #f1f5f9;
            color: #475569;
            border: none;
        }

        .btn-confirm {
            background: var(--primary);
            color: white;
            border: none;
        }

        .btn-confirm:hover {
            background: var(--primary-hover);
        }

        .congrats-icon {
            font-size: 3.5rem;
            color: #10b981;
            margin-bottom: 1rem;
            display: block;
            text-align: center;
        }
        
        @media (max-width: 768px) {
            .apply-container {
                margin: 1rem;
                padding: 1.5rem;
            }
            .form-grid {
                grid-template-columns: 1fr;
            }
            .full-width, .span-2 {
                grid-column: span 1;
            }
            .modal-content {
                padding: 1.5rem;
            }
            .modal-footer {
                grid-template-columns: 1fr;
            }
            .btn-modal {
                grid-column: span 1 !important;
            }
        }
    </style>
</head>
<body>

    <nav>
        <a href="index.php" class="logo">
            <i class="fas fa-hand-holding-dollar"></i>
            ZETA <span>CREDIT</span>
        </a>
    </nav>

    <div class="apply-container">
        <h2>Loan Application Form</h2>
        <p style="color: var(--secondary); margin-bottom: 1.5rem; font-size: 0.9rem;">Complete the form below to submit your application.</p>

        <?php if ($success): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> Application submitted successfully! We will contact you soon.
            </div>
        <?php elseif ($error): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i> <?= $error ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-grid">
                <div class="form-group">
                    <label>Full Name</label>
                    <input type="text" name="full_name" required placeholder="John Doe">
                </div>
                <div class="form-group">
                    <label>Email Address</label>
                    <input type="email" name="email" required placeholder="john@example.com">
                </div>
                <div class="form-group">
                    <label>Phone Number (M-Pesa)</label>
                    <input type="text" name="phone" required placeholder="0712345678">
                </div>
                <div class="form-group">
                    <label>ID Number</label>
                    <input type="text" name="id_number" required placeholder="12345678">
                </div>
                <div class="form-group span-2">
                    <label>Physical Address</label>
                    <textarea name="address" rows="1" required placeholder="Nairobi, Kenya..."></textarea>
                </div>

                <div class="form-group">
                    <label>Loan Product</label>
                    <select name="product_id" required>
                        <?php foreach ($products as $product): ?>
                            <option value="<?= $product['id'] ?>"><?= $product['name'] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Amount (KES)</label>
                    <input type="number" name="amount" required min="1000" placeholder="5000">
                </div>
                <div class="form-group">
                    <label>Duration (Months)</label>
                    <input type="number" name="duration" required min="1" max="12" placeholder="1">
                </div>
            </div>

            <button type="button" id="btn-submit-trigger" class="btn-apply" style="margin-top: 1rem; padding: 0.8rem;">Submit Application</button>
            
            <!-- Hidden real submit button -->
            <button type="submit" id="real-submit" style="display: none;"></button>
        </form>
    </div>

    <!-- Modal 1: Loan Summary -->
    <div id="modal-summary" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Loan Summary</h3>
                <p style="color: #64748b; font-size: 0.9rem;">Please confirm your details below</p>
            </div>
            <div id="summary-details">
                <!-- Dynamic Content -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-modal btn-cancel" onclick="closeModal('modal-summary')">Edit Details</button>
                <button type="button" class="btn-modal btn-confirm" onclick="showPaymentModal()">Confirm & Proceed</button>
            </div>
        </div>
    </div>

    <!-- Modal 2: Payment & Congratulation -->
    <div id="modal-payment" class="modal">
        <div class="modal-content">
            <i class="fas fa-check-circle congrats-icon"></i>
            <div class="modal-header">
                <h3>Congratulations!</h3>
                <p style="color: #64748b; font-size: 0.9rem;">Your application is pre-approved.</p>
            </div>
            <p style="text-align: center; margin-bottom: 1.5rem; color: #475569; line-height: 1.5;">
                To finalize your application, please pay a processing fee of <strong>KES 50.00</strong>.
            </p>
            <div class="form-group" style="margin-bottom: 1rem;">
                <label>M-Pesa Number for STK Push</label>
                <input type="text" id="mpesa-phone" placeholder="0712345678">
            </div>
            <button type="button" class="btn-modal btn-confirm" style="width: 100%; grid-column: span 2;" onclick="processFinalPayment()">
                Pay KES 50 via M-Pesa
            </button>
        </div>
    </div>

    <!-- Modal 3: Final Success -->
    <div id="modal-success" class="modal">
        <div class="modal-content" style="position: relative;">
            <i class="fas fa-times" style="position: absolute; top: 1.5rem; right: 1.5rem; font-size: 1.2rem; color: #94a3b8; cursor: pointer; transition: color 0.3s;" onclick="closeModal('modal-success')"></i>
            <i class="fas fa-hand-holding-dollar congrats-icon" style="color: var(--primary);"></i>
            <div class="modal-header">
                <h3>Congratulations!</h3>
            </div>
            <p style="text-align: center; margin-bottom: 1.5rem; color: #475569; line-height: 1.5; font-size: 1.1rem;">
                Your loan of <strong id="success-loan-amount" style="color: var(--primary);"></strong> will be processed in 24hrs.
            </p>
            <p style="text-align: center; color: #10b981; font-size: 0.9rem;">
                <i class="fas fa-mobile-alt"></i> Please check your phone to enter your M-Pesa PIN for the processing fee.
            </p>
        </div>
    </div>

    <script>
        const products = <?= json_encode($products) ?>;
        const form = document.querySelector('form');
        const submitBtn = document.getElementById('btn-submit-trigger');

        submitBtn.addEventListener('click', () => {
            if(!form.checkValidity()) {
                form.reportValidity();
                return;
            }

            const formData = new FormData(form);
            const productId = formData.get('product_id');
            const product = products.find(p => p.id == productId);
            const amount = parseFloat(formData.get('amount'));
            const duration = parseInt(formData.get('duration'));
            
            const interestAmount = (amount * (product.interest_rate / 100) * duration);
            const totalPayable = amount + interestAmount;

            const summaryHtml = `
                <div class="summary-item"><span>Applicant Name</span> <span>${formData.get('full_name')}</span></div>
                <div class="summary-item"><span>ID Number</span> <span>${formData.get('id_number')}</span></div>
                <div class="summary-item"><span>Contact Phone</span> <span>${formData.get('phone')}</span></div>
                <div class="summary-item"><span>Loan Product</span> <span>${product.name}</span></div>
                <div class="summary-item"><span>Principal Amount</span> <span>KES ${amount.toLocaleString()}</span></div>
                <div class="summary-item"><span>Interest (${product.interest_rate}%)</span> <span>KES ${interestAmount.toLocaleString()}</span></div>
                <div class="summary-item" style="border-top: 2px solid #e2e8f0; border-bottom: none; margin-top: 0.5rem; padding-top: 1rem;">
                    <span style="color: var(--primary); font-size: 1.1rem;">Total Payable</span> 
                    <span style="color: var(--primary); font-size: 1.1rem;">KES ${totalPayable.toLocaleString()}</span>
                </div>
            `;

            document.getElementById('summary-details').innerHTML = summaryHtml;
            document.getElementById('mpesa-phone').value = formData.get('phone');
            document.getElementById('modal-summary').style.display = 'flex';
        });

        function closeModal(id) {
            document.getElementById(id).style.display = 'none';
        }

        function showPaymentModal() {
            closeModal('modal-summary');
            document.getElementById('modal-payment').style.display = 'flex';
        }

        async function processFinalPayment() {
            const mpesaPhone = document.getElementById('mpesa-phone').value;
            if(!mpesaPhone) {
                alert('Please enter your M-Pesa number');
                return;
            }

            const btn = document.querySelector('#modal-payment .btn-confirm');
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Initiating STK Push...';

            // Gather form data
            const formData = new FormData(form);
            formData.append('ajax', '1');
            formData.append('mpesa_phone', mpesaPhone);

            try {
                const response = await fetch('apply.php', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.status === 'success') {
                    // Update success modal with loan amount
                    const appliedAmount = parseFloat(formData.get('amount')).toLocaleString();
                    document.getElementById('success-loan-amount').innerText = 'KES ' + appliedAmount;
                    
                    // Hide payment modal, show success modal
                    closeModal('modal-payment');
                    document.getElementById('modal-success').style.display = 'flex';
                    
                    // Reset button for next time just in case
                    btn.innerHTML = 'Pay KES 50 via M-Pesa';
                    btn.disabled = false;
                } else {
                    alert('Error: ' + result.message);
                    btn.disabled = false;
                    btn.innerHTML = 'Pay KES 50 via M-Pesa';
                }
            } catch (error) {
                console.error(error);
                alert('An error occurred while initiating payment.');
                btn.disabled = false;
                btn.innerHTML = 'Pay KES 50 via M-Pesa';
            }
        }
    </script>
</body>
</html>
