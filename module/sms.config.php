<?php
/**
 * iPROG SMS Configuration
 * API Documentation: https://sms.iprogtech.com/api/v1/documentation
 */

define('IPROGSMS_API_TOKEN', 'd6b973ffb82a09db6eaebd6d3c823dee411df8bd');
define('IPROGSMS_API_URL', 'https://sms.iprogtech.com/api/v1/sms_messages');

/**
 * Send SMS via iPROG SMS API
 * 
 * @param string $phone_number Phone number (09XXXXXXXXX format)
 * @param string $message SMS message content
 * @return array Response with success status and details
 */
function sendSMS($phone_number, $message) {
    // Clean and format phone number
    $phone_number = formatPhoneNumber($phone_number);
    
    if (!$phone_number) {
        return [
            'success' => false,
            'error' => 'Invalid phone number format',
            'message_id' => null
        ];
    }
    
    // Prepare data to send
    $postData = [
        'api_token' => IPROGSMS_API_TOKEN,
        'phone_number' => $phone_number,
        'message' => $message
    ];
    
    // Initialize cURL (this sends the HTTP request)
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, IPROGSMS_API_URL);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    
    // Execute the request
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curl_error = curl_error($ch);
    curl_close($ch);
    
    // Parse the JSON response
    $responseData = json_decode($response, true);
    
    // Log for debugging
    error_log("SMS API Response: " . print_r($responseData, true));
    
    if ($curl_error) {
        error_log("cURL Error: " . $curl_error);
        return [
            'success' => false,
            'error' => $curl_error,
            'message_id' => null
        ];
    }
    
    // Check if successful (status 200 means success)
    $success = ($http_code == 200 && isset($responseData['status']) && $responseData['status'] == 200);
    
    return [
        'success' => $success,
        'message' => $responseData['message'] ?? 'Unknown error',
        'message_id' => $responseData['message_id'] ?? null,
        'http_code' => $http_code,
        'phone_number' => $phone_number
    ];
}

/**
 * Format phone number to iPROG accepted format
 * 
 * @param string $phone Raw phone number
 * @return string|false Formatted phone number or false if invalid
 */
function formatPhoneNumber($phone) {
    // Remove all non-numeric characters
    $phone = preg_replace('/[^0-9]/', '', $phone);
    
    // Check if empty
    if (empty($phone)) {
        return false;
    }
    
    // Handle different formats
    if (strlen($phone) == 10 && substr($phone, 0, 1) == '9') {
        // 9XXXXXXXXX -> 09XXXXXXXXX
        return '0' . $phone;
    } elseif (strlen($phone) == 11 && substr($phone, 0, 2) == '09') {
        // 09XXXXXXXXX (already correct)
        return $phone;
    } elseif (strlen($phone) == 12 && substr($phone, 0, 2) == '63') {
        // 639XXXXXXXXX -> 09XXXXXXXXX
        return '0' . substr($phone, 2);
    } else {
        // Invalid format
        return false;
    }
}