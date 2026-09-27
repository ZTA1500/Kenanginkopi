# KenanginKopi - Local XAMPP PHP + MySQL App

## Quick setup (Windows + XAMPP)
1. Install XAMPP and start **Apache** and **MySQL**.
2. Create folder `C:\xampp\htdocs\kenanginkopi` and place all files preserving the structure.
3. Open phpMyAdmin: `http://localhost/phpmyadmin`.
4. Import `sql/kenanginkopi.sql` (choose the file in the Import tab). This creates tables and sample data.
   - Note: sample admin password hashes are placeholders. To create a known admin, either register a new user then set `role = 'Admin'` in `users` table, or update the admin password manually using a PHP password_hash snippet.
5. Ensure `inc/db.php` has correct DB credentials (`root` / blank by default).
6. Open the app: `http://localhost/kenanginkopi/index.php`.

## How it works (stages)
- Static UI files are in `assets/`.
- Shared DB connection: `inc/db.php`.
- Header/footer: `inc/header.php` and `inc/footer.php`.
- User flows:
  - Register: `register.php`
  - Login: `login.php` (option "Remember me" sets a cookie for 7 days)
  - Cart: `cart.php` uses PHP sessions to track cart
  - Profile / Edit / History pages for user actions
- Admin pages are in `admin/` (make a user an admin via phpMyAdmin to access)
  - Manage users: `admin/manage_users.php`
  - Manage stores: `admin/manage_stores.php`, add `admin/add_store.php`
  - Manage coffee: `admin/manage_coffee.php`, add `admin/add_coffee.php`

## Security & production notes
- This is a learning/demo project. For production:
  - Use HTTPS, secure cookies, CSRF tokens for forms.
  - Implement role checks more securely and centralize them.
  - Add prepared statements for all DB queries (already used mostly).
  - Validate and sanitize all inputs, protect file uploads, rate-limit, etc.

## Troubleshooting
- If DB connection fails, edit `inc/db.php` and confirm host/user/password/database.
- If admin access denied, check the `users` table and set `role='Admin'` for a user.

Enjoy — ask me to zip all files into a downloadable archive or to expand any page (AJAX, file uploads, improved UI).

PASSWORD FOR RIN = Rinn.12345 for the role Admin
adam = Adam1234 for User
jojo = Jojo1234 for User