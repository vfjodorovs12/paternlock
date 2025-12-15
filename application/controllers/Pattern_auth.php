<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Pattern Authentication Controller
 * 
 * Handles pattern lock authentication, setup, and recovery
 * 
 * @package    Pattern_Lock
 * @subpackage Controllers
 * @category   Authentication
 * @author     Pattern Lock Team
 * @version    1.0
 */
class Pattern_auth extends CI_Controller {

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->load->library(array('session', 'form_validation', 'email'));
        $this->load->library(array('pattern_lock', 'pattern_logger', 'recaptcha'));
        $this->load->model('Pattern_model');
        $this->load->helper(array('url', 'form', 'pattern'));
        $this->load->config('pattern_lock');
    }

    /**
     * Pattern login page
     */
    public function login()
    {
        // Check system lockout
        if (check_system_lockout()) {
            $data['error'] = $this->config->item('pattern_lock_system_lockout_message');
            $this->load->view('pattern_auth/login', $data);
            return;
        }

        // Check if already logged in
        if ($this->session->userdata($this->config->item('pattern_lock_session_key'))) {
            redirect($this->config->item('pattern_lock_redirect_after_login'));
            return;
        }

        // Check IP lockout
        $ip_address = $this->input->ip_address();
        if (is_pattern_locked($ip_address)) {
            $time_remaining = get_lockout_time_remaining($ip_address);
            $data['error'] = 'Your IP is temporarily locked due to too many failed attempts. Please try again in ' . format_time_remaining($time_remaining) . '.';
            $data['locked'] = TRUE;
            $data['time_remaining'] = $time_remaining;
            $this->load->view('pattern_auth/login', $data);
            return;
        }

        $data = array();

        // Handle form submission
        if ($this->input->post()) {
            $this->_handle_login();
            return;
        }

        // Get failed attempts for this IP
        $lockout = $this->Pattern_model->get_lockout($ip_address);
        $failed_attempts = $lockout ? $lockout['failed_attempts'] : 0;

        // Check if reCAPTCHA should be shown
        $data['show_recaptcha'] = FALSE;
        if ($this->config->item('pattern_lock_recaptcha_enabled') && $this->recaptcha->is_configured()) {
            $data['show_recaptcha'] = $this->recaptcha->should_show($failed_attempts);
        }

        $data['recaptcha_html'] = $data['show_recaptcha'] ? $this->recaptcha->render() : '';
        $data['grid_size'] = $this->config->item('pattern_lock_grid_size');
        $data['failed_attempts'] = $failed_attempts;

        $this->load->view('pattern_auth/login', $data);
    }

    /**
     * Handle login form submission
     */
    private function _handle_login()
    {
        $ip_address = $this->input->ip_address();

        // CSRF validation is automatic in CodeIgniter if enabled
        $username = $this->input->post('username', TRUE);
        $pattern = $this->input->post('pattern', TRUE);

        // Validate input
        if (empty($username) || empty($pattern)) {
            $this->session->set_flashdata('error', 'Username and pattern are required');
            redirect('pattern_auth/login');
            return;
        }

        // Decode pattern JSON
        $pattern_array = json_decode($pattern, TRUE);
        if (!is_array($pattern_array)) {
            $this->session->set_flashdata('error', 'Invalid pattern format');
            redirect('pattern_auth/login');
            return;
        }

        // Get user pattern
        $user_pattern = $this->Pattern_model->get_user_pattern_by_username($username);

        if (!$user_pattern) {
            $this->_handle_failed_login(NULL, $username, 'User not found or pattern not configured');
            redirect('pattern_auth/login');
            return;
        }

        // Check if pattern is active
        if (!$user_pattern['is_active']) {
            $this->_handle_failed_login($user_pattern['user_id'], $username, 'Pattern authentication is disabled');
            redirect('pattern_auth/login');
            return;
        }

        // Verify reCAPTCHA if shown
        $lockout = $this->Pattern_model->get_lockout($ip_address);
        $failed_attempts = $lockout ? $lockout['failed_attempts'] : 0;

        if ($this->config->item('pattern_lock_recaptcha_enabled') && 
            $this->recaptcha->is_configured() && 
            $this->recaptcha->should_show($failed_attempts)) {
            
            $recaptcha_response = $this->input->post('g-recaptcha-response') ?: $this->input->post('recaptcha_response');
            $recaptcha_result = $this->recaptcha->verify($recaptcha_response);

            if (!$recaptcha_result['success']) {
                $this->session->set_flashdata('error', $recaptcha_result['error']);
                redirect('pattern_auth/login');
                return;
            }
        }

        // Verify pattern
        if ($this->pattern_lock->verify_pattern($pattern_array, $user_pattern['pattern_hash'], $user_pattern['grid_size'])) {
            // Success! Log and login
            $this->_handle_successful_login($user_pattern);
        } else {
            // Failed login
            $this->_handle_failed_login($user_pattern['user_id'], $username, 'Incorrect pattern');
            redirect('pattern_auth/login');
        }
    }

    /**
     * Handle successful login
     * 
     * @param array $user_pattern User pattern data
     */
    private function _handle_successful_login($user_pattern)
    {
        // Reset IP lockout
        $this->Pattern_model->reset_lockout($this->input->ip_address());

        // Log successful attempt
        $this->pattern_logger->log_attempt(array(
            'user_id' => $user_pattern['user_id'],
            'username' => $user_pattern['username'],
            'status' => 'success',
            'attempt_type' => 'pattern'
        ));

        // Check if device is known
        if ($this->config->item('pattern_lock_track_devices')) {
            if (!$this->pattern_logger->is_known_device($user_pattern['user_id'])) {
                // New device - register and send email
                $this->pattern_logger->register_device($user_pattern['user_id'], FALSE);
                
                if ($this->config->item('pattern_lock_email_on_new_device')) {
                    $this->_send_new_device_email($user_pattern);
                }
            } else {
                // Update last used
                $this->pattern_logger->update_device_last_used($user_pattern['user_id']);
            }
        }

        // Set session
        $session_data = array(
            'user_id' => $user_pattern['user_id'],
            'username' => $user_pattern['username'],
            'logged_in' => TRUE,
            'auth_method' => 'pattern'
        );
        $this->session->set_userdata($this->config->item('pattern_lock_session_key'), $session_data);

        // Redirect
        redirect($this->config->item('pattern_lock_redirect_after_login'));
    }

    /**
     * Handle failed login
     * 
     * @param int $user_id User ID
     * @param string $username Username
     * @param string $reason Failure reason
     */
    private function _handle_failed_login($user_id, $username, $reason)
    {
        $ip_address = $this->input->ip_address();

        // Log failed attempt
        $this->pattern_logger->log_attempt(array(
            'user_id' => $user_id,
            'username' => $username,
            'status' => 'failed',
            'attempt_type' => 'pattern',
            'failure_reason' => $reason
        ));

        // Increment failed attempts
        $this->Pattern_model->increment_failed_attempts($ip_address);

        // Check if should be locked
        $lockout = $this->Pattern_model->get_lockout($ip_address);
        $max_attempts = $this->config->item('pattern_lock_max_failed_attempts');

        if ($lockout && $lockout['failed_attempts'] >= $max_attempts) {
            // Lock the IP
            $duration = $this->config->item('pattern_lock_lockout_duration');
            $this->Pattern_model->lock_ip($ip_address, $duration, FALSE);

            // Send lockout email if configured
            if ($user_id && $this->config->item('pattern_lock_email_on_lockout')) {
                $this->_send_lockout_email($user_id, $username);
            }

            $this->session->set_flashdata('error', 'Too many failed attempts. Your IP has been locked for ' . format_time_remaining($duration) . '.');
        } else {
            $remaining = $max_attempts - ($lockout ? $lockout['failed_attempts'] : 0);
            $this->session->set_flashdata('error', $reason . '. You have ' . $remaining . ' attempt(s) remaining.');
        }
    }

    /**
     * Pattern setup page
     */
    public function setup()
    {
        // Check if user is logged in (you may need to integrate with your user system)
        // For this example, we'll assume user info is in session
        $user_id = $this->session->userdata('user_id');
        
        if (!$user_id) {
            $this->session->set_flashdata('error', 'You must be logged in to setup pattern authentication');
            redirect('pattern_auth/login');
            return;
        }

        $data = array();
        $data['grid_size'] = $this->config->item('pattern_lock_grid_size');
        $data['min_points'] = $this->config->item('pattern_lock_min_points');

        // Handle form submission
        if ($this->input->post('step') == 'confirm') {
            $this->_handle_pattern_setup($user_id);
            return;
        }

        $this->load->view('pattern_auth/setup', $data);
    }

    /**
     * Handle pattern setup
     * 
     * @param int $user_id User ID
     */
    private function _handle_pattern_setup($user_id)
    {
        $pattern = $this->input->post('pattern', TRUE);
        $pattern_confirm = $this->input->post('pattern_confirm', TRUE);
        $username = $this->input->post('username', TRUE);

        // Decode patterns
        $pattern_array = json_decode($pattern, TRUE);
        $pattern_confirm_array = json_decode($pattern_confirm, TRUE);

        if (!is_array($pattern_array) || !is_array($pattern_confirm_array)) {
            $this->session->set_flashdata('error', 'Invalid pattern format');
            redirect('pattern_auth/setup');
            return;
        }

        // Check if patterns match
        if ($pattern !== $pattern_confirm) {
            $this->session->set_flashdata('error', 'Patterns do not match. Please try again.');
            redirect('pattern_auth/setup');
            return;
        }

        // Validate pattern
        $grid_size = $this->config->item('pattern_lock_grid_size');
        $validation = $this->pattern_lock->validate_pattern($pattern_array, $grid_size);

        if (!$validation['valid']) {
            $this->session->set_flashdata('error', implode(', ', $validation['errors']));
            redirect('pattern_auth/setup');
            return;
        }

        // Calculate pattern strength
        $strength = $this->pattern_lock->calculate_pattern_strength($pattern_array, $grid_size);

        // Generate and encrypt backup code
        $backup_code = $this->pattern_lock->generate_backup_code();
        $encrypted_backup = $this->pattern_lock->encrypt_backup_code($backup_code);

        // Hash pattern
        $pattern_hash = $this->pattern_lock->hash_pattern($pattern_array, $grid_size);

        // Save pattern
        $pattern_data = array(
            'user_id' => $user_id,
            'username' => $username,
            'pattern_hash' => $pattern_hash,
            'grid_size' => $grid_size,
            'backup_code' => $encrypted_backup,
            'pattern_strength' => $strength,
            'is_active' => 1
        );

        if ($this->Pattern_model->save_user_pattern($pattern_data)) {
            $this->session->set_flashdata('success', 'Pattern authentication configured successfully!');
            $this->session->set_flashdata('backup_code', $backup_code);
            redirect('pattern_auth/setup_complete');
        } else {
            $this->session->set_flashdata('error', 'Failed to save pattern. Please try again.');
            redirect('pattern_auth/setup');
        }
    }

    /**
     * Setup complete page (shows backup code)
     */
    public function setup_complete()
    {
        $data['backup_code'] = $this->session->flashdata('backup_code');
        
        if (!$data['backup_code']) {
            redirect('pattern_auth/setup');
            return;
        }

        $this->load->view('pattern_auth/setup_complete', $data);
    }

    /**
     * Pattern recovery page
     */
    public function recover()
    {
        $data = array();

        // Handle form submission
        if ($this->input->post()) {
            $this->_handle_recovery();
            return;
        }

        $this->load->view('pattern_auth/recover', $data);
    }

    /**
     * Handle recovery form submission
     */
    private function _handle_recovery()
    {
        $username = $this->input->post('username', TRUE);
        $backup_code = $this->input->post('backup_code', TRUE);

        if (empty($username) || empty($backup_code)) {
            $this->session->set_flashdata('error', 'Username and backup code are required');
            redirect('pattern_auth/recover');
            return;
        }

        // Get user pattern
        $user_pattern = $this->Pattern_model->get_user_pattern_by_username($username);

        if (!$user_pattern) {
            $this->session->set_flashdata('error', 'User not found or pattern not configured');
            redirect('pattern_auth/recover');
            return;
        }

        // Verify backup code
        if ($this->pattern_lock->verify_backup_code($backup_code, $user_pattern['backup_code'])) {
            // Success! Log and login
            $this->Pattern_model->reset_lockout($this->input->ip_address());

            $this->pattern_logger->log_attempt(array(
                'user_id' => $user_pattern['user_id'],
                'username' => $user_pattern['username'],
                'status' => 'recovered',
                'attempt_type' => 'backup_code'
            ));

            // Set session
            $session_data = array(
                'user_id' => $user_pattern['user_id'],
                'username' => $user_pattern['username'],
                'logged_in' => TRUE,
                'auth_method' => 'backup_code'
            );
            $this->session->set_userdata($this->config->item('pattern_lock_session_key'), $session_data);

            $this->session->set_flashdata('success', 'Access recovered successfully!');
            redirect($this->config->item('pattern_lock_redirect_after_login'));
        } else {
            $this->session->set_flashdata('error', 'Invalid backup code');
            redirect('pattern_auth/recover');
        }
    }

    /**
     * Logout
     */
    public function logout()
    {
        $this->session->unset_userdata($this->config->item('pattern_lock_session_key'));
        redirect($this->config->item('pattern_lock_redirect_after_logout'));
    }

    /**
     * Access logs page (admin only)
     */
    public function access_logs()
    {
        // Check if user is admin (you'll need to implement this check)
        // For now, just check if logged in
        if (!$this->session->userdata($this->config->item('pattern_lock_session_key'))) {
            redirect('pattern_auth/login');
            return;
        }

        // Get filters from query string
        $filters = array(
            'username' => $this->input->get('username'),
            'status' => $this->input->get('status'),
            'date_from' => $this->input->get('date_from'),
            'date_to' => $this->input->get('date_to')
        );

        // Pagination
        $page = (int)$this->input->get('page') ?: 1;
        $per_page = 50;
        $offset = ($page - 1) * $per_page;

        // Get logs
        $data['logs'] = $this->pattern_logger->get_logs($filters, $per_page, $offset);
        $data['total'] = $this->Pattern_model->count_logs($filters);
        $data['page'] = $page;
        $data['per_page'] = $per_page;
        $data['filters'] = $filters;

        // Export to CSV if requested
        if ($this->input->get('export') == 'csv') {
            $csv = $this->pattern_logger->export_to_csv($filters);
            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="pattern_logs_' . date('Y-m-d') . '.csv"');
            echo $csv;
            return;
        }

        $this->load->view('pattern_auth/access_logs', $data);
    }

    /**
     * Settings page (admin only)
     */
    public function settings()
    {
        // Check if user is admin
        if (!$this->session->userdata($this->config->item('pattern_lock_session_key'))) {
            redirect('pattern_auth/login');
            return;
        }

        // Handle settings update
        if ($this->input->post('action') == 'update_settings') {
            $this->_update_settings();
            return;
        }

        // Handle unlock IP
        if ($this->input->post('action') == 'unlock_ip') {
            $ip = $this->input->post('ip_address');
            if ($this->Pattern_model->reset_lockout($ip)) {
                $this->session->set_flashdata('success', 'IP address unlocked successfully');
            }
            redirect('pattern_auth/settings');
            return;
        }

        // Get statistics
        $data['statistics'] = $this->pattern_logger->get_statistics();
        $data['lockouts'] = $this->Pattern_model->get_active_lockouts();
        $data['settings'] = $this->Pattern_model->get_all_settings();

        $this->load->view('pattern_auth/settings', $data);
    }

    /**
     * Update settings
     */
    private function _update_settings()
    {
        $settings = $this->input->post('setting');
        
        foreach ($settings as $key => $value) {
            $this->Pattern_model->update_setting($key, $value);
        }

        $this->session->set_flashdata('success', 'Settings updated successfully');
        redirect('pattern_auth/settings');
    }

    /**
     * Send lockout email notification
     * 
     * @param int $user_id User ID
     * @param string $username Username
     */
    private function _send_lockout_email($user_id, $username)
    {
        // You'll need to implement user email retrieval
        // This is a placeholder
        $user_email = 'user@example.com'; // Get from your user system

        $this->email->from($this->config->item('pattern_lock_email_from'), 
                          $this->config->item('pattern_lock_email_from_name'));
        $this->email->to($user_email);
        $this->email->subject('Account Lockout Notification');
        
        $message = "Your account has been temporarily locked due to multiple failed login attempts.\n\n";
        $message .= "IP Address: " . $this->input->ip_address() . "\n";
        $message .= "Time: " . date('Y-m-d H:i:s') . "\n\n";
        $message .= "If this wasn't you, please contact support immediately.";
        
        $this->email->message($message);
        $this->email->send();
    }

    /**
     * Send new device email notification
     * 
     * @param array $user_pattern User pattern data
     */
    private function _send_new_device_email($user_pattern)
    {
        // You'll need to implement user email retrieval
        $user_email = 'user@example.com'; // Get from your user system

        $this->email->from($this->config->item('pattern_lock_email_from'), 
                          $this->config->item('pattern_lock_email_from_name'));
        $this->email->to($user_email);
        $this->email->subject('New Device Login Detected');
        
        $message = "A new device has accessed your account.\n\n";
        $message .= "IP Address: " . $this->input->ip_address() . "\n";
        $message .= "Time: " . date('Y-m-d H:i:s') . "\n";
        $message .= "User Agent: " . $this->input->user_agent() . "\n\n";
        $message .= "If this wasn't you, please secure your account immediately.";
        
        $this->email->message($message);
        $this->email->send();
    }
}
