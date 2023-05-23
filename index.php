<?php
require_once "functions.php";

$result = -1;
$address = $_SERVER['REMOTE_ADDR'];

if (isset($_POST["user"]) && isset($_POST["password"])) {
    $username = $_POST["user"];
    $password = $_POST["password"];

    $result = check_password($username, $password, $address);
    if (is_int($result)) {
        header("Location: login.php?error=" . $result);
        exit();
    }
} else {
    $reference = get_user($address, get_conn(CONNECTIONS));
    if ($reference != null)
        $result = new Reference($reference["reference"]);
}
$has_privileges = (!is_int($result) && $result->getType() == Reference::ADMIN);

$output = "";
if ($has_privileges && isset($_GET["command"])) {
    $command = $_GET["command"];
    $args = explode(" ", $command);

    if (substr($args[0], 0, 1) == ".") {
        switch ($args[0]) {
            case ".hash":
                if (count($args) < 2) {
                    $output = "[ERROR] Se necesita un argumento para ejecutar el comando 'hash': .hash {texto}";
                } else {
                    $output = password_hash($args[1], PASSWORD_DEFAULT);
                    if ($output == false || $output == null)
                        $output = "[ERROR] hubo un error al generar el hash: " . $output;
                }
                break;

            default:
                $output = "[ERROR] El comando introducido no existe";
                break;
        }
    }
}

// Get data according to "reference"

?>


<!DOCTYPE html>
<html lang="es">

<head><?php
        if ($has_privileges)
            echo
            '
            <script>
                /**@param {Event} event */
                function on_keydown(event) {
                    var n = (window.Event) ? event.which : event.keyCode;
                    if (n == 13) {
                        let text = document.getElementById("search").value;
                        if (text.startsWith(".")) {
                            window.location.href = "index.php?command=" + encodeURIComponent(text);
                        }
                        return false;
                    }
                }
            </script>
            ' ?>

    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="index.css">
    <link rel="icon" href="images/favicon.png" type="image/png">
    <title>PANA</title>

</head>

<body>

    <div class="header">
        <img class="logo" src="images/logo.png" alt="logo" width="66" height="66" draggable="false">

        <form>
            <input class="search" type="text" name="search" id="search" <?php check_reference($has_privileges);
                                                                        can_use_commands($has_privileges);
                                                                        set_output($output) ?>>
            <img class="search_icon" src="images/search.png" alt="search" width="20" height="20" draggable="false">
        </form>

        <form action="login.php" method="post">
            <input type="text" style="display: none;" name="login" value="1">
            <button class="login"><?php if (is_int($result) && $result == -1) echo "Iniciar sesión";
                                    else echo "Cerrar sesión"; ?></button>
        </form>
    </div>

    <div class="separator"></div>
    <table class="content">
        <caption>This is a table of fruits</caption>
        <thead>
            <tr>
                <th>Fruit</th>
                <th>Color</th>
                <th>Taste</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Apple</td>
                <td>Red</td>
                <td>Sweet</td>
            </tr>
            <tr>
                <td>Orange</td>
                <td>Orange</td>
                <td>Sour</td>
            </tr>
            <tr>
                <td>Banana</td>
                <td>Yellow</td>
                <td>Sweet</td>
            </tr>
        </tbody>
    </table>

</body>

</html>

<?php
function check_reference($has_privileges)
{
    if ($has_privileges)
        echo 'placeholder = "Ingrese su orden aquí"';
    else
        echo 'placeholder = "Busque el producto que desee"';
}

function can_use_commands($has_privileges)
{
    if ($has_privileges)
        echo 'onkeydown="return on_keydown(event);"';
    else
        echo 'onkeydown="return event.key !== \'Enter\';"';
}
function set_output($message)
{
    if ($message != "") {
        echo "value='$message'";
    }
}
