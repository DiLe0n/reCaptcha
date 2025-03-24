<?php
session_start();
require_once 'config.php';

// Función para limpiar entradas (Protección XSS)
function limpiarEntrada($dato) {
    return htmlspecialchars(strip_tags(trim($dato)));
}

// Limitar intentos de inicio de sesión (máx. 5 intentos en 10 minutos)
if (!isset($_SESSION['intentos_fallidos'])) {
    $_SESSION['intentos_fallidos'] = 0;
    $_SESSION['ultimo_intento'] = time();
}

// Bloqueo temporal si supera los intentos
if ($_SESSION['intentos_fallidos'] >= 3 && time() - $_SESSION['ultimo_intento'] < 600) {
    $mensajeError = " Demasiados intentos fallidos. Inténtalo más tarde.";
}

if (isset($_SESSION['username'])) {
    header("Location: welcome.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = limpiarEntrada($_POST['username']);
    $password = limpiarEntrada($_POST['password']);
    $captchaResponse = $_POST['g-recaptcha-response'];

    // Verificar el captcha
    $secretKey = "6LeCa-IqAAAAAKi7iW4VVwEKren2-OLhjoOZVq_i";
    $response = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret=$secretKey&response=$captchaResponse");
    $responseKeys = json_decode($response, true);
    
    if(intval($responseKeys["success"]) !== 1) {
        $mensajeError = " Por favor, verifica que no eres un robot.";
    } else {
        // Validar usuario y contraseña desde la base de datos
        $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE username = :username");
        $stmt->bindParam(":username", $username);
        $stmt->execute();
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            $_SESSION['username'] = $user['username'];
            $_SESSION['intentos_fallidos'] = 0; // Reiniciar intentos al iniciar sesión
            header("Location: welcome.php");
            exit();
        } else {
            $_SESSION['intentos_fallidos']++;
            $_SESSION['ultimo_intento'] = time();
            $mensajeError = " Usuario o contraseña incorrectos.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar sesión</title>
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
            box-sizing: border-box; /* Incluye padding en el ancho total */
            text-align: center;
        }

        label {
            display: block;
            margin: 10px 0 5px;
            text-align: left;
            color: #555;
        }

        input[type="text"],
        input[type="password"] {
            width: calc(100% - 20px); /* Ajuste para no pegarse a los bordes */
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 16px;
            box-sizing: border-box; /* Incluye padding en el ancho */
            color: #333;
        }

        button {
            width: calc(100% - 20px); /* Ajuste de ancho */
            padding: 12px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            cursor: pointer;
            margin-top: 10px;
            box-sizing: border-box; /* Consistencia de tamaño */
        }

        button:hover {
            background-color: #45a049;
        }

        .alerta {
            background-color: #f8d7da;
            color: #721c24;
            padding: 12px;
            border-radius: 5px;
            margin-bottom: 15px;
            text-align: center;
            box-sizing: border-box;
        }

        .g-recaptcha {
            margin-bottom: 15px;
        }

        .enlace {
            display: inline-block;
            margin-top: 15px;
            padding: 10px 20px;
            background-color: #007bff;
            color: white;
            border-radius: 4px;
            text-decoration: none;
            text-align: center;
            width: calc(100% - 20px); /* Ajustar el ancho */
            box-sizing: border-box;
            transition: background-color 0.3s;
        }

        .enlace:hover {
            background-color: #0056b3;
        }

        @media (max-width: 480px) {
            form {
                padding: 20px;
            }

            button {
                font-size: 14px;
            }
        }
    </style>
</head>
<body>
    <form action="login.php" method="POST">
        <h2>Iniciar sesión</h2>

        <?php if (!empty($mensajeError)): ?>
            <div class="alerta"><?= $mensajeError; ?></div>
        <?php endif; ?>

        <label for="username">Usuario:</label>
        <input type="text" id="username" name="username" required>
        
        <label for="password">Contraseña:</label>
        <input type="password" id="password" name="password" required>
        
        <div class="g-recaptcha" data-sitekey="6LeCa-IqAAAAAHezv1pHnimAEwdiT52HOV6JlXRM"></div>
        
        <button type="submit">Iniciar sesión</button>
        <a href="register.php" class="enlace">No tienes cuenta?</a>
    </form>
</body>
</html>