<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GINPAC-SOAP | Clínica SaludTotal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(180deg, #e8f0ff 0%, #ffffff 100%);
            font-family: "Segoe UI", sans-serif;
            padding-top: 40px;
        }
        header {
            background-color: #007bff;
            color: white;
            border-radius: 12px;
            padding: 15px 20px;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
        }
        header img {
            height: 40px;
        }
        .card {
            border-radius: 16px;
            box-shadow: 0px 4px 15px rgba(0, 0, 0, 0.1);
            border: none;
        }
        footer {
            margin-top: 40px;
            text-align: center;
            color: #6c757d;
            font-size: 14px;
        }
        .btn {
            border-radius: 10px;
        }
        .btn-primary {
            background-color: #007bff;
        }
        .btn-success {
            background-color: #28a745;
        }
        .table th {
            background-color: #007bff;
            color: white;
        }
    </style>
</head>
<body>
<div class="container">
    <header>
        <img src="https://cdn-icons-png.flaticon.com/512/2966/2966327.png" alt="logo">
        <div>
            <h3 class="mb-0">Gestor Interno de Pacientes</h3>
            <small>Clínica SaludTotal</small>
        </div>
    </header>

    <div class="card p-4">
        @yield('content')
    </div>

    <footer class="mt-4">
        <p>© {{ date('Y') }} Clínica SaludTotal — Sistema Interno GINPAC-SOAP</p>
    </footer>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
