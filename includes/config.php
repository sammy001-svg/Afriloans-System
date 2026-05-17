<?php
// Loan Management System Configuration

// Database Credentials
define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'loan_system');
define('DB_USER', 'root');
define('DB_PASS', '');

// System Info
define('SITE_NAME', 'ZETA CREDIT');
define('SITE_URL', 'http://localhost/loan-system'); // Update this on deployment

// M-Pesa Credentials (Live)
// NOTE: Replace these with your actual credentials on cPanel
define('MPESA_CONSUMER_KEY', 'YOUR_CONSUMER_KEY_HERE');
define('MPESA_CONSUMER_SECRET', 'YOUR_CONSUMER_SECRET_HERE');
define('MPESA_SHORTCODE', 'YOUR_SHORTCODE_HERE');
define('MPESA_PASSKEY', 'YOUR_PASSKEY_HERE');
define('MPESA_ENV', 'live'); // sandbox or live

// Session Security
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Set Timezone
date_default_timezone_set('Africa/Nairobi');
?>
