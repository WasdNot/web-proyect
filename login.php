<?php
require_once "functions.php";

$address = $_SERVER['REMOTE_ADDR'];
$result = get_user($address, get_conn(CONNECTIONS));
if ($result != null) {
    remove_address($address);
    header("Location: index.php");
    exit();
}

$error = "-1";
if (isset($_GET["error"]))
    $error = $_GET["error"];
?>

<!DOCTYPE html>
<html lang="es">

<head>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var element = document.getElementById('form');
            element.classList.add('show');
        });

        function handleKeyPress(event) {
            if (event.key === "Enter") {
                event.preventDefault();
                console.log(event.target.id + 1 + "");
                document.getElementById(parseInt(event.target.id) + 1).focus();
            }
        }
    </script>

    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="login.css">
    <link rel="icon" href="images/favicon.png" type="image/png">
    <title>Iniciar sesión</title>

</head>

<body>

    <form class="background" id="form" action="index.php" method="post">
        <img class="logo" src="images/logo.png" alt="logo" width="99" height="99" draggable="false">
        <img class="back" src="images/back.png" alt="back" width="45" height="45" draggable="false" onclick="window.location.href = 'index.php'">

        <p class="header">d_1</p>

        <p class="subtitle">Usuario</p>
        <input type="text" name="user" id="0" onkeydown="handleKeyPress(event)">

        <p class="subtitle">Contraseña</p>
        <input type="text" name="password" id="1">

        <button id="2">Entra a tu tienda</button>
    </form>

</body>

</html>