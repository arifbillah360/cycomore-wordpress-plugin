# Partner CIU Manager

A simple WordPress plugin for managing partner profiles with basic partner information and settings.

## Features

### Partner Management
- **Custom Post Type** for Partner Profiles
- **Partner Logo Management** - Upload and display partner logos
- **Bank Details Storage** - Securely store partner banking information
- **Hero Partner Status** - Designate and highlight key partners
- **User Account Association** - Link partner profiles to WordPress user accounts

### Admin Tools
- **Partner Profile Management** - Add, edit, and manage partner profiles
- **Settings Panel** - Configure plugin settings
- **Partner Role** - Custom user role for partners

### Security Features
- **Nonce Verification** - All forms protected with WordPress nonces
- **Capability Checks** - Role-based access control
- **Input Sanitization** - All user inputs sanitized and validated
- **Secure File Uploads** - Logo uploads restricted to image files

## Requirements

- WordPress 6.0 or higher
- PHP 7.4 or higher
- MySQL 5.6 or higher

## Installation

### Method 1: Manual Installation

1. **Download the plugin**
   ```bash
   git clone https://github.com/arifbillah360/cycomore-wordpress-plugin.git
   ```

2. **Upload to WordPress**
   - Upload the `partner-ciu-manager` folder to `/wp-content/plugins/` directory
   - Or zip the folder and upload via WordPress admin (Plugins → Add New → Upload Plugin)

3. **Activate the plugin**
   - Go to WordPress admin → Plugins
   - Find "Partner CIU Manager" and click "Activate"

### Method 2: WP-CLI Installation

```bash
wp plugin install /path/to/partner-ciu-manager.zip --activate
```

## Initial Setup

### 1. Configure Settings

Go to **Partners → Settings** and configure basic plugin settings.

### 2. Create Partner Profiles

1. Go to **Partners → Add New Partner**
2. Enter partner name as the title
3. Fill in the required information:
   - Associate with a user account (create Partner user first)
   - Upload partner logo
   - Enter bank details
   - Set hero partner status (optional)
   - Add hero highlight text (optional)
4. Click "Publish"

### 3. Create Partner Users

1. Go to **Users → Add New**
2. Fill in user details (username, email, password)
3. Select **Partner** as the role
4. Click "Add New User"
5. Associate this user with a partner profile (edit partner profile and select the user)

### 4. Update Permalinks

After activation, flush rewrite rules:
- Go to **Settings → Permalinks**
- Click "Save Changes" (no changes needed)

## Usage

### For Administrators

#### Managing Partner Profiles
- **View Partners**: Go to Partners in admin menu
- **Edit Profile**: Click on partner name to edit
- **Add New Partner**: Click "Add New Partner"
- **Delete Partner**: Move to trash from partner list

## User Roles

### Partner Role
Custom role created by the plugin with these capabilities:
- Access own profile
- View own information

### Administrator Role
Full access to all plugin features:
- Manage all partner profiles
- Configure settings
- Manage partner users

## File Structure

```
partner-ciu-manager/
├── partner-ciu-manager.php          # Main plugin file
├── README.md                         # Documentation
├── includes/                         # Core classes
│   ├── class-partner-post-type.php
│   ├── class-partner-role.php
│   └── class-settings.php
├── admin/                            # Admin interface
│   └── css/
│       └── admin.css
└── assets/                           # Additional assets
```

## Database Schema

### Custom Post Types

#### partner_profile
- **Post Meta Fields:**
  - `_partner_user_id` - Associated user ID
  - `_partner_logo` - Attachment ID for logo
  - `_bank_name` - Bank name
  - `_account_number` - Account number
  - `_sort_code` - Sort code
  - `_is_hero_partner` - Hero status (1/0)
  - `_hero_highlight_text` - Hero highlight text

### Options
- `partner_ciu_settings` - Plugin settings array

## Troubleshooting

### Partner profile not saving
1. Check user has proper permissions
2. Verify nonce is valid
3. Check for JavaScript errors in browser console

## Development

### Extending the Plugin

The plugin is built with extensibility in mind. Key extension points:

1. **Custom Fields**: Add custom meta fields to partner profiles
2. **CSS Customization**: Override styles in theme CSS
3. **Database Queries**: Use helper methods in post type classes

### Coding Standards

- Follows WordPress Coding Standards
- PSR-4 autoloading compatible structure
- Comprehensive inline documentation
- Nonce verification on all forms
- Sanitization on all inputs
- Escaping on all outputs

## Security

### Reporting Security Issues

If you discover a security vulnerability, please email security@example.com. All security vulnerabilities will be promptly addressed.

### Security Features

- Nonce verification on all forms
- Capability checks for all admin functions
- Input sanitization and validation
- Prepared SQL statements (via WordPress API)
- Secure file upload handling
- XSS protection via escaping

## Changelog

### Version 2.0.0 (2025-12-05)
- Simplified plugin to focus on partner profile management
- Removed WooCommerce integration
- Removed CIU transaction tracking
- Removed CIU allocation system
- Removed partner sorting
- Removed email notifications
- Kept core partner profile management
- Kept settings panel

### Version 1.0.0 (2025-12-01)
- Initial release with full WooCommerce integration

## License

GPL v2 or later

## Support

For support, please:
1. Check the documentation above
2. Review the troubleshooting section
3. Submit an issue on GitHub
4. Contact support@example.com

## Credits

Developed by Cycomore
https://cycomore.com

## Contributing

Contributions are welcome! Please:
1. Fork the repository
2. Create a feature branch
3. Commit your changes
4. Push to the branch
5. Create a Pull Request

## FAQ

**Q: Can partners see other partners' information?**
A: No, partners can only view their own profile.

**Q: Can I add custom fields to partner profiles?**
A: Yes, you can extend the plugin by adding custom meta fields.

**Q: Is the plugin translation-ready?**
A: Yes, the plugin uses WordPress translation functions and includes a text domain.

## Additional Notes

- Always backup your database before major updates
- Test on a staging site before deploying to production
- Keep WordPress and this plugin updated
- Review logs regularly for any errors

---

**Version:** 2.0.0
**Last Updated:** December 5, 2025
**Maintained by:** Cycomore Development Team
