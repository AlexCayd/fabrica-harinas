<?php
require '../config/validar_permisos.php';
require '../config/conn.php';

// Variable para determinar si estamos editando un análisis existente
$editando = false;
$analisis = null;
$equipos_seleccionados = [];
$parametros_cargados = false;

// Si se recibe un ID para editar por medio de GET entonces...
if (isset($_GET['id']) && !empty($_GET['id'])) {
    $id_inspeccion = $_GET['id'];
    $editando = true;
    
    // Consulta para encontrar la inspección que se está editando
    $sql_inspeccion = "SELECT * FROM Inspeccion WHERE id_inspeccion = :id_inspeccion";
    $stmt_inspeccion = $pdo->prepare($sql_inspeccion);
    $stmt_inspeccion->bindParam(':id_inspeccion', $id_inspeccion);
    $stmt_inspeccion->execute();
    
    $analisis = $stmt_inspeccion->fetch(PDO::FETCH_ASSOC);
    
    // Si no se encuentra la inspección, redirigir a la lista
    if (!$analisis) {
        $_SESSION['error'] = "No se encontró el análisis especificado.";
        header('Location: analisiscalidad.php');
        exit;
    }
    
    // Consultar los equipos utilizados en esta inspección
    $sql_equipos_inspeccion = "SELECT ei.id_equipo, e.clave, e.marca, e.modelo, e.tipo_equipo 
                              FROM Equipo_Inspeccion ei 
                              JOIN Equipos_Laboratorio e ON ei.id_equipo = e.id_equipo 
                              WHERE ei.id_inspeccion = :id_inspeccion";
    $stmt_equipos = $pdo->prepare($sql_equipos_inspeccion);
    $stmt_equipos->bindParam(':id_inspeccion', $id_inspeccion);
    $stmt_equipos->execute();
    
    $equipos_seleccionados = $stmt_equipos->fetchAll(PDO::FETCH_ASSOC);
    
    // Consultar los resultados de parámetros de esta inspección
    $sql_resultados = "SELECT * FROM Resultado_Inspeccion WHERE id_inspeccion = :id_inspeccion";
    $stmt_resultados = $pdo->prepare($sql_resultados);
    $stmt_resultados->bindParam(':id_inspeccion', $id_inspeccion);
    $stmt_resultados->execute();
    
    $resultados = $stmt_resultados->fetchAll(PDO::FETCH_ASSOC);
    
    // Crear array asociativo para acceder fácilmente a los valores
    $valores_parametros = [];
    foreach ($resultados as $resultado) {
        $valores_parametros[$resultado['nombre_parametro']] = [
            'valor_obtenido' => $resultado['valor_obtenido'],
            'aprobado' => $resultado['aprobado']
        ];
    }
    
    $parametros_cargados = true;
}

// Consulta para obtener todos los equipos activos
$sql_equipos = "SELECT id_equipo, clave, marca, modelo, tipo_equipo FROM Equipos_Laboratorio WHERE estado = 'Activo' ORDER BY tipo_equipo, clave";
$stmt_equipos = $pdo->query($sql_equipos);
$equipos = $stmt_equipos->fetchAll(PDO::FETCH_ASSOC);

// Organizar equipos por tipo
$equipos_alveografo = [];
$equipos_farinografo = [];

foreach ($equipos as $equipo) {
    if ($equipo['tipo_equipo'] == 'Alveógrafo') {
        $equipos_alveografo[] = $equipo;
    } else {
        $equipos_farinografo[] = $equipo;
    }
}

// Consulta para obtener todos los clientes
$sql_clientes = "SELECT id_cliente, nombre, rfc, parametros FROM Clientes WHERE estado = 'Activo' ORDER BY nombre";
$stmt_clientes = $pdo->query($sql_clientes);
$clientes = $stmt_clientes->fetchAll(PDO::FETCH_ASSOC);

// Consulta para obtener todos los lotes existentes
$sql_lotes = "SELECT DISTINCT lote FROM Inspeccion ORDER BY lote DESC";
$stmt_lotes = $pdo->query($sql_lotes);
$lotes_existentes = $stmt_lotes->fetchAll(PDO::FETCH_COLUMN);

