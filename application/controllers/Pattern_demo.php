<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Pattern Lock Demo Controller
 * 
 * Demonstration of how to integrate Pattern Lock into your application
 * You can use this as a reference for implementing pattern authentication
 * 
 * @package    Pattern_Lock
 * @subpackage Controllers
 * @category   Examples
 */
class Pattern_demo extends CI_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->library(array('session', 'pattern_lock', 'pattern_logger'));
        $this->load->model('Pattern_model');
        $this->load->helper(array('url', 'pattern'));
    }

    /**
     * Demo home page
     */
    public function index()
    {
        echo "<h1>Pattern Lock Demo</h1>";
        echo "<p>This controller demonstrates various Pattern Lock features.</p>";
        echo "<ul>";
        echo "<li><a href='" . site_url('pattern_demo/test_pattern_hashing') . "'>Test Pattern Hashing</a></li>";
        echo "<li><a href='" . site_url('pattern_demo/test_pattern_validation') . "'>Test Pattern Validation</a></li>";
        echo "<li><a href='" . site_url('pattern_demo/test_strength_calculation') . "'>Test Strength Calculation</a></li>";
        echo "<li><a href='" . site_url('pattern_demo/test_backup_code') . "'>Test Backup Code</a></li>";
        echo "<li><a href='" . site_url('pattern_demo/test_helpers') . "'>Test Helper Functions</a></li>";
        echo "<li><a href='" . site_url('pattern_demo/integration_example') . "'>Integration Example</a></li>";
        echo "</ul>";
        echo "<hr>";
        echo "<p><a href='" . site_url('pattern_auth/login') . "'>Go to Pattern Login</a></p>";
    }

    /**
     * Test pattern hashing
     */
    public function test_pattern_hashing()
    {
        echo "<h2>Pattern Hashing Test</h2>";
        
        $pattern = [0, 1, 2, 5, 8]; // Top-left to bottom-right diagonal
        $grid_size = 3;
        
        echo "<p><strong>Pattern:</strong> " . json_encode($pattern) . "</p>";
        echo "<p><strong>Grid Size:</strong> {$grid_size}x{$grid_size}</p>";
        
        $hash = $this->pattern_lock->hash_pattern($pattern, $grid_size);
        echo "<p><strong>Hash:</strong> <code>{$hash}</code></p>";
        
        // Verify pattern
        $is_valid = $this->pattern_lock->verify_pattern($pattern, $hash, $grid_size);
        echo "<p><strong>Verification:</strong> " . ($is_valid ? '✓ Valid' : '✗ Invalid') . "</p>";
        
        // Test with wrong pattern
        $wrong_pattern = [0, 1, 2, 3, 4];
        $is_valid_wrong = $this->pattern_lock->verify_pattern($wrong_pattern, $hash, $grid_size);
        echo "<p><strong>Wrong Pattern Test:</strong> " . ($is_valid_wrong ? '✗ Should be invalid!' : '✓ Correctly rejected') . "</p>";
        
        echo "<p><a href='" . site_url('pattern_demo') . "'>← Back</a></p>";
    }

    /**
     * Test pattern validation
     */
    public function test_pattern_validation()
    {
        echo "<h2>Pattern Validation Test</h2>";
        
        $test_cases = array(
            array('pattern' => [0, 1, 2, 5, 8], 'description' => 'Valid diagonal pattern'),
            array('pattern' => [0, 1], 'description' => 'Too short (only 2 points)'),
            array('pattern' => [0, 1, 1, 2], 'description' => 'Has duplicate points'),
            array('pattern' => [0, 1, 2, 3, 4, 5, 6, 7, 8], 'description' => 'All 9 points'),
            array('pattern' => [], 'description' => 'Empty pattern'),
        );
        
        echo "<table border='1' cellpadding='10' style='border-collapse: collapse;'>";
        echo "<tr><th>Pattern</th><th>Description</th><th>Valid?</th><th>Errors</th></tr>";
        
        foreach ($test_cases as $case) {
            $result = $this->pattern_lock->validate_pattern($case['pattern'], 3);
            echo "<tr>";
            echo "<td><code>" . json_encode($case['pattern']) . "</code></td>";
            echo "<td>" . $case['description'] . "</td>";
            echo "<td>" . ($result['valid'] ? '✓ Yes' : '✗ No') . "</td>";
            echo "<td>" . (!empty($result['errors']) ? implode(', ', $result['errors']) : '-') . "</td>";
            echo "</tr>";
        }
        
        echo "</table>";
        echo "<p><a href='" . site_url('pattern_demo') . "'>← Back</a></p>";
    }

    /**
     * Test strength calculation
     */
    public function test_strength_calculation()
    {
        echo "<h2>Pattern Strength Calculation Test</h2>";
        
        $test_patterns = array(
            array('pattern' => [0, 1, 2, 5], 'description' => 'Simple L-shape'),
            array('pattern' => [0, 1, 2, 5, 8], 'description' => 'Diagonal line'),
            array('pattern' => [0, 4, 8, 6, 2], 'description' => 'X pattern'),
            array('pattern' => [0, 1, 4, 7, 8, 5, 2, 3, 6], 'description' => 'Complex spiral'),
            array('pattern' => [0, 3, 6, 7, 8], 'description' => 'L-shape with corner'),
        );
        
        echo "<table border='1' cellpadding='10' style='border-collapse: collapse;'>";
        echo "<tr><th>Pattern</th><th>Description</th><th>Points</th><th>Strength</th><th>Label</th></tr>";
        
        foreach ($test_patterns as $case) {
            $strength = $this->pattern_lock->calculate_pattern_strength($case['pattern'], 3);
            $label = pattern_strength_label($strength);
            $color = pattern_strength_color($strength);
            
            echo "<tr>";
            echo "<td><code>" . json_encode($case['pattern']) . "</code></td>";
            echo "<td>" . $case['description'] . "</td>";
            echo "<td>" . count($case['pattern']) . "</td>";
            echo "<td>{$strength}/5</td>";
            echo "<td style='color: " . ($color == 'success' ? 'green' : ($color == 'danger' ? 'red' : ($color == 'warning' ? 'orange' : 'blue'))) . "'>{$label}</td>";
            echo "</tr>";
        }
        
        echo "</table>";
        echo "<p><a href='" . site_url('pattern_demo') . "'>← Back</a></p>";
    }

    /**
     * Test backup code generation
     */
    public function test_backup_code()
    {
        echo "<h2>Backup Code Test</h2>";
        
        // Generate codes
        echo "<h3>Generated Backup Codes:</h3>";
        echo "<ul>";
        for ($i = 0; $i < 5; $i++) {
            $code = $this->pattern_lock->generate_backup_code();
            echo "<li><code>{$code}</code></li>";
        }
        echo "</ul>";
        
        // Test encryption
        echo "<h3>Encryption Test:</h3>";
        $original_code = $this->pattern_lock->generate_backup_code();
        echo "<p><strong>Original:</strong> <code>{$original_code}</code></p>";
        
        $encrypted = $this->pattern_lock->encrypt_backup_code($original_code);
        echo "<p><strong>Encrypted:</strong> <code>" . base64_encode($encrypted) . "</code></p>";
        
        $decrypted = $this->pattern_lock->decrypt_backup_code($encrypted);
        echo "<p><strong>Decrypted:</strong> <code>{$decrypted}</code></p>";
        
        $match = ($original_code === $decrypted);
        echo "<p><strong>Match:</strong> " . ($match ? '✓ Yes' : '✗ No') . "</p>";
        
        // Test verification
        echo "<h3>Verification Test:</h3>";
        $is_valid = $this->pattern_lock->verify_backup_code($original_code, $encrypted);
        echo "<p>Verify correct code: " . ($is_valid ? '✓ Valid' : '✗ Invalid') . "</p>";
        
        $is_valid_wrong = $this->pattern_lock->verify_backup_code('XXXX-XXXX-XXXX-XXXX', $encrypted);
        echo "<p>Verify wrong code: " . ($is_valid_wrong ? '✗ Should be invalid!' : '✓ Correctly rejected') . "</p>";
        
        echo "<p><a href='" . site_url('pattern_demo') . "'>← Back</a></p>";
    }

    /**
     * Test helper functions
     */
    public function test_helpers()
    {
        echo "<h2>Helper Functions Test</h2>";
        
        echo "<h3>Pattern Strength Helpers:</h3>";
        echo "<table border='1' cellpadding='10' style='border-collapse: collapse;'>";
        echo "<tr><th>Strength</th><th>Label</th><th>Color</th></tr>";
        for ($i = 0; $i <= 5; $i++) {
            echo "<tr>";
            echo "<td>{$i}</td>";
            echo "<td>" . pattern_strength_label($i) . "</td>";
            echo "<td>" . pattern_strength_color($i) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        echo "<h3>Format Backup Code:</h3>";
        $code = 'ABCD1234EFGH5678';
        echo "<p>Raw: <code>{$code}</code></p>";
        echo "<p>Formatted: <code>" . format_backup_code($code) . "</code></p>";
        
        echo "<h3>Time Formatting:</h3>";
        $seconds_tests = array(30, 90, 300, 900, 3600, 7200);
        echo "<ul>";
        foreach ($seconds_tests as $seconds) {
            echo "<li>{$seconds} seconds = " . format_time_remaining($seconds) . "</li>";
        }
        echo "</ul>";
        
        echo "<p><a href='" . site_url('pattern_demo') . "'>← Back</a></p>";
    }

    /**
     * Integration example
     */
    public function integration_example()
    {
        echo "<h2>Integration Example</h2>";
        
        echo "<h3>Example: Protecting a Dashboard</h3>";
        echo "<pre><code>";
        echo htmlspecialchars("
<?php
class Dashboard extends CI_Controller {
    
    public function __construct() {
        parent::__construct();
        \$this->load->helper('pattern');
        
        // Check if user is logged in via pattern
        if (!\$this->session->userdata('pattern_lock_user')) {
            redirect('pattern_auth/login');
        }
    }
    
    public function index() {
        \$user = \$this->session->userdata('pattern_lock_user');
        echo 'Welcome, ' . \$user['username'];
    }
}
");
        echo "</code></pre>";
        
        echo "<h3>Example: Optional Pattern Authentication</h3>";
        echo "<pre><code>";
        echo htmlspecialchars("
<?php
class Login extends CI_Controller {
    
    public function index() {
        // Check if user has pattern enabled
        \$username = \$this->input->post('username');
        
        if (is_pattern_user(\$user_id)) {
            // Redirect to pattern login
            redirect('pattern_auth/login');
        } else {
            // Use regular password login
            // Your password auth code here
        }
    }
}
");
        echo "</code></pre>";
        
        echo "<h3>Example: Checking Lockout Status</h3>";
        echo "<pre><code>";
        echo htmlspecialchars("
<?php
if (is_pattern_locked()) {
    \$remaining = get_lockout_time_remaining();
    echo 'Account locked for ' . format_time_remaining(\$remaining);
}
");
        echo "</code></pre>";
        
        echo "<p><a href='" . site_url('pattern_demo') . "'>← Back</a></p>";
    }
}
