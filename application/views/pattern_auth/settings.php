<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pattern Lock Settings - Admin</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: #f5f7fa;
            padding: 20px;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .header {
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }

        h1 {
            color: #333;
            font-size: 28px;
            margin-bottom: 10px;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }

        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .stat-label {
            font-size: 13px;
            color: #999;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 8px;
        }

        .stat-value {
            font-size: 32px;
            font-weight: 700;
            color: #333;
        }

        .stat-card.success .stat-value {
            color: #27ae60;
        }

        .stat-card.danger .stat-value {
            color: #e74c3c;
        }

        .stat-card.warning .stat-value {
            color: #f39c12;
        }

        .stat-card.info .stat-value {
            color: #3498db;
        }

        .section {
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }

        .section h2 {
            color: #333;
            font-size: 20px;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #f0f0f0;
        }

        .settings-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
        }

        .setting-item {
            display: flex;
            flex-direction: column;
        }

        .setting-item label {
            font-size: 14px;
            color: #333;
            font-weight: 500;
            margin-bottom: 8px;
        }

        .setting-item small {
            font-size: 12px;
            color: #999;
            margin-top: 4px;
        }

        .setting-item input,
        .setting-item select {
            padding: 10px 12px;
            border: 1px solid #e1e8ed;
            border-radius: 6px;
            font-size: 14px;
        }

        .setting-item input[type="checkbox"] {
            width: auto;
            margin-right: 8px;
        }

        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-block;
        }

        .btn-primary {
            background: #667eea;
            color: white;
        }

        .btn-primary:hover {
            background: #5568d3;
        }

        .btn-danger {
            background: #e74c3c;
            color: white;
        }

        .btn-danger:hover {
            background: #c0392b;
        }

        .btn-secondary {
            background: #e1e8ed;
            color: #666;
        }

        .lockouts-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .lockouts-table th {
            background: #f7f9fc;
            padding: 12px;
            text-align: left;
            font-weight: 600;
            color: #333;
            font-size: 13px;
            border-bottom: 2px solid #e1e8ed;
        }

        .lockouts-table td {
            padding: 12px;
            border-bottom: 1px solid #f0f0f0;
            color: #666;
        }

        .alert {
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
        }

        .alert-success {
            background: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
        }

        .alert-error {
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
        }

        .emergency-lockout {
            background: #fff3cd;
            padding: 20px;
            border-radius: 8px;
            border: 2px solid #ffc107;
            margin-top: 20px;
        }

        .emergency-lockout h3 {
            color: #856404;
            margin-bottom: 10px;
        }

        .emergency-lockout p {
            color: #856404;
            margin-bottom: 15px;
        }

        @media (max-width: 768px) {
            .stats-grid,
            .settings-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>⚙️ Pattern Lock Settings</h1>
            <p style="color: #666;">Manage pattern authentication system</p>
        </div>

        <?php if ($this->session->flashdata('success')): ?>
            <div class="alert alert-success">
                <?php echo htmlspecialchars($this->session->flashdata('success'), ENT_QUOTES, 'UTF-8'); ?>
            </div>
        <?php endif; ?>

        <?php if ($this->session->flashdata('error')): ?>
            <div class="alert alert-error">
                <?php echo htmlspecialchars($this->session->flashdata('error'), ENT_QUOTES, 'UTF-8'); ?>
            </div>
        <?php endif; ?>

        <!-- Statistics -->
        <div class="stats-grid">
            <div class="stat-card success">
                <div class="stat-label">Successful Logins</div>
                <div class="stat-value"><?php echo $statistics['success'] ?? 0; ?></div>
            </div>
            <div class="stat-card danger">
                <div class="stat-label">Failed Attempts</div>
                <div class="stat-value"><?php echo $statistics['failed'] ?? 0; ?></div>
            </div>
            <div class="stat-card warning">
                <div class="stat-label">Blocked Attempts</div>
                <div class="stat-value"><?php echo $statistics['blocked'] ?? 0; ?></div>
            </div>
            <div class="stat-card info">
                <div class="stat-label">Total Attempts</div>
                <div class="stat-value"><?php echo $statistics['total'] ?? 0; ?></div>
            </div>
        </div>

        <!-- Settings Form -->
        <div class="section">
            <h2>System Settings</h2>
            
            <?php echo form_open('pattern_auth/settings'); ?>
                <input type="hidden" name="action" value="update_settings">
                
                <div class="settings-grid">
                    <?php foreach ($settings as $setting): ?>
                        <div class="setting-item">
                            <label><?php echo ucwords(str_replace('_', ' ', $setting['setting_key'])); ?></label>
                            
                            <?php if ($setting['setting_type'] == 'boolean'): ?>
                                <label style="display: flex; align-items: center;">
                                    <input type="checkbox" 
                                           name="setting[<?php echo $setting['setting_key']; ?>]" 
                                           value="1" 
                                           <?php echo $setting['setting_value'] == '1' ? 'checked' : ''; ?>>
                                    Enable
                                </label>
                            <?php elseif ($setting['setting_type'] == 'integer'): ?>
                                <input type="number" 
                                       name="setting[<?php echo $setting['setting_key']; ?>]" 
                                       value="<?php echo htmlspecialchars($setting['setting_value'], ENT_QUOTES, 'UTF-8'); ?>"
                                       min="0">
                            <?php else: ?>
                                <input type="text" 
                                       name="setting[<?php echo $setting['setting_key']; ?>]" 
                                       value="<?php echo htmlspecialchars($setting['setting_value'], ENT_QUOTES, 'UTF-8'); ?>">
                            <?php endif; ?>
                            
                            <?php if ($setting['description']): ?>
                                <small><?php echo htmlspecialchars($setting['description'], ENT_QUOTES, 'UTF-8'); ?></small>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div style="margin-top: 30px;">
                    <button type="submit" class="btn btn-primary">Save Settings</button>
                </div>
            <?php echo form_close(); ?>
        </div>

        <!-- Active Lockouts -->
        <div class="section">
            <h2>Active IP Lockouts</h2>
            
            <?php if (empty($lockouts)): ?>
                <p style="color: #999; text-align: center; padding: 20px;">No active lockouts</p>
            <?php else: ?>
                <table class="lockouts-table">
                    <thead>
                        <tr>
                            <th>IP Address</th>
                            <th>Failed Attempts</th>
                            <th>Locked Until</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($lockouts as $lockout): ?>
                            <tr>
                                <td><code><?php echo htmlspecialchars($lockout['ip_address'], ENT_QUOTES, 'UTF-8'); ?></code></td>
                                <td><?php echo $lockout['failed_attempts']; ?></td>
                                <td>
                                    <?php if ($lockout['is_permanent']): ?>
                                        <span style="color: #e74c3c;">Permanent</span>
                                    <?php elseif ($lockout['locked_until']): ?>
                                        <?php echo date('M d, Y H:i', strtotime($lockout['locked_until'])); ?>
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($lockout['is_permanent']): ?>
                                        <span style="background: #e74c3c; color: white; padding: 4px 10px; border-radius: 12px; font-size: 12px;">Permanent</span>
                                    <?php else: ?>
                                        <span style="background: #f39c12; color: white; padding: 4px 10px; border-radius: 12px; font-size: 12px;">Temporary</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php echo form_open('pattern_auth/settings', array('style' => 'display: inline;')); ?>
                                        <input type="hidden" name="action" value="unlock_ip">
                                        <input type="hidden" name="ip_address" value="<?php echo htmlspecialchars($lockout['ip_address'], ENT_QUOTES, 'UTF-8'); ?>">
                                        <button type="submit" class="btn btn-secondary" style="padding: 6px 12px; font-size: 12px;">
                                            Unlock
                                        </button>
                                    <?php echo form_close(); ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

        <!-- Emergency System Lockout -->
        <div class="section">
            <h2>Emergency Controls</h2>
            
            <div class="emergency-lockout">
                <h3>⚠️ System-Wide Lockout</h3>
                <p>Enable this to prevent all pattern authentication attempts. Use only in emergency situations.</p>
                
                <?php
                $system_lockout = FALSE;
                foreach ($settings as $setting) {
                    if ($setting['setting_key'] == 'system_lockout') {
                        $system_lockout = $setting['setting_value'] == '1';
                        break;
                    }
                }
                ?>
                
                <?php if ($system_lockout): ?>
                    <div style="background: #e74c3c; color: white; padding: 15px; border-radius: 6px; margin-bottom: 15px;">
                        <strong>🔒 SYSTEM IS CURRENTLY LOCKED</strong><br>
                        All pattern authentication is disabled.
                    </div>
                <?php endif; ?>
                
                <?php echo form_open('pattern_auth/settings'); ?>
                    <input type="hidden" name="action" value="update_settings">
                    <input type="hidden" name="setting[system_lockout]" value="<?php echo $system_lockout ? '0' : '1'; ?>">
                    <button type="submit" class="btn <?php echo $system_lockout ? 'btn-primary' : 'btn-danger'; ?>">
                        <?php echo $system_lockout ? 'Disable System Lockout' : 'Enable System Lockout'; ?>
                    </button>
                <?php echo form_close(); ?>
            </div>
        </div>

        <div style="margin-top: 20px; text-align: center;">
            <a href="<?php echo site_url('pattern_auth/access_logs'); ?>" class="btn btn-primary">View Access Logs</a>
            <a href="<?php echo site_url('dashboard'); ?>" class="btn btn-secondary">Back to Dashboard</a>
        </div>
    </div>
</body>
</html>
