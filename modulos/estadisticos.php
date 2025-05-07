<?php 
include_once '../includes/config.php';
require '../config/validar_permisos.php'; 
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FHE | Reportes Estadísticos</title>
    <link rel="stylesheet" href="../styles.css">
    <link rel="stylesheet" href="../css/menu.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <main  class="contenedor hoja">
        <?php include '../includes/header.php' ?>

        <div class="contenedor__modulo">
            <a href="../menu.php" class="atras">Ir atrás</a>
            <h2 class="heading">Reporte Estadístico</h2>
            <form action="resultadosestadisticos.php" id="formFechas" class="formulario" method="post">
                <div class="formulario__campo">
                    <label for="fecha_inicio" class="formulario__label">Fecha de inicio</label>
                    <input type="date" name="fecha_inicio" class="formulario__input" require>
                </div>

                <div class="formulario__campo">
                    <label for="fecha_fin" class="formulario__label">Fecha de fin</label>
                    <input type="date" name="fecha_fin" class="formulario__input" require>
                </div>

                <button type="button" id="btnSubmit" class="formulario__submit">Generar Estadísticos</button>
            </form>
        </div>
        <?php include '../includes/footer.php' ?>
    </main>
    <script>
document.getElementById('btnSubmit').addEventListener('click', function () {
    const form = document.getElementById('formFechas');
    const fechaInicio = form.fecha_inicio.value;
    const fechaFin = form.fecha_fin.value;

    const hoy = new Date();
    const fechaHoy = hoy.getFullYear() + '-' +
                    String(hoy.getMonth() + 1).padStart(2, '0') + '-' +
                    String(hoy.getDate()).padStart(2, '0');

    if (!fechaInicio || !fechaFin) {
        Swal.fire({
            icon: 'warning',
            title: 'Campos incompletos',
            text: 'Debes seleccionar ambas fechas.',
        });
        return;
    }

    if (fechaInicio > fechaHoy) {
        Swal.fire({
            icon: 'error',
            title: 'Fecha de inicio inválida',
            text: 'La fecha de inicio no puede ser posterior a hoy.',
        });
        return;
    }

    if (fechaFin > fechaHoy) {
        Swal.fire({
            icon: 'error',
            title: 'Fecha de fin inválida',
            text: 'La fecha de fin no puede ser posterior a hoy.',
        });
        return;
    }

    if (fechaInicio > fechaFin) {
        Swal.fire({
            icon: 'error',
            title: 'Rango de fechas inválido',
            text: 'La fecha de inicio no puede ser mayor que la fecha de fin.',
        });
        return;
    }

    // Confirmación antes de enviar
    Swal.fire({
        title: '¿Deseas continuar?',
        html: `
            <p><strong>Fecha de inicio:</strong> ${fechaInicio}</p>
            <p><strong>Fecha de fin:</strong> ${fechaFin}</p>
        `,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Sí, generar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            form.submit();
        }
    });
});
</script>

</body>
</html>