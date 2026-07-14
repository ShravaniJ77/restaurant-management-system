# Database Schema Documentation

## Overview

The Restaurant Management System uses a relational database with 6 core tables and appropriate foreign key constraints to ensure data integrity.

**Database Name:** `restaurant_management_db`
**Character Set:** `utf8mb4`
**Collation:** `utf8mb4_unicode_ci`

## Table Relationships

```
admins (1) ──────────────┐
                         │
                    (many) orders (many) ─────────────────┐
                         │                                 │
                  customer_id                           order_id
                         │                                 │
                    (1) customers              (many) order_items (many) ──────┐
                                                           │                   │
                                                       menu_item_id            │
                                                           │                   │
                                                      (1) menu_items ◄─────────┘
                                                           │
                                                       category_id
                                                           │
                                                      (1) categories
```

## Tables

### 1. admins
**Purpose:** Store administrator/staff accounts for system access and order creation

| Field | Type | Null | Key | Default | Description |
|-------|------|------|-----|---------|-------------|
| `id` | INT | NO | PK | AUTO_INCREMENT | Unique admin identifier |
| `full_name` | VARCHAR(100) | NO | | | Admin's full name |
| `email` | VARCHAR(100) | NO | UQ | | Unique email address (login credential) |
| `password_hash` | VARCHAR(255) | NO | | | Hashed password (PHP password_hash) |
| `phone` | VARCHAR(20) | YES | | NULL | Contact phone number |
| `status` | VARCHAR(20) | NO | | 'active' | Account status: active, inactive, suspended |
| `created_at` | TIMESTAMP | NO | | CURRENT_TIMESTAMP | Account creation timestamp |
| `updated_at` | TIMESTAMP | NO | | CURRENT_TIMESTAMP | Last update timestamp |

**Indexes:**
- Primary Key: `id`
- Unique Key: `email` (for login uniqueness)

**Sample Query:**
```sql
SELECT * FROM admins WHERE status = 'active' ORDER BY full_name;
```

---

### 2. categories
**Purpose:** Organize menu items into logical groupings

| Field | Type | Null | Key | Default | Description |
|-------|------|------|-----|---------|-------------|
| `id` | INT | NO | PK | AUTO_INCREMENT | Unique category identifier |
| `name` | VARCHAR(100) | NO | UQ | | Category name (e.g., "Appetizers") |
| `description` | TEXT | YES | | NULL | Detailed category description |
| `status` | VARCHAR(20) | NO | | 'active' | Status: active, archived |
| `created_at` | TIMESTAMP | NO | | CURRENT_TIMESTAMP | Creation timestamp |
| `updated_at` | TIMESTAMP | NO | | CURRENT_TIMESTAMP | Last update timestamp |

**Indexes:**
- Primary Key: `id`
- Unique Key: `name`

**Sample Query:**
```sql
SELECT * FROM categories WHERE status = 'active' ORDER BY name;
```

---

### 3. menu_items
**Purpose:** Store individual food and beverage items with pricing and descriptions

| Field | Type | Null | Key | Default | Description |
|-------|------|------|-----|---------|-------------|
| `id` | INT | NO | PK | AUTO_INCREMENT | Unique menu item identifier |
| `category_id` | INT | NO | FK | | Reference to category |
| `name` | VARCHAR(150) | NO | | | Item name (e.g., "Grilled Chicken") |
| `description` | TEXT | YES | | NULL | Detailed item description |
| `price` | DECIMAL(10,2) | NO | | | Item price (max $99,999.99) |
| `image_path` | VARCHAR(255) | YES | | NULL | Path to item image file |
| `status` | VARCHAR(20) | NO | | 'active' | Status: active, archived, discontinued |
| `created_at` | TIMESTAMP | NO | | CURRENT_TIMESTAMP | Creation timestamp |
| `updated_at` | TIMESTAMP | NO | | CURRENT_TIMESTAMP | Last update timestamp |

**Foreign Keys:**
- `category_id` → `categories.id` (ON DELETE CASCADE)

**Indexes:**
- Primary Key: `id`
- Foreign Key: `category_id`

