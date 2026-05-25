<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $code ?? 'Error' }} - {{ $message ?? 'Something went wrong' }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <link rel="icon" href="{{ asset('img/aarpiz-fav.png') }}" type="image/png">

    <style>
        body {
            background: #f8f9fa;
            font-family: 'Poppins', sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .error-container {
            text-align: center;
            max-width: 600px;
            padding: 30px;
            background: #fff;
            border-radius: 15px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }
        .error-code {
            font-size: 80px;
            font-weight: bold;
            color: #dc3545;
        }
        .error-message {
            font-size: 24px;
            margin-bottom: 20px;
            color: #495057;
        }
        .error-description {
            font-size: 16px;
            color: #6c757d;
            margin-bottom: 30px;
        }
        .btn-home {
            background: #007bff;
            color: #fff;
            padding: 10px 25px;
            border-radius: 50px;
            text-decoration: none;
        }
        .btn-home:hover {
            background: #0056b3;
        }
    </style>
</head>
<body>
    <div class="error-container">
        <div class="error-code">{{ $code ?? 'Oops!' }}</div>
        <div class="error-message">{{ $message ?? 'Something went wrong' }}</div>
        <div class="error-description">{{ $description ?? 'Please try again or contact support if the problem persists.' }}</div>
        <a href="{{ url('/') }}" class="btn-home">Go to Homepage</a>
    </div>
</body>
</html>
