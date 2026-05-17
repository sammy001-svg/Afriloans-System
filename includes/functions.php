<?php
/**
 * Utility functions for Loan Management System
 */

/**
 * Clean user input
 */
function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

/**
 * Format currency
 */
function formatCurrency($amount) {
    return "KES " . number_format($amount, 2);
}

/**
 * Check if admin is logged in
 */
function isAdminLoggedIn() {
    return isset($_SESSION['user_id']) && $_SESSION['role'] === 'admin';
}

/**
 * Redirect with a message
 */
function redirect($path, $message = '', $type = 'success') {
    if ($message) {
        $_SESSION['msg'] = $message;
        $_SESSION['msg_type'] = $type;
    }
    header("Location: " . SITE_URL . $path);
    exit();
}

/**
 * Generate a random transaction reference if none exists
 */
function generateRef($prefix = 'LN') {
    return $prefix . strtoupper(uniqid());
}

/**
 * Calculate loan repayment details
 */
function calculateLoan($amount, $rate, $months) {
    $interest = ($amount * ($rate / 100) * $months);
    $total = $amount + $interest;
    $monthly = $total / $months;
    
    return [
        'principal' => $amount,
        'interest' => $interest,
        'total' => $total,
        'monthly' => $monthly
    ];
}
?>
