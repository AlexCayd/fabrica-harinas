<?php
require '../config/validar_permisos.php';
include '../config/conn.php';
include '../config/functions.php';
session_start();

// Obtener el ID del cliente a editar
$id_cliente = $_GET['id'];
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (!$id_cliente) {
    header("Location: /fabrica-harinas/modulos/clientes.php");
    exit;
}

// Obtener datos del cliente
$sql_cliente = "SELECT * FROM Clientes WHERE id_cliente = :id";
$stmt_cliente = $pdo->prepare($sql_cliente);
$stmt_cliente->bindParam(':id', $id_cliente);
$stmt_cliente->execute();
$cliente = $stmt_cliente->fetch(PDO::FETCH_ASSOC);

if (!$cliente) {
    header("Location: /fabrica-harinas/modulos/clientes.php");
    exit;
}

// Obtener parámetros del cliente
$sql_parametros = "SELECT nombre_parametro, lim_Inferior, lim_Superior 
                  FROM Parametros 
                  WHERE id_cliente = :id_cliente";
$stmt_parametros = $pdo->prepare($sql_parametros);
$stmt_parametros->bindParam(':id_cliente', $id_cliente);
$stmt_parametros->execute();
$parametros = $stmt_parametros->fetchAll(PDO::FETCH_ASSOC);

// Separar parámetros por tipo de equipo
$parametros_alveografo = [];
$parametros_farinografo = [];

