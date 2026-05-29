# C&B Daily Dose Ordering and POS System

A real small-business web system for C&B Daily Dose, a bottled iced coffee and snack business.

Stack:
- Frontend: HTML, CSS, JavaScript
- Backend: PHP with PDO prepared statements
- Database: MySQL
- Local development: XAMPP or Laragon

## Features Included in Version 1

- Customer landing page, online menu, cart, checkout, and order confirmation
- Admin/cashier login with role-based access
- Dashboard with today’s sales, pending orders, low stock alerts, and best seller preview
- POS screen with product selection, payment method, amount paid, change, and receipt page
- Online order management with Pending, Accepted, Preparing, Ready, Completed, and Cancelled status flow
- Product and product variant management
- Inventory management with add, deduct, adjust, low stock alerts, and stock movement logs
- Recipe/ingredient setup for automatic inventory deduction
- Sales reports using both POS sales and completed online orders
- Expense tracking
- User management for owner/admin accounts

## Folder Structure

```text
cb-daily-dose-system/
  admin/              Admin dashboard pages
  assets/
    css/              Shared responsive styles
    img/              Local product and brand visuals
    js/               Shared JavaScript
  config/             Database connection
  customer/           Customer ordering website
  database/           MySQL schema and seed data
  include/            Shared PHP helpers and layout
  pos/                POS transaction and receipt pages
  public/             Entry redirect
  README.md
```

## Installation with XAMPP

1. Copy or keep this folder inside your XAMPP `htdocs` folder.
   Example:
   `C:\xampp\htdocs\cb-daily-dose-system`

2. Start Apache and MySQL from the XAMPP Control Panel.

3. Open phpMyAdmin:
   `http://localhost/phpmyadmin`

4. Import the database:
   - Click **Import**
   - Choose `database/cb_daily_dose.sql`
   - Click **Go**

5. Check the database config in:
   `config/database.php`

   Default local settings:
   ```php
   $host = '127.0.0.1';
   $dbName = 'cb_daily_dose';
   $username = 'root';
   $password = '';
   ```

6. Open the system:
   - Customer website: `http://localhost/cb-daily-dose-system/customer/`
   - Admin login: `http://localhost/cb-daily-dose-system/admin/login.php`
   - POS screen: `http://localhost/cb-daily-dose-system/pos/`

## Installation with Laragon

1. Put this project folder inside Laragon’s `www` folder.
   Example:
   `C:\laragon\www\cb-daily-dose-system`

2. Start Laragon, then start Apache/Nginx and MySQL.

3. Open phpMyAdmin, HeidiSQL, or your preferred MySQL tool.

4. Import:
   `database/cb_daily_dose.sql`

5. Visit:
   `http://localhost/cb-daily-dose-system/customer/`

## Default Logins

Owner/Admin:
- Email: `admin@cbdailydose.local`
- Password: `password`

Cashier:
- Email: `cashier@cbdailydose.local`
- Password: `password`

Change these passwords after importing the database.

## How Inventory Deduction Works

Products have variants, and variants can be connected to inventory items through the `recipes` table.

Example:
Caramel Coffee 250ml can deduct:
- Coffee: `100 ml`
- Milk: `80 ml`
- Flavored syrup: `20 ml`
- 250ml bottle: `1 pc`
- Label: `1 pc`

When a POS sale is completed, inventory is deducted immediately.

When an online order is marked `Completed`, inventory is deducted once. The `inventory_deducted` field prevents double deduction if the status is updated again.

## Notes for Beginners

- Use `admin/products.php` to add products and variants.
- Use `admin/inventory.php` to add raw materials, packaging, and recipe ingredients.
- Use `pos/index.php` for walk-in sales.
- Use `admin/orders.php` to process online customer orders.
- Use `admin/reports.php` for sales summaries.

All database queries that receive user input use PDO prepared statements.
