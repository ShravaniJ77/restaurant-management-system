# Installation and Setup Guide

## Prerequisites

Before you begin, make sure you have the following installed:

- **PHP 8.0 or higher** with MySQLi extension enabled
- **MySQL 5.7 or higher** (or MariaDB)
- **Apache** or **Nginx** web server
- **Git** (optional, for cloning the repository)
- A modern web browser (Chrome, Firefox, Safari, Edge)

## Installation Steps

### Step 1: Install XAMPP (Easiest Method)

#### Windows
1. Download XAMPP from [https://www.apachefriends.org](https://www.apachefriends.org)
2. Run the installer and follow the setup wizard
3. Choose components: Apache, MySQL, PHP (all checked by default)
4. Install to a location like `C:\xampp`
5. Launch XAMPP Control Panel after installation

#### Mac
1. Download XAMPP for Mac
2. Mount the DMG file
3. Run the installer
4. Default installation path: `/Applications/XAMPP`

#### Linux (Ubuntu/Debian)
```bash
# Install dependencies
sudo apt-get update
sudo apt-get install apache2 mysql-server php libapache2-mod-php php-mysql

# Or use XAMPP installer
wget https://www.apachefriends.org/xampp-installer.php
```

### Step 2: Start Services

#### Using XAMPP Control Panel
1. Open XAMPP Control Panel
2. Click "Start" for Apache
3. Click "Start" for MySQL
4. Verify both are running (green indicators)

#### Using Command Line (Linux/Mac)
```bash
sudo /opt/lampp/manager-linux-x64.run
# Or
sudo /Applications/XAMPP/manager-osx
```

### Step 3: Clone/Extract the Project

#### Using Git
```bash
cd C:\xampp\htdocs                # Windows
cd /Applications/XAMPP/htdocs/    # Mac
cd /opt/lampp/htdocs/             # Linux

git clone https://github.com/yourusername/restaurant-management-system.git
cd restaurant-management-system
```

#### Using Direct Download
1. Download the project ZIP file
2. Extract to your web root (`C:\xampp\htdocs\restaurant-management-system`)
3. Ensure the folder structure is intact

### Step 4: Create and Import Database

#### Method 1: Using phpMyAdmin (GUI - Easiest)

1. Open phpMyAdmin: [http://localhost/phpmyadmin](http://localhost/phpmyadmin)
2. Click "New" to create a new database
3. Database name: `restaurant_management_db`
4. Collation: `utf8mb4_unicode_ci`
5. Click "Create"
6. Select the new database
7. Go to "Import" tab
8. Click "Choose File" and select `database/schema.sql`
9. Click "Import"
10. (Optional) Import sample data: `database/sample_data.sql`

#### Method 2: Using MySQL Command Line

```bash
# Connect to MySQL
mysql -u root -p

# Create database
CREATE DATABASE restaurant_management_db;
USE restaurant_management_db;

# Import schema
source path/to/database/schema.sql;

# Import sample data (optional)
source path/to/database/sample_data.sql;

# Exit MySQL
exit;
```

#### Method 3: Using Terminal/PowerShell

```bash
# Windows PowerShell
mysql -u root -p restaurant_management_db < C:\xampp\htdocs\restaurant-management-system\database\schema.sql

# Mac/Linux Terminal
mysql -u root -p restaurant_management_db < /path/to/database/schema.sql

# Optional: Import sample data
mysql -u root -p restaurant_management_db < /path/to/database/sample_data.sql
```

### Step 5: Create Required Directories

Make sure these directories exist and are writable:

```bash
# Create uploads directory
mkdir -p uploads/menu_images

# Set permissions (Linux/Mac)
chmod 755 uploads
chmod 755 uploads/menu_images

# Create logs directory
mkdir -p logs
chmod 755 logs
```

**Windows Note:** Use File Explorer to create these directories if command line is not available.

### Step 6: Configure Database Connection (If Needed)

If you used different credentials, update `config/database.php`:

```php
<?php
define('DB_HOST', 'localhost');      // Database host
define('DB_USER', 'root');           // Database username
define('DB_PASSWORD', '');           // Database password (empty for default XAMPP)
define('DB_NAME', 'restaurant_management_db');
?>
```

### Step 7: Verify Installation

1. Navigate to the application in your browser:
   ```
   http://localhost/restaurant-management-system
   ```

2. You should be redirected to the login page

3. Use default credentials:
   - **Email:** `admin@restaurant.local`
   - **Password:** `admin123`

4. **⚠️ Important:** Change the admin password immediately!

## Post-Installation Configuration

### 1. Change Default Admin Password

1. Login with default credentials
2. Go to Profile → Settings
3. Enter new password
4. Save changes

### 2. Add Restaurant Information

Update these files with your restaurant details:

- `includes/config.php` - Restaurant name, URL
- Email settings (for notifications, if implemented)
- Business hours and contact info

### 3. Set Up Initial Data

1. **Add Categories:** Menu → Categories → Add Category
2. **Add Menu Items:** Menu → Menu Items → Add Item
3. **Add Customers:** Customers → Add Customer
4. **Create Orders:** Orders → Create Order

### 4. Configure File Uploads

1. Ensure `uploads/menu_images/` is writable
2. Check `config/database.php` for upload settings
3. Default max file size: 2MB (configurable in `includes/functions.php`)

### 5. Enable Logging (Production)

1. Ensure `logs/` directory exists and is writable
2. Check `logs/app.log` for error messages
3. Configure log retention policy in production

## Troubleshooting

### Common Issues and Solutions

#### "Connection Refused" Error
- **Cause:** MySQL or Apache not running
- **Solution:** Start services in XAMPP Control Panel

#### "Database Does Not Exist" Error
- **Cause:** Database not imported
- **Solution:** Re-run the SQL schema import (Step 4)

#### "Permission Denied" Error (Linux/Mac)
- **Cause:** Directory permissions not set correctly
- **Solution:** 
  ```bash
  chmod -R 755 uploads/
  chmod -R 755 logs/
  ```

#### "No Such File or Directory" Error
- **Cause:** File path incorrect
- **Solution:** Verify paths in `config/database.php` and `includes/config.php`

#### Login Loop
- **Cause:** Session not configured
- **Solution:** Check `includes/config.php` for session settings

#### Images Not Uploading
- **Cause:** Upload directory not writable or size limit exceeded
- **Solution:**
  - Check directory permissions: `chmod 755 uploads/menu_images/`
  - Verify image size (max 2MB)
  - Check server error logs

#### White Page / PHP Error
- **Cause:** PHP syntax error or fatal exception
- **Solution:**
  - Check `logs/app.log` for errors
  - Verify PHP version (8.0+)
  - Check error_reporting in php.ini

### Enable PHP Error Reporting (Development)

Edit `php.ini` in XAMPP:
```ini
display_errors = On
error_reporting = E_ALL
```

Then restart Apache.

## Database Backup and Recovery

### Create a Backup

```bash
# Windows
mysqldump -u root -p restaurant_management_db > backup.sql

# Mac/Linux
mysqldump -u root -p restaurant_management_db > backup.sql
```

### Restore from Backup

```bash
mysql -u root -p restaurant_management_db < backup.sql
```

## Performance Optimization

### For Development
- Use SQLite for local testing (optional)
- Enable query logging to identify slow queries
- Use Chrome DevTools for client-side optimization

### For Production
- Enable query caching in MySQL
- Use prepared statements (already implemented)
- Optimize database indexes
- Enable opcode caching (OPcache)
- Use a CDN for static assets
- Implement database connection pooling

## Security Hardening

1. **Change Default Credentials**
   - Admin password ✓
   - MySQL root password
   - Database name

2. **File Permissions**
   - `uploads/` - 755
   - `logs/` - 755
   - Configuration files - 644

3. **HTTPS/SSL**
   - Enable SSL in production
   - Update BASE_URL to use https://

4. **Database Security**
   - Use strong MySQL password
   - Limit database user privileges
   - Regular backups

5. **Update PHP**
   - Keep PHP version updated
   - Enable security patches
   - Review deprecation warnings

## Next Steps

1. ✅ Review the [README.md](../README.md) for feature documentation
2. ✅ Check [Database Schema](database_schema.md) for table structures
3. ✅ Create sample data for testing (already provided)
4. ✅ Test all module workflows
5. ✅ Set up regular backups
6. ✅ Monitor logs for errors

## Support and Resources

- **Official Documentation:** [README.md](../README.md)
- **Database Schema:** [database_schema.md](database_schema.md)
- **PHP Documentation:** [php.net](https://www.php.net)
- **MySQL Documentation:** [mysql.com](https://www.mysql.com)
- **Bootstrap Documentation:** [getbootstrap.com](https://getbootstrap.com)

## Getting Help

If you encounter issues:

1. Check this guide's troubleshooting section
2. Review `logs/app.log` for error messages
3. Check browser console for JavaScript errors (F12)
4. Open an issue on GitHub

---

**Happy Installing! 🚀**