foreach ($parametros as $param) {
    if (strpos($param['nombre_parametro'], 'alveograma') !== false) {
        $parametros_alveografo[] = $param;
    } else {
        $parametros_farinografo[] = $param;
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FHE | Editar Cliente</title>
    <link rel="stylesheet" href="../styles.css">
    <link rel="stylesheet" href="../css/menu.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

</head>

<body>
    <style>
        .parametros-section {
            margin-top: 20px;
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            background-color: #f9f9f9;
        }

        .parametros-title {
            font-weight: bold;
            margin-bottom: 15px;
            color: #333;
            border-bottom: 1px solid #ddd;
            padding-bottom: 8px;
        }

        .parametro-row {
            display: flex;
            flex-wrap: wrap;
            margin-bottom: 10px;
            align-items: center;
        }

        .parametro-nombre {
            flex: 0 0 30%;
            font-weight: bold;
        }

        .parametro-inputs {
            flex: 0 0 70%;
            display: flex;
            gap: 10px;
        }

        .parametro-input {
            width: 100px;
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        .parametro-label {
            font-size: 12px;
            color: #666;
        }

         /* Estilos para el título de datos de contacto */
         h1 {
            color: var(--colorSecundario);
            font-family: var(--fuenteHeading);
            font-size: 2rem;
            margin: 2rem 0 1.5rem;
            padding-bottom: 0.5rem;
            border-bottom: 3px solid var(--colorPrimario);
            text-align: center;
            display: block;
        }

        .parametro-group {
            background-color: #f9f9f9;
            padding: 1.5rem;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
        }

        .formulario-baja {
            display: none;
        }

    </style>
    <main class="contenedor hoja">
        <?php include '../includes/header.php' ?>

        <div class="contenedor__modulo">
            <a href="clientes.php" class="atras">Ir atrás</a>
            <h2 class="heading">Editar Cliente</h2>
            <form action="clientes/editar_cliente.php?id=<?php echo htmlspecialchars($id_cliente); ?>" class="formulario" method="post">

                <div class="formulario__campo">
                    <label for="nombre" class="formulario__label">Nombre de la empresa </label>
                    <input type="text" name="nombre" class="formulario__input"
                        value="<?php echo htmlspecialchars($cliente['nombre']); ?>" required>
                </div>

                <div class="formulario__campo">
                    <label for="certificado" class="formulario__label">Requiere certificado</label>
                    <select name="certificado" class="formulario__select">
                        <option value="1" <?php echo $cliente['req_certificado'] == 1 ? 'selected' : ''; ?>>Sí</option>
                        <option value="0" <?php echo $cliente['req_certificado'] == 0 ? 'selected' : ''; ?>>No</option>
                    </select>
                </div>

                <div class="formulario__campo">
                    <label for="rfc" class="formulario__label">RFC</label>
                    <input type="text" name="rfc" class="formulario__input"
                        value="<?php echo htmlspecialchars($cliente['rfc']); ?>" required>
                </div>

                <div class="formulario__campo">
                    <label for="estado" class="formulario__label">Estado</label>
                    <select name="estado" id="estado" class="formulario__select">
                        <option value="activo" <?php echo $cliente['estado'] == 'Activo' ? 'selected' : ''; ?>>Activo</option>
                        <option value="inactivo" <?php echo $cliente['estado'] == 'Inactivo' ? 'selected' : ''; ?>>Inactivo</option>
                    </select>
                </div>

                <div class="formulario__campo">
                    <label for="filtro-aprobacion" class="formulario__label">Filtrar por aprobación</label>
                    <select name="filtro-aprobacion" id="filtro-aprobacion" class="formulario__select">
                        <option value="todos">Todos</option>
                        <option value="aprobado">Aprobado</option>
                        <option value="desaprobado">Desaprobado</option>
                    </select>
                </div>
                
                <div class="formulario__campo formulario-baja" id="causa-baja">
                    <label for="causa-baja" class="formulario__label"> Causa de la baja </label>
                    <input type="text" name="causa-baja" class="formulario__input"
                        value="<?php echo $cliente['causa_baja']; ?>">
                </div>

                <div class="formulario__campo">
                    <label for="parametros" class="formulario__label">Parámetros [lectura]</label>
                    <select name="parametros" id="parametros" class="formulario__select" disabled>
                        <option value="Internacionales" <?php echo $cliente['parametros'] == 'Internacionales' ? 'selected' : ''; ?>>Internacionales</option>
                        <option value="Personalizados" <?php echo $cliente['parametros'] == 'Personalizados' ? 'selected' : ''; ?>>Personalizados</option>
                    </select>
                </div>

                <div class="formulario__campo">
                    <label for="tipo_equipo" class="formulario__label">Tipo de Equipo [lectura]</label>
                    <select class="formulario__input" id="tipo_equipo" name="tipo_equipo" required disabled>
                        <option value="Alveógrafo" <?php echo $cliente['tipo_equipo'] == 'Alveógrafo' ? 'selected' : ''; ?>>Alveógrafo</option>
                        <option value="Farinógrafo" <?php echo $cliente['tipo_equipo'] == 'Farinógrafo' ? 'selected' : ''; ?>>Farinógrafo</option>
                    </select>
                </div>

                <div class="formulario__campo" id="datos-contacto">
                    <h1>Datos de contacto</h1> 
                </div> 

                <div class="formulario__campo">
                    <label for="puesto_nombre" class="formulario__label"> Nombre </label>
                    <input type="text" name="puesto_nombre" class="formulario__input" placeholder="Nombre" 
                    value="<?php echo htmlspecialchars($cliente['nombre_contacto']); ?>" required>
                </div>

                <div class="formulario__campo">
                    <label for="puesto" class="formulario__label"> Puesto </label>
                    <input type="text" name="puesto" class="formulario__input" placeholder="Puesto" 
                    value="<?php echo $cliente['puesto_contacto']; ?>" required>
                </div>

                <div class="formulario__campo">
                    <label for="email" class="formulario__label">Correo electrónico</label>
                    <input type="email" name="email" class="formulario__input"
                        value="<?php echo $cliente['correo_contacto']; ?>" required>
                </div>

                <div class="formulario__campo">
                    <label for="numero-telefonico" class="formulario__label">Número telefónico</label>
                    <input type="text" name="numero-telefonico" class="formulario__input"
                        value="<?php echo $cliente['telefono_contacto']; ?>" required>
                </div>

                <div class="formulario__campo">
                    <label for="direccion-fiscal" class="formulario__label">Dirección fiscal</label>
                    <input type="text" name="direccion-fiscal" class="formulario__input"
                        value="<?php echo $cliente['direccion_fiscal']; ?>" required>
                </div>

                
                <input type="submit" class="formulario__submit" value="Actualizar cliente">
            </form>
        </div>
        <?php include '../includes/footer.php' ?>
    </main>

    <script>
        const estadoSelector = document.getElementById('estado');
        const filtroAprobacion = document.getElementById('filtro-aprobacion');

        // Función para mostrar campo de causa de la baja
        function verCausaBaja() {
            const causaBaja = document.getElementById('causa-baja');
            const datosContacto = document.getElementById('datos-contacto');

            if(estadoSelector.value === 'inactivo') {
                causaBaja.style.display = 'flex';
                datosContacto.style.gridColumn = '1 / 3';
            } else if(estadoSelector.value === 'activo') {
                causaBaja.style.display = 'none';
                datosContacto.style.gridColumn = '1 / 3';
            } else {
                causaBaja.style.display = 'none';
                datosContacto.style.gridColumn = '1 / 3';
            }
        }

        // Función para filtrar por aprobación
        function filtrarPorAprobacion() {
            const valorFiltro = filtroAprobacion.value;
            const elementos = document.querySelectorAll('.formulario__campo');

            elementos.forEach(elemento => {
                if (valorFiltro === 'todos') {
                    elemento.style.display = 'block';
                } else {
                    // Aquí puedes agregar la lógica para mostrar/ocultar elementos según el estado de aprobación
                    // Por ejemplo, si tienes una clase o atributo que indique el estado de aprobación:
                    const estadoAprobacion = elemento.getAttribute('data-aprobacion');
                    if (estadoAprobacion === valorFiltro) {
                        elemento.style.display = 'block';
                    } else {
                        elemento.style.display = 'none';
                    }
                }
            });
        }

        // Agregar eventos a los selectores
        estadoSelector.addEventListener('change', verCausaBaja);
        filtroAprobacion.addEventListener('change', filtrarPorAprobacion);

        // Ejecutar funciones al cargar la página
        verCausaBaja();
        filtrarPorAprobacion();
    </script>
</body>

</html>