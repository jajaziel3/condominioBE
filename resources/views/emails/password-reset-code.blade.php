<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 8px;
        }
        .header {
            background-color: #4CAF50;
            color: white;
            padding: 20px;
            border-radius: 8px 8px 0 0;
            text-align: center;
        }
        .content {
            padding: 20px;
            background-color: #f9f9f9;
        }
        .code {
            font-size: 24px;
            font-weight: bold;
            text-align: center;
            margin: 20px 0;
            letter-spacing: 4px;
        }
        .footer {
            text-align: center;
            padding: 20px;
            font-size: 12px;
            color: #999;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Recuperación de contraseña</h1>
        </div>
        <div class="content">
            <h2>Hola {{ $usuario->nombre }},</h2>
            <p>Hemos recibido una solicitud para restablecer la contraseña de tu cuenta.</p>
            <p>Utiliza el siguiente código de 6 dígitos:</p>
            <div class="code">{{ $usuario->password_reset_code }}</div>
            <p>Este código expirará en 10 minutos. Si no solicitaste este cambio, puedes ignorar este correo.</p>
        </div>
        <div class="footer">
            <p>&copy; {{ date('Y') }} Condominio. Todos los derechos reservados.</p>
        </div>
    </div>
</body>
</html>