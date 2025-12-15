# Pattern Lock Module - Changelog

All notable changes to this project will be documented in this file.

## [1.0.0] - 2024-12-15

### Added
- Initial release of Pattern Lock authentication module for CodeIgniter 3
- Canvas-based pattern drawing interface with touch and mouse support
- User pattern setup with two-step confirmation
- Pattern strength calculator (0-5 scale)
- SHA-256 pattern encryption
- Backup recovery code system
- IP-based brute force protection
- Configurable lockout duration
- Google reCAPTCHA v2 and v3 integration
- Comprehensive access logging system
- Device fingerprinting and tracking
- Geo-location logging support
- Email notifications for lockouts and new devices
- CSV export for access logs
- Admin dashboard for statistics
- Settings management interface
- Active lockout management
- Emergency system-wide lockout
- Automatic log cleanup based on retention policy
- Complete database schema with migrations
- Helper functions for common operations
- Responsive UI design
- Complete API documentation
- Installation guide
- Security best practices documentation

### Configuration Files
- `pattern_lock.php` - Main configuration
- `recaptcha.php` - reCAPTCHA settings

### Libraries
- `Pattern_lock` - Core pattern handling
- `Pattern_logger` - Logging and analytics
- `Recaptcha` - reCAPTCHA integration

### Models
- `Pattern_model` - Database operations

### Controllers
- `Pattern_auth` - Authentication handling

### Views
- `login.php` - Pattern login interface
- `setup.php` - Pattern setup wizard
- `setup_complete.php` - Backup code display
- `recover.php` - Account recovery
- `access_logs.php` - Log viewer
- `settings.php` - Admin settings

### Database Tables
- `user_patterns` - Pattern storage
- `pattern_access_logs` - Access logging
- `pattern_lockouts` - IP lockouts
- `pattern_settings` - System settings
- `pattern_known_devices` - Device tracking

### Features
- Configurable grid size (3x3 to 20x20)
- Minimum pattern points validation
- Pattern strength analysis
- Direction change detection
- Adjacent/non-adjacent point detection
- Straight line penalty
- Real-time visual feedback
- Touch-screen compatible
- Responsive design
- CSRF protection
- XSS protection
- Session management
- Fallback to password authentication
- Multi-language support ready

### Security
- SHA-256 pattern hashing
- Encrypted backup codes
- IP-based lockout
- Failed attempt tracking
- reCAPTCHA integration
- Device fingerprinting
- Secure session handling
- XSS and CSRF protection

### Admin Features
- Real-time statistics dashboard
- Access log filtering
- CSV export
- IP unlock functionality
- Settings management
- System-wide lockout control
- Active lockout monitoring

---

For installation instructions, see INSTALL.md
For usage documentation, see README.md
