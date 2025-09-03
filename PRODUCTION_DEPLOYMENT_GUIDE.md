# Cannabis POS Production Deployment Guide

## 🚀 Complete Production Setup

This guide covers the final steps to deploy your Cannabis POS system securely to production.

---

## ✅ **Prerequisites Completed**

- [x] JavaScript dependencies self-hosted
- [x] Test endpoints secured with admin-only access
- [x] Demo credentials removed from frontend
- [x] Production database configuration scripts created
- [x] Monitoring and backup systems implemented
- [x] Security hardening measures in place
- [x] Web server security configurations ready

---

## 📋 **Deployment Checklist**

### **1. Pre-Deployment Setup**

- [ ] Set up production server (Ubuntu/CentOS recommended)
- [ ] Install required software:
  ```bash
  # PHP 8.1+, MySQL 8.0+, Nginx/Apache, Redis (optional)
  sudo apt update
  sudo apt install php8.1-fpm mysql-server nginx redis-server
  sudo apt install php8.1-mysql php8.1-zip php8.1-curl php8.1-xml php8.1-mbstring
  ```
- [ ] Configure firewall (UFW/iptables)
- [ ] Obtain SSL certificates (Let's Encrypt recommended)

### **2. Database Setup**

```bash
# Run the database setup script
./scripts/setup-production-db.sh

# Or manually set database credentials:
mysql -u root -p
> CREATE DATABASE cannabis_pos_production;
> CREATE USER 'cannabis_user'@'localhost' IDENTIFIED BY 'SECURE_PASSWORD';
> GRANT ALL ON cannabis_pos_production.* TO 'cannabis_user'@'localhost';
> FLUSH PRIVILEGES;
```

### **3. Application Deployment**

```bash
# 1. Clone/upload your code
git clone your-repository.git /var/www/cannabis-pos
cd /var/www/cannabis-pos

# 2. Install dependencies
composer install --no-dev --optimize-autoloader
npm install
npm run build

# 3. Set up environment
cp .env.example .env
# Edit .env with production settings

# 4. Generate application key
php artisan key:generate

# 5. Run migrations and seeders
php artisan migrate --force
php artisan db:seed --class=ProductionUserSeeder

# 6. Optimize for production
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan storage:link

# 7. Set permissions
sudo chown -R www-data:www-data /var/www/cannabis-pos
sudo chmod -R 755 /var/www/cannabis-pos
sudo chmod -R 777 /var/www/cannabis-pos/storage
sudo chmod -R 777 /var/www/cannabis-pos/bootstrap/cache
sudo chmod 600 /var/www/cannabis-pos/.env
```

### **4. Web Server Configuration**

#### **For Nginx:**
```bash
# Copy the nginx configuration
sudo cp nginx-security.conf /etc/nginx/sites-available/cannabis-pos
sudo ln -s /etc/nginx/sites-available/cannabis-pos /etc/nginx/sites-enabled/
sudo nginx -t && sudo systemctl reload nginx
```

#### **For Apache:**
```bash
# The .htaccess file is already in public/ directory
# Ensure mod_rewrite and mod_headers are enabled
sudo a2enmod rewrite headers ssl
sudo systemctl restart apache2
```

### **5. SSL Certificate Setup**

```bash
# Using Certbot (Let's Encrypt)
sudo apt install certbot python3-certbot-nginx
sudo certbot --nginx -d yourdomain.com
```

### **6. Security Configuration**

- [ ] Update `/config/security.php` with your IP whitelist
- [ ] Configure fail2ban for additional protection
- [ ] Set up intrusion detection (optional)

### **7. Monitoring Setup**

```bash
# Set up automated health checks
(crontab -l; echo "*/5 * * * * cd /var/www/cannabis-pos && php artisan system:health-check --log") | crontab -

# Set up automated backups
(crontab -l; echo "0 2 * * * cd /var/www/cannabis-pos && php artisan system:backup") | crontab -
```

---

## 🔧 **Configuration Files**

### **Environment Variables (.env)**

```bash
# Application
APP_NAME="Cannabis POS System"
APP_ENV=production
APP_KEY=base64:YOUR_GENERATED_KEY
APP_DEBUG=false
APP_URL=https://yourdomain.com

# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=cannabis_pos_production
DB_USERNAME=cannabis_user
DB_PASSWORD=your_secure_password

# METRC (your credentials)
METRC_BASE_URL=https://api-or.metrc.com
METRC_USER_KEY=No4ErUcQCjtzeoyS8podd7UgoZ8tjrGb7fAqkwl1XKrgDx64
METRC_VENDOR_KEY=-wEp3jabxlsIYVhclDdbdRmMvazx557w7P5TEeYN2OeAOyWV
METRC_USERNAME=d2g214
METRC_PASSWORD=Hms2019!
METRC_FACILITY=050-1010496F611
METRC_ENABLED=true

# Security
SECURITY_FORCE_HEADERS=true
FORCE_HTTPS=true
SESSION_SECURE_COOKIES=true

# User Configuration
ADMIN_EMAIL=admin@yourdomain.com
MANAGER_EMAIL=manager@yourdomain.com
```

---

## 🔐 **Security Checklist**

- [ ] All demo credentials removed
- [ ] Strong passwords generated for admin users
- [ ] SSL certificate installed and HTTPS enforced
- [ ] Security headers configured
- [ ] IP whitelist configured for admin endpoints
- [ ] Rate limiting enabled
- [ ] File permissions properly set
- [ ] Firewall configured
- [ ] Database credentials secured
- [ ] Environment file protected (chmod 600)

---

## 📊 **Testing Your Deployment**

### **1. System Health Check**
```bash
php artisan system:health-check --detailed
```

### **2. Production Validation**
```bash
php scripts/validate-production.php
```

### **3. METRC Integration Test**
```bash
# Visit: https://yourdomain.com/test/metrc-test
# (Requires admin login)
```

### **4. Security Headers Test**
```bash
curl -I https://yourdomain.com
# Check for security headers in response
```

---

## 🔄 **Maintenance**

### **Daily Tasks (Automated)**
- Health checks every 5 minutes
- Log rotation
- Database backups at 2 AM

### **Weekly Tasks**
- Review security logs
- Update system packages
- Check backup integrity

### **Monthly Tasks**
- Change default passwords
- Review user permissions
- Update dependencies
- Security audit

---

## 📞 **Support & Monitoring**

### **Log Files to Monitor**
- `/var/log/nginx/cannabis-pos-error.log`
- `/var/log/nginx/security.log` 
- `/var/www/cannabis-pos/storage/logs/laravel.log`

### **Health Check Endpoints**
- `GET /health` - Public health status
- `GET /test/health-check` - Detailed health (admin only)
- `GET /test/metrc-test` - METRC integration (admin only)

### **Backup Status**
```bash
php artisan system:backup --status
```

---

## 🚨 **Emergency Procedures**

### **If System is Compromised**
1. Immediately change all passwords
2. Review access logs
3. Check for unauthorized database changes
4. Restore from latest backup if necessary
5. Update security configurations

### **If METRC Integration Fails**
1. Check API credentials
2. Verify facility license status
3. Review METRC service status
4. Check network connectivity
5. Review API rate limits

---

## 🎉 **Go-Live Checklist**

- [ ] All production configurations applied
- [ ] SSL certificate active
- [ ] DNS pointing to production server
- [ ] Monitoring systems active
- [ ] Backups configured and tested
- [ ] Team trained on admin procedures
- [ ] Emergency contacts configured
- [ ] Documentation updated

---

**🔐 Your Cannabis POS system is now production-ready and secure!**

### **Default Admin Credentials** (CHANGE IMMEDIATELY)
- Will be generated during production user seeding
- Check the output of `php artisan db:seed --class=ProductionUserSeeder`
- Change passwords on first login

### **Important Security Notes**
- Never commit `.env` files to version control
- Regularly update dependencies
- Monitor security logs daily
- Keep SSL certificates up to date
- Review and rotate API keys quarterly

For ongoing support and updates, refer to the system documentation and monitoring dashboards.
