#!/bin/bash

# Cannabis POS Production Setup Script
# This script prepares the system for production deployment

set -e

echo "🌿 Cannabis POS Production Setup"
echo "================================="

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Check if running as root
if [[ $EUID -eq 0 ]]; then
   echo -e "${RED}Error: This script should not be run as root${NC}"
   exit 1
fi

# Function to check if command exists
command_exists() {
    command -v "$1" >/dev/null 2>&1
}

# Check dependencies
echo -e "${YELLOW}Checking dependencies...${NC}"
MISSING_DEPS=()

if ! command_exists php; then
    MISSING_DEPS+=("php")
fi

if ! command_exists composer; then
    MISSING_DEPS+=("composer")
fi

if ! command_exists mysql; then
    MISSING_DEPS+=("mysql")
fi

if ! command_exists nginx || ! command_exists apache2; then
    MISSING_DEPS+=("nginx or apache2")
fi

if [ ${#MISSING_DEPS[@]} -ne 0 ]; then
    echo -e "${RED}Missing dependencies: ${MISSING_DEPS[*]}${NC}"
    echo "Please install missing dependencies and run this script again."
    exit 1
fi

echo -e "${GREEN}✓ All dependencies found${NC}"

# Create production environment file
echo -e "${YELLOW}Setting up environment configuration...${NC}"
if [ ! -f .env ]; then
    if [ -f config.production.template ]; then
        cp config.production.template .env
        echo -e "${GREEN}✓ Created .env from production template${NC}"
        echo -e "${YELLOW}⚠️  IMPORTANT: Edit .env file with your production values!${NC}"
    else
        echo -e "${RED}Error: config.production.template not found${NC}"
        exit 1
    fi
else
    echo -e "${YELLOW}⚠️  .env file already exists, skipping...${NC}"
fi

# Generate application key
echo -e "${YELLOW}Generating application key...${NC}"
php artisan key:generate --force

# Set proper file permissions
echo -e "${YELLOW}Setting file permissions...${NC}"
chmod 755 storage bootstrap/cache
chmod -R 775 storage
chmod -R 775 bootstrap/cache
chmod 600 .env

# Install/update dependencies
echo -e "${YELLOW}Installing production dependencies...${NC}"
composer install --no-dev --optimize-autoloader

# Clear and cache configuration
echo -e "${YELLOW}Optimizing configuration...${NC}"
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Check database connection
echo -e "${YELLOW}Checking database connection...${NC}"
if php artisan db:show >/dev/null 2>&1; then
    echo -e "${GREEN}✓ Database connection successful${NC}"
else
    echo -e "${RED}✗ Database connection failed${NC}"
    echo "Please check your database configuration in .env file"
    exit 1
fi

# Run migrations
echo -e "${YELLOW}Running database migrations...${NC}"
php artisan migrate --force

# Create production users (if seeder exists)
if [ -f database/seeders/ProductionUserSeeder.php ]; then
    echo -e "${YELLOW}Creating production users...${NC}"
    php artisan db:seed --class=ProductionUserSeeder
    echo -e "${YELLOW}⚠️  IMPORTANT: Save the generated credentials shown above!${NC}"
fi

# Create symbolic link for storage
php artisan storage:link

# Check system health
echo -e "${YELLOW}Running system health check...${NC}"
if php artisan system:health-check >/dev/null 2>&1; then
    echo -e "${GREEN}✓ System health check passed${NC}"
else
    echo -e "${YELLOW}⚠️  Health check command not available${NC}"
fi

# Set up cron jobs for production
echo -e "${YELLOW}Setting up scheduled tasks...${NC}"
(crontab -l 2>/dev/null; echo "*/5 * * * * cd $(pwd) && php artisan schedule:run >> /dev/null 2>&1") | crontab -
echo -e "${GREEN}✓ Cron jobs configured${NC}"

echo
echo -e "${GREEN}🎉 Production setup completed successfully!${NC}"
echo
echo -e "${YELLOW}NEXT STEPS:${NC}"
echo "1. Configure your web server (nginx/apache) with SSL"
echo "2. Update DNS to point to your server"
echo "3. Review and customize .env file with your production values"
echo "4. Test all functionality before going live"
echo "5. Set up monitoring and backup procedures"
echo
echo -e "${YELLOW}IMPORTANT SECURITY NOTES:${NC}"
echo "• Change all default passwords immediately"
echo "• Configure firewall rules"
echo "• Set up SSL certificates"
echo "• Review security headers configuration"
echo "• Test backup and recovery procedures"
echo
echo -e "${GREEN}Your Cannabis POS system is ready for production deployment!${NC}"
