# Legal Aid Beyond Bars

A web-based platform connecting imprisoned women in Kenya with pro bono lawyers and legal resources.

## Features

- **Multi-role Authentication**: Clients, Wardens, Lawyers, and Administrators
- **Case Management Workflow**: Submit → Verify → Assign → Manage
- **Legal Resources**: Rights information, procedures, and FAQs
- **Admin Dashboard**: User management and system monitoring
- **Mobile-friendly Interface**: Responsive design for all devices

## Setup Instructions

### Prerequisites
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Web server (Apache/Nginx) or PHP built-in server

### Installation

1. **Database Setup**
   ```bash
   # Create database and user in MySQL
   mysql -u root -p
   CREATE DATABASE legal_aid_db;
   ```

2. **Configure Database**
   - Edit `config/database.php` with your database credentials
   - Default settings: host=localhost, database=legal_aid_db, user=root, password=''

3. **Initialize Database**
   - Open your browser and navigate to `http://localhost/Legal Aid/setup.php`
   - This will create all tables and insert sample data

4. **Start the Application**
   ```bash
   # Using PHP built-in server
   cd "Legal Aid"
   php -S localhost:8000
   ```

## Default Login Credentials

- **Admin**: username: `admin`, password: `password`
- **Test Users**: All test users have password: `password`

## User Roles

### Clients (Imprisoned Women/Representatives)
- Submit legal aid requests
- Track case progress
- Access legal resources

### Prison Wardens
- Verify submitted cases
- Add verification notes
- Monitor case status

### Pro Bono Lawyers
- Browse verified cases
- Filter by location/type/urgency
- Take on cases and manage progress

### System Administrators
- Manage users and approvals
- Monitor system activity
- Manage prisons and legal resources

## File Structure

```
Legal Aid/
├── assets/css/           # Stylesheets
├── auth/                 # Authentication files
├── config/               # Database configuration
├── dashboard/            # Role-specific dashboards
├── database/             # SQL schema and sample data
├── includes/             # Common functions
├── legal/                # Legal resources
├── index.php             # Main entry point
└── setup.php             # Database setup script
```

## Security Features

- Password hashing with PHP's `password_hash()`
- Role-based access control (RBAC)
- SQL injection prevention with prepared statements
- Input sanitization
- Session management

## Testing

The platform includes sample data for testing:
- 5 sample prisons across Kenya
- Test users for each role
- Sample cases in various stages
- Legal resources and FAQs

## Support

This is an academic prototype demonstrating the concept of connecting imprisoned women with legal aid. For production deployment, additional security measures and compliance checks would be required.
