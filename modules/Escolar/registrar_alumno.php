<?php
session_start();
// Seguridad: Si no es admin, para afuera
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') { 
    header("Location: ../../index.php"); 
    exit(); 
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SGEI - Registro de Alumnos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --azul-base: #1a1a2e;    
            --azul-vibrante: #0d6efd; 
            --azul-fondo: #f0f4f8;     
            --blanco: #ffffff;
            --texto: #2c3e50;
        }

        body { 
            background-color: var(--azul-fondo); 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: var(--texto);
        }

        .card { 
            border-radius: 12px; 
            border: none; 
            box-shadow: 0 8px 20px rgba(0,0,0,0.08);
        }

        .card-header { 
            background-color: var(--azul-base) !important;
            color: var(--blanco);
            font-weight: 600;
            border-bottom: none;
        }

        .form-label {
            font-size: 0.85rem;
            color: #555;
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

        .btn-primary { 
            background-color: var(--azul-vibrante); 
            border: none;
            padding: 12px;
            font-weight: 600;
            border-radius: 8px;
            transition: 0.3s;
        }

        .btn-primary:hover {
            background-color: #0b5ed7;
            transform: translateY(-2px);
        }

        .btn-outline-secondary {
            border-radius: 8px;
            font-size: 0.85rem;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <div class="col-md-6 mx-auto">
            <div class="card overflow-hidden">
                <div class="card-header text-center py-3">
                    <h5 class="mb-0">Registro de Alumno</h5>
                </div>
                <div class="card-body p-4">
                    <form action="procesar_alumno.php" method="POST" class="needs-validation" novalidate>
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold">Nombre Completo</label>
                            <input type="text" name="nombre" class="form-control" 
                                   placeholder="Nombre y Apellidos" pattern="[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+" required>
                            <div class="invalid-feedback">Use solo letras para el nombre.</div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Número de Control</label>
                            <input type="text" name="n_control" class="form-control" 
                                   placeholder="Identificador institucional" maxlength="16" required>
                            <div class="invalid-feedback">El número de control es obligatorio.</div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Correo Electrónico</label>
                            <input type="email" name="correo" class="form-control" 
                                   placeholder="ejemplo@vladimir.edu" required>
                            <div class="invalid-feedback">Ingrese un correo válido.</div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold">Fecha de Nacimiento</label>
                            <input type="date" name="fecha_nac" class="form-control" required>
                            <div class="invalid-feedback">Seleccione una fecha válida.</div>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary shadow-sm">Confirmar Registro</button>
                            <a href="control_escolar.php" class="btn btn-outline-secondary mt-2">Regresar al Panel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
    (function () {
      'use strict'
      var forms = document.querySelectorAll('.needs-validation')
      Array.prototype.slice.call(forms).forEach(function (form) {
        form.addEventListener('submit', function (event) {
          if (!form.checkValidity()) {
            event.preventDefault()
            event.stopPropagation()
          }
          form.classList.add('was-validated')
        }, false)
      })
    })()
    </script>
</body>
</html>