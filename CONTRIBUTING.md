# Contributing to Pattern Lock Module

Thank you for considering contributing to the Pattern Lock module for CodeIgniter 3!

## How to Contribute

### Reporting Bugs

If you find a bug, please create an issue with:
- Clear description of the bug
- Steps to reproduce
- Expected behavior
- Actual behavior
- CodeIgniter version
- PHP version
- Browser (if UI-related)
- Screenshots (if applicable)

### Suggesting Enhancements

We welcome feature suggestions! Please create an issue with:
- Clear description of the feature
- Use case and benefits
- Possible implementation approach
- Any mockups or examples

### Code Contributions

1. **Fork the Repository**
   ```bash
   git clone https://github.com/yourusername/paternlock.git
   cd paternlock
   ```

2. **Create a Branch**
   ```bash
   git checkout -b feature/your-feature-name
   ```

3. **Make Your Changes**
   - Follow CodeIgniter coding standards
   - Write clean, documented code
   - Test your changes thoroughly

4. **Commit Your Changes**
   ```bash
   git add .
   git commit -m "Add: Brief description of your changes"
   ```

5. **Push to Your Fork**
   ```bash
   git push origin feature/your-feature-name
   ```

6. **Create a Pull Request**
   - Provide a clear title and description
   - Reference any related issues
   - Include screenshots for UI changes

## Coding Standards

### PHP Code Style

Follow CodeIgniter's coding standards:

```php
<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Class description
 * 
 * @package    Pattern_Lock
 * @subpackage Category
 * @category   Type
 */
class Example_class {

    /**
     * Method description
     * 
     * @param string $param Parameter description
     * @return bool Return value description
     */
    public function example_method($param)
    {
        // Use tabs for indentation
        if ($condition) {
            // Code here
        }
        
        return TRUE;
    }
}
```

### JavaScript Code Style

- Use ES5 for compatibility
- Use clear variable names
- Comment complex logic
- Maintain consistent indentation (4 spaces)

### CSS Code Style

- Use meaningful class names
- Group related properties
- Comment sections
- Maintain consistent indentation

## Testing

Before submitting:

1. **Test Functionality**
   - Test all modified features
   - Test on different browsers (if UI changes)
   - Test with different PHP versions

2. **Security**
   - Check for SQL injection vulnerabilities
   - Verify XSS protection
   - Test CSRF protection
   - Validate input sanitization

3. **Performance**
   - Check for N+1 queries
   - Test with large datasets
   - Verify resource usage

## Documentation

- Update README.md if adding features
- Add inline code comments
- Update CHANGELOG.md
- Include PHPDoc blocks for new methods

## Security Issues

If you discover a security vulnerability:

1. **DO NOT** create a public issue
2. Email details privately to the maintainers
3. Allow time for a fix before public disclosure

## Questions?

Feel free to create an issue for:
- General questions
- Usage help
- Implementation guidance

## Code of Conduct

- Be respectful and inclusive
- Provide constructive feedback
- Focus on what's best for the project
- Help others learn and grow

## License

By contributing, you agree that your contributions will be licensed under the MIT License.

---

Thank you for contributing to Pattern Lock! 🎉
