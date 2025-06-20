# Database Configuration

Your Educational Portal now supports both SQLite and PostgreSQL databases.

## Current Setup
- **Active Database**: SQLite (default)
- **Location**: `database/education_portal.db`
- **Status**: Fully functional with all data

## PostgreSQL Support
A PostgreSQL database has been provisioned and is ready to use:
- Full schema created with proper relationships
- User data migrated
- All tables and indexes configured

## Switching to PostgreSQL
To switch to PostgreSQL, set this environment variable:
```
USE_POSTGRESQL=true
```

## Database Features
- User authentication and management
- Department-based organization (IT, Business Management, Biomedical)
- File upload and approval workflow
- Comments and download tracking
- Admin management capabilities

## Demo Credentials
- **Admin**: admin / admin123
- **Student**: student1 / password123

## Tables Structure
- `users` - User accounts and profiles
- `departments` - Academic departments
- `modules` - Course modules by department
- `categories` - Material categories
- `uploads` - Educational materials
- `comments` - User feedback
- `downloads` - Download tracking