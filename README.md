# WFN24 - World Football News 24

A comprehensive, mobile-first, responsive web application for football/soccer news, live scores, and match coverage. Built with pure PHP backend and React frontend using Inertia.js.

## 🚀 Features

### Core Features
- **Live Match Updates**: Real-time scores and match statistics
- **News Management**: Comprehensive news articles with categories
- **Team & Player Profiles**: Detailed information and statistics
- **League Coverage**: Standings, fixtures, and results
- **Search Functionality**: Full-text search across all content
- **User Authentication**: Registration, login, and profile management
- **Admin Dashboard**: Complete CMS for content management

### Technical Features
- **Mobile-First Design**: Responsive design optimized for all devices
- **Real-Time Updates**: WebSocket integration for live data
- **API Integration**: Football data from api-football.com
- **SEO Optimized**: Meta tags, sitemaps, and structured data
- **Performance**: Caching, optimized queries, and asset bundling
- **Security**: CSRF protection, input validation, and secure authentication

## 🛠 Tech Stack

### Backend
- **PHP 8.1+**: Pure PHP with custom MVC architecture
- **PostgreSQL**: Primary database with full-text search
- **Composer**: Dependency management
- **PDO**: Database interactions with prepared statements

### Frontend
- **React 18**: Modern React with hooks and functional components
- **Inertia.js**: Seamless SPA experience with server-side rendering
- **Tailwind CSS**: Utility-first CSS framework
- **Vite**: Fast build tool and development server

### Additional Libraries
- **Chart.js**: Data visualization for statistics
- **Guzzle**: HTTP client for API requests
- **PHPMailer**: Email functionality with SendGrid
- **Ratchet**: WebSocket server for real-time updates
- **Monolog**: Logging system

## 📋 Prerequisites

Before you begin, ensure you have the following installed:
- **PHP 8.1 or higher**
- **PostgreSQL 12 or higher**
- **Node.js 16 or higher**
- **Composer**
- **npm or yarn**

## 🚀 Installation

### 1. Clone the Repository
```bash
git clone https://github.com/mdYoungDOer/wfn-24-project.git
cd wfn-24-project
```

### 2. Install PHP Dependencies
```bash
composer install
```

### 3. Install Node.js Dependencies
```bash
npm install
```

### 4. Environment Configuration
```bash
cp env.example .env
```

Edit the `.env` file with your configuration:
```env
# Database Configuration
DB_HOST=localhost
DB_PORT=5432
DB_NAME=wfn24_db
DB_USER=your_username
DB_PASSWORD=your_password

# API Configuration
FOOTBALL_API_KEY=your_api_football_key
FOOTBALL_API_BASE_URL=https://v3.football.api-sports.io

# Email Configuration (SendGrid)
SENDGRID_API_KEY=your_sendgrid_api_key
SENDGRID_FROM_EMAIL=noreply@wfn24.com
SENDGRID_FROM_NAME=WFN24

# Application Configuration
APP_NAME=WFN24
APP_ENV=development
APP_DEBUG=true
APP_URL=http://localhost:8000
APP_KEY=base64:your_random_32_character_key_here
```

### 5. Database Setup
```bash
# Create PostgreSQL database
createdb wfn24_db

# Run database migrations
psql -d wfn24_db -f database/schema.sql
```

### 6. Build Frontend Assets
```bash
# Development
npm run dev

# Production
npm run build
```

### 7. Start the Development Server
```bash
# Start PHP development server
composer dev

# Or manually
php -S localhost:8000 -t public
```

### 8. WebSocket Server (Optional)
For real-time features, start the WebSocket server:
```bash
php websocket-server.php
```

## 🔧 Configuration

### API Keys Setup

1. **Football API**: Get your API key from [api-football.com](https://www.api-football.com)
2. **SendGrid**: Create an account and get your API key from [sendgrid.com](https://sendgrid.com)

### Database Configuration

The application uses PostgreSQL with the following features:
- Full-text search capabilities
- JSONB support for flexible data storage
- Optimized indexes for performance
- Foreign key constraints for data integrity

### File Permissions

Ensure proper permissions for upload directories:
```bash
chmod -R 755 public/uploads
chmod -R 755 storage/logs
```

## 📁 Project Structure

```
wfn24-project/
├── src/                    # PHP source code
│   ├── Controllers/        # Application controllers
│   ├── Models/            # Database models
│   ├── Services/          # Business logic services
│   ├── Config/            # Configuration classes
│   └── Middleware/        # HTTP middleware
├── resources/             # Frontend resources
│   ├── js/               # React components
│   ├── jsx/              # JSX files
│   └── css/              # Stylesheets
├── public/               # Public assets
│   ├── build/            # Compiled assets
│   ├── images/           # Static images
│   └── index.php         # Entry point
├── database/             # Database files
│   └── schema.sql        # Database schema
├── admin/                # Admin-specific files
└── tests/                # PHPUnit tests
```

## 🎯 Key Features Implementation

### 1. Live Match Center
- Real-time score updates via WebSocket
- Live commentary and match events
- Statistics visualization with Chart.js
- Team formations and lineups

### 2. News Management
- Rich text editor with TipTap
- Image upload and management
- SEO optimization with meta tags
- Category and tag system

### 3. Admin Dashboard
- Complete CRUD operations
- Content moderation
- Analytics and reporting
- User management

### 4. Search Functionality
- Full-text search using PostgreSQL
- Search across news, teams, players, and leagues
- Advanced filtering options
- Search suggestions

## 🔒 Security Features

- **CSRF Protection**: Built-in CSRF token validation
- **Input Validation**: Comprehensive input sanitization
- **SQL Injection Prevention**: Prepared statements with PDO
- **XSS Protection**: Output escaping and sanitization
- **Password Hashing**: Secure password storage with bcrypt
- **Session Security**: Secure session management

## 📱 Mobile-First Design

The application is built with mobile-first principles:
- Responsive grid layouts
- Touch-optimized interactions
- Progressive enhancement
- Fast loading on mobile networks

## 🚀 Deployment

### Digital Ocean App Platform

1. **Prepare for Deployment**:
   ```bash
   npm run build
   composer install --optimize-autoloader --no-dev
   ```

2. **Environment Variables**: Set all required environment variables in your deployment platform

3. **Database**: Ensure PostgreSQL is configured and accessible

4. **File Uploads**: Configure proper file storage (local or cloud)

### Production Checklist

- [ ] Set `APP_ENV=production`
- [ ] Set `APP_DEBUG=false`
- [ ] Configure proper database credentials
- [ ] Set up SSL certificates
- [ ] Configure caching (Redis/APCu)
- [ ] Set up monitoring and logging
- [ ] Configure backup strategy

## 🧪 Testing

Run the test suite:
```bash
composer test
```

## 📊 Performance Optimization

- **Database Indexing**: Optimized indexes for common queries
- **Caching**: API response caching and query result caching
- **Asset Optimization**: Minified CSS/JS and image optimization
- **CDN Integration**: Static asset delivery via CDN
- **Lazy Loading**: Images and components loaded on demand

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 🆘 Support

For support and questions:
- Create an issue on GitHub
- Check the documentation
- Review the code comments

## 🙏 Acknowledgments

- [api-football.com](https://www.api-football.com) for football data
- [Inertia.js](https://inertiajs.com) for the seamless SPA experience
- [Tailwind CSS](https://tailwindcss.com) for the utility-first CSS framework
- [Chart.js](https://www.chartjs.org) for data visualization

---

**WFN24** - Your ultimate destination for world football news, live scores, and comprehensive match coverage. ⚽
