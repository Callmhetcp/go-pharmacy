Go Pharmacy 💊

A modern digital pharmacy platform designed to make it easier for customers to discover healthcare products, place orders, access pharmacy services, and connect with pharmacists online.

📌 About the Project

Go Pharmacy is a full-stack pharmacy web application being developed for a real-world pharmacy business.

The platform is designed to provide customers with a convenient online pharmacy experience while providing the pharmacy with the tools needed to manage products, orders, customers, prescriptions, and other business operations.

The project is being developed with a scalable architecture that will allow future integration with the pharmacy’s existing inventory and POS system.

⸻

🚀 Features

Customer Features

* Browse pharmacy products
* Search and filter products
* View product details
* Add products to cart
* Place orders
* Customer accounts and authentication
* Order management
* Health and wellness content
* Promotional products and hot deals

Pharmacy Features

* Product management
* Category management
* Order management
* Customer management
* Prescription management
* Administrative dashboard

Planned Features

* Online payment integration
* Prescription upload and pharmacist approval
* Prescription refill requests
* Location-based delivery pricing
* Free and discounted delivery options
* Expiry-based product discounts
* Health and wellness blog
* SEO optimization
* Product promotions and campaigns
* Integration with the existing pharmacy inventory/POS system
* Support for multiple pharmacy branches

⸻

🛠️ Technology Stack

Backend

* PHP
* Laravel 12
* Laravel REST API
* Eloquent ORM
* MySQL

Frontend

* Vue.js
* Tailwind CSS
* JavaScript / TypeScript

Development & Deployment

* Git
* GitHub
* Render
* Namecheap — Domain

⸻

🏗️ Architecture

Go Pharmacy uses a frontend/backend architecture where the Vue.js frontend communicates with the Laravel backend through APIs.

                    CUSTOMER
                       │
                       ▼
              Vue.js + Tailwind
                       │
                       │ REST API
                       ▼
                Laravel 12 API
                       │
                       ▼
                    MySQL

This separation allows the frontend and backend to be developed and maintained independently.

⸻

💊 Pharmacy Workflow

The platform is designed around a simple customer journey:

Browse Products
      ↓
View Product
      ↓
Add to Cart
      ↓
Checkout
      ↓
Payment
      ↓
Order Processing
      ↓
Pharmacy Fulfilment
      ↓
Delivery

For prescription-required products:

Customer Uploads Prescription
            ↓
     Pharmacist Review
            ↓
       Approval
            ↓
      Order Processing

⸻

🗄️ Database

The application uses MySQL as its primary database.

The database is designed to support areas such as:

* Users
* Products
* Categories
* Orders
* Order Items
* Payments
* Prescriptions
* Customers
* Inventory

The database structure will continue to evolve as additional pharmacy requirements are implemented.

⸻

🔌 API

The Laravel backend provides API endpoints that allow the Vue.js frontend to communicate with the application.

Example API responsibilities include:

Products
Categories
Authentication
Cart
Orders
Customers
Prescriptions
Payments

The API architecture also provides a foundation for future mobile applications or third-party integrations.

⸻

🔐 Security

Security is considered throughout the development of the platform.

The application uses or plans to use:

* Laravel authentication
* Password hashing
* Request validation
* Authorization
* Protected API endpoints
* Environment variables for sensitive credentials
* HTTPS in production
* Secure payment processing through the selected payment provider

⸻

🌍 Deployment

The project is being prepared for production deployment using Render.

Current planned structure:

              Go Pharmacy Domain
                      │
                      ▼
               Production Hosting
                      │
          ┌───────────┴───────────┐
          ▼                       ▼
     Vue Frontend            Laravel Backend
                                  │
                                  ▼
                                MySQL

⸻

🔄 Future Inventory Integration

One of the major future phases of Go Pharmacy is connecting the online platform to the pharmacy’s existing inventory/POS system.

The objective is to synchronize stock between the physical pharmacy and online store.

Physical Pharmacy
        │
        ▼
Existing Inventory/POS
        │
        ▼
Go Pharmacy Backend
        │
        ▼
Online Pharmacy

This will help reduce stock discrepancies between online and walk-in sales.

⸻

📈 Future Vision

Go Pharmacy is being developed with long-term scalability in mind.

The future vision is to create a complete digital pharmacy ecosystem connecting:

Customers → Pharmacists → Online Store → Inventory → POS → Physical Pharmacy

The architecture is also intended to support additional pharmacy branches as the business grows.

⸻

📂 Project Structure

A simplified Laravel project structure:

go-pharmacy/
│
├── app/
│   ├── Http/
│   ├── Models/
│   └── ...
│
├── database/
│   ├── migrations/
│   └── seeders/
│
├── resources/
│   ├── js/
│   └── views/
│
├── routes/
│   ├── api.php
│   └── web.php
│
├── public/
├── storage/
├── tests/
│
├── .env.example
├── composer.json
├── package.json
└── README.md

⸻

⚙️ Local Development

1. Clone the repository

git clone <repository-url>
cd go-pharmacy

2. Install PHP dependencies

composer install

3. Install frontend dependencies

npm install

4. Create environment file

cp .env.example .env

Configure your database and application settings inside .env.

5. Generate Laravel application key

php artisan key:generate

6. Run database migrations

php artisan migrate

7. Start Laravel

php artisan serve

8. Start the frontend development server

npm run dev

⸻

🚧 Project Status

Status: In Development

The project is currently being developed in phases.

Phase 1

Core online pharmacy MVP:

* Website
* Products
* Categories
* Customer accounts
* Cart
* Orders
* Prescription workflow
* Payment integration
* Delivery system
* Admin functionality
* Health/wellness content

Phase 2

Advanced pharmacy integration:

* Existing inventory/POS integration
* Real-time stock synchronization
* Advanced pharmacy operations
* Multiple branches
* Additional automation

⸻

👨‍💻 Developer

Obinna Wisdom Ojo

Full-Stack Developer → Backend-focused → PHP/Laravel

Focused on building practical business systems using Laravel, APIs, databases, and modern frontend technologies.

⸻

📄 License

This project is proprietary software developed for Go Pharmacy.

Unauthorized copying, redistribution, or commercial use is not permitted without permission from the project owner.