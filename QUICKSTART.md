# Pattern Lock - Quick Start Guide

Get up and running with Pattern Lock in 5 minutes!

## Prerequisites
- CodeIgniter 3.x installed
- MySQL/MariaDB database
- PHP 7.2+

## Installation (5 Steps)

### 1. Copy Files
```bash
# Copy all files to your CodeIgniter installation
cp -r application/* /path/to/codeigniter/application/
```

### 2. Import Database
```bash
mysql -u username -p database_name < application/sql/pattern_lock_tables.sql
```

### 3. Configure Encryption Key
Edit `application/config/config.php`:
```php
$config['encryption_key'] = 'your-random-32-char-key';
```
Generate key: `php -r "echo bin2hex(random_bytes(16));"`

### 4. Update Autoload
Edit `application/config/autoload.php`:
```php
$autoload['libraries'] = array('database', 'session');
$autoload['helper'] = array('url', 'pattern');
```

### 5. Test Installation
Navigate to: `http://yourdomain.com/pattern_auth/login`

## First Pattern Setup

1. Go to: `http://yourdomain.com/pattern_auth/setup`
2. Enter a username
3. Draw your pattern (minimum 4 points)
4. Confirm your pattern
5. **IMPORTANT:** Save the backup code shown!

## Basic Usage

### Login with Pattern
```
URL: http://yourdomain.com/pattern_auth/login
```

### Recover Access
```
URL: http://yourdomain.com/pattern_auth/recover
Use your username + backup code
```

### Admin Dashboard
```
Settings: http://yourdomain.com/pattern_auth/settings
Logs: http://yourdomain.com/pattern_auth/access_logs
```

## Protect Your Pages

Add to your controller:
```php
public function __construct() {
    parent::__construct();
    $this->load->helper('pattern');
    
    if (!$this->session->userdata('pattern_lock_user')) {
        redirect('pattern_auth/login');
    }
}
```

## Optional: Configure reCAPTCHA

1. Get keys: https://www.google.com/recaptcha/admin
2. Edit `application/config/recaptcha.php`:
```php
$config['recaptcha_site_key'] = 'your-site-key';
$config['recaptcha_secret_key'] = 'your-secret-key';
```

## Demo & Examples

Visit the demo controller for examples:
```
http://yourdomain.com/pattern_demo
```

## Common Settings

Edit `application/config/pattern_lock.php`:

```php
// Grid size (3x3 to 20x20)
$config['pattern_lock_grid_size'] = 3;

// Minimum points in pattern
$config['pattern_lock_min_points'] = 4;

// Max failed attempts before lockout
$config['pattern_lock_max_failed_attempts'] = 5;

// Lockout duration (seconds)
$config['pattern_lock_lockout_duration'] = 900; // 15 minutes
```

## Troubleshooting

**"Encryption key not set"**
- Set encryption key in `config/config.php`

**"Table doesn't exist"**
- Import `application/sql/pattern_lock_tables.sql`

**Pattern not working**
- Clear browser cache
- Check browser console for errors
- Verify grid size setting

## Need Help?

- 📖 Full docs: See `README.md`
- 🔧 Installation: See `INSTALL.md`  
- 💡 Examples: Visit `/pattern_demo`
- 🐛 Issues: GitHub Issues

## Next Steps

1. ✅ Test pattern login
2. ✅ Configure admin access
3. ✅ Set up email notifications
4. ✅ Enable reCAPTCHA
5. ✅ Customize UI colors
6. ✅ Review security settings

---

**You're all set! Enjoy secure pattern authentication! 🔐**
