<?php
/**
 * FairFare System - SMS Notification Service
 * 
 * Sends SMS notifications using Africa's Talking Gateway
 * 
 * Configuration:
 * Set environment variables: SMS_USERNAME, SMS_API_KEY
 * Or update the config below for development
 * 
 * @package FairFare
 * @version 1.0.0
 */

// SMS Configuration - Use environment variables in production
$sms_config = [
    'username' => getenv('SMS_USERNAME') !== false ? getenv('SMS_USERNAME') : 'sandbox',
    'apiKey' => getenv('SMS_API_KEY') !== false ? getenv('SMS_API_KEY') : 'YOUR_API_KEY',
    'enabled' => getenv('SMS_ENABLED') !== false ? (getenv('SMS_ENABLED') === 'true') : false
];

/**
 * Send SMS notification
 * 
 * @param string $phone_number Phone number to send to (E.164 format)
 * @param string $message Message to send
 * @return bool True if successful, false otherwise
 */
function send_sms($phone_number, $message) {
    global $sms_config;
    
    // SMS not enabled
    if (!$sms_config['enabled'] || $sms_config['apiKey'] === 'YOUR_API_KEY') {
        error_log("SMS service not configured. Message: " . $message . " To: " . $phone_number);
        return false;
    }
    
    // Validate phone number format
    if (empty($phone_number) || !preg_match('/^\+?[1-9]\d{1,14}$/', $phone_number)) {
        error_log("Invalid phone number format: " . $phone_number);
        return false;
    }
    
    // Validate message
    if (empty($message) || strlen($message) > 160) {
        error_log("Invalid message: too long or empty");
        return false;
    }
    
    try {
        // Check if AfricasTalkingGateway class exists
        if (!file_exists(dirname(__FILE__) . '/AfricasTalkingGateway.php')) {
            error_log("AfricasTalkingGateway.php not found");
            return false;
        }
        
        require_once "AfricasTalkingGateway.php";
        
        $gateway = new AfricasTalkingGateway($sms_config['username'], $sms_config['apiKey']);
        
        $results = $gateway->sendMessage($phone_number, $message);
        
        // Log successful sends
        error_log("SMS sent successfully to: " . $phone_number);
        
        return true;
        
    } catch (Exception $e) {
        error_log("SMS sending error: " . $e->getMessage());
        return false;
    }
}

/**
 * Send incident notification SMS
 * 
 * @param string $phone Phone number
 * @param string $route Route name
 * @param string $incident_id Incident ID
 * @return bool Success status
 */
function notify_incident($phone, $route, $incident_id) {
    $message = "FairFare: Incident #" . $incident_id . " reported on " . $route . " route. Status will be updated soon.";
    return send_sms($phone, $message);
}

/**
 * Send fare update notification SMS
 * 
 * @param string $phone Phone number
 * @param string $route Route name
 * @param string $new_fare New fare amount
 * @return bool Success status
 */
function notify_fare_update($phone, $route, $new_fare) {
    $message = "FairFare: Fare updated on " . $route . " route. New fare: Ksh " . $new_fare;
    return send_sms($phone, $message);
}

?>