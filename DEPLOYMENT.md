# WFN24 Deployment Guide - Digital Ocean App Platform

This guide will walk you through deploying WFN24 to Digital Ocean's App Platform with PostgreSQL.

## 🚀 Quick Start (5 minutes)

### Prerequisites
- Digital Ocean account
- GitHub account
- Football API key from [api-football.com](https://www.api-football.com)
- SendGrid API key (optional, for email notifications)

### Step 1: Prepare Your Repository

1. **Fork/Clone the Repository**
   ```bash
   git clone https://github.com/mdYoungDOer/wfn-24-project.git
   cd wfn-24-project
   ```

2. **Run Deployment Script**
   ```bash
   chmod +x deploy.sh
   ./deploy.sh
   ```

3. **Update Environment Variables**
   ```bash
   # Edit .env file with your production settings
   nano .env
   ```

### Step 2: Push to GitHub

```bash
git add .
git commit -m "Prepare for Digital Ocean deployment"
git push origin main
```

### Step 3: Deploy to Digital Ocean

1. **Login to Digital Ocean**
   - Go to [cloud.digitalocean.com](https://cloud.digitalocean.com)
   - Sign in to your account

2. **Create New App**
   - Click "Create" → "Apps"
   - Choose "GitHub" as source
   - Select your `wfn-24-project` repository
   - Choose `main` branch

3. **Configure App Settings**
   - **Name**: `wfn24` (or your preferred name)
   - **Region**: Choose closest to your users
   - **Instance Size**: `Basic XXS` (1 vCPU, 512MB RAM, $5/month)
   - **Instance Count**: `1`

4. **Add Database**
   - Click "Add Resource" → "Database"
   - Choose "PostgreSQL"
   - Version: `12`
   - Size: `db-s-1vcpu-1gb` ($15/month)
   - Name: `wfn24-db`

5. **Configure Environment Variables**
   Add these environment variables:
   ```
   APP_ENV=production
   APP_DEBUG=false
   APP_URL=https://your-app-url.ondigitalocean.app
   DB_HOST=${db.DATABASE_HOST}
   DB_PORT=${db.DATABASE_PORT}
   DB_NAME=${db.DATABASE_NAME}
   DB_USER=${db.DATABASE_USERNAME}
   DB_PASSWORD=${db.DATABASE_PASSWORD}
   FOOTBALL_API_KEY=your_football_api_key_here
   FOOTBALL_API_BASE_URL=https://v3.football.api-sports.io
   SENDGRID_API_KEY=your_sendgrid_api_key_here
   SENDGRID_FROM_EMAIL=noreply@yourdomain.com
   SENDGRID_FROM_NAME=WFN24
   SESSION_DRIVER=file
   CACHE_DRIVER=file
   LOG_LEVEL=error
   UPLOAD_MAX_SIZE=10485760
   ALLOWED_IMAGE_TYPES=jpg,jpeg,png,gif,webp
   CSRF_TOKEN_NAME=_token
   PASSWORD_MIN_LENGTH=8
   WEBSOCKET_HOST=0.0.0.0
   WEBSOCKET_PORT=8080
   ```

6. **Deploy**
   - Click "Create Resources"
   - Wait for deployment (5-10 minutes)

### Step 4: Setup Database

1. **Access Database**
   - Go to your app in Digital Ocean
   - Click on the database resource
   - Click "Connect" → "Connection Details"

2. **Run Database Script**
   ```bash
   # Connect to your PostgreSQL database
   psql "postgresql://username:password@host:port/database"
   
   # Run the deployment script
   \i database/deploy.sql
   ```

### Step 5: Verify Deployment

1. **Check App Status**
   - Your app should be running at `https://your-app-url.ondigitalocean.app`

2. **Test Admin Access**
   - Go to `/admin`
   - Login with:
     - Email: `admin@wfn24.com`
     - Password: `admin123`

3. **Update Admin Password**
   - Change the default admin password immediately

## 🔧 Advanced Configuration

### Custom Domain Setup

1. **Add Domain in Digital Ocean**
   - Go to your app settings
   - Click "Settings" → "Domains"
   - Add your custom domain

2. **Update DNS Records**
   - Add CNAME record pointing to your app URL
   - Wait for DNS propagation (up to 48 hours)

3. **Update Environment Variables**
   ```
   APP_URL=https://yourdomain.com
   ```

### SSL Certificate

- Digital Ocean automatically provides SSL certificates
- No additional configuration needed

### Scaling

1. **Vertical Scaling**
   - Go to app settings
   - Change instance size as needed

2. **Horizontal Scaling**
   - Increase instance count
   - Load balancer is automatically configured

### Monitoring

1. **App Metrics**
   - CPU, memory, and request metrics available
   - Set up alerts for high usage

2. **Database Metrics**
   - Connection count, query performance
   - Storage usage monitoring

## 🛠️ Troubleshooting

### Common Issues

1. **App Won't Start**
   - Check environment variables
   - Verify database connection
   - Check logs in Digital Ocean console

2. **Database Connection Failed**
   - Verify database credentials
   - Check firewall settings
   - Ensure database is running

3. **Assets Not Loading**
   - Run `npm run build` locally
   - Commit and push changes
   - Check Vite build output

4. **API Calls Failing**
   - Verify Football API key
   - Check API rate limits
   - Review API service logs

### Logs and Debugging

1. **View App Logs**
   ```bash
   # In Digital Ocean console
   Apps → Your App → Logs
   ```

2. **Database Logs**
   ```bash
   # In Digital Ocean console
   Databases → Your Database → Logs
   ```

3. **Local Debugging**
   ```bash
   # Enable debug mode temporarily
   APP_DEBUG=true
   ```

## 📊 Performance Optimization

### Caching

1. **API Caching**
   - Football API responses cached for 5-10 minutes
   - Reduces API calls and improves performance

2. **Database Caching**
   - Query results cached in database
   - Automatic cache cleanup

3. **Static Assets**
   - CSS/JS files cached for 1 year
   - Images cached appropriately

### Database Optimization

1. **Indexes**
   - Full-text search indexes for news/articles
   - Performance indexes on frequently queried columns

2. **Connection Pooling**
   - PDO connection pooling enabled
   - Efficient database connections

### CDN Setup (Optional)

1. **Digital Ocean Spaces**
   - Create Spaces bucket for static assets
   - Configure CDN for global delivery

2. **Update Configuration**
   ```php
   // In your app configuration
   ASSET_URL=https://your-space.nyc3.digitalocean.com
   ```

## 🔒 Security

### Environment Variables
- Never commit `.env` files
- Use Digital Ocean's environment variable system
- Rotate API keys regularly

### Database Security
- Database accessible only from app
- Strong passwords required
- Regular backups enabled

### Application Security
- CSRF protection enabled
- Input validation on all forms
- SQL injection prevention via PDO
- XSS protection headers

## 💰 Cost Optimization

### Current Costs
- **App**: $5/month (Basic XXS)
- **Database**: $15/month (1 vCPU, 1GB)
- **Total**: ~$20/month

### Cost Reduction Options
1. **Development Environment**
   - Use smaller instance for development
   - Scale up for production

2. **Database Optimization**
   - Monitor query performance
   - Optimize indexes

3. **Asset Optimization**
   - Compress images
   - Minify CSS/JS

## 📈 Monitoring and Maintenance

### Regular Tasks
1. **Weekly**
   - Check app performance
   - Review error logs
   - Update dependencies

2. **Monthly**
   - Review costs
   - Backup verification
   - Security updates

3. **Quarterly**
   - Performance optimization
   - Feature updates
   - User feedback review

### Backup Strategy
- Database backups: Automatic daily
- Code backups: GitHub repository
- Configuration: Environment variables

## 🆘 Support

### Digital Ocean Support
- [Documentation](https://docs.digitalocean.com/products/app-platform/)
- [Community](https://www.digitalocean.com/community)
- [Support Tickets](https://cloud.digitalocean.com/support)

### Application Support
- Check [README.md](README.md) for detailed documentation
- Review logs for error details
- Test locally before deploying

---

**Need Help?** Create an issue in the GitHub repository or contact the development team.
