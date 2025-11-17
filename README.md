# 🎉 Event Management System

A beautiful, modern event management platform built with PHP, MySQL, and Tailwind CSS.

---

## 🚀 Quick Start

### 1. **Setup Database**
```sql
-- Create database
CREATE DATABASE event_management;

-- Import schema (use phpMyAdmin or MySQL CLI)
-- Tables: users, orders, order_notifications, organizer_pages, organizer_portfolio
```

### 2. **Configure Database**
Edit `db.php`:
```php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "event_management";
```

### 3. **Access Application**
- **Home**: http://localhost/project_event_management/
- **Login**: http://localhost/project_event_management/login.php
- **Sign Up**: http://localhost/project_event_management/role_select.php

---

## 👥 User Roles

### 🏢 **Organizer**
- Create and manage portfolio
- View orders from customers
- Approve/reject orders
- Manage event details
- Dashboard: `organizer_dashboard.php`

### 👤 **Customer**
- Browse organizers
- Place orders
- View order status
- Manage profile
- Dashboard: `customer_profile_dashboard.php`

### 👨‍💼 **Admin**
- Manage all users
- Remove organizers/customers
- View system statistics
- Monitor orders
- Dashboard: `admin_dashboard.php`

---

## 📁 Project Structure

```
project_event_management/
├── index.php                          # Home page
├── login.php                          # Login page
├── logout.php                         # Logout
├── role_select.php                    # Sign up role selection
├── 
├── 📊 CUSTOMER PAGES
├── customer_profile_dashboard.php     # Customer dashboard
├── customer_profile_edit.php          # Edit customer profile
├── customer_dashboard.php             # Legacy customer dashboard
├── place_order.php                    # Place order
├── 
├── 🏢 ORGANIZER PAGES
├── organizer_dashboard.php            # Organizer dashboard
├── organizer_view.php                 # View organizer profile
├── organizer_profile_view.php         # Customer view of organizer
├── portfolio_add.php                  # Add portfolio item
├── portfolio_edit.php                 # Edit portfolio item
├── portfolio_manage.php               # Manage portfolio
├── 
├── 👨‍💼 ADMIN PAGES
├── admin_dashboard.php                # Admin dashboard
├── 
├── 🔔 NOTIFICATIONS
├── get_notifications.php              # Notification API
├── notification_component.php         # Notification component
├── notifications.css                  # Notification styles
├── notifications.js                   # Notification script
├── 
├── 📋 ORDERS
├── orders.php                         # Orders management
├── 
├── 🔐 AUTHENTICATION
├── check_session.php                  # Session validation
├── forgot_password.php                # Forgot password
├── reset_password.php                 # Reset password
├── 
├── 🎨 STYLES
├── MainStyle.css                      # Main stylesheet
├── admin_dashboard.css                # Admin dashboard styles
├── organizer_dashboard.css            # Organizer dashboard styles
├── forgot_password.css                # Forgot password styles
├── reset_password.css                 # Reset password styles
├── role_select.css                    # Role select styles
├── organizer_view.css                 # Organizer view styles
├── list.css                           # Organizer list styles
├── notifications.css                  # Notification styles
├── 
├── ⚙️ SCRIPTS
├── Mainpage.js                        # Main page script
├── notifications.js                   # Notification script
├── reset_password.js                  # Reset password script
├── role_select.js                     # Role select script
├── organizer_view.js                  # Organizer view script
├── list.js                            # Organizer list script
├── 
├── 🗄️ DATABASE
├── db.php                             # Database connection
├── 
├── 📁 FOLDERS
├── img/                               # Images
├── uploads/                           # User uploads
│   ├── profile_pictures/              # Customer profile pictures
│   └── portfolio/                     # Portfolio images/videos
└── video/                             # Videos
```

---

## 🔑 Key Features

### ✨ **Customer Features**
- ✅ Beautiful profile dashboard
- ✅ Upload profile picture
- ✅ Browse organizers by event type
- ✅ Place orders
- ✅ Track order status
- ✅ Real-time notifications
- ✅ View organizer portfolios

### 🎯 **Organizer Features**
- ✅ Professional dashboard
- ✅ Upload portfolio items
- ✅ Add images and videos
- ✅ Manage portfolio
- ✅ View customer orders
- ✅ Approve/reject orders
- ✅ Featured portfolio items

### 🛡️ **Admin Features**
- ✅ System dashboard
- ✅ User management
- ✅ Remove users
- ✅ Filter by role
- ✅ View statistics
- ✅ Monitor orders

---

## 🔐 Security

✅ Session validation
✅ Role-based access control
✅ SQL injection prevention (prepared statements)
✅ XSS prevention (htmlspecialchars)
✅ Password hashing
✅ CSRF protection
✅ File upload validation

---

## 🎨 Design

- **Framework**: Tailwind CSS
- **Color Scheme**: Purple/Blue gradients
- **Responsive**: Mobile, tablet, desktop
- **Modern**: Clean, professional UI
- **Accessible**: WCAG compliant

---

## 📱 Responsive Design

- ✅ **Desktop** (1200px+): Full layout
- ✅ **Tablet** (768px-1199px): Adjusted layout
- ✅ **Mobile** (<768px): Optimized layout

---

## 🗄️ Database Tables

### users
- user_id, name, email, phone, password, role, profile_pic, dob, location

### orders
- order_id, customer_id, organizer_id, event_details, event_date, status

### order_notifications
- id, order_id, customer_id, status, is_read, created_at

### organizer_pages
- id, user_id, page_title, description, rating, profile_pic

### organizer_portfolio
- id, organizer_id, title, description, event_type, event_date, client_name, location, images, videos, featured, status, created_at

---

## 🚀 Deployment

### Requirements
- PHP 7.4+
- MySQL 5.7+
- Apache/Nginx
- Tailwind CSS CDN

### Steps
1. Upload files to web server
2. Create database
3. Configure `db.php`
4. Set folder permissions
5. Access application

---

## 📞 Support

For issues or questions:
1. Check browser console for errors
2. Review database connection
3. Verify file permissions
4. Check PHP error logs

---

## 📝 License

This project is for educational purposes.

---

## ✅ Status

**Production Ready** ✨

All features tested and working perfectly!

---

**Last Updated**: November 16, 2025
**Version**: 1.0
**Status**: Complete
