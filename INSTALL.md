# Pattern Lock Module - Installation Guide

## Quick Start

This guide will help you install and configure the Pattern Lock module for CodeIgniter 3.

## Prerequisites

Before installing, ensure you have:
- CodeIgniter 3.x installed and working
- MySQL or MariaDB database
- PHP 7.2 or higher
- Write permissions on application directory

## Step-by-Step Installation

### Step 1: Copy Files

Copy the `application` folder contents to your CodeIgniter installation:

```bash
# From the pattern lock module directory
cp -r application/* /path/to/your/codeigniter/application/
```

### Step 2: Database Setup

Choose one of the following methods:

#### Method A: Import SQL File (Recommended)

```bash
mysql -u your_username -p your_database < application/sql/pattern_lock_tables.sql
```

Or using phpMyAdmin:
1. Open phpMyAdmin
2. Select your database
3. Go to "Import" tab
4. Choose `application/sql/pattern_lock_tables.sql`
5. Click "Go"

#### Method B: Use CodeIgniter Migrations

1. Enable migrations in `application/config/migration.php`:
```php
$config['migration_enabled'] = TRUE;
$config['migration_type'] = 'sequential';
```

2. Run migration via controller:
```php
$this->load->library('migration');
if ($this->migration->current() === FALSE) {
    show_error($this->migration->error_string());
} else {
    echo "Migration successful!";
}
```

### Step 3: Configure Encryption

Edit `application/config/config.php`:

```php
// Generate a random 32-character key
$config['encryption_key'] = 'your-random-32-character-key-here';
```

To generate a secure key, you can use:
```php
php -r "echo bin2hex(random_bytes(16));"
```

### Step 4: Configure Pattern Lock Settings

Edit `application/config/pattern_lock.php`:

```php
// Basic settings
$config['pattern_lock_grid_size'] = 3;              // 3x3 grid
$config['pattern_lock_min_points'] = 4;             // Minimum 4 points
$config['pattern_lock_max_failed_attempts'] = 5;    // Lock after 5 failed attempts
$config['pattern_lock_lockout_duration'] = 900;     // 15 minutes lockout

// Email settings
$config['pattern_lock_email_from'] = 'noreply@yourdomain.com';
$config['pattern_lock_email_from_name'] = 'Your App Name';
```

### Step 5: Configure reCAPTCHA (Optional but Recommended)

1. Get reCAPTCHA keys from https://www.google.com/recaptcha/admin
2. Edit `application/config/recaptcha.php`:

```php
$config['recaptcha_version'] = 'v2';
$config['recaptcha_site_key'] = 'your-site-key-here';
$config['recaptcha_secret_key'] = 'your-secret-key-here';
```

### Step 6: Configure Autoload

Edit `application/config/autoload.php`:

```php
// Add to autoloaded libraries
$autoload['libraries'] = array('database', 'session');

// Add to autoloaded helpers
$autoload['helper'] = array('url', 'pattern');
```

### Step 7: Set Up Routes (Optional)

Edit `application/config/routes.php` for friendly URLs:

```php
$route['login'] = 'pattern_auth/login';
$route['pattern-setup'] = 'pattern_auth/setup';
$route['pattern-recover'] = 'pattern_auth/recover';
$route['admin/logs'] = 'pattern_auth/access_logs';
$route['admin/pattern-settings'] = 'pattern_auth/settings';
```

### Step 8: Configure Email (If Using Notifications)

Edit `application/config/email.php` (create if it doesn't exist):

```php
$config['protocol'] = 'smtp';
$config['smtp_host'] = 'your-smtp-host';
$config['smtp_port'] = 587;
$config['smtp_user'] = 'your-email@domain.com';
$config['smtp_pass'] = 'your-password';
$config['mailtype'] = 'html';
$config['charset'] = 'utf-8';
$config['newline'] = "\r\n";
```

## Verification

### Test Database Connection

Create a test controller `application/controllers/Pattern_test.php`:

```php
<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Pattern_test extends CI_Controller {
    
    public function index() {
        $this->load->model('Pattern_model');
        
        // Test database tables
        $settings = $this->Pattern_model->get_all_settings();
        
        if ($settings) {
            echo "✓ Database tables created successfully<br>";
            echo "✓ Settings loaded: " . count($settings) . " items<br>";
        } else {
            echo "✗ Error loading settings<br>";
        }
        
        // Test libraries
        $this->load->library('pattern_lock');
        $test_pattern = [0, 1, 2, 5, 8];
        $hash = $this->pattern_lock->hash_pattern($test_pattern, 3);
        
        if ($hash) {
            echo "✓ Pattern Lock library working<br>";
            echo "✓ Test pattern hash: " . substr($hash, 0, 16) . "...<br>";
        }
        
        // Test helper
        $this->load->helper('pattern');
        echo "✓ Pattern helper loaded<br>";
        echo "✓ Pattern strength label test: " . pattern_strength_label(4) . "<br>";
        
        echo "<br><strong>Installation successful! You can now use Pattern Lock.</strong>";
    }
}
```

Access: `http://yourdomain.com/pattern_test`

### Access Pattern Login

Navigate to: `http://yourdomain.com/pattern_auth/login`

You should see the pattern login interface.

## Integration with Existing User System

### Option 1: Standalone Pattern Auth

Use pattern lock as the primary authentication method.

### Option 2: Hybrid (Password + Pattern)

Allow users to choose between password or pattern:

```php
// In your login controller
public function login() {
    if ($this->input->post('auth_method') == 'pattern') {
        redirect('pattern_auth/login');
    } else {
        // Your existing password login
    }
}
```

### Option 3: Two-Factor with Pattern

Use pattern as a second factor after password:

```php
// After successful password authentication
if (is_pattern_user($user_id)) {
    redirect('pattern_auth/verify');
} else {
    // Normal login flow
}
```

## Common Issues and Solutions

### Issue: "Encryption key not set"
**Solution**: Set `$config['encryption_key']` in `application/config/config.php`

### Issue: Database tables not found
**Solution**: Run the SQL file manually or check migration configuration

### Issue: reCAPTCHA not showing
**Solution**: 
- Check if keys are set in `recaptcha.php`
- Verify domain is registered with Google reCAPTCHA
- Check JavaScript console for errors

### Issue: Email notifications not working
**Solution**:
- Configure email settings in `config/email.php`
- Test email configuration separately
- Check SMTP credentials

### Issue: Patterns not matching
**Solution**:
- Clear browser cache
- Check grid_size is consistent
- Verify encryption key hasn't changed

## Next Steps

1. **Setup a user pattern**: Navigate to `pattern_auth/setup`
2. **Test login**: Try logging in with your pattern
3. **Configure admin access**: Set up admin panel access control
4. **Customize UI**: Modify view files to match your design
5. **Set up monitoring**: Review access logs regularly

## Security Recommendations

1. Use HTTPS in production
2. Set strong encryption key (32+ characters)
3. Enable reCAPTCHA
4. Configure appropriate lockout duration
5. Regularly review access logs
6. Set up email notifications
7. Implement IP whitelist for admin pages
8. Regular database backups

## Support

For help and support:
- Check the main README.md
- Review code comments
- Check CodeIgniter documentation
- Test with the verification controller above

---

Installation complete! Your Pattern Lock module is ready to use.
