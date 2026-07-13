# Contributing to Restaurant Management System

Thank you for your interest in contributing to the Restaurant Management System! This document provides guidelines and instructions for contributing.

## Code of Conduct

Be respectful, inclusive, and professional. We're committed to providing a welcoming environment for all contributors.

## Getting Started

### 1. Fork the Repository
- Click the "Fork" button on GitHub
- Clone your fork to your local machine:
  ```bash
  git clone https://github.com/yourusername/restaurant-management-system.git
  cd restaurant-management-system
  ```

### 2. Create a Feature Branch
```bash
git checkout -b feature/your-feature-name
```

Use descriptive branch names:
- `feature/add-discount-system`
- `bugfix/fix-order-calculation`
- `docs/improve-readme`
- `refactor/clean-up-database`

### 3. Set Up Development Environment
```bash
# Create .env file with your local database credentials
cp .env.example .env

# Install dependencies (if using Composer)
composer install

# Create sample database
mysql restaurant_management_db < database/schema.sql
mysql restaurant_management_db < database/sample_data.sql
```

## Coding Standards

### PHP Code Style
- Follow PSR-12 coding standards
- Use meaningful variable and function names
- Add comments for complex logic
- Maximum line length: 120 characters

```php
<?php
// ✅ Good
class OrderService {
    private $connection;
    
    public function __construct($db) {
        $this->connection = $db;
    }
    
    public function createOrder(array $orderData): int {
        // Implementation
        return $orderId;
    }
}

// ❌ Avoid
class os {
    private $c;
    public function co($o) {
        // unclear what this does
    }
}
```

### JavaScript Code Style
- Use ES6+ features where appropriate
- Use const/let instead of var
- Meaningful function names
- Add JSDoc comments for complex functions

```javascript
// ✅ Good
function validateEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

// ❌ Avoid
function ve(e) {
    return /^.+@.+\..+$/.test(e);
}
```

### CSS/HTML
- Use Bootstrap 5 utility classes
- Maintain consistent indentation (4 spaces for PHP, 2 spaces for HTML/CSS)
- Use semantic HTML5 elements
- Mobile-first responsive design

```html
<!-- ✅ Good -->
<div class="card p-4 rounded-3">
    <h2 class="fw-semibold mb-3">Menu Items</h2>
    <table class="table table-hover">
        <!-- content -->
    </table>
</div>

<!-- ❌ Avoid -->
<div style="border: 1px solid gray; padding: 20px; border-radius: 5px;">
    <h2 style="font-weight: bold; margin-bottom: 15px;">Menu Items</h2>
    <table border="1">
        <!-- content -->
    </table>
</div>
```

## Security Requirements

All contributions must follow security best practices:

### ✅ Required
- [ ] Use prepared statements for all database queries
- [ ] Escape all user input with `htmlspecialchars()` or sanitization functions
- [ ] Include CSRF tokens on all forms
- [ ] Validate and sanitize server-side
- [ ] Use `password_hash()` for passwords
- [ ] Log security events

### ❌ Not Allowed
- Eval/include user input
- Dynamic SQL without prepared statements
- Unescaped output
- Hardcoded credentials
- Unnecessary file uploads

## Testing

### Manual Testing Checklist
Before submitting a PR, test:

- [ ] Feature works on your local environment
- [ ] No PHP errors or warnings
- [ ] No JavaScript console errors
- [ ] Responsive design (mobile, tablet, desktop)
- [ ] Forms validate correctly
- [ ] Database transactions work
- [ ] CSRF protection works
- [ ] Authentication works

### Automated Testing (if applicable)
```bash
# Run any available tests
./vendor/bin/phpunit

# Check PHP syntax
php -l your_file.php

# Run static analysis
./vendor/bin/phpstan analyse
```

## Commit Messages

Write clear, descriptive commit messages following this format:

```
[TYPE] Brief description

Detailed explanation if needed. Explain WHY the change was made,
not just WHAT was changed.

Fixes #123
```

**Types:**
- `feat` - New feature
- `fix` - Bug fix
- `docs` - Documentation
- `refactor` - Code refactoring
- `perf` - Performance improvement
- `test` - Test changes
- `chore` - Build/tool changes

**Examples:**
```
[feat] Add inventory management module

Implements tracking of menu item quantities and low-stock alerts.
Includes automated reorder notifications.

Fixes #456

[fix] Correct order total calculation for discounts

Order totals were not accounting for percentage discounts correctly.
Now uses proper decimal arithmetic to prevent rounding errors.

[docs] Update installation guide for Windows users

Added PowerShell commands and screen captures for clarity.
```

## Pull Request Process

### 1. Before Submitting
- Ensure all tests pass
- Update documentation if needed
- Check for conflicts with main branch
- Run code style checks

### 2. Create Pull Request
```bash
git push origin feature/your-feature-name
```

Then create a PR on GitHub with:

**Title:** Clear, descriptive title
```
Add session timeout functionality
Fix calculation bug in order totals
Update documentation for security features
```

