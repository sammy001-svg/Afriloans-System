<?php 
require_once 'includes/db.php';
require_once 'includes/functions.php';

// Fetch products for the calculator/dropdown
$products = fetchAll("SELECT * FROM loan_products");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ZETA CREDIT | Instant & Affordable Loans for Your Growth</title>
    
    <!-- SEO Meta Tags -->
    <meta name="description" content="Access fast, flexible, and low-interest loans directly to your M-Pesa. ZETA CREDIT empowers entrepreneurs and individuals across Africa with transparent credit solutions.">
    <meta name="keywords" content="loans, affordable credit, M-Pesa loans, entrepreneur loans, Africa finance, ZETA CREDIT">
    <meta name="author" content="ZETA CREDIT">
    <meta name="robots" content="index, follow">

    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="http://localhost:8000/">
    <meta property="og:title" content="ZETA CREDIT | Instant & Affordable Loans">
    <meta property="og:description" content="Empowering your growth with fast, affordable credit. No hidden fees, instant approval.">
    <meta property="og:image" content="assets/images/seo-share.png">

    <!-- Twitter -->
    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:url" content="http://localhost:8000/">
    <meta property="twitter:title" content="ZETA CREDIT | Instant & Affordable Loans">
    <meta property="twitter:description" content="Empowering your growth with fast, affordable credit. No hidden fees, instant approval.">
    <meta property="twitter:image" content="assets/images/seo-share.png">

    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

    <!-- Navigation -->
    <nav>
        <a href="index.php" class="logo">
            <i class="fas fa-hand-holding-dollar"></i>
            ZETA <span>CREDIT</span>
        </a>
    </nav>

    <!-- Hero Section -->
    <section class="hero">
        <div class="hero-container">
            <div class="hero-left">
                <!-- Marketing Carousel -->
                <div class="carousel-container" style="margin-bottom: 2rem;">
                    <div class="carousel-track">
                        <div class="carousel-slide">
                            <img src="assets/images/banner1.webp" loading="lazy" alt="Mary's Mobile Solutions">
                        </div>
                        <div class="carousel-slide">
                            <img src="assets/images/banner2.webp" loading="lazy" alt="Sarah's Fine Textiles">
                        </div>
                        <div class="carousel-slide">
                            <img src="assets/images/banner3.webp" loading="lazy" alt="Samuel's Precision Agri-Tech">
                        </div>
                    </div>
                    <div class="carousel-dots">
                        <div class="dot active"></div>
                        <div class="dot"></div>
                        <div class="dot"></div>
                    </div>
                </div>

                <div class="hero-text">
                    <h1 style="font-size: 2.5rem;">Get an Instant Loan for Your <span>Every Need</span>.</h1>
                    <p>Access quick, flexible, and low-interest loans directly to your M-Pesa. No paperwork, no hidden fees.</p>
                    
                    <div style="display: flex; gap: 2rem; margin-top: 2rem;">
                        <div class="stat-item">
                            <h3 style="color: var(--primary);">10min</h3>
                            <p style="font-size: 0.8rem;">Approval</p>
                        </div>
                        <div class="stat-item">
                            <h3 style="color: var(--primary);">5k+</h3>
                            <p style="font-size: 0.8rem;">Happy Clients</p>
                        </div>
                        <div class="stat-item">
                            <h3 style="color: var(--primary);">3%</h3>
                            <p style="font-size: 0.8rem;">Low Interest</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Loan Calculator -->
            <div class="calculator-card">
                <h3>Loan Calculator</h3>
                
                <div class="form-group">
                    <label>Loan Product</label>
                    <select id="loan-product" style="width: 100%; padding: 0.8rem; border-radius: var(--radius); border: 1px solid #e2e8f0; outline: none;">
                        <?php foreach ($products as $product): ?>
                            <option value="<?= $product['id'] ?>" 
                                    data-rate="<?= $product['interest_rate'] ?>"
                                    data-min="<?= $product['min_amount'] ?>"
                                    data-max="<?= $product['max_amount'] ?>"
                                    data-duration="<?= $product['max_duration_months'] ?>">
                                <?= $product['name'] ?> (<?= $product['interest_rate'] ?>%)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Loan Amount: <span id="amount-val">KES 5,000</span></label>
                    <input type="range" id="loan-amount" min="1000" max="50000" step="500" value="5000">
                </div>

                <div class="form-group">
                    <label>Duration: <span id="duration-val">1 Month</span></label>
                    <input type="range" id="loan-duration" min="1" max="12" step="1" value="1">
                </div>

                <div class="results">
                    <div class="result-item">
                        <span>Principal Amount</span>
                        <span id="res-principal">KES 5,000.00</span>
                    </div>
                    <div class="result-item">
                        <span>Interest Amount</span>
                        <span id="res-interest">KES 250.00</span>
                    </div>
                    <div class="result-item">
                        <span>Monthly Installment</span>
                        <span id="res-monthly">KES 5,250.00</span>
                    </div>
                    <div class="result-item">
                        <span>Total Payable</span>
                        <span id="res-total" class="total-payable">KES 5,250.00</span>
                    </div>
                </div>

                <button class="btn-apply" onclick="window.location.href='apply.php'">Apply Now</button>
            </div>
        </div>
    </section>

    <!-- JS Logic -->
    <script src="assets/js/main.js"></script>
</body>
</html>
