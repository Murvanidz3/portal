# Project Baseline: Auto Dealer CRM

**Date**: 2026-02-02
**Version**: 1.0 (Baseline Analysis)

## 1. Architecture Overview
- **Framework**: Laravel 12.x (latest).
- **Stack**: Monolithic MVC.
- **Frontend**: Blade Templates + Vite (Vanilla CSS/JS or Tailwind).
- **Database**: MySQL/MariaDB.
- **Asset Management**: Vite.

## 2. Domain & Modules

### Core Entities
- **Users**: 
  - Roles: Admin (`admin`), Dealer (`dealer`), Client (`client`).
  - Features: Balance tracking (`balance`), SMS notifications, Approval system.
- **Cars**:
  - The central entity tracking the vehicle lifecycle.
  - **Lifecycle Statuses**: Purchased -> Warehouse -> Loaded -> On Way -> Poti -> Green -> Delivered.
  - **Financials**: Tracks Vehicle Cost, Auction Fee, Shipping, Additional Costs vs Paid Amount.
  - **Relationships**: Belongs to a User (Dealer) and a Client. Has many Files (Photos) and Transactions.
- **Finance**:
  - **Transactions**: Records payments/expenses linked to Cars or Users.
  - **Invoices**: Generating PDF/Viewable invoices for cars.
  - **Wallet**: Concept mapping to `User->balance`. Supports transfers (Wallet -> Car, Car -> Car).
- **Utilities**:
  - **Calculator**: Shipping cost estimation tool.
  - **SMS**: Notification system with templates and logging.
  - **Notifications**: Internal system notifications.

### Directory Structure Key Points
- **Models**: `app/Models` (Rich models with scopes and accessors).
- **Controllers**: `app/Http/Controllers` (Resource interactions).
- **Services**: `app/Services` (extracted logic for Files, SMS, Notifications).
- **Legacy**: `backupv1` directory exists, and the application creates a fallback bridge to serve uploads from there.

## 3. Eloquent Relationships
- **User** 
  - `hasMany` Cars (as Dealer).
  - `hasMany` ClientCars (as Client).
  - `hasMany` Transactions.
- **Car**
  - `belongsTo` User (Dealer).
  - `belongsTo` Client (User).
  - `hasMany` CarFiles (Polymorphic-like via category: auction, port, terminal).
  - `hasMany` Transactions.
  - `hasMany` Notifications.

## 4. Business Rules Summary
- **Access Control**: 
  - Admins see/manage all.
  - Dealers manage their own inventory.
  - Clients likely have read-only access to their assigned cars.
- **Financial Logic**:
  - **Debt Calculation**: `(Cost + Fees + Shipping + Other) - Paid Amount`.
  - **Overpayment**: Positive balance on a car.
  - **Transfers**: complex logic exists to move funds between entities.
- **Uploads**:
  - Stored in `storage/app/public` (standard) but also looks in `backupv1` (legacy).
  - Served via a PHP route `/uploads/{path}` which proxies the file.

## 5. Technical Debt & Weak Points
1.  **Legacy File Serving**: The `/uploads/{path}` route passes all image traffic through PHP to handle legacy paths. This is performantly expensive compared to direct Nginx/Apache serving.
2.  **Fat Controllers**: `CarController` is large (~30KB), likely containing mixed concerns that should be in `CarService` or FormRequests.
3.  **Limited FormRequests**: Only 2 Request classes exist (`StoreCarRequest`, `StoreUserRequest`). Validation logic is likely duplicated or inline in controllers.
4.  **No API Layer**: The application is tightly coupled to the web routes.
5.  **Hardcoded Configurations**: Some financial calculations or calculator logic might be hardcoded in controllers rather than configuration files or database tables.

## 6. Next Steps (Implicit)
- Maintain the strict strict types for financial calculations (`decimal:2` casts).
- Respect the existing Service layer (`SmsService`, `FileUploadService`).
- When refactoring, aim to move logic out of `CarController` into Services.
