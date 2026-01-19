<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Security Settings
    |--------------------------------------------------------------------------
    |
    | Configure security features for the application
    |
    */

    'audit_logging' => [
        'enabled' => env('AUDIT_LOGGING_ENABLED', true),
        'retention_days' => env('AUDIT_LOG_RETENTION_DAYS', 90),
        'log_all_requests' => env('AUDIT_LOG_ALL_REQUESTS', false),
    ],

    'authentication' => [
        'token_lifetime' => env('TOKEN_LIFETIME_MINUTES', 60),
        'refresh_token_lifetime' => env('REFRESH_TOKEN_LIFETIME_DAYS', 7),
        'max_login_attempts' => env('MAX_LOGIN_ATTEMPTS', 5),
        'lockout_duration' => env('LOCKOUT_DURATION_MINUTES', 15),
        'require_email_verification' => env('REQUIRE_EMAIL_VERIFICATION', true),
        'require_2fa_for_admin' => env('REQUIRE_2FA_FOR_ADMIN', false),
    ],

    'password_policy' => [
        'min_length' => env('PASSWORD_MIN_LENGTH', 8),
        'require_uppercase' => env('PASSWORD_REQUIRE_UPPERCASE', true),
        'require_lowercase' => env('PASSWORD_REQUIRE_LOWERCASE', true),
        'require_numbers' => env('PASSWORD_REQUIRE_NUMBERS', true),
        'require_special_chars' => env('PASSWORD_REQUIRE_SPECIAL', true),
        'password_history' => env('PASSWORD_HISTORY_COUNT', 5), // Prevent reusing last N passwords
        'expiry_days' => env('PASSWORD_EXPIRY_DAYS', 90), // Force password change every N days
    ],

    'session' => [
        'lifetime' => env('SESSION_LIFETIME', 60), // minutes
        'idle_timeout' => env('SESSION_IDLE_TIMEOUT', 30), // minutes
        'concurrent_sessions' => env('ALLOW_CONCURRENT_SESSIONS', false),
    ],

    'rate_limiting' => [
        'api' => [
            'per_minute' => env('API_RATE_LIMIT_PER_MINUTE', 60),
            'per_hour' => env('API_RATE_LIMIT_PER_HOUR', 1000),
        ],
        'login' => [
            'per_minute' => env('LOGIN_RATE_LIMIT_PER_MINUTE', 5),
            'per_hour' => env('LOGIN_RATE_LIMIT_PER_HOUR', 20),
        ],
    ],

    'ip_whitelist' => [
        'enabled' => env('IP_WHITELIST_ENABLED', false),
        'admin_only' => env('IP_WHITELIST_ADMIN_ONLY', true),
        'allowed_ips' => array_filter(explode(',', env('IP_WHITELIST', ''))),
    ],

    'encryption' => [
        'sensitive_fields' => [
            'ssn',
            'national_id',
            'passport_number',
            'bank_account',
            'medical_records',
            'emergency_contact_phone',
        ],
    ],

    'file_uploads' => [
        'max_size' => env('MAX_UPLOAD_SIZE_MB', 5) * 1024, // Convert to KB
        'allowed_extensions' => [
            'pdf', 'doc', 'docx', 'xls', 'xlsx',
            'jpg', 'jpeg', 'png', 'gif',
            'txt', 'csv',
        ],
        'scan_for_viruses' => env('SCAN_UPLOADS_FOR_VIRUSES', false),
        'storage_path' => env('UPLOAD_STORAGE_PATH', 'uploads'),
    ],

    'content_security_policy' => [
        'enabled' => env('CSP_ENABLED', true),
        'report_uri' => env('CSP_REPORT_URI', '/api/csp-report'),
        'report_only' => env('CSP_REPORT_ONLY', false),
    ],

    'cors' => [
        'allowed_origins' => array_filter(explode(',', env('CORS_ALLOWED_ORIGINS', 'http://localhost:5173'))),
        'allowed_methods' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'],
        'allowed_headers' => ['Content-Type', 'Authorization', 'X-Requested-With', 'Accept'],
        'exposed_headers' => ['Authorization'],
        'max_age' => 86400,
        'supports_credentials' => true,
    ],

    'security_headers' => [
        'hsts' => [
            'enabled' => env('HSTS_ENABLED', env('APP_ENV') === 'production'),
            'max_age' => env('HSTS_MAX_AGE', 31536000),
            'include_subdomains' => env('HSTS_INCLUDE_SUBDOMAINS', true),
            'preload' => env('HSTS_PRELOAD', true),
        ],
        'x_frame_options' => env('X_FRAME_OPTIONS', 'DENY'),
        'x_content_type_options' => env('X_CONTENT_TYPE_OPTIONS', 'nosniff'),
        'x_xss_protection' => env('X_XSS_PROTECTION', '1; mode=block'),
        'referrer_policy' => env('REFERRER_POLICY', 'strict-origin-when-cross-origin'),
    ],

    'notifications' => [
        'notify_on_failed_login' => env('NOTIFY_ON_FAILED_LOGIN', true),
        'notify_on_account_lockout' => env('NOTIFY_ON_ACCOUNT_LOCKOUT', true),
        'notify_on_password_change' => env('NOTIFY_ON_PASSWORD_CHANGE', true),
        'notify_on_suspicious_activity' => env('NOTIFY_ON_SUSPICIOUS_ACTIVITY', true),
        'security_email' => env('SECURITY_NOTIFICATION_EMAIL', 'security@academic-nexus.com'),
    ],

    'data_retention' => [
        'audit_logs_days' => env('AUDIT_LOGS_RETENTION_DAYS', 90),
        'deleted_users_days' => env('DELETED_USERS_RETENTION_DAYS', 30),
        'old_grades_years' => env('OLD_GRADES_RETENTION_YEARS', 7),
    ],

];
