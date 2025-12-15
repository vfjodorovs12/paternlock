# Pattern Lock Authentication Module for CodeIgniter 3

A complete pattern-based authentication system for CodeIgniter 3, providing Android-style graphical pattern login as an alternative to traditional password authentication.

## Features

### 🔐 Pattern Authentication
- **Graphical Pattern Login**: Draw patterns instead of typing passwords
- **Configurable Grid Size**: Support for 3x3 to 20x20 grids
- **SHA-256 Encryption**: Secure pattern hashing
- **Pattern Strength Analysis**: Real-time pattern complexity validation
- **Backup Recovery Codes**: Secure account recovery mechanism

### 🛡️ Security Features
- **Brute Force Protection**: IP-based lockout after failed attempts
- **Google reCAPTCHA Integration**: Support for both v2 and v3
- **Device Tracking**: Monitor and trust known devices
- **CSRF Protection**: All forms protected against CSRF attacks
- **XSS Protection**: Secure output rendering

### 📊 Logging & Analytics
- **Detailed Access Logs**: Track all authentication attempts
- **IP & Geo-location Logging**: Monitor login locations
- **Device Fingerprinting**: Identify unique devices
- **CSV Export**: Export logs for analysis
- **Automatic Log Cleanup**: Configurable retention period

### 📧 Notifications
- **Email Alerts**: Notifications for lockouts and new device logins
- **Customizable Templates**: Easy to customize email content

### 🎨 User Interface
- **Responsive Design**: Works on desktop, tablet, and mobile
- **Touch Support**: Full touch-screen compatibility
- **Canvas-based Drawing**: Smooth pattern drawing with visual feedback
- **Modern UI**: Clean, professional interface

## Requirements

- PHP 7.2 or higher
- CodeIgniter 3.x
- MySQL 5.6+ or MariaDB 10.0+
- OpenSSL PHP extension (for encryption)
- cURL PHP extension (for reCAPTCHA)

## Installation

### 1. Database Setup

Create the necessary database tables using one of these methods:

**Option A: Using SQL file**
```bash
mysql -u username -p database_name < application/sql/pattern_lock_tables.sql
```

**Option B: Using CodeIgniter Migrations**
```php
// In your controller or CLI
$this->load->library('migration');
if ($this->migration->current() === FALSE) {
    show_error($this->migration->error_string());
}
```

### 2. Configuration

#### Enable Encryption Library
Edit `application/config/config.php`:
```php
$config['encryption_key'] = 'your-32-character-encryption-key-here';
```

#### Configure Pattern Lock Settings
Edit `application/config/pattern_lock.php` to customize:
- Grid size and minimum points
- Brute force protection settings
- Email notification preferences
- UI customization options

#### Configure reCAPTCHA (Optional)
Edit `application/config/recaptcha.php`:
```php
$config['recaptcha_site_key'] = 'your-site-key';
$config['recaptcha_secret_key'] = 'your-secret-key';
$config['recaptcha_version'] = 'v2'; // or 'v3'
```

Get your reCAPTCHA keys at: https://www.google.com/recaptcha/admin

### 3. Load Required Files

Update `application/config/autoload.php`:
```php
$autoload['libraries'] = array('database', 'session');
$autoload['helper'] = array('url', 'pattern');
```

### 4. Routes Configuration

Add to `application/config/routes.php`:
```php
$route['pattern-login'] = 'pattern_auth/login';
$route['pattern-setup'] = 'pattern_auth/setup';
$route['pattern-recover'] = 'pattern_auth/recover';
```

## Usage

### User Pattern Setup

1. User must be logged in with traditional authentication
2. Navigate to pattern setup page: `pattern_auth/setup`
3. Draw a pattern (minimum 4 points by default)
4. Confirm the pattern
5. Save the displayed backup code in a secure location

```php
// Example: Adding setup link to user dashboard
<a href="<?php echo site_url('pattern_auth/setup'); ?>">Setup Pattern Lock</a>
```

### Pattern Login

Users can now login using their pattern:

```php
// Direct users to pattern login
redirect('pattern_auth/login');
```

### Checking Authentication Status

```php
// Check if user is logged in via pattern
$pattern_user = $this->session->userdata('pattern_lock_user');
if ($pattern_user) {
    echo "Welcome, " . $pattern_user['username'];
}
```

### Using Helper Functions

```php
// Load helper
$this->load->helper('pattern');

// Check if IP is locked
if (is_pattern_locked()) {
    echo "You are temporarily locked out";
}

// Get pattern strength label
$strength = 4;
echo pattern_strength_label($strength); // Output: "Strong"

// Check system lockout
if (check_system_lockout()) {
    echo "System is in lockout mode";
}

// Get/Set settings
$max_attempts = get_pattern_setting('max_failed_attempts', 5);
set_pattern_setting('max_failed_attempts', 10);
```

## Admin Features

### Access Logs

View all authentication attempts:
```
URL: pattern_auth/access_logs
```

Features:
- Filter by username, status, date range
- Export to CSV
- Pagination
- Detailed attempt information

### Settings Dashboard

Manage system settings:
```
URL: pattern_auth/settings
```

Features:
- View authentication statistics
- Manage active IP lockouts
- Configure system settings
- Emergency system-wide lockout

## API Documentation

### Pattern_lock Library

