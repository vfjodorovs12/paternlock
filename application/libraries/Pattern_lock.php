<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Pattern Lock Library
 * 
 * Core library for pattern lock authentication system
 * Handles pattern validation, encryption, and strength analysis
 * 
 * @package    Pattern_Lock
 * @subpackage Libraries
 * @category   Authentication
 * @author     Pattern Lock Team
 * @version    1.0
 */
class Pattern_lock {

    protected $CI;
    protected $config = array();

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->CI =& get_instance();
        $this->CI->load->config('pattern_lock');
        $this->CI->load->model('Pattern_model');
        
        // Load configuration
        $config_keys = array(
            'pattern_lock_grid_size',
            'pattern_lock_min_grid_size',
            'pattern_lock_max_grid_size',
            'pattern_lock_min_points',
            'pattern_lock_hash_algorithm',
            'pattern_lock_backup_code_length',
            'pattern_lock_strength_rules'
        );
        
        foreach ($config_keys as $key) {
            $this->config[$key] = $this->CI->config->item($key);
        }
    }

    /**
     * Generate pattern hash from array of points
     * 
     * @param array $pattern Array of point indices (e.g., [0, 1, 2, 5, 8])
     * @param int $grid_size Grid size (default: 3)
     * @return string SHA-256 hash of pattern
     */
    public function hash_pattern($pattern, $grid_size = 3)
    {
        if (!is_array($pattern) || empty($pattern)) {
            return FALSE;
        }

        // Sort pattern to normalize it (order matters, so we use implode)
        $pattern_string = implode('-', $pattern);
        
        // Add grid size to the hash to prevent same pattern on different grids from matching
        $pattern_string .= '|grid:' . $grid_size;
        
        return hash($this->config['pattern_lock_hash_algorithm'], $pattern_string);
    }

    /**
     * Validate pattern complexity and rules
     * 
     * @param array $pattern Array of point indices
     * @param int $grid_size Grid size
     * @return array Array with 'valid' boolean and 'errors' array
     */
    public function validate_pattern($pattern, $grid_size = 3)
    {
        $result = array('valid' => TRUE, 'errors' => array());

        // Check if pattern is array
        if (!is_array($pattern) || empty($pattern)) {
            $result['valid'] = FALSE;
            $result['errors'][] = 'Pattern cannot be empty';
            return $result;
        }

        // Check minimum points
        $min_points = $this->config['pattern_lock_min_points'];
        if (count($pattern) < $min_points) {
            $result['valid'] = FALSE;
            $result['errors'][] = "Pattern must have at least {$min_points} points";
        }

        // Check for duplicate points
        if (count($pattern) !== count(array_unique($pattern))) {
            $result['valid'] = FALSE;
            $result['errors'][] = 'Pattern cannot have duplicate points';
        }

        // Validate grid size
        if ($grid_size < $this->config['pattern_lock_min_grid_size'] || 
            $grid_size > $this->config['pattern_lock_max_grid_size']) {
            $result['valid'] = FALSE;
            $result['errors'][] = 'Invalid grid size';
        }

        // Check if points are within grid bounds
        $max_index = ($grid_size * $grid_size) - 1;
        foreach ($pattern as $point) {
            if ($point < 0 || $point > $max_index) {
                $result['valid'] = FALSE;
                $result['errors'][] = 'Pattern contains invalid point indices';
                break;
            }
        }

        return $result;
    }

    /**
     * Calculate pattern strength (0-5 scale)
     * 
     * @param array $pattern Array of point indices
     * @param int $grid_size Grid size
     * @return int Strength score (0-5)
     */
    public function calculate_pattern_strength($pattern, $grid_size = 3)
    {
        if (!is_array($pattern) || empty($pattern)) {
            return 0;
        }

        $strength = 0;
        $rules = $this->config['pattern_lock_strength_rules'];

        // Base score: length
        $length = count($pattern);
        if ($length >= 4) $strength++;
        if ($length >= 6) $strength++;
        if ($length >= 8) $strength++;

        // Calculate directional changes
        $direction_changes = $this->count_direction_changes($pattern, $grid_size);
        if ($direction_changes >= $rules['directional_changes']) {
            $strength++;
        }

        // Check for non-adjacent points
        if ($rules['no_adjacent_only'] && $this->has_non_adjacent_points($pattern, $grid_size)) {
            $strength++;
        }

        // Penalize simple straight lines
        if ($rules['no_straight_lines'] && $this->is_straight_line($pattern, $grid_size)) {
            $strength = max(0, $strength - 1);
        }

        return min(5, $strength);
    }

    /**
     * Count direction changes in pattern
     * 
     * @param array $pattern Array of point indices
     * @param int $grid_size Grid size
     * @return int Number of direction changes
     */
    protected function count_direction_changes($pattern, $grid_size)
    {
        if (count($pattern) < 3) {
            return 0;
        }

        $changes = 0;
        $prev_direction = NULL;

        for ($i = 1; $i < count($pattern); $i++) {
            $direction = $this->get_direction($pattern[$i-1], $pattern[$i], $grid_size);
            if ($prev_direction !== NULL && $direction !== $prev_direction) {
                $changes++;
            }
            $prev_direction = $direction;
        }

        return $changes;
    }

    /**
     * Get direction between two points
     * 
     * @param int $from From point index
     * @param int $to To point index
     * @param int $grid_size Grid size
     * @return string Direction code
     */
    protected function get_direction($from, $to, $grid_size)
    {
        $from_row = floor($from / $grid_size);
        $from_col = $from % $grid_size;
        $to_row = floor($to / $grid_size);
        $to_col = $to % $grid_size;

        $delta_row = $to_row - $from_row;
        $delta_col = $to_col - $from_col;

        // Normalize to -1, 0, 1
        $delta_row = $delta_row == 0 ? 0 : ($delta_row > 0 ? 1 : -1);
        $delta_col = $delta_col == 0 ? 0 : ($delta_col > 0 ? 1 : -1);

        return $delta_row . ',' . $delta_col;
    }

    /**
     * Check if pattern has non-adjacent points
     * 
     * @param array $pattern Array of point indices
     * @param int $grid_size Grid size
     * @return bool TRUE if has non-adjacent points
     */
    protected function has_non_adjacent_points($pattern, $grid_size)
    {
        for ($i = 1; $i < count($pattern); $i++) {
            $distance = $this->get_point_distance($pattern[$i-1], $pattern[$i], $grid_size);
            if ($distance > 1.5) { // More than adjacent (diagonal = ~1.41)
                return TRUE;
            }
        }
        return FALSE;
    }

    /**
     * Calculate distance between two points
     * 
     * @param int $from From point index
     * @param int $to To point index
     * @param int $grid_size Grid size
     * @return float Distance
     */
    protected function get_point_distance($from, $to, $grid_size)
    {
        $from_row = floor($from / $grid_size);
        $from_col = $from % $grid_size;
        $to_row = floor($to / $grid_size);
        $to_col = $to % $grid_size;

        return sqrt(pow($to_row - $from_row, 2) + pow($to_col - $from_col, 2));
    }

    /**
     * Check if pattern is a straight line
     * 
     * @param array $pattern Array of point indices
     * @param int $grid_size Grid size
     * @return bool TRUE if pattern is straight line
     */
    protected function is_straight_line($pattern, $grid_size)
    {
        if (count($pattern) < 3) {
            return FALSE;
        }

        $direction = $this->get_direction($pattern[0], $pattern[1], $grid_size);
        
        for ($i = 2; $i < count($pattern); $i++) {
            $current_direction = $this->get_direction($pattern[$i-1], $pattern[$i], $grid_size);
            if ($current_direction !== $direction) {
                return FALSE;
            }
        }

        return TRUE;
    }

    /**
     * Generate a random backup code
     * 
     * @return string Backup code
     */
    public function generate_backup_code()
    {
        $length = $this->config['pattern_lock_backup_code_length'];
        $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $code = '';
        
        for ($i = 0; $i < $length; $i++) {
            $code .= $characters[random_int(0, strlen($characters) - 1)];
        }
        
        // Format as XXXX-XXXX-XXXX-XXXX
        return implode('-', str_split($code, 4));
    }

    /**
     * Encrypt backup code
     * 
     * @param string $code Backup code
     * @return string Encrypted code
     */
    public function encrypt_backup_code($code)
    {
        $this->CI->load->library('encryption');
        return $this->CI->encryption->encrypt($code);
    }

    /**
     * Decrypt backup code
     * 
     * @param string $encrypted_code Encrypted backup code
     * @return string Decrypted code
     */
    public function decrypt_backup_code($encrypted_code)
    {
        $this->CI->load->library('encryption');
        return $this->CI->encryption->decrypt($encrypted_code);
    }

    /**
     * Verify pattern against stored hash
     * 
     * @param array $pattern Pattern to verify
     * @param string $stored_hash Stored hash
     * @param int $grid_size Grid size
     * @return bool TRUE if pattern matches
     */
    public function verify_pattern($pattern, $stored_hash, $grid_size = 3)
    {
        $pattern_hash = $this->hash_pattern($pattern, $grid_size);
        return hash_equals($stored_hash, $pattern_hash);
    }

    /**
     * Verify backup code
     * 
     * @param string $code Provided code
     * @param string $stored_encrypted_code Stored encrypted code
     * @return bool TRUE if code matches
     */
    public function verify_backup_code($code, $stored_encrypted_code)
    {
        $decrypted = $this->decrypt_backup_code($stored_encrypted_code);
        // Normalize codes (remove hyphens and convert to uppercase)
        $code = strtoupper(str_replace('-', '', $code));
        $decrypted = strtoupper(str_replace('-', '', $decrypted));
        
        return hash_equals($decrypted, $code);
    }
}
