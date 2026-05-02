# DAINIKI – Customer Ledger Management System

## 📌 Overview

> 🎯 **The Problem:** Small and medium-sized retail shops still rely on traditional paper-based ledgers and notebooks to manage financial and inventory records. This manual approach is time-consuming, prone to human error (incorrect balance calculations, data duplication), and inefficient as transaction volumes grow. Tracking customer dues, managing stock levels, and retrieving historical transaction data becomes unreliable and labor-intensive.

> ✅ **The Solution:** DAINIKI is a web-based application that automates ledger and stock management processes. It provides an integrated platform to manage customers, buyers, products, credit/debit transactions, and inventory in real-time. The system replaces paper errors with automated calculations, real-time stock updates, and a user-friendly dashboard, enhancing accuracy, security, and operational efficiency for retail shop owners.

## ✨ Features

- **Customer & Buyer Management:** Maintain separate digital records for all customers and buyers with full CRUD operations.
- **Product & Stock Management:** Manage product details (code, price, stock quantity) with automatic stock updates after every transaction.
- **Transaction Recording:** Record credit and debit transactions with date, product, quantity, and amount details. Balances are calculated automatically.
- **Smart Dashboard:** Get a quick overview of business health showing total credit, total debit, net balance, stock count, and recent transactions.
- **Monthly Ledger:** Generate and filter transaction history by month and party name for easy financial analysis.
- **User Authentication:** Secure login system to protect financial data.

## 🛠️ Tech Stack

| Layer | Technology |
|-------|------------|
| **Frontend** | HTML5, CSS3, JavaScript |
| **Backend** | PHP |
| **Database** | MySQL |
| **Server** | XAMPP / Apache |
| **Development Tools** | Visual Studio Code, phpMyAdmin |

## 📁 Project Structure

| Folder / File | Description |
| :--- | :--- |
| `assets/` | Contains static assets (CSS, images, JS) |
| `assets/css/style.css` | Custom styling for the application |
| `assets/js/script.js` | Frontend JavaScript logic |
| `auth/` | Authentication related files |
| `auth/login.php` | User login page |
| `auth/logout.php` | Logout handler |
| `includes/` | Core configuration files |
| `includes/config.php` | Main configuration file |
| `includes/db.php` | Database connection setup |
| `includes/functions.php` | Reusable helper functions |
| `pages/` | Main application modules |
| `pages/customers.php` | Manage customer records |
| `pages/buyer.php` | Manage buyer records |
| `pages/stock.php` | Product and inventory management |
| `pages/transaction.php` | Record and process transactions |
| `pages/ledger.php` | Monthly ledger view |
| `pages/dashboard.php` | Dashboard with business summary |
| `index.php` | Application entry point |

## 🔧 Future Improvements

- **Multi-User Role Management:** Add support for admin and staff roles with different permission levels.
- **Online Payment & Invoicing:** Integrate payment gateways and generate digital invoices for transactions.
- **Graphical Reports:** Implement interactive charts for sales trends, profit analysis, and product performance.
- **Backup & Restore:** Add functionality to backup and restore the database directly from the application.
- **Cloud Deployment:** Deploy the system to a cloud server for remote access from anywhere.
