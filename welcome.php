<?php
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bienvenido</title>
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

        .container {
            background-color: #fff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 500px;
            text-align: center;
        }

        h1 {
            color: #333;
            font-size: 24px;
        }

        .welcome-message {
            margin-bottom: 20px;
            font-size: 18px;
            color: #555;
        }

        a {
            text-decoration: none;
            background-color: #4CAF50;
            color: white;
            padding: 12px 20px;
            border-radius: 4px;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        a:hover {
            background-color: #45a049;
        }

        .logout-button {
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>¡Bienvenido!</h1>
        <div class="welcome-message">
            <?php
                echo "Hola, " . $_SESSION['username'] . "!";
            ?>
        </div>
        <div class="logout-button">
            <a href="logout.php">Cerrar sesión</a>
        </div>
    </div>
</body>
</html>
