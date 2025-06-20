# Educational Portal - Setup & Installation Guide

A comprehensive educational resource management system for universities and academic institutions.

## 🚀 Quick Start

### Prerequisites
- PHP 8.2 or higher
- Web server (Apache, Nginx, or built-in PHP server)
- SQLite extension (usually included with PHP)
- 100MB+ free disk space

### Option 1: Using Built-in PHP Server (Recommended for Testing)

1. **Download/Clone the Project**
   ```bash
   # If you have the files, navigate to the project directory
   cd educational-portal
   ```

2. **Start the Server**
   ```bash
   php -S localhost:8000
   ```

3. **Access the Application**
   - Open your browser and go to: `http://localhost:8000`
   - Login with: **admin** / **admin123**

### Option 2: Using XAMPP/WAMP/MAMP

1. **Install XAMPP/WAMP/MAMP**
   - Download from official websites
   - Install and start Apache

2. **Copy Project Files**
   ```bash
   # Copy all files to your web server directory
   # XAMPP: C:\xampp\htdocs\educational-portal\
   # WAMP: C:\wamp64\www\educational-portal\
   # MAMP: /Applications/MAMP/htdocs/educational-portal/
   ```

3. **Set Permissions**
   ```bash
   # Make uploads directory writable
   chmod 755 uploads/
   chmod 755 database/
   ```

4. **Access the Application**
   - Go to: `http://localhost/educational-portal/`
   - Login with: **admin** / **admin123**

### Option 3: Using Apache/Nginx

1. **Configure Virtual Host**
   
   **Apache (`/etc/apache2/sites-available/educational-portal.conf`):**
   ```apache
   <VirtualHost *:80>
       ServerName educational-portal.local
       DocumentRoot /var/www/educational-portal
       
       <Directory /var/www/educational-portal>
           AllowOverride All
           Require all granted
       </Directory>
   </VirtualHost>
   ```

   **Nginx (`/etc/nginx/sites-available/educational-portal`):**
   ```nginx
   server {
       listen 80;
       server_name educational-portal.local;
       root /var/www/educational-portal;
       index index.php index.html;

       location / {
           try_files $uri $uri/ /index.php?$query_string;
       }

       location ~ \.php$ {
           fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
           fastcgi_index index.php;
           fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
           include fastcgi_params;
       }
   }
   ```

2. **Enable Site and Restart**
   ```bash
   # Apache
   sudo a2ensite educational-portal
   sudo systemctl restart apache2

   # Nginx
   sudo ln -s /etc/nginx/sites-available/educational-portal /etc/nginx/sites-enabled/
   sudo systemctl restart nginx
   ```

3. **Add to Hosts File**
   ```bash
   # Add this line to /etc/hosts (Linux/Mac) or C:\Windows\System32\drivers\etc\hosts (Windows)
   127.0.0.1   educational-portal.local
   ```

## 👥 Default User Accounts

### Administrator
- **Username:** admin
- **Password:** admin123
- **Access:** Full admin panel, user management, content approval

### Student Accounts
All student accounts use password: **password123**

| Username | Department | Student ID |
|----------|------------|------------|
| john_doe | IT | IT2024001 |
| sarah_smith | Business Management | BM2024002 |
| mike_johnson | Biomedical | BIO2024003 |
| emily_brown | IT | IT2024004 |
| david_wilson | Business Management | BM2024005 |
| student1 | IT | 123 |

## 📁 Project Structure

```
educational-portal/
├── admin/                  # Admin panel files
├── api/                    # API endpoints
├── assets/                 # CSS, JS, images
├── config/                 # Database configuration
├── database/               # SQLite database file
├── includes/               # Shared PHP functions
├── uploads/                # User uploaded files
├── index.php              # Homepage
├── login.php              # User authentication
├── dashboard.php          # User dashboard
├── departments.php        # Browse materials
├── upload.php             # File upload
└── README.md              # This file
```

## 🗄️ Database

The application uses SQLite by default with the following features:
- **File:** `database/education_portal.db`
- **Tables:** users, uploads, departments, modules, categories, comments, downloads, ratings, notifications
- **Sample Data:** 7 users, 10 educational materials, ratings, comments

### Switching to PostgreSQL (Optional)
1. Set environment variable: `USE_POSTGRESQL=true`
2. Configure PostgreSQL connection in `config/database.php`
3. The system will automatically use PostgreSQL instead of SQLite

## 🎯 Features Included

### For Students
- Browse educational materials by department
- Advanced search and filtering
- Download approved content
- Rate and comment on materials
- Upload new educational resources
- Personal dashboard and profile management

### For Administrators
- User management and approval
- Content moderation and approval workflow
- System statistics and analytics
- Data export capabilities
- Bulk operations for efficiency
- Comprehensive admin panel

### Departments Supported
- **IT:** Web Development, Mobile Apps (Frontend, Backend, Full Stack)
- **Business Management:** Marketing, Accounting, Business Law
- **Biomedical:** Anatomy, Medical Technology, Healthcare

## 🔧 Configuration

### File Upload Settings
- **Allowed Types:** PDF, DOC, DOCX, TXT, JPG, PNG
- **Max Size:** 10MB per file
- **Location:** `uploads/documents/`

### Security Features
- Password hashing with PHP's built-in functions
- SQL injection prevention
- File type validation
- Access control and authentication
- Input sanitization

## 🛠️ Troubleshooting

### Common Issues

**Database Permission Error:**
```bash
chmod 755 database/
chmod 666 database/education_portal.db
```

**Upload Directory Not Writable:**
```bash
chmod 755 uploads/
chmod 755 uploads/documents/
```

**PHP Extensions Missing:**
```bash
# Install required extensions
sudo apt-get install php-sqlite3 php-pdo
# or for other systems
php -m | grep -i sqlite
```

**Port Already in Use:**
```bash
# Use different port
php -S localhost:8080
```

### Development Mode
For development, you can enable error reporting by adding to any PHP file:
```php
ini_set('display_errors', 1);
error_reporting(E_ALL);
```

## 📊 Sample Data Included

The application comes with realistic sample data:
- **10 Educational Materials** across all departments
- **User ratings and reviews** for authentic testing
- **Download statistics** showing user engagement
- **Comments and feedback** from students

## 🔐 Security Considerations

### Production Deployment
1. Change default admin password
2. Enable HTTPS/SSL
3. Configure proper file permissions
4. Set up regular backups
5. Monitor upload directory size
6. Review security headers in `.htaccess`

### File Security
- Upload directory protected from direct PHP execution
- File type validation on server side
- Size limits enforced
- Secure download handling

## 📞 Support

For issues or questions:
1. Check the troubleshooting section above
2. Verify PHP version and extensions
3. Check file permissions
4. Review error logs

## 🚀 Ready to Go!

The application is production-ready with:
- Complete user management system
- Real educational content
- Admin panel for full control
- Security best practices
- Mobile-responsive design
- Comprehensive documentation

Start the server and begin using your educational portal immediately!