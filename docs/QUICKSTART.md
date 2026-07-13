# Quick Reference Guide

## 🚀 Quick Start for Developers

### 1. First Time Setup (5 minutes)

```bash
# Clone repository
git clone https://github.com/yourusername/restaurant-management-system.git
cd restaurant-management-system

# Create database
mysql -u root -p << EOF
CREATE DATABASE restaurant_management_db;
USE restaurant_management_db;
SOURCE database/schema.sql;
SOURCE database/sample_data.sql;
EOF

# Start web server
# Open XAMPP Control Panel and start Apache + MySQL

# Access application
# http://localhost/restaurant-management-system
```

### 2. Default Login Credentials
- **Email:** admin@restaurant.local
- **Password:** admin123
- ⚠️ Change immediately after first login!

---

## 📁 Project Structure Quick Reference

```
restaurant-management-system/
├── pages/
│   ├── auth/login.php                 # Login page
│   ├── dashboard/dashboard.php        # Main dashboard
│   ├── menu/                          # Menu management
│   ├── customers/                     # Customer management
│   ├── orders/                        # Order management
│   ├── billing/                       # Invoice management
│   ├── categories/                    # Category management
│   └── errors/                        # Error pages
├── includes/
│   ├── config.php                     # Bootstrap config
│   ├── auth.php                       # Auth helpers
│   ├── functions.php                  # Utility functions
│   ├── header.php                     # Page header template
│   └── footer.php                     # Page footer template
├── config/
│   └── database.php                   # Database connection
├── assets/
│   ├── css/styles.css                 # Main stylesheet
│   ├── js/app.js                      # JavaScript utilities
│   └── uploads/                       # User uploads
├── database/
│   ├── schema.sql                     # Database schema
│   └── sample_data.sql                # Sample/test data
├── docs/
│   ├── setup_guide.md                 # Installation guide
│   ├── database_schema.md             # Database documentation
│   ├── function_reference.md          # API reference
│   ├── FEATURE_CHECKLIST.md           # Feature list
│   └── quickstart.md                  # This file
├── logs/
│   └── app.log                        # Error logs
├── README.md                          # Project readme
├── CONTRIBUTING.md                    # Contribution guidelines
├── LICENSE                            # MIT License
└── .gitignore                         # Git ignore rules
```

---

## 🔑 Key Functions Reference

### PHP Functions

```php
// Authentication
requireAdminAuth();                          // Require admin login
ensureDefaultAdmin();                        // Create default admin if needed

// Database
$conn = getDbConnection();                  // Get DB connection
$conn->prepare("SELECT * FROM table");      // Use prepared statements

// Security
$token = generateCsrfToken('form_name');    // Generate CSRF token
verifyCsrfToken($_POST['csrf_token'], 'form_name'); // Verify token
echo csrfInputField('form_name');           // Print CSRF input
echo e($user_input);                        // Escape for HTML
$safe = sanitizeString($_POST['name']);     // Sanitize string
$id = sanitizeInt($_GET['id']);             // Sanitize integer

// UI/Messages
setFlashMessage('success', 'Done!');        // Set flash message
$flash = getFlashMessage();                 // Get flash message
echo displayFlashMessages();                // Display flash messages

// Files
uploadMenuImage($_FILES['image'], $dir);    // Upload image
renderEmptyState('Title', 'Message');       // Render empty state

// Logging
logError('Something went wrong');           // Log error
```

### JavaScript Utilities

```javascript
// Loading
LoadingSpinner.show();                      // Show loading spinner
LoadingSpinner.hide();                      // Hide loading spinner

// Notifications
Toast.success('Saved!', 5000);              // Success toast
Toast.error('Error!', 5000);                // Error toast
Toast.warning('Warning!', 5000);            // Warning toast
Toast.info('Info', 5000);                   // Info toast

// Form Validation
FormValidator.validateEmail(email);         // Validate email
FormValidator.validatePhone(phone);         // Validate phone
FormValidator.validatePrice(price);         // Validate price
FormValidator.validateRequired(value);      // Validate required
FormValidator.validateFileSize(file, 2);    // Validate file size
FormValidator.markFieldError(id, 'msg');    // Mark field error
FormValidator.clearFieldError(id);          // Clear field error
```

---

## 🔒 Security Checklist

When adding a new page/feature:

- [ ] Use `requireAdminAuth()` at the top
- [ ] Add CSRF token to all forms: `<?php echo csrfInputField('form_name'); ?>`
- [ ] Verify CSRF in handler: `verifyCsrfToken($_POST['csrf_token'], 'form_name')`
- [ ] Use prepared statements for all queries
- [ ] Sanitize inputs: `$name = sanitizeString($_POST['name']);`
- [ ] Escape output: `<?php echo e($variable); ?>`
- [ ] Validate all inputs on server-side
- [ ] Log security events: `logError('Security event: ...')`
- [ ] Validate file uploads (if applicable)

---

## 🐛 Common Issues & Solutions

| Issue | Solution |
|-------|----------|
| Blank page | Check `logs/app.log` for errors |
| Login fails | Ensure MySQL is running, DB imported |
| Can't upload images | Check `uploads/menu_images/` permissions (755) |
| CSRF errors | Ensure form has `csrfInputField('form_name')` |
| Session timeout | Check session settings in `includes/config.php` |
| Mobile display | Use Bootstrap classes: `col-md-6`, `d-md-flex` |
| Database locked | Restart MySQL, check long-running queries |

