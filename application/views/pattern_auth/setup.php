<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setup Pattern Lock - CodeIgniter Pattern Lock</title>
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

        .setup-container {
            background: white;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            padding: 40px;
            max-width: 600px;
            width: 100%;
        }

        .setup-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .setup-header h1 {
            color: #333;
            font-size: 28px;
            margin-bottom: 10px;
        }

        .setup-header p {
            color: #666;
            font-size: 14px;
        }

        .steps {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
        }

        .step {
            flex: 1;
            text-align: center;
            position: relative;
        }

        .step::after {
            content: '';
            position: absolute;
            top: 15px;
            left: 60%;
            width: 80%;
            height: 2px;
            background: #e1e8ed;
            z-index: 0;
        }

        .step:last-child::after {
            display: none;
        }

        .step-number {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: #e1e8ed;
            color: #999;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            margin-bottom: 8px;
            position: relative;
            z-index: 1;
        }

        .step.active .step-number {
            background: #667eea;
            color: white;
        }

        .step.completed .step-number {
            background: #27ae60;
            color: white;
        }

        .step-label {
            font-size: 12px;
            color: #666;
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

        .form-group input[type="text"] {
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
            margin: 0 auto;
            display: block;
        }

        .pattern-info {
            margin-top: 12px;
            font-size: 13px;
            color: #666;
        }

        .strength-meter {
            margin-top: 15px;
        }

        .strength-label {
            font-size: 13px;
            font-weight: 500;
            margin-bottom: 8px;
        }

        .strength-bar {
            height: 8px;
            background: #e1e8ed;
            border-radius: 4px;
            overflow: hidden;
        }

        .strength-fill {
            height: 100%;
            transition: width 0.3s, background-color 0.3s;
            border-radius: 4px;
        }

        .strength-0 { width: 0%; background: #e1e8ed; }
        .strength-1 { width: 20%; background: #e74c3c; }
        .strength-2 { width: 40%; background: #e67e22; }
        .strength-3 { width: 60%; background: #f39c12; }
        .strength-4 { width: 80%; background: #3498db; }
        .strength-5 { width: 100%; background: #27ae60; }

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
            margin-top: 10px;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .btn-primary:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(102, 126, 234, 0.4);
        }

        .btn-secondary {
            background: #e1e8ed;
            color: #666;
        }

        .btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        .hidden {
            display: none;
        }

        .requirements {
            background: #f7f9fc;
            padding: 15px;
            border-radius: 8px;
            margin: 15px 0;
            font-size: 13px;
        }

        .requirements ul {
            margin-left: 20px;
            color: #666;
        }

        .requirements li {
            margin: 5px 0;
        }

        @media (max-width: 480px) {
            .setup-container {
                padding: 30px 20px;
            }

            .setup-header h1 {
                font-size: 24px;
            }
        }
    </style>
</head>
<body>
    <div class="setup-container">
        <div class="setup-header">
            <h1>🔐 Setup Pattern Lock</h1>
            <p>Create a secure pattern for authentication</p>
        </div>

        <div class="steps">
            <div class="step active" id="step1">
                <div class="step-number">1</div>
                <div class="step-label">Draw Pattern</div>
            </div>
            <div class="step" id="step2">
                <div class="step-number">2</div>
                <div class="step-label">Confirm</div>
            </div>
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

        <div class="requirements">
            <strong>Pattern Requirements:</strong>
            <ul>
                <li>Minimum <?php echo $min_points; ?> points required</li>
                <li>Use direction changes for stronger patterns</li>
                <li>Avoid simple straight lines</li>
                <li>Mix adjacent and non-adjacent points</li>
            </ul>
        </div>

        <?php echo form_open('pattern_auth/setup', array('id' => 'setupForm')); ?>
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required autofocus>
            </div>

            <!-- Step 1: Draw Pattern -->
            <div id="stepOne">
                <div class="pattern-container">
                    <label>Draw Your Pattern</label>
                    <canvas id="patternCanvas" class="pattern-canvas" width="300" height="300"></canvas>
                    <div class="pattern-info">
                        <span id="patternStatus">Touch or drag to draw pattern</span>
                    </div>
                </div>

                <div class="strength-meter" id="strengthMeter" style="display: none;">
                    <div class="strength-label">
                        Pattern Strength: <span id="strengthText">-</span>
                    </div>
                    <div class="strength-bar">
                        <div class="strength-fill" id="strengthFill"></div>
                    </div>
                </div>

                <button type="button" class="btn btn-primary" id="nextBtn" disabled>
                    Next: Confirm Pattern
                </button>
                <button type="button" class="btn btn-secondary" id="resetBtn">
                    Reset Pattern
                </button>
            </div>

            <!-- Step 2: Confirm Pattern -->
            <div id="stepTwo" class="hidden">
                <div class="alert alert-info">
                    Please draw the same pattern again to confirm.
                </div>

                <div class="pattern-container">
                    <label>Confirm Your Pattern</label>
                    <canvas id="confirmCanvas" class="pattern-canvas" width="300" height="300"></canvas>
                    <div class="pattern-info">
                        <span id="confirmStatus">Redraw your pattern to confirm</span>
                    </div>
                </div>

                <input type="hidden" name="pattern" id="patternInput">
                <input type="hidden" name="pattern_confirm" id="patternConfirmInput">
                <input type="hidden" name="step" value="confirm">

                <button type="submit" class="btn btn-primary" id="submitBtn" disabled>
                    Setup Pattern Lock
                </button>
                <button type="button" class="btn btn-secondary" id="backBtn">
                    Back
                </button>
            </div>
        <?php echo form_close(); ?>
    </div>

    <script>
        // Pattern Lock Setup Implementation
        (function() {
            const gridSize = <?php echo $grid_size; ?>;
            const minPoints = <?php echo $min_points; ?>;
            const canvasSize = 300;
            const pointRadius = 20;
            const spacing = canvasSize / (gridSize + 1);

            let currentStep = 1;
            let pattern1 = [];
            let pattern2 = [];

            // Step 1 Canvas
            const canvas1 = document.getElementById('patternCanvas');
            const ctx1 = canvas1.getContext('2d');
            const patternDrawer1 = new PatternDrawer(canvas1, ctx1, (pattern) => {
                pattern1 = pattern;
                document.getElementById('patternInput').value = JSON.stringify(pattern);
                updateStrength(pattern);
                document.getElementById('nextBtn').disabled = pattern.length < minPoints;
            });

            // Step 2 Canvas
            const canvas2 = document.getElementById('confirmCanvas');
            const ctx2 = canvas2.getContext('2d');
            const patternDrawer2 = new PatternDrawer(canvas2, ctx2, (pattern) => {
                pattern2 = pattern;
                document.getElementById('patternConfirmInput').value = JSON.stringify(pattern);
                
                const match = JSON.stringify(pattern1) === JSON.stringify(pattern2);
                const confirmStatus = document.getElementById('confirmStatus');
                document.getElementById('submitBtn').disabled = !match;
                
                if (pattern.length > 0) {
                    if (match) {
                        confirmStatus.textContent = '✓ Patterns match!';
                        confirmStatus.style.color = '#27ae60';
                    } else {
                        confirmStatus.textContent = '✗ Patterns do not match';
                        confirmStatus.style.color = '#e74c3c';
                    }
                }
            });

            // Pattern Drawer Class
            function PatternDrawer(canvas, ctx, onComplete) {
                let points = [];
                let selectedPoints = [];
                let isDrawing = false;
                let currentPos = null;

                this.reset = function() {
                    selectedPoints = [];
                    points.forEach(p => p.selected = false);
                    this.draw();
                };

                this.initPoints = function() {
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
                };

                this.draw = function() {
                    ctx.clearRect(0, 0, canvasSize, canvasSize);

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
                        
                        if (isDrawing && currentPos) {
                            ctx.lineTo(currentPos.x, currentPos.y);
                        }
                        
                        ctx.stroke();
                    }

                    points.forEach((point) => {
                        ctx.beginPath();
                        ctx.arc(point.x, point.y, pointRadius, 0, Math.PI * 2);
                        
                        if (point.selected) {
                            ctx.fillStyle = '#667eea';
                            ctx.fill();
                            ctx.strokeStyle = '#4c5fd5';
                            ctx.lineWidth = 2;
                            ctx.stroke();
                            
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
                };

                const getPointAt = (x, y) => {
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
                };

                const startDrawing = (x, y) => {
                    const point = getPointAt(x, y);
                    if (point && !point.selected) {
                        isDrawing = true;
                        point.selected = true;
                        selectedPoints.push(point.index);
                    }
                };

                const continueDrawing = (x, y) => {
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
                        point.selected = true;
                        selectedPoints.push(point.index);
                    }

                    this.draw();
                };

                const stopDrawing = () => {
                    if (isDrawing) {
                        isDrawing = false;
                        currentPos = null;
                        onComplete(selectedPoints.slice());
                        this.draw();
                    }
                };

                // Mouse events
                canvas.addEventListener('mousedown', (e) => {
                    e.preventDefault();
                    this.reset();
                    startDrawing(e.clientX, e.clientY);
                });

                canvas.addEventListener('mousemove', (e) => {
                    e.preventDefault();
                    continueDrawing.call(this, e.clientX, e.clientY);
                });

                canvas.addEventListener('mouseup', (e) => {
                    e.preventDefault();
                    stopDrawing.call(this);
                });

                canvas.addEventListener('mouseleave', () => stopDrawing.call(this));

                // Touch events
                canvas.addEventListener('touchstart', (e) => {
                    e.preventDefault();
                    this.reset();
                    const touch = e.touches[0];
                    startDrawing(touch.clientX, touch.clientY);
                });

                canvas.addEventListener('touchmove', (e) => {
                    e.preventDefault();
                    const touch = e.touches[0];
                    continueDrawing.call(this, touch.clientX, touch.clientY);
                });

                canvas.addEventListener('touchend', (e) => {
                    e.preventDefault();
                    stopDrawing.call(this);
                });

                this.initPoints();
                this.draw();
            }

            // Update strength meter
            function updateStrength(pattern) {
                if (pattern.length === 0) {
                    document.getElementById('strengthMeter').style.display = 'none';
                    return;
                }

                document.getElementById('strengthMeter').style.display = 'block';
                
                // Simple strength calculation (mimic server-side logic)
                let strength = 0;
                if (pattern.length >= 4) strength++;
                if (pattern.length >= 6) strength++;
                if (pattern.length >= 8) strength++;
                
                // Direction changes
                let dirChanges = 0;
                for (let i = 2; i < pattern.length; i++) {
                    const dir1 = getDirection(pattern[i-2], pattern[i-1]);
                    const dir2 = getDirection(pattern[i-1], pattern[i]);
                    if (dir1 !== dir2) dirChanges++;
                }
                if (dirChanges >= 2) strength++;
                
                // Non-adjacent points
                let hasNonAdjacent = false;
                for (let i = 1; i < pattern.length; i++) {
                    if (getDistance(pattern[i-1], pattern[i]) > 1.5) {
                        hasNonAdjacent = true;
                        break;
                    }
                }
                if (hasNonAdjacent) strength++;

                const labels = ['Very Weak', 'Weak', 'Fair', 'Good', 'Strong', 'Very Strong'];
                document.getElementById('strengthText').textContent = labels[strength];
                document.getElementById('strengthFill').className = 'strength-fill strength-' + strength;
            }

            function getDirection(from, to) {
                const fromRow = Math.floor(from / gridSize);
                const fromCol = from % gridSize;
                const toRow = Math.floor(to / gridSize);
                const toCol = to % gridSize;
                return (toRow - fromRow) + ',' + (toCol - fromCol);
            }

            function getDistance(from, to) {
                const fromRow = Math.floor(from / gridSize);
                const fromCol = from % gridSize;
                const toRow = Math.floor(to / gridSize);
                const toCol = to % gridSize;
                return Math.sqrt(Math.pow(toRow - fromRow, 2) + Math.pow(toCol - fromCol, 2));
            }

            // Navigation
            document.getElementById('nextBtn').addEventListener('click', () => {
                document.getElementById('stepOne').classList.add('hidden');
                document.getElementById('stepTwo').classList.remove('hidden');
                document.getElementById('step1').classList.remove('active');
                document.getElementById('step1').classList.add('completed');
                document.getElementById('step2').classList.add('active');
            });

            document.getElementById('backBtn').addEventListener('click', () => {
                document.getElementById('stepTwo').classList.add('hidden');
                document.getElementById('stepOne').classList.remove('hidden');
                document.getElementById('step2').classList.remove('active');
                document.getElementById('step1').classList.add('active');
                document.getElementById('step1').classList.remove('completed');
                patternDrawer2.reset();
            });

            document.getElementById('resetBtn').addEventListener('click', () => {
                patternDrawer1.reset();
                pattern1 = [];
                document.getElementById('nextBtn').disabled = true;
                document.getElementById('strengthMeter').style.display = 'none';
            });
        })();
    </script>
</body>
</html>