**Sample Query:**
```sql
SELECT m.*, c.name as category_name FROM menu_items m
LEFT JOIN categories c ON c.id = m.category_id
WHERE m.status = 'active' ORDER BY c.name, m.name;
```

---

### 4. customers
**Purpose:** Store customer information for orders and tracking

| Field | Type | Null | Key | Default | Description |
|-------|------|------|-----|---------|-------------|
| `id` | INT | NO | PK | AUTO_INCREMENT | Unique customer identifier |
| `full_name` | VARCHAR(100) | NO | | | Customer's full name |
| `phone` | VARCHAR(20) | YES | | NULL | Contact phone number |
| `email` | VARCHAR(100) | YES | | NULL | Email address |
| `address` | TEXT | YES | | NULL | Physical delivery address |
| `created_at` | TIMESTAMP | NO | | CURRENT_TIMESTAMP | Record creation timestamp |
| `updated_at` | TIMESTAMP | NO | | CURRENT_TIMESTAMP | Last update timestamp |

**Indexes:**
- Primary Key: `id`

**Sample Query:**
```sql
SELECT * FROM customers ORDER BY created_at DESC;
```

---

### 5. orders
**Purpose:** Store customer orders with status and payment tracking

| Field | Type | Null | Key | Default | Description |
|-------|------|------|-----|---------|-------------|
| `id` | INT | NO | PK | AUTO_INCREMENT | Unique order identifier |
| `customer_id` | INT | NO | FK | | Reference to customer |
| `admin_id` | INT | NO | FK | | Reference to admin who created order |
| `order_date` | TIMESTAMP | NO | | CURRENT_TIMESTAMP | Order placement timestamp |
| `total_amount` | DECIMAL(10,2) | NO | | | Order total (auto-calculated) |
| `payment_status` | VARCHAR(30) | NO | | 'unpaid' | Payment status: unpaid, paid, refunded, partial |
| `order_status` | VARCHAR(30) | NO | | 'pending' | Order status: pending, preparing, ready, served, cancelled |
| `created_at` | TIMESTAMP | NO | | CURRENT_TIMESTAMP | Record creation timestamp |
| `updated_at` | TIMESTAMP | NO | | CURRENT_TIMESTAMP | Last update timestamp |

**Foreign Keys:**
- `customer_id` → `customers.id` (ON DELETE CASCADE)
- `admin_id` → `admins.id` (ON DELETE CASCADE)

**Indexes:**
- Primary Key: `id`
- Foreign Key: `customer_id`, `admin_id`

**Order Status Flow:**
```
pending → preparing → ready → served → (complete)
  └──────────────→ cancelled → (complete)
```

**Sample Query:**
```sql
SELECT o.*, c.full_name, a.full_name as admin_name 
FROM orders o
LEFT JOIN customers c ON c.id = o.customer_id
LEFT JOIN admins a ON a.id = o.admin_id
WHERE o.order_date >= DATE_SUB(NOW(), INTERVAL 7 DAY)
ORDER BY o.order_date DESC;
```

---

### 6. order_items
**Purpose:** Store line items in each order with quantity and pricing

| Field | Type | Null | Key | Default | Description |
|-------|------|------|-----|---------|-------------|
| `id` | INT | NO | PK | AUTO_INCREMENT | Unique line item identifier |
| `order_id` | INT | NO | FK | | Reference to parent order |
| `menu_item_id` | INT | NO | FK | | Reference to menu item |
| `quantity` | INT | NO | | | Quantity ordered (must be > 0) |
| `unit_price` | DECIMAL(10,2) | NO | | | Price per unit at time of order |
| `line_total` | DECIMAL(10,2) | NO | | | Total for this line (quantity × unit_price) |
| `created_at` | TIMESTAMP | NO | | CURRENT_TIMESTAMP | Record creation timestamp |

**Foreign Keys:**
- `order_id` → `orders.id` (ON DELETE CASCADE)
- `menu_item_id` → `menu_items.id` (ON DELETE CASCADE)

**Indexes:**
- Primary Key: `id`
- Foreign Key: `order_id`, `menu_item_id`

