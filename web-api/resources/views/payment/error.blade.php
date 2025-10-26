<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>خطا در پرداخت</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Tahoma', 'Arial', sans-serif;
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
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
            background: #ef4444;
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
        
        .error-icon {
            width: 50px;
            height: 50px;
            stroke: white;
            stroke-width: 4;
            stroke-linecap: round;
            stroke-linejoin: round;
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
        
        .error-message {
            background: #fee2e2;
            border-right: 4px solid #ef4444;
            border-radius: 10px;
            padding: 15px 20px;
            margin-bottom: 30px;
            text-align: right;
            color: #991b1b;
            font-size: 14px;
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
        
        .buttons {
            display: flex;
            gap: 15px;
            justify-content: center;
            flex-wrap: wrap;
        }
        
        .button {
            display: inline-block;
            padding: 12px 30px;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
            font-size: 16px;
        }
        
        .button-primary {
            background: #667eea;
        }
        
        .button-primary:hover {
            background: #5568d3;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
        
        .button-secondary {
            background: #6b7280;
        }
        
        .button-secondary:hover {
            background: #4b5563;
            transform: translateY(-2px);
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
            
            .buttons {
                flex-direction: column;
            }
            
            .button {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="icon">
            <svg class="error-icon" viewBox="0 0 100 100">
                <circle cx="50" cy="50" r="45" fill="none" stroke="white" stroke-width="4"/>
                <line x1="30" y1="30" x2="70" y2="70" stroke="white"/>
                <line x1="70" y1="30" x2="30" y2="70" stroke="white"/>
            </svg>
        </div>
        
        <h1>پرداخت انجام نشد</h1>
        <p>متأسفانه پرداخت شما انجام نشد. لطفاً دوباره تلاش کنید.</p>
        
        @if(isset($error))
        <div class="error-message">
            {{ $error }}
        </div>
        @endif
        
        @if(isset($payment_id))
        <div class="info">
            <div class="info-item">
                <span class="info-label">شناسه پرداخت:</span>
                <span class="info-value">#{{ $payment_id }}</span>
            </div>
        </div>
        @endif
        
        <div class="buttons">
            <button class="button button-primary" onclick="retryPayment()">
                تلاش مجدد
            </button>
            <button class="button button-secondary" onclick="redirectToDashboard()">
                بازگشت به داشبورد
            </button>
        </div>
    </div>
    
    <script>
        function redirectToDashboard() {
            const frontendUrl = '{{ config("app.frontend_url", "http://localhost:5174") }}';
            window.location.href = frontendUrl + '/dashboard';
        }
        
        function retryPayment() {
            const frontendUrl = '{{ config("app.frontend_url", "http://localhost:5174") }}';
            @if(isset($payment_id))
                window.location.href = frontendUrl + '/payment/failed?payment_id={{ $payment_id }}';
            @else
                window.location.href = frontendUrl + '/dashboard';
            @endif
        }
    </script>
</body>
</html>

