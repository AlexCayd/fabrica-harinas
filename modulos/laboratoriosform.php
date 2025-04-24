<?php
require '../config/validar_permisos.php';
// Incluir el archivo de configuración de la base de datos
require '../config/conn.php';

// Variable para determinar si estamos editando un equipo existente
$editando = false;
$equipo = null;

// Verificar si se ha recibido un ID de equipo para editar
if (isset($_GET['id']) && !empty($_GET['id'])) {
    $id_equipo = $_GET['id'];
    $editando = true;
    
    // Consultar los datos del equipo a editar
    $sql_equipo = "SELECT * FROM Equipos_Laboratorio WHERE id_equipo = :id_equipo";
    $stmt_equipo = $pdo->prepare($sql_equipo);
    $stmt_equipo->bindParam(':id_equipo', $id_equipo);
    $stmt_equipo->execute();
    
    $equipo = $stmt_equipo->fetch(PDO::FETCH_ASSOC);
    
    // Si no se encuentra el equipo, redirigir a la lista
    if (!$equipo) {
        header('Location: laboratorios.php');
        exit;
    }
    
    // Consultar los parámetros del equipo
    $sql_parametros = "SELECT * FROM Parametros WHERE id_equipo = :id_equipo AND tipo = 'Internacional'";
    $stmt_parametros = $pdo->prepare($sql_parametros);
    $stmt_parametros->bindParam(':id_equipo', $id_equipo);
    $stmt_parametros->execute();
    
    $parametros_equipo = $stmt_parametros->fetchAll(PDO::FETCH_ASSOC);
}

// Consulta para obtener los responsables disponibles
$sql_responsables = "SELECT id_usuario, nombre FROM Usuarios 
                    WHERE rol IN ('Gerencia de Control de Calidad', 'Laboratorio')
                    ORDER BY nombre";
$responsables = $pdo->query($sql_responsables)->fetchAll(PDO::FETCH_ASSOC);

// Definir parámetros por defecto según el tipo de equipo
$parametros_alveografo = [
    ['nombre' => 'Valor P (mm H₂O)', 'id_parametro' => 'alveograma_p', 'lim_Inferior' => '', 'lim_Superior' => ''],
    ['nombre' => 'Valor L (mm)', 'id_parametro' => 'alveograma_l', 'lim_Inferior' => '', 'lim_Superior' => ''],
    ['nombre' => 'Valor W (10⁻⁴ J)', 'id_parametro' => 'alveograma_w', 'lim_Inferior' => '', 'lim_Superior' => ''],
    ['nombre' => 'Relación P/L', 'id_parametro' => 'alveograma_pl', 'lim_Inferior' => '', 'lim_Superior' => ''],
    ['nombre' => 'Índice de elasticidad (Ie)', 'id_parametro' => 'alveograma_ie', 'lim_Inferior' => '', 'lim_Superior' => '']
];

$parametros_farinografo = [
    ['nombre' => 'Absorción de agua (%)', 'id_parametro' => 'absorcion_agua', 'lim_Inferior' => '', 'lim_Superior' => ''],
    ['nombre' => 'Tiempo de desarrollo (min)', 'id_parametro' => 'tiempo_desarrollo', 'lim_Inferior' => '', 'lim_Superior' => ''],
    ['nombre' => 'Estabilidad (min)', 'id_parametro' => 'estabilidad', 'lim_Inferior' => '', 'lim_Superior' => '']
];

