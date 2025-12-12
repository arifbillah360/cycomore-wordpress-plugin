# Partner CIU Manager

A comprehensive WordPress plugin for managing environmental impact partnerships with CIU (Cumulative Impact Unit) allocation across multiple categories and collections.

![Version](https://img.shields.io/badge/version-3.0.0-blue.svg)
![WordPress](https://img.shields.io/badge/wordpress-5.8%2B-brightgreen.svg)
![PHP](https://img.shields.io/badge/php-7.4%2B-purple.svg)
![License](https://img.shields.io/badge/license-GPL--2.0-red.svg)

**Version:** 3.0.0
**Developer:** [Arif Billah](https://arifbillah.com)
**Company:** [Softorio](https://softorio.com)
**Last Updated:** December 13, 2025

---

## 📋 Table of Contents

- [Overview](#overview)
- [Features](#features)
- [Requirements](#requirements)
- [Installation](#installation)
- [Initial Setup](#initial-setup)
- [Usage Guide](#usage-guide)
- [Shortcodes](#shortcodes)
- [User Roles](#user-roles)
- [File Structure](#file-structure)
- [Database Schema](#database-schema)
- [Configuration](#configuration)
- [Customization](#customization)
- [Troubleshooting](#troubleshooting)
- [Security](#security)
- [Changelog](#changelog)
- [FAQ](#faq)
- [Support](#support)
- [Contributing](#contributing)
- [License](#license)

---

## 🌟 Overview

Partner CIU Manager is a powerful WordPress plugin designed for organizations managing environmental impact partnerships. Track and allocate CIUs (Cumulative Impact Units) across multiple environmental categories, manage comprehensive partner profiles, and display public transformation dashboards.

Perfect for:
- Environmental conservation organizations
- Corporate sustainability programs
- Non-profit environmental initiatives
- Impact investment firms
- CSR (Corporate Social Responsibility) departments

---

## ✨ Features

### Partner Management
- **Custom Post Type** - Dedicated partner profiles with rich metadata
- **Partner Logo Management** - Upload and display partner logos (PNG/SVG)
- **Company Information** - Store legal name, trading name, registration details
- **Contact Management** - Primary and impact reporting contacts
- **Hero Partner Status** - Designate and highlight key partners
- **User Account Association** - Link partner profiles to WordPress users
- **Admin Notes System** - Internal notes visible only to admins and editors

### CIU Allocation System
- **8 Environmental Categories**:
  - 🌊 Oceans
  - 👕 Fast Fashion
  - 🌳 Forests
  - 🐅 Endangered Species
  - 🏖️ Sustainable Tourism
  - 🚰 Rivers
  - ♻️ Eco Waste
  - 🌱 Soil Erosion

- **Collection-Based Projects** - Organize CIUs into specific projects under each category
- **Status Tracking** - Track CIUs as Pending, Active, or Verified/Retired
- **Real-time Calculations** - Auto-calculate totals, funds, and category breakdowns
- **Flexible Allocation** - Admin can manually allocate CIUs to any category/collection

### Admin Tools
- **Partner Profile Management** - Add, edit, and manage partners
- **CIU Allocation Interface** - Drag-and-drop collection management
- **Settings Panel** - Configure CIU price, emails, pages
- **Admin Notes** - Internal communication tool
- **Role-Based Access** - Both Admin and Editor roles have full access

### Security Features
- ✅ Nonce Verification - All forms protected
- ✅ Capability Checks - Role-based access control
- ✅ Input Sanitization - All user inputs cleaned
- ✅ Output Escaping - XSS protection
- ✅ Secure File Uploads - Restricted file types and sizes
- ✅ SQL Injection Prevention - Prepared statements via WordPress API

---

## 📦 Requirements

- **WordPress:** 5.8 or higher
- **PHP:** 7.4 or higher
- **MySQL:** 5.6 or higher
- **Memory Limit:** 128MB minimum (256MB recommended)
- **Disk Space:** 10MB

---

## 🚀 Installation

### Method 1: Manual Installation

1. **Download the plugin**
```bash
git clone https://github.com/arifbillah360/cycomore-wordpress-plugin.git
```

2. **Upload to WordPress**
   - Upload the `partner-ciu-manager` folder to `/wp-content/plugins/`
   - Or zip the folder and upload via WordPress Admin → Plugins → Add New → Upload Plugin

3. **Activate the plugin**
   - Go to WordPress Admin → Plugins
   - Find "Partner CIU Manager" and click "Activate"

### Method 2: WP-CLI Installation
```bash
wp plugin install /path/to/partner-ciu-manager.zip --activate
```

### Method 3: Upload via Admin

1. Go to WordPress Admin → Plugins → Add New
2. Click "Upload Plugin"
3. Choose the ZIP file
4. Click "Install Now"
5. Click "Activate Plugin"

---

## ⚙️ Initial Setup

### 1. Flush Permalinks
After activation, flush rewrite rules:
- Go to **Settings → Permalinks**
- Click **"Save Changes"** (no changes needed, just save)

### 2. Configure Plugin Settings
Go to **Partners → Settings** and configure:

- **CIU Price per Unit**: Default £250 GBP (adjustable)
- **Admin Notification Email**: Email for important updates
- **Enable Public CIU Dashboard**: Toggle public viewing
- **Partner Dashboard Page**: Select page with `[partner_dashboard]` shortcode
- **Public Transformation Page**: Select page with `[public_ciu_dashboard]` shortcode

---

## 📖 Usage Guide

### For Administrators & Editors

#### Managing Partners

**View All Partners:**
- Go to **Partners** in admin menu
- See list of all partners with CIU totals
- Use search/filter to find specific partners

**Add New Partner:**
- Click **Add New Partner**
- Fill in company information
- Allocate CIUs across categories
- Add admin notes if needed
- Publish

**Edit Partner:**
- Click partner name from list
- Modify any information
- Update CIU allocations
- Add/edit collections
- Save changes

#### CIU Allocation Workflow

1. **Choose Category** - Click to expand (e.g., Oceans)
2. **Add Collection** - Click "Add New Collection"
3. **Enter Details**:
   - Collection title: "Coral Reef Restoration"
   - CIU amount: 50000
   - Status: Pending/Active/Verified
   - Description (optional)
   - External link (optional)
4. **Repeat** - Add more collections as needed
5. **Review Summary** - Check totals at bottom
6. **Save** - Click "Update" to save

---

## 🔗 Shortcodes

### `[ciu_partners]`
Displays partner list with cards showing Total CIUs and Categories.

**Usage:**
```
[ciu_partners]
```

**Optional Attributes:**
```
[ciu_partners
    view="list"
    columns="2"
    show_search="yes"]
```

**Attributes:**
- `view` - Display view (list, grid) (default: list)
- `columns` - Number of columns for grid (default: 2)
- `show_search` - Show search box (default: yes)

---

### `[ciu_allocation partner_id="123"]`
Displays CIU allocation for a specific partner (single partner page).

**Usage:**
```
[ciu_allocation partner_id="123" show_summary="yes" show_categories="yes"]
```

**Attributes:**
- `partner_id` - Partner post ID (required)
- `show_summary` - Show summary stats (default: yes)
- `show_categories` - Show category tabs (default: yes)

---

## 👥 User Roles

### Administrator
**Full access** to all plugin features:
- ✅ Manage all partner profiles
- ✅ Allocate/edit CIUs
- ✅ Configure settings
- ✅ Add/edit admin notes
- ✅ View all dashboards

### Editor
**Full access** to all plugin features (same as Administrator):
- ✅ Manage all partner profiles
- ✅ Allocate/edit CIUs
- ✅ Configure settings
- ✅ Add/edit admin notes
- ✅ View all dashboards

---

## 🐛 Troubleshooting

### CIU Allocations Not Saving

**Problem:** Changes to CIU allocations don't persist

**Solutions:**
1. Check user has Editor or Administrator role
2. Look for JavaScript errors in browser console (F12)
3. Verify nonce is valid (refresh page and try again)
4. Check PHP error logs for backend issues
5. Ensure sufficient memory limit (256MB recommended)

---

### Partner List Shows No Data

**Problem:** `[ciu_partners]` shortcode shows "No partners found"

**Solutions:**
1. Verify at least one partner exists and is published
2. Check shortcode is spelled correctly: `[ciu_partners]`
3. Clear site cache (if using caching plugin)
4. Check browser console for JavaScript errors
5. Verify WordPress permalinks are flushed

---

### Upload Errors

**Problem:** Cannot upload logo or documents

**Solutions:**
1. Check file size (max 2MB for logos)
2. Verify file type (PNG/SVG/JPG for logos)
3. Check WordPress upload_max_filesize in php.ini
4. Verify upload directory permissions (wp-content/uploads/)
5. Try smaller file size

---

### 404 Errors on Partner Pages

**Problem:** Partner URLs return 404 Not Found

**Solutions:**
1. Flush permalinks: Settings → Permalinks → Save
2. Check .htaccess file is writable
3. Verify mod_rewrite is enabled (Apache)
4. Deactivate and reactivate plugin
5. Check for permalink conflicts with other plugins

---

## 🔒 Security

### Security Features

- ✅ **Nonce Verification** - All forms protected with WordPress nonces
- ✅ **Capability Checks** - Role-based access control on all actions
- ✅ **Input Sanitization** - All user inputs sanitized using WordPress functions
- ✅ **Output Escaping** - All outputs escaped to prevent XSS
- ✅ **Prepared Statements** - SQL injection prevention via WordPress $wpdb
- ✅ **File Upload Validation** - Restricted file types, sizes, and mime types
- ✅ **CSRF Protection** - Cross-Site Request Forgery prevention
- ✅ **Direct Access Prevention** - Files cannot be accessed directly

### Reporting Security Issues

If you discover a security vulnerability:

1. **DO NOT** open a public GitHub issue
2. Email: **security@softorio.com**
3. Include detailed description
4. Provide steps to reproduce
5. Allow 48 hours for initial response

All security vulnerabilities will be promptly addressed.

---

## 📝 Changelog

### Version 3.0.0 - December 13, 2025

**UI/UX Improvements:**
- ✅ Modern minimal design for partner pages
- ✅ Improved partner list and single partner displays
- ✅ Streamlined partner cards with only Total CIUs and Categories
- ✅ Centered stats display on partner cards
- ✅ Removed unnecessary icons for cleaner UI

**Frontend Enhancements:**
- ✅ Fixed collection card description line break preservation
- ✅ Optimized stat number font sizes (28px desktop, 24px mobile)
- ✅ Removed Active CIUs and Funds from partner list cards
- ✅ 2-column centered grid layout for stats

**Code Optimization:**
- ✅ Improved JavaScript debugging and modal functionality
- ✅ Enhanced responsive design and mobile optimization
- ✅ Better CSS organization and specificity
- ✅ Placeholder method for preserving line breaks during text truncation

**Performance:**
- ✅ Reduced font sizes for better readability
- ✅ Optimized grid layouts for different screen sizes
- ✅ Improved CSS efficiency with better selectors

---

### Version 2.0.0 - December 5, 2025

**Simplified Plugin**

- Removed WooCommerce dependency
- Focused on core partner profile management
- Removed complex CIU allocation
- Kept basic settings panel

---

### Version 1.0.0 - December 1, 2025

**Initial Release**

- Partner profile custom post type
- WooCommerce integration
- Basic CIU tracking
- Settings panel

---

## ❓ FAQ

### General Questions

**Q: What are CIUs?**
A: CIUs (Cumulative Impact Units) are standardized units for measuring and tracking environmental impact contributions across different conservation projects.

**Q: Is this plugin free?**
A: Yes, Partner CIU Manager is licensed under GPL v2 and is free to use.

**Q: Can I use this for commercial projects?**
A: Yes, the GPL license allows commercial use.

---

### User Roles & Access

**Q: Who can access the plugin?**
A: Administrators and Editors have full access. Other users can view public partner lists.

**Q: Can I add more user roles?**
A: Yes, you can extend the plugin to add custom roles with specific capabilities.

---

### CIU Management

**Q: How do I allocate CIUs to a partner?**
A: Edit the partner profile, expand a category (e.g., Oceans), add collections with CIU amounts, and save.

**Q: What's the difference between Pending, Active, and Verified CIUs?**
A:
- **Pending**: Allocated but not yet deployed
- **Active**: Currently being deployed in projects
- **Verified**: Completed and verified/retired

---

### Customization

**Q: Can I add custom fields to partner profiles?**
A: Yes, use WordPress add_meta_box() function to add custom fields.

**Q: Can I customize the partner list design?**
A: Yes, override CSS in your theme or create a custom template.

**Q: Is the plugin translation-ready?**
A: Yes, all strings use translation functions. Use Poedit or Loco Translate to create translations.

---

## 📞 Support

### Getting Help

1. **Check Documentation** - Read this README thoroughly
2. **Review Troubleshooting** - See common issues above
3. **GitHub Issues** - [Open an issue](https://github.com/arifbillah360/cycomore-wordpress-plugin/issues)
4. **Email Support** - contact@softorio.com
5. **Developer Contact** - Via [arifbillah.com](https://arifbillah.com)

### Professional Support

For custom development, premium support, or consultations:
- Website: [softorio.com](https://softorio.com)
- Developer: [arifbillah.com](https://arifbillah.com)
- Email: contact@softorio.com

---

## 🤝 Contributing

Contributions are welcome! Here's how you can help:

### Reporting Bugs

1. Check if bug already reported in [Issues](https://github.com/arifbillah360/partner-ciu-manager/issues)
2. If not, create new issue with:
   - Clear title
   - Detailed description
   - Steps to reproduce
   - Expected vs actual behavior
   - WordPress/PHP/plugin versions
   - Screenshots (if applicable)

### Submitting Code

1. Fork the repository
2. Create a feature branch: `git checkout -b feature/amazing-feature`
3. Make your changes
4. Follow WordPress Coding Standards
5. Test thoroughly
6. Commit: `git commit -m 'Add amazing feature'`
7. Push: `git push origin feature/amazing-feature`
8. Open a Pull Request

---

## 📄 License

This plugin is licensed under the **GNU General Public License v2 or later**.

```
Partner CIU Manager - WordPress Plugin
Copyright (C) 2025 Arif Billah

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.
```

Full license text: [https://www.gnu.org/licenses/gpl-2.0.html](https://www.gnu.org/licenses/gpl-2.0.html)

---

## 👨‍💻 Credits

**Developed by:** [Arif Billah](https://arifbillah.com)
**Company:** [Softorio](https://softorio.com)
**GitHub:** [@arifbillah360](https://github.com/arifbillah360)
**Email:** contact@softorio.com

### Special Thanks

- WordPress community for excellent documentation
- All contributors and testers
- Environmental organizations using this plugin

---

## ⚠️ Important Notes

### Before Production Use

1. ✅ **Backup Everything** - Database and files
2. ✅ **Test on Staging** - Never test on live site
3. ✅ **Review Settings** - Configure all options
4. ✅ **Test User Roles** - Verify permissions
5. ✅ **Monitor Logs** - Watch for errors

---

**That's it! You're ready to manage environmental impact partnerships with Partner CIU Manager.**

For questions, support, or custom development, contact **[Arif Billah](https://arifbillah.com)** or visit **[Softorio](https://softorio.com)**.

---

*Last Updated: December 13, 2025*
*Version: 3.0.0*
*Maintained by: Arif Billah @ Softorio*
