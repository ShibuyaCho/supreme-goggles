<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Security Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains security-related configuration options for the
    | Cannabis POS application. These settings control various security
    | features and restrictions.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Force Security Headers
    |--------------------------------------------------------------------------
    |
    | When this option is set to true, security headers will be applied
    | even in non-production environments. This is useful for testing
    | security configurations during development.
    |
    */

    'force_headers' => env('SECURITY_FORCE_HEADERS', false),

    /*
    |--------------------------------------------------------------------------
    | Force HTTPS
    |--------------------------------------------------------------------------
    |
    | When this option is set to true, the application will force HTTPS
    | connections. This should be enabled in production environments.
    |
    */

    'force_https' => env('FORCE_HTTPS', false),

    /*
    |--------------------------------------------------------------------------
    | Admin IP Whitelist
    |--------------------------------------------------------------------------
    |
    | This array contains IP addresses or ranges that are allowed to access
    | administrative functions. You can use exact IPs, CIDR notation, or
    | wildcard patterns.
    |
    | Examples:
    | - '192.168.1.100' (exact IP)
    | - '192.168.1.0/24' (CIDR notation)
    | - '192.168.1.*' (wildcard)
    |
    */

    'admin_ip_whitelist' => [
        // Add your admin IP addresses here
        // '192.168.1.100',
        // '10.0.0.0/8',
        // '172.16.0.0/12',
        // '192.168.0.0/16',
    ],

    /*
    |--------------------------------------------------------------------------
    | Authentication Rate Limiting
    |--------------------------------------------------------------------------
    |
    | These options control rate limiting for authentication attempts.
    | Failed authentication attempts will be tracked and limited to
    | prevent brute force attacks.
    |
    */

    'auth_rate_limit' => [
        'max_attempts' => env('AUTH_MAX_ATTEMPTS', 5),
        'decay_minutes' => env('AUTH_DECAY_MINUTES', 15),
        'lockout_duration' => env('AUTH_LOCKOUT_DURATION', 900), // 15 minutes
    ],

    /*
    |--------------------------------------------------------------------------
    | API Rate Limiting
    |--------------------------------------------------------------------------
    |
    | These options control general API rate limiting to prevent abuse
    | and ensure fair usage of the system resources.
    |
    */

    'api_rate_limit' => [
        'per_minute' => env('API_RATE_LIMIT_PER_MINUTE', 60),
        'per_hour' => env('API_RATE_LIMIT_PER_HOUR', 1000),
        'per_day' => env('API_RATE_LIMIT_PER_DAY', 10000),
    ],

    /*
    |--------------------------------------------------------------------------
    | Session Security
    |--------------------------------------------------------------------------
    |
    | These options control session security settings including timeout,
    | regeneration, and secure cookie settings.
    |
    */

    'session' => [
        'timeout' => env('SESSION_TIMEOUT', 120), // minutes
        'regenerate_on_login' => true,
        'secure_cookies' => env('SESSION_SECURE_COOKIES', true),
        'http_only_cookies' => true,
        'same_site_cookies' => 'strict',
    ],

    /*
    |--------------------------------------------------------------------------
    | Password Security
    |--------------------------------------------------------------------------
    |
    | These options control password requirements and security policies.
    |
    */

    'password' => [
        'min_length' => env('PASSWORD_MIN_LENGTH', 8),
        'require_uppercase' => env('PASSWORD_REQUIRE_UPPERCASE', true),
        'require_lowercase' => env('PASSWORD_REQUIRE_LOWERCASE', true),
        'require_numbers' => env('PASSWORD_REQUIRE_NUMBERS', true),
        'require_symbols' => env('PASSWORD_REQUIRE_SYMBOLS', true),
        'max_age_days' => env('PASSWORD_MAX_AGE_DAYS', 90),
        'history_count' => env('PASSWORD_HISTORY_COUNT', 5),
    ],

    /*
    |--------------------------------------------------------------------------
    | File Upload Security
    |--------------------------------------------------------------------------
    |
    | These options control file upload security including allowed types,
    | size limits, and virus scanning.
    |
    */

    'uploads' => [
        'max_size' => env('UPLOAD_MAX_SIZE', 5120), // KB
        'allowed_extensions' => [
            'jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx', 'xls', 'xlsx'
        ],
        'scan_for_viruses' => env('UPLOAD_VIRUS_SCAN', false),
        'quarantine_suspicious' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Audit Logging
    |--------------------------------------------------------------------------
    |
    | These options control security audit logging for tracking user
    | actions and system events.
    |
    */

    'audit' => [
        'enabled' => env('AUDIT_LOGGING_ENABLED', true),
        'log_successful_logins' => true,
        'log_failed_logins' => true,
        'log_admin_actions' => true,
        'log_data_changes' => true,
        'retention_days' => env('AUDIT_RETENTION_DAYS', 365),
    ],

    /*
    |--------------------------------------------------------------------------
    | Intrusion Detection
    |--------------------------------------------------------------------------
    |
    | These options control intrusion detection and suspicious activity
    | monitoring.
    |
    */

    'intrusion_detection' => [
        'enabled' => env('INTRUSION_DETECTION_ENABLED', true),
        'suspicious_ip_threshold' => 50, // Failed attempts before flagging
        'block_suspicious_ips' => env('BLOCK_SUSPICIOUS_IPS', false),
        'alert_admin_on_intrusion' => true,
        'whitelist_known_ips' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Content Security Policy
    |--------------------------------------------------------------------------
    |
    | These options allow you to customize the Content Security Policy
    | header to match your application's requirements.
    |
    */

    'csp' => [
        'enabled' => env('CSP_ENABLED', true),
        'report_only' => env('CSP_REPORT_ONLY', false),
        'report_uri' => env('CSP_REPORT_URI', null),
        'upgrade_insecure_requests' => env('CSP_UPGRADE_INSECURE', true),
        
        'directives' => [
            'default-src' => ["'self'"],
            'script-src' => ["'self'", "'unsafe-inline'"],
            'style-src' => ["'self'", "'unsafe-inline'"],
            'img-src' => ["'self'", 'data:', 'https:'],
            'font-src' => ["'self'"],
            'connect-src' => ["'self'"],
            'media-src' => ["'self'"],
            'object-src' => ["'none'"],
            'child-src' => ["'none'"],
            'frame-src' => ["'none'"],
            'worker-src' => ["'none'"],
            'frame-ancestors' => ["'none'"],
            'form-action' => ["'self'"],
            'base-uri' => ["'self'"],
            'manifest-src' => ["'self'"],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Two-Factor Authentication
    |--------------------------------------------------------------------------
    |
    | These options control two-factor authentication settings.
    |
    */

    'two_factor' => [
        'enabled' => env('TWO_FACTOR_ENABLED', false),
        'required_for_admin' => env('TWO_FACTOR_REQUIRED_ADMIN', false),
        'backup_codes_count' => 8,
        'window' => 30, // seconds
        'remember_device_days' => 30,
    ],

    /*
    |--------------------------------------------------------------------------
    | Backup Security
    |--------------------------------------------------------------------------
    |
    | These options control backup security including encryption and
    | storage security.
    |
    */

    'backup' => [
        'encrypt_backups' => env('BACKUP_ENCRYPT', true),
        'encryption_key' => env('BACKUP_ENCRYPTION_KEY'),
        'secure_storage' => env('BACKUP_SECURE_STORAGE', true),
        'verify_integrity' => true,
        'remote_storage_only' => env('BACKUP_REMOTE_ONLY', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | Database Security
    |--------------------------------------------------------------------------
    |
    | These options control database security settings.
    |
    */

    'database' => [
        'encrypt_sensitive_data' => env('DB_ENCRYPT_SENSITIVE', true),
        'log_queries' => env('DB_LOG_QUERIES', false),
        'prevent_sql_injection' => true,
        'sanitize_inputs' => true,
        'use_prepared_statements' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | METRC Security
    |--------------------------------------------------------------------------
    |
    | These options control METRC API security settings.
    |
    */

    'metrc' => [
        'verify_ssl' => env('METRC_VERIFY_SSL', true),
        'timeout' => env('METRC_TIMEOUT', 30),
        'retry_attempts' => env('METRC_RETRY_ATTEMPTS', 3),
        'log_api_calls' => env('METRC_LOG_API_CALLS', true),
        'encrypt_credentials' => true,
    ],

];
