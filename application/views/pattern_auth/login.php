<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pattern Login - CodeIgniter Pattern Lock</title>
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

        .login-container {
            background: white;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            padding: 40px;
            max-width: 500px;
            width: 100%;
        }

        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .login-header h1 {
            color: #333;
            font-size: 28px;
            margin-bottom: 10px;
        }

        .login-header p {
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

        .form-group input[type="text"],
        .form-group input[type="password"] {
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

        .pattern-container {
            text-align: center;
            margin: 25px 0;
        }

        .pattern-canvas {
            border: 2px solid #e1e8ed;
            border-radius: 12px;
            cursor: crosshair;
            touch-action: none;
            background: #f7f9fc;
            box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.05);
        }

        .pattern-info {
            margin-top: 12px;
            font-size: 13px;
            color: #666;
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

        .alert-warning {
            background: #ffeaa7;
            border: 1px solid #fdcb6e;
            color: #d63031;
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

        .btn-primary:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(102, 126, 234, 0.4);
        }

        .btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        .links {
            margin-top: 20px;
            text-align: center;
            font-size: 14px;
        }

        .links a {
            color: #667eea;
            text-decoration: none;
            margin: 0 10px;
        }

        .links a:hover {
            text-decoration: underline;
        }

        .recaptcha-container {
            margin: 20px 0;
            display: flex;
            justify-content: center;
        }

        @media (max-width: 480px) {
            .login-container {
                padding: 30px 20px;
            }

            .login-header h1 {
                font-size: 24px;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <h1>🔐 Pattern Login</h1>
            <p>Draw your pattern to login</p>
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

        <?php if (isset($locked) && $locked): ?>
            <div class="alert alert-warning">
                Your account is temporarily locked. Please wait <span id="countdown"><?php echo $time_remaining; ?></span> seconds.
            </div>
        <?php else: ?>
            <?php echo form_open('pattern_auth/login', array('id' => 'patternForm')); ?>
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" required autofocus>
                </div>

                <div class="pattern-container">
                    <label>Draw Your Pattern</label>
                    <canvas id="patternCanvas" class="pattern-canvas" width="300" height="300"></canvas>
                    <div class="pattern-info">
                        <span id="patternStatus">Touch or drag to draw pattern</span>
                    </div>
                </div>

                <input type="hidden" id="patternInput" name="pattern" required>

                <?php if (isset($show_recaptcha) && $show_recaptcha): ?>
                    <div class="recaptcha-container">
                        <?php echo $recaptcha_html; ?>
                    </div>
                <?php endif; ?>

                <button type="submit" class="btn btn-primary" id="submitBtn" disabled>
                    Login with Pattern
                </button>
            <?php echo form_close(); ?>
        <?php endif; ?>

        <div class="links">
            <a href="<?php echo site_url('pattern_auth/recover'); ?>">Forgot Pattern?</a>
            <?php if ($this->config->item('pattern_lock_allow_password_fallback')): ?>
                <a href="<?php echo site_url($this->config->item('pattern_lock_password_login_url')); ?>">Use Password</a>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // Pattern Lock Canvas Implementation
        (function() {
            const canvas = document.getElementById('patternCanvas');
            const ctx = canvas.getContext('2d');
            const patternInput = document.getElementById('patternInput');
            const patternStatus = document.getElementById('patternStatus');
            const submitBtn = document.getElementById('submitBtn');
            
            const gridSize = <?php echo isset($grid_size) ? $grid_size : 3; ?>;
            const canvasSize = 300;
            const pointRadius = 20;
            const spacing = canvasSize / (gridSize + 1);
            
            let points = [];
            let selectedPoints = [];
            let isDrawing = false;
            let currentPos = null;

            // Initialize grid points
            function initPoints() {
                points = [];
                for (let row = 0; row < gridSize; row++) {
                    for (let col = 0; col < gridSize; col++) {
                        points.push({
                            index: row * gridSize + col,
                            x: spacing * (col + 1),
                            y: spacing * (row + 1),
                            selected: false
                        });
                    }
                }
            }

            // Draw the pattern grid
            function draw() {
                ctx.clearRect(0, 0, canvasSize, canvasSize);

                // Draw connections
                if (selectedPoints.length > 0) {
                    ctx.strokeStyle = '#667eea';
                    ctx.lineWidth = 3;
                    ctx.lineCap = 'round';
                    ctx.lineJoin = 'round';
                    
                    ctx.beginPath();
                    ctx.moveTo(points[selectedPoints[0]].x, points[selectedPoints[0]].y);
                    
                    for (let i = 1; i < selectedPoints.length; i++) {
                        ctx.lineTo(points[selectedPoints[i]].x, points[selectedPoints[i]].y);
                    }
                    
                    // Draw line to current position if drawing
                    if (isDrawing && currentPos) {
                        ctx.lineTo(currentPos.x, currentPos.y);
                    }
                    
                    ctx.stroke();
                }

                // Draw points
                points.forEach((point, idx) => {
                    ctx.beginPath();
                    ctx.arc(point.x, point.y, pointRadius, 0, Math.PI * 2);
                    
                    if (point.selected) {
                        ctx.fillStyle = '#667eea';
                        ctx.fill();
                        ctx.strokeStyle = '#4c5fd5';
                        ctx.lineWidth = 2;
                        ctx.stroke();
                        
                        // Draw inner circle
                        ctx.beginPath();
                        ctx.arc(point.x, point.y, 8, 0, Math.PI * 2);
                        ctx.fillStyle = 'white';
                        ctx.fill();
                    } else {
                        ctx.fillStyle = 'white';
                        ctx.fill();
                        ctx.strokeStyle = '#cbd5e0';
                        ctx.lineWidth = 2;
                        ctx.stroke();
                    }
                });
            }

            // Get point at position
            function getPointAt(x, y) {
                const rect = canvas.getBoundingClientRect();
                const scaleX = canvas.width / rect.width;
                const scaleY = canvas.height / rect.height;
                const canvasX = (x - rect.left) * scaleX;
                const canvasY = (y - rect.top) * scaleY;

                for (let point of points) {
                    const distance = Math.sqrt(
                        Math.pow(canvasX - point.x, 2) + 
                        Math.pow(canvasY - point.y, 2)
                    );
                    if (distance <= pointRadius) {
                        return point;
                    }
                }
                return null;
            }

            // Start drawing
            function startDrawing(x, y) {
                const point = getPointAt(x, y);
                if (point && !point.selected) {
                    isDrawing = true;
                    selectPoint(point);
                }
            }

            // Continue drawing
            function continueDrawing(x, y) {
                if (!isDrawing) return;

                const rect = canvas.getBoundingClientRect();
                const scaleX = canvas.width / rect.width;
                const scaleY = canvas.height / rect.height;
                currentPos = {
                    x: (x - rect.left) * scaleX,
                    y: (y - rect.top) * scaleY
                };

                const point = getPointAt(x, y);
                if (point && !point.selected) {
                    selectPoint(point);
                }

                draw();
            }

            // Stop drawing
            function stopDrawing() {
                if (isDrawing) {
                    isDrawing = false;
                    currentPos = null;
                    
                    if (selectedPoints.length > 0) {
                        patternInput.value = JSON.stringify(selectedPoints);
                        submitBtn.disabled = false;
                        patternStatus.textContent = `Pattern set (${selectedPoints.length} points)`;
                        patternStatus.style.color = '#27ae60';
                    }
                    
                    draw();
                }
            }

            // Select a point
            function selectPoint(point) {
                point.selected = true;
                selectedPoints.push(point.index);
            }

            // Reset pattern
            function reset() {
                selectedPoints = [];
                points.forEach(p => p.selected = false);
                patternInput.value = '';
                submitBtn.disabled = true;
                patternStatus.textContent = 'Touch or drag to draw pattern';
                patternStatus.style.color = '#666';
                draw();
            }

            // Mouse events
            canvas.addEventListener('mousedown', (e) => {
                e.preventDefault();
                reset();
                startDrawing(e.clientX, e.clientY);
            });

            canvas.addEventListener('mousemove', (e) => {
                e.preventDefault();
                continueDrawing(e.clientX, e.clientY);
            });

            canvas.addEventListener('mouseup', (e) => {
                e.preventDefault();
                stopDrawing();
            });

            canvas.addEventListener('mouseleave', (e) => {
                stopDrawing();
            });

            // Touch events
            canvas.addEventListener('touchstart', (e) => {
                e.preventDefault();
                reset();
                const touch = e.touches[0];
                startDrawing(touch.clientX, touch.clientY);
            });

            canvas.addEventListener('touchmove', (e) => {
                e.preventDefault();
                const touch = e.touches[0];
                continueDrawing(touch.clientX, touch.clientY);
            });

            canvas.addEventListener('touchend', (e) => {
                e.preventDefault();
                stopDrawing();
            });

            // Initialize
            initPoints();
            draw();

            // Countdown timer for locked accounts
            <?php if (isset($locked) && $locked): ?>
                let timeRemaining = <?php echo $time_remaining; ?>;
                const countdownElement = document.getElementById('countdown');
                const countdownInterval = setInterval(() => {
                    timeRemaining--;
                    countdownElement.textContent = timeRemaining;
                    if (timeRemaining <= 0) {
                        clearInterval(countdownInterval);
                        location.reload();
                    }
                }, 1000);
            <?php endif; ?>
        })();
    </script>
</body>
</html>
