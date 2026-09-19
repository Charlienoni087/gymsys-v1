<?php

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GYMSYS - Iniciar Sesión</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <style>
        :root {
            --amarillo: #ffd600;
            --negro: #0d0d0d;
            --gris: #4a4a4a
        }
        body {
            background: var(--gris);
        }
        .login-card {
            background-color: var(--negro);
            border-radius: 1.25rem;
        }
        .btn-primary {
            background-color: var(--amarillo);
            color: #ffffff;
            border-color: var(--amarillo);
            transition: all 0.3s ease;
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(13, 110, 253, 0.2);
        }
    </style>
</head>
<body>

    <div class="container d-flex justify-content-center align-items-center min-vh-100">
        
        <div class="col-12 col-sm-8 col-md-6 col-lg-4">
            
            <div class="card shadow-lg login-card border-0">
                <div class="card-body p-4 p-md-5">
                    
                    <div class="text-center mb-4">
                        <img src="assets/img/gymsys-logo.jpg" alt="logo" class="img-fluid mb-3" style="max-width: 100px; border-radius: 70px;">
                        <h2 class="fw-bold mt-2" style="color: #ffffff">GYMSYS</h2>
                        <p class="text" style="color: #ffffff;">Ingresa a tu cuenta</p>
                    </div>
                    
                    <form action="Controllers/LoginController.php" method="POST">
                        
                        <div class="form-floating mb-3">
                            <input type="email" class="form-control" id="email" name="email" placeholder="correo@example.com" required>
                            <label for="email" class="text-muted"><i class="bi bi-envelope me-2"></i>Correo electrónico</label>
                        </div>
                        <br>
                        <div class="form-floating mb-3">
                            <input type="password" class="form-control" id="password" name="password" placeholder="Contraseña" required>
                            <label for="password" class="text-muted"><i class="bi bi-lock me-2"></i>Contraseña</label>
                        </div>
                        <br>
                        <button type="submit" class="btn btn-primary btn-lg w-100 fw-bold">Entrar</button>
                    </form>
                    
                </div>
            </div>
            
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>