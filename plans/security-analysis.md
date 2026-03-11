# 🔐 OneCar CRM - უსაფრთხოების სრული ანალიზი

**თარიღი:** 2026-03-11  
**პროექტი:** portalv2 (OneCar CRM)  
**Framework:** Laravel (PHP)

---

## 📊 საერთო შეფასება

| კატეგორია | შეფასება | სტატუსი |
|-----------|----------|---------|
| Authentication | 7/10 | ⚠️ საშუალო |
| Authorization | 6/10 | ⚠️ საშუალო |
| Input Validation | 7/10 | ⚠️ საშუალო |
| File Upload Security | 6/10 | ⚠️ საშუალო |
| Session Management | 8/10 | ✅ კარგი |
| Rate Limiting | 5/10 | ❌ სუსტი |
| Sensitive Data | 6/10 | ⚠️ საშუალო |
| Route Security | 7/10 | ⚠️ საშუალო |

**საერთო ქულა: 6.5/10** — არსებობს მნიშვნელოვანი პრობლემები, რომლებიც გამოსასწორებელია.

---

## 🔴 კრიტიკული პრობლემები (HIGH SEVERITY)

### 1. Path Traversal - `/uploads` Route

**ფაილი:** [`routes/web.php:125-144`](routes/web.php:125)

```php
Route::get('/uploads/{path}', function ($path) {
    $filePath = storage_path('app/public/uploads/' . $path);
    if (!file_exists($filePath)) {
        $filePath = base_path('backupv1/uploads/' . $path);
    }
    if (!file_exists($filePath)) {
        $filePath = base_path('backupv1/public/uploads/' . $path);
    }
    return response()->file($filePath);
})->where('path', '.*')->name('uploads');
```

**პრობლემა:**
- Route **არ საჭიროებს authentication-ს** — ნებისმიერ ვიზიტორს შეუძლია ფაილების წვდომა
- `->where('path', '.*')` regex-ი იძლევა `../` path traversal-ის საშუალებას
- `backupv1/` დირექტორია ხელმისაწვდომია — შეიძლება შეიცავდეს სენსიტიურ მონაცემებს
- MIME type validation არ ხდება — შეიძლება PHP ფაილების სერვირება

**გამოსწორება:**
```php
Route::get('/uploads/{path}', function ($path) {
    // 1. Auth check
    if (!auth()->check()) abort(403);
    
    // 2. Path traversal prevention
    $path = ltrim($path, '/');
    if (str_contains($path, '..') || str_contains($path, "\0")) {
        abort(403);
    }
    
    // 3. Only serve from storage, not backupv1
    $filePath = storage_path('app/public/uploads/' . $path);
    
    if (!file_exists($filePath)) abort(404);
    
    // 4. MIME type whitelist
    $allowedMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'application/pdf'];
    $mime = mime_content_type($filePath);
    if (!in_array($mime, $allowedMimes)) abort(403);
    
    return response()->file($filePath);
})->where('path', '[a-zA-Z0-9/_\-\.]+')->middleware('auth')->name('uploads');
```

---

### 2. `ApprovedMiddleware` არ გამოიყენება Routes-ში

**ფაილი:** [`bootstrap/app.php:17-22`](bootstrap/app.php:17), [`routes/web.php:34`](routes/web.php:34)

**პრობლემა:**
```php
// bootstrap/app.php - middleware registered
'approved' => ApprovedMiddleware::class,

// routes/web.php - მაგრამ არ გამოიყენება!
Route::middleware(['auth'])->group(function () {
    // 'approved' middleware აქ არ არის!
```

`ApprovedMiddleware` კლასი არსებობს, მაგრამ **არცერთ route-ზე არ გამოიყენება**. შედეგად, unapproved მომხმარებელს შეუძლია სრული წვდომა სისტემაზე — მხოლოდ `LoginController`-ში ხდება `is_active` შემოწმება, მაგრამ `approved` ველი route-level-ზე არ მოწმდება.

**გამოსწორება:**
```php
Route::middleware(['auth', 'approved'])->group(function () {
    // ყველა protected route
});
```