```php
$this->load->library('pattern_lock');

// Hash a pattern
$pattern = [0, 1, 2, 5, 8]; // Array of point indices
$hash = $this->pattern_lock->hash_pattern($pattern, 3); // grid_size = 3

// Validate pattern
$validation = $this->pattern_lock->validate_pattern($pattern, 3);
if ($validation['valid']) {
    // Pattern is valid
} else {
    // Show errors: $validation['errors']
}

// Calculate strength (0-5)
$strength = $this->pattern_lock->calculate_pattern_strength($pattern, 3);

// Generate backup code
$code = $this->pattern_lock->generate_backup_code();

// Encrypt/Decrypt backup code
$encrypted = $this->pattern_lock->encrypt_backup_code($code);
$decrypted = $this->pattern_lock->decrypt_backup_code($encrypted);

// Verify pattern
$is_valid = $this->pattern_lock->verify_pattern($pattern, $stored_hash, 3);

// Verify backup code
$is_valid = $this->pattern_lock->verify_backup_code($input_code, $stored_encrypted);
```

### Pattern_logger Library

```php
$this->load->library('pattern_logger');

// Log an authentication attempt
$this->pattern_logger->log_attempt(array(
    'user_id' => 1,
    'username' => 'john',
    'status' => 'success', // success, failed, blocked, recovered
    'attempt_type' => 'pattern' // pattern or backup_code
));

// Get logs with filters
$logs = $this->pattern_logger->get_logs(array(
    'username' => 'john',
    'status' => 'failed',
    'date_from' => '2024-01-01'
), 50, 0);

// Get statistics
$stats = $this->pattern_logger->get_statistics($user_id);

// Export to CSV
$csv = $this->pattern_logger->export_to_csv($filters);

// Clean old logs
$deleted = $this->pattern_logger->clean_old_logs();

// Device management
$is_known = $this->pattern_logger->is_known_device($user_id);
$this->pattern_logger->register_device($user_id, $trusted = false);
```

### Pattern_model

```php
$this->load->model('Pattern_model');

// Get user pattern
$pattern = $this->Pattern_model->get_user_pattern($user_id);
$pattern = $this->Pattern_model->get_user_pattern_by_username($username);

// Save/Update pattern
$data = array(
    'user_id' => 1,
    'username' => 'john',
    'pattern_hash' => $hash,
    'grid_size' => 3,
    'backup_code' => $encrypted_code,
    'pattern_strength' => 4,
    'is_active' => 1
);
$this->Pattern_model->save_user_pattern($data);

// Lockout management
$lockout = $this->Pattern_model->get_lockout($ip);
$is_locked = $this->Pattern_model->is_ip_locked($ip);
$this->Pattern_model->increment_failed_attempts($ip);
$this->Pattern_model->lock_ip($ip, $duration, $permanent);
$this->Pattern_model->reset_lockout($ip);

// Settings
$setting = $this->Pattern_model->get_setting('max_failed_attempts');
$this->Pattern_model->update_setting('max_failed_attempts', 10);
```

## Configuration Options

### Grid Settings
- `pattern_lock_grid_size`: Default grid size (3-20)
- `pattern_lock_min_points`: Minimum points required in pattern

### Security Settings
- `pattern_lock_hash_algorithm`: Hash algorithm (default: sha256)
- `pattern_lock_backup_code_length`: Length of backup code

### Brute Force Protection
- `pattern_lock_max_failed_attempts`: Maximum failed attempts before lockout
- `pattern_lock_lockout_duration`: Lockout duration in seconds

### reCAPTCHA
- `pattern_lock_recaptcha_enabled`: Enable/disable reCAPTCHA
- `pattern_lock_recaptcha_threshold`: Failed attempts before showing CAPTCHA

### Logging
- `pattern_lock_enable_logging`: Enable access logging
- `pattern_lock_log_retention_days`: Days to keep logs (0 = forever)

### Email Notifications
- `pattern_lock_email_on_lockout`: Email on account lockout
- `pattern_lock_email_on_new_device`: Email on new device login

## Security Best Practices

1. **Use Strong Encryption Keys**: Generate a random 32-character encryption key
2. **Enable reCAPTCHA**: Protect against automated attacks
3. **Configure Lockout Duration**: Balance security and user experience
4. **Regular Log Review**: Monitor access logs for suspicious activity
5. **Backup Code Storage**: Instruct users to store backup codes securely
6. **HTTPS Only**: Always use HTTPS in production
7. **Regular Updates**: Keep CodeIgniter and dependencies updated

## Troubleshooting

### Pattern Not Saving
- Check encryption key is set in config.php
- Verify database tables are created
- Check PHP error logs

### reCAPTCHA Not Working
- Verify site and secret keys are correct
- Check domain is registered with Google reCAPTCHA
- Ensure cURL extension is enabled

### Email Notifications Not Sending
- Configure CodeIgniter email settings in config/email.php
- Update email addresses in pattern_lock.php config
- Test email configuration separately

## License

This project is provided as-is for use with CodeIgniter 3 applications.

## Support

For issues, questions, or contributions, please open an issue in the repository.

## Credits

Developed for CodeIgniter 3 framework, inspired by Android pattern lock systems and WordPress Secure Pattern Lock plugin.

---

**Version**: 1.0  
**Last Updated**: December 2024  
**Compatibility**: CodeIgniter 3.x