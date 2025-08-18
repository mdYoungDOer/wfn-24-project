#!/bin/bash

# WFN24 Deployment Script for Digital Ocean App Platform
# This script prepares the application for deployment

set -e

echo "🚀 Starting WFN24 deployment preparation..."

# Check if required tools are installed
command -v composer >/dev/null 2>&1 || { echo "❌ Composer is required but not installed. Aborting." >&2; exit 1; }
command -v npm >/dev/null 2>&1 || { echo "❌ NPM is required but not installed. Aborting." >&2; exit 1; }

# Install PHP dependencies
echo "📦 Installing PHP dependencies..."
composer install --no-dev --optimize-autoloader

# Install Node.js dependencies
echo "📦 Installing Node.js dependencies..."
npm ci

# Build frontend assets
echo "🔨 Building frontend assets..."
npm run build

# Create necessary directories
echo "📁 Creating necessary directories..."
mkdir -p public/uploads/{images,avatars}
mkdir -p logs
mkdir -p cache

# Set proper permissions
echo "🔐 Setting proper permissions..."
chmod 755 public/uploads
chmod 755 logs
chmod 755 cache

# Create .env file if it doesn't exist
if [ ! -f .env ]; then
    echo "📝 Creating .env file from .env.example..."
    cp .env.example .env
    echo "⚠️  Please update .env file with your production settings before deployment"
fi

# Generate application key
echo "🔑 Generating application key..."
php -r "echo 'APP_KEY=' . base64_encode(random_bytes(32)) . PHP_EOL;" >> .env

# Create robots.txt if it doesn't exist
if [ ! -f public/robots.txt ]; then
    echo "🤖 Creating robots.txt..."
    cat > public/robots.txt << 'EOF'
User-agent: *
Allow: /

# Sitemap
Sitemap: https://wfn24.com/sitemap.xml

# Disallow admin areas
Disallow: /admin/
Disallow: /api/
Disallow: /ws

# Allow important pages
Allow: /news/
Allow: /matches/
Allow: /teams/
Allow: /players/
Allow: /leagues/
EOF
fi

# Create sitemap.xml if it doesn't exist
if [ ! -f public/sitemap.xml ]; then
    echo "🗺️  Creating sitemap.xml..."
    cat > public/sitemap.xml << 'EOF'
<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    <url>
        <loc>https://wfn24.com/</loc>
        <lastmod>2024-01-01</lastmod>
        <changefreq>daily</changefreq>
        <priority>1.0</priority>
    </url>
    <url>
        <loc>https://wfn24.com/news</loc>
        <lastmod>2024-01-01</lastmod>
        <changefreq>hourly</changefreq>
        <priority>0.9</priority>
    </url>
    <url>
        <loc>https://wfn24.com/matches</loc>
        <lastmod>2024-01-01</lastmod>
        <changefreq>hourly</changefreq>
        <priority>0.9</priority>
    </url>
    <url>
        <loc>https://wfn24.com/leagues</loc>
        <lastmod>2024-01-01</lastmod>
        <changefreq>daily</changefreq>
        <priority>0.8</priority>
    </url>
    <url>
        <loc>https://wfn24.com/teams</loc>
        <lastmod>2024-01-01</lastmod>
        <changefreq>daily</changefreq>
        <priority>0.8</priority>
    </url>
    <url>
        <loc>https://wfn24.com/players</loc>
        <lastmod>2024-01-01</lastmod>
        <changefreq>daily</changefreq>
        <priority>0.8</priority>
    </url>
</urlset>
EOF
fi

# Create .htaccess if it doesn't exist
if [ ! -f public/.htaccess ]; then
    echo "🔧 Creating .htaccess..."
    cat > public/.htaccess << 'EOF'
RewriteEngine On

# Handle Authorization Header
RewriteCond %{HTTP:Authorization} .
RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]

# Redirect Trailing Slashes If Not A Folder...
RewriteCond %{REQUEST_FILENAME} !-d
RewriteCond %{REQUEST_URI} (.+)/$
RewriteRule ^ %1 [L,R=301]

# Send Requests To Front Controller...
RewriteCond %{REQUEST_FILENAME} !-d
RewriteCond %{REQUEST_FILENAME} !-f
RewriteRule ^ index.php [L]

# Security Headers
Header always set X-Content-Type-Options nosniff
Header always set X-Frame-Options DENY
Header always set X-XSS-Protection "1; mode=block"
Header always set Referrer-Policy "strict-origin-when-cross-origin"
Header always set Permissions-Policy "geolocation=(), microphone=(), camera=()"

# Enable Compression
<IfModule mod_deflate.c>
    AddOutputFilterByType DEFLATE text/plain
    AddOutputFilterByType DEFLATE text/html
    AddOutputFilterByType DEFLATE text/xml
    AddOutputFilterByType DEFLATE text/css
    AddOutputFilterByType DEFLATE application/xml
    AddOutputFilterByType DEFLATE application/xhtml+xml
    AddOutputFilterByType DEFLATE application/rss+xml
    AddOutputFilterByType DEFLATE application/javascript
    AddOutputFilterByType DEFLATE application/x-javascript
</IfModule>

# Enable Caching
<IfModule mod_expires.c>
    ExpiresActive on
    ExpiresByType text/css "access plus 1 year"
    ExpiresByType application/javascript "access plus 1 year"
    ExpiresByType image/png "access plus 1 year"
    ExpiresByType image/jpg "access plus 1 year"
    ExpiresByType image/jpeg "access plus 1 year"
    ExpiresByType image/gif "access plus 1 year"
    ExpiresByType image/webp "access plus 1 year"
    ExpiresByType image/svg+xml "access plus 1 year"
</IfModule>
EOF
fi

echo "✅ Deployment preparation completed!"
echo ""
echo "📋 Next steps:"
echo "1. Update .env file with your production settings"
echo "2. Set up your Digital Ocean App Platform account"
echo "3. Connect your GitHub repository"
echo "4. Deploy using the app.yaml configuration"
echo ""
echo "🔗 For detailed deployment instructions, see README.md"