// Determinar último lote para sugerir el siguiente
$ultimo_lote = '';
$siguiente_lote = '';
if (!empty($lotes_existentes)) {
    $ultimo_lote = $lotes_existentes[0];
    
    // Extraer parte numérica y generar el siguiente
    if (preg_match('/LOTE(\d+)/', $ultimo_lote, $matches)) {
        $num = intval($matches[1]) + 1;
        $siguiente_lote = 'LOTE' . str_pad($num, strlen($matches[1]), '0', STR_PAD_LEFT);
    } else { 
        $siguiente_lote = 'LOTE001';
    }
} else {
    $siguiente_lote = 'LOTE001';
}

// Función para obtener los parámetros de un cliente específico
function obtenerParametrosCliente($pdo, $id_cliente) {
    // Primero, verificar qué tipo de parámetros usa este cliente
    $sql_cliente = "SELECT parametros FROM Clientes WHERE id_cliente = :id_cliente";
    $stmt_cliente = $pdo->prepare($sql_cliente);
    $stmt_cliente->bindParam(':id_cliente', $id_cliente, PDO::PARAM_INT);
    $stmt_cliente->execute();
    $tipo_parametros = $stmt_cliente->fetchColumn();
    
    // Consulta base
    $sql = "SELECT nombre_parametro, lim_Inferior, lim_Superior 
            FROM Parametros 
            WHERE id_cliente = :id_cliente";
            
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':id_cliente', $id_cliente, PDO::PARAM_INT);
    $stmt->execute();
    
    $parametros = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $parametros[$row['nombre_parametro']] = [
            'min' => $row['lim_Inferior'],
            'max' => $row['lim_Superior']
        ];
    }
    
    return $parametros;
}

// Función para obtener los parámetros de un equipo específico
function obtenerParametrosEquipo($pdo, $id_equipo) {
    $sql = "SELECT nombre_parametro, lim_Inferior, lim_Superior 
            FROM Parametros 
            WHERE id_equipo = :id_equipo";
    
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':id_equipo', $id_equipo, PDO::PARAM_INT);
    $stmt->execute();
    
    $resultado = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $resultado[$row['nombre_parametro']] = [
            'min' => $row['lim_Inferior'],
            'max' => $row['lim_Superior']
        ];
    }
    
    return $resultado;
}

// INSPECCIONES para el alveógrafo y farinógrafo
$parametros_alveografo = [
    ['nombre' => 'Humedad', 'id_parametro' => 'Humedad', 'valor_obtenido' => ''],
    ['nombre' => 'Cenizas', 'id_parametro' => 'Cenizas', 'valor_obtenido' => ''],
    ['nombre' => 'Gluten Humedo', 'id_parametro' => 'Gluten_Humedo', 'valor_obtenido' => ''],
    ['nombre' => 'Gluten Seco', 'id_parametro' => 'Gluten_Seco', 'valor_obtenido' => ''],
    ['nombre' => 'Indice de gluten', 'id_parametro' => 'Indice_Gluten', 'valor_obtenido' => ''],
    ['nombre' => 'Indice de caída', 'id_parametro' => 'Indice_Caida', 'valor_obtenido' => ''],
    ['nombre' => 'Valor P (mm H₂O)', 'id_parametro' => 'Alveograma_P', 'valor_obtenido' => ''],
    ['nombre' => 'Valor L (mm)', 'id_parametro' => 'Alveograma_L', 'valor_obtenido' => ''],
    ['nombre' => 'Valor W (10⁻⁴ J)', 'id_parametro' => 'Alveograma_W', 'valor_obtenido' => ''],
    ['nombre' => 'Relación P/L', 'id_parametro' => 'Alveograma_PL', 'valor_obtenido' => ''],
    ['nombre' => 'Índice de elasticidad (Ie)', 'id_parametro' => 'Alveograma_IE', 'valor_obtenido' => '']
    
];

$parametros_farinografo = [
    ['nombre' => 'Humedad', 'id_parametro' => 'Humedad', 'valor_obtenido' => ''],
    ['nombre' => 'Cenizas', 'id_parametro' => 'Cenizas', 'valor_obtenido' => ''],
    ['nombre' => 'Gluten Humedo', 'id_parametro' => 'Gluten_Humedo', 'valor_obtenido' => ''],
    ['nombre' => 'Gluten Seco', 'id_parametro' => 'Gluten_Seco', 'valor_obtenido' => ''],
    ['nombre' => 'Indice de gluten', 'id_parametro' => 'Indice_Gluten', 'valor_obtenido' => ''],
    ['nombre' => 'Indice de caída', 'id_parametro' => 'Indice_Caida', 'valor_obtenido' => ''],
    ['nombre' => 'Absorción de agua (%)', 'id_parametro' => 'Farinograma_Absorcion_Agua', 'valor_obtenido' => ''],
    ['nombre' => 'Tiempo de desarrollo (min)', 'id_parametro' => 'Farinograma_Tiempo_Desarrollo', 'valor_obtenido' => ''],
    ['nombre' => 'Estabilidad (min)', 'id_parametro' => 'Farinograma_Estabilidad', 'valor_obtenido' => ''],
    ['nombre' => 'Grado Decaimiento', 'id_parametro' => 'Farinograma_Grado_Decaimiento', 'valor_obtenido' => '']
];

// Cargar valores existentes si estamos editando
if ($editando && $parametros_cargados) {
    foreach ($parametros_alveografo as &$param) {
        if (isset($valores_parametros[$param['id_parametro']])) {
            $param['valor_obtenido'] = $valores_parametros[$param['id_parametro']]['valor_obtenido'];
        }
    }
    
    foreach ($parametros_farinografo as &$param) {
        if (isset($valores_parametros[$param['id_parametro']])) {
            $param['valor_obtenido'] = $valores_parametros[$param['id_parametro']]['valor_obtenido'];
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FHE | <?php echo $editando ? 'Editar' : 'Agregar'; ?> Análisis de Calidad</title>
    <link rel="stylesheet" href="../styles.css">
    <link rel="stylesheet" href="../css/menu.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        .formulario {
            max-width: 60%;
            margin: 0 auto;
            display: block; /* Cambiamos de grid a block para flujo vertical */
        }
        
        .formulario__campo {
            margin-bottom: 20px;
            width: 100%; /* Ocupa todo el ancho disponible */
        }
        
        .parametro-group {
            margin-bottom: 15px;
            padding: 10px;
            border: 1px solid #eee;
            border-radius: 5px;
            background-color: #f9f9f9;
            width: 100%; /* Ocupa todo el ancho */
        }
        
        .parametro-row {
            display: flex;
            align-items: center;
            margin-bottom: 8px;
            width: 100%; /* Ocupa todo el ancho */
        }
        
        .parametro-label {
            flex: 0 0 35%;
            font-weight: 600;
        }
        
        .parametro-input {
            flex: 0 0 30%;
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        
        .parametro-referencia {
            flex: 0 0 35%;
            font-size: 13px;
            color: #666;
            padding-left: 10px;
        }
        
        .valor-dentro {
            color: green;
            font-weight: bold;
        }
        
        .valor-fuera {
            color: red;
            font-weight: bold;
        }
        
        .loading-message {
            margin: 20px 0;
            padding: 10px;
            background-color: #f8f9fa;
            border: 1px solid #ddd;
            border-radius: 4px;
            text-align: center;
            font-style: italic;
            color: #666;
            width: 100%; /* Ocupa todo el ancho */
        }
        
        .switch-group {
            display: flex;
            gap: 20px;
            margin-bottom: 15px;
            width: 100%; /* Ocupa todo el ancho */
        }
        
        .switch-option {
            flex: 1;
            padding: 10px;
            border: 2px solid #ddd;
            border-radius: 5px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .switch-option.active {
            border-color: #4c3325;
            background-color: #EBDED0;
            font-weight: bold;
        }
        
        .option-group {
            margin-bottom: 20px;
            display: none;
            width: 100%; /* Ocupa todo el ancho */
        }
        
        .option-group.active {
            display: block;
        }
        
        .input-error {
            border-color: #ff4d4d !important;
        }
        
        .section-title {
            border-bottom: 2px solid #4c3325;
            padding-bottom: 5px;
            margin-top: 20px;
            margin-bottom: 15px;
            color: #4c3325;
            width: 100%; /* Ocupa todo el ancho */
        }
        
        /* Asegurarnos que los elementos de formulario ocupen todo el ancho */
        .formulario__input {
            width: 100%;
            box-sizing: border-box;
        }
        
        /* Estilo para el botón de submit */
        .formulario__submit {
            width: 100%;
            padding: 12px;
            margin-top: 20px;
        }
        
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
            margin-bottom: 10px;
            align-items: center;
        }
        
        .parametro-nombre {
            flex: 1;
            font-weight: bold;
        }
        
        .parametro-inputs {
            flex: 1;
            display: flex;
            gap: 10px;
        }
        
        .parametro-input {
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-family: inherit;
        }
        
        .parametro-label {
            font-size: 12px;
            color: #666;
        }
    </style>
</head>
<body>
    <main class="contenedor hoja">
    <?php include '../includes/header.php'; ?>

        <div class="contenedor__modulo">
            <a href="analisiscalidad.php" class="atras">Ir atrás</a>
            <h2 class="heading"><?php echo $editando ? 'Editar' : 'Agregar'; ?> Análisis de Calidad</h2>
            
            <form action="../config/procesar_analisis.php" class="formulario" method="POST" id="analisisForm" novalidate>
                <!-- Campo oculto para identificar si estamos editando -->
                <?php if ($editando): ?>
                <input type="hidden" name="editando" value="1">
                <input type="hidden" name="id_inspeccion" value="<?php echo htmlspecialchars($analisis['id_inspeccion']); ?>">
                <?php endif; ?>

                <!-- Sección Lote -->
                <h3 class="section-title">Información del Lote</h3>
                
                <div class="switch-group">
                    <div class="switch-option <?php echo !$editando ? 'active' : ''; ?>" data-target="lote-nuevo">Crear Nuevo Lote</div>
                    <div class="switch-option <?php echo $editando ? 'active' : ''; ?>" data-target="lote-existente">Usar Lote Existente</div>
                </div>
                
                <div class="option-group <?php echo !$editando ? 'active' : ''; ?>" id="lote-nuevo">
                    <div class="formulario__campo">
                        <label for="lote_nuevo" class="formulario__label">Nuevo Lote de Producción</label>
                        <input type="text" class="formulario__input" id="lote_nuevo" name="lote_nuevo" 
                               value="<?php echo htmlspecialchars($siguiente_lote); ?>" readonly>
                        <p style="font-size: 0.8rem; color: #666; margin-top: 5px;">
                            El sistema sugiere este número de lote basado en la secuencia actual
                        </p>
                    </div>
                </div>
                
                <div class="option-group <?php echo $editando ? 'active' : ''; ?>" id="lote-existente">
                    <div class="formulario__campo">
                        <label for="lote_existente" class="formulario__label">Seleccionar Lote Existente</label>
                        <select class="formulario__input" id="lote_existente" name="lote_existente">
                            <option value="" disabled <?php echo !$editando ? 'selected' : ''; ?>>-- Seleccione un lote --</option>
                            <?php foreach ($lotes_existentes as $lote): ?>
                            <option value="<?php echo htmlspecialchars($lote); ?>" 
                                    <?php echo ($editando && $analisis['lote'] == $lote) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($lote); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>                        
                    </div>
                </div>

                <!-- Sección Equipo de Laboratorio -->
                <h3 class="section-title">Equipo de Laboratorio</h3>
                
                <div class="formulario__campo">
                    <label for="tipo_equipo" class="formulario__label">Tipo de Equipo</label>
                    <select class="formulario__input" id="tipo_equipo" name="tipo_equipo">
                        <option value="" disabled selected>-- Seleccione tipo de equipo --</option>
                        <option value="Alveógrafo" <?php echo ($editando && isset($equipos_seleccionados[0]) && $equipos_seleccionados[0]['tipo_equipo'] == 'Alveógrafo') ? 'selected' : ''; ?>>Alveógrafo</option>
                        <option value="Farinógrafo" <?php echo ($editando && isset($equipos_seleccionados[0]) && $equipos_seleccionados[0]['tipo_equipo'] == 'Farinógrafo') ? 'selected' : ''; ?>>Farinógrafo</option>
                    </select>
                </div>
                
                <!-- Campo oculto para almacenar el lote final -->
                <input type="hidden" name="lote" id="lote_final" value="<?php echo $editando ? htmlspecialchars($analisis['lote']) : $siguiente_lote; ?>">
                
                <!-- Sección Origen de Parámetros -->
                <h3 class="section-title">Origen de Parámetros</h3>
                
                <div class="switch-group">
                    <div class="switch-option active" data-target="cliente-group">Parámetros por Cliente</div>
                    <div class="switch-option" data-target="equipo-group">Parámetros por Equipo</div>
                </div>
                
                <div class="option-group active" id="cliente-group">
                    <div class="formulario__campo">
                        <label for="id_cliente" class="formulario__label">Cliente</label>
                        <select class="formulario__input" id="id_cliente" name="id_cliente">
                            <option value="" disabled selected>-- Seleccione un cliente --</option>
                            <?php foreach ($clientes as $cliente): ?>
                            <option value="<?php echo $cliente['id_cliente']; ?>" 
                                    data-parametros="<?php echo htmlspecialchars($cliente['parametros']); ?>"
                                    <?php echo ($editando && isset($analisis['id_cliente']) && $analisis['id_cliente'] == $cliente['id_cliente']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cliente['nombre']); ?> (<?php echo htmlspecialchars($cliente['rfc']); ?>)
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <div class="option-group" id="equipo-group">                              
                    <div class="formulario__campo">
                        <label for="id_equipo" class="formulario__label">Equipo de Laboratorio</label>
                        <select class="formulario__input" id="id_equipo" name="id_equipo">
                            <option value="" disabled selected>-- Seleccione un equipo --</option>
                            <?php foreach ($equipos as $equipo): ?>
                            <option class="equipo-option" 
                                    data-tipo="<?php echo htmlspecialchars($equipo['tipo_equipo']); ?>" 
                                    value="<?php echo $equipo['id_equipo']; ?>" 
                                    style="display: none;"
                                    <?php echo ($editando && isset($equipos_seleccionados[0]) && $equipos_seleccionados[0]['id_equipo'] == $equipo['id_equipo']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($equipo['clave']); ?> - <?php echo htmlspecialchars($equipo['marca']); ?> <?php echo htmlspecialchars($equipo['modelo']); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <!-- Sección Parámetros de Medición -->
                <h3 class="section-title">Parámetros de Medición</h3>
                
                <div id="seccion-alveografo" class="parametros-section" style="display: none;">
                    <h3 class="parametros-title">Inspección para Alveógrafo</h3>
                    <?php foreach ($parametros_alveografo as $param): ?>
                    <div class="parametro-row">
                        <div class="parametro-nombre"><?php echo htmlspecialchars($param['nombre']); ?></div>
                        <div class="parametro-inputs">
                            <div>
                                <input type="number" step="0.01" class="parametro-input" 
                                       name="alveografo[<?php echo $param['id_parametro']; ?>][valor]" 
                                       value="<?php echo htmlspecialchars($param['valor_obtenido']); ?>" 
                                       placeholder="Valor"
                                       data-parametro="<?php echo htmlspecialchars($param['nombre']); ?>">
                                
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <!-- Sección para Farinografo -->
                <div id="seccion-farinografo" class="parametros-section" style="display: none;">
                    <div class="parametros-title">Inspección para Farinógrafo</div>
                    <?php foreach ($parametros_farinografo as $param): ?>
                    <div class="parametro-row">
                        <div class="parametro-nombre"><?php echo htmlspecialchars($param['nombre']); ?></div>
                        <div class="parametro-inputs">
                            <div>
                                <input type="number" step="0.01" class="parametro-input" 
                                       name="farinografo[<?php echo $param['id_parametro']; ?>][valor]"  
                                       placeholder="Valor"
                                       value="<?php echo htmlspecialchars($param['valor_obtenido']); ?>"
                                       data-parametro="<?php echo htmlspecialchars($param['nombre']); ?>">
                                
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <input type="submit" class="formulario__submit" value="<?php echo $editando ? 'Guardar cambios' : 'Registrar análisis'; ?>">
            </form>
        </div>
        <?php include '../includes/footer.php'; ?>
    </main>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Referencias a elementos del DOM
            const form = document.getElementById('analisisForm');
            const tipoEquipoSelector = document.getElementById('tipo_equipo');
            const equipoSelector = document.getElementById('id_equipo');
            const clienteSelector = document.getElementById('id_cliente');
            const seccionAlveografo = document.getElementById('seccion-alveografo');
            const seccionFarinografo = document.getElementById('seccion-farinografo');
            const switchOptions = document.querySelectorAll('.switch-option');
            const loteFinal = document.getElementById('lote_final');
            const loteNuevo = document.getElementById('lote_nuevo');
            const loteExistente = document.getElementById('lote_existente');
            
            // Variable para rastrear el origen actual de parámetros (cliente o equipo)
            let parametrosPorCliente = true;
            
            // Inicializar selectores de opciones para cambio entre tabs
            switchOptions.forEach(option => {
                option.addEventListener('click', function() {
                    // Quitar clase activa de todos los del mismo grupo
                    const parent = this.parentElement;
                    parent.querySelectorAll('.switch-option').forEach(opt => opt.classList.remove('active'));
                    
                    // Agregar clase activa al seleccionado
                    this.classList.add('active');
                    
                    // Activar/desactivar bloques correspondientes
                    const targetId = this.dataset.target;
                    
                    if (parent.querySelector('[data-target="lote-nuevo"]') && parent.querySelector('[data-target="lote-existente"]')) {
                        // Toggle entre lotes nuevo y existente
                        document.getElementById('lote-nuevo').classList.toggle('active', targetId === 'lote-nuevo');
                        document.getElementById('lote-existente').classList.toggle('active', targetId === 'lote-existente');
                        
                        // Actualizar valor del lote final
                        if (targetId === 'lote-nuevo') {
                            loteFinal.value = loteNuevo.value;
                        } else if (targetId === 'lote-existente' && loteExistente.value) {
                            loteFinal.value = loteExistente.value;
                        }
                    } else {
                        // Toggle entre origen de parámetros (cliente o equipo)
                        document.getElementById('cliente-group').classList.toggle('active', targetId === 'cliente-group');
                        document.getElementById('equipo-group').classList.toggle('active', targetId === 'equipo-group');
                        
                        // Actualizar variable de seguimiento
                        parametrosPorCliente = targetId === 'cliente-group';
                    }
                });
            });
            
            // Función para mostrar/ocultar secciones según tipo de equipo
            function actualizarSecciones() {
                const tipoSeleccionado = tipoEquipoSelector.value;
                
                // Filtrar opciones de equipos según el tipo seleccionado
                const opciones = document.querySelectorAll('.equipo-option');
                opciones.forEach(opcion => {
                    if (tipoSeleccionado === opcion.dataset.tipo) {
                        opcion.style.display = '';
                    } else {
                        opcion.style.display = 'none';
                    }
                });
                
                // Resetear selección de equipo si cambiamos el tipo y no coincide
                if (equipoSelector.selectedIndex > 0) {
                    const equipoOption = equipoSelector.options[equipoSelector.selectedIndex];
                    if (equipoOption.dataset.tipo !== tipoSeleccionado) {
                        equipoSelector.selectedIndex = 0;
                    }
                }
                
                // Mostrar sección de parámetros correspondiente
                seccionAlveografo.style.display = tipoSeleccionado === 'Alveógrafo' ? 'block' : 'none';
                seccionFarinografo.style.display = tipoSeleccionado === 'Farinógrafo' ? 'block' : 'none';
            }
            
            // Función para obtener y mostrar parámetros
            function cargarParametros() {
                // Obtener valores seleccionados
                const idEquipo = equipoSelector.value;
                if (!idEquipo) return;
                
                const origenCliente = document.querySelector('.switch-option.active[data-target="cliente-group"]') !== null;
                const idCliente = origenCliente ? clienteSelector.value : null;
                
                if (origenCliente && !idCliente) return;
                
                // Consulta directa PHP (no AJAX)
                // Aquí solo actualizamos la UI con datos simulados para pruebas
                const tipoEquipo = tipoEquipoSelector.value;
                
                if (tipoEquipo === 'Alveógrafo') {
                    // Valores de ejemplo para Alveógrafo
                    actualizarReferencia('alveograma_p', {min: 80, max: 100});
                    actualizarReferencia('alveograma_l', {min: 100, max: 120});
                    actualizarReferencia('alveograma_w', {min: 250, max: 300});
                    actualizarReferencia('alveograma_pl', {min: 0.4, max: 0.6});
                    actualizarReferencia('alveograma_ie', {min: 0.8, max: 1.2});
                } else if (tipoEquipo === 'Farinógrafo') {
                    // Valores de ejemplo para Farinógrafo
                    actualizarReferencia('farinograma_absorcion_agua', {min: 58, max: 62});
                    actualizarReferencia('farinograma_tiempo_desarrollo', {min: 1.5, max: 2.5});
                    actualizarReferencia('farinograma_estabilidad', {min: 8, max: 10});
                    actualizarReferencia('farinograma_grado_decaimiento', {min: 60, max: 80});
                }
                
                // Nota: En una implementación real, aquí harías una consulta a la base de datos
                // para obtener los valores reales de los parámetros según el cliente/equipo
            }
            
            // Función para actualizar una referencia específica
            function actualizarReferencia(id, parametro) {
                if (!parametro) return;
                
                const input = document.getElementById(id);
                const refSpan = document.getElementById(`ref-${id}`);
                
                if (input && refSpan) {
                    refSpan.textContent = `Ref: ${parametro.min} - ${parametro.max}`;
                    
                    // Verificar si el valor actual está dentro del rango
                    if (input.value) {
                        const valor = parseFloat(input.value);
                        const min = parseFloat(parametro.min);
                        const max = parseFloat(parametro.max);
                        
                        if (valor >= min && valor <= max) {
                            refSpan.classList.add('valor-dentro');
                            refSpan.classList.remove('valor-fuera');
                        } else {
                            refSpan.classList.add('valor-fuera');
                            refSpan.classList.remove('valor-dentro');
                        }
                    }
                    
                    // Agregar evento para verificación en tiempo real
                    input.addEventListener('input', function() {
                        const valor = parseFloat(this.value);
                        if (!isNaN(valor)) {
                            const min = parseFloat(parametro.min);
                            const max = parseFloat(parametro.max);
                            
                            if (valor >= min && valor <= max) {
                                refSpan.classList.add('valor-dentro');
                                refSpan.classList.remove('valor-fuera');
                            } else {
                                refSpan.classList.add('valor-fuera');
                                refSpan.classList.remove('valor-dentro');
                            }
                        }
                    });
                }
            }
            
            // Función para cargar información de un lote existente
            function cargarInfoLote() {
                const lote = loteExistente.value;
                if (!lote) return;
                
                // Aquí simularemos la carga de datos del lote
                // En una implementación real, harías una consulta a la base de datos
                
                // Simular que encontramos un lote con un cliente y un equipo específico
                // Nota: Esto es solo para demostración
                Swal.fire({
                    title: 'Lote seleccionado',
                    text: `Has seleccionado el lote ${lote}. En una implementación real, se cargarían los datos desde la base de datos.`,
                    icon: 'info'
                });
                
                // Actualizar valor final del lote
                loteFinal.value = lote;
            }
            
            // Validación del formulario antes de enviar
            form.addEventListener('submit', function(e) {
                e.preventDefault();

                // Validación personalizada
                let isValid = true;
                let errorMessage = '';

                // 1. Verificar selección de lote
                const usandoLoteNuevo = document.querySelector('.switch-option.active[data-target="lote-nuevo"]') !== null;
                const usandoLoteExistente = document.querySelector('.switch-option.active[data-target="lote-existente"]') !== null;
                
                if (usandoLoteExistente && !loteExistente.value) {
                    isValid = false;
                    errorMessage = 'Por favor seleccione un lote existente.';
                    loteExistente.classList.add('input-error');
                } else {
                    loteExistente.classList.remove('input-error');
                }

                // 2. Verificar que hay un tipo de equipo seleccionado
                if (!tipoEquipoSelector.value) {
                    isValid = false;
                    errorMessage = errorMessage || 'Por favor seleccione un tipo de equipo.';
                    tipoEquipoSelector.classList.add('input-error');
                } else {
                    tipoEquipoSelector.classList.remove('input-error');
                }

                // 3. Verificar origen de parámetros
                parametrosPorCliente = document.querySelector('.switch-option.active[data-target="cliente-group"]') !== null;
                
                if (parametrosPorCliente) {
                    // 3.1 Si usamos parámetros por cliente, validar que hay un cliente seleccionado
                    if (!clienteSelector.value) {
                        isValid = false;
                        errorMessage = errorMessage || 'Por favor seleccione un cliente.';
                        clienteSelector.classList.add('input-error');
                    } else {
                        clienteSelector.classList.remove('input-error');
                    }
                } else {
                    // 3.2 Si usamos parámetros por equipo, validar que hay un equipo seleccionado
                    if (!equipoSelector.value) {
                        isValid = false;
                        errorMessage = errorMessage || 'Por favor seleccione un equipo de laboratorio.';
                        equipoSelector.classList.add('input-error');
                    } else {
                        equipoSelector.classList.remove('input-error');
                    }
                }

                // 4. Verificar que hay parámetros visibles y que tienen valores
                let seccionesParametrosVisibles = false;
                let parametrosIncompletos = false;
                
                if (seccionAlveografo.style.display !== 'none') {
                    seccionesParametrosVisibles = true;
                    const inputs = seccionAlveografo.querySelectorAll('input[type="number"]');
                    inputs.forEach(input => {
                        if (!input.value) {
                            parametrosIncompletos = true;
                            input.classList.add('input-error');
                        } else {
                            input.classList.remove('input-error');
                        }
                    });
                }
                
                if (seccionFarinografo.style.display !== 'none') {
                    seccionesParametrosVisibles = true;
                    const inputs = seccionFarinografo.querySelectorAll('input[type="number"]');
                    inputs.forEach(input => {
                        if (!input.value) {
                            parametrosIncompletos = true;
                            input.classList.add('input-error');
                        } else {
                            input.classList.remove('input-error');
                        }
                    });
                }
                
                if (!seccionesParametrosVisibles) {
                    isValid = false;
                    errorMessage = errorMessage || 'No hay sección de parámetros visible. Por favor, seleccione un tipo de equipo válido.';
                } else if (parametrosIncompletos) {
                    isValid = false;
                    errorMessage = errorMessage || 'Por favor, complete todos los valores de parámetros.';
                }

                // 5. Mostrar errores o continuar con el envío
                if (!isValid) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error de validación',
                        text: errorMessage
                    });
                    return;
                }

                // 6. Confirmación antes de enviar
                Swal.fire({
                    title: '¿Confirmar registro?',
                    text: 'Se procederá a registrar el análisis con los parámetros ingresados.',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#4c3325',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Confirmar',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            });
            
            // Configurar eventos
            tipoEquipoSelector.addEventListener('change', function() {
                actualizarSecciones();
                cargarParametros();
            });
            equipoSelector.addEventListener('change', cargarParametros);
            clienteSelector.addEventListener('change', cargarParametros);
            loteExistente.addEventListener('change', function() {
                cargarInfoLote();
                // Actualizar lote final cuando cambia la selección
                loteFinal.value = this.value;
            });
            
            // Inicializar la vista
            actualizarSecciones();
            
            // Si estamos editando y ya hay tipo de equipo, actualizar secciones al cargar
            if (tipoEquipoSelector.value) {
                actualizarSecciones();
                
                // Si hay equipo seleccionado, mostrar parámetros correspondientes
                if (equipoSelector.value || clienteSelector.value) {
                    cargarParametros();
                }
            }
        });
    </script>
</body>
</html>