// Si estamos editando, rellenar los valores de los parámetros
if ($editando && !empty($parametros_equipo)) {
    foreach ($parametros_equipo as $param) {
        if ($equipo['tipo_equipo'] == 'Alveógrafo') {
            foreach ($parametros_alveografo as &$p) {
                if ($p['id_parametro'] == $param['nombre_parametro']) {
                    $p['lim_Inferior'] = $param['lim_Inferior'];
                    $p['lim_Superior'] = $param['lim_Superior'];
                    break;
                }
            }
        } else if ($equipo['tipo_equipo'] == 'Farinógrafo') {
            foreach ($parametros_farinografo as &$p) {
                if ($p['id_parametro'] == $param['nombre_parametro']) {
                    $p['lim_Inferior'] = $param['lim_Inferior'];
                    $p['lim_Superior'] = $param['lim_Superior'];
                    break;
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FHE | <?php echo $editando ? 'Editar' : 'Agregar'; ?> Equipo de Laboratorio</title>
    <link rel="stylesheet" href="../styles.css">
    <link rel="stylesheet" href="../css/menu.css">
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
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Mostrar/ocultar secciones de parámetros según el tipo de equipo seleccionado
            const tipoEquipoSelect = document.querySelector('select[name="tipo_equipo"]');
            const seccionAlveografo = document.getElementById('parametros-alveografo');
            const seccionFarinografo = document.getElementById('parametros-farinografo');
            
            function mostrarParametrosPorTipo() {
                const tipoSeleccionado = tipoEquipoSelect.value;
                
                if (tipoSeleccionado === 'Alveógrafo') {
                    seccionAlveografo.style.display = 'block';
                    seccionFarinografo.style.display = 'none';
                } else if (tipoSeleccionado === 'Farinógrafo') {
                    seccionAlveografo.style.display = 'none';
                    seccionFarinografo.style.display = 'block';
                } else {
                    seccionAlveografo.style.display = 'none';
                    seccionFarinografo.style.display = 'none';
                }
            }
            
            tipoEquipoSelect.addEventListener('change', mostrarParametrosPorTipo);
            
            // Inicializar vista
            mostrarParametrosPorTipo();
        });
    </script>
</head>
<body>
    <main class="contenedor hoja">
        <?php include '../includes/header.php'; ?>

        <div class="contenedor__modulo">
            <a href="laboratorios.php" class="atras">Ir atrás</a>
            <h2 class="heading"><?php echo $editando ? 'Editar' : 'Agregar'; ?> Equipo de Laboratorio</h2>
            <form action="../config/procesar_equipo.php" class="formulario" method="POST">
                <!-- Campo oculto para identificar si estamos editando -->
                <?php if ($editando): ?>
                <input type="hidden" name="editando" value="1">
                <input type="hidden" name="id_equipo" value="<?php echo htmlspecialchars($equipo['id_equipo']); ?>">
                <?php endif; ?>

                <div class="formulario__campo">
                    <label for="clave" class="formulario__label">Clave del equipo</label>
                    <input type="text" class="formulario__input" placeholder="Clave del equipo" name="clave" value="<?php echo $editando ? htmlspecialchars($equipo['clave']) : ''; ?>" required>
                </div>
                
                <div class="formulario__campo">
                    <label for="tipo_equipo" class="formulario__label">Tipo de equipo</label>
                    <select class="formulario__input" name="tipo_equipo" required>
                        <option value="" disabled <?php echo !$editando ? 'selected' : ''; ?>>-- Seleccione un tipo --</option>
                        <option value="Alveógrafo" <?php echo ($editando && $equipo['tipo_equipo'] == 'Alveógrafo') ? 'selected' : ''; ?>>Alveógrafo</option>
                        <option value="Farinógrafo" <?php echo ($editando && $equipo['tipo_equipo'] == 'Farinógrafo') ? 'selected' : ''; ?>>Farinógrafo</option>
                    </select>
                </div>
                
                <div class="formulario__campo">
                    <label for="marca" class="formulario__label">Marca</label>
                    <input type="text" class="formulario__input" placeholder="Marca" name="marca" value="<?php echo $editando ? htmlspecialchars($equipo['marca']) : ''; ?>">
                </div>

                <div class="formulario__campo">
                    <label for="modelo" class="formulario__label">Modelo</label>
                    <input type="text" class="formulario__input" placeholder="Modelo" name="modelo" value="<?php echo $editando ? htmlspecialchars($equipo['modelo']) : ''; ?>">
                </div>

                <div class="formulario__campo">
                    <label for="serie" class="formulario__label">Serie</label>
                    <input type="text" class="formulario__input" placeholder="Serie" name="serie" value="<?php echo $editando ? htmlspecialchars($equipo['serie']) : ''; ?>" required>
                </div>
                
                <div class="formulario__campo">
                    <label for="proveedor" class="formulario__label">Proveedor</label>
                    <input type="text" class="formulario__input" placeholder="Proveedor" name="proveedor" value="<?php echo $editando ? htmlspecialchars($equipo['proveedor']) : ''; ?>">
                </div>

                <div class="formulario__campo">
                    <label for="descripcion_larga" class="formulario__label">Descripción larga</label>
                    <textarea name="desc_larga" id="descripcion_larga" class="formulario__input"><?php echo $editando ? htmlspecialchars($equipo['desc_larga']) : ''; ?></textarea>
                </div>

                <div class="formulario__campo">
                    <label for="descripcion_corta" class="formulario__label">Descripción corta</label>
                    <input type="text" class="formulario__input" placeholder="Descripción corta" name="desc_corta" value="<?php echo $editando ? htmlspecialchars($equipo['desc_corta']) : ''; ?>" required>
                </div>

                <div class="formulario__campo">
                    <label for="garantia" class="formulario__label">Garantía</label>
                    <input type="text" class="formulario__input" placeholder="Garantía" name="garantia" value="<?php echo $editando ? htmlspecialchars($equipo['garantia']) : ''; ?>">
                </div>

                <!-- Sección para parámetros de Alveógrafo -->
                <div id="parametros-alveografo" class="parametros-section" style="display: none;">
                    <div class="parametros-title">Valores de referencia internacionales - Alveógrafo</div>
                    
                    <?php foreach ($parametros_alveografo as $param): ?>
                    <div class="parametro-row">
                        <div class="parametro-nombre"><?php echo htmlspecialchars($param['nombre']); ?></div>
                        <div class="parametro-inputs">
                            <div>
                                <input type="number" step="0.01" class="parametro-input" 
                                       name="alveografo[<?php echo $param['id_parametro']; ?>][min]" 
                                       value="<?php echo htmlspecialchars($param['lim_Inferior']); ?>" 
                                       placeholder="Mínimo">
                                <div class="parametro-label">Límite inferior</div>
                            </div>
                            <div>
                                <input type="number" step="0.01" class="parametro-input" 
                                       name="alveografo[<?php echo $param['id_parametro']; ?>][max]" 
                                       value="<?php echo htmlspecialchars($param['lim_Superior']); ?>" 
                                       placeholder="Máximo">
                                <div class="parametro-label">Límite superior</div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                
                <!-- Sección para parámetros de Farinógrafo -->
                <div id="parametros-farinografo" class="parametros-section" style="display: none;">
                    <div class="parametros-title">Valores de referencia internacionales - Farinógrafo</div>
                    
                    <?php foreach ($parametros_farinografo as $param): ?>
                    <div class="parametro-row">
                        <div class="parametro-nombre"><?php echo htmlspecialchars($param['nombre']); ?></div>
                        <div class="parametro-inputs">
                            <div>
                                <input type="number" step="0.01" class="parametro-input" 
                                       name="farinografo[<?php echo $param['id_parametro']; ?>][min]" 
                                       value="<?php echo htmlspecialchars($param['lim_Inferior']); ?>" 
                                       placeholder="Mínimo">
                                <div class="parametro-label">Límite inferior</div>
                            </div>
                            <div>
                                <input type="number" step="0.01" class="parametro-input" 
                                       name="farinografo[<?php echo $param['id_parametro']; ?>][max]" 
                                       value="<?php echo htmlspecialchars($param['lim_Superior']); ?>" 
                                       placeholder="Máximo">
                                <div class="parametro-label">Límite superior</div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <div class="formulario__campo">
                    <label for="encargado" class="formulario__label">Encargado del equipo</label>
                    <select class="formulario__input" name="id_responsable" required>
                        <option value="" disabled <?php echo !$editando ? 'selected' : ''; ?>>-- Seleccione un Responsable --</option>
                        <?php foreach($responsables as $resp): ?>
                        <option value="<?= htmlspecialchars($resp['id_usuario']) ?>"
                            <?php echo ($editando && $equipo['id_responsable'] == $resp['id_usuario']) ? 'selected' : ''; ?>>
                            <?= htmlspecialchars($resp['nombre']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="formulario__campo">
                    <label for="ubicacion" class="formulario__label">Ubicación del equipo</label>
                    <input type="text" class="formulario__input" placeholder="Ubicación" name="ubicacion" value="<?php echo $editando ? htmlspecialchars($equipo['ubicacion']) : ''; ?>">
                </div>

                <div class="formulario__campo">
                    <label for="estado" class="formulario__label">Estado</label>
                    <select class="formulario__input" name="estado" required>
                        <option value="Activo" <?php echo ($editando && $equipo['estado'] == 'Activo') ? 'selected' : ''; ?>>Activo</option>
                        <option value="Inactivo" <?php echo ($editando && $equipo['estado'] == 'Inactivo') ? 'selected' : ''; ?>>Inactivo</option>
                        <option value="Baja" <?php echo ($editando && $equipo['estado'] == 'Baja') ? 'selected' : ''; ?>>Baja</option>
                    </select>
                </div>
                
                <div class="formulario__campo">
                    <label for="vencimiento_garantia" class="formulario__label">Vigencia de la garantía</label>
                    <input type="date" class="formulario__input" name="vencimiento_garantia" value="<?php echo $editando && $equipo['vencimiento_garantia'] ? htmlspecialchars($equipo['vencimiento_garantia']) : ''; ?>">
                </div>

                <div class="formulario__campo">
                    <label for="fecha_adquisicion" class="formulario__label">Fecha de adquisición</label>
                    <input type="date" class="formulario__input" name="fecha_adquisicion" value="<?php echo $editando && $equipo['fecha_adquisicion'] ? htmlspecialchars($equipo['fecha_adquisicion']) : ''; ?>">
                </div>

                <input type="submit" class="formulario__submit" value="<?php echo $editando ? 'Guardar cambios' : 'Agregar equipo'; ?>">
            </form>
        </div>
        <?php include '../includes/footer.php'; ?>
    </main>
</body>
</html>