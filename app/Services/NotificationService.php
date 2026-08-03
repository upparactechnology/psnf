<?php

declare(strict_types=1);

namespace App\Services;

class NotificationService
{
    /**
     * Send WhatsApp notification using web.upparac.com
     *
     * @param string $phone The recipient's phone number
     * @param string $message The message body
     * @return bool True if successful
     */
    public static function sendWhatsApp(string $phone, string $message): bool
    {
        $db = \Core\Application::$app->db;
        $tenantId = \Core\Database::getTenantId() ?? 1;

        // Check if WhatsApp is enabled
        $enabled = $db->selectOne("SELECT value FROM system_settings WHERE `key` = 'whatsapp_enabled'")['value'] ?? '1';
        if ($enabled === '0') {
            return false;
        }

        // Get API Key
        $apiKey = $db->selectOne("SELECT value FROM system_settings WHERE `key` = 'whatsapp_api_key'")['value'] ?? '';
        
        if (empty($apiKey)) {
            error_log("WhatsApp API Key is missing in system settings.");
            return false;
        }

        $endpoint = 'https://web.upparac.com/api/send-message.php?action=send';
        
        $payload = [
            'phone' => $phone,
            'message' => $message
        ];
        
        $ch = curl_init($endpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Authorization: Bearer {$apiKey}"
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        
        $responseRaw = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        $status = ($httpCode >= 200 && $httpCode < 300) ? 'sent' : 'failed';

        // Log the message
        $db->insert('whatsapp_message_logs', [
            'tenant_id' => $tenantId,
            'phone'     => $phone,
            'message'   => $message,
            'status'    => $status,
            'response'  => $responseRaw
        ]);

        if ($status === 'sent') {
            error_log("WhatsApp Notification sent to {$phone}");
            return true;
        } else {
            error_log("WhatsApp Notification failed to {$phone}: HTTP {$httpCode} - {$responseRaw}");
            return false;
        }
    }
    
    public static function notifyInvoiceCreated(array $student, array $invoice): void
    {
        if (empty($student['phone'])) return;
        
        $message = "Dear {$student['first_name']}, your fee invoice '{$invoice['title']}' for Rs. {$invoice['amount']} has been generated. Due date is {$invoice['due_date']}. Please pay to avoid late fees. Regards, PSNF.";
        self::sendWhatsApp($student['phone'], $message);
    }
    
    public static function notifyPaymentReceived(array $student, array $invoice, float $amount): void
    {
        if (empty($student['phone'])) return;
        
        $message = "Dear {$student['first_name']}, we have received your payment of Rs. {$amount} for '{$invoice['title']}'. Thank you! Regards, PSNF.";
        self::sendWhatsApp($student['phone'], $message);
    }
}