---

### 3. Mass Assignment - `User` Model Balance

**ფაილი:** [`app/Models/User.php:13-23`](app/Models/User.php:13)

```php
protected $fillable = [
    'username', 'password', 'full_name', 'email', 'phone',
    'role',      // ⚠️ DANGEROUS
    'balance',   // ⚠️ DANGEROUS
    'sms_enabled',
    'approved',  // ⚠️ DANGEROUS
];
```

**პრობლემა:** `role`, `balance`, `approved` ველები `$fillable`-შია. თუ სადმე `$request->all()` ან `$request->validated()` გამოიყენება სრულად, მომხმარებელს შეუძლია საკუთარი role-ის შეცვლა.

**ProfileController-ში:**
```php
// app/Http/Controllers/ProfileController.php:33
$user->update($validated); // validated-ში მხოლოდ full_name, email, phone - OK
```

ProfileController სწორია, მაგრამ `role` და `balance` `$fillable`-ში ყოფნა რისკია.

**გამოსწორება:**
```php
protected $fillable = ['username', 'full_name', 'email', 'phone', 'sms_enabled'];
protected $guarded = ['role', 'balance', 'approved', 'password'];
```

---

### 4. `updateSingle` Settings - Arbitrary Key Write

**ფაილი:** [`app/Http/Controllers/SettingsController.php:64-82`](app/Http/Controllers/SettingsController.php:64)

```php
public function updateSingle(Request $request)
{
    $validated = $request->validate([
        'key' => 'required|string|max:100',  // ⚠️ ნებისმიერი key!
        'value' => 'nullable|string|max:1000',
        'group' => 'nullable|string|max:50',
    ]);

    Setting::set($validated['key'], $validated['value'], ...);
```

**პრობლემა:** Admin-ს შეუძლია ნებისმიერი setting key-ის დაწერა, მათ შორის `maintenance_mode`, `registration_enabled` და სხვა სისტემური პარამეტრები, რომლებიც `update()` მეთოდში excluded-ია.

**გამოსწორება:**
```php
$allowedKeys = ['company_name', 'company_address', 'company_phone', ...]; // whitelist
$validated = $request->validate([
    'key' => ['required', 'string', Rule::in($allowedKeys)],
    ...
]);
```

---

## 🟡 საშუალო სიმძიმის პრობლემები (MEDIUM SEVERITY)

### 5. Login Rate Limiting არ არის მთავარ Login-ზე

**ფაილი:** [`app/Http/Controllers/Auth/LoginController.php:29-63`](app/Http/Controllers/Auth/LoginController.php:29)

**პრობლემა:** God Mode-ს login-ს აქვს rate limiting (5 მცდელობა, 5 წუთი), მაგრამ მთავარ CRM login-ს **არ აქვს**. Brute force შეტევა შესაძლებელია.

**გამოსწორება:**
```php
public function login(Request $request)
{
    $key = 'login:' . $request->ip();
    if (RateLimiter::tooManyAttempts($key, 5)) {
        $seconds = RateLimiter::availableIn($key);
        throw ValidationException::withMessages([
            'username' => ["ძალიან ბევრი მცდელობა. სცადეთ {$seconds} წამში."],
        ]);
    }
    
    // ... existing logic ...
    
    if (!$user || !Hash::check($request->password, $user->password)) {
        RateLimiter::hit($key, 300);
        throw ValidationException::withMessages([...]);
    }
    
    RateLimiter::clear($key);
    // ...
}
```

---

### 6. File Upload - Extension-Only Validation

**ფაილი:** [`app/Services/FileUploadService.php:206-233`](app/Services/FileUploadService.php:206)

```php
public function validateFile(UploadedFile $file): bool
{
    $extension = strtolower($file->getClientOriginalExtension()); // ⚠️ client-provided!
    $allowedTypes = array_merge($this->allowedImageTypes, $this->allowedVideoTypes, $this->allowedDocTypes);
    
    if (!in_array($extension, $allowedTypes)) {
        return false;
    }
    // ...
}
```

