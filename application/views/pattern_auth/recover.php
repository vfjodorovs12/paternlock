<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recover Access - CodeIgniter Pattern Lock</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .recover-container {
            background: white;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            padding: 40px;
            max-width: 500px;
            width: 100%;
        }

        .recover-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .recover-header h1 {
            color: #333;
            font-size: 28px;
            margin-bottom: 10px;
        }

        .recover-header p {
            color: #666;
            font-size: 14px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            color: #333;
            font-weight: 500;
            margin-bottom: 8px;
            font-size: 14px;
        }

        .form-group input {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #e1e8ed;
            border-radius: 8px;
            font-size: 14px;
            transition: border-color 0.3s;
        }

        .form-group input:focus {
            outline: none;
            border-color: #667eea;
        }

        .alert {
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
        }

        .alert-error {
            background: #fee;
            border: 1px solid #fcc;
            color: #c33;
        }

        .alert-success {
            background: #efe;
            border: 1px solid #cfc;
            color: #3c3;
        }

        .alert-info {
            background: #e8f4fd;
            border: 1px solid #bee5eb;
            color: #0c5460;
        }

        .btn {
            display: block;
            width: 100%;
            padding: 14px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(102, 126, 234, 0.4);
        }

        .links {
            margin-top: 20px;
            text-align: center;
            font-size: 14px;
        }

        .links a {
            color: #667eea;
            text-decoration: none;
        }

        .links a:hover {
            text-decoration: underline;
        }

        .info-box {
            background: #f7f9fc;
            padding: 15px;
            border-radius: 8px;
            margin: 20px 0;
            font-size: 13px;
            color: #666;
        }

        .info-box strong {
            display: block;
            margin-bottom: 8px;
            color: #333;
        }

        @media (max-width: 480px) {
            .recover-container {
                padding: 30px 20px;
            }

            .recover-header h1 {
                font-size: 24px;
            }
        }
    </style>
</head>
<body>
    <div class="recover-container">
        <div class="recover-header">
            <h1>🔑 Recover Access</h1>
            <p>Use your backup code to regain access</p>
        </div>

        <?php if ($this->session->flashdata('error')): ?>
            <div class="alert alert-error">
                <?php echo htmlspecialchars($this->session->flashdata('error'), ENT_QUOTES, 'UTF-8'); ?>
            </div>
        <?php endif; ?>

        <?php if ($this->session->flashdata('success')): ?>
            <div class="alert alert-success">
                <?php echo htmlspecialchars($this->session->flashdata('success'), ENT_QUOTES, 'UTF-8'); ?>
            </div>
        <?php endif; ?>

        <div class="info-box">
            <strong>How to recover:</strong>
            Enter your username and the backup code you received when setting up pattern authentication.
            The backup code is in the format: XXXX-XXXX-XXXX-XXXX
        </div>

        <?php echo form_open('pattern_auth/recover'); ?>
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required autofocus 
                       placeholder="Enter your username">
            </div>

            <div class="form-group">
                <label for="backup_code">Backup Recovery Code</label>
                <input type="text" id="backup_code" name="backup_code" required 
                       placeholder="XXXX-XXXX-XXXX-XXXX" 
                       pattern="[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{4}"
                       style="text-transform: uppercase; font-family: 'Courier New', monospace; letter-spacing: 1px;">
            </div>

            <button type="submit" class="btn btn-primary">
                Recover Access
            </button>
        <?php echo form_close(); ?>

        <div class="links">
            <a href="<?php echo site_url('pattern_auth/login'); ?>">← Back to Login</a>
        </div>
    </div>

    <script>
        // Auto-format backup code as user types
        document.getElementById('backup_code').addEventListener('input', function(e) {
            let value = e.target.value.replace(/[^A-Z0-9]/g, '').toUpperCase();
            
            if (value.length > 16) {
                value = value.substr(0, 16);
            }
            
            let formatted = '';
            for (let i = 0; i < value.length; i++) {
                if (i > 0 && i % 4 === 0) {
                    formatted += '-';
                }
                formatted += value[i];
            }
            
            e.target.value = formatted;
        });
    </script>
</body>
</html>
