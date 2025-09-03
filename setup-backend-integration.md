# Cannabis POS Backend Integration Setup

This guide provides complete instructions for setting up the Laravel backend with authentication, API security, and METRC integration.

## 🚀 Quick Setup

### 1. Environment Configuration

Create your `.env` file with the following required settings:

```bash
# Database Configuration
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=cannabis_pos
DB_USERNAME=your_username
DB_PASSWORD=your_password

# Laravel Application
APP_NAME="Cannabis POS"
APP_ENV=production
APP_KEY=base64:your_app_key_here
APP_DEBUG=false
APP_URL=http://your-domain.com

# Laravel Sanctum
SANCTUM_STATEFUL_DOMAINS=your-domain.com,localhost

# METRC API Configuration
METRC_BASE_URL=https://api-or.metrc.com
METRC_USER_KEY=your_metrc_user_key
METRC_VENDOR_KEY=your_metrc_vendor_key  
METRC_FACILITY=your_facility_license_number
METRC_TAG_PREFIX=1A4
METRC_ENABLED=true

# POS Settings
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
POS_STORE_NAME="Your Cannabis Store"
POS_STORE_ADDRESS="123 Main St, City, State 12345"
POS_RECEIPT_FOOTER="Thank you for your business!\nKeep receipt for returns and warranty."
```

### 2. Run Database Migrations and Seeders

```bash
# Run migrations to create all tables
php artisan migrate

# Seed the database with initial users and permissions
php artisan db:seed --class=UserSeeder

# Optional: Run all seeders if you have more
php artisan db:seed
```

### 3. Generate Application Key

```bash
php artisan key:generate
```

### 4. Publish Sanctum Configuration

```bash
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
```

## 🔐 Authentication System

### User Roles and Permissions

The system includes 5 user roles with specific permissions:

1. **Admin** (`admin`)
   - Full system access (`*` permission)
   - Can manage all users, settings, and data

2. **Manager** (`manager`)
   - POS operations, inventory management, reports
   - Cannot manage system settings or create admin users

3. **Cashier** (`cashier`)
   - Basic POS operations
   - Can process sales and manage customers

4. **Budtender** (`budtender`)
   - POS operations plus customer service features
   - Can enroll customers in loyalty programs

5. **Inventory** (`inventory`)
   - Product and inventory management
   - METRC integration access

### Default Login Credentials

After running the seeder, use these credentials:

```
Admin: admin@cannabis-pos.local / admin123! (PIN: 1234)
Manager: manager@cannabis-pos.local / manager123! (PIN: 2345)  
Cashier: cashier@cannabis-pos.local / cashier123! (PIN: 3456)
Budtender: budtender@cannabis-pos.local / budtender123! (PIN: 4567)
Inventory: inventory@cannabis-pos.local / inventory123! (PIN: 5678)
```

### Permission System

Permissions are namespace-based for granular control:

- `pos:*` - Full POS access
- `products:read`, `products:write`, `products:delete` - Product management
- `metrc:*` - METRC integration access  
- `sales:create`, `sales:manage` - Sales operations
- `reports:read` - Report access
- `analytics:read` - Analytics access

## 🔌 API Endpoints

### Authentication

```
POST /api/auth/login - Email/password login
POST /api/auth/pin-login - Employee PIN login  
POST /api/auth/logout - Logout current session
GET /api/auth/me - Get current user info
POST /api/auth/refresh - Refresh auth token
POST /api/auth/change-password - Change password
```

### Products

```
GET /api/products - List products (requires products:read)
POST /api/products - Create product (requires products:write)
PUT /api/products/{id} - Update product (requires products:write)
DELETE /api/products/{id} - Delete product (requires products:delete)
```

### METRC Integration

```
GET /api/metrc/test-connection - Test METRC connection
GET /api/metrc/packages - Get all packages
GET /api/metrc/packages/{tag} - Get package details
POST /api/metrc/packages/create - Create new package
POST /api/metrc/sales/receipts - Create sales receipt
```

### Sales & POS

```
GET /api/sales - List sales
POST /api/sales - Create new sale
POST /api/pos/process-payment - Process payment
GET /api/pos/config - Get POS configuration
```

## 🌿 METRC Integration Verification

### Testing METRC Connection

1. **Via API Endpoint:**
```bash
curl -X GET "http://your-domain.com/api/metrc/test-connection" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Accept: application/json"
```

2. **Via Frontend:**
   - Login as a user with `metrc:access` permission
   - The system will automatically test METRC connection on login
   - Check browser console for connection status

3. **Manual Verification:**
```bash
# Test with curl directly to METRC (replace with your credentials)
curl -X GET "https://api-or.metrc.com/facilities/v1" \
  -u "YOUR_USER_KEY:YOUR_VENDOR_KEY" \
  -H "Content-Type: application/json"
```

### METRC Configuration Checklist

- ✅ Valid METRC User Key  
- ✅ Valid METRC Vendor Key
- ✅ Correct Facility License Number
- ✅ Proper API URL (sandbox vs production)
- ✅ Network connectivity to METRC servers
- ✅ User permissions for METRC access

## 🔒 Security Features

### API Security

1. **Token-based Authentication** - Laravel Sanctum
2. **Role-based Access Control** - Custom middleware
3. **Permission-based Authorization** - Granular permissions
4. **Rate Limiting** - Built-in Laravel throttling
5. **Request Validation** - Comprehensive input validation
6. **Error Handling** - Secure error responses

### Security Best Practices

- All API routes require authentication except login endpoints
- Sensitive data is never logged or exposed in responses
- METRC credentials are stored securely and never returned in API responses
- User passwords are hashed using Laravel's secure hashing
- Employee PINs are hashed and validated securely

## 🚀 Frontend Integration

The frontend automatically connects to the backend API:

1. **Authentication:** Users must login before accessing any features
2. **Real-time Data:** All product, customer, and sales data comes from the database
3. **METRC Integration:** Live METRC data when properly configured
4. **Offline Fallback:** Falls back to demo data if API is unavailable

### Frontend Features

- **Automatic Login Detection** - Redirects to login if not authenticated
- **Permission-based UI** - Shows/hides features based on user permissions  
- **Real-time METRC** - Live package tracking and compliance
- **Secure Storage** - Tokens stored securely in localStorage
- **Auto-refresh** - Automatic token refresh for long sessions

## 🛠️ Development Tools

### API Documentation
Visit `/api/docs` for complete API documentation with all endpoints, parameters, and examples.

### Testing Users
The seeder creates test users for each role - perfect for development and testing different permission levels.

### Debug Mode
Set `APP_DEBUG=true` in `.env` for development to see detailed error messages.

## 📋 Troubleshooting

### Common Issues

1. **METRC Connection Failed**
   - Verify credentials in `.env`
   - Check network connectivity
   - Ensure correct API URL (sandbox vs production)

2. **Authentication Errors**  
   - Clear browser localStorage
   - Check if Laravel Sanctum is properly configured
   - Verify database connection

3. **Permission Denied**
   - Check user role and permissions
   - Verify middleware is working
   - Check API route protection

4. **Database Errors**
   - Run migrations: `php artisan migrate`
   - Seed data: `php artisan db:seed`
   - Check database connection in `.env`

### Logs

Check Laravel logs for detailed error information:
```bash
tail -f storage/logs/laravel.log
```

## 🎯 Next Steps

1. **Setup SSL Certificate** - Required for production METRC integration
2. **Configure Backup System** - Protect your business data
3. **Setup Monitoring** - Monitor API performance and uptime
4. **Customize Settings** - Adjust tax rates, store info, and preferences
5. **Train Staff** - Ensure team knows how to use the system

## 📞 Support

For technical support or questions about the Cannabis POS system:

- Check the API documentation at `/api/docs`
- Review Laravel logs for error details  
- Verify METRC credentials and connectivity
- Test with different user roles and permissions

---

**🌿 Your Cannabis POS system is now fully integrated with Laravel backend, secure authentication, and real METRC API integration!**
