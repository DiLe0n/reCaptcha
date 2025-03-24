<?php
session_start();
require_once 'config.php';

// Función para limpiar entradas (Protección XSS)
function limpiarEntrada($dato) {
    return htmlspecialchars(strip_tags(trim($dato)));
}

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = limpiarEntrada($_POST['username']);
    $password = limpiarEntrada($_POST['password']);
    $confirm_password = limpiarEntrada($_POST['confirm_password']);
    $email = limpiarEntrada($_POST['email']);
    $captchaResponse = $_POST['g-recaptcha-response'];

    // Verificar el captcha
    $secretKey = "6LeCa-IqAAAAAKi7iW4VVwEKren2-OLhjoOZVq_i";
    $response = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret=$secretKey&response=$captchaResponse");
    $responseKeys = json_decode($response, true);
    
    if(intval($responseKeys["success"]) !== 1) {
        $error = "Por favor, verifica que no eres un robot.";
    } else {
        // Validar que las contraseñas coincidan
        if ($password !== $confirm_password) {
            $error = "Las contraseñas no coinciden.";
        } else {
    
            // Verificar si el usuario ya existe
            $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE username = :username OR email = :email");
            $stmt->bindParam(':username', $username, PDO::PARAM_STR);
            $stmt->bindParam(':email', $email, PDO::PARAM_STR);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                $error = "Este usuario ya está registrado.";
            } else {
                // Hashing y salting de la contraseña
                $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

                // Insertar usuario en la base de datos
                $stmt = $pdo->prepare("INSERT INTO usuarios (username, password_hash, email) VALUES (:username, :password_hash, :email)");
                $stmt->bindParam(":username", $username);
                $stmt->bindParam(":password_hash", $hashedPassword);
                $stmt->bindParam(":email", $email);

                if ($stmt->execute()) {
                    $_SESSION['username'] = $username;  // Guardamos el usuario en la sesión
                    header("Location: welcome.php");     // Redirigir al welcome.php
                    exit();
                } else {
                    $error = "Hubo un error al registrar el usuario.";
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrarse</title>
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        h2 {
            color: #333;
            text-align: center;
        }

        form {
            background-color: #fff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
            text-align: center;
        }

        label {
            display: block;
            margin: 10px 0 5px;
            text-align: left;
            color: #555;
        }

        input[type="text"],
        input[type="password"],
        input[type="email"] {
            width: calc(100% - 20px); /* Ajusta el ancho para que no toquen los bordes */
            padding: 12px; /* Espaciado interno */
            margin-bottom: 15px; /* Espacio entre campos */
            border: 1px solid #ccc; /* Borde delgado y gris */
            border-radius: 4px; /* Esquinas redondeadas */
            font-size: 16px; /* Tamaño del texto */
            color: #333; /* Color del texto */
            box-sizing: border-box; /* Incluye padding y border en el tamaño */
        }


        button {
            width: 100%;
            padding: 12px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            cursor: pointer;
            margin-top: 10px;
            text-decoration: none;
        }

        button:hover {
            background-color: #45a049;
        }

        .btn-secondary {
            display: inline-block; /* Permite aplicar padding y márgenes */
            margin-top: 15px; /* Espaciado superior */
            padding: 10px 20px; /* Espaciado interno (vertical, horizontal) */
            text-decoration: none; /* Quita el subrayado */
            background-color: #007bff; /* Color azul */
            color: white; /* Texto en blanco */
            border-radius: 4px; /* Esquinas redondeadas */
            text-align: center; /* Centrar el texto */
            width: calc(100% - 20px); /* Ajustar ancho sin pegarse al borde */
            box-sizing: border-box; /* Incluye padding y border en el tamaño */
            transition: background-color 0.3s; /* Efecto suave al pasar el cursor */
        }

        .btn-secondary:hover {
            background-color: #1976D2;
        }

        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border: 1px solid #f44336;
            border-radius: 4px;
            background-color: #ffebee;
            color: #d32f2f;
            text-align: left;
        }

        .g-recaptcha {
            margin-bottom: 15px;
        }

        @media (max-width: 480px) {
            form {
                padding: 20px;
            }

            button, .btn-secondary {
                font-size: 14px;
            }
        }
    </style>
</head>
<body>
    <form action="register.php" method="POST">
        <h2>Registrarse</h2>
        <?php if (!empty($error)): ?>
            <div class="alert"><?php echo $error; ?></div>
        <?php endif; ?>
        <label for="username">Usuario:</label>
        <input type="text" id="username" name="username" required>
        
        <label for="password">Contraseña:</label>
        <input type="password" id="password" name="password" required>

        <label for="confirm_password">Confirmar Contraseña:</label>
        <input type="password" id="confirm_password" name="confirm_password" required>
        
        <label for="email">Correo:</label>
        <input type="email" id="email" name="email" required>
        
        <div class="g-recaptcha" data-sitekey="6LeCa-IqAAAAAHezv1pHnimAEwdiT52HOV6JlXRM"></div>
        
        <button type="submit">Registrarse</button>
        <a href="login.php" class="btn-secondary">¿Ya tienes cuenta?</a>
    </form>
</body>
</html>

