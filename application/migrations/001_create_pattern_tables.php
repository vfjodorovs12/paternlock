<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Create_pattern_tables extends CI_Migration {

    public function up()
    {
        // Таблица паттернов пользователей
        $this->dbforge->add_field(array(
            'id' => array('type' => 'INT', 'constraint' => 11, 'unsigned' => TRUE, 'auto_increment' => TRUE),
            'user_id' => array('type' => 'INT', 'constraint' => 11, 'unsigned' => TRUE),
            'pattern_hash' => array('type' => 'VARCHAR', 'constraint' => 255),
            'grid_size' => array('type' => 'TINYINT', 'constraint' => 2, 'default' => 3),
            'is_active' => array('type' => 'TINYINT', 'constraint' => 1, 'default' => 1),
            'backup_code' => array('type' => 'VARCHAR', 'constraint' => 255, 'null' => TRUE),
            'created_at' => array('type' => 'DATETIME'),
            'updated_at' => array('type' => 'DATETIME')
        ));
        $this->dbforge->add_key('id', TRUE);
        $this->dbforge->add_key('user_id');
        $this->dbforge->create_table('user_patterns', TRUE);

        // Таблица логов доступа
        $this->dbforge->add_field(array(
            'id' => array('type' => 'BIGINT', 'constraint' => 20, 'unsigned' => TRUE, 'auto_increment' => TRUE),
            'user_id' => array('type' => 'INT', 'constraint' => 11, 'unsigned' => TRUE, 'null' => TRUE),
            'username' => array('type' => 'VARCHAR', 'constraint' => 255, 'null' => TRUE),
            'ip_address' => array('type' => 'VARCHAR', 'constraint' => 45),
            'user_agent' => array('type' => 'TEXT', 'null' => TRUE),
            'device_fingerprint' => array('type' => 'VARCHAR', 'constraint' => 64, 'null' => TRUE),
            'attempt_type' => array('type' => 'ENUM', 'constraint' => array('pattern', 'backup_code', 'password', 'captcha_fail'), 'default' => 'pattern'),
            'status' => array('type' => 'ENUM', 'constraint' => array('success', 'failed', 'blocked', 'captcha_required'), 'default' => 'failed'),
            'failure_reason' => array('type' => 'VARCHAR', 'constraint' => 255, 'null' => TRUE),
            'captcha_score' => array('type' => 'DECIMAL', 'constraint' => '3,2', 'null' => TRUE),
            'location_country' => array('type' => 'VARCHAR', 'constraint' => 100, 'null' => TRUE),
            'location_city' => array('type' => 'VARCHAR', 'constraint' => 100, 'null' => TRUE),
            'session_id' => array('type' => 'VARCHAR', 'constraint' => 128, 'null' => TRUE),
            'request_data' => array('type' => 'TEXT', 'null' => TRUE),
            'created_at' => array('type' => 'DATETIME')
        ));
        $this->dbforge->add_key('id', TRUE);
        $this->dbforge->add_key('user_id');
        $this->dbforge->add_key('ip_address');
        $this->dbforge->add_key('status');
        $this->dbforge->add_key('created_at');
        $this->dbforge->create_table('pattern_access_logs', TRUE);

        // Таблица блокировок
        $this->dbforge->add_field(array(
            'id' => array('type' => 'INT', 'constraint' => 11, 'unsigned' => TRUE, 'auto_increment' => TRUE),
            'ip_address' => array('type' => 'VARCHAR', 'constraint' => 45),
            'user_id' => array('type' => 'INT', 'constraint' => 11, 'unsigned' => TRUE, 'null' => TRUE),
            'attempts' => array('type' => 'INT', 'constraint' => 11, 'default' => 0),
            'captcha_required' => array('type' => 'TINYINT', 'constraint' => 1, 'default' => 0),
            'locked_until' => array('type' => 'DATETIME', 'null' => TRUE),
            'permanent_block' => array('type' => 'TINYINT', 'constraint' => 1, 'default' => 0),
            'block_reason' => array('type' => 'VARCHAR', 'constraint' => 255, 'null' => TRUE),
            'created_at' => array('type' => 'DATETIME'),
            'updated_at' => array('type' => 'DATETIME')
        ));
        $this->dbforge->add_key('id', TRUE);
        $this->dbforge->add_key('ip_address');
        $this->dbforge->create_table('pattern_lockouts', TRUE);

        // Таблица настроек
        $this->dbforge->add_field(array(
            'id' => array('type' => 'INT', 'constraint' => 11, 'unsigned' => TRUE, 'auto_increment' => TRUE),
            'setting_key' => array('type' => 'VARCHAR', 'constraint' => 100),
            'setting_value' => array('type' => 'TEXT'),
            'setting_type' => array('type' => 'ENUM', 'constraint' => array('string', 'integer', 'boolean', 'json'), 'default' => 'string'),
            'description' => array('type' => 'VARCHAR', 'constraint' => 255, 'null' => TRUE),
            'created_at' => array('type' => 'DATETIME'),
            'updated_at' => array('type' => 'DATETIME')
        ));
        $this->dbforge->add_key('id', TRUE);
        $this->dbforge->add_key('setting_key');
        $this->dbforge->create_table('pattern_settings', TRUE);

        // Таблица известных устройств
        $this->dbforge->add_field(array(
            'id' => array('type' => 'INT', 'constraint' => 11, 'unsigned' => TRUE, 'auto_increment' => TRUE),
            'user_id' => array('type' => 'INT', 'constraint' => 11, 'unsigned' => TRUE),
            'device_fingerprint' => array('type' => 'VARCHAR', 'constraint' => 64),
            'device_name' => array('type' => 'VARCHAR', 'constraint' => 255, 'null' => TRUE),
            'user_agent' => array('type' => 'TEXT', 'null' => TRUE),
            'ip_address' => array('type' => 'VARCHAR', 'constraint' => 45),
            'is_trusted' => array('type' => 'TINYINT', 'constraint' => 1, 'default' => 0),
            'last_used_at' => array('type' => 'DATETIME'),
            'created_at' => array('type' => 'DATETIME')
        ));
        $this->dbforge->add_key('id', TRUE);
        $this->dbforge->add_key('user_id');
        $this->dbforge->add_key('device_fingerprint');
        $this->dbforge->create_table('pattern_known_devices', TRUE);
    }

    public function down()
    {
        $this->dbforge->drop_table('user_patterns', TRUE);
        $this->dbforge->drop_table('pattern_access_logs', TRUE);
        $this->dbforge->drop_table('pattern_lockouts', TRUE);
        $this->dbforge->drop_table('pattern_settings', TRUE);
        $this->dbforge->drop_table('pattern_known_devices', TRUE);
    }
}