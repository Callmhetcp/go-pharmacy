<p align="center">
    <a href="https://laravel.com" target="_blank">
        <img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo">
    </a>
</p>

# 💊 Go Pharmacy

<p align="center">
    <strong>A modern pharmacy management and e-commerce application built with Laravel 12, Vue.js, Inertia.js, Tailwind CSS, MySQL, and REST APIs.</strong>
</p>

<p align="center">
    <a href="https://github.com/Callmhetcp4/go-pharmacy">
        <img src="https://img.shields.io/badge/GitHub-Callmhetcp4%2Fgo--pharmacy-black?logo=github" alt="GitHub Repository">
    </a>
    <img src="https://img.shields.io/badge/Laravel-12-red?logo=laravel" alt="Laravel 12">
    <img src="https://img.shields.io/badge/Vue.js-3-42b883?logo=vue.js" alt="Vue.js 3">
    <img src="https://img.shields.io/badge/Inertia.js-2-9553E9?logo=inertia" alt="Inertia.js">
    <img src="https://img.shields.io/badge/Tailwind%20CSS-4-06B6D4?logo=tailwindcss" alt="Tailwind CSS">
    <img src="https://img.shields.io/badge/MySQL-Database-blue?logo=mysql" alt="MySQL">
</p>

---

## 📌 About the Project

**Go Pharmacy** is a pharmacy management and e-commerce application built with Laravel.

The application is designed to provide a single system for managing pharmacy products, inventory, purchases, customers, orders, prescriptions, and online shopping.

The project combines a Laravel web application with a REST API so that the same backend can support the web storefront, administrative operations, and future external clients such as mobile applications.

The application follows the Go Pharmacy brand:

**GOOD HEALTH. MADE SIMPLE.**

---

## ✨ Features

### 🔐 Authentication

* Customer registration
* Customer login
* Customer logout
* Laravel web/session authentication
* Google authentication
* Google OAuth integration
* API authentication with Laravel Sanctum
* Authenticated user profile endpoint
* Protected customer routes
* Protected admin routes
* Admin authorization middleware

### 👤 Customer Management

* Customer accounts
* Customer profiles
* Customer orders
* Customer prescriptions
* Customer addresses
* Customer order history
* Customer activity management through the admin area

### 💊 Product Management

* Product creation
* Product editing
* Product deletion
* Product categories
* Product SKU
* Product pricing
* Product images
* Product activation/deactivation
* Featured products
* Prescription-required products
* Base units and selling units
* Units per selling unit
* Partial-sale configuration

### 📦 Inventory Management

The application uses a centralized inventory system designed to keep physical, POS, and online stock based on the same inventory quantity.

* Inventory quantity tracking
* Available quantity
* Reserved quantity
* Base-unit inventory
* Stock reservations
* Stock deductions
* Inventory transactions
* Purchase stock
* Online sales
* Expired stock handling
* Expired stock disposal
* Purchase returns
* Inventory transaction history

Selling quantities are converted into base quantities using the product's configured selling-unit relationship.

For example:

```text
1 pack = 10 tablets

2 packs sold
2 × 10 = 20 tablets deducted
```

### 🛒 Shopping Cart

* Add products to cart
* Update cart quantities
* Remove products from cart
* Product quantity validation
* Inventory-aware cart operations
* Selling-unit quantity handling

### 🧾 Orders

* Customer order creation
* Order items
* Order totals
* Order status tracking
* Payment status tracking
* Stock reservation
* Stock fulfillment
* Inventory deduction after fulfillment
* Order history
* Admin order management

The system prevents stock from being deducted during an unpaid order reservation and handles fulfillment according to the order's payment and fulfillment state.

### 💳 Payments

The application currently includes payment status handling within the order workflow.

Payment gateway integration is planned and has not yet been initialized for production.

A payment gateway abstraction can be added later without changing the core order and inventory structure.

### 📋 Prescriptions

* Prescription records
* Prescription-required products
* Customer prescription management
* Admin prescription review
* Prescription-aware order workflow

### 🏪 Pharmacy Administration

The admin dashboard provides management functionality for:

* Products
* Categories
* Inventory
* Suppliers
* Purchases
* Orders
* Prescriptions
* Customers
* Reports
* POS
* Settings

### 🧑‍⚕️ AI Pharmacy Assistant

