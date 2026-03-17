<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SGEI - Acceso Institucional</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* Paleta de Colores SGEI Vladimir */
        :root {
            --azul-base: #1a1a2e;    
            --azul-vibrante: #0d6efd; 
            --azul-fondo: #f0f4f8;     
            --blanco: #ffffff;
            --texto: #2c3e50;
        }

        body { 
            background: var(--azul-base); 
            height: 100vh; 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
        }

        .card-login { 
            width: 380px; 
            border-radius: 12px; 
            border: none; 
            background: var(--blanco); 
            box-shadow: 0 10px 25px rgba(0,0,0,0.3);
        }

        .btn-primary { 
            background-color: var(--azul-vibrante); 
            border: none; 
            padding: 12px; 
            transition: 0.3s;
        }

        .btn-primary:hover {
            background-color: #0b5ed7;
            transform: translateY(-2px);
        }

        .school-title { 
            color: var(--azul-base); 
            font-weight: 800; 
            letter-spacing: -0.5px;
        }

        .form-control {
            border-radius: 8px;
            padding: 10px;
            border: 1px solid #d1d9e0;
        }

        .form-control:focus {
            box-shadow: 0 0 0 3px rgba(13, 110, 253, 0.15);
            border-color: var(--azul-vibrante);
        }
    </style>
</head>
<body>
    <div class="card card-login p-4">
        <div class="text-center">
            <h3 class="school-title">Escuela Vladimir</h3>
            <p class="text-muted small">Sistema de Gestion Escolar Inteligente</p>
        </div>
        <hr class="my-4" style="opacity: 0.1;">

        <?php 
        if (isset($_GET['error'])) {
            $mensaje = "";
            $error = $_GET['error'];

            if ($error == 'credenciales') {
                $mensaje = "Datos de acceso incorrectos";
            } elseif ($error == 'rol_invalido') {
                $mensaje = "Usuario sin privilegios de acceso";
            } else {
                $mensaje = "Error de conexion con el servidor";
            }

            echo '<div class="alert alert-danger py-2 text-center" style="font-size: 0.85rem; border-radius: 8px;">' . $mensaje . '</div>';
        }
        ?>

        <form action="modules/auth/auth_processor.php" method="POST">
            <div class="mb-3">
                <label class="form-label small fw-bold">Usuario</label>
                <input type="email" name="correo" class="form-control" placeholder="nombre@vladimir.edu" required autofocus>
            </div>
            <div class="mb-4">
                <label class="form-label small fw-bold">Clave de Acceso</label>
                <input type="password" name="password" class="form-control" placeholder="Escriba su contraseña" required>
            </div>
            <button type="submit" class="btn btn-primary w-100 fw-bold">Entrar</button>
        </form>
    </div>
</body>
</html>