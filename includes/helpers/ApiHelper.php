<?php
declare(strict_types=1);

/**
 * ApiHelper - Static class for API responses
 */
class ApiHelper {
    /**
     * Send JSON response
     */
    public static function sendResponse(bool $success, string $message, array $extra = []) {
        header('Content-Type: application/json');
        echo json_encode(array_merge([
            'success' => $success,
            'message' => $message
        ], $extra));
        exit;
    }

    /**
     * Require POST request
     */
    public static function requirePost() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            self::sendResponse(false, 'Método não permitido.');
        }
    }
}

// Keep global functions for backward compatibility (optional but safer for now)
if (!function_exists('sendResponse')) {
    function sendResponse(bool $success, string $message, array $extra = []) {
        ApiHelper::sendResponse($success, $message, $extra);
    }
}
if (!function_exists('requirePost')) {
    function requirePost() {
        ApiHelper::requirePost();
    }
}
