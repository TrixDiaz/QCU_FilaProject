<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Access Forbidden</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        body {
            height: 100vh;
            display: flex;
            align-items: center;
            background-color: #f8f9fa;
        }

        .forbidden-container {
            max-width: 600px;
            padding: 40px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        .forbidden-icon {
            font-size: 80px;
            color: #dc3545;
            margin-bottom: 20px;
        }

        .btn-container {
            margin-top: 30px;
            display: flex;
            justify-content: center;
            gap: 15px;
        }

        .instructions {
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 15px;
            margin-top: 20px;
            border-left: 4px solid #0d6efd;
            text-align: left;
        }

        .alert {
            margin-top: 20px;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="forbidden-container mx-auto">
            <div class="forbidden-icon">
                <i class="fas fa-exclamation-circle"></i>
            </div>
            <h1 class="mb-4">Access Forbidden</h1>
            <p class="lead mb-4">Sorry, you don't have permission to access this page.</p>

            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <div class="instructions">
                <h5><i class="fas fa-info-circle me-2"></i>What should I do?</h5>
                <p>If you need access to this page:</p>
                <ul>
                    <li>Click the <strong>"Send Activation to Technician"</strong> button below to request account
                        activation</li>
                    <li>A technician will review your request and grant appropriate permissions</li>

                    <li>For urgent matters and any access request, please contact your system administrator or IT
                        department directly</li>

                </ul>
            </div>

            <div class="btn-container">
                <form method="POST" action="{{ route('logout.and.home') }}">
                    @csrf
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-home me-1"></i> Return Home & Logout
                    </button>
                </form>

                <form method="POST" action="{{ route('send.technician.activation') }}">
                    @csrf
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-paper-plane me-1"></i> Send Activation to Technician
                    </button>
                </form>
            </div>

            <p class="text-muted mt-4">
                <small>
                    <i class="fas fa-headset me-1"></i> Need help? Contact your system administrator or Technician
                </small>
            </p>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://kit.fontawesome.com/your-fontawesome-kit.js"></script>
</body>

</html>