---

## 📊 Database Quick Commands

```bash
# Backup
mysqldump -u root -p restaurant_management_db > backup.sql

# Restore
mysql -u root -p restaurant_management_db < backup.sql

# Access MySQL console
mysql -u root -p
USE restaurant_management_db;
SHOW TABLES;
DESCRIBE menu_items;

# Export specific table
mysqldump -u root -p restaurant_management_db menu_items > menu_items.sql

# Import specific table
mysql -u root -p restaurant_management_db < menu_items.sql
```

---

## 🎯 Common Development Tasks

### Add a New Menu Item Field

1. **Database:**
   ```sql
   ALTER TABLE menu_items ADD COLUMN new_field VARCHAR(255) DEFAULT NULL;
   ```

2. **Add to Form (add_menu_item.php):**
   ```php
   <input type="text" name="new_field" value="<?php echo e($newField); ?>">
   ```

3. **Handle in POST:**
   ```php
   $newField = sanitizeString($_POST['new_field']);
   ```

4. **Save to Database:**
   ```php
   $stmt = $conn->prepare("INSERT INTO menu_items (..., new_field) VALUES (..., ?)");
   $stmt->bind_param("..., s", ..., $newField);
   ```

### Add a New Page

1. **Create file:** `pages/module/your_page.php`
2. **Add auth:** `<?php requireAdminAuth(); ?>`
3. **Add header/footer:** Include header.php and footer.php
4. **Add CSRF:** Use `csrfInputField()` on forms
5. **Handle POST:** Verify CSRF, sanitize, validate
6. **Add links:** Update sidebar if needed

### Modify the Dashboard

1. Edit: `pages/dashboard/dashboard.php`
2. Add new query: Use prepared statements
3. Update chart data: Modify JS variables
4. Add new card: Use Bootstrap row/col structure
5. Test responsiveness: Check on mobile/tablet

---

## 📈 Performance Tips

### Database Optimization
- Add indexes for frequently searched fields
- Use LIMIT for pagination
- Archive old orders periodically
- Use prepared statements (faster with repeated queries)

### Frontend Optimization
- Compress images before upload
- Lazy load images in lists
- Use Bootstrap utility classes (not custom CSS)
- Minimize CSS/JS in production

### General
- Cache database connections
- Use CDN for Bootstrap/icons
- Monitor logs for slow queries
- Use query analysis: `EXPLAIN SELECT ...`

---

## 🚀 Deployment Checklist

Before going live:

- [ ] Change all default passwords
- [ ] Review `logs/app.log` for errors
- [ ] Test all forms and workflows
- [ ] Set up database backups
- [ ] Enable HTTPS/SSL
- [ ] Review security settings
- [ ] Set appropriate file permissions
- [ ] Configure error reporting
- [ ] Test on production database size
- [ ] Set up monitoring/alerts
- [ ] Document deployment process
- [ ] Create emergency rollback plan

---

## 📞 Support Commands

```bash
# Check PHP version
php -v

# Check PHP syntax
php -l pages/dashboard/dashboard.php

# Check MySQL connection
mysql -u root -p -e "SELECT 1;"

# View logs
tail -f logs/app.log

# Check file permissions
ls -la uploads/menu_images/

# Check directory size
du -sh uploads/
```

---

## 🎓 Learning Resources

- **PHP:** [php.net/manual](https://www.php.net/manual)
- **MySQL:** [dev.mysql.com](https://dev.mysql.com)
- **Bootstrap:** [getbootstrap.com](https://getbootstrap.com)
- **Security:** [owasp.org](https://owasp.org)
- **Git:** [git-scm.com/doc](https://git-scm.com/doc)

---

## 💡 Tips & Tricks

### VSCode Extensions
- PHP Intelephense
- MySQL
- Bootstrap 5 Snippets
- Thunder Client (API testing)

### Debugging
```php
// Quick debug dump
echo "<pre>"; var_dump($variable); echo "</pre>";

// Log to file
logError("Debug: " . print_r($data, true));

// Check session
echo "<pre>"; print_r($_SESSION); echo "</pre>";
```

### Git Workflow
```bash
# Create feature branch
git checkout -b feature/your-feature

# Make changes
git add .
git commit -m "[feat] Your feature description"

# Push and create PR
git push origin feature/your-feature
```

---

## 📋 Helpful Templates

### Basic CRUD Page Template
```php
<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

requireAdminAuth();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF
    if (!verifyCsrfToken($_POST['csrf_token'], 'action')) {
        setFlashMessage('error', 'Invalid request');
        header('Location: list.php');
        exit;
    }
    
    // Sanitize inputs
    $name = sanitizeString($_POST['name']);
    
    // Validate
    if (empty($name)) {
        setFlashMessage('error', 'Name is required');
        header('Location: add.php');
        exit;
    }
    
    // Save
    $conn = getDbConnection();
    // ... database operations
    $conn->close();
    
    setFlashMessage('success', 'Done!');
    header('Location: list.php');
    exit;
}

$pageTitle = 'Page Title';
require_once 'includes/header.php';
?>
<!-- HTML content -->
<?php require_once 'includes/footer.php'; ?>
```

---

## Last Updated
2026-07-13

**Happy coding! 🎉**
