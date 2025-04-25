<?php require '../config/validar_permisos.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FHE | Reportes Estadísticos</title>
    <link rel="stylesheet" href="../styles.css">
    <link rel="stylesheet" href="../css/menu.css">
</head>
<body>
    <main  class="contenedor hoja">
        <?php include '../includes/header.php' ?>

        <div class="contenedor__modulo">
            <a href="estadisticos.php" class="atras">Ir atrás</a>
            <h2 class="heading">Resultados de estadísticos</h2>

            <div class="grafica" style="">
                <canvas id="graficaParametros"></canvas>
            </div>

            
            <div class="resultados">
                <div class="resultados__contenedor">
                    <h2 class="resultados__texto">Total de parámetros analizados</h2>
                    <h2 class="resultados__numero">15</h2>
                </div>

                <div class="resultados__contenedor">
                    <h2 class="resultados__texto">Parámetros aprobados</h2>
                    <h2 class="resultados__numero">10</h2>
                </div>

                <div class="resultados__contenedor">
                    <h2 class="resultados__texto">Parámetros no aprobados</h2>
                    <h2 class="resultados__numero">10</h2>
                </div>

                <div class="resultados__contenedor">
                    <h2 class="resultados__texto">Certificados generados</h2>
                    <h2 class="resultados__numero">6</h2>
                </div>
            </div>
        </div>
        <?php include '../includes/footer.php' ?>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const ctx = document.getElementById('graficaParametros').getContext('2d');
        const grafica = new Chart(ctx, {
            type: 'pie',
            data: {
                labels: ['Aprobados', 'No aprobados'],
                datasets: [{
                    label: 'Parámetros',
                    data: [10, 5], // valores de ejemplo
                    backgroundColor: [
                        '#4c3325',  
                        '#EBDED0' 
                    ],
                    borderColor: '#fff',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    },
                    title: {
                        display: true,
                        text: 'Distribución de Parámetros Analizados'
                    }
                }
            }
        });
    </script>
</body>
</html>