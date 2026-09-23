<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>غير مصرح بالوصول - WCCMS</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8fafc;
            color: #1e293b;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            margin: 0;
        }
        .card {
            background-color: #ffffff;
            border-radius: 12px;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
            padding: 2.5rem;
            max-width: 480px;
            width: 100%;
            text-align: center;
            border-top: 5px solid #ef4444;
        }
        h1 {
            color: #dc2626;
            font-size: 1.5rem;
            margin-bottom: 1rem;
        }
        p {
            font-size: 1.1rem;
            color: #475569;
            line-height: 1.6;
            margin-bottom: 1.5rem;
        }
        .btn {
            display: inline-block;
            background-color: #0284c7;
            color: #ffffff;
            padding: 0.75rem 1.5rem;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 600;
        }
        .btn:hover {
            background-color: #0369a1;
        }
    </style>
</head>
<body>
    <div class="card">
        <h1>غير مصرح بالوصول (403)</h1>
        <p>{{ $message ?? 'غير مصرح لك بالوصول إلى هذه الصفحة' }}</p>
        <a href="{{ config('services.hospital_auth.login_url', 'http://hospital.local/login') }}" class="btn">العودة لبوابة المستشفى</a>
    </div>
</body>
</html>