**პრობლემა:**
- `getClientOriginalExtension()` client-მიერ მოწოდებული მონაცემია — შეიძლება გაყალბდეს
- `getimagesize()` მხოლოდ სურათებზე მოწმდება, video/doc ფაილებზე არა
- MIME type server-side შემოწმება არ ხდება

**გამოსწორება:**
```php
// Use finfo for MIME detection
$finfo = new \finfo(FILEINFO_MIME_TYPE);
$mimeType = $finfo->file($file->getPathname());

$allowedMimes = [
    'image/jpeg', 'image/png', 'image/gif', 'image/webp',
    'video/mp4', 'video/webm',
    'application/pdf',
];

if (!in_array($mimeType, $allowedMimes)) {
    return false;
}
```

---

### 7. `SESSION_ENCRYPT=false` Production-ში

**ფაილი:** [`.env.example:33`](.env.example:33)

```
SESSION_ENCRYPT=false
```

**პრობლემა:** Session encryption გამორთულია. Session hijacking შემთხვევაში session მონაცემები plaintext-ად იკითხება.

**გამოსწორება:** Production-ში `SESSION_ENCRYPT=true`.

---

### 8. `APP_DEBUG=true` .env.example-ში

**ფაილი:** [`.env.example:3`](.env.example:3)

```
APP_DEBUG=true
```

**პრობლემა:** Debug mode production-ში stack traces, database queries, environment variables-ს ამჟღავნებს.

**გამოსწორება:** Production-ში `APP_DEBUG=false`.

---

### 9. Wallet Transfer - Race Condition

**ფაილი:** [`app/Http/Controllers/WalletController.php:149-188`](app/Http/Controllers/WalletController.php:149)

```php
$balance = (float) $user->balance;
$amount = (float) $validated['amount'];
if ($amount > $balance) {
    return response()->json(['error' => '...'], 422);
}

// ⚠️ Race condition: balance შეიძლება შეიცვალოს check-დან create-მდე!
Transaction::create([...]);
```

**გამოსწორება:** Database transaction + pessimistic locking:
```php
DB::transaction(function () use ($user, $car, $amount) {
    $user = User::lockForUpdate()->find($user->id);
    if ($amount > $user->balance) {
        throw new \Exception('Insufficient balance');
    }
    Transaction::create([...]);
});
```

---

### 10. `StoreCarRequest` - VIN Uniqueness არ მოწმდება

**ფაილი:** [`app/Http/Requests/StoreCarRequest.php:24`](app/Http/Requests/StoreCarRequest.php:24)

```php
'vin' => 'required|string|max:17',
// ⚠️ unique:cars,vin არ არის!
```

**პრობლემა:** ერთი და იგივე VIN-ით მრავალი მანქანის დამატება შესაძლებელია, რაც ფინანსური გაბნეულობის მიზეზი შეიძლება გახდეს.

**გამოსწორება:**
```php
'vin' => 'required|string|max:17|unique:cars,vin',
```

---

### 11. `SettingsController::systemInfo()` - Server Information Disclosure

**ფაილი:** [`app/Http/Controllers/SettingsController.php:125-141`](app/Http/Controllers/SettingsController.php:125)

```php
$info = [
    'php_version' => PHP_VERSION,
    'laravel_version' => app()->version(),
    'server' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
    'database' => config('database.default'),
    ...
];
```

**პრობლემა:** სისტემური ინფორმაცია (PHP version, server software) ხელმისაწვდომია ნებისმიერი admin-ისთვის. ეს ინფორმაცია attackers-ს CVE-ების მოძიებაში ეხმარება.

---

## 🟢 კარგი პრაქტიკები (რაც სწორია)

### ✅ God Mode - კარგი იზოლაცია
- ცალკე `god` guard და `super_admins` ცხრილი
- Rate limiting (5 მცდელობა / 5 წუთი)
- Audit logging (login/logout)
- Active status check

### ✅ Session Management
- `$request->session()->regenerate()` login-ზე
- `$request->session()->invalidate()` + `regenerateToken()` logout-ზე
- CSRF protection (Laravel default)

