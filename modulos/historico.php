<?php
require '../config/validar_permisos.php';
include '../config/conn.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
$estado = $_GET['estado'] ?? 1;


// Recuperamos los datos de los certificados y los resultados de la inspección
$sql = "SELECT 
    i.lote, 
    i.id_inspeccion, 
    c.nombre, 
    c.correo_contacto,
    ce.cantidad_solicitada, 
    ce.cantidad_recibida,
    MIN(ri.aprobado) AS aprobado
FROM Inspeccion i
INNER JOIN Clientes c ON i.id_cliente = c.id_cliente
INNER JOIN Certificados ce ON ce.id_inspeccion = i.id_inspeccion
LEFT JOIN Resultado_Inspeccion ri ON ri.id_inspeccion = i.id_inspeccion
WHERE ri.aprobado = $estado
GROUP BY i.id_inspeccion, i.lote, c.nombre, ce.cantidad_solicitada, ce.cantidad_recibida
ORDER BY i.id_inspeccion DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute();
$resultado = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FHE | Certificados</title>
    <link rel="stylesheet" href="../styles.css">
    <link rel="stylesheet" href="../css/menu.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>
    <main class="contenedor hoja">
        <?php include '../includes/header.php' ?>

        <div class="contenedor__modulo">
            <a href="../menu.php" class="atras">Ir atrás</a>
            <h2 class="heading">Certificados</h2>

            <div class="controles">
                <div class="buscador">
                    <h4 class="buscador__label">Buscar</h4>
                    <input type="text" class="buscador__input" id="searchBar" placeholder="Lote de producción">
                </div>

                <div class="ordenar">
                    <h4 class="ordenar__label">Resultados</h4>
                    <select name="estado" id="ordenarPor" class="ordenar__select">
                        <option value="1" <?php echo $estado == 1 ? 'selected' : ''; ?>>Aprobado</option>
                        <option value="0" <?php echo $estado == 0 ? 'selected' : ''; ?>>Desaprobado</option>
                    </select>
                </div>

                <h2 class="botones__buscar">Buscar</h2>
                <a href="generar_certificadoform.php" class="botones__crear"> Generar certificado </a>

            </div>

            <table class="tabla">
                <thead>
                    <tr class="tabla__encabezado">
                        <th>Lote de producción</th>
                        <th>Id inspección</th>
                        <th>Cliente</th>
                        <th>Cantidad solicitada (kg)</th>
                        <th>Cantidad recibida (kg)</th>
                        <th>Resultados del análisis</th>
                        <th>Certificado</th>
                        <th> Correo </th>
                    </tr>
                </thead>
                <tbody>

                    <?php foreach ($resultado as $row) { ?>

                        <tr class="tabla__fila">
                            <td><?php echo $row['lote'] ?></td>
                            <td><?php echo $row['id_inspeccion'] ?></td>
                            <td><?php echo $row['nombre'] ?></td>
                            <td><?php echo $row['cantidad_solicitada'] ?></td>
                            <td><?php echo $row['cantidad_recibida'] ?></td>
                            <td>
                                <?php
                                echo $row['aprobado'] ? 'Aprobado' : 'Desaprobado';
                                ?>
                            </td>
                            <td>
                                <a href="generar_pdf.php?id=<?php echo $row['id_inspeccion'] ?>" class="tabla__descargar" download>Descargar PDF</a>
                            </td>

                            <td>
                                <a href="#" onclick="confirmarEnvio(<?php echo $row['id_inspeccion'] ?>, '<?php echo $row['correo_contacto'] ?>');" class="tabla__descargar">Enviar correo</a>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
        <?php include '../includes/footer.php' ?>
    </main>

    <script>
        // Buscar por nombre
        const buscador = document.getElementById('searchBar');
        const filasUsuarios = document.querySelectorAll('.tabla__fila');

        buscador.addEventListener('input', () => {
            busquedaUsuario = buscador.value.toLowerCase();

            filasUsuarios.forEach((fila) => {
                const contenidoFila = fila.textContent.toLocaleLowerCase();

                if (contenidoFila.includes(busquedaUsuario)) {
                    fila.style.display = '';
                } else {
                    fila.style.display = 'none';
                }
            });
        });

        // Filtros por aprobado
        // // Script para seleccionar un estado
        document.addEventListener("DOMContentLoaded", function() {
            const selectEstado = document.getElementById("ordenarPor");

            selectEstado.addEventListener("change", function() {
                // alert("Opción seleccionada: " + this.value);
                const estado = this.value;
                // Cambiar la URL 
                const nuevaUrl = new URL(window.location);
                nuevaUrl.searchParams.set("estado", estado);
                history.pushState({}, "", nuevaUrl);

                location.reload();
            });
        });


        function confirmarEnvio(id, correo) {
            Swal.fire({
                title: '¿Enviar correo?',
                text: "¿Desea enviar el certificado por correo electrónico a: " + correo + "?",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Sí, enviar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = '../config/mandar_mail.php?id=' + id;
                }
            })
        }
    </script>
</body>

</html>