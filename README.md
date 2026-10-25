# OnlineBookStore — Fresh PHP + MySQL Project

A responsive bookstore demo built with HTML, CSS, JavaScript, Bootstrap-free custom responsive styling, procedural PHP, PDO, and MySQL. Runs locally in XAMPP.

## Included features
- Responsive homepage with OnlineBookStore SVG logo, moving announcement strip, animated hero/books, scrolling promo banners and reveal-on-scroll cards
- Seeded books and categories; search by title/author/category
- Book detail pages and category browse page
- Session-based shopping cart with add/update/remove and stock checks
- Registration/login/logout using password hashing
- Checkout with address, Cash on Delivery, and clearly labelled simulated demo payment
- My Orders and order tracking timeline
- Admin login, dashboard metrics, book add/edit/delete, stock/featured fields, order status updates
- Database schema and sample seed data

## Simple XAMPP setup (Windows)
1. Extract the `OnlineBookStore` folder into `C:\xampp\htdocs\`.
2. Open XAMPP Control Panel. Start **Apache** and **MySQL**.
3. In your browser, open `http://localhost/phpmyadmin/`.
4. Click **Import**, choose `OnlineBookStore/database.sql`, and click **Import/Go**. The SQL creates the `online_book_store` database and sample data.
5. Open `http://localhost/OnlineBookStore/setup.php` once. It creates the demo admin account with a hashed password.
6. Open `http://localhost/OnlineBookStore/`.
7. Admin panel: `http://localhost/OnlineBookStore/admin/login.php`
   - Username: `admin`
   - Password: `BookStore@123`
8. Delete `setup.php` after it has created the admin account.

## Database settings
The default settings in `config/config.php` match a typical XAMPP install:
- Host: `127.0.0.1`
- Port: `3307` (because the MySQL port was changed in your XAMPP setup; use `3306` if your MySQL is on the default port)
- Database: `online_book_store`
- Username: `root`
- Password: blank

If you set a MySQL password, update `DB_PASS` in `config/config.php`. If your project folder is not named `OnlineBookStore`, update `BASE_URL` in the same file.

## Try the site
- Browse the home page and categories
- Add books to cart
- Register a customer account, then log in
- Checkout with Cash on Delivery or the simulated demo payment
- Open My Orders and Track Order
- Sign in to admin to edit books and update order status

## Important notes
- **Demo payment only:** The `demo` payment option records a simulated success in the local database. It does not charge money and is not connected to Razorpay, Stripe, UPI, or any real payment gateway.
- **Local development:** Change the demo admin password and secure PHP/database settings before deployment. Delete `setup.php` after setup.
- Book covers use remote Unsplash image URLs. An SVG fallback appears if an image cannot load. For fully offline use, replace these URLs with local image files.
- Newsletter is a front-end demo only and does not send email.
- SQL import resets tables with these names in `online_book_store`; do not import into a database containing data you need to keep.