**Description:**
```markdown
## Description
Brief explanation of changes

## Type
- [ ] Bug fix
- [ ] New feature
- [ ] Breaking change
- [ ] Documentation update

## Related Issues
Fixes #123

## How to Test
1. Step 1
2. Step 2
3. Expected result

## Checklist
- [x] Code follows style guidelines
- [x] Tested locally
- [x] Documentation updated
- [x] No breaking changes
- [x] CSRF/Security reviewed
```

### 3. Code Review
- Address reviewer feedback
- Make requested changes in new commits
- Tag reviewers for feedback

### 4. Merge
Maintainers will merge after approval.

## Reporting Bugs

### Security Issues
⚠️ Do NOT create a public issue. Email security issues to: security@example.com

### Regular Bugs

Create an issue with:
1. **Title:** Brief description
2. **Description:** What happened?
3. **Steps to Reproduce:** How to trigger the bug
4. **Expected Behavior:** What should happen
5. **Actual Behavior:** What actually happens
6. **Environment:** OS, PHP version, browser, etc.
7. **Screenshots:** If applicable

**Example:**
```markdown
**Title:** Order total incorrect when applying discount

**Description:**
Order totals display incorrect amount when a percentage discount is applied.

**Steps to Reproduce:**
1. Create order with 2 items ($10 each = $20)
2. Apply 10% discount
3. View order total

**Expected:** $18.00
**Actual:** $17.93

**Environment:**
- OS: Windows 10
- PHP: 8.1
- Browser: Chrome 120
```

## Feature Requests

Create an issue with:
1. **Title:** Feature name
2. **Description:** Detailed explanation
3. **Use Case:** Why is this needed?
4. **Proposed Solution:** How should it work?
5. **Alternatives:** Other approaches considered

**Example:**
```markdown
**Title:** Add SMS notifications for order status updates

**Description:**
Send customers SMS notifications when their order status changes.

**Use Case:**
Customers often miss email notifications. SMS would provide immediate alerts.

**Proposed Solution:**
- Integrate Twilio API
- Add SMS settings to admin panel
- Queue SMS sending for reliability
- Add opt-in/opt-out to customer profile
```

## Documentation

### When to Update Documentation
- New features or changes to existing features
- API changes or new endpoints
- Configuration changes
- Security considerations

### Where to Update
- README.md - Project overview and quick start
- docs/setup_guide.md - Installation instructions
- docs/database_schema.md - Database changes
- docs/function_reference.md - New functions/APIs
- Code comments - Complex logic

### Documentation Standards
- Use clear, concise language
- Include examples
- Keep it up-to-date
- Use proper Markdown formatting
- Link to related sections

## Code Review Guidelines

### What We Look For
- ✅ Follows coding standards
- ✅ Security best practices followed
- ✅ No breaking changes
- ✅ Tests pass
- ✅ Documentation updated
- ✅ Clear commit messages
- ✅ No code duplication

### Common Review Comments
- "Please add comments explaining this logic"
- "This could be simplified using..."
- "Security concern: should use prepared statements"
- "Add validation for edge cases"
- "Update documentation"

### Responding to Feedback
- Be professional and open to suggestions
- Ask for clarification if needed
- Implement changes promptly
- Thank reviewers for their time

## Development Tips

### Useful Commands
```bash
# Set up pre-commit hooks
cp scripts/pre-commit.sh .git/hooks/pre-commit
chmod +x .git/hooks/pre-commit

# Check PHP syntax
php -l pages/dashboard/dashboard.php

# Format code
./vendor/bin/phpcbf --standard=PSR12 pages/

# Run tests
./vendor/bin/phpunit

# Check for issues
./vendor/bin/phpstan analyse

# View local changes
git diff

# Sync with upstream
git fetch upstream
git rebase upstream/main
```

### Debugging
```php
// Log to file
logError("Debug: " . var_export($data, true));

// Temporary debugging
error_log("Message: " . print_r($var, true));
```

### Database
```bash
# Backup database
mysqldump -u root -p restaurant_management_db > backup.sql

# Restore database
mysql -u root -p restaurant_management_db < backup.sql

# Check table structure
mysql -u root -p -e "DESC restaurant_management_db.orders;"
```

## Getting Help

- **GitHub Issues:** Ask questions or report problems
- **GitHub Discussions:** General questions and ideas
- **Documentation:** Check docs/ folder
- **Code Comments:** Most complex code is commented

## Recognition

Contributors will be recognized in:
- CONTRIBUTORS.md file
- GitHub contributors page
- Project releases

## Questions?

Feel free to:
1. Check existing issues/discussions
2. Review documentation
3. Ask in an issue comment
4. Reach out to maintainers

---

## Summary

1. Fork and create a feature branch
2. Follow coding standards
3. Make changes with good commit messages
4. Test thoroughly
5. Submit a pull request with clear description
6. Respond to code review
7. Celebrate your contribution! 🎉

Thank you for contributing to make this project better!
