# Function Reference

## Overview

This document provides a comprehensive reference of all helper functions available in the Restaurant Management System.

## Table of Contents

1. [Configuration Functions](#configuration-functions)
2. [Authentication Functions](#authentication-functions)
3. [Utility Functions](#utility-functions)
4. [Security Functions](#security-functions)
5. [UI/Validation Functions](#uivalidation-functions)
6. [JavaScript Utilities](#javascript-utilities)

---

## Configuration Functions

### File: `includes/config.php`

#### `getDbConnection()`
**Purpose:** Establish and return a database connection

**Returns:** `mysqli` object

**Example:**
```php
$connection = getDbConnection();
if ($connection->query("SELECT 1")) {
    echo "Connected";
}
$connection->close();
```

**Error Handling:** Exits with error message if connection fails

---

## Authentication Functions

### File: `includes/auth.php`

#### `requireAdminAuth()`
**Purpose:** Check if user is logged in as admin; redirect to login if not

**Returns:** void

**Behavior:**
- Verifies session is active
- Checks admin status in database
- Redirects to login if not authenticated

**Example:**
```php
<?php
require_once 'includes/auth.php';
requireAdminAuth();
// Code here only executes if user is authenticated admin
?>
```

#### `ensureDefaultAdmin()`
**Purpose:** Seed a default admin account if none exists

**Returns:** void

**Default Credentials:**
- Email: `admin@restaurant.local`
- Password: `admin123` (hashed)

**Example:**
```php
ensureDefaultAdmin();
// Now you can login with default credentials
```

---

## Utility Functions

### File: `includes/functions.php`

#### `getPageTitle($title, $default = 'Restaurant Admin')`
**Purpose:** Return page title with fallback

**Parameters:**
- `$title` (string): Page title
- `$default` (string): Fallback title if $title is empty

**Returns:** string

**Example:**
```php
$pageTitle = getPageTitle('Menu Items', 'Restaurant Admin');
echo $pageTitle; // "Menu Items"
```

#### `setFlashMessage($type, $message)`
**Purpose:** Store a message to display on next page load

**Parameters:**
- `$type` (string): 'success', 'error', 'warning', 'info'
- `$message` (string): Message content

**Returns:** void

**Example:**
```php
setFlashMessage('success', 'Item saved successfully!');
header('Location: index.php');
exit;
```

#### `getFlashMessage()`
**Purpose:** Retrieve and clear the stored flash message

**Returns:** array with 'type' and 'message' keys

**Example:**
```php
$flash = getFlashMessage();
if (!empty($flash['message'])) {
    echo "Type: " . $flash['type'];
    echo "Message: " . $flash['message'];
}
```

#### `displayFlashMessages()`
**Purpose:** Render flash message as Bootstrap alert HTML

**Returns:** HTML string (or empty string if no message)

**Example:**
```php
// In your template:
<?php echo displayFlashMessages(); ?>
```

**Output:**
```html
<div class="alert alert-success rounded-3" role="alert">Item saved successfully!</div>
```

#### `uploadMenuImage($file, $uploadDirectory)`
**Purpose:** Validate and process uploaded menu item image

**Parameters:**
- `$file` (array): $_FILES['image'] array
- `$uploadDirectory` (string): Destination directory path

**Returns:** array with keys:
- `success` (bool): Upload successful
- `file_name` (string): Uploaded filename (if successful)
- `message` (string): Error message (if unsuccessful)

**Validation Rules:**
- Accepted types: JPG, PNG, WEBP, GIF
- Max size: 2MB
- MIME type verified with finfo
- Content verified with getimagesize()

**Example:**
```php
$result = uploadMenuImage($_FILES['image'], ROOT_PATH . 'uploads/menu_images');

if ($result['success']) {
    $imagePath = $result['file_name'];
    // Save to database...
} else {
    echo "Error: " . $result['message'];
}
```

#### `renderEmptyState($title, $message, $icon = 'bi bi-info-circle')`
**Purpose:** Render a bootstrap empty state component

**Parameters:**
- `$title` (string): Empty state title
- `$message` (string): Empty state message
- `$icon` (string): Bootstrap icon class

**Returns:** HTML string

**Example:**
```php
<?php echo renderEmptyState('No items', 'Create your first menu item.', 'bi bi-plus-circle'); ?>
```

**Output:**
```html
<div class="empty-state text-center py-5">
    <div class="mb-3 text-muted" style="font-size: 2rem;"><i class="bi bi-plus-circle"></i></div>
    <h5 class="fw-semibold">No items</h5>
    <p class="text-muted mb-0">Create your first menu item.</p>
</div>
```

#### `logError($message)`
**Purpose:** Write error message to application log file

**Parameters:**
- `$message` (string): Error message

**Returns:** void

**Log Location:** `logs/app.log`

**Log Format:** `[YYYY-MM-DD HH:MM:SS] message`

**Example:**
```php
try {
    // Some operation
} catch (Exception $e) {
    logError('Error processing order: ' . $e->getMessage());
}
```

---

## Security Functions

### File: `includes/functions.php`

#### `generateCsrfToken($form = 'default')`
**Purpose:** Generate or retrieve existing CSRF token for a form

**Parameters:**
- `$form` (string): Form identifier for token scoping

**Returns:** string (32-byte hex token)

**Behavior:**
- Generates new token if none exists for form
- Stores in session: `$_SESSION['csrf_' . $form]`

**Example:**
```php
$token = generateCsrfToken('login');
echo $token; // "a1b2c3d4e5f6g7h8i9j0k1l2m3n4o5p6"
```

#### `verifyCsrfToken($token, $form = 'default')`
**Purpose:** Verify CSRF token validity

**Parameters:**
- `$token` (string): Token to verify
- `$form` (string): Form identifier

**Returns:** bool (true if valid)

**Behavior:**
- Uses `hash_equals()` for timing-safe comparison
- Regenerates token on successful verification
- Returns false if token invalid or missing

**Example:**
```php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'], 'login')) {
        die('CSRF token validation failed');
    }
    // Process form...
}
```

#### `csrfInputField($form = 'default')`
**Purpose:** Render hidden CSRF token input field

**Parameters:**
- `$form` (string): Form identifier

**Returns:** HTML string

**Example:**
```php
<form method="POST">
    <?php echo csrfInputField('add_menu'); ?>
    <input type="text" name="name">
    <button type="submit">Add</button>
</form>
```

**Output:**
```html
<input type="hidden" name="csrf_token" value="a1b2c3d4e5f6g7h8i9j0k1l2m3n4o5p6">
```

#### `e($value)`
**Purpose:** Escape string for safe HTML output (htmlspecialchars)

**Parameters:**
- `$value` (string): String to escape

**Returns:** string (escaped)

**Example:**
```php
<?php echo e($user_input); ?>
// Prevents XSS attacks
```

#### `sanitizeString($value)`
**Purpose:** Sanitize string input (trim and remove special chars)

**Parameters:**
- `$value` (mixed): Value to sanitize

**Returns:** string (sanitized)

**Example:**
```php
$name = sanitizeString($_POST['name']);
// "<script>alert('xss')</script>" becomes "scriptalertxssscript"
```

#### `sanitizeInt($value)`
**Purpose:** Sanitize and cast value to integer

**Parameters:**
- `$value` (mixed): Value to sanitize

**Returns:** int

**Example:**
```php
$id = sanitizeInt($_GET['id']);
// "123abc" becomes 123
```

#### `getPost($key, $default = null)`
**Purpose:** Safely retrieve POST value with fallback

**Parameters:**
- `$key` (string): POST key
- `$default` (mixed): Default if key doesn't exist

**Returns:** mixed

**Example:**
```php
$name = getPost('name', '');
$age = getPost('age', 0);
```

---

## UI/Validation Functions

### File: `assets/js/app.js`

#### JavaScript: `LoadingSpinner`
**Purpose:** Show/hide global loading spinner overlay

**Methods:**

##### `LoadingSpinner.show()`
```javascript
LoadingSpinner.show();
// Shows semi-transparent overlay with spinner
```

##### `LoadingSpinner.hide()`
```javascript
LoadingSpinner.hide();
// Hides spinner and allows user interaction
```

**Example:**
```javascript
LoadingSpinner.show();
fetch('/api/data')
    .then(response => response.json())
    .then(data => {
        console.log(data);
        LoadingSpinner.hide();
    });
```

---

#### JavaScript: `Toast`
**Purpose:** Display toast notifications

**Methods:**

##### `Toast.show(message, type, duration)`
```javascript
Toast.show('Order placed!', 'success', 5000);
// message: string
// type: 'success', 'danger', 'warning', 'info'
// duration: milliseconds (0 = never auto-close)
```

##### `Toast.success(message, duration)`
```javascript
Toast.success('Saved successfully!', 5000);
```

##### `Toast.error(message, duration)`
```javascript
Toast.error('An error occurred!', 5000);
```

##### `Toast.warning(message, duration)`
```javascript
Toast.warning('Please review this!', 5000);
```

##### `Toast.info(message, duration)`
```javascript
Toast.info('Here is some information.', 5000);
```

##### `Toast.hide(toastId)`
```javascript
const id = Toast.success('Message');
Toast.hide(id); // Manually close
```

**Example:**
```javascript
document.getElementById('saveBtn').addEventListener('click', function() {
    LoadingSpinner.show();
    
    fetch('/save', { method: 'POST', body: new FormData() })
        .then(r => r.json())
        .then(data => {
            LoadingSpinner.hide();
            Toast.success('Saved!');
        })
        .catch(err => {
            LoadingSpinner.hide();
            Toast.error('Error: ' + err.message);
        });
});
```

---

#### JavaScript: `FormValidator`
**Purpose:** Client-side form validation utilities

**Methods:**

##### `FormValidator.validateEmail(email)`
```javascript
FormValidator.validateEmail('test@example.com'); // true
FormValidator.validateEmail('invalid'); // false
```

##### `FormValidator.validatePhone(phone)`
```javascript
FormValidator.validatePhone('+1-555-0123'); // true
FormValidator.validatePhone('123'); // false
```

##### `FormValidator.validatePrice(price)`
```javascript
FormValidator.validatePrice('19.99'); // true
FormValidator.validatePrice('0'); // false
```

##### `FormValidator.validateRequired(value)`
```javascript
FormValidator.validateRequired('text'); // true
FormValidator.validateRequired(''); // false
```

##### `FormValidator.validateMinLength(value, minLength)`
```javascript
FormValidator.validateMinLength('password123', 8); // true
```

##### `FormValidator.validateMaxLength(value, maxLength)`
```javascript
FormValidator.validateMaxLength('text', 100); // true
```

##### `FormValidator.validateFileSize(file, maxSizeMB)`
```javascript
FormValidator.validateFileSize(fileInput.files[0], 2); // true if < 2MB
```

##### `FormValidator.validateFileType(file, allowedTypes)`
```javascript
FormValidator.validateFileType(file, ['image/jpeg', 'image/png']);
```

##### `FormValidator.markFieldError(fieldId, errorMessage)`
```javascript
FormValidator.markFieldError('emailInput', 'Invalid email format');
// Adds is-invalid class and displays error message
```

##### `FormValidator.clearFieldError(fieldId)`
```javascript
FormValidator.clearFieldError('emailInput');
// Removes is-invalid class and hides error
```

**Example:**
```javascript
document.getElementById('emailInput').addEventListener('blur', function() {
    if (FormValidator.validateEmail(this.value)) {
        FormValidator.clearFieldError('emailInput');
    } else {
        FormValidator.markFieldError('emailInput', 'Please enter a valid email');
    }
});
```

---

## Database Functions

### File: `config/database.php`

#### `getDbConnection()`
**Purpose:** Create and return MySQLi database connection

**Returns:** `mysqli` object

**Configuration:**
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASSWORD', '');
define('DB_NAME', 'restaurant_management_db');
```

**Example:**
```php
$conn = getDbConnection();
$result = $conn->query("SELECT * FROM admins");
$conn->close();
```

---

## Best Practices

### 1. Always Use Prepared Statements
```php
// ✅ Good
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();

// ❌ Bad
$result = $conn->query("SELECT * FROM users WHERE id = $id");
```

### 2. Escape Output
```php
// ✅ Good
<?php echo htmlspecialchars($user_input); ?>

// ❌ Bad
<?php echo $user_input; ?>
```

### 3. Validate CSRF on Forms
```php
// ✅ Good
<?php echo csrfInputField('my_form'); ?>

if (!verifyCsrfToken($_POST['csrf_token'], 'my_form')) {
    die('Invalid request');
}

// ❌ Bad - No CSRF protection
```

### 4. Log Errors
```php
// ✅ Good
try {
    // operation
} catch (Exception $e) {
    logError('Operation failed: ' . $e->getMessage());
}

// ❌ Bad - No logging
```

### 5. Use Flash Messages
```php
// ✅ Good
setFlashMessage('success', 'Item saved!');
header('Location: list.php');

// ❌ Bad - No user feedback
```

---

## Common Patterns

### Secure Form Submission
```php
<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

requireAdminAuth();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'], 'add_item')) {
        setFlashMessage('error', 'Security validation failed');
        header('Location: list.php');
        exit;
    }
    
    $name = sanitizeString($_POST['name']);
    $email = sanitizeString($_POST['email']);
    
    // Validate
    if (empty($name)) {
        setFlashMessage('error', 'Name is required');
        header('Location: add.php');
        exit;
    }
    
    // Save to database
    $conn = getDbConnection();
    $stmt = $conn->prepare("INSERT INTO items (name, email) VALUES (?, ?)");
    $stmt->bind_param("ss", $name, $email);
    
    if ($stmt->execute()) {
        setFlashMessage('success', 'Item added successfully');
    } else {
        logError('Database error: ' . $stmt->error);
        setFlashMessage('error', 'Failed to add item');
    }
    
    $stmt->close();
    $conn->close();
    
    header('Location: list.php');
    exit;
}
?>
```

### Image Upload
```php
$result = uploadMenuImage($_FILES['image'], ROOT_PATH . 'uploads/menu_images');

if ($result['success']) {
    // Save to database
    $imagePath = $result['file_name'];
} else {
    $errors[] = $result['message'];
    logError('Image upload failed: ' . $result['message']);
}
```

---

## See Also

- [Database Schema](database_schema.md)
- [Setup Guide](setup_guide.md)
- [README.md](../README.md)
