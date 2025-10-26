<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>پرداخت موفق</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Tahoma', 'Arial', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
            max-width: 500px;
            width: 100%;
            padding: 40px;
            text-align: center;
            animation: slideIn 0.5s ease-out;
        }
        
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .icon {
            width: 100px;
            height: 100px;
            margin: 0 auto 30px;
            background: #10b981;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            animation: scaleIn 0.6s ease-out 0.2s backwards;
        }
        
        @keyframes scaleIn {
            from {
                transform: scale(0);
            }
            to {
                transform: scale(1);
            }
        }
        
        .checkmark {
            width: 60px;
            height: 60px;
            stroke: white;
            stroke-width: 4;
            stroke-linecap: round;
            stroke-linejoin: round;
            animation: checkmarkDraw 0.8s ease-out 0.4s backwards;
        }
        
        @keyframes checkmarkDraw {
            from {
                stroke-dasharray: 100;
                stroke-dashoffset: 100;
            }
            to {
                stroke-dashoffset: 0;
            }
        }
        
        h1 {
            font-size: 28px;
            color: #1f2937;
            margin-bottom: 15px;
            font-weight: bold;
        }
        
        p {
            font-size: 16px;
            color: #6b7280;
            margin-bottom: 30px;
            line-height: 1.6;
        }
        
        .info {
            background: #f3f4f6;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 30px;
            text-align: right;
        }
        
        .info-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            font-size: 14px;
        }
        
        .info-item:last-child {
            margin-bottom: 0;
        }
        
        .info-label {
            color: #6b7280;
            font-weight: 500;
        }
        
        .info-value {
            color: #1f2937;
            font-weight: bold;
        }
        
        .button {
            display: inline-block;
            padding: 12px 30px;
            background: #667eea;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
            font-size: 16px;
        }
        
        .button:hover {
            background: #5568d3;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
        
        .loading {
            display: none;
            margin-top: 20px;
        }
        
        .loading.active {
            display: block;
        }
        
        @media (max-width: 600px) {
            .container {
                padding: 30px 20px;
            }
            
            h1 {
                font-size: 24px;
            }
            
            .icon {
                width: 80px;
                height: 80px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="icon">
            <svg class="checkmark" viewBox="0 0 100 100">
                <circle cx="50" cy="50" r="45" fill="none" stroke="white" stroke-width="4"/>
                <path d="M 30 50 L 45 65 L 70 35" fill="none" stroke="white"/>
            </svg>
        </div>
        
        <h1>پرداخت با موفقیت انجام شد!</h1>
        <p>پرداخت شما با موفقیت انجام شد و اطلاعات به‌روزرسانی شد.</p>
        
        @if(isset($payment_id))
        <div class="info">
            <div class="info-item">
                <span class="info-label">شناسه پرداخت:</span>
                <span class="info-value">#{{ $payment_id }}</span>
            </div>
        </div>
        @endif
        
        <button class="button" onclick="redirectToDashboard()">بازگشت به داشبورد</button>
        
        <div class="loading" id="loading">
            در حال انتقال...
        </div>
</div>
    
    <script>
        function redirectToDashboard() {
            const frontendUrl = '{{ config("app.frontend_url", "http://localhost:5174") }}';
            window.location.href = frontendUrl + '/dashboard';
        }
        
        // Auto-redirect after 5 seconds
        setTimeout(function() {
            redirectToDashboard();
        }, 5000);
    </script>
</body>
</html>
