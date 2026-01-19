<?php

namespace App\Services;

use Illuminate\Support\Facades\Crypt;
use Illuminate\Contracts\Encryption\DecryptException;

class DataEncryptionService
{
    /**
     * Sensitive fields that should be encrypted
     */
    protected static array $encryptedFields = [
        'ssn',
        'national_id',
        'passport_number',
        'bank_account',
        'medical_records',
        'emergency_contact_phone',
    ];

    /**
     * Encrypt sensitive data
     */
    public static function encrypt(string $value): string
    {
        return Crypt::encryptString($value);
    }

    /**
     * Decrypt sensitive data
     */
    public static function decrypt(string $value): string
    {
        try {
            return Crypt::decryptString($value);
        } catch (DecryptException $e) {
            \Log::error('Decryption failed: ' . $e->getMessage());
            return '[ENCRYPTED]';
        }
    }

    /**
     * Encrypt an array of data
     */
    public static function encryptArray(array $data): array
    {
        foreach (self::$encryptedFields as $field) {
            if (isset($data[$field]) && !empty($data[$field])) {
                $data[$field] = self::encrypt($data[$field]);
            }
        }
        return $data;
    }

    /**
     * Decrypt an array of data
     */
    public static function decryptArray(array $data): array
    {
        foreach (self::$encryptedFields as $field) {
            if (isset($data[$field]) && !empty($data[$field])) {
                $data[$field] = self::decrypt($data[$field]);
            }
        }
        return $data;
    }

    /**
     * Mask sensitive data for display
     */
    public static function mask(string $value, int $visibleChars = 4): string
    {
        if (strlen($value) <= $visibleChars) {
            return str_repeat('*', strlen($value));
        }

        $visible = substr($value, -$visibleChars);
        $masked = str_repeat('*', strlen($value) - $visibleChars);
        
        return $masked . $visible;
    }

    /**
     * Hash sensitive data for comparison (one-way)
     */
    public static function hash(string $value): string
    {
        return hash('sha256', $value);
    }
}
