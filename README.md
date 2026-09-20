# MediShop

An online medicine shop coursework app built with PHP, HTML, CSS, and JavaScript. Customers can register, browse medicines, manage a cart, place orders, and track them. An admin can manage categories, medicines, customers, and order status.

## Run locally with XAMPP and MySQL

1. Start Apache and MySQL in XAMPP.
2. Copy this repository into `C:\xampp\htdocs\Online-Medical-Shop-Web-Tech`.
3. Import **`database.sql`** in phpMyAdmin. It creates the `wti` MySQL database and sample medicines.
4. Open `http://localhost/Online-Medical-Shop-Web-Tech/`.
5. Register a customer account. Public registration cannot create admin accounts.

The default local MySQL connection is in `model/database.php`. To make a local account an admin, run this in phpMyAdmin after registration:

```sql
UPDATE users SET role = 'admin' WHERE email = 'your-admin-email@example.com';
```

Use XAMPP's PHP when running the built-in server; another PHP installation may lack `mysqli`:

```powershell
& 'C:\xampp\php\php.exe' -S localhost:8000 -t .
```

## Deploy with Supabase, Render, Vercel, and GitHub

See **[DEPLOYMENT.md](DEPLOYMENT.md)** for the exact dashboard and environment-variable steps.

- `database.pg.sql`: PostgreSQL schema and sample medicines for a new Supabase project.
- `model/pg_compat.php`: PDO PostgreSQL adapter for the app's existing query/result API.
- `Dockerfile`: PHP/Apache service for Render.
- `vercel-proxy/vercel.mjs`: Vercel proxy; set `RENDER_ORIGIN` to the Render URL in Vercel project settings.
- Uploaded images use Supabase Storage in production and the local `uploads/` folder in development.

Existing MySQL customer/order data is **not** copied by `database.pg.sql`; it needs a separate data migration if you want to preserve it.

## Verification

```powershell
& 'C:\xampp\php\php.exe' tests\pg_compat_smoke.php
```

The test exercises the PostgreSQL adapter and the main database flow using an in-memory SQLite fixture. A live Supabase connection should also be tested after you configure your project credentials.