### ✅ Password Security
- `Hash::check()` password verification
- `Password::min(6)` validation rule
- `'password' => 'hashed'` cast

### ✅ Authorization Checks
- `authorizeView()` / `authorizeEdit()` CarController-ში
- `forUser()` scope Car model-ში
- Admin-only routes middleware-ით დაცული

### ✅ Input Validation
- Form Requests (`StoreCarRequest`, `UpdateCarRequest`)
- Validated data გამოიყენება (არა `$request->all()`)
- Numeric fields min:0 validation

### ✅ File Upload
- Image processing (Intervention Image)
- Max size limit (10MB)
- Extension whitelist

---

## 📋 გამოსასწორებელი საკითხების პრიორიტეტული სია

```
🔴 CRITICAL (დაუყოვნებლივ):
  1. /uploads route - auth + path traversal fix
  2. ApprovedMiddleware - routes-ზე გამოყენება
  3. Mass Assignment - role/balance/approved guarding

🟡 HIGH (მალე):
  4. Login rate limiting - main CRM login
  5. File upload - MIME type server-side validation
  6. Wallet transfer - race condition (DB transaction)
  7. updateSingle settings - key whitelist

🟠 MEDIUM (დაგეგმვა):
  8. VIN uniqueness validation
  9. SESSION_ENCRYPT=true production
  10. APP_DEBUG=false production

🟢 LOW (გაუმჯობესება):
  11. systemInfo() - version info hiding
  12. Password minimum length (6 → 8+)
  13. HTTPS enforcement (HSTS headers)
```

---

## 🏗️ არქიტექტურული რეკომენდაციები

### 1. Laravel Policies გამოყენება
ამჟამად authorization custom methods-ით ხდება (`authorizeView`, `authorizeEdit`). Laravel Policies უფრო სტანდარტული და maintainable მიდგომაა:

```php
// app/Policies/CarPolicy.php
class CarPolicy {
    public function view(User $user, Car $car): bool { ... }
    public function update(User $user, Car $car): bool { ... }
}
```

### 2. API Rate Limiting
Calculator და სხვა public-facing endpoints-ზე rate limiting დამატება.

### 3. Content Security Policy (CSP) Headers
```php
// middleware
response()->header('Content-Security-Policy', "default-src 'self'");
response()->header('X-Frame-Options', 'DENY');
response()->header('X-Content-Type-Options', 'nosniff');
```

### 4. Audit Logging მთავარ CRM-ზე
God Mode-ს აქვს audit log, მაგრამ მთავარ CRM-ს არ აქვს. მნიშვნელოვანი ოპერაციები (balance change, car delete, user role change) უნდა ილოგოს.

---

## 🔄 Mermaid - Security Flow Diagram

```mermaid
graph TD
    A[HTTP Request] --> B{Route Match}
    B --> C[/uploads/path - PUBLIC]
    B --> D[/login - Guest Only]
    B --> E[Protected Routes]
    B --> F[/god/* - God Mode]

    C --> C1[⚠️ No Auth Check]
    C --> C2[⚠️ Path Traversal Risk]
    C --> C3[⚠️ backupv1 Access]

    E --> E1{auth middleware}
    E1 -->|Not logged in| E2[Redirect to login]
    E1 -->|Logged in| E3[⚠️ approved NOT checked]
    E3 --> E4{role middleware}
    E4 -->|admin| E5[Admin Routes]
    E4 -->|any| E6[General Routes]

    F --> F1{GodModeAuth}
    F1 -->|Not logged in| F2[Redirect god.login]
    F1 -->|Logged in| F3{isActive check}
    F3 -->|Active| F4[God Mode Dashboard]
    F3 -->|Inactive| F5[Logout + Error]

    D --> D1[⚠️ No Rate Limiting]
    D1 --> D2[Username/Password Check]
    D2 --> D3[is_active check]
    D3 --> D4[Login + Session Regenerate]
```

---

*ანგარიში შედგენილია: 2026-03-11 | Roo Architect Mode*
