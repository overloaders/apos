<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Login') - POS Supermarket</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <style>
        html, body {
            height: 100%;
            margin: 0;
            padding: 0;
        }
        body {
            background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .auth-wrapper {
            width: 100%;
            max-width: 400px;
            padding: 15px;
        }
        .auth-card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            overflow: hidden;
        }
        .auth-header {
            text-align: center;
            padding: 2.5rem 2rem 1rem;
            background: #fff;
        }
        .auth-header .logo-icon {
            width: 64px;
            height: 64px;
            background: linear-gradient(135deg, #0d6efd, #0a58ca);
            border-radius: 16px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1rem;
            box-shadow: 0 4px 15px rgba(13,110,253,0.3);
        }
        .auth-header .logo-icon i {
            color: #fff;
            font-size: 1.8rem;
        }
        .auth-body {
            padding: 2rem 2.5rem 2.5rem;
            background: #fff;
        }
        .auth-body .form-group label {
            font-weight: 600;
            font-size: 0.85rem;
            color: #495057;
        }
        .auth-body .form-control {
            border-radius: 8px;
            padding: 0.7rem 1rem;
            border: 1px solid #dee2e6;
        }
        .auth-body .form-control:focus {
            border-color: #0d6efd;
            box-shadow: 0 0 0 0.2rem rgba(13,110,253,0.15);
        }
        .btn-login {
            border-radius: 8px;
            padding: 0.7rem;
            font-weight: 600;
            font-size: 1rem;
            background: linear-gradient(135deg, #0d6efd, #0a58ca);
            border: none;
        }
        .btn-login:hover {
            background: linear-gradient(135deg, #0a58ca, #084298);
        }
    </style>
</head>
<body>
    <div class="auth-wrapper">
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible text-center mb-3" style="border-radius:10px;">
                {{ session('error') }}
                <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
            </div>
        @endif
        <div class="card auth-card">
            <div class="auth-header">
                @php $loginLogo = App\Models\CompanySetting::instance()->logo; @endphp
                @if($loginLogo)
                    <img src="{{ asset('storage/' . $loginLogo) }}" alt="Logo" style="max-height:80px; max-width:100%; border-radius:16px; box-shadow:0 4px 15px rgba(0,0,0,0.15);">
                @else
                    <div class="logo-icon">
                        <i class="fas fa-store"></i>
                    </div>
                @endif
                <h4 class="font-weight-bold mb-1">{{ App\Models\CompanySetting::instance()->company_name ?? 'POS Supermarket' }}</h4>
                <p class="text-muted small mb-0">Sistem Kasir Modern</p>
            </div>
            <div class="auth-body">
                @yield('content')
            </div>
        </div>
        <div class="text-center mt-3">
            <small class="text-white-50">&copy; {{ date('Y') }} POS Supermarket</small>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>