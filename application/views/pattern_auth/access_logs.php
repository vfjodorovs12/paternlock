<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Access Logs - Pattern Lock Admin</title>
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

        .filters {
            background: white;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }

        .filters h3 {
            color: #333;
            margin-bottom: 15px;
        }

        .filter-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 15px;
        }

        .filter-group {
            display: flex;
            flex-direction: column;
        }

        .filter-group label {
            font-size: 13px;
            color: #666;
            margin-bottom: 5px;
            font-weight: 500;
        }

        .filter-group input,
        .filter-group select {
            padding: 8px 12px;
            border: 1px solid #e1e8ed;
            border-radius: 6px;
            font-size: 14px;
        }

        .btn {
            padding: 10px 20px;
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

        .btn-secondary {
            background: #e1e8ed;
            color: #666;
        }

        .btn-success {
            background: #27ae60;
            color: white;
        }

        .logs-container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .table-responsive {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        thead {
            background: #f7f9fc;
        }

        th {
            padding: 15px;
            text-align: left;
            font-weight: 600;
            color: #333;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 2px solid #e1e8ed;
        }

        td {
            padding: 15px;
            border-bottom: 1px solid #f0f0f0;
            color: #666;
            font-size: 14px;
        }

        tr:hover {
            background: #f7f9fc;
        }

        .badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
        }

        .badge-success {
            background: #d4edda;
            color: #155724;
        }

        .badge-danger {
            background: #f8d7da;
            color: #721c24;
        }

        .badge-warning {
            background: #fff3cd;
            color: #856404;
        }

        .badge-info {
            background: #d1ecf1;
            color: #0c5460;
        }

        .pagination {
            padding: 20px;
            text-align: center;
        }

        .pagination a,
        .pagination span {
            display: inline-block;
            padding: 8px 12px;
            margin: 0 4px;
            border: 1px solid #e1e8ed;
            border-radius: 6px;
            color: #667eea;
            text-decoration: none;
        }

        .pagination .current {
            background: #667eea;
            color: white;
            border-color: #667eea;
        }

        .empty-state {
            padding: 60px 20px;
            text-align: center;
            color: #999;
        }

        .empty-state-icon {
            font-size: 64px;
            margin-bottom: 20px;
        }

        @media (max-width: 768px) {
            .filter-grid {
                grid-template-columns: 1fr;
            }
            
            table {
                font-size: 12px;
            }
            
            th, td {
                padding: 10px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📊 Access Logs</h1>
            <p style="color: #666;">Monitor all authentication attempts</p>
        </div>

        <div class="filters">
            <h3>Filters</h3>
            <?php echo form_open('pattern_auth/access_logs', array('method' => 'get')); ?>
                <div class="filter-grid">
                    <div class="filter-group">
                        <label>Username</label>
                        <input type="text" name="username" value="<?php echo htmlspecialchars($filters['username'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" placeholder="Filter by username">
                    </div>
                    
                    <div class="filter-group">
                        <label>Status</label>
                        <select name="status">
                            <option value="">All Statuses</option>
                            <option value="success" <?php echo ($filters['status'] ?? '') == 'success' ? 'selected' : ''; ?>>Success</option>
                            <option value="failed" <?php echo ($filters['status'] ?? '') == 'failed' ? 'selected' : ''; ?>>Failed</option>
                            <option value="blocked" <?php echo ($filters['status'] ?? '') == 'blocked' ? 'selected' : ''; ?>>Blocked</option>
                            <option value="recovered" <?php echo ($filters['status'] ?? '') == 'recovered' ? 'selected' : ''; ?>>Recovered</option>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <label>Date From</label>
                        <input type="date" name="date_from" value="<?php echo htmlspecialchars($filters['date_from'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                    </div>
                    
                    <div class="filter-group">
                        <label>Date To</label>
                        <input type="date" name="date_to" value="<?php echo htmlspecialchars($filters['date_to'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                    </div>
                </div>
                
                <div style="margin-top: 15px;">
                    <button type="submit" class="btn btn-primary">Apply Filters</button>
                    <a href="<?php echo site_url('pattern_auth/access_logs'); ?>" class="btn btn-secondary">Clear</a>
                    <a href="<?php echo current_url(); ?>?<?php echo http_build_query($filters); ?>&export=csv" class="btn btn-success">Export CSV</a>
                </div>
            <?php echo form_close(); ?>
        </div>

        <div class="logs-container">
            <?php if (empty($logs)): ?>
                <div class="empty-state">
                    <div class="empty-state-icon">📝</div>
                    <h3>No logs found</h3>
                    <p>Try adjusting your filters</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Username</th>
                                <th>IP Address</th>
                                <th>Status</th>
                                <th>Type</th>
                                <th>Location</th>
                                <th>Date/Time</th>
                                <th>Failure Reason</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($logs as $log): ?>
                                <tr>
                                    <td>#<?php echo $log['id']; ?></td>
                                    <td><?php echo htmlspecialchars($log['username'] ?? 'N/A', ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><code><?php echo htmlspecialchars($log['ip_address'], ENT_QUOTES, 'UTF-8'); ?></code></td>
                                    <td>
                                        <?php
                                        $badge_class = array(
                                            'success' => 'badge-success',
                                            'failed' => 'badge-danger',
                                            'blocked' => 'badge-warning',
                                            'recovered' => 'badge-info'
                                        );
                                        $class = $badge_class[$log['status']] ?? 'badge-secondary';
                                        ?>
                                        <span class="badge <?php echo $class; ?>"><?php echo ucfirst($log['status']); ?></span>
                                    </td>
                                    <td><?php echo ucfirst(str_replace('_', ' ', $log['attempt_type'])); ?></td>
                                    <td>
                                        <?php if ($log['country'] || $log['city']): ?>
                                            <?php echo htmlspecialchars($log['city'] . ', ' . $log['country'], ENT_QUOTES, 'UTF-8'); ?>
                                        <?php else: ?>
                                            -
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo date('M d, Y H:i', strtotime($log['created_at'])); ?></td>
                                    <td>
                                        <?php if ($log['failure_reason']): ?>
                                            <small style="color: #999;"><?php echo htmlspecialchars($log['failure_reason'], ENT_QUOTES, 'UTF-8'); ?></small>
                                        <?php else: ?>
                                            -
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div class="pagination">
                    <?php
                    $total_pages = ceil($total / $per_page);
                    
                    if ($total_pages > 1):
                        for ($i = 1; $i <= $total_pages; $i++):
                            $query = array_merge($filters, array('page' => $i));
                            if ($i == $page):
                    ?>
                                <span class="current"><?php echo $i; ?></span>
                    <?php
                            else:
                    ?>
                                <a href="?<?php echo http_build_query($query); ?>"><?php echo $i; ?></a>
                    <?php
                            endif;
                        endfor;
                    endif;
                    ?>
                </div>
            <?php endif; ?>
        </div>

        <div style="margin-top: 20px; text-align: center;">
            <a href="<?php echo site_url('pattern_auth/settings'); ?>" class="btn btn-secondary">← Back to Settings</a>
        </div>
    </div>
</body>
</html>
