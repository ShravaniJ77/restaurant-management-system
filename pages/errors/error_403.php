<?php
// Error 403 - Access Forbidden
header('HTTP/1.1 403 Forbidden');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>403 - Access Forbidden</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
        }
        .error-container {
            background: white;
            border-radius: 20px;
            padding: 3rem;
            text-align: center;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            max-width: 500px;
            width: 100%;
        }
        .error-code {
            font-size: 5rem;
            font-weight: 800;
            color: #f5576c;
            margin: 0;
            line-height: 1;
        }
        .error-icon {
            font-size: 4rem;
            color: #f5576c;
            margin: 1rem 0;
        }
        .error-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: #1f2937;
            margin: 1rem 0;
        }
        .error-message {
            color: #6b7280;
            margin: 1rem 0 2rem 0;
            font-size: 1rem;
        }
        .btn-home {
            padding: 0.75rem 2rem;
            border-radius: 12px;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s ease;
        }
        .btn-home:hover {
            transform: translateY(-2px);
        }
    </style>
</head>
<body>
    <div class="error-container">
        <div class="error-icon">
            <i class="bi bi-lock-fill"></i>
        </div>
        <h1 class="error-code">403</h1>
        <h2 class="error-title">Access Forbidden</h2>
        <p class="error-message">
            You don't have permission to access this resource. If you believe this is a mistake, please contact the administrator.
        </p>
        <a href="/restaurant-management-system/pages/dashboard/dashboard.php" class="btn btn-outline-primary btn-home">
            <i class="bi bi-house"></i> Go to Dashboard
        </a>
    </div>
</body>
</html>
