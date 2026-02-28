# OneCar CRM

Modern Laravel-based CRM system for vehicle import management.

## Features

- **Vehicle Management**: Track vehicles from purchase to delivery with 7 status stages
- **User Management**: Admin, Dealer, and Client roles with permissions
- **Financial Tracking**: Vehicle costs, shipping, payments, and debt management
- **SMS Notifications**: Automated SMS via smsoffice.ge API
- **Calculator**: Cost estimation tool with customs and taxes calculation
- **Modern UI**: Dark theme with Tailwind CSS and Alpine.js

## Requirements

- PHP 8.2+
- MySQL 5.7+ or MariaDB 10.3+
- Composer
- Node.js 18+ (for building assets)

## Installation

### 1. Clone and Install Dependencies

```bash
git clone <repository-url>
cd portalv2

composer install
npm install
```

### 2. Configure Environment

```bash
cp .env.example .env
php artisan key:generate
```

Edit `.env` with your database credentials and other settings.

### 3. Run Migrations

```bash
php artisan migrate
php artisan db:seed
```

### 4. Create Storage Link

```bash
php artisan storage:link
```

### 5. Build Assets

```bash
npm run build
```

### 6. Start Development Server

```bash
php artisan serve
```

Visit `http://localhost:8000` and login with:
- **Username**: admin
- **Password**: admin123

## Development

```bash
# Start development server with hot reload
npm run dev

# Or use the composer script
composer dev
```

## Production Deployment

See [DEPLOYMENT.md](DEPLOYMENT.md) for detailed deployment instructions for Hostinger shared hosting.

## Project Structure

```
portalv2/
├── app/
│   ├── Http/Controllers/    # Controllers
│   ├── Models/              # Eloquent models
│   ├── Services/            # Business logic services
│   └── Http/Middleware/     # Custom middleware
├── database/
│   ├── migrations/          # Database migrations
│   └── seeders/             # Database seeders
├── resources/
│   ├── views/               # Blade templates
│   └── css/                 # Tailwind CSS
├── routes/
│   └── web.php              # Web routes
└── public/                  # Public assets
```

## Key Technologies

- **Backend**: Laravel 12, PHP 8.2
- **Frontend**: Tailwind CSS 4.0, Alpine.js 3.14
- **Database**: MySQL/MariaDB/SQLite
- **Build Tool**: Vite 7

## License

Proprietary - All rights reserved.
