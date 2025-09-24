# 🍔 Wendy's Diner - Restaurant Management System

<div align="center">

![Wendy's Diner Logo](https://via.placeholder.com/200x100/FF6B6B/FFFFFF?text=Wendy%27s+Diner)

**A modern restaurant management system built with Laravel 12**

[![Laravel](https://img.shields.io/badge/Laravel-12.31.1-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.4.5-777BB4?style=for-the-badge&logo=php&logoColor=white)](https://php.net)
[![Livewire](https://img.shields.io/badge/Livewire-3.6.4-4E56A6?style=for-the-badge&logo=livewire&logoColor=white)](https://laravel-livewire.com)
[![Tailwind CSS](https://img.shields.io/badge/Tailwind_CSS-4.1.11-38B2AC?style=for-the-badge&logo=tailwind-css&logoColor=white)](https://tailwindcss.com)
[![MySQL](https://img.shields.io/badge/MySQL-8.0+-4479A1?style=for-the-badge&logo=mysql&logoColor=white)](https://mysql.com)

</div>

---

## 📖 About

Wendy's Diner is a comprehensive restaurant management system designed to streamline operations for modern dining establishments. Built with the latest Laravel 12 framework and cutting-edge technologies, it provides both customer-facing features and powerful administrative tools.

### ✨ Key Features

- 🍽️ **Menu Management** - Dynamic categories and product management
- 👨‍💼 **Admin Dashboard** - Comprehensive back-office functionality
- 🔐 **Authentication** - Secure login with two-factor authentication support
- 📱 **Responsive Design** - Mobile-first approach with Tailwind CSS
- ⚡ **Real-time Updates** - Powered by Livewire and Volt components
- 🎨 **Modern UI** - Beautiful interface with Flux UI components
- 🧪 **Testing Suite** - Comprehensive testing with Pest

---

## 🛠️ Tech Stack

### Backend
- **Laravel 12.31.1** - PHP web application framework
- **PHP 8.4.5** - Latest PHP version with modern features
- **MySQL** - Robust database management
- **Laravel Fortify** - Authentication scaffolding

### Frontend & UI
- **Livewire 3.6.4** - Dynamic interfaces without JavaScript complexity
- **Livewire Volt** - Single-file components for rapid development
- **Flux UI 2.4.0** - Beautiful, hand-crafted UI components
- **Tailwind CSS 4.1.11** - Utility-first CSS framework
- **Vite** - Modern build tool for assets

### Development & Testing
- **Pest 4.1.0** - Elegant testing framework with browser testing
- **Laravel Pint** - Code style fixer
- **Laravel Sail** - Docker development environment

---

## 🚀 Quick Start

### Prerequisites

- PHP 8.4 or higher
- Composer
- Node.js & NPM
- MySQL 8.0+
- Git

### Installation

1. **Clone the repository**
   ```bash
   git clone https://github.com/your-username/wendys-diner.git
   cd wendys-diner
   ```

2. **Install PHP dependencies**
   ```bash
   composer install
   ```

3. **Install JavaScript dependencies**
   ```bash
   npm install
   ```

4. **Environment setup**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

5. **Configure your database**
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=wendysdiner
   DB_USERNAME=your_username
   DB_PASSWORD=your_password
   ```

6. **Run migrations**
   ```bash
   php artisan migrate
   ```

7. **Build assets**
   ```bash
   npm run build
   # or for development
   npm run dev
   ```

8. **Start the development server**
   ```bash
   php artisan serve
   ```

Visit `http://localhost:8000` to view the application! 🎉

---

## 📊 Database Schema

The application uses a well-structured database with the following main entities:

- **Users** - Authentication and user management
- **Categories** - Menu category organization
- **Products** - Menu items with pricing and descriptions
- **Sessions** - User session management
- **Cache** - Application caching layer

### Entity Relationships
```
Categories (1) ──── (many) Products
Users ──── Sessions
```

---

## 🎯 Usage

### Customer Features
- 🏠 **Homepage** - Welcome and restaurant information
- 🍽️ **Menu Browsing** - View categorized menu items
- 📱 **Mobile Responsive** - Seamless experience on all devices

### Admin Features
- 📊 **Dashboard** - Overview of restaurant operations
- 📝 **Category Management** - Create, edit, and organize menu categories
- 🍕 **Product Management** - Manage menu items, pricing, and descriptions
- 👤 **User Profile** - Account settings and preferences
- 🔐 **Two-Factor Auth** - Enhanced security options

### Navigation
- `/` - Homepage
- `/dashboard` - Admin dashboard
- `/dashboard/categories` - Category management
- `/login` - Authentication
- `/settings/*` - User settings and preferences

---

## 🧪 Testing

The application includes comprehensive testing with Pest:

```bash
# Run all tests
php artisan test

# Run specific test file
php artisan test tests/Feature/ExampleTest.php

# Run with coverage
php artisan test --coverage
```

### Browser Testing
Pest 4 includes powerful browser testing capabilities:

```bash
# Run browser tests
php artisan test tests/Browser/
```

---

## 🔧 Development

### Code Style
The project uses Laravel Pint for consistent code formatting:

```bash
# Fix code style
vendor/bin/pint

# Check without fixing
vendor/bin/pint --test
```

### Asset Development
```bash
# Development with hot reload
npm run dev

# Production build
npm run build
```

### Database Operations
```bash
# Create migration
php artisan make:migration create_something_table

# Create model with migration and factory
php artisan make:model Something -mf

# Run seeders
php artisan db:seed
```

---

## 📁 Project Structure

```
wendys-diner/
├── app/
│   ├── Livewire/           # Livewire components
│   ├── Models/             # Eloquent models
│   └── ...
├── database/
│   ├── migrations/         # Database migrations
│   └── seeders/           # Database seeders
├── resources/
│   ├── views/             # Blade templates
│   │   └── livewire/      # Livewire views
│   └── js/                # JavaScript assets
├── routes/
│   └── web.php           # Web routes
├── tests/
│   ├── Feature/          # Feature tests
│   ├── Unit/            # Unit tests
│   └── Browser/         # Browser tests
└── public/              # Public assets
```

---

## 🚧 Development Roadmap

### ✅ Completed Features
- [x] Laravel 12 setup with modern stack
- [x] User authentication with Fortify
- [x] Category management system
- [x] Admin dashboard structure
- [x] Responsive UI with Flux components

### 🔄 In Progress
- [ ] Product management interface
- [ ] Enhanced admin dashboard with statistics
- [ ] Image upload functionality

### 📋 Planned Features
- [ ] Customer ordering system
- [ ] Google Maps integration
- [ ] Google Reviews display
- [ ] SEO optimization
- [ ] Performance enhancements
- [ ] Mobile app API

---

## 🤝 Contributing

We welcome contributions! Please follow these steps:

1. **Fork the repository**
2. **Create a feature branch**
   ```bash
   git checkout -b feature/amazing-feature
   ```
3. **Make your changes**
4. **Run tests and code style checks**
   ```bash
   php artisan test
   vendor/bin/pint
   ```
5. **Commit your changes**
   ```bash
   git commit -m "Add amazing feature"
   ```
6. **Push to your branch**
   ```bash
   git push origin feature/amazing-feature
   ```
7. **Open a Pull Request**

### Development Guidelines
- Follow PSR-12 coding standards
- Write tests for new features
- Update documentation as needed
- Use descriptive commit messages

---

## 📄 License

This project is open-sourced software licensed under the [MIT license](LICENSE).

---

## 📞 Support

Need help? Feel free to reach out:

- 📧 **Email**: support@wendysdiner.com
- 🐛 **Issues**: [GitHub Issues](https://github.com/your-username/wendys-diner/issues)
- 📚 **Documentation**: [Wiki](https://github.com/your-username/wendys-diner/wiki)

---

<div align="center">

**Made with ❤️ for the restaurant industry**

*Wendy's Diner - Serving digital excellence since 2025* 🍽️

</div>
