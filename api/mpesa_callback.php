<?php
require_once '../includes/db.php';
require_once '../includes/functions.php';

// Get the callback data
$callbackJSONData = file_get_contents('php://input');
$callbackData = json_decode($callbackJSONData);

if (!$callbackData) {
    die("Invalid data");
}

$resultCode = $callbackData->Body->stkCallback->ResultCode;
$resultDesc = $callbackData->Body->stkCallback->ResultDesc;
$merchantRequestID = $callbackData->Body->stkCallback->MerchantRequestID;
$checkoutRequestID = $callbackData->Body->stkCallback->CheckoutRequestID;

if ($resultCode == 0) {
    // Payment was successful
    $callbackMetadata = $callbackData->Body->stkCallback->CallbackMetadata->Item;
    $amount = 0;
    $mpesaReceiptNumber = "";
    $phoneNumber = "";

    foreach ($callbackMetadata as $item) {
        if ($item->Name == "Amount") $amount = $item->Value;
        if ($item->Name == "MpesaReceiptNumber") $mpesaReceiptNumber = $item->Value;
        if ($item->Name == "PhoneNumber") $phoneNumber = $item->Value;
    }

    // Update database
    try {
        $loan = fetch("SELECT id FROM loans WHERE checkout_request_id = ?", [$checkoutRequestID]);
        if ($loan) {
            $loan_id = $loan['id'];
            
            $pdo->beginTransaction();
            // Update loan processing fee status
            $stmt = $pdo->prepare("UPDATE loans SET processing_fee_paid = 1 WHERE id = ?");
            $stmt->execute([$loan_id]);
            
            // Insert into payments table
            $stmt = $pdo->prepare("INSERT INTO payments (loan_id, amount, transaction_ref, payment_method) VALUES (?, ?, ?, 'mpesa')");
            $stmt->execute([$loan_id, $amount, $mpesaReceiptNumber]);
            
            $pdo->commit();
            file_put_contents('mpesa_log.txt', "Success: Loan $loan_id updated. Receipt $mpesaReceiptNumber\n", FILE_APPEND);
        } else {
            file_put_contents('mpesa_log.txt', "Error: Loan not found for CheckoutRequestID $checkoutRequestID\n", FILE_APPEND);
        }
    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        file_put_contents('mpesa_log.txt', "Database Error: " . $e->getMessage() . "\n", FILE_APPEND);
    }
} else {
    // Payment failed
    file_put_contents('mpesa_log.txt', "Failed ($checkoutRequestID): $resultDesc\n", FILE_APPEND);
}

header("Content-Type: application/json");
echo json_encode(["ResultCode" => 0, "ResultDesc" => "Success"]);
?>
