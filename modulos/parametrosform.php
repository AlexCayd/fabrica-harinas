<?php
    include_once '../includes/config.php';
    require '../config/validar_permisos.php';

    require '../config/conn.php';

    if (!isset($_GET['id']) || !ctype_digit($_GET['id'])) {
        $_SESSION['error'] = 'ID inválido.';
        header('location: parametros.php?tipo=internacionales');
        exit;
    }
    $id = (int) $_GET['id'];
    $origin = $_GET['ow'];

    if ($origin === 'clientes'){    
        $sql = "SELECT 
                P.nombre_parametro, 
                P.lim_Superior, 
                P.lim_Inferior,
                A.nombre AS origen
                FROM parametros P 
                LEFT JOIN clientes A ON A.id_cliente = P.id_cliente 
                WHERE P.id_parametro = ?";
    } else if ($origin === 'equipos_laboratorio'){
        $sql = "SELECT 
                P.nombre_parametro, 
                P.lim_Superior, 
                P.lim_Inferior,
                A.clave AS origen,
                A.tipo_equipo
                FROM parametros P 
                LEFT JOIN equipos_laboratorio A ON A.id_equipo = P.id_equipo 
                WHERE P.id_parametro = ?";
    } else {
        $_SESSION['error'] = 'URL no válida.';
        header ('location: parametros.php?tipo=internacionales');
        exit;
    }

   
    $stmt = $pdo -> prepare($sql);
    $stmt -> execute([$id]);
    
    $parametro = $stmt -> fetch();

    if (empty($parametro)){
        $_SESSION['error'] = 'Parámetro no encontrado.';
        header('location: parametros.php?tipo=' . (($origin === 'clientes') ? 'personalizados' : 'internacionales'));
        exit;
    }
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FHE | Clientes</title>
    <link rel="stylesheet" href="../styles.css">
    <link rel="stylesheet" href="../css/menu.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>
    <main class="contenedor hoja">
        <?php include '../includes/header.php'; ?>

        <div class="contenedor__modulo">
            <a href="parametros.php?tipo=<?php echo ($origin === 'clientes') ? 'personalizados' : 'internacionales'; ?>" class="atras">Ir atrás</a>
            <h2 class="heading">Editar Parámetro de <?php echo ($origin === 'clientes')?'Cliente':'Equipo'; ?></h2>
            <form action="../config/parametros/updateParametros.php" id="formParametros" class="formulario" method="post">

                <input type="hidden" name="origen" value="<?php echo $origin ?>">

                <div class="formulario__campo">
                    <label for="id" class="formulario__label">Id de parámetro [lectura]</label>
                    <input type="text" name="id" class="formulario__input" value="<?php echo $id?>" readonly>
                </div>

                <div class="formulario__campo">
                    <label for="nombre_parametro" class="formulario__label">Parametro [lectura]</label>
                    <input type="text" name="nombre_parametro" class="formulario__input" 
                        value="<?php echo htmlspecialchars($parametro['nombre_parametro']) ?>" readonly>
                </div>

                <div class="formulario__campo">
                    <label for="nombre" class="formulario__label"><?php echo ($origin === 'clientes')?'Cliente':'Clave del equipo'; ?> [lectura]</label>
                    <input type="text" name="nombre" class="formulario__input" 
                        value="<?php echo htmlspecialchars($parametro['origen']) ?>" readonly>
                </div>

                <?php 
                    if ($origin === 'equipos_laboratorio'){
                        echo '
                        <div class="formulario__campo">
                            <label for="tipo_equipo" class="formulario__label">Tipo de equipo [lectura]</label>
                            <input type="text" name="tipo_equipo" class="formulario__input" value="' . 
                            htmlspecialchars($parametro['tipo_equipo']) . '" readonly>
                        </div>
                        ';
                    }
                ?>
                <div class="formulario__campo">
                    <label for="limite_inf" class="formulario__label">Límite inferior</label>
                    <input type="number" name="limite_inf" value="<?php echo htmlspecialchars($parametro['lim_Inferior']) ?>" class="formulario__input" placeholder="Límite inferior">
                </div>

                <div class="formulario__campo">
                    <label for="limite_sup" class="formulario__label">Límite superior</label>
                    <input type="number" name="limite_sup" value="<?php echo htmlspecialchars($parametro['lim_Superior']) ?>" class="formulario__input" placeholder="Límite superior">
                </div>

                <button type="button" id="btnSubmit" class="formulario__submit">Actualizar Parámetro</button>
            </form>
        </div>
        <?php include '../includes/footer.php'; ?>
    </main>
</body>
<script>
    document.getElementById('btnSubmit').addEventListener('click', function() {
        const form = document.getElementById('formParametros');
        const limInferior = form.limite_inf.value;
        const limSuperior = form.limite_sup.value;

        const regexDecimal = /^\d{1,10}(\.\d{1,2})?$/;

        if (limInferior === "" || limSuperior === "") {
            Swal.fire({
                icon: 'error',
                title: 'Campo vacío',
                text: 'No puedes dejar ningún límite vacío.',
            });
            return;
        }

        if (!regexDecimal.test(limInferior) || !regexDecimal.test(limSuperior)) {
            Swal.fire({
                icon: 'error',
                title: 'Formato inválido',
                text: 'Cada límite debe tener hasta 10 dígitos enteros y como máximo 2 decimales.',
            });
            return;
        }

        if (parseFloat(limSuperior) <= parseFloat(limInferior)) {
            Swal.fire({
                icon: 'error',
                title: 'Límite incorrecto',
                text: 'El límite superior debe ser mayor al límite inferior.',
            });
            return;
        }

        let resumen = `<b>Limite inferior:</b> ${limInferior}<br><b>Limite superior:</b> ${limSuperior}<br>`;

        Swal.fire({
            title: 'Confirma los datos antes de continuar',
            html: resumen,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Confirmar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                form.submit();
            }
        });
    });
</script>
</html>