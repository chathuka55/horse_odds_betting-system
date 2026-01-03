# 🐴 Horse Racing Platform

<div align="center">

**A comprehensive, AI-powered horse racing predictions and betting platform**

[![PHP Version](https://img.shields.io/badge/PHP-8.0%2B-blue.svg)](https://www.php.net/)
[![MySQL](https://img.shields.io/badge/MySQL-5.7%2B-orange.svg)](https://www.mysql.com/)
[![License](https://img.shields.io/badge/License-Proprietary-red.svg)](LICENSE)

*Professional horse racing analytics platform with AI predictions, live betting, and comprehensive race management*

</div>

---

## 📖 Table of Contents

- [Overview](#-overview)
- [Features](#-features)
- [Tech Stack](#-tech-stack)
- [Requirements](#-requirements)
- [Installation](#-installation)
- [Configuration](#-configuration)
- [Usage](#-usage)
- [Project Structure](#-project-structure)
- [Database Schema](#-database-schema)
- [API Documentation](#-api-documentation)
- [AI Predictions](#-ai-predictions)
- [Betting System](#-betting-system)
- [Admin Panel](#-admin-panel)
- [Security](#-security)
- [Development](#-development)
- [Contributing](#-contributing)
- [License](#-license)
- [Support](#-support)

---

## 🎯 Overview

**Horse Racing Platform** is a full-featured, enterprise-grade platform for horse racing enthusiasts, bettors, and administrators. The system provides AI-powered predictions, comprehensive race management, live betting capabilities, and extensive analytics to help users make informed decisions.

### Key Highlights

- 🤖 **AI-Powered Predictions** - Machine learning algorithms analyze thousands of data points
- 💰 **Betting System** - Complete betting platform with odds tracking and payout management
- 📊 **Real-time Analytics** - Live race updates, odds tracking, and performance metrics
- 🎨 **Modern UI** - Beautiful, responsive design built with Tailwind CSS
- 🔐 **Secure & Scalable** - Enterprise-grade security with role-based access control
- 🔌 **API Integration** - Support for external racing data APIs (TheRacingAPI, Betfair, SportsRadar)

---

## 🚀 Features

### Core Features

- ✅ **Centralized Model Layer** - All CRUD operations via model classes (no raw SQL in endpoints)
- ✅ **Admin Dashboard** - Comprehensive management interface for all platform entities
- ✅ **Race Management** - Create, edit, and manage races with full details
- ✅ **Horse Registry** - Complete horse database with form, stats, and images
- ✅ **Entry Management** - Add entries to races with jockeys, trainers, and odds
- ✅ **Results Tracking** - Record and display race results with detailed statistics
- ✅ **Track Management** - Manage racing venues and track information
- ✅ **User Management** - Multi-role user system (Admin, Editor, User)
- ✅ **Credit System** - Virtual credit system for betting transactions
- ✅ **Activity Logging** - Complete audit trail of all CRUD operations
- ✅ **Soft Deletes** - Safe deletion with data preservation and audit logging
- ✅ **MockDB Fallback** - Works without MySQL for local testing/demo
- ✅ **Responsive Design** - Works seamlessly on desktop, tablet, and mobile
- ✅ **Multi-API Support** - Integrate with multiple external racing data providers

### Advanced Features

- 🤖 **AI Prediction Engine** - Win probability calculations with confidence levels
- 💸 **Betting Platform** - Place bets, track odds, manage payouts
- 📈 **Form Analysis** - Comprehensive form guides and historical tracking
- 🌐 **RESTful API** - Complete API for external integrations
- 📱 **Mobile-First** - Optimized for mobile devices
- 🔄 **Real-time Updates** - Live odds and race status updates
- 📊 **Analytics Dashboard** - Statistics and insights for users and admins
- 🔐 **CSRF Protection** - Cross-site request forgery protection
- 📝 **Audit Trail** - Complete activity logging for compliance
- 🎯 **Search & Filter** - Advanced search and filtering capabilities

---

## 🛠 Tech Stack

### Backend
- **PHP** 8.0+ (Object-oriented, PDO for database operations)
- **MySQL** 5.7+ / **MariaDB** 10.4+ (Relational database)
- **PDO** (Database abstraction layer)

### Frontend
- **HTML5** / **CSS3**
- **Tailwind CSS** (Utility-first CSS framework)
- **JavaScript** (Vanilla JS for interactivity)
- **Font Awesome** (Icons)

### Architecture
- **MVC Pattern** (Model-View-Controller structure)
- **RESTful API** (REST principles)
- **Session Management** (PHP sessions for authentication)
- **JSON Data Fallback** (Mock data when database unavailable)

---

## 📋 Requirements

### Server Requirements
- **PHP** 8.0 or higher
- **MySQL** 5.7+ or **MariaDB** 10.4+
- **Apache** or **Nginx** web server
- **mod_rewrite** enabled (for clean URLs)
- **PDO** PHP extension
- **GD** or **Imagick** extension (for image processing)
- **JSON** PHP extension
- **cURL** PHP extension (for API integrations)

### Optional Requirements
- **Composer** (for dependency management)
- **Git** (for version control)

---

## 🔧 Installation

### Step 1: Clone the Repository

```bash
git clone https://github.com/chathuka55/horse_odds_betting-system.git
cd horse_odds_betting-system
```

### Step 2: Database Setup

1. Create a MySQL database:

```sql
CREATE DATABASE horse_racing_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

2. Import the database schema:

```bash
mysql -u root -p horse_racing_db < sql/database.sql
```

Or using MySQL command line:

```bash
mysql -u root -p horse_racing_db < sql/database.sql
```

### Step 3: Configure Database Connection

Edit `includes/config.php` and update the database credentials:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'horse_racing_db');
define('DB_USER', 'root');
define('DB_PASS', 'your_password');
```

### Step 4: Set File Permissions

**Linux/Mac:**
```bash
chmod 755 assets/uploads/
chmod 755 assets/uploads/horses/
chmod 755 assets/uploads/tracks/
chmod 755 data/
```

**Windows:**
Ensure the uploads directories have write permissions for the web server user.

### Step 5: Configure Site Settings

Edit `includes/config.php` to customize:

```php
define('SITE_NAME', 'Your Site Name');
define('SITE_TAGLINE', 'Your Tagline');
define('SITE_URL', 'http://localhost/horse-racing-platform');
define('DEBUG_MODE', true); // Set to false in production
```

### Step 6: Web Server Configuration

#### Apache (.htaccess)

Ensure `.htaccess` files are enabled and `mod_rewrite` is installed.

#### Nginx

Add the following to your server block:

```nginx
location / {
    try_files $uri $uri/ /index.php?$query_string;
}
```

### Step 7: Access the Application

1. Open your browser and navigate to: `http://localhost/horse-racing-platform`
2. Login with default admin credentials (see [Default Credentials](#default-credentials))

---

## ⚙️ Configuration

### Environment Configuration

All configuration is done in `includes/config.php`. Key settings include:

#### Database Configuration
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'horse_racing_db');
define('DB_USER', 'root');
define('DB_PASS', '');
```

#### Site Configuration
```php
define('SITE_NAME', 'RacingPro Analytics');
define('SITE_TAGLINE', 'AI-Powered Racing Predictions');
define('SITE_URL', 'http://localhost/horse-racing-platform');
```

#### Security Configuration
```php
define('HASH_COST', 12);
define('SESSION_LIFETIME', 86400); // 24 hours
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOCKOUT_TIME', 900); // 15 minutes
```

#### API Configuration
```php
define('RACING_API_KEY', 'your_api_key_here');
define('RACING_API_URL', 'https://api.theracingapi.com/v1');
```

### External API Setup

The platform supports multiple external API providers:

1. **TheRacingAPI** - https://api.theracingapi.com
2. **BetfairAPI** - https://api.betfair.com
3. **SportsRadar** - https://api.sportradar.com

Configure APIs in the Admin Panel under **API Settings** or directly in the `api_settings` database table.

---

## 💻 Usage

### Default Credentials

**Admin Account:**
- Username: `admin`
- Email: `admin@racingpro.com`
- Password: `password`

> ⚠️ **Important**: Change the default password immediately after first login!

### Creating a New Admin User

```php
<?php
require_once 'includes/config.php';

$password = password_hash('your_secure_password', PASSWORD_DEFAULT);

$stmt = $db->prepare("
    INSERT INTO users (username, email, password, role, is_active, email_verified) 
    VALUES (?, ?, ?, 'admin', 1, 1)
");

$stmt->execute(['newadmin', 'admin@example.com', $password]);
?>
```

### Generating Predictions

**Via CLI:**
```bash
php scripts/migrate_predictions.php
php scripts/generate_predictions_all.php
```

**Via Admin Panel:**
1. Log in as admin
2. Navigate to Predictions page
3. Click "Generate Predictions" for specific races

**Via Cron Job (Linux):**
```cron
0 */2 * * * cd /path/to/project && php scripts/generate_predictions_all.php
```

**Via Task Scheduler (Windows):**
Create a scheduled task to run:
```
php.exe C:\path\to\project\scripts\generate_predictions_all.php
```

### User Roles

1. **Admin** - Full access to all features and admin panel
2. **Editor** - Can create and edit races, entries, and results
3. **User** - Can view races, predictions, and place bets

---

## 📁 Project Structure

```
horse-racing-platform/
├── admin/                      # Admin panel
│   ├── index.php              # Admin dashboard
│   ├── races.php              # Race management
│   ├── horses.php             # Horse management
│   ├── users.php              # User management
│   ├── results.php            # Results management
│   ├── tracks.php             # Track management
│   ├── settings.php           # Site settings
│   ├── api-settings.php       # API configuration
│   ├── ajax/                  # AJAX endpoints
│   │   ├── save-race.php
│   │   ├── save-horse.php
│   │   ├── delete-item.php
│   │   ├── generate-predictions.php
│   │   └── ...
│   └── components/            # Admin components
│       ├── header.php
│       ├── sidebar.php
│       └── footer.php
│
├── api/                       # RESTful API endpoints
│   ├── races.php             # Race API
│   ├── horses.php            # Horse API
│   ├── results.php           # Results API
│   ├── external-api.php      # External API handler
│   └── mock-data.php         # Mock data for testing
│
├── assets/                    # Static assets
│   ├── css/
│   │   ├── custom.css        # Custom styles
│   │   └── admin.css         # Admin styles
│   ├── js/
│   │   ├── main.js           # Main JavaScript
│   │   └── admin.js          # Admin JavaScript
│   └── uploads/              # User uploads
│       ├── horses/           # Horse images
│       └── tracks/           # Track images
│
├── auth/                      # Authentication
│   ├── login.php             # Login page
│   ├── register.php          # Registration page
│   ├── logout.php            # Logout handler
│   └── forgot-password.php   # Password recovery
│
├── ajax/                      # Frontend AJAX handlers
│   └── place-bet.php         # Betting handler
│
├── components/                # Reusable components
│   ├── navbar.php            # Navigation bar
│   ├── footer.php            # Footer
│   └── race-card.php         # Race card component
│
├── data/                      # JSON data files (fallback)
│   ├── races.json
│   ├── horses.json
│   ├── tracks.json
│   ├── jockeys.json
│   └── trainers.json
│
├── includes/                  # Core includes
│   ├── config.php            # Configuration
│   ├── database.php          # Database connection
│   ├── functions.php         # Helper functions
│   ├── auth.php              # Authentication functions
│   ├── models.php            # Data models
│   ├── api-handler.php       # API handler class
│   └── api-helpers.php       # API helper functions
│
├── scripts/                   # Utility scripts
│   ├── migrate_predictions.php
│   ├── generate_predictions_all.php
│   ├── migrate_betting.php
│   ├── check_predictions_count.php
│   └── ...
│
├── sql/                       # Database schema
│   └── database.sql          # Complete database schema
│
├── index.php                  # Home page
├── races.php                  # Races listing
├── racecard.php              # Race details
├── results.php               # Results page
├── predictions.php           # Predictions page
├── about.php                 # About page
├── account.php               # User account page
└── README.md                 # This file
```

---

## 🗄️ Database Schema

### Key Tables

| Table | Description |
|-------|-------------|
| `users` | Platform users (admin, editor, user roles) |
| `races` | Race events and details |
| `horses` | Horse registry with statistics |
| `race_entries` | Entries in races (horse + jockey + race) |
| `jockeys` | Jockey information |
| `trainers` | Trainer information |
| `owners` | Horse owners |
| `tracks` | Racing venues |
| `predictions` | AI predictions per entry |
| `race_results` | Race outcomes and results |
| `bets` | User bets and betting history |
| `payouts` | Payout information |
| `site_settings` | Site configuration |
| `api_settings` | External API configurations |
| `activity_log` | Audit trail and activity logging |

### Relationships

- `races` → `tracks` (Many-to-One)
- `race_entries` → `races`, `horses`, `jockeys` (Many-to-One)
- `horses` → `trainers`, `owners` (Many-to-One)
- `bets` → `users`, `races`, `race_entries` (Many-to-One)
- `predictions` → `races`, `race_entries` (Many-to-One)
- `race_results` → `races`, `race_entries` (Many-to-One)

---

## 📊 API Documentation

### Public Endpoints

#### Get All Races
```http
GET /api/races.php
```

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "Derby Stakes",
      "race_date": "2025-12-15",
      "race_time": "15:30:00",
      "status": "scheduled",
      "track_name": "Epsom Downs"
    }
  ]
}
```

#### Get Race by ID
```http
GET /api/races.php?id=1
```

#### Get Horses
```http
GET /api/horses.php
```

#### Get Race Results
```http
GET /api/results.php?race_id=1
```

### Admin Endpoints (Requires Authentication)

#### Create Race
```http
POST /admin/ajax/save-race.php
Content-Type: application/json

{
  "name": "Race Name",
  "race_date": "2025-12-15",
  "race_time": "15:30:00",
  "track_id": 1,
  "csrf_token": "token_here"
}
```

#### Update Horse
```http
PUT /admin/ajax/save-horse.php
```

#### Delete Item
```http
POST /admin/ajax/delete-item.php
```

### Response Format

**Success Response:**
```json
{
  "success": true,
  "message": "Operation successful",
  "data": { ... }
}
```

**Error Response:**
```json
{
  "success": false,
  "message": "Error description",
  "errors": {
    "field": ["error message"]
  }
}
```

---

## 🤖 AI Predictions

The platform includes a sophisticated AI prediction engine that analyzes multiple factors to calculate win probabilities.

### Prediction Factors

1. **Horse Form** - Recent performance (form field: "1-2-3-1-2")
2. **Career Statistics** - Wins, places, starts, earnings
3. **Jockey Performance** - Win percentage and track record
4. **Trainer Record** - Historical success rate
5. **Course Suitability** - Performance on similar conditions
6. **Track Conditions** - Going, weather, temperature
7. **Historical Patterns** - Analysis of historical race data

### Prediction Output

- **Win Probability** - Percentage chance of winning (0-100%)
- **Confidence Level** - Model confidence (low/medium/high/very_high)
- **Value Rating** - Betting value assessment (poor/fair/good/excellent)
- **Recommendation** - Betting recommendation (strong_bet/bet/consider/avoid)

### Usage

**Generate Predictions (CLI):**
```bash
php scripts/generate_predictions_all.php
```

**Generate Predictions (Admin Panel):**
- Navigate to Predictions page
- Click "Generate Predictions" for a race

**Schedule Automated Generation:**
- Set up a cron job (Linux) or scheduled task (Windows)
- Run `generate_predictions_all.php` periodically

---

## 💸 Betting System

The platform includes a complete betting system with the following features:

### Betting Features

- **Place Bets** - Users can place bets on race entries
- **Bet Types** - Win, Place, Show, and exotic bets
- **Odds Tracking** - Real-time odds updates
- **Credit System** - Virtual credit for betting
- **Payout Management** - Automatic payout calculation
- **Bet History** - Complete betting history for users
- **Status Tracking** - Pending, Won, Lost, Refunded status

### Placing a Bet

```javascript
// Example: Place a bet via AJAX
fetch('/ajax/place-bet.php', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/x-www-form-urlencoded',
    },
    body: new URLSearchParams({
        race_entry_id: 1,
        amount: 10.00,
        bet_type: 'win',
        csrf_token: csrfToken
    })
});
```

### Bet Status

- **pending** - Bet placed, awaiting race completion
- **won** - Bet won, payout calculated
- **lost** - Bet lost
- **refunded** - Bet refunded (race cancelled, etc.)

---

## 👨‍💼 Admin Panel

The admin panel provides comprehensive management capabilities:

### Admin Features

- **Dashboard** - Overview statistics and recent activity
- **Race Management** - Create, edit, and manage races
- **Horse Management** - Manage horse registry
- **Entry Management** - Add and manage race entries
- **Results Management** - Record and manage race results
- **User Management** - Manage users and roles
- **Track Management** - Manage racing venues
- **API Settings** - Configure external API integrations
- **Site Settings** - Configure site-wide settings
- **Activity Logs** - View audit trail
- **Credit Management** - Manage user credits

### Accessing Admin Panel

1. Login with admin credentials
2. Navigate to `/admin/index.php`
3. Use the sidebar navigation to access different sections

---

## 🔒 Security

### Security Features

✅ **Implemented Security Measures:**

- Password hashing with bcrypt (cost factor 12)
- CSRF token protection on all forms
- SQL prepared statements (PDO) - prevents SQL injection
- Input sanitization and validation
- Role-based access control (RBAC)
- Session management with secure settings
- Audit logging for compliance
- XSS protection via output escaping
- File upload validation

### Security Recommendations

⚠️ **For Production:**

1. **Enable HTTPS** - Use SSL/TLS certificates
2. **Rate Limiting** - Implement rate limiting on login attempts
3. **Environment Variables** - Move sensitive data to environment variables
4. **Regular Updates** - Keep PHP and dependencies updated
5. **Security Audits** - Perform regular security audits
6. **Two-Factor Authentication** - Consider 2FA for admin users
7. **Database Backups** - Regular automated backups
8. **Error Logging** - Configure proper error logging (disable display_errors in production)
9. **File Permissions** - Set proper file permissions (755 for dirs, 644 for files)
10. **Firewall** - Configure server firewall rules

### Security Configuration

```php
// In includes/config.php for production
error_reporting(0);
ini_set('display_errors', 0);
define('DEBUG_MODE', false);
```

---

## 🚨 Fallback Mode

The system includes a fallback mechanism that uses JSON data files when MySQL is unavailable. This allows the site to remain partially functional for demo/testing purposes.

**Fallback Data Location:** `/data/` folder

**Supported Data:**
- Races (`races.json`)
- Horses (`horses.json`)
- Tracks (`tracks.json`)
- Jockeys (`jockeys.json`)
- Trainers (`trainers.json`)

The system automatically detects database availability and falls back to JSON files when needed.

---

## 🛠 Development

### Development Setup

1. **Enable Debug Mode:**
```php
define('DEBUG_MODE', true); // In includes/config.php
```

2. **Enable Error Display:**
```php
error_reporting(E_ALL);
ini_set('display_errors', 1);
```

3. **Development Tools:**
   - Use browser DevTools for debugging
   - Check PHP error logs
   - Use database management tools (phpMyAdmin, MySQL Workbench)

### Code Structure

- **Models** - Data access layer (`includes/models.php`)
- **Controllers** - Business logic in page files
- **Views** - Presentation layer (PHP templates)
- **API** - RESTful endpoints in `/api/`
- **AJAX** - AJAX handlers in `/admin/ajax/` and `/ajax/`

### Database Migrations

To add new tables or modify schema:

1. Create SQL migration file in `sql/migrations/`
2. Document changes
3. Run migration: `mysql horse_racing_db < sql/migrations/001-add-table.sql`
4. Update models if needed

### Testing

**Manual Testing:**
- Test all CRUD operations
- Test authentication and authorization
- Test API endpoints
- Test betting functionality
- Test predictions generation

**Automated Testing:**
- Consider implementing PHPUnit for unit tests
- Use integration tests for API endpoints

---

## 🤝 Contributing

We welcome contributions! Please follow these guidelines:

### Contribution Guidelines

1. **Fork the Repository**
   
   Visit the repository on GitHub and click the "Fork" button, or use:
   ```bash
   git clone https://github.com/chathuka55/horse_odds_betting-system.git
   ```

2. **Create a Feature Branch**
   ```bash
   git checkout -b feature/amazing-feature
   ```

3. **Make Your Changes**
   - Follow PSR coding standards
   - Add comments for complex logic
   - Update documentation as needed

4. **Commit Your Changes**
   ```bash
   git commit -m 'Add some amazing feature'
   ```

5. **Push to the Branch**
   ```bash
   git push origin feature/amazing-feature
   ```

6. **Open a Pull Request**
   - Provide a clear description
   - Reference any related issues
   - Ensure all tests pass

### Code Style

- Follow PSR-12 coding standard
- Use meaningful variable and function names
- Add PHPDoc comments for functions and classes
- Keep functions focused and small
- Use prepared statements for all database queries

---

## 📝 License

This project is **proprietary software**. All rights reserved.

Copyright © 2025 Horse Racing Platform

**Note:** This software is not open source. Unauthorized copying, modification, distribution, or use of this software, via any medium, is strictly prohibited.

---

## 🆘 Support

### Getting Help

- **Documentation** - Check this README and code comments
- **Issues** - Open an issue on GitHub (if repository is public)
- **Email** - Contact support at info@racingpro.com (if applicable)

### Common Issues

#### Database Connection Failed
1. Verify MySQL is running
2. Check credentials in `includes/config.php`
3. Ensure database exists
4. Check file permissions

#### Uploads Not Working
```bash
chmod 755 assets/uploads/
chmod 755 assets/uploads/horses/
chmod 755 assets/uploads/tracks/
```

#### Session Issues
- Ensure `session_start()` is called before any output
- Check session directory permissions
- Verify session configuration in `php.ini`

#### Predictions Not Generating
1. Run migration script: `php scripts/migrate_predictions.php`
2. Check database connection
3. Verify race entries exist
4. Check PHP error logs

---

## 📈 Roadmap

### Planned Features

- [ ] Mobile app (React Native)
- [ ] Real-time notifications (WebSockets)
- [ ] Advanced analytics dashboard
- [ ] Social features (tips, comments)
- [ ] Email notifications
- [ ] SMS alerts
- [ ] Multi-language support
- [ ] Advanced betting options (accumulators, systems)
- [ ] Payment gateway integration
- [ ] API rate limiting
- [ ] GraphQL API
- [ ] Docker containerization

---

## 🙏 Acknowledgments

- **Tailwind CSS** - For the amazing utility-first CSS framework
- **Font Awesome** - For the comprehensive icon library
- **PHP Community** - For the excellent documentation and resources

---

## 📞 Contact

For questions, support, or inquiries:

- **Project Maintainer** - Chathuka Jayasekara
- **GitHub** - [@chathuka55](https://github.com/chathuka55)
- **LinkedIn** - [chathuka-jayasekara-013595216](https://www.linkedin.com/in/chathuka-jayasekara-013595216)
- **Instagram** - [@chathux_j](https://instagram.com/chathux_j)

---

<div align="center">

**Made with ❤️ for horse racing enthusiasts**

⭐ **Star this repo if you find it helpful!**

</div>

---

## 📄 Changelog

### Version 1.0.0 (December 2025)

- ✅ Initial release
- ✅ Complete admin panel
- ✅ AI prediction engine
- ✅ Betting system
- ✅ API integrations
- ✅ User authentication
- ✅ Activity logging
- ✅ Responsive design

---

**Last Updated:** December 2025  
**Status:** Production Ready ✅
