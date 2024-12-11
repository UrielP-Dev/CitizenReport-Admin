    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Reportes - ViAlert!</title>
        <link rel="stylesheet" href="styles.css">
    </head>
    <body>
        <?php
        session_start(); // Inicia o continua la sesión

        // Verificar si el usuario desea cerrar sesión
        if (isset($_GET['logout']) && $_GET['logout'] == 'true') {
            // Limpiar la sesión
            $_SESSION = array();

            // Destruir la sesión
            session_destroy();

            // Redirigir al usuario a la página de login
            header('Location: login.php');
            exit;
        }

        // Verificar si existe una sesión activa
        if (!isset($_SESSION['userName'])) {
            // No hay sesión activa, redirigir a la página de login
            header('Location: login.php');
            exit;
        }

        // Manejar el envío de la infracción
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['reason'], $_POST['observations'], $_POST['mountDue'], $_POST['managerID'], $_POST['reportID'])) {
            $apiUrl = "http://localhost:8081/infraction";
            $postData = json_encode(array(
                "reason" => $_POST['reason'],
                "observations" => $_POST['observations'],
                "mountDue" => $_POST['mountDue'],
                "managerID" => $_POST['managerID'],
                "reportID" => $_POST['reportID']
            ));

            // Crear un contexto de stream para enviar datos de la solicitud POST
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
            $responseData = json_decode($response, true);

            // Manejar la respuesta
            if ($responseData && isset($responseData['reason'], $responseData['observations'], $responseData['mountDue'], $responseData['managerID'], $responseData['reportID'])) {
                echo "<script>alert('Infracción creada con éxito');</script>";

            // Preparar datos para enviar al endpoint /history antes de eliminar el reporte
            $historyUrl = "http://localhost:8081/history";
            $historyData = json_encode(array(
                "status" => "completado",
                "observations" => $_POST['observations'],
                "managerID" => $_POST['managerID']
            ));

            $historyContextOptions = [
                "http" => [
                    "method" => "POST",
                    "header" => "Content-Type: application/json\r\n",
                    "content" => $historyData
                ]
            ];
            $historyContext = stream_context_create($historyContextOptions);
            $historyResponse = file_get_contents($historyUrl, false, $historyContext);

            // Independientemente del resultado de la actualización del historial, proceder a eliminar el reporte
            $deleteUrl = "http://localhost:8081/report/" . $_POST['reportID'];
            $deleteContextOptions = [
                "http" => [
                    "method" => "DELETE",
                    "header" => "Content-Type: application/json"
                ]
            ];
            $deleteContext = stream_context_create($deleteContextOptions);
            $deleteResponse = file_get_contents($deleteUrl, false, $deleteContext);

            if (!$deleteResponse) {
                echo "<script>alert('Reporte eliminado con éxito');</script>";
            } else {
                echo "<script>alert('Error al eliminar el reporte');</script>";
            }
        } else {
            // Mostrar la respuesta de la API en caso de error
            echo "<script>alert('Error al crear la infracción: " . addslashes($response) . "');</script>";
        }

        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['rejectObservations'], $_POST['rejectReportID'], $_POST['rejectPlate'], $_POST['managerID'])) {
            // Preparar los datos para enviar al endpoint /history antes de eliminar el reporte
            $historyUrl = "http://localhost:8081/history";
            $historyData = json_encode(array(
                "status" => "rechazado",
                "observations" => $_POST['rejectObservations'],
                "plate" => $_POST['rejectPlate'],
                "managerID" => $_POST['managerID']
            ));
        
            $historyContextOptions = [
                "http" => [
                    "method" => "POST",
                    "header" => "Content-Type: application/json\r\n",
                    "content" => $historyData
                ]
            ];
            $historyContext = stream_context_create($historyContextOptions);
            $historyResponse = file_get_contents($historyUrl, false, $historyContext);
        
            // Proceder a eliminar el reporte, independientemente del resultado de la actualización del historial
            $deleteUrl = "http://localhost:8081/report/" . $_POST['rejectReportID'];
            $deleteContextOptions = [
                "http" => [
                    "method" => "DELETE",
                    "header" => "Content-Type: application/json"
                ]
            ];
            $deleteContext = stream_context_create($deleteContextOptions);
            $deleteResponse = file_get_contents($deleteUrl, false, $deleteContext);
        
            if (!$deleteResponse) {
                echo "<script>alert('Reporte eliminado con éxito');</script>";
            } else {
                echo "<script>alert('Error al eliminar el reporte');</script>";
            }
        }    
                
        ?>

        <header>
            <h1>ViAlert! Bienvenido <?php echo htmlspecialchars($_SESSION['userName']); ?></h1>
            <button onclick="window.location='?logout=true';">Cerrar sesión</button>
        </header>

        <main>
            <div id="navigation">
                <?php
                $apiUrl = "http://localhost:8081/report";

                // Crear un contexto de stream para definir los encabezados HTTP
                $contextOptions = [
                    "http" => [
                        "method" => "GET",
                        "header" => "Content-Type: application/json"
                    ]
                ];
                $context = stream_context_create($contextOptions);

                // Hacer la solicitud GET a la API
                $response = file_get_contents($apiUrl, false, $context);

                // Decodificar la respuesta JSON
                $reports = json_decode($response, true);

                // Verificar si hay reportes y usar un contador para los nombres de los botones
                if (!empty($reports)) {
                    $count = 1;
                    foreach ($reports as $report) {
                        echo "<button type='button' onclick='showDetails(" . $count . ")'>Reporte " . $count . "</button>";
                        $count++;
                    }
                } else {
                    echo "<p>No se encontraron reportes.</p>";
                }
                ?>
            </div>
            <div id="details">
                <p id="default-message">Selecciona un reporte para ver los detalles</p>
                <?php
                if (!empty($reports)) {
                    $count = 1;
                    foreach ($reports as $report) {
                        echo "<div id='details" . $count . "' class='report-details'>";
                        echo "<img src='http://localhost:8081/report/" . htmlspecialchars($report['id']) . "/photo' alt='Foto del reporte'>";
                        echo "<p> Descrición: " . htmlspecialchars($report['description']) . "</p>";
                        echo "<p> Ubicación: " . htmlspecialchars($report['location']) . "</p>";
                        echo "<p> Fecha: " . htmlspecialchars(date("Y-m-d", strtotime($report['date']))) . "</p>";
                        echo "<p>Hora: " . htmlspecialchars($report['time']) . "</p>";
                        echo "<p>Placa: " . htmlspecialchars($report['plate']) . "</p>";
                        echo "<p>Estatus: " . htmlspecialchars($report['status']) . "</p>";
                        echo "<p>Usuario: " . htmlspecialchars($report['userId']) . "</p>";
                        echo "<p>Administrador: " . htmlspecialchars($report['managerID']) . "</p>";
                        echo "<div class='action-buttons'>";
                        echo "<button class='action-button' onclick='openModal(\"" . htmlspecialchars($report['id']) . "\")'>Aprobar</button>";
                        echo "<button class='action-button action-button-reject' data-report-id='" . htmlspecialchars($report['id']) . "' onclick='openRejectModal(\"" . htmlspecialchars($report['id']) . "\", \"" . htmlspecialchars($report['plate']) . "\", \"" . htmlspecialchars($_SESSION['userName']) . "\")'>Rechazar</button>";
                        echo "</div>";
                        echo "</div>";
                        $count++;
                    }
                }
                ?>
            </div>
            <div class="clear"></div>
        </main>

        <!-- The Modal -->
        <div id="myModal" class="modal">
            <div class="modal-content">
                <span class="close">&times;</span>
                <h2>Generar Infracción</h2>
                <form id="infractionForm" method="POST" action="">
                    <label for="reason">Razón:</label>
                    <input type="text" id="reason" name="reason" required>
                    <label for="observations">Observaciones:</label>
                    <input type="text" id="observations" name="observations" required>
                    <label for="mountDue">Monto de la Infracción:</label>
                    <input type="text" id="mountDue" name="mountDue" required>
                    <input type="hidden" id="reportID" name="reportID">
                    <input type="hidden" id="managerID" name="managerID" value="<?php echo htmlspecialchars($_SESSION['userName']); ?>">
                    <button type="submit">Enviar</button>
                </form>
            </div>
        </div>

        <!-- The Reject Modal -->
        <div id="rejectModal" class="modal">
            <div class="modal-content">
                <span class="close-reject">&times;</span>
                <h2>Rechazar Reporte</h2>
                <form id="rejectForm" method="POST" action="">
                    <label for="rejectObservations">Observaciones:</label>
                    <textarea id="rejectObservations" name="rejectObservations" required></textarea>
                    <input type="hidden" id="rejectReportID" name="rejectReportID" value="<?php echo $reportId; ?>"> <!-- Asegura que tienes este valor disponible -->
                    <input type="hidden" name="rejectPlate" value="<?php echo $plate; ?>"> <!-- Asegura que el valor de plate está disponible -->
                    <input type="hidden" name="managerID" value="<?php echo $_SESSION['userName']; ?>">
                    <button type="submit">Enviar</button>
                </form>
            </div>
        </div>
        <script src="script.js" ></script>
    </body>
    </html>
