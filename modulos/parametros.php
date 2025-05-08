<?php
include_once '../includes/config.php';
require '../config/validar_permisos.php';
$filtro = $_GET['tipo'];

if ($filtro != 'internacionales' && $filtro != 'personalizados') {
    $_SESSION['error'] = "URL no válida.";
    header('location: ../menu.php ');
    exit;
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FHE | Parámetros</title>
    <link rel="stylesheet" href="../styles.css">
    <link rel="stylesheet" href="../css/menu.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>
    <main class="contenedor hoja">
        <?php include '../includes/header.php'; ?>

        <div class="contenedor__modulo">
            <a href="../menu.php" class="atras">Ir atrás</a>
            <h2 class="heading">Parámetros <?php echo $filtro; ?></h2>

            <div class="controles">
                <div class="buscador">
                    <h4 class="buscador__label">Buscar</h4>
                    <input type="text" id="searchBar" class="buscador__input" placeholder="Busqueda">
                </div>
                <div class="ordenar">
                    <h4 class="ordenar__label">Tipo de parámetro:</h4>
                    <select name="orden" id="filtroParam" class="ordenar__select">
                        <option value="internacionales" <?= ($filtro === 'internacionales') ? 'selected' : '' ?>>
                            Internacionales</option>
                        <option value="personalizados" <?= ($filtro === 'personalizados') ? 'selected' : ''; ?>>
                            Personalizados</option>
                    </select>
                </div>
            </div>

            <table class="tabla">
                <thead>
                    <tr class="tabla__encabezado">
                        <th><?php echo ($filtro === 'internacionales') ? 'Clave Equipo' : 'ID Cliente'; ?></th>
                        <th><?php echo ($filtro === 'internacionales') ? 'Tipo Equipo' : 'Cliente'; ?></th>
                        <th>Parámetro</th>
                        <th>Límite inferior</th>
                        <th>Límite superior</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    require '../config/conn.php';

                    $tabla = 'equipos_laboratorio';
                    $tipo_param = 'id_equipo';

                    if ($filtro === 'internacionales') {
                        $sql_id = "SELECT 
                P.id_parametro, 
                P.nombre_parametro, 
                P.lim_Superior, 
                P.lim_Inferior, 
                A.clave AS origen,
                A.tipo_equipo AS tipo,
                A.id_equipo AS id
            FROM Parametros P 
            LEFT JOIN Equipos_Laboratorio A ON A.id_equipo = P.id_equipo 
            WHERE P.id_equipo = :id";
                        $sql = "SELECT DISTINCT id_equipo FROM Parametros";
                        $campo = 'tipo_equipo';
                    } else {
                        $sql_id = "SELECT 
                P.id_parametro, 
                P.nombre_parametro, 
                P.lim_Superior, 
                P.lim_Inferior, 
                A.nombre AS origen,
                A.id_cliente AS id
            FROM Parametros P 
            LEFT JOIN Clientes A ON A.id_cliente = P.id_cliente 
            WHERE P.id_cliente = :id";

                        $tabla = "clientes";
                        $tipo_param = 'id_cliente';
                        $campo = 'nombre';
                        $sql = "SELECT DISTINCT id_cliente FROM Parametros";
                    }

                    $stmt = $pdo->prepare($sql);
                    $stmt->execute();

                    while ($id = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        $valor_id = $id[$tipo_param];
                        $stmt_inner = $pdo->prepare($sql_id);
                        $stmt_inner->bindValue(':id', $valor_id, PDO::PARAM_INT);
                        $stmt_inner->execute();

                        $parametros = $stmt_inner->fetchAll(PDO::FETCH_ASSOC);

                        if (!empty($parametros)) {
                            // Obtenemos los valores de "origen" y "tipo" una sola vez
                            $origen = htmlspecialchars($parametros[0][($filtro === 'internacionales') ? 'origen' : 'id']);
                            $tipo = htmlspecialchars($parametros[0][($filtro === 'internacionales') ? 'tipo' : 'origen']);

                            $primera = true; // Variable para saber si estamos en la primera fila
                    
                            foreach ($parametros as $parametro) {
                                echo '<tr class="tabla__fila">';

                                if ($primera) {
                                    echo '<td>' . $origen . '</td>';
                                    echo '<td>' . $tipo . '</td>';
                                    $primera = false;
                                } else {
                                    echo '<td class = "oculto">' . $origen . '</td>';
                                    echo '<td class = "oculto">' . $tipo . '</td>';
                                }

                                echo '<td>' . htmlspecialchars($parametro['nombre_parametro']) . '</td>';
                                echo '<td>' . htmlspecialchars($parametro['lim_Inferior']) . '</td>';
                                echo '<td>' . htmlspecialchars($parametro['lim_Superior']) . '</td>';
                                echo '<td class="tabla__botones">';
                                echo '<a href="parametrosform.php?id=' . $parametro['id_parametro'] . '&ow=' . $tabla . '">
                  <img src="../img/edit.svg" alt="Editar" class="tabla__boton"></a>';
                                echo '</td>';
                                echo '</tr>';
                            }

                            // Línea separadora después del grupo
                            echo '<tr class="tabla__fila--sep"><td colspan="6" class="separador">---</td></tr>';
                        }
                    }
                    ?>
                </tbody>
            </table>


        </div>
        <?php include '../includes/footer.php'; ?>
    </main>
</body>
<script>
    // Filtros por tipo de parametro
    const filtro_param = document.getElementById('filtroParam');

    filtro_param.addEventListener('change', () => {
        const seleccion = filtro_param.value;
        window.location.href = "?tipo=" + seleccion;
    });

    // Buscar 
    const buscador = document.getElementById('searchBar');
    const filas = document.querySelectorAll('.tabla__fila');

    buscador.addEventListener('input', () => {
        busqueda = buscador.value.toLowerCase();

        filas.forEach((fila) => {
            const contenidoFila = fila.textContent.toLocaleLowerCase();

            if (contenidoFila.includes(busqueda)) {
                fila.style.display = '';
            } else {
                fila.style.display = 'none';
            }
        });
    });
</script>

</html>