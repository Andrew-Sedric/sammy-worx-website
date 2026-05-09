# Staff Login Setup Guide

## Step-by-Step Setup Instructions

### 1. Update TiDB Configuration
Open `api/config.php` and replace the placeholder values with your TiDB credentials:

```php
define('DB_HOST', 'your_tidb_host:4000');        // Example: gateway01.us-west-2.prod.aws.tidbcloud.com:4000
define('DB_USER', 'your_tidb_user');              // Example: root or your custom TiDB user
define('DB_PASSWORD', 'your_tidb_password');      // Your TiDB password
define('DB_NAME', 'your_database_name');          // Your database name
```

### 2. Verify Your TiDB Tables
Make sure you have created the `users` table in TiDB with this structure:

```sql
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

### 3. Insert Your Staff Credentials
Insert your staff login credentials into the `users` table:

```sql
INSERT INTO users (username, password) VALUES ('your_username', 'your_password');
```

**Note:** The system supports both plain text and MD5-hashed passwords.

### 4. Test the Login
- Go to any HTML page on your site
- Click on "Staff Login" link (in the footer)
- Enter your username and password
- Click "Login"

If successful, you'll be redirected to the admin dashboard showing all client inquiries.

## Login Links Location

The "Staff Login" link appears in the footer of these pages:
- index.html
- about.html
- blog.html
- blog-single-1.html
- blog-single-2.html
- blog-single-3.html
- contact.html
- gallery.html
- testimonials.html
- services.html

All links point to: `/api/login.php`

## Troubleshooting

### If you see "Database is not configured"
- Check that you updated `api/config.php` with all credentials
- Ensure no placeholder values (like 'your_tidb_host') remain

### If you see "Database connection failed"
- Verify your TiDB host, port, username, and password
- Check that your TiDB user has permission to access the database
- Ensure your TiDB instance is running and accessible

### If you see "Wrong username or password"
- Verify the credentials are correctly inserted in the `users` table
- Check for typos in username or password
- Ensure the password matches exactly what was stored

## Security Notes
- Change default credentials in `config.php` (placeholder values)
- Ensure `config.php` has proper file permissions (readable by web server only)
- Consider implementing password hashing for production
- Always use HTTPS in production environments (set `session.cookie_secure` to 1 and `USE_SSL` to true)
