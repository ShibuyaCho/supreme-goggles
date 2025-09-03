<?php

/**
 * Production Security Configuration for Cannabis POS
 * 
 * This file contains security settings that should be applied in production.
 * Copy relevant sections to your Laravel configuration files.
 */

return [
    /*
    |--------------------------------------------------------------------------
    | Security Headers Configuration
    |--------------------------------------------------------------------------
    */
    'headers' => [
        // Force HTTPS
        'force_https' => env('FORCE_HTTPS', true),
        
        // HTTP Strict Transport Security (HSTS)
        'hsts' => [
            'enabled' => true,
            'max_age' => env('HSTS_MAX_AGE', 31536000), // 1 year
            'include_subdomains' => true,
            'preload' => true,
        ],
        
        // Content Security Policy (CSP)
        'csp' => [
            'enabled' => env('CSP_ENABLED', true),
            'default_src' => "'self'",
            'script_src' => "'self' 'unsafe-inline' 'unsafe-eval'",
            'style_src' => "'self' 'unsafe-inline'",
            'img_src' => "'self' data: https:",
            'font_src' => "'self'",
            'connect_src' => "'self'",
            'frame_ancestors' => "'none'",
            'form_action' => "'self'",
            'base_uri' => "'self'",
        ],
        
        // X-Frame-Options
        'x_frame_options' => 'DENY',
        
        // X-Content-Type-Options
        'x_content_type_options' => 'nosniff',
        
        // X-XSS-Protection
        'x_xss_protection' => '1; mode=block',
        
        // Referrer Policy
        'referrer_policy' => 'strict-origin-when-cross-origin',
        
        // Permissions Policy (Feature Policy)
        'permissions_policy' => [
            'geolocation' => [],
            'microphone' => [],
            'camera' => [],
            'payment' => ['self'],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Rate Limiting Configuration
    |--------------------------------------------------------------------------
    */
    'rate_limiting' => [
        'enabled' => env('RATE_LIMIT_ENABLED', true),
        
        // API rate limiting
        'api' => [
            'requests' => env('API_RATE_LIMIT', 60),
            'per_minutes' => 1,
        ],
        
        // Login rate limiting
        'login' => [
            'attempts' => env('LOGIN_RATE_LIMIT', 5),
            'decay_minutes' => env('LOGIN_RATE_LIMIT_DECAY', 5),
        ],
        
        // Password reset rate limiting
        'password_reset' => [
            'attempts' => 3,
            'decay_minutes' => 60,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | IP Whitelist Configuration
    |--------------------------------------------------------------------------
    */
    'ip_whitelist' => [
        'enabled' => env('IP_WHITELIST_ENABLED', false),
        'admin_routes' => [
            // Add your admin IP addresses here
            '127.0.0.1',
            '::1',
            // 'your.office.ip.address',
        ],
        'api_routes' => [
            // Add trusted API client IPs here
            '127.0.0.1',
            '::1',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Session Security Configuration
    |--------------------------------------------------------------------------
    */
    'session' => [
        'secure' => env('SESSION_SECURE_COOKIES', true),
        'http_only' => true,
        'same_site' => 'strict',
        'lifetime' => env('SESSION_LIFETIME', 120), // minutes
        'encrypt' => env('SESSION_ENCRYPT', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | File Upload Security
    |--------------------------------------------------------------------------
    */
    'file_uploads' => [
        'max_size' => env('MAX_FILE_SIZE', 10240), // KB
        'allowed_types' => explode(',', env('ALLOWED_FILE_TYPES', 'jpg,jpeg,png,pdf,txt')),
        'scan_for_viruses' => env('VIRUS_SCAN_ENABLED', false),
        'quarantine_suspicious' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Database Security
    |--------------------------------------------------------------------------
    */
    'database' => [
        'ssl_mode' => env('DB_SSL_MODE', 'preferred'),
        'charset' => 'utf8mb4',
        'collation' => 'utf8mb4_unicode_ci',
        'strict' => true,
        'engine' => null,
    ],

    /*
    |--------------------------------------------------------------------------
    | Audit Logging
    |--------------------------------------------------------------------------
    */
    'audit' => [
        'enabled' => env('AUDIT_LOGGING_ENABLED', true),
        'events' => [
            'login_attempts',
            'password_changes',
            'admin_actions',
            'data_exports',
            'system_changes',
            'metrc_transactions',
            'sales_modifications',
        ],
        'retention_days' => env('AUDIT_RETENTION_DAYS', 90),
    ],

    /*
    |--------------------------------------------------------------------------
    | Cannabis Compliance Security
    |--------------------------------------------------------------------------
    */
    'cannabis_compliance' => [
        'mode' => env('CANNABIS_COMPLIANCE_MODE', 'strict'),
        'age_verification' => env('AGE_VERIFICATION_REQUIRED', true),
        'id_scanning' => env('ID_SCAN_REQUIRED', false),
        'metrc_logging' => true,
        'transaction_immutability' => true,
        'audit_trail_required' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Backup Security
    |--------------------------------------------------------------------------
    */
    'backup' => [
        'enabled' => env('BACKUP_ENABLED', true),
        'encrypt_backups' => true,
        'retention_days' => env('BACKUP_RETENTION_DAYS', 30),
        'verify_integrity' => true,
        'offsite_storage' => env('OFFSITE_BACKUP_ENABLED', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | Third-party Integration Security
    |--------------------------------------------------------------------------
    */
    'integrations' => [
        'metrc' => [
            'encrypt_credentials' => true,
            'verify_ssl' => true,
            'timeout' => 30,
            'retry_attempts' => 3,
        ],
        'payment_gateways' => [
            'encrypt_credentials' => true,
            'pci_compliance' => true,
            'tokenize_cards' => true,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Monitoring and Alerting
    |--------------------------------------------------------------------------
    */
    'monitoring' => [
        'enabled' => env('MONITORING_ENABLED', true),
        'failed_login_threshold' => 10,
        'suspicious_activity_threshold' => 5,
        'alert_emails' => [
            env('ADMIN_EMAIL', 'admin@yourdomain.com'),
        ],
        'sentry_dsn' => env('SENTRY_DSN'),
    ],
];
