<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>الدفع</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <style>
        body {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            margin: 0;
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
        }
        .container {
            padding: 20px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            max-width: 700px;
            width: 100%;
        }
        h1 {
            color: #0d6efd;
            font-size: 1.7em;
            margin-bottom: 12px;
            text-align: center;
        }
        label {
            display: block;
            margin-bottom: 6px;
            color: #333;
            font-weight: 600;
        }
        input, select {
            width: 100%;
            padding: 10px;
            border-radius: 6px;
            border: 1px solid #ddd;
            margin-bottom: 12px;
            outline: none;
        }
        button {
            width: 100%;
            padding: 11px 14px;
            border: 0;
            border-radius: 6px;
            background: #0d6efd;
            color: #fff;
            font-weight: 700;
            cursor: pointer;
        }
        .error {
            color: #dc3545;
            font-size: 14px;
            margin: 8px 0 12px;
            text-align: center;
        }
        .grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
        }
        @media (max-width: 640px) {
            .grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
<div class="container">
    <h1>إتمام الدفع</h1>

    @if ($errors->any())
        <div class="error">{{ $errors->first() }}</div>
    @endif

    <form method="POST" action="{{ route('payment.process.web') }}">
        @csrf

        <label>المبلغ</label>
        <input type="number" name="amount" step="0.01" min="1" value="{{ old('amount', 1) }}" required>

        <label>العملة</label>
        <select name="currency" required>
            <option value="EGP" {{ old('currency') === 'EGP' ? 'selected' : '' }}>EGP</option>
            <option value="USD" {{ old('currency', 'USD') === 'USD' ? 'selected' : '' }}>USD</option>
        </select>

        <div class="grid">
            <div>
                <label>الاسم الأول</label>
                <input type="text" name="first_name" value="{{ old('first_name') }}" required>
            </div>
            <div>
                <label>اسم العائلة</label>
                <input type="text" name="last_name" value="{{ old('last_name') }}" required>
            </div>
        </div>

        <div class="grid">
            <div>
                <label>رقم الهاتف</label>
                <input type="text" name="phone_number" value="{{ old('phone_number') }}" required>
            </div>
            <div>
                <label>البريد الإلكتروني</label>
                <input type="email" name="email" value="{{ old('email') }}" required>
            </div>
        </div>

        <button type="submit">ادفع الآن</button>
    </form>
</div>
</body>
</html>

