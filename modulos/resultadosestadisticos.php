<?php 
include_once '../includes/config.php';
require '../config/validar_permisos.php'; 
require '../config/conn.php'; 

// Validar que se acceda por POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error'] = 'Acceso denegado: se requiere el método POST.';
    header ('Location: ' . BASE_URL . 'modulos/estadisticos.php');
    exit;
}

// Obtener fechas desde POST de forma segura
$fecha_ini = $_POST['fecha_inicio'] ?? null;
$fecha_fin = $_POST['fecha_fin'] ?? null;

// Validar que las fechas no estén vacías6
if (empty($fecha_ini) || empty($fecha_fin)) {
    $_SESSION['error'] = 'Fechas inválidas.';
    header ('Location: '. BASE_URL . 'modulos/estadisticos.php');
    exit;
}

// Primer query: parámetros no aprobados
$query1 = "
    SELECT nombre_parametro, COUNT(aprobado) AS cantidad
    FROM resultado_inspeccion RI
    JOIN inspeccion I ON I.id_inspeccion = RI.id_inspeccion
    WHERE aprobado = 0
    AND fecha_inspeccion BETWEEN :fecha_ini AND :fecha_fin
    GROUP BY nombre_parametro
";

$stmt1 = $pdo->prepare($query1);
$stmt1->execute([
    ':fecha_ini' => $fecha_ini,
    ':fecha_fin' => $fecha_fin
]);

$labels1 = [];
$data1 = [];
while ($row = $stmt1->fetch(PDO::FETCH_ASSOC)) {
    $labels1[] = $row['nombre_parametro'];
    $data1[] = (int) $row['cantidad'];
}
$hay_resultados1 = count($data1) > 0;

// Segundo query: inspecciones aprobadas / no aprobadas
$query2 = "
    SELECT clasificacion, COUNT(*) AS total_inspecciones 
    FROM (
        SELECT I.id_inspeccion,
        CASE 
            WHEN MAX(CASE WHEN aprobado = 0 THEN 1 ELSE 0 END) = 1 THEN 'No aprobado'
            ELSE 'Aprobado'
        END AS clasificacion
        FROM resultado_inspeccion RI
        JOIN inspeccion I ON I.id_inspeccion = RI.id_inspeccion
        WHERE fecha_inspeccion BETWEEN :fecha_ini AND :fecha_fin
        GROUP BY I.id_inspeccion
    ) subquery
    GROUP BY clasificacion
";

$stmt2 = $pdo->prepare($query2);
$stmt2->execute([
    ':fecha_ini' => $fecha_ini,
    ':fecha_fin' => $fecha_fin
]);

$labels2 = [];
$data2 = [];
while ($row = $stmt2->fetch(PDO::FETCH_ASSOC)) {
    $labels2[] = $row['clasificacion'];
    $data2[] = (int) $row['total_inspecciones'];
}
$hay_resultados2 = count($data2) > 0;

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FHE | Reportes Estadísticos</title>
    <link rel="stylesheet" href="../styles.css">
    <link rel="stylesheet" href="../css/menu.css">
    <style>
        .mensaje-vacio {
            color: #A21726;
            font-size: 1.2rem;
            font-weight: bold;
            text-align: center;
            margin-top: 2rem;
        }
        .tabla-resultados {
            margin-top: 2rem;
            width: 100%;
            border-collapse: collapse;
        }
        .tabla-resultados th, .tabla-resultados td {
            padding: 0.8rem 1rem;
            border: 1px solid #ccc;
            text-align: center;
        }
        .tabla-resultados th {
            background-color: #f4f4f4;
        }
    </style>
</head>
<body>
<main class="contenedor hoja">
    <?php include '../includes/header.php' ?>

    <div class="contenedor__modulo">
        <a href="estadisticos.php" class="atras">Ir atrás</a>
        <h2 class="heading">Reporte Estadístico [<?= htmlspecialchars($fecha_ini) ?> - <?= htmlspecialchars($fecha_fin) ?>]</h2>

        <div class="contenedor-graficas">

            <!-- Gráfica 1 -->
            <div class="grafica">
                <h3>Parámetros no aprobados</h3>
                <?php if ($hay_resultados1): ?>
                    <canvas id="graficaParametros"></canvas>
                    <table class="tabla-resultados">
                        <thead>
                            <tr><th>Parámetro</th><th>No aprobados</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($labels1 as $i => $label): ?>
                                <tr>
                                    <td><?= htmlspecialchars($label) ?></td>
                                    <td><?= $data1[$i] ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p class="mensaje-vacio">No se encontraron parámetros no aprobados en el rango seleccionado.</p>
                <?php endif; ?>
            </div>

            <!-- Gráfica 2 -->
            <div class="grafica">
                <h3>Clasificación de inspecciones</h3>
                <?php if ($hay_resultados2): ?>
                    <canvas id="graficaClasificacion"></canvas>
                    <table class="tabla-resultados">
                        <thead>
                            <tr><th>Clasificación</th><th>Total de inspecciones</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($labels2 as $i => $label): ?>
                                <tr>
                                    <td><?= htmlspecialchars($label) ?></td>
                                    <td><?= $data2[$i] ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p class="mensaje-vacio">No se encontraron inspecciones en el rango seleccionado.</p>
                <?php endif; ?>
            </div>

        </div>
    </div>

    <?php include '../includes/footer.php' ?>
</main>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const coloresPastel = [
        '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF',
        '#FF9F40', '#C9CBCF', '#8DD17E', '#D3A4FF', '#FF8A65',
        '#BA68C8', '#4DD0E1', '#AED581', '#F06292', '#7986CB',
        '#FFD54F', '#A1887F'
    ];

    // Parámetros no aprobados
    const parametrosLabels = <?php echo json_encode($labels1); ?>;
    const parametrosValues = <?php echo json_encode($data1); ?>;

    if (parametrosLabels.length === 0) {
        document.getElementById('graficaParametros').insertAdjacentHTML(
            'beforebegin',
            '<p style="text-align: center; font-weight: bold;">No hay parámetros no aprobados para mostrar.</p>'
        );
    } else {
        const ctx1 = document.getElementById('graficaParametros').getContext('2d');
        new Chart(ctx1, {
            type: 'pie',
            data: {
                labels: parametrosLabels,
                datasets: [{
                    label: 'Parámetros no aprobados',
                    data: parametrosValues,
                    backgroundColor: coloresPastel.slice(0, parametrosLabels.length),
                    borderColor: '#fff',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { position: 'bottom' },
                    title: {
                        display: true,
                        text: 'Parámetros No Aprobados por Nombre'
                    }
                }
            }
        });
    }

    // Clasificación de inspecciones
    const clasificacionesLabels = <?php echo json_encode($labels2); ?>;
    const clasificacionesValues = <?php echo json_encode($data2); ?>;

    if (clasificacionesLabels.length === 0) {
        document.getElementById('graficaClasificacion').insertAdjacentHTML(
            'beforebegin',
            '<p style="text-align: center; font-weight: bold;">No hay inspecciones registradas en el rango seleccionado.</p>'
        );
    } else {
        const ctx2 = document.getElementById('graficaClasificacion').getContext('2d');
        new Chart(ctx2, {
            type: 'pie',
            data: {
                labels: clasificacionesLabels,
                datasets: [{
                    label: 'Clasificación',
                    data: clasificacionesValues,
                    backgroundColor: ['#8DD17E', '#FF6384'], 
                    borderColor: '#fff',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { position: 'bottom' },
                    title: {
                        display: true,
                        text: 'Clasificación de Inspecciones'
                    }
                }
            }
        });
    }
</script>
</body>
</html>