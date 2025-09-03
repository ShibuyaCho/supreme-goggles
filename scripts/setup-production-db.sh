#!/bin/bash

# Cannabis POS - Production Database Setup Script
# This script securely configures the production database

set -e  # Exit on any error

echo "🔐 Cannabis POS Production Database Setup"
echo "========================================"

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Check if running as root
if [[ $EUID -eq 0 ]]; then
   echo -e "${RED}❌ This script should not be run as root for security reasons${NC}"
   exit 1
fi

# Function to generate secure password
generate_password() {
    openssl rand -base64 32 | tr -d "=+/" | cut -c1-25
}

# Function to prompt for input with default
prompt_with_default() {
    local prompt="$1"
    local default="$2"
    local varname="$3"
    
    echo -en "${BLUE}${prompt}${NC}"
    if [ -n "$default" ]; then
        echo -en " ${YELLOW}[${default}]${NC}"
    fi
    echo -n ": "
    
    read input
    if [ -z "$input" ]; then
        input="$default"
    fi
    
    eval "$varname='$input'"
}

# Function to prompt for password
prompt_password() {
    local prompt="$1"
    local varname="$2"
    
    echo -en "${BLUE}${prompt}${NC}: "
    read -s password
    echo
    eval "$varname='$password'"
}

echo "📋 Database Configuration"
echo "------------------------"

# Database configuration
prompt_with_default "Database Host" "127.0.0.1" DB_HOST
prompt_with_default "Database Port" "3306" DB_PORT
prompt_with_default "Database Name" "cannabis_pos_production" DB_DATABASE
prompt_with_default "Database Username" "cannabis_pos_user" DB_USERNAME

echo
echo "🔑 Database Password Options:"
echo "1) Generate secure password automatically"
echo "2) Enter password manually"
echo -n "Choose option [1]: "
read password_option

if [ "$password_option" = "2" ]; then
    prompt_password "Database Password" DB_PASSWORD
    prompt_password "Confirm Password" DB_PASSWORD_CONFIRM
    
    if [ "$DB_PASSWORD" != "$DB_PASSWORD_CONFIRM" ]; then
        echo -e "${RED}❌ Passwords do not match${NC}"
        exit 1
    fi
else
    DB_PASSWORD=$(generate_password)
    echo -e "${GREEN}✅ Generated secure password${NC}"
fi

echo
echo "🏢 Application Configuration"
echo "----------------------------"

prompt_with_default "Application Domain" "yourdomain.com" APP_DOMAIN
prompt_with_default "Admin Email" "admin@${APP_DOMAIN}" ADMIN_EMAIL
prompt_with_default "Manager Email" "manager@${APP_DOMAIN}" MANAGER_EMAIL
prompt_with_default "Admin Phone (optional)" "" ADMIN_PHONE
prompt_with_default "Manager Phone (optional)" "" MANAGER_PHONE

# Generate application key
APP_KEY="base64:$(openssl rand -base64 32)"

echo
echo "💾 Creating Environment Configuration"
echo "-----------------------------------"

# Backup existing .env if it exists
if [ -f .env ]; then
    cp .env .env.backup.$(date +%Y%m%d_%H%M%S)
    echo -e "${YELLOW}⚠️  Backed up existing .env file${NC}"
fi

# Create production .env file
cat > .env << EOF
# Cannabis POS Production Configuration
# Generated on: $(date)

APP_NAME="Cannabis POS System"
APP_ENV=production
APP_KEY=${APP_KEY}
APP_DEBUG=false
APP_URL=https://${APP_DOMAIN}
APP_DOMAIN=${APP_DOMAIN}

LOG_CHANNEL=stack
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=error

# Database Configuration
DB_CONNECTION=mysql
DB_HOST=${DB_HOST}
DB_PORT=${DB_PORT}
DB_DATABASE=${DB_DATABASE}
DB_USERNAME=${DB_USERNAME}
DB_PASSWORD=${DB_PASSWORD}

# Broadcasting & Cache
BROADCAST_DRIVER=log
CACHE_DRIVER=file
FILESYSTEM_DISK=local
QUEUE_CONNECTION=sync
SESSION_DRIVER=file
SESSION_LIFETIME=120

# Redis (optional - uncomment if using Redis)
# REDIS_HOST=127.0.0.1
# REDIS_PASSWORD=null
# REDIS_PORT=6379

# Mail Configuration (configure for your mail service)
MAIL_MAILER=smtp
MAIL_HOST=mailhog
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="noreply@${APP_DOMAIN}"
MAIL_FROM_NAME="\${APP_NAME}"

# METRC Configuration (already set)
METRC_BASE_URL=https://api-or.metrc.com
METRC_USER_KEY=${METRC_USER_KEY:-""}
METRC_VENDOR_KEY=${METRC_VENDOR_KEY:-""}
METRC_USERNAME=${METRC_USERNAME:-""}
METRC_PASSWORD=${METRC_PASSWORD:-""}
METRC_FACILITY=${METRC_FACILITY:-""}
METRC_TAG_PREFIX=1A4
METRC_ENABLED=true

# POS Configuration
POS_SALES_TAX=20.0
POS_EXCISE_TAX=10.0
POS_CANNABIS_TAX=17.0
POS_TAX_INCLUSIVE=false
POS_AUTO_PRINT_RECEIPT=true
POS_REQUIRE_CUSTOMER=true
POS_AGE_VERIFICATION=true
POS_LIMIT_ENFORCEMENT=true
POS_ACCEPT_CASH=true
POS_ACCEPT_DEBIT=true
POS_ACCEPT_CHECK=false
POS_ROUND_TO_NEAREST=false
POS_STORE_NAME="Cannabis POS"
POS_STORE_ADDRESS=""
POS_RECEIPT_FOOTER="Thank you for your business!\nKeep receipt for returns and warranty."

# User Configuration
ADMIN_EMAIL=${ADMIN_EMAIL}
MANAGER_EMAIL=${MANAGER_EMAIL}
ADMIN_PHONE=${ADMIN_PHONE}
MANAGER_PHONE=${MANAGER_PHONE}

# Security Headers
SECURITY_HEADERS=true
FORCE_HTTPS=true

# Sanctum Configuration
SANCTUM_STATEFUL_DOMAINS=${APP_DOMAIN}
EOF

# Set secure permissions on .env file
chmod 600 .env
echo -e "${GREEN}✅ Created secure .env file${NC}"

echo
echo "🗄️  Database Setup Commands"
echo "-------------------------"

# Create database setup SQL
cat > setup-database.sql << EOF
-- Cannabis POS Database Setup
-- Create database and user with minimal privileges

CREATE DATABASE IF NOT EXISTS \`${DB_DATABASE}\` 
    CHARACTER SET utf8mb4 
    COLLATE utf8mb4_unicode_ci;

-- Create user with secure password
CREATE USER IF NOT EXISTS '${DB_USERNAME}'@'${DB_HOST}' 
    IDENTIFIED BY '${DB_PASSWORD}';

-- Grant only necessary privileges
GRANT SELECT, INSERT, UPDATE, DELETE, CREATE, ALTER, INDEX, DROP 
    ON \`${DB_DATABASE}\`.* 
    TO '${DB_USERNAME}'@'${DB_HOST}';

-- Flush privileges
FLUSH PRIVILEGES;

-- Show created database and user
SHOW DATABASES LIKE '${DB_DATABASE}';
SELECT User, Host FROM mysql.user WHERE User = '${DB_USERNAME}';
EOF

echo -e "${BLUE}📄 Database SQL commands saved to: setup-database.sql${NC}"

echo
echo "🚀 Next Steps"
echo "------------"
echo "1. Run the database setup as MySQL root user:"
echo "   mysql -u root -p < setup-database.sql"
echo
echo "2. Run Laravel database migrations:"
echo "   php artisan migrate --force"
echo
echo "3. Create production users:"
echo "   php artisan db:seed --class=ProductionUserSeeder"
echo
echo "4. Clear and cache configuration:"
echo "   php artisan config:clear"
echo "   php artisan config:cache"
echo "   php artisan route:cache"
echo
echo "5. Set proper file permissions:"
echo "   chmod -R 755 storage bootstrap/cache"
echo "   chmod -R 777 storage/logs"
echo

echo -e "${GREEN}✅ Production database configuration complete!${NC}"
echo
echo -e "${YELLOW}⚠️  IMPORTANT SECURITY NOTES:${NC}"
echo "• Database password: ${DB_PASSWORD}"
echo "• Save this password securely - it's not stored anywhere else"
echo "• The .env file contains sensitive data - keep it secure"
echo "• Change default admin passwords after first login"
echo "• Review and update all configuration values as needed"
echo
echo -e "${RED}🔐 Remember to:${NC}"
echo "• Configure SSL/TLS certificates"
echo "• Set up firewall rules"
echo "• Configure backup procedures"
echo "• Set up monitoring and logging"

# Clean up temporary files
rm -f setup-database.sql

echo
echo -e "${GREEN}🎉 Setup complete! Your Cannabis POS system is ready for production.${NC}"
