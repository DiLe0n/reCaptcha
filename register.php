<?php
session_start();
require_once 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirm_password'];
    $email = $_POST['email'];
    $captchaResponse = $_POST['g-recaptcha-response'];

    // Verificar el captcha
    $secretKey = "6LeCa-IqAAAAAKi7iW4VVwEKren2-OLhjoOZVq_i";
    $response = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret=$secretKey&response=$captchaResponse");
    $responseKeys = json_decode($response, true);
    
    if(intval($responseKeys["success"]) !== 1) {
        echo "Por favor, verifica que no eres un robot.";
    } else {
        // Validar que las contraseñas coincidan
        if ($password !== $confirmPassword) {
            echo "Las contraseñas no coinciden.";
        } else {
            // Hashing y salting de la contraseña
            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

            // Insertar usuario en la base de datos
            $stmt = $pdo->prepare("INSERT INTO usuarios (username, password_hash, email) VALUES (:username, :password_hash, :email)");
            $stmt->bindParam(":username", $username);
            $stmt->bindParam(":password_hash", $hashedPassword);
            $stmt->bindParam(":email", $email);

            if ($stmt->execute()) {
                echo "Usuario registrado exitosamente.";
                $_SESSION['username'] = $username;  // Guardamos el usuario en la sesión
                header("Location: welcome.php");     // Redirigir al welcome.php
            } else {
                echo "Hubo un error al registrar el usuario.";
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

        input[type="text"], input[type="password"], input[type="email"] {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 16px;
            color: #333;
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
        }

        button:hover {
            background-color: #45a049;
        }

        .g-recaptcha {
            margin-bottom: 15px;
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
    <form action="register.php" method="POST">
        <h2>Registrarse</h2>
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
    </form>
</body>
</html>

