<?php
session_start(); // Inicia o continua la sesión

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['ingresar'])) {
    $userName = isset($_POST['userName']) ? $_POST['userName'] : null;
    $password = isset($_POST['password']) ? $_POST['password'] : null;

    if ($userName && $password) {
        $apiUrl = "https://api-rest-cr.nicepebble-44974112.eastus.azurecontainerapps.io/manager/login";

        $postData = json_encode(array("userName" => $userName, "password" => $password));

        // Crear un contexto de stream para enviar datos de la solicitud
        $contextOptions = [
            "http" => [
                "method" => "POST",
                "header" => "Content-Type: application/json\r\n",
                "content" => $postData
            ]
        ];
        $context = stream_context_create($contextOptions);

        // Hacer la solicitud POST a la API
        $response = file_get_contents($apiUrl, false, $context);

        if ($response !== false) {
            $responseArray = json_decode($response, true);
            // Verifica si el userName y password se recibieron de vuelta como confirmación
            if ($responseArray['userName'] == $userName && $responseArray['password'] == $password) {
                $_SESSION['userName'] = $userName; // Guarda el nombre de usuario en la sesión
                header("Location: index.php"); // Redirige al usuario a la página de bienvenida
                exit;
            } else {
                $errorLogin = "<p>Credenciales incorrectas. Intente de nuevo.</p>";
            }
        } else {
            $errorLogin = "<p>Error en la conexión con la API. Intente de nuevo.</p>";
        }
    } else {
        $errorLogin = "<p>Por favor, complete todos los campos.</p>";
    }
} else {
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar sesión</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="login form">
        <form action="" method="POST">
            <h2>Iniciar Sesión</h2>
            <div class="form_input">
                <label for="email_login">Usuario</label>
                <input type="text" id="email_login" name="userName" required>
                <label for="password_login">Contraseña</label>
                <input type="password" id="password_login" name="password" required>
            </div>
            <div class="form_input">
                <input type="submit" value="Iniciar sesión" name="ingresar">
            </div>
            <?php if (isset($errorLogin)) echo $errorLogin; ?>
        </form>
    </div>
</body>
</html>
