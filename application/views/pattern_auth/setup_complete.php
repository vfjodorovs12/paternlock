<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setup Complete - CodeIgniter Pattern Lock</title>
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

        .complete-container {
            background: white;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            padding: 40px;
            max-width: 500px;
            width: 100%;
            text-align: center;
        }

        .success-icon {
            font-size: 64px;
            margin-bottom: 20px;
        }

        h1 {
            color: #333;
            font-size: 28px;
            margin-bottom: 10px;
        }

        p {
            color: #666;
            margin-bottom: 20px;
            line-height: 1.6;
        }

        .backup-code-box {
            background: #f7f9fc;
            border: 2px dashed #667eea;
            border-radius: 12px;
            padding: 30px;
            margin: 30px 0;
        }

        .backup-code-label {
            font-size: 14px;
            color: #666;
            font-weight: 500;
            margin-bottom: 10px;
        }

        .backup-code {
            font-size: 24px;
            font-weight: 700;
            color: #667eea;
            font-family: 'Courier New', monospace;
            letter-spacing: 2px;
            margin: 15px 0;
        }

        .warning-box {
            background: #fff3cd;
            border: 1px solid #ffc107;
            border-radius: 8px;
            padding: 15px;
            margin: 20px 0;
            text-align: left;
        }

        .warning-box strong {
            color: #856404;
            display: block;
            margin-bottom: 8px;
        }

        .warning-box ul {
            margin-left: 20px;
            color: #856404;
            font-size: 14px;
        }

        .warning-box li {
            margin: 5px 0;
        }

        .btn {
            display: inline-block;
            padding: 14px 30px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            margin: 5px;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(102, 126, 234, 0.4);
        }

        .btn-secondary {
            background: #e1e8ed;
            color: #666;
        }

        .btn-secondary:hover {
            background: #cbd5e0;
        }

        @media print {
            body {
                background: white;
            }
            
            .btn {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="complete-container">
        <div class="success-icon">✅</div>
        
        <h1>Pattern Lock Setup Complete!</h1>
        <p>Your pattern authentication has been successfully configured.</p>

        <div class="backup-code-box">
            <div class="backup-code-label">Your Backup Recovery Code</div>
            <div class="backup-code"><?php echo htmlspecialchars($backup_code, ENT_QUOTES, 'UTF-8'); ?></div>
        </div>

        <div class="warning-box">
            <strong>⚠️ Important Security Information:</strong>
            <ul>
                <li>Save this backup code in a secure location</li>
                <li>You'll need it if you forget your pattern</li>
                <li>This code will not be shown again</li>
                <li>Keep it confidential - anyone with this code can access your account</li>
            </ul>
        </div>

        <div style="margin-top: 30px;">
            <button class="btn btn-secondary" onclick="window.print()">
                🖨️ Print This Page
            </button>
            <a href="<?php echo site_url('pattern_auth/login'); ?>" class="btn btn-primary">
                Continue to Login
            </a>
        </div>
    </div>

    <script>
        // Copy to clipboard functionality
        document.querySelector('.backup-code').addEventListener('click', function() {
            const code = this.textContent;
            navigator.clipboard.writeText(code).then(() => {
                const original = this.textContent;
                this.textContent = 'Copied!';
                setTimeout(() => {
                    this.textContent = original;
                }, 2000);
            });
        });
    </script>
</body>
</html>