**Sample Query:**
```sql
SELECT oi.*, m.name as menu_item_name, o.id as order_id
FROM order_items oi
LEFT JOIN menu_items m ON m.id = oi.menu_item_id
LEFT JOIN orders o ON o.id = oi.order_id
WHERE o.id = ? ORDER BY oi.id;
```

---

## Common Query Patterns

### Get Order Summary with Items
```sql
SELECT 
    o.id,
    o.order_date,
    c.full_name,
    o.total_amount,
    o.order_status,
    o.payment_status,
    COUNT(oi.id) as item_count
FROM orders o
LEFT JOIN customers c ON c.id = o.customer_id
LEFT JOIN order_items oi ON oi.order_id = o.id
WHERE o.id = ?
GROUP BY o.id;
```

### Revenue Report by Category
```sql
SELECT 
    c.name,
    COUNT(oi.id) as items_sold,
    SUM(oi.line_total) as total_revenue,
    AVG(oi.unit_price) as avg_price
FROM categories c
LEFT JOIN menu_items m ON m.category_id = c.id
LEFT JOIN order_items oi ON oi.menu_item_id = m.id
WHERE c.status = 'active'
GROUP BY c.id
ORDER BY total_revenue DESC;
```

### Top Selling Items
```sql
SELECT 
    m.id,
    m.name,
    m.price,
    SUM(oi.quantity) as total_quantity,
    SUM(oi.line_total) as total_revenue
FROM menu_items m
LEFT JOIN order_items oi ON oi.menu_item_id = m.id
WHERE m.status = 'active'
GROUP BY m.id
ORDER BY total_quantity DESC
LIMIT 10;
```

### Recent Orders (Last 7 Days)
```sql
SELECT 
    o.id,
    o.order_date,
    c.full_name,
    SUM(oi.quantity) as items_count,
    o.total_amount,
    o.order_status
FROM orders o
LEFT JOIN customers c ON c.id = o.customer_id
LEFT JOIN order_items oi ON oi.order_id = o.id
WHERE o.order_date >= DATE_SUB(NOW(), INTERVAL 7 DAY)
GROUP BY o.id
ORDER BY o.order_date DESC;
```

---

## Data Integrity Rules

1. **Referential Integrity**
   - Orders can only reference existing customers and admins
   - Order items can only reference existing orders and menu items
   - Menu items must belong to existing categories

2. **Cascade Deletes**
   - Deleting a customer cascades to their orders and order items
   - Deleting a menu item cascades to associated order items
   - Deleting an order cascades to its order items

3. **Required Fields**
   - All primary keys are required and auto-increment
   - Foreign keys are required
   - Critical fields like name, price, quantity are required

4. **Timestamps**
   - Automatically set to CURRENT_TIMESTAMP on creation
   - Automatically updated on record modification

---

## Performance Considerations

### Indexing Strategy
- Primary keys indexed for fast lookups
- Foreign keys indexed for join performance
- Unique keys indexed for constraint checking

### Query Optimization
- Use prepared statements (prevents SQL injection)
- Use JOINs instead of multiple queries
- Filter by date ranges for large datasets
- Limit results with LIMIT clause

### Database Maintenance
- Regular backups (daily recommended)
- Monitor slow query logs
- Rebuild indexes periodically
- Archive old orders (recommended after 1-2 years)

---

## Sample Data Statistics

When importing `database/sample_data.sql`:
- **Admins:** 3 accounts
- **Categories:** 6 categories
- **Menu Items:** 21 items
- **Customers:** 10 customers
- **Orders:** 10 sample orders
- **Order Items:** 50 line items

Default admin credentials (password: `admin123`):
- `admin@restaurant.local`
- `john.manager@restaurant.local`
- `sarah.admin@restaurant.local`

---

## Backup and Recovery

### Backup Specific Tables
```bash
mysqldump -u root -p restaurant_management_db orders > orders_backup.sql
```

### Restore Specific Tables
```bash
mysql -u root -p restaurant_management_db < orders_backup.sql
```

---

## See Also

- [Setup Guide](setup_guide.md) - Installation and configuration
- [README.md](../README.md) - Project overview and features
- [database/schema.sql](../database/schema.sql) - Raw SQL schema
- [database/sample_data.sql](../database/sample_data.sql) - Sample data

