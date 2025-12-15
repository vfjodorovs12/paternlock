<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Pattern Lock Tables Migration
 * 
 * Creates all necessary tables for the Pattern Lock authentication system
 * 
 * @package    Pattern_Lock
 * @subpackage Migrations
 * @category   Database
 * @author     Pattern Lock Team
 * @version    1.0
 */
class Migration_Create_pattern_tables extends CI_Migration {

    public function up()
    {
        // user_patterns table
        $this->dbforge->add_field(array(
            'id' => array(
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => TRUE,
                'auto_increment' => TRUE
            ),
            'user_id' => array(
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => TRUE
            ),
            'username' => array(
                'type' => 'VARCHAR',
                'constraint' => 100
            ),
            'pattern_hash' => array(
                'type' => 'VARCHAR',
                'constraint' => 64,
                'comment' => 'SHA-256 hash of pattern'
            ),
            'grid_size' => array(
                'type' => 'TINYINT',
                'constraint' => 2,
                'unsigned' => TRUE,
                'default' => 3,
                'comment' => 'Grid size (3-20)'
            ),
            'backup_code' => array(
                'type' => 'VARCHAR',
                'constraint' => 64,
                'comment' => 'Encrypted backup code'
            ),
            'pattern_strength' => array(
                'type' => 'TINYINT',
                'constraint' => 1,
                'unsigned' => TRUE,
                'default' => 0,
                'comment' => '0-5 strength rating'
            ),
            'is_active' => array(
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 1
            ),
            'created_at' => array(
                'type' => 'DATETIME',
                'null' => FALSE
            ),
            'updated_at' => array(
                'type' => 'DATETIME',
                'null' => FALSE
            )
        ));
        $this->dbforge->add_key('id', TRUE);
        $this->dbforge->add_key('user_id', FALSE, TRUE);
        $this->dbforge->add_key('username', FALSE, TRUE);
        $this->dbforge->create_table('user_patterns');

        // pattern_access_logs table
        $this->dbforge->add_field(array(
            'id' => array(
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => TRUE,
                'auto_increment' => TRUE
            ),
            'user_id' => array(
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => TRUE,
                'null' => TRUE
            ),
            'username' => array(
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => TRUE
            ),
            'ip_address' => array(
                'type' => 'VARCHAR',
                'constraint' => 45
            ),
            'user_agent' => array(
                'type' => 'TEXT',
                'null' => TRUE
            ),
            'status' => array(
                'type' => 'ENUM',
                'constraint' => array('success', 'failed', 'blocked', 'recovered')
            ),
            'attempt_type' => array(
                'type' => 'ENUM',
                'constraint' => array('pattern', 'backup_code'),
                'default' => 'pattern'
            ),
            'failure_reason' => array(
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => TRUE
            ),
            'country' => array(
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => TRUE,
                'comment' => 'Geo-location country'
            ),
            'city' => array(
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => TRUE,
                'comment' => 'Geo-location city'
            ),
            'device_fingerprint' => array(
                'type' => 'VARCHAR',
                'constraint' => 64,
                'null' => TRUE,
                'comment' => 'Device identification'
            ),
            'created_at' => array(
                'type' => 'DATETIME',
                'null' => FALSE
            )
        ));
        $this->dbforge->add_key('id', TRUE);
        $this->dbforge->create_table('pattern_access_logs');

        // pattern_lockouts table
        $this->dbforge->add_field(array(
            'id' => array(
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => TRUE,
                'auto_increment' => TRUE
            ),
            'ip_address' => array(
                'type' => 'VARCHAR',
                'constraint' => 45
            ),
            'failed_attempts' => array(
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => TRUE,
                'default' => 0
            ),
            'locked_until' => array(
                'type' => 'DATETIME',
                'null' => TRUE
            ),
            'is_permanent' => array(
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 0
            ),
            'created_at' => array(
                'type' => 'DATETIME',
                'null' => FALSE
            ),
            'updated_at' => array(
                'type' => 'DATETIME',
                'null' => FALSE
            )
        ));
        $this->dbforge->add_key('id', TRUE);
        $this->dbforge->add_key('ip_address', FALSE, TRUE);
        $this->dbforge->create_table('pattern_lockouts');

        // pattern_settings table
        $this->dbforge->add_field(array(
            'id' => array(
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => TRUE,
                'auto_increment' => TRUE
            ),
            'setting_key' => array(
                'type' => 'VARCHAR',
                'constraint' => 100
            ),
            'setting_value' => array(
                'type' => 'TEXT',
                'null' => TRUE
            ),
            'setting_type' => array(
                'type' => 'ENUM',
                'constraint' => array('string', 'integer', 'boolean', 'json'),
                'default' => 'string'
            ),
            'description' => array(
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => TRUE
            ),
            'updated_at' => array(
                'type' => 'DATETIME',
                'null' => FALSE
            )
        ));
        $this->dbforge->add_key('id', TRUE);
        $this->dbforge->add_key('setting_key', FALSE, TRUE);
        $this->dbforge->create_table('pattern_settings');

        // pattern_known_devices table
        $this->dbforge->add_field(array(
            'id' => array(
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => TRUE,
                'auto_increment' => TRUE
            ),
            'user_id' => array(
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => TRUE
            ),
            'device_fingerprint' => array(
                'type' => 'VARCHAR',
                'constraint' => 64
            ),
            'device_name' => array(
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => TRUE
            ),
            'ip_address' => array(
                'type' => 'VARCHAR',
                'constraint' => 45
            ),
            'user_agent' => array(
                'type' => 'TEXT',
                'null' => TRUE
            ),
            'is_trusted' => array(
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 0
            ),
            'last_used' => array(
                'type' => 'DATETIME',
                'null' => FALSE
            ),
            'created_at' => array(
                'type' => 'DATETIME',
                'null' => FALSE
            )
        ));
        $this->dbforge->add_key('id', TRUE);
        $this->dbforge->create_table('pattern_known_devices');

        // Insert default settings
        $default_settings = array(
            array('setting_key' => 'max_failed_attempts', 'setting_value' => '5', 'setting_type' => 'integer', 'description' => 'Maximum failed attempts before lockout', 'updated_at' => date('Y-m-d H:i:s')),
            array('setting_key' => 'lockout_duration', 'setting_value' => '900', 'setting_type' => 'integer', 'description' => 'Lockout duration in seconds (default: 15 minutes)', 'updated_at' => date('Y-m-d H:i:s')),
            array('setting_key' => 'min_pattern_points', 'setting_value' => '4', 'setting_type' => 'integer', 'description' => 'Minimum number of points in pattern', 'updated_at' => date('Y-m-d H:i:s')),
            array('setting_key' => 'default_grid_size', 'setting_value' => '3', 'setting_type' => 'integer', 'description' => 'Default grid size for pattern (3x3)', 'updated_at' => date('Y-m-d H:i:s')),
            array('setting_key' => 'recaptcha_enabled', 'setting_value' => '1', 'setting_type' => 'boolean', 'description' => 'Enable reCAPTCHA integration', 'updated_at' => date('Y-m-d H:i:s')),
            array('setting_key' => 'recaptcha_threshold', 'setting_value' => '3', 'setting_type' => 'integer', 'description' => 'Failed attempts before showing reCAPTCHA', 'updated_at' => date('Y-m-d H:i:s')),
            array('setting_key' => 'log_retention_days', 'setting_value' => '90', 'setting_type' => 'integer', 'description' => 'Days to keep access logs (0 = forever)', 'updated_at' => date('Y-m-d H:i:s')),
            array('setting_key' => 'system_lockout', 'setting_value' => '0', 'setting_type' => 'boolean', 'description' => 'Total system lockout (admin only)', 'updated_at' => date('Y-m-d H:i:s')),
            array('setting_key' => 'email_on_lockout', 'setting_value' => '1', 'setting_type' => 'boolean', 'description' => 'Send email notification on account lockout', 'updated_at' => date('Y-m-d H:i:s')),
            array('setting_key' => 'email_on_new_device', 'setting_value' => '1', 'setting_type' => 'boolean', 'description' => 'Send email notification on new device login', 'updated_at' => date('Y-m-d H:i:s')),
            array('setting_key' => 'recaptcha_version', 'setting_value' => 'v2', 'setting_type' => 'string', 'description' => 'reCAPTCHA version (v2 or v3)', 'updated_at' => date('Y-m-d H:i:s')),
            array('setting_key' => 'recaptcha_v3_threshold', 'setting_value' => '0.5', 'setting_type' => 'string', 'description' => 'reCAPTCHA v3 score threshold (0.0-1.0)', 'updated_at' => date('Y-m-d H:i:s'))
        );
        $this->db->insert_batch('pattern_settings', $default_settings);
    }

    public function down()
    {
        $this->dbforge->drop_table('pattern_known_devices', TRUE);
        $this->dbforge->drop_table('pattern_settings', TRUE);
        $this->dbforge->drop_table('pattern_lockouts', TRUE);
        $this->dbforge->drop_table('pattern_access_logs', TRUE);
        $this->dbforge->drop_table('user_patterns', TRUE);
    }
}
