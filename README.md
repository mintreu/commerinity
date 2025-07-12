# Commerinity

**Commerinity** is a modular, scalable platform that combines **Multi-Level Marketing (MLM)**, **eCommerce**, **Marketing Automation**, and **Content Management** into a unified system.

> 💡 _"Where Commerce Meets Community"_

---

## 📁 Project Structure

```
commerinity/
├── api/              # Laravel REST API + FilamentPHP Admin
├── client/           # Frontend (Nuxt or Next.js)
├── marketing/        # Campaign logic (email, SMS, automation)
├── docs/             # Architecture diagrams, API docs, DB schemas
├── .env.example      # Sample environment config
├── README.md         # Project documentation
├── LICENSE           # License file
└── composer.json / package.json / etc.
```

---

## 🔧 Tech Stack

| Layer        | Technology                                 |
|--------------|---------------------------------------------|
| Backend      | Laravel (API) + FilamentPHP (Admin Panel)   |
| Frontend     | Nuxt 3 (Vue) or Next.js (React)             |
| Database     | MySQL / PostgreSQL                          |
| Auth         | Laravel Sanctum / Passport                  |
| CMS          | FilamentPHP                                 |
| Marketing    | Email, SMS, Advertising, Automation         |
| Hosting      | Cloud Hosting, VPS, or Managed Deployments  |


---

## 🚀 Features

- 🧩 Modular Laravel API with Laravel Sanctum / Passport
- 🛍️ Product, Order, Cart, and Payment APIs
- 🌱 MLM Logic: Referral Trees, Commission Systems
- 🧑‍💼 Admin Panel built with **FilamentPHP**
- 📣 Marketing Automation (Email/SMS Campaigns)
- 🧑 Frontend in Nuxt or Next.js (SEO + SSR support)
- 🛡️ Role-based Access Control
- 📊 Analytics Dashboard and Reporting

---

## 📦 Getting Started

### 🔧 Backend (Laravel + Filament)

```bash
# Navigate to backend
cd api

# Install dependencies
composer install

# Copy and edit environment config
cp .env.example .env

# Generate app key
php artisan key:generate

# Run migrations
php artisan migrate

# Serve the app
php artisan serve
```

### 🌐 Frontend (Nuxt or Next.js)

```bash
# Navigate to frontend
cd ../client

# Install dependencies
npm install

# Run development server
npm run dev
```

---

## 🧪 Development Workflow

- `api/` – Laravel API with MLM logic, FilamentPHP for admin UI
- `client/` – Nuxt or Next.js frontend for users/vendors
- `marketing/` – Campaign management (SMS/Email automations)
- `docs/` – Architecture diagrams, workflows, API docs

---

## 📄 License

This project is licensed under the [MIT License](LICENSE).

---

## 🤝 Contributing

We welcome contributions! To get started:

```bash
# 1. Fork the repository
# 2. Create a feature branch
git checkout -b feature/your-feature

# 3. Commit your changes
git commit -am "Add your feature"

# 4. Push the branch
git push origin feature/your-feature

# 5. Create a Pull Request
```

---

## 📫 Contact

For feedback, collaboration, or inquiries:

- 📧 contactus@mintreu.com  
- 🌐 mintreu.com

---

## ⭐ Support

If you find this project useful, please consider starring the repo and sharing it!