Go Pharmacy includes an AI-assisted customer support workflow.

The AI assistant can:

* Answer customer questions
* Search pharmacy products
* Understand product-related questions
* Filter products by price
* Detect prescription requirements
* Provide product context
* Maintain customer AI conversations
* Hand conversations over to a pharmacist

Routine product filtering can be handled locally to reduce unnecessary AI API usage.

### 👩‍⚕️ Pharmacist Conversations

Customers can request assistance from a pharmacist when the AI assistant is not sufficient.

The pharmacist workflow supports:

* Customer-to-pharmacist handoff
* Pharmacist replies
* Conversation assignment
* Conversation status tracking
* Pharmacist conversation history
* Ending a pharmacist conversation
* Returning the customer to the AI assistant

Conversation statuses include:

```text
active
waiting_for_pharmacist
with_pharmacist
expired
```

### ⏱️ AI Conversation Expiry

AI conversations automatically expire after 24 hours of inactivity.

The workflow is:

```text
Active Conversation
        │
        ▼
24 Hours Without Activity
        │
        ▼
Expired Conversation
        │
        ▼
Customer Sends New Message
        │
        ▼
New Active Conversation
```

Expired conversations remain in the database for historical records.

### 🔌 REST API

The application provides versioned REST API endpoints under:

```text
/api/v1
```

API functionality includes:

* Authentication
* Google authentication
* Customer profile
* Products
* Categories
* AI assistant
* AI conversations
* Admin AI conversations

The API is designed to support future frontend clients and mobile applications.

---

## 🛠️ Technology Stack

### Backend

* Laravel 12
* PHP 8.2+
* Laravel Sanctum
* Laravel Socialite
* Eloquent ORM
* MySQL / MariaDB
* REST API

### Frontend

* Vue.js 3
* Inertia.js
* Tailwind CSS 4
* Vite
* JavaScript

### AI

* Google Gemini API
* Custom Go Pharmacy AI service layer
* Local product-query filtering

### Development Tools

* XAMPP
* MySQL
* phpMyAdmin
* Visual Studio Code
* Git
* GitHub

---

## 🏗️ Application Architecture

Go Pharmacy currently uses a Laravel full-stack architecture with Vue.js and Inertia.js.

```text
Vue.js
   │
   ▼
Inertia.js
   │
   ▼
Laravel Application
   │
   ├── Web Controllers
   ├── API Controllers
   ├── Services
   ├── Models
   ├── Middleware
   └── Validation
   │
   ▼
Eloquent ORM
   │
   ▼
MySQL Database
```

The REST API is available separately for API consumers and future applications.

```text
Mobile / External Client
          │
          ▼
     REST API /api/v1
          │
          ▼
   Laravel Application
          │
          ▼
       MySQL
```

---

## 🗄️ Database

The application uses MySQL for persistent data storage.

Major areas of the database include:

```text
Users
Products
Categories
Inventory
Inventory Transactions
Purchases
Purchase Items
Suppliers
Orders
Order Items
Prescriptions
Addresses
AI Conversations
AI Messages
```

The application uses Laravel migrations and Eloquent relationships to manage the database structure.

---

## 📦 Inventory Workflow

Go Pharmacy uses a centralized inventory model.

```text
Purchase
   │
   ▼
Inventory
   │
   ├── Physical Stock
   ├── POS Sales
   └── Online Sales
```

Inventory quantities are stored using base units.

For products sold in packs:

```text
Product:
Paracetamol 500mg

Base Unit:
Tablet

Selling Unit:
Pack

Units Per Selling Unit:
10
```

If a customer purchases:

```text
2 packs
```

The inventory calculation becomes:

```text
2 × 10 = 20 tablets
```

This allows the same inventory system to support different selling units without maintaining separate stock records.

---

## 🔑 API

API routes are versioned under:

```text
/api/v1
```

### Authentication

```text
POST   /api/v1/auth/register
POST   /api/v1/auth/login
POST   /api/v1/auth/google
POST   /api/v1/auth/logout
GET    /api/v1/auth/me
```

### Products

```text
GET    /api/v1/products
GET    /api/v1/products/{product}
```

### Categories

```text
GET    /api/v1/categories
```

### AI Assistant

```text
GET    /api/v1/ai/conversation
POST   /api/v1/ai/chat
```

### Admin AI Conversations

