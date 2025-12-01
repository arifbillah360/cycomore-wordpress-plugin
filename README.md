# Partner CIU Manager

A comprehensive WordPress + WooCommerce plugin for partner management and CIU (Cumulative Impact Unit) purchasing. This plugin provides a complete solution with partner dashboards, admin tools, and a specialized purchasing workflow.

## Features

### Partner Management
- **Custom Post Type** for Partner Profiles with detailed metadata
- **Partner Logo Management** - Upload and display partner logos
- **Bank Details Storage** - Securely store partner banking information
- **Hero Partner Status** - Designate and highlight key partners
- **User Account Association** - Link partner profiles to WordPress user accounts

### CIU Purchasing System
- **WooCommerce Integration** - Seamless CIU purchasing through WooCommerce checkout
- **Flexible Pricing** - Configurable CIU price (default: £250)
- **Automatic Transaction Tracking** - All purchases logged with detailed metadata
- **Status Management** - Track CIUs through Pending → Active → Verified lifecycle
- **Order History** - Complete purchase history linked to WooCommerce orders

### Partner Dashboard
- **Front-end Dashboard** - Accessible via `[partner_dashboard]` shortcode
- **Profile Management** - Partners can edit their logo and bank details
- **CIU Purchase Form** - Real-time cost calculation and checkout
- **Status Overview** - Visual display of CIU counts by status
- **Collection Breakdown** - View CIUs allocated to different collections
- **Financial Summary** - Total funds contributed and last purchase date

### Admin Tools
- **CIU Allocation Interface** - Move CIUs between statuses (individual or bulk)
- **Partner Sorting** - Drag-and-drop partner reordering
- **Transaction Management** - View and edit all CIU transactions
- **Collection Management** - Add collections and allocate CIUs
- **Configurable Settings** - CIU price, currency, email notifications

### Email Notifications
- **Admin Notifications** - Alert admins of new CIU purchases
- **Partner Confirmations** - Send purchase confirmations to partners
- **Customizable Templates** - HTML email templates with order details

### Security Features
- **Nonce Verification** - All forms protected with WordPress nonces
- **Capability Checks** - Role-based access control
- **Input Sanitization** - All user inputs sanitized and validated
- **Secure File Uploads** - Logo uploads restricted to image files

## Requirements

- WordPress 6.0 or higher
- WooCommerce 8.0 or higher
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

4. **Verify WooCommerce**
   - Ensure WooCommerce is installed and activated
   - The plugin will display a warning if WooCommerce is not active

### Method 2: WP-CLI Installation

```bash
wp plugin install /path/to/partner-ciu-manager.zip --activate
```

## Initial Setup

### 1. Configure Settings

Go to **Partners → Settings** and configure:

- **CIU Price**: Default price per CIU (e.g., 250 for £250)
- **Currency**: Currency code (GBP, USD, EUR, etc.)
- **Admin Email**: Email address for admin notifications
- **Enable Notifications**: Toggle email notifications on/off

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

### 4. Create Dashboard Page

1. Go to **Pages → Add New**
2. Create a page titled "Partner Dashboard"
3. Add the shortcode: `[partner_dashboard]`
4. Publish the page
5. Note the page URL for partner access

### 5. Update Permalinks

After activation, flush rewrite rules:
- Go to **Settings → Permalinks**
- Click "Save Changes" (no changes needed)

## Usage

### For Partners

#### Accessing the Dashboard
1. Log in with partner credentials
2. Navigate to the dashboard page (URL from setup step 4)
3. View CIU status, purchase history, and profile

#### Purchasing CIUs
1. Enter desired CIU quantity in the purchase form
2. Review the auto-calculated total cost
3. Click "Purchase CIUs"
4. Complete checkout via WooCommerce
5. CIUs automatically added to "Pending" status after payment

#### Managing Profile
1. Upload or update partner logo
2. Edit bank details
3. Click "Update Profile" to save changes

### For Administrators

#### Managing Partner Profiles
- **View Partners**: Go to Partners in admin menu
- **Edit Profile**: Click on partner name to edit
- **Add Collections**: Use the Collection Breakdown section
- **Manual CIU Entry**: Edit CIU counts directly in partner profile

#### Allocating CIUs

##### Individual Allocation
1. Go to **Partners → CIU Allocation**
2. Find the partner in the table
3. Click "Allocate CIUs"
4. Select From Status and To Status
5. Enter quantity to move
6. Click "Allocate"

##### Bulk Allocation
1. Go to **Partners → CIU Allocation**
2. Scroll to "Bulk Allocation" section
3. Select From Status and To Status
4. Click "Bulk Allocate"
5. All partners' CIUs will be moved

#### Sorting Partners
1. Go to **Partners → Partner Sorting**
2. Drag and drop partners to reorder
3. Hero partners automatically appear first
4. Click "Save Order" to apply changes

#### Viewing Transactions
1. Go to **Partners → Transactions**
2. View all CIU purchases with details
3. Click on transaction to edit status
4. Filter by partner, status, or date

## Shortcodes

### [partner_dashboard]

Displays the complete partner dashboard with:
- Profile section (logo, bank details)
- CIU purchase form
- Status overview
- Collection breakdown
- Financial summary

**Usage:**
```
[partner_dashboard]
```

**Note:** Dashboard only visible to logged-in partners.

## User Roles

