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
        .button {
            display: inline-block;
            background-color: #4CAF50;
            color: white;
            padding: 12px 30px;
            text-decoration: none;
            border-radius: 4px;
            margin: 20px 0;
        }
        .button:hover {
            background-color: #45a049;
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
            <h1>Bienvenido a Condominio</h1>
        </div>
        <div class="content">
            <h2>Hola {{ $usuario->nombre }},</h2>
            
            <p>Gracias por registrarte en nuestro sistema de gestión de condominio.</p>
            
            <p>Para completar tu registro, por favor verifica tu correo electrónico haciendo clic en el botón de abajo:</p>
            
            <p style="text-align: center;">
                <a href="{{ $verificationUrl }}" class="button">Verificar Email</a>
            </p>
            
            <p>O copia y pega esta URL en tu navegador:</p>
            <p style="word-break: break-all; background-color: white; padding: 10px; border: 1px solid #ddd;">
                {{ $verificationUrl }}
            </p>
            
            <p>Este enlace de verificación expirará en 24 horas.</p>
            
            <p>Si no creaste una cuenta en nuestro sistema, puedes ignorar este correo.</p>
        </div>
        <div class="footer">
            <p>&copy; {{ date('Y') }} Condominio. Todos los derechos reservados.</p>
        </div>
    </div>
</body>
</html>
