# 🛡️ God Mode - Super Admin System

## შეჯამება

God Mode არის სრულად იზოლირებული Super Admin სისტემა, რომელიც საშუალებას გაძლევთ:

- **უფლებების კონტროლი** - ფუნქციების ჩართვა/გამორთვა გლობალურად და როლების მიხედვით
- **სტილების მართვა** - ფერების, ლოგოების და ბრენდინგის დინამიური ცვლილება
- **Audit Logging** - ყველა მოქმედების ლოგირება

---

## 🚀 დაწყება

### 1. Login URL
```
http://localhost:8000/god/login
```

### 2. Default Credentials
- **Username:** `superadmin`
- **Email:** `superadmin@onecar.ge`
- **Password:** `SuperAdmin@2026!`

> ⚠️ **გაფრთხილება:** პროდაქშენზე აუცილებლად შეცვალეთ პაროლი!

---

## 📁 ფაილების სტრუქტურა

```
app/
├── Http/
│   ├── Controllers/
│   │   └── GodMode/
│   │       ├── AuthController.php
│   │       ├── DashboardController.php
│   │       ├── PermissionController.php
│   │       └── StyleController.php
│   └── Middleware/
│       ├── GodModeAuth.php
│       └── CheckGodModePermission.php
├── Models/
│   ├── SuperAdmin.php
│   ├── GodModePermission.php
│   ├── GodModeStyle.php
│   └── GodModeAuditLog.php
├── Providers/
│   └── GodModeServiceProvider.php
└── Services/
    └── GodModeService.php

resources/views/god-mode/
├── layout.blade.php
├── login.blade.php
├── dashboard.blade.php
├── permissions.blade.php
├── styles.blade.php
└── audit-logs.blade.php

database/
├── migrations/
│   └── 2026_02_03_000001_create_god_mode_tables.php
└── seeders/
    └── GodModeSeeder.php
```

---

## 🔐 ავთენტიფიკაცია

### Guard Configuration
God Mode იყენებს ცალკე `god` guard-ს:

```php
// config/auth.php
'guards' => [
    'god' => [
        'driver' => 'session',
        'provider' => 'super_admins',
    ],
],

'providers' => [
    'super_admins' => [
        'driver' => 'eloquent',
        'model' => App\Models\SuperAdmin::class,
    ],
],
```

### Security Features
- **Rate Limiting:** 5 მცდელობა / 5 წუთში
- **Session Isolation:** სრულად ცალკე session
- **Audit Logging:** ყველა login/logout ლოგირდება
- **CSRF Protection:** ყველა POST request-ზე

---

## 🎛️ უფლებების სისტემა

### Database Schema
```sql
CREATE TABLE god_mode_permissions (
    id BIGINT PRIMARY KEY,
    feature_key VARCHAR(100) UNIQUE,  -- მაგ: 'cars.delete'
    feature_name VARCHAR(150),         -- მაგ: 'მანქანის წაშლა'
    feature_group VARCHAR(50),         -- მაგ: 'cars'
    is_enabled_global BOOLEAN,         -- გლობალური toggle
    is_enabled_admin BOOLEAN,          -- Admin როლისთვის
    is_enabled_dealer BOOLEAN,         -- Dealer როლისთვის
    is_enabled_client BOOLEAN,         -- Client როლისთვის
);
```

### Blade Directive-ები

```blade
{{-- შეამოწმე უფლება მიმდინარე მომხმარებლისთვის --}}
@godcan('cars.delete')
    <button>მანქანის წაშლა</button>
@endgodcan

{{-- შეამოწმე გლობალურად ჩართულია თუ არა --}}
@godfeature('invoices.access')
    <a href="/invoices">ინვოისები</a>
@endgodfeature
```

### Middleware გამოყენება

```php
// routes/web.php
Route::get('cars', [CarController::class, 'index'])
    ->middleware('god.permission:cars.access');

Route::delete('cars/{car}', [CarController::class, 'destroy'])
    ->middleware('god.permission:cars.delete');
```

### PHP-ში შემოწმება

```php
use App\Models\GodModePermission;

// შეამოწმე კონკრეტული როლისთვის
if (GodModePermission::isEnabled('cars.delete', 'dealer')) {
    // allowed
}

// შეამოწმე გლობალურად
if (GodModePermission::isEnabled('cars.delete')) {
    // globally enabled
}

// Service-ით
$godModeService = app(\App\Services\GodModeService::class);
if ($godModeService->can('cars.delete')) {
    // current user can delete
}
```

---

## 🎨 სტილების სისტემა

### CSS Variables
სტილები ავტომატურად ინექცირდება `:root`-ში:

```css
:root {
    --color-primary: #3b82f6;
    --color-secondary: #64748b;
    --color-success: #22c55e;
    --color-warning: #f59e0b;
    --color-error: #ef4444;
    --color-header-bg: #1e293b;
    --color-sidebar-bg: #0f172a;
    /* ... და სხვა */
}
```

### გამოყენება CSS-ში

```css
.button-primary {
    background: var(--color-primary);
    color: var(--color-btn-primary-text);
}

.sidebar {
    background: var(--color-sidebar-bg);
}
```

### Blade-ში სტილის მიღება

```blade
{{-- კონკრეტული სტილის მნიშვნელობა --}}
<img src="@godstyle('brand_header_logo')" alt="Logo">

{{-- ლოგო --}}
<img src="{{ $godModeBranding['brand_header_logo'] ?? '/images/logo.png' }}">

{{-- CSS injection ხელით --}}
@godstyles
```

### ინვოისებში გამოყენება

```php
use App\Models\GodModeStyle;

$invoiceLogo = GodModeStyle::getValue('brand_invoice_logo');
$primaryColor = GodModeStyle::getValue('color_primary');
```

---

## 📋 Audit Logging

### რა ლოგირდება
- Login / Logout
- უფლებების ცვლილება
- სტილების ცვლილება
- ლოგოების ატვირთვა
- Reset actions

### Log Entry Structure
```php
[
    'super_admin_id' => 1,
    'action' => 'permission.updated',
    'target_type' => 'App\Models\GodModePermission',
    'target_id' => 5,
    'old_value' => ['is_enabled_dealer' => true],
    'new_value' => ['is_enabled_dealer' => false],
    'ip_address' => '192.168.1.1',
    'user_agent' => 'Mozilla/5.0...',
    'created_at' => '2026-02-03 15:30:00'
]
```

---

## 🔧 არსებულ კოდში ინტეგრაცია

### ნაბიჯი 1: Middleware დამატება Route-ებზე

```php
// routes/web.php

// მაგალითი: ტრანზაქციების გვერდი
Route::resource('transactions', TransactionController::class)
    ->middleware('god.permission:transactions.access');

// მაგალითი: მანქანის წაშლა
Route::delete('cars/{car}', [CarController::class, 'destroy'])
    ->middleware('god.permission:cars.delete');
```

### ნაბიჯი 2: Blade Views-ში ღილაკების შემოწმება

```blade
{{-- cars/show.blade.php --}}
@godcan('cars.edit')
    <a href="{{ route('cars.edit', $car) }}" class="btn">
        რედაქტირება
    </a>
@endgodcan

@godcan('cars.delete')
    <form action="{{ route('cars.destroy', $car) }}" method="POST">
        @csrf @method('DELETE')
        <button type="submit" class="btn-danger">წაშლა</button>
    </form>
@endgodcan
```

### ნაბიჯი 3: მენიუში დამალვა

```blade
{{-- layouts/partials/sidebar.blade.php --}}
@godcan('transactions.access')
    <a href="{{ route('transactions.index') }}">ტრანზაქციები</a>
@endgodcan

@godcan('users.access')
    <a href="{{ route('users.index') }}">მომხმარებლები</a>
@endgodcan
```

---

## ⚙️ Cache Management

### Cache Clear
```bash
php artisan cache:clear
```

### Force Refresh
პერმიშენები და სტილები ავტომატურად ნახავენ cache-ს ცვლილებისას.

---

## 🚨 Security Considerations

1. **URL დამალვა** - `/god` URL არ უნდა იყოს საჯაროდ ცნობილი
2. **IP Whitelist** - პროდაქშენზე დაამატეთ IP შეზღუდვა
3. **2FA** - რეკომენდებულია ორფაქტორიანი ავთენტიფიკაცია
4. **პაროლი** - შეცვალეთ default პაროლი დაუყოვნებლივ
5. **Logs** - რეგულარულად შეამოწმეთ audit logs

---

## 📝 ახალი უფლების დამატება

```php
// database/seeders/GodModeSeeder.php

// ან პირდაპირ:
GodModePermission::create([
    'feature_key' => 'reports.export',
    'feature_name' => 'რეპორტების ექსპორტი',
    'feature_group' => 'reports',
    'description' => 'PDF/Excel ექსპორტი',
    'is_enabled_global' => true,
    'is_enabled_admin' => true,
    'is_enabled_dealer' => false,
    'is_enabled_client' => false,
]);
```

---

## 📝 ახალი სტილის დამატება

```php
GodModeStyle::create([
    'style_key' => 'color_card_bg',
    'style_name' => 'ბარათის ფონი',
    'style_group' => 'layout',
    'style_type' => 'color',
    'style_value' => '#1e293b',
    'default_value' => '#1e293b',
]);
```

---

## 🆘 Troubleshooting

### პრობლემა: სტილები არ ჩანს
```bash
php artisan view:clear
php artisan cache:clear
```

### პრობლემა: Permission არ მუშაობს
1. შეამოწმეთ cache: `php artisan cache:clear`
2. შეამოწმეთ DB-ში ჩანაწერი
3. შეამოწმეთ middleware დარეგისტრირებულია

### პრობლემა: Login არ მუშაობს
1. შეამოწმეთ super_admins ცხრილი
2. შეამოწმეთ is_active = true
3. Rate limit - დაელოდეთ 5 წუთი

---

**Created:** 2026-02-03  
**Version:** 1.0  
**Author:** God Mode System
