<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class StrongPassword implements ValidationRule
{
    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $config = config('security.password_policy');
        
        $minLength = $config['min_length'] ?? 8;
        $requireUppercase = $config['require_uppercase'] ?? true;
        $requireLowercase = $config['require_lowercase'] ?? true;
        $requireNumbers = $config['require_numbers'] ?? true;
        $requireSpecialChars = $config['require_special_chars'] ?? true;

        $errors = [];

        // Check minimum length
        if (strlen($value) < $minLength) {
            $errors[] = "at least {$minLength} characters";
        }

        // Check for uppercase
        if ($requireUppercase && !preg_match('/[A-Z]/', $value)) {
            $errors[] = "at least one uppercase letter";
        }

        // Check for lowercase
        if ($requireLowercase && !preg_match('/[a-z]/', $value)) {
            $errors[] = "at least one lowercase letter";
        }

        // Check for numbers
        if ($requireNumbers && !preg_match('/[0-9]/', $value)) {
            $errors[] = "at least one number";
        }

        // Check for special characters
        if ($requireSpecialChars && !preg_match('/[!@#$%^&*(),.?":{}|<>]/', $value)) {
            $errors[] = "at least one special character (!@#$%^&*(),.?\":{}|<>)";
        }

        if (!empty($errors)) {
            $message = "The {$attribute} must contain " . implode(', ', $errors) . ".";
            $fail($message);
        }
    }
}
