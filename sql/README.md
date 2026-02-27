# Spotlight Listings - Database Setup

This folder contains all SQL files needed to set up the Spotlight Listings database.

## Files

### 1. `complete_database.sql` ⭐ **RECOMMENDED**

**Use this file for a fresh installation.**

Contains:

- ✅ All database tables
- ✅ All indexes and foreign keys
- ✅ Sample admin user
- ✅ Sample virtual tour data
- ✅ Proper structure and organization

**How to use:**

1. Open phpMyAdmin
2. Create a new database named `spotlisting` (or your preferred name)
3. Select the database
4. Click "Import"
5. Upload `complete_database.sql`
6. Click "Go"

**Default Admin Login:**

- Username: `admin`
- Password: `admin123`
- ⚠️ **Change this immediately after first login!**

---

### 2. `schema.sql`

Original schema file (kept for reference). Contains the core table structures.

---

### 3. `virtual_tours_sample_data.sql`

Sample data for virtual tours only. Use this if you already have the database set up and just need to add sample virtual tour data.

---

## Database Tables

The complete database includes:

### User Management

- `users` - Regular user accounts
- `admin_users` - Admin panel users

### Properties

- `properties` - Property listings
- `property_images` - Property photo gallery
- `property_inquiries` - Property inquiry submissions

### Verification System

- `verifications` - Property verification requests
- `verification_status_history` - Audit trail for verification status changes

### Service Requests

- `consultations` - Consultation bookings
- `inspections` - Property inspection requests
- `media_requests` - Media service requests

### Communication

- `messages` - Internal messaging system

### Content

- `news` - News articles and blog posts
- `virtual_tours` - YouTube video property tours

---

## Quick Start

**Fresh Installation:**

```sql
-- 1. Create database
CREATE DATABASE spotlisting CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- 2. Select database
USE spotlisting;

-- 3. Import complete_database.sql through phpMyAdmin
-- OR via command line:
-- mysql -u root -p spotlisting < complete_database.sql
```

**Testing:**

1. Login to admin: `http://localhost/spotlisting/admin-login.php`
2. Username: `admin`, Password: `admin123`
3. Change password in Admin Profile
4. Start adding properties and content!

---

## Notes

- All tables use `InnoDB` engine for transaction support
- Character set: `utf8mb4` for full Unicode support
- Foreign keys ensure referential integrity
- Indexes optimize query performance
- Sample data is included for quick testing

---

## Backup Your Data

Always backup your database before making changes:

```bash
mysqldump -u root -p spotlisting > backup_$(date +%Y%m%d).sql
```

---

**Need Help?** Check the main README.md in the project root.
