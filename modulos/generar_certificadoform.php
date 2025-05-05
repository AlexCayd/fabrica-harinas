<?php 
include '../config/conn.php';
include '../config/validar_permisos.php';

$sql = "SELECT nombre, id_inspeccion FROM Inspeccion, Clientes WHERE Inspeccion.id_cliente = Clientes.id_cliente";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$clientes = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../styles.css">
    <link rel="stylesheet" href="../css/menu.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <title> Generar certificado </title>
</head>
<body>
<main class="contenedor hoja">
        <?php include '../includes/header.php' ?>

        <div class="contenedor__modulo">
            <a href="historico.php" class="atras">Ir atrás</a>
            <h2 class="heading">Agregar Cliente</h2>
            <form action="../config/crear_certificado.php" class="formulario" method="post">
                <div class="formulario__campo">
                    <label for="nombre" class="formulario__label"> Nombre cliente </label>
                    <select name="nombre" class="formulario__input">
                        <?php foreach($clientes as $cliente): ?>
                            <option value="<?php echo $cliente['id_inspeccion']; ?>"><?php echo $cliente['nombre']; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="formulario__campo">
                    <label for="fecha_emision" class="formulario__label">  Fecha de emisión </label>
                    <input type="date" name="fecha_emision" id="fecha_emision" class="formulario__input" value="<?php echo date('Y-m-d'); ?>" required>
                </div>
                
                <div class="formulario__campo">
                    <label for="cantidad_solicitada" class="formulario__label"> Cantidad solicitada </label>
                    <input type="text" name="cantidad_solicitada" class="formulario__input" placeholder="Cantidad solicitada" required>
                </div> 

                <div class="formulario__campo">
                    <label for="cantidad_recibida" class="formulario__label"> Cantidad recibida </label>
                    <input type="text" name="cantidad_recibida" class="formulario__input" placeholder="Cantidad recibida" required>
                </div>  

                <div class="formulario__campo">
                    <label for="fecha_envio" class="formulario__label">  Fecha de envio</label>
                    <input type="date" name="fecha_envio" id="fecha_envio" class="formulario__input" required>
                </div>
                
                <div class="formulario__campo">
                    <label for="fecha_caducidad" class="formulario__label">  Fecha de caducidad </label>
                    <input type="date" name="fecha_caducidad" id="fecha_caducidad" class="formulario__input" required>
                </div>

                <div class="formulario__campo">
                    <label for="desviacion" class="formulario__label">  Desviacion </label>
                    <input type="number" name="desviacion" id="desviacion" class="formulario__input" required>
                </div>

                <input type="submit" class="formulario__submit" value="Generar certificado">
            </form>
        </div>
        <?php include '../includes/footer.php' ?>
    </main>
</body>
</html>