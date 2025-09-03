# Production Setup Instructions

## Environment Variables Configured

The following environment variables have been set for production:

### METRC API Configuration
- `METRC_USER_KEY`: No4ErUcQCjtzeoyS8podd7UgoZ8tjrGb7fAqkwl1XKrgDx64
- `METRC_VENDOR_KEY`: -wEp3jabxlsIYVhclDdbdRmMvazx557w7P5TEeYN2OeAOyWV
- `METRC_USERNAME`: d2g214
- `METRC_PASSWORD`: Hms2019!
- `METRC_FACILITY`: 050-1010496F611
- `METRC_BASE_URL`: https://api-or.metrc.com
- `METRC_ENABLED`: true
- `METRC_TAG_PREFIX`: 1A4

### Application Configuration
- `APP_ENV`: production
- `APP_DEBUG`: false

### Database Configuration  
- `DB_CONNECTION`: mysql
- `DB_HOST`: 127.0.0.1
- `DB_PORT`: 3306
- `DB_DATABASE`: cannabis_pos_production

**⚠️ IMPORTANT:** You need to set your database username and password:
```bash
# Set your database credentials
DB_USERNAME=your_database_username
DB_PASSWORD=your_database_password
```

## Required Setup Steps

### 1. Database Setup
Run the following commands in your Laravel project directory:

```bash
# Run migrations to create database tables
php artisan migrate --force

# Run seeders to populate initial data
php artisan db:seed --force

# Generate application key
php artisan key:generate --force
```

### 2. Test Your Setup
After running migrations, you can test your setup by visiting these endpoints:

- **Environment Test**: `GET /test/env-test`
- **Database Test**: `GET /test/database-test`  
- **METRC Integration Test**: `GET /test/metrc-test`

### 4. User Accounts Created
The UserSeeder will create the following accounts:

| Role | Email | Password | PIN |
|------|-------|----------|-----|
| Admin | admin@cannabis-pos.local | admin123! | 1234 |
| Manager | manager@cannabis-pos.local | manager123! | 2345 |
| Cashier | cashier@cannabis-pos.local | cashier123! | 3456 |
| Budtender | budtender@cannabis-pos.local | budtender123! | 4567 |
| Inventory | inventory@cannabis-pos.local | inventory123! | 5678 |

## Verification Steps

1. **Test Database Connection**: Visit `/test/database-test` to verify database is connected
2. **Test Environment**: Visit `/test/env-test` to verify all environment variables are set
3. **Test METRC Integration**: Visit `/test/metrc-test` to verify METRC API is working

## Security Notes

- Change all default passwords immediately after setup
- Set a strong `APP_KEY` using `php artisan key:generate`
- Ensure your `.env` file is not accessible via web
- Use HTTPS in production
- Set appropriate file permissions on your Laravel installation

## Troubleshooting

### Database Issues
- Ensure MySQL/MariaDB is running
- Verify database credentials are correct
- Check that the database `cannabis_pos_production` exists

### METRC Issues
- Verify your facility license is active in METRC
- Check that your API keys have proper permissions
- Ensure your server can reach `https://api-or.metrc.com`

### Authentication Issues
- Clear config cache: `php artisan config:clear`
- Clear route cache: `php artisan route:clear`
- Restart your web server after environment changes