### Partner Role
Custom role created by the plugin with these capabilities:
- Access partner dashboard
- View own profile
- Edit own profile
- Purchase CIUs
- View purchase history

### Administrator Role
Full access to all plugin features:
- Manage all partner profiles
- Allocate CIUs
- View all transactions
- Sort partners
- Configure settings

## Hooks and Filters

### Actions

#### partner_ciu_purchase_completed
Triggered when a CIU purchase is completed.

```php
add_action('partner_ciu_purchase_completed', function($partner_id, $order_id, $ciu_quantity, $purchase_amount) {
    // Custom code here
}, 10, 4);
```

### Filters

#### Partner sorting can be extended with custom filters (coming soon)

## File Structure

```
partner-ciu-manager/
├── partner-ciu-manager.php          # Main plugin file
├── README.md                         # Documentation
├── includes/                         # Core classes
│   ├── class-partner-post-type.php
│   ├── class-ciu-transaction-post-type.php
│   ├── class-partner-role.php
│   ├── class-settings.php
│   ├── class-partner-dashboard.php
│   ├── class-woocommerce-integration.php
│   ├── class-email-notifications.php
│   ├── class-admin-panel.php
│   └── class-partner-sorting.php
├── admin/                            # Admin interface
│   ├── css/
│   │   └── admin.css
│   ├── js/
│   │   └── admin.js
│   └── partials/
│       ├── ciu-allocation.php
│       └── partner-sorting.php
├── public/                           # Frontend interface
│   ├── css/
│   │   └── public.css
│   ├── js/
│   │   └── public.js
│   └── partials/
│       └── partner-dashboard.php
└── templates/                        # Email templates
    └── emails/
        ├── admin-purchase-notification.php
        └── partner-purchase-confirmation.php
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
  - `_pending_cius` - Pending CIU count
  - `_active_cius` - Active CIU count
  - `_verified_cius` - Verified CIU count
  - `_total_funds` - Total funds contributed
  - `_last_purchase_date` - Last purchase date
  - `_collection_breakdown` - JSON array of collections

#### ciu_transaction
- **Post Meta Fields:**
  - `_partner_id` - Partner profile ID
  - `_order_id` - WooCommerce order ID
  - `_ciu_quantity` - CIU quantity
  - `_purchase_amount` - Purchase amount
  - `_purchase_date` - Purchase date
  - `_ciu_status` - Status (pending/active/verified)

### Options
- `partner_ciu_settings` - Plugin settings array
- `partner_ciu_product_id` - Hidden CIU product ID
- `partner_sort_order` - Partner display order

## Troubleshooting

### Dashboard not displaying
1. Verify user has "Partner" role
2. Check user is associated with a partner profile
3. Ensure shortcode is correct: `[partner_dashboard]`
4. Check for JavaScript errors in browser console

### WooCommerce integration not working
1. Verify WooCommerce is active
2. Check hidden CIU product exists (Settings → Products)
3. Clear WooCommerce transients
4. Test checkout with a different product

### Email notifications not sending
1. Check email notifications are enabled (Settings)
2. Verify admin email is correct
3. Test WordPress email functionality with other plugins
4. Check spam folder

### Partner sorting not saving
1. Clear browser cache
2. Check for JavaScript errors
3. Verify admin has proper capabilities
4. Disable conflicting plugins

## Development

### Extending the Plugin

The plugin is built with extensibility in mind. Key extension points:

1. **Custom Email Templates**: Override email templates in theme
2. **Action Hooks**: Use `partner_ciu_purchase_completed` for custom logic
3. **CSS Customization**: Override styles in theme CSS
4. **Database Queries**: Use helper methods in post type classes

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

- Nonce verification on all AJAX requests
- Capability checks for all admin functions
- Input sanitization and validation
- Prepared SQL statements (via WordPress API)
- Secure file upload handling
- XSS protection via escaping

## Changelog

### Version 1.0.0 (2025-12-01)
- Initial release
- Partner profile management
- CIU purchasing system
- WooCommerce integration
- Partner dashboard
- Admin allocation tools
- Partner sorting
- Email notifications
- Complete documentation

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

## Roadmap

Future features planned:
- [ ] Collection-specific reports
- [ ] Advanced filtering for transactions
- [ ] Export functionality (CSV, PDF)
- [ ] Multi-currency support
- [ ] Partner performance analytics
- [ ] Automated CIU allocation rules
- [ ] REST API endpoints
- [ ] Webhook integrations

## FAQ

**Q: Can partners see other partners' information?**
A: No, partners can only view and edit their own profile and dashboard.

**Q: Can I change the CIU price?**
A: Yes, go to Partners → Settings and update the CIU Price field.

**Q: How do I add a new status level?**
A: The plugin currently supports Pending, Active, and Verified. Custom statuses require code modification.

**Q: Can partners purchase CIUs offline?**
A: Yes, admins can manually create transactions via the admin panel.

**Q: Is the plugin translation-ready?**
A: Yes, the plugin uses WordPress translation functions and includes a text domain.

**Q: Can I customize the email templates?**
A: Yes, copy templates from `templates/emails/` to your theme and modify.

## Additional Notes

- Always backup your database before major updates
- Test on a staging site before deploying to production
- Keep WordPress, WooCommerce, and this plugin updated
- Review logs regularly for any errors
- Monitor email deliverability

---

**Version:** 1.0.0
**Last Updated:** December 1, 2025
**Maintained by:** Cycomore Development Team
