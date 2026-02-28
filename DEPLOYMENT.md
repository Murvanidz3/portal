# OneCar CRM - Hostinger Deployment Guide

## Prerequisites

- Hostinger shared hosting account
- PHP 8.2 or higher
- MySQL/MariaDB database
- Composer access (via SSH or cPanel)

## Deployment Steps

### 1. Prepare the Application

```bash
# Install dependencies (on local machine)
composer install --optimize-autoloader --no-dev

# Build frontend assets
npm install
npm run build
```

### 2. Upload Files

Upload all files to Hostinger, typically to `public_html` or a subdomain folder.

**Important:** The `public` folder contents should be in your web root, and Laravel files should be one level above.

### 3. Directory Structure for Shared Hosting

```
/home/username/
├── domains/
│   └── yourdomain.com/
│       └── public_html/          # Laravel's public folder contents
│           ├── index.php         # Modified to point to Laravel
│           ├── .htaccess
│           ├── build/
│           └── images/
├── laravel/                      # Laravel application files
│   ├── app/
│   ├── bootstrap/
│   ├── config/
│   ├── database/
│   ├── resources/
│   ├── routes/
│   ├── storage/
│   └── vendor/
```

### 4. Configure public/index.php

Modify `public/index.php` to point to the correct Laravel location:

```php
<?php

use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Adjust these paths based on your hosting structure
require __DIR__.'/../laravel/vendor/autoload.php';

$app = require_once __DIR__.'/../laravel/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$response = $kernel->handle(
    $request = Request::capture()
)->send();

$kernel->terminate($request, $response);
```

### 5. Configure Environment

Create `.env` file in the Laravel folder with your production settings:

```env
APP_NAME="OneCar CRM"
APP_ENV=production
APP_KEY=base64:YOUR_APP_KEY_HERE
APP_DEBUG=false
APP_URL=https://yourdomain.com

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=your_database_name
DB_USERNAME=your_database_user
DB_PASSWORD=your_database_password

SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=database

SMS_API_KEY=your_sms_api_key
SMS_SENDER=OneCar
```

### 6. Set Permissions

```bash
chmod -R 755 storage
chmod -R 755 bootstrap/cache
```

### 7. Run Migrations

Via SSH or Hostinger's terminal:

```bash
cd /path/to/laravel
php artisan key:generate
php artisan migrate --force
php artisan db:seed --force
php artisan storage:link
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 8. Create Storage Link

If `php artisan storage:link` doesn't work on shared hosting, create a symbolic link manually:

```bash
ln -s /home/username/laravel/storage/app/public /home/username/domains/yourdomain.com/public_html/storage
```

Or create a PHP file to make the link:

```php
<?php
// Run once and then delete this file
symlink('/home/username/laravel/storage/app/public', '/home/username/domains/yourdomain.com/public_html/storage');
echo 'Storage link created!';
```

## Database Migration

### Option 1: Fresh Install

Run migrations to create tables:

```bash
php artisan migrate --force
php artisan db:seed --force
```

### Option 2: Import Existing Data

1. Export the SQL file from your old database
2. Import it via phpMyAdmin or command line
3. The existing data will work with the new Laravel application

## Troubleshooting

### 500 Internal Server Error

1. Check `.htaccess` is uploaded
2. Verify PHP version (8.2+)
3. Check file permissions
4. Check error logs in Hostinger panel

### Database Connection Error

1. Verify database credentials in `.env`
2. Make sure database exists
3. Check if user has proper permissions

### Storage Issues

1. Ensure storage link is created
2. Check permissions on storage folder
3. Verify `FILESYSTEM_DISK` in `.env`

### CSS/JS Not Loading

1. Run `npm run build` before uploading
2. Check if `build` folder is uploaded
3. Verify `APP_URL` in `.env`

## Performance Optimization

After deployment, run:

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize
```

## Security Checklist

- [ ] Set `APP_DEBUG=false`
- [ ] Set `APP_ENV=production`
- [ ] Use strong database password
- [ ] Remove any test/debug files
- [ ] Enable HTTPS
- [ ] Set proper file permissions
- [ ] Update `.htaccess` security headers

## Support

For issues, check:
1. Laravel logs: `storage/logs/laravel.log`
2. Hostinger error logs
3. PHP error logs