```text
GET    /api/v1/admin/ai/conversations
GET    /api/v1/admin/ai/conversations/{conversation}
POST   /api/v1/admin/ai/conversations/{conversation}/messages
```

> API endpoints may evolve as development continues.

---

## 🚀 Installation

Clone the repository:

```bash
git clone https://github.com/Callmhetcp4/go-pharmacy.git
```

Move into the project directory:

```bash
cd go-pharmacy
```

Install PHP dependencies:

```bash
composer install
```

Install frontend dependencies:

```bash
npm install
```

Create the environment file.

### Windows

```powershell
Copy-Item .env.example .env
```

### macOS / Linux

```bash
cp .env.example .env
```

Generate the application key:

```bash
php artisan key:generate
```

Configure the database in `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=go_pharmacy
DB_USERNAME=root
DB_PASSWORD=
```

Run migrations:

```bash
php artisan migrate
```

Create the storage link:

```bash
php artisan storage:link
```

Start the Laravel development server:

```bash
php artisan serve
```

Start the Vite development server:

```bash
npm run dev
```

---

## ⚙️ Environment Configuration

The following environment variables may be required:

```env
APP_NAME="Go Pharmacy"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=go_pharmacy
DB_USERNAME=root
DB_PASSWORD=

GOOGLE_CLIENT_ID=
GOOGLE_CLIENT_SECRET=
GOOGLE_REDIRECT_URI=

GEMINI_API_KEY=
GEMINI_MODEL=
```

Do not commit your `.env` file or private credentials to GitHub.

---

## 🧪 Testing

Run the Laravel test suite with:

```bash
php artisan test
```

The project includes tests covering important pharmacy application functionality, including inventory and order-related workflows.

---

## 📁 Project Structure

Important application directories include:

```text
app/
├── Http/
│   ├── Controllers/
│   │   ├── Admin/
│   │   └── Api/
│   ├── Middleware/
│   └── Requests/
├── Models/
├── Services/
└── ...

database/
├── migrations/
└── seeders/

resources/
├── js/
│   ├── Components/
│   ├── Layouts/
│   └── Pages/
└── ...

routes/
├── api.php
└── web.php

tests/
├── Feature/
└── Unit/
```

---

## 🔒 Security

The application uses Laravel's built-in security features together with application-specific authorization rules.

Security considerations include:

* Laravel authentication
* Laravel Sanctum
* Google OAuth authentication
* Protected admin routes
* Admin authorization middleware
* API authentication
* CSRF protection
* Request validation
* Password hashing
* Eloquent relationships
* Environment-based secrets
* Order and inventory authorization
* Conversation ownership and authorization

Private customer and administrative operations are protected according to the authenticated user's permissions.

---

## 📚 Learning Objectives

Go Pharmacy is also a practical learning project designed to strengthen real-world Laravel development skills.

The project covers:

* Laravel application architecture
* REST API development
* Authentication
* Google OAuth
* Authorization
* Laravel Sanctum
* Laravel Socialite
* Eloquent relationships
* Database migrations
* Inventory management
* Order management
* Stock reservation
* API design
* Vue.js
* Inertia.js
* Tailwind CSS
* AI API integration
* Git and GitHub
* Testing
* Application deployment

---

## 🔮 Future Improvements

Planned improvements include:

* Production payment gateway integration
* POS and online inventory synchronization improvements
* Multiple pharmacy branches
* Advanced delivery management
* Improved reporting
* Pharmacist and staff roles
* More granular admin permissions
* Mobile application support
* Additional AI capabilities
* Production deployment improvements

These features are planned improvements and are not represented as completed functionality.

---

## 🤝 Contributing

Go Pharmacy is currently a personal development and portfolio project.

Suggestions, improvements, and constructive feedback are welcome.

---

## 📄 License

This project is proprietary software.

The source code is published for portfolio and demonstration purposes. Unauthorized commercial use, redistribution, or modification is not permitted without permission from the project owner.

---

## 👨‍💻 Developer

**Obinna Wisdom Ojo**

* GitHub: [@Callmhetcp4](https://github.com/Callmhetcp)
* LinkedIn: [obinnaojo](https://www.linkedin.com/in/obinnaojo/)

---

## 🏥 Go Pharmacy

**GOOD HEALTH. MADE SIMPLE.**
