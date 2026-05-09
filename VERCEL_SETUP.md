# Vercel Deployment Setup Guide

## For Sammy Worx Website Login System

### Prerequisites
- Vercel account
- TiDB Cloud database (already set up)
- GitHub repository connected to Vercel

### Step 1: Deploy to Vercel

1. Go to [vercel.com](https://vercel.com)
2. Click "Add New" → "Project"
3. Select your GitHub repository (sammy-worx-website)
4. Click "Import"

### Step 2: Configure Environment Variables in Vercel

In the Vercel dashboard for your project:

1. Go to **Settings** → **Environment Variables**
2. Add these variables:

```
DB_HOST = gateway01.ap-southeast-1.prod.alicloud.tidbcloud.com
DB_USER = 21cRDQ1yQsS5317.root
DB_PASSWORD = LZhyUl0yo2bHuMBE
DB_NAME = sammyworx_db
```

3. Click "Save"

### Step 3: Verify Configuration

The following files are already Vercel-compatible:

- ✅ `api/config.php` - Reads from environment variables
- ✅ `api/auth.php` - Handles login with SSL fallback
- ✅ `api/admin.php` - Displays inquiries
- ✅ `api/login.php` - Login form
- ✅ `api/logout.php` - Logout handler
- ✅ `vercel.json` - Vercel routing configuration

### Step 4: Session Management

Sessions are automatically saved to `/tmp` on Vercel. This is configured in `api/config.php`:

```php
if (IS_VERCEL && !file_exists('/tmp')) {
    mkdir('/tmp', 0777, true);
}
```

### Step 5: Test Login

1. Visit your Vercel deployment: `https://your-domain.vercel.app/api/login.php`
2. Try logging in with:
   - **Username:** `sammyworx` | **Password:** `0844sammy`
   - OR **Username:** `staff` | **Password:** `staff123`

3. If successful, you should see the admin dashboard with inquiries

### Troubleshooting

#### Issue: "Database connection failed"
- Check that environment variables are set in Vercel dashboard
- Verify TiDB is running and accessible from Vercel
- Check that the host, user, and password are correct

#### Issue: "Wrong username or password"
- Verify credentials exist in TiDB `users` table
- Check that passwords match exactly (case-sensitive)
- Run this query in TiDB:
  ```sql
  SELECT username, password FROM users;
  ```

#### Issue: Session not persisting
- This is normal on Vercel's serverless architecture
- Sessions use `/tmp` which persists within the same deployment
- Logging out clears the session cookie

### Database Query Reference

Check users in TiDB:
```sql
SELECT * FROM sammyworx_db.users;
```

Check inquiries:
```sql
SELECT * FROM sammyworx_db.contact_inquiries ORDER BY created_at DESC;
```

### Security Notes

For production:
1. Store sensitive credentials in Vercel's Environment Variables (NOT in code)
2. Use environment variables for all database credentials
3. Enable HTTPS (Vercel does this by default)
4. Consider using stronger password hashing in the database
