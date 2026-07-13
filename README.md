# Restaurant Management System

[![PHP](https://img.shields.io/badge/PHP-8.0%2B-777BB4?logo=php&logoColor=white)](https://www.php.net/)
[![MySQL](https://img.shields.io/badge/MySQL-5.7%2B-005E87?logo=mysql&logoColor=white)](https://www.mysql.com/)
[![Bootstrap](https://img.shields.io/badge/Bootstrap-5.3-7952B3?logo=bootstrap&logoColor=white)](https://getbootstrap.com/)
[![License](https://img.shields.io/badge/License-MIT-green.svg)](LICENSE)

A modern, secure, and feature-rich restaurant management system built with PHP 8, MySQL, HTML5, CSS3, JavaScript, and Bootstrap 5. Perfect for managing menus, customers, orders, and billing operations.

## 🌟 Features

### Authentication & Security
- ✅ Secure admin authentication with session management
- ✅ CSRF protection on all forms
- ✅ Session timeout after inactivity
- ✅ Password hashing with PHP's `password_hash()`
- ✅ XSS protection with HTML escaping
- ✅ Input sanitization and validation
- ✅ Prepared statements to prevent SQL injection
- ✅ Error logging system

### Dashboard & Analytics
- ✅ Executive dashboard with KPI cards (Revenue, Orders, Customers, Menu Items)
- ✅ Interactive charts (Chart.js) showing revenue and order trends
- ✅ Recent orders list with status tracking
- ✅ Top-selling items analysis
- ✅ Quick action buttons for common tasks

### Menu Management
- ✅ Create, read, update, delete menu items
- ✅ Organize items by categories
- ✅ Image upload with validation and compression
- ✅ Price management
- ✅ Item descriptions and details
- ✅ Search and filter functionality

### Customer Management
- ✅ Add and manage customer profiles
- ✅ Customer contact information
- ✅ View customer order history
- ✅ Search and filter customers
- ✅ Customer details on orders

### Order Management
- ✅ Create new orders with customer selection
- ✅ Multi-item order composition
- ✅ Automatic price calculation
- ✅ Live grand total updates
- ✅ Order status tracking (Pending → Preparing → Ready → Served → Cancelled)
- ✅ Order history and details view
- ✅ Database transactions for data integrity

### Billing & Invoicing
- ✅ Automatic invoice generation from orders
- ✅ Professional invoice layout
- ✅ Invoice numbering system
- ✅ Printable invoices
- ✅ Restaurant, customer, and order details on invoice
- ✅ Itemized billing with line totals

### UI/UX Features
- ✅ Responsive Bootstrap 5 design (Mobile, Tablet, Desktop)
- ✅ Loading spinner for async operations
- ✅ Toast notifications (Success, Error, Warning, Info)
- ✅ Form validation (Client & Server-side)
- ✅ Empty state pages with helpful messages
- ✅ Error pages (404, 403, 500)
- ✅ Sidebar navigation
- ✅ Dark-friendly color scheme
- ✅ Accessibility features (ARIA labels, semantic HTML)

### Mobile Optimization
- ✅ Mobile-first responsive design
- ✅ Touch-friendly interface
- ✅ Optimized forms for mobile devices
- ✅ Responsive tables with horizontal scroll
- ✅ Mobile-optimized navigation

## 📋 Requirements

- **Server**: XAMPP/LAMP/LEMP with Apache or Nginx
- **PHP**: 8.0 or higher
- **MySQL**: 5.7 or higher
- **Browser**: Modern browser with JavaScript enabled (Chrome, Firefox, Safari, Edge)

## 🚀 Quick Start

### 1. Installation

#### Option A: Using XAMPP (Windows/Mac/Linux)

```bash
# 1. Download and install XAMPP from https://www.apachefriends.org

# 2. Clone the project into htdocs
cd C:\xampp\htdocs          # Windows
# or
cd /Applications/XAMPP/htdocs  # Mac
# or
cd /opt/lampp/htdocs        # Linux

git clone https://github.com/yourusername/restaurant-management-system.git
cd restaurant-management-system

# 3. Start XAMPP
# Start Apache and MySQL services from the XAMPP Control Panel

# 4. Import the database schema
# Open http://localhost/phpmyadmin
# Create a new database or let the script create it
# Import database/schema.sql

# 5. Open the application
# Navigate to http://localhost/restaurant-management-system
```

#### Option B: Manual Setup

1. **Extract the project** to your web root directory
2. **Create a MySQL database**:
   ```sql
   CREATE DATABASE restaurant_management_db;
   ```
3. **Import the schema** from `database/schema.sql`:
   ```bash
   mysql -u root -p restaurant_management_db < database/schema.sql
   ```
4. **Configure database connection** in `config/database.php` if needed
5. **Create the uploads directory** (if not exists):
   ```bash
   mkdir -p uploads/menu_images
   chmod 755 uploads
   ```

### 2. Default Admin Login

After setup, log in with:
- **Email**: admin@restaurant.local
- **Password**: admin123

⚠️ **Change the default password immediately after first login!**

### 3. First Steps

1. ✅ Add restaurant categories (e.g., Appetizers, Main Courses, Desserts)
2. ✅ Add menu items with images and prices
3. ✅ Add customers to your database
4. ✅ Create orders for customers
5. ✅ Generate invoices
6. ✅ Monitor analytics on the dashboard

## 📁 Project Structure

```
restaurant-management-system/
├── assets/
│   ├── css/
│   │   └── styles.css           # Main stylesheet with responsive design
│   ├── js/
│   │   └── app.js               # JavaScript utilities (validation, toast, spinner)
│   └── uploads/
│       └── menu_images/         # User-uploaded menu item images
├── config/
│   └── database.php             # Database connection configuration
├── database/
│   ├── schema.sql               # Database schema and tables
│   └── sample_data.sql          # Sample/dummy data for testing
├── docs/
│   ├── setup_guide.md           # Detailed setup instructions
│   ├── database_schema.md       # Database structure documentation
│   ├── api_reference.md         # API endpoints (if applicable)
│   └── screenshots/             # Project screenshots
├── includes/
│   ├── config.php               # Bootstrap and session configuration
│   ├── auth.php                 # Authentication helpers
│   ├── functions.php            # Reusable utility functions
│   ├── header.php               # Page header/layout template
│   └── footer.php               # Page footer template
├── logs/
│   └── app.log                  # Application error and security logs
├── pages/
│   ├── auth/
│   │   └── login.php            # Admin login page
│   ├── dashboard/
│   │   └── dashboard.php        # Main dashboard with analytics
│   ├── menu/
│   │   ├── add_menu_item.php    # Add new menu item
│   │   ├── edit_menu_item.php   # Edit menu item
│   │   ├── view_menu.php        # View all menu items
│   │   └── delete_menu_item.php # Delete menu item
│   ├── customers/
│   │   ├── add_customer.php     # Add new customer
│   │   ├── edit_customer.php    # Edit customer details
│   │   ├── view_customers.php   # View all customers
│   │   └── delete_customer.php  # Delete customer
│   ├── orders/
│   │   ├── create_order.php     # Create new order
│   │   ├── view_orders.php      # View all orders
│   │   ├── order_details.php    # View order details
│   │   └── delete_order.php     # Delete order
│   ├── billing/
│   │   ├── invoice.php          # Invoice preview
│   │   └── printable_bill.php   # Printable invoice
│   ├── profile/
│   │   └── profile.php          # Admin profile settings
│   ├── categories/              # Category management
│   └── errors/
│       ├── error_404.php        # 404 Not Found page
│       ├── error_403.php        # 403 Forbidden page
│       └── error_500.php        # 500 Server Error page
├── uploads/
│   └── menu_images/             # Directory for uploaded menu images
├── tests/
│   └── test_data.sql            # Test data setup
├── .gitignore                   # Git ignore configuration
├── index.php                    # Application entry point
├── README.md                    # This file
└── LICENSE                      # MIT License

```

## 🔐 Security Features

### Implemented Security Measures

1. **CSRF Protection**
   - Token generation and verification on all forms
   - Token regeneration after successful use
   - Form-specific tokens to prevent collision

2. **Session Security**
   - Secure session cookie parameters
   - Session timeout after 30 minutes of inactivity
   - Session ID regeneration on login

3. **Password Security**
   - PHP `password_hash()` for secure password storage
   - `password_verify()` for authentication
   - Default admin password should be changed immediately

4. **Input Validation & Sanitization**
   - Server-side validation on all forms
   - Input sanitization to prevent XSS attacks
   - Type casting and filtering
   - Regular expressions for format validation

5. **Database Security**
   - Prepared statements for all SQL queries
   - Parameter binding to prevent SQL injection
   - Foreign key constraints for data integrity

6. **File Upload Security**
   - MIME type validation (via `finfo`)
   - File size limits (2MB for images)
   - Content verification using `getimagesize()`
   - Extension validation against MIME type
   - Unique file naming
   - Upload directory outside web root (optional)

7. **Error Handling**
   - Custom error pages (404, 403, 500)
   - Error logging without exposing sensitive details
   - User-friendly error messages

8. **Access Control**
   - Admin authentication required for all pages
   - Role-based access (extendable)
   - Session verification on every request

## 📊 Database Schema

### Tables Overview

| Table | Purpose | Key Fields |
|-------|---------|-----------|
| `admins` | Admin user accounts | id, email, password_hash, status |
| `categories` | Menu categories | id, name, description, status |
| `menu_items` | Food/beverage items | id, category_id, name, price, image_path, status |
| `customers` | Customer records | id, full_name, phone, email, address |
| `orders` | Customer orders | id, customer_id, admin_id, order_date, total_amount, status |
| `order_items` | Items in orders | id, order_id, menu_item_id, quantity, unit_price, line_total |

See [Database Schema Documentation](docs/database_schema.md) for detailed information.

## 🎨 UI/UX Improvements

### Recent Enhancements

- ✅ Loading spinner for better UX during async operations
- ✅ Toast notifications (Success, Error, Warning, Info)
- ✅ Form validation with visual feedback
- ✅ Empty state pages with helpful messages
- ✅ Mobile-optimized interface
- ✅ Responsive Bootstrap 5 layout
- ✅ Professional error pages

### Responsive Breakpoints

- **Desktop**: > 992px (Full layout with sidebar)
- **Tablet**: 768px - 992px (Stacked layout)
- **Mobile**: < 768px (Optimized for touch)

## 🧪 Testing

### Manual Testing Checklist

- [ ] Login with default admin credentials
- [ ] Change admin password
- [ ] Add/Edit/Delete menu categories
- [ ] Add/Edit/Delete menu items with image upload
- [ ] Add/Edit/Delete customers
- [ ] Create new orders with multiple items
- [ ] Update order status
- [ ] Generate and print invoices
- [ ] Check analytics dashboard
- [ ] Test CSRF protection (should reject invalid tokens)
- [ ] Test session timeout
- [ ] Test on mobile devices
- [ ] Check all error pages (404, 403, 500)

### Sample Data

Import sample data for testing:
```bash
mysql -u root -p restaurant_management_db < database/sample_data.sql
```

## 🐛 Known Issues & Limitations

- None currently documented. Please report any issues!

## 🛣️ Roadmap

- [ ] Payment gateway integration
- [ ] Inventory management
- [ ] Staff management
- [ ] Table reservation system
- [ ] Multi-language support
- [ ] API for mobile apps
- [ ] Advanced reporting
- [ ] Email notifications
- [ ] SMS alerts

## 📝 License

This project is licensed under the MIT License. See [LICENSE](LICENSE) for details.

## 🤝 Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

### Steps to Contribute

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

## 📧 Support

For support, please open an issue on the GitHub repository or contact the development team.

## 🙏 Acknowledgments

- Bootstrap 5.3 for UI framework
- Chart.js for analytics charts
- Bootstrap Icons for icon set
- PHP community for excellent documentation
- All contributors who have helped improve this project

---

**Built with ❤️ by the Restaurant Management Team**

