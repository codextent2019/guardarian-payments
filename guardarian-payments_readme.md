# Guardarian Payment Gateway for WordPress

A complete, production-ready WordPress plugin for integrating Guardarian cryptocurrency payment processing. Convert USD to USDC on Ethereum with full transaction management and operator-assisted purchase flow.

## 📋 Table of Contents

- [Features](#features)
- [Requirements](#requirements)
- [Installation](#installation)
- [Configuration](#configuration)
- [Usage](#usage)
- [File Structure](#file-structure)
- [API Integration](#api-integration)
- [Webhook Setup](#webhook-setup)
- [Troubleshooting](#troubleshooting)

## ✨ Features

### Core Functionality
- **Standalone WordPress plugin** - No WooCommerce required
- **Fixed conversion path**: USD → USDC (Ethereum blockchain)
- **Operator-assisted flow** - Perfect for phone support scenarios
- **Real-time exchange rates** - Auto-updates every 30 seconds
- **Transaction management** - Complete history with status tracking
- **Webhook support** - Real-time payment status updates
- **Email notifications** - Admin and customer alerts
- **Comprehensive logging** - Debug and monitor all activity

### Admin Dashboard
- **Statistics overview** - Total transactions, success rate, volume
- **Transaction management** - Filter, search, export to CSV
- **Log viewer** - Debug and monitor system activity
- **Settings panel** - Multi-tab configuration interface
- **API testing** - One-click connection verification

### Frontend Widget
- **Shortcode support** - `[guardarian_payment_widget]`
- **Responsive design** - Mobile, tablet, desktop optimized
- **Accessibility features** - ARIA labels, keyboard navigation
- **Theme support** - Light/dark mode options
- **Real-time estimates** - Live exchange rate display

## 📦 Requirements

- **WordPress**: 6.0 or higher
- **PHP**: 8.1 or higher
- **MySQL**: 5.7 or higher
- **Guardarian API Key**: Required (get from [Guardarian Partner Portal](https://guardarian.com/partner))
- **SSL Certificate**: Required for production (HTTPS)

## 🚀 Installation

### Method 1: Manual Upload

1. **Download the plugin files** and create this directory structure:

```
wp-content/plugins/guardarian-payment-gateway/
├── guardarian-payment-gateway.php (main file)
├── includes/
│   ├── class-guardarian-database.php
│   ├── class-guardarian-api.php
│   ├── class-guardarian-logger.php
│   ├── class-guardarian-admin.php
│   ├── class-guardarian-widget.php
│   ├── class-guardarian-webhook.php
│   └── class-guardarian-email.php
├── templates/
│   ├── admin/
│   │   ├── dashboard.php
│   │   ├── settings.php
│   │   ├── transactions.php
│   │   ├── logs.php
│   │   └── settings/
│   │       ├── api.php
│   │       ├── payment.php
│   │       ├── display.php
│   │       ├── redirect.php
│   │       ├── webhook.php
│   │       └── notification.php
│   └── widget/
│       └── payment-form.php
├── assets/
│   ├── css/
│   │   ├── admin.css
│   │   └── widget.css
│   └── js/
│       ├── admin.js
│       └── widget.js
└── languages/
```

2. **Upload the plugin folder** to `/wp-content/plugins/`

3. **Activate the plugin** from WordPress Admin → Plugins

### Method 2: WordPress Admin Upload

1. Zip the plugin folder
2. Go to WordPress Admin → Plugins → Add New
3. Click "Upload Plugin" and select the zip file
4. Click "Install Now" and then "Activate"

## ⚙️ Configuration

### 1. API Setup

1. Navigate to **Guardarian Payments → Settings → API Configuration**
2. Select your **API Environment** (Production or Sandbox)
3. Enter your **Production API Key**: `f3eaa23e-84e9-4b76-b981-e6c7bb02fe59`
4. Click **Test API Connection** to verify
5. Click **Save Settings**

### 2. Payment Settings

Navigate to **Settings → Payment Settings**:

- **Default Wallet Address**: `0x8C848036496E6af33A22c4f59A4B442305c3f8BE`
- **Currency From**: USD (default)
- **Currency To**: USDC_ETH (default)
- **Minimum Amount**: 50 USD (recommended)
- **Maximum Amount**: 10000 USD (optional)
- **Transaction Timeout**: 30 minutes

### 3. Display Settings

Customize the frontend widget appearance:

- **Widget Title**: "Buy Cryptocurrency"
- **Button Text**: "Continue to Payment"
- **Color Theme**: Light or Dark
- **Custom CSS**: Advanced styling options

### 4. Webhook Configuration

1. Navigate to **Settings → Webhook**
2. Copy the **Webhook URL**: `https://yoursite.com/guardarian-webhook/`
3. Add this URL to your Guardarian Partner Portal
4. Enable webhook processing
5. Note the **Webhook Secret** for verification

### 5. Email Notifications

Configure email alerts:

- **Admin Notifications**: Enable/disable
- **Admin Email**: Override default admin email
- **Customer Notifications**: Optional
- **Email Templates**: Customize messages

## 📖 Usage

### Adding the Payment Widget to Your Site

#### Option 1: Using Shortcode

Add this shortcode to any page, post, or widget area:

```
[guardarian_payment_widget]
```

#### Option 2: With Custom Attributes

```
[guardarian_payment_widget 
    title="Buy USDC" 
    button_text="Start Purchase" 
    default_amount="100" 
    show_exchange_rate="true" 
    theme="dark"
    width="600px"]
```

#### Option 3: In PHP Template

```php
<?php echo do_shortcode('[guardarian_payment_widget]'); ?>
```

#### Option 4: Block Editor

1. Add a "Shortcode" block
2. Paste: `[guardarian_payment_widget]`
3. Publish the page

### Homepage Integration

To add below the "For Rent" section as discussed:

1. Edit your homepage in WordPress
2. Add the shortcode where you want the widget to appear
3. Save and preview

### Customer Flow

1. Customer enters USD amount
2. Real-time USDC estimate displays
3. Customer clicks "Continue to Payment"
4. Redirected to Guardarian's secure checkout
5. Completes KYC/payment on Guardarian's platform
6. Returns to your site with confirmation

### Operator-Assisted Flow

For elderly customers (50+ years) with phone support:

1. Operator opens the widget on their computer
2. Customer provides amount over phone
3. Operator enters amount and clicks submit
4. Operator guides customer through Guardarian's secure checkout
5. Customer provides SSN/verification on Guardarian's platform
6. Transaction completes, USDC sent to destination wallet

## 📁 File Structure

### Main Plugin File
- `guardarian-payment-gateway.php` - Plugin initialization and hooks

### Core Classes (includes/)
- `class-guardarian-database.php` - Database operations and queries
- `class-guardarian-api.php` - Guardarian API communication
- `class-guardarian-logger.php` - Logging system
- `class-guardarian-admin.php` - Admin interface
- `class-guardarian-widget.php` - Frontend widget
- `class-guardarian-webhook.php` - Webhook handler
- `class-guardarian-email.php` - Email notifications

### Templates
- `templates/admin/` - Admin dashboard pages
- `templates/widget/` - Frontend widget HTML

### Assets
- `assets/css/admin.css` - Admin styling
- `assets/css/widget.css` - Widget styling
- `assets/js/admin.js` - Admin JavaScript
- `assets/js/widget.js` - Widget JavaScript

## 🔌 API Integration

### Guardarian API Endpoints Used

1. **GET /currencies** - Available currencies
2. **GET /estimate** - Exchange rate calculation
3. **POST /transactions** - Create new transaction
4. **GET /transactions/{id}** - Get transaction status
5. **GET /limits** - Transaction limits
6. **GET /validate-address** - Wallet validation

### API Configuration

```php
Production URL: https://api-payments.guardarian.com/v1
Sandbox URL: https://api-payments-sandbox.guardarian.com/v1
API Key: f3eaa23e-84e9-4b76-b981-e6c7bb02fe59
```

### Request Example

```php
$api = new Guardarian_API();
$result = $api->create_transaction([
    'from_amount' => 100,
    'from_currency' => 'USD',
    'to_currency' => 'USDC_ETH',
    'payout_address' => '0x8C848036496E6af33A22c4f59A4B442305c3f8BE'
]);
```

## 🔔 Webhook Setup

### Configure in Guardarian Portal

1. Log into Guardarian Partner Portal
2. Navigate to Webhooks section
3. Add webhook URL: `https://yoursite.com/guardarian-webhook/`
4. Save configuration

### Webhook Payload Example

```json
{
    "id": "grd_123456",
    "status": "success",
    "from_amount": 100.00,
    "to_amount": 99.85,
    "from_currency": "USD",
    "to_currency": "USDC_ETH",
    "rate": 0.9985,
    "payout_address": "0x8C848036496E6af33A22c4f59A4B442305c3f8BE"
}
```

### Webhook Security

The plugin verifies webhooks using HMAC-SHA256 signature:

```php
$signature = hash_hmac('sha256', $payload, $webhook_secret);
```

## 🔧 Troubleshooting

### Common Issues

#### 1. API Connection Failed

**Solution**:
- Verify API key is correct
- Check API environment setting (Production/Sandbox)
- Test connection from Settings page
- Check server firewall allows outbound HTTPS

#### 2. Webhook Not Receiving Updates

**Solution**:
- Verify webhook URL in Guardarian Portal
- Check webhook is enabled in plugin settings
- Review webhook logs in Logs page
- Ensure site has valid SSL certificate

#### 3. Widget Not Displaying

**Solution**:
- Clear WordPress cache
- Check shortcode spelling
- Verify plugin is activated
- Check browser console for JavaScript errors

#### 4. Transactions Not Saving

**Solution**:
- Check database tables were created
- Verify WordPress database permissions
- Review logs for database errors
- Try deactivating and reactivating plugin

### Debug Mode

Enable debug logging:

1. Go to Settings → Logs
2. Enable "Debug Logging"
3. Reproduce the issue
4. Check Logs page for detailed information

### Support

For technical support:
- Review plugin logs (Admin → Guardarian Payments → Logs)
- Check error_log in wp-content/
- Contact Guardarian API support for API-related issues
- Review WordPress debug.log if WP_DEBUG is enabled

## 📊 Database Schema

### Transactions Table

```sql
CREATE TABLE wp_guardarian_transactions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    transaction_id VARCHAR(100) NOT NULL UNIQUE,
    guardarian_id VARCHAR(100),
    status VARCHAR(50) NOT NULL DEFAULT 'pending',
    amount_from DECIMAL(20,8) NOT NULL,
    amount_to DECIMAL(20,8),
    currency_from VARCHAR(10) NOT NULL,
    currency_to VARCHAR(10) NOT NULL,
    exchange_rate DECIMAL(20,8),
    wallet_address VARCHAR(255) NOT NULL,
    payment_url TEXT,
    customer_email VARCHAR(255),
    customer_name VARCHAR(255),
    api_response LONGTEXT,
    error_message TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    completed_at DATETIME
);
```

### Logs Table

```sql
CREATE TABLE wp_guardarian_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    log_type VARCHAR(50) NOT NULL,
    severity VARCHAR(20) NOT NULL DEFAULT 'info',
    message TEXT NOT NULL,
    context LONGTEXT,
    transaction_id VARCHAR(100),
    user_id BIGINT UNSIGNED,
    ip_address VARCHAR(45),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);
```

## 🔐 Security Features

- API keys stored as WordPress options (encrypted in database)
- Webhook signature verification
- SQL injection prevention via prepared statements
- XSS protection via output escaping
- CSRF protection via nonces
- User capability checks for admin functions
- Input sanitization and validation

## 📝 License

GPL v2 or later

## 🤝 Credits

Developed by SnapyCode
Powered by Guardarian API

---

**Version**: 1.0.0  
**Last Updated**: October 2025  
**Compatibility**: WordPress 6.0+, PHP 8.1+
