<?php
require '../config/validar_permisos.php';

include '../config/conn.php';
include '../config/functions.php';
session_start();

// Obtener el ID del cliente a editar
$id_cliente = $_GET['id'];

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
                  WHERE id_cliente = :id_cliente AND tipo = 'Personalizado'";
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
    </style>
    <main class="contenedor hoja">
        <?php include '../includes/header.php' ?>


        <div class="contenedor__modulo">
            <a href="clientes.php" class="atras">Ir atrás</a>
            <h2 class="heading">Editar Cliente</h2>
            <form action="clientes/editar_cliente.php?id=<?php echo htmlspecialchars($id_cliente); ?>" class="formulario" method="post">
                <input type="hidden" name="id" value="<?php echo htmlspecialchars($id_cliente); ?>" required>

                <div class="formulario__campo">
                    <label for="nombre" class="formulario__label">Nombre</label>
                    <input type="text" name="nombre" class="formulario__input"
                        value="<?php echo htmlspecialchars($cliente['nombre']); ?>" required>
                </div>

                <div class="formulario__campo">
                    <label for="certificado" class="formulario__label">Requiere certificado</label>
                    <select name="certificado" class="formulario__select" required>
                        <option value="1" <?php echo $cliente['req_certificado'] == 1 ? 'selected' : ''; ?>>Sí</option>
                        <option value="0" <?php echo $cliente['req_certificado'] == 0 ? 'selected' : ''; ?>>No</option>
                    </select>
                </div>

                <div class="formulario__campo">
                    <label for="email" class="formulario__label">Correo electrónico</label>
                    <input type="email" name="email" class="formulario__input"
                        value="<?php echo htmlspecialchars($cliente['correo_contacto']); ?>" required>
                </div>

                <div class="formulario__campo">
                    <label for="rfc" class="formulario__label">RFC</label>
                    <input type="text" name="rfc" class="formulario__input"
                        value="<?php echo htmlspecialchars($cliente['rfc']); ?>" required>
                </div>

                <div class="formulario__campo">
                    <label for="puesto" class="formulario__label">Puesto</label>
                    <input type="text" name="puesto" class="formulario__input"
                        value="<?php echo htmlspecialchars($cliente['puesto_contacto']); ?>" required>
                </div>

                <div class="formulario__campo">
                    <label for="numero-telefonico" class="formulario__label">Número telefónico</label>
                    <input type="text" name="numero-telefonico" class="formulario__input"
                        value="<?php echo htmlspecialchars($cliente['telefono_contacto']); ?>" required>
                </div>

                <div class="formulario__campo">
                    <label for="direccion-fiscal" class="formulario__label">Dirección fiscal</label>
                    <input type="text" name="direccion-fiscal" class="formulario__input"
                        value="<?php echo htmlspecialchars($cliente['direccion_fiscal']); ?>" required>
                </div>

                <div class="formulario__campo">
                    <label for="estado" class="formulario__label">Estado</label>
                    <select name="estado" class="formulario__select">
                        <option value="activo" <?php echo $cliente['estado'] == 'activo' ? 'selected' : ''; ?>>Activo</option>
                        <option value="inactivo" <?php echo $cliente['estado'] == 'inactivo' ? 'selected' : ''; ?>>Inactivo</option>
                    </select>
                </div>

                <div class="formulario__campo">
                    <label for="parametros" class="formulario__label">Parámetros</label>
                    <select name="parametros" id="parametros" class="formulario__select">
                        <option value="Internacionales" <?php echo $cliente['parametros'] == 'Internacionales' ? 'selected' : ''; ?>>Internacionales</option>
                        <option value="Personalizados" <?php echo $cliente['parametros'] == 'Personalizados' ? 'selected' : ''; ?>>Personalizados</option>
                    </select>
                </div>

                <div class="formulario__campo">
                    <label for="tipo_equipo" class="formulario__label">Tipo de Equipo</label>
                    <select class="formulario__input" id="tipo_equipo" name="tipo_equipo" required>
                        <option value="Alveógrafo" <?php echo $cliente['tipo_equipo'] == 'Alveógrafo' ? 'selected' : ''; ?>>Alveógrafo</option>
                        <option value="Farinógrafo" <?php echo $cliente['tipo_equipo'] == 'Farinógrafo' ? 'selected' : ''; ?>>Farinógrafo</option>
                    </select>
                </div>

                <!-- Sección para Alveógrafos -->
                <div id="parametros-alveografo" class="parametros-section" style="display: none;">
                    <div class="parametros-title">Valores de referencia internacionales - Alveógrafo</div>

                    <?php foreach ($parametros_alveografo as $param): ?>
                        <div class="parametro-row">
                            <div class="parametro-nombre"><?php echo htmlspecialchars($param['nombre_parametro']); ?></div>
                            <div class="parametro-inputs">
                                <div>
                                    <input type="number" step="0.01" class="parametro-input"
                                        name="alveografo[<?php echo $param['nombre_parametro']; ?>][min]"
                                        value="<?php echo htmlspecialchars($param['lim_Inferior']); ?>"
                                        placeholder="Mínimo">
                                    <div class="parametro-label">Límite inferior</div>
                                </div>
                                <div>
                                    <input type="number" step="0.01" class="parametro-input"
                                        name="alveografo[<?php echo $param['nombre_parametro']; ?>][max]"
                                        value="<?php echo htmlspecialchars($param['lim_Superior']); ?>"
                                        placeholder="Máximo">
                                    <div class="parametro-label">Límite superior</div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Sección para Farinógrafos -->
                <div id="parametros-farinografo" class="parametros-section" style="display: none;">
                    <div class="parametros-title">Valores de referencia internacionales - Farinógrafo</div>

                    <?php foreach ($parametros_farinografo as $param): ?>
                        <div class="parametro-row">
                            <div class="parametro-nombre"><?php echo htmlspecialchars($param['nombre_parametro']); ?></div>
                            <div class="parametro-inputs">
                                <div>
                                    <input type="number" step="0.01" class="parametro-input"
                                        name="farinografo[<?php echo $param['nombre_parametro']; ?>][min]"
                                        value="<?php echo htmlspecialchars($param['lim_Inferior']); ?>"
                                        placeholder="Mínimo">
                                    <div class="parametro-label">Límite inferior</div>
                                </div>
                                <div>
                                    <input type="number" step="0.01" class="parametro-input"
                                        name="farinografo[<?php echo $param['nombre_parametro']; ?>][max]"
                                        value="<?php echo htmlspecialchars($param['lim_Superior']); ?>"
                                        placeholder="Máximo">
                                    <div class="parametro-label">Límite superior</div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <input type="submit" class="formulario__submit" value="Actualizar cliente">
            </form>
        </div>
        <?php include '../includes/footer.php' ?>
    </main>

    <script>
        // Get DOM elements
        const tipoEquipoSelector = document.getElementById('tipo_equipo');
        const parametrosSelector = document.getElementById('parametros');
        const seccionAlveografo = document.getElementById('parametros-alveografo');
        const seccionFarinografo = document.getElementById('parametros-farinografo');

        // Function to update sections visibility
        function actualizarSecciones() {
            const tipoSeleccionado = tipoEquipoSelector.value;
            const parametrosSeleccionados = parametrosSelector.value;

            // Hide both sections if "Internacionales" is selected
            if (parametrosSeleccionados === 'Internacionales') {
                seccionAlveografo.style.display = 'none';
                if (seccionFarinografo) {
                    seccionFarinografo.style.display = 'none';
                }
                return;
            }

            // Show/hide sections based on equipment type when "Personalizados" is selected
            if (tipoSeleccionado === 'Alveógrafo') {
                seccionAlveografo.style.display = 'block';
                if (seccionFarinografo) {
                    seccionFarinografo.style.display = 'none';
                }
            } else if (tipoSeleccionado === 'Farinógrafo') {
                seccionAlveografo.style.display = 'none';
                if (seccionFarinografo) {
                    seccionFarinografo.style.display = 'block';
                }
            } else {
                seccionAlveografo.style.display = 'none';
                if (seccionFarinografo) {
                    seccionFarinografo.style.display = 'none';
                }
            }
        }

        // Add event listeners for both select changes
        tipoEquipoSelector.addEventListener('change', actualizarSecciones);
        parametrosSelector.addEventListener('change', actualizarSecciones);

        // Run on page load to set initial state
        actualizarSecciones();
    </script>
</body>

</html>