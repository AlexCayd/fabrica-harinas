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
        $siguiente_lote = 'LOTE' . str_pad($num, strlen($matches[1]), '0', STR_PAD_LEFT); // Junta todo el string y le agrega ceros a la izquierda si es que ya llego al limite.
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
    
    if ($tipo_parametros == 'Personalizados') {
        // Usar parámetros personalizados del cliente
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
        
        // Si no hay parámetros personalizados, usar los internacionales
        if (empty($parametros)) {
            return obtenerParametrosInternacionales($pdo);
        }
        
        return $parametros;
    } else {
        // Usar parámetros internacionales
        return obtenerParametrosInternacionales($pdo);
    }
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

// Función para obtener información de un lote existente
function obtenerInfoLote($pdo, $lote) {
    $sql = "SELECT i.id_cliente, ei.id_equipo, e.tipo_equipo 
            FROM Inspeccion i
            JOIN Equipo_Inspeccion ei ON i.id_inspeccion = ei.id_inspeccion
            JOIN Equipos_Laboratorio e ON ei.id_equipo = e.id_equipo
            WHERE i.lote = :lote 
            LIMIT 1";
            
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':lote', $lote);
    $stmt->execute();
    
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// Función para obtener la última secuencia de un lote
function obtenerUltimaSecuencia($pdo, $lote) {
    $sql = "SELECT secuencia FROM Inspeccion WHERE lote = :lote ORDER BY secuencia DESC LIMIT 1";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':lote', $lote);
    $stmt->execute();
    
    $ultima = $stmt->fetchColumn();
    
    if (!$ultima) {
        return 'A';
    }
    
    // Incrementar la secuencia (A -> B, Z -> AA, etc.)
    return ++$ultima;
}

// Función para obtener los resultados de inspección de un lote y equipo específicos
function obtenerResultadosLote($pdo, $lote, $id_equipo) {
    $sql = "SELECT ri.nombre_parametro, ri.valor_obtenido, ri.aprobado 
            FROM Resultado_Inspeccion ri
            JOIN Inspeccion i ON ri.id_inspeccion = i.id_inspeccion
            JOIN Equipo_Inspeccion ei ON i.id_inspeccion = ei.id_inspeccion
            WHERE i.lote = :lote AND ei.id_equipo = :id_equipo
            ORDER BY ri.nombre_parametro";
            
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':lote', $lote);
    $stmt->bindParam(':id_equipo', $id_equipo);
    $stmt->execute();
    
    $resultados = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $resultados[$row['nombre_parametro']] = [
            'valor_obtenido' => $row['valor_obtenido'],
            'aprobado' => $row['aprobado']
        ];
    }
    
    return $resultados;
}

// Parámetros para el alveógrafo y farinógrafo
$parametros_alveografo = [
    ['nombre' => 'Humedad', 'id_parametro' => 'Humedad', 'lim_Inferior' => '', 'lim_Superior' => ''],
    ['nombre' => 'Cenizas', 'id_parametro' => 'Cenizas', 'lim_Inferior' => '', 'lim_Superior' => ''],
    ['nombre' => 'Gluten Humedo', 'id_parametro' => 'Gluten_Humedo', 'lim_Inferior' => '', 'lim_Superior' => ''],
    ['nombre' => 'Gluten Seco', 'id_parametro' => 'Gluten_Seco', 'lim_Inferior' => '', 'lim_Superior' => ''],
    ['nombre' => 'Indice de gluten', 'id_parametro' => 'Indice_Gluten', 'lim_Inferior' => '', 'lim_Superior' => ''],
    ['nombre' => 'Indice de caída', 'id_parametro' => 'Indice_Caida', 'lim_Inferior' => '', 'lim_Superior' => ''],
    ['nombre' => 'Valor P (mm H₂O)', 'id_parametro' => 'Alveograma_P', 'lim_Inferior' => '', 'lim_Superior' => ''],
    ['nombre' => 'Valor L (mm)', 'id_parametro' => 'Alveograma_L', 'lim_Inferior' => '', 'lim_Superior' => ''],
    ['nombre' => 'Valor W (10⁻⁴ J)', 'id_parametro' => 'Alveograma_W', 'lim_Inferior' => '', 'lim_Superior' => ''],
    ['nombre' => 'Relación P/L', 'id_parametro' => 'Alveograma_PL', 'lim_Inferior' => '', 'lim_Superior' => ''],
    ['nombre' => 'Índice de elasticidad (Ie)', 'id_parametro' => 'Alveograma_IE', 'lim_Inferior' => '', 'lim_Superior' => '']
];

$parametros_farinografo = [
    ['nombre' => 'Humedad', 'id_parametro' => 'Humedad', 'lim_Inferior' => '', 'lim_Superior' => ''],
    ['nombre' => 'Cenizas', 'id_parametro' => 'Cenizas', 'lim_Inferior' => '', 'lim_Superior' => ''],
    ['nombre' => 'Gluten Humedo', 'id_parametro' => 'Gluten_Humedo', 'lim_Inferior' => '', 'lim_Superior' => ''],
    ['nombre' => 'Gluten Seco', 'id_parametro' => 'Gluten_Seco', 'lim_Inferior' => '', 'lim_Superior' => ''],
    ['nombre' => 'Indice de gluten', 'id_parametro' => 'Indice_Gluten', 'lim_Inferior' => '', 'lim_Superior' => ''],
    ['nombre' => 'Indice de caída', 'id_parametro' => 'Indice_Caida', 'lim_Inferior' => '', 'lim_Superior' => ''],
    ['nombre' => 'Absorción de agua (%)', 'id_parametro' => 'Farinograma_Absorcion_agua', 'lim_Inferior' => '', 'lim_Superior' => ''],
    ['nombre' => 'Tiempo de desarrollo (min)', 'id_parametro' => 'Farinograma_Tiempo_Desarrollo', 'lim_Inferior' => '', 'lim_Superior' => ''],
    ['nombre' => 'Estabilidad (min)', 'id_parametro' => 'Farinograma_Estabilidad', 'lim_Inferior' => '', 'lim_Superior' => ''],
    ['nombre' => 'Grado Decaimiento', 'id_parametro' => 'Farinograma_Grado_Decaimiento', 'lim_Inferior' => '', 'lim_Superior' => '']
];
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
       .parametro-group {
            margin-bottom: 15px;
            padding: 10px;
            border: 1px solid #eee;
            border-radius: 5px;
            background-color: #f9f9f9;
        }
        
        .parametro-row {
            display: flex;
            align-items: center;
            margin-bottom: 8px;
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
        }

        .parametros-title {
            margin-top: 15px;
            margin-bottom: 10px;
            font-weight: bold;
            color: #333;
        }
        
        .switch-group {
            display: flex;
            gap: 20px;
            margin-bottom: 15px;
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
        }
    </style>
</head>
<body>
    <main class="contenedor hoja">
    <?php include '../includes/header.php'; ?>

        <div class="contenedor__modulo">
            <a href="analisiscalidad.php" class="atras">Ir atrás</a>
            <h2 class="heading"><?php echo $editando ? 'Editar' : 'Agregar'; ?> Análisis de Calidad</h2>
            
            <form action="../config/procesar_analisis.php" class="formulario" method="POST">
                <!-- Campo oculto para identificar si estamos editando -->
                <?php if ($editando): ?>
                <input type="hidden" name="editando" value="1">
                <input type="hidden" name="id_inspeccion" value="<?php echo htmlspecialchars($inspeccion['id_inspeccion']); ?>">
                <?php endif; ?>

                <h3 class="section-title">Información del Lote</h3>

                <div class="switch-group">
                    <div class="switch-option active" data-target="lote-nuevo">Crear Nuevo Lote</div>
                    <div class="switch-option" data-target="lote-existente">Usar Lote Existente</div>
                </div>

                <div class="option-group active" id="lote-nuevo">
                    <div class="formulario__campo">
                        <label for="lote_nuevo" class="formulario__label">Nuevo Lote de Producción</label>
                        <input type="text" class="formulario__input" id="lote_nuevo" name="lote_nuevo" 
                               value="<?php echo htmlspecialchars($siguiente_lote); ?>" readonly>
                        <p style="font-size: 0.8rem; color: #666; margin-top: 5px;">
                            El sistema sugiere este número de lote basado en la secuencia actual
                        </p>
                    </div>
                </div>

                <div class="option-group" id="lote-existente">
                    <div class="formulario__campo">
                        <label for="lote_existente" class="formulario__label">Seleccionar Lote Existente</label>
                        <select class="formulario__input" id="lote_existente" name="lote_existente">
                            <option value="" disabled selected>-- Seleccione un lote --</option>
                            <?php foreach ($lotes_existentes as $lote): ?>
                            <option value="<?php echo htmlspecialchars($lote); ?>">
                                <?php echo htmlspecialchars($lote); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                        <p style="font-size: 0.8rem; color: #666; margin-top: 5px;">
                            Al seleccionar un lote existente, se cargará automáticamente información del cliente y equipo asociados
                        </p>
                    </div>
                </div>

                <!-- Campo oculto para almacenar el lote final -->
                <input type="hidden" name="lote" id="lote_final" value="<?php echo $editando ? htmlspecialchars($analisis['lote']) : $siguiente_lote; ?>">

                <!-- Sección de Origen de Parámetros -->
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
                    <p>Los parámetros se tomarán directamente del equipo seleccionado.</p>
                </div>
                
                <!-- Sección Equipo de Laboratorio -->
                <h3 class="section-title">Equipo de Laboratorio</h3>
                
                <div class="formulario__campo">
                    <label for="tipo_equipo" class="formulario__label">Tipo de Equipo</label>
                    <select class="formulario__input" id="tipo_equipo" name="tipo_equipo" required>
                        <option value="" disabled selected>-- Seleccione tipo de equipo --</option>
                        <option value="Alveógrafo">Alveógrafo</option>
                        <option value="Farinógrafo">Farinógrafo</option>
                    </select>
                </div>

                <div class="formulario__campo">
                    <label for="id_equipo" class="formulario__label">Equipo de Laboratorio</label>
                    <select class="formulario__input" id="id_equipo" name="id_equipo" required>
                        <option value="" disabled selected>-- Seleccione un equipo --</option>
                        <?php foreach ($equipos as $equipo): ?>
                        <option class="equipo-option" 
                                data-tipo="<?php echo htmlspecialchars($equipo['tipo_equipo']); ?>" 
                                value="<?php echo $equipo['id_equipo']; ?>" 
                                style="display: none;">
                            <?php echo htmlspecialchars($equipo['clave']); ?> - <?php echo htmlspecialchars($equipo['marca']); ?> <?php echo htmlspecialchars($equipo['modelo']); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Mensaje de carga para los parámetros -->
                <div id="loading-message" class="loading-message" style="display: none;">
                    Cargando valores de referencia y parámetros...
                </div>

                <div class="formulario__campo">
                    <label for="tipo_equipo" class="formulario__label">Tipo de equipo</label>
                    <select class="formulario__input" name="tipo_equipo" required>
                        <option value="" disabled>-- Seleccione un tipo --</option>
                        <option value="Alveógrafo">Alveógrafo</option>
                        <option value="Farinógrafo">Farinógrafo</option>
                    </select>
                </div>

                <!-- Sección para seleccionar parámetros -->
                <div id="parametros-alveografo" class="parametros-section" style="display: none;">
                    <div class="parametros-title">Valores de referencia internacionales - Alveógrafo</div>
                    
                    <?php foreach ($parametros_alveografo as $param): ?>
                    <div class="parametro-row">
                        <div class="parametro-nombre"><?php echo htmlspecialchars($param['nombre']); ?></div>
                        <div class="parametro-inputs">
                            <div>
                                <input type="number" step="0.01" class="parametro-input min-input" 
                                       name="alveografo[<?php echo $param['id_parametro']; ?>][min]" 
                                       value="<?php echo htmlspecialchars($param['lim_Inferior']); ?>" 
                                       placeholder="Mínimo"
                                       data-parametro="<?php echo htmlspecialchars($param['nombre']); ?>"
                                       >
                                <div class="parametro-label">Límite inferior</div>
                            </div>
                            <div>
                                <input type="number" step="0.01" class="parametro-input max-input" 
                                       name="alveografo[<?php echo $param['id_parametro']; ?>][max]" 
                                       value="<?php echo htmlspecialchars($param['lim_Superior']); ?>" 
                                       placeholder="Máximo"
                                       data-parametro="<?php echo htmlspecialchars($param['nombre']); ?>"
                                       >
                                <div class="parametro-label">Límite superior</div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>

                
                <div id="parametros-farinografo" class="parametros-section" style="display: none;">
                    <div class="parametros-title">Valores de referencia internacionales - Farinógrafo</div>
                    
                    <?php foreach ($parametros_farinografo as $param): ?>
                    <div class="parametro-row">
                        <div class="parametro-nombre"><?php echo htmlspecialchars($param['nombre']); ?></div>
                        <div class="parametro-inputs">
                            <div>
                                <input type="number" step="0.01" class="parametro-input min-input" 
                                       name="farinografo[<?php echo $param['id_parametro']; ?>][min]"  
                                       placeholder="Mínimo"
                                       data-parametro="<?php echo htmlspecialchars($param['nombre']); ?>"
                                       >
                                <div class="parametro-label">Límite inferior</div>
                            </div>
                            <div>
                                <input type="number" step="0.01" class="parametro-input max-input" 
                                       name="farinografo[<?php echo $param['id_parametro']; ?>][max]" 
                                       placeholder="Máximo"
                                       data-parametro="<?php echo htmlspecialchars($param['nombre']); ?>"
                                       >
                                <div class="parametro-label">Límite superior</div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>  

                <div class="formulario__campo">
                    <label for="cliente" class="formulario__label">Cliente</label>
                    <select class="formulario__input" id="id_cliente" name="id_cliente" required>
                        <option value="" disabled <?php echo !$editando ? 'selected' : ''; ?>>-- Seleccione un cliente --</option>
                        <?php foreach ($clientes as $cliente): ?>
                        <option value="<?php echo $cliente['id_cliente']; ?>" 
                                data-parametros="<?php echo htmlspecialchars($cliente['parametros']); ?>"
                                <?php echo ($editando && $inspeccion['id_cliente'] == $cliente['id_cliente']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($cliente['nombre']); ?> (<?php echo htmlspecialchars($cliente['rfc']); ?>)
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="formulario__campo">
                    <label for="lote" class="formulario__label">Lote de producción</label>
                    <input type="text" class="formulario__input" placeholder="Ej. BARBS12024" name="lote" value="<?php echo $editando ? htmlspecialchars($inspeccion['lote']) : ''; ?>" required>
                </div>

                <div class="formulario__campo">
                    <label for="secuencia" class="formulario__label">Secuencia de inspección</label>
                    <input type="text" class="formulario__input" placeholder="Ej. A, B, C..." name="secuencia" maxlength="3" value="<?php echo $editando ? htmlspecialchars($inspeccion['secuencia']) : ''; ?>" required>
                </div>

                <div class="formulario__campo">
                    <label for="tipo_equipo" class="formulario__label">Tipo de Equipo</label>
                    <select class="formulario__input" id="tipo_equipo" name="tipo_equipo" required>
                        <option value="" disabled <?php echo !$editando ? 'selected' : ''; ?>>-- Seleccione tipo de equipo --</option>
                        <option value="Alveógrafo" <?php echo ($editando && count($equipos_seleccionados) > 0 && in_array($equipos_seleccionados[0], array_column($equipos_alveografo, 'id_equipo'))) ? 'selected' : ''; ?>>Alveógrafo</option>
                        <option value="Farinógrafo" <?php echo ($editando && count($equipos_seleccionados) > 0 && in_array($equipos_seleccionados[0], array_column($equipos_farinografo, 'id_equipo'))) ? 'selected' : ''; ?>>Farinógrafo</option>
                    </select>
                </div>

                <div class="formulario__campo">
                    <label for="id_equipo" class="formulario__label">Equipo de Laboratorio</label>
                    <select class="formulario__input" id="id_equipo" name="id_equipo" required>
                        <option value="" disabled <?php echo !$editando ? 'selected' : ''; ?>>-- Seleccione un equipo --</option>
                        <?php foreach ($equipos as $equipo): ?>
                        <option class="equipo-option" 
                                data-tipo="<?php echo htmlspecialchars($equipo['tipo_equipo']); ?>" 
                                value="<?php echo $equipo['id_equipo']; ?>" 
                                <?php echo ($editando && in_array($equipo['id_equipo'], $equipos_seleccionados)) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($equipo['clave']); ?> - <?php echo htmlspecialchars($equipo['marca']); ?> <?php echo htmlspecialchars($equipo['modelo']); ?> (<?php echo htmlspecialchars($equipo['tipo_equipo']); ?>)
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Mensaje de carga para los parámetros -->
                <div id="loading-message" class="loading-message" style="display: none;">
                    Cargando valores de referencia...
                </div>
                
                <!-- Sección para hacer la inspección de los parámetros -->
                <div id="parametros-alveografo" class="parametros-section" style="display: none;">
                    <div class="parametros-title">Inspección de referencia</div>
                    
                    <?php foreach ($parametros_alveografo as $param): ?>
                    <div class="parametro-row">
                        <div class="parametro-nombre"><?php echo htmlspecialchars($param['nombre']); ?></div>
                        <div class="parametro-inputs">
                            <div>
                                <input type="number" step="0.01" class="parametro-input min-input" 
                                       name="alveografo[<?php echo $param['id_parametro']; ?>][min]" 
                                       value="<?php echo htmlspecialchars($param['lim_Inferior']); ?>" 
                                       placeholder="Mínimo"
                                       data-parametro="<?php echo htmlspecialchars($param['nombre']); ?>"
                                       >
                                <div class="parametro-label">Límite inferior</div>
                            </div>
                            <div>
                                <input type="number" step="0.01" class="parametro-input max-input" 
                                       name="alveografo[<?php echo $param['id_parametro']; ?>][max]" 
                                       value="<?php echo htmlspecialchars($param['lim_Superior']); ?>" 
                                       placeholder="Máximo"
                                       data-parametro="<?php echo htmlspecialchars($param['nombre']); ?>"
                                       >
                                <div class="parametro-label">Límite superior</div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>

                
                <div id="parametros-farinografo" class="parametros-section" style="display: none;">
                    <div class="parametros-title">Inspección de referencia</div>
                    
                    <?php foreach ($parametros_farinografo as $param): ?>
                    <div class="parametro-row">
                        <div class="parametro-nombre"><?php echo htmlspecialchars($param['nombre']); ?></div>
                        <div class="parametro-inputs">
                            <div>
                                <input type="number" step="0.01" class="parametro-input min-input" 
                                       name="farinografo[<?php echo $param['id_parametro']; ?>][min]"  
                                       placeholder="Mínimo"
                                       data-parametro="<?php echo htmlspecialchars($param['nombre']); ?>"
                                       >
                                <div class="parametro-label">Límite inferior</div>
                            </div>
                            <div>
                                <input type="number" step="0.01" class="parametro-input max-input" 
                                       name="farinografo[<?php echo $param['id_parametro']; ?>][max]" 
                                       placeholder="Máximo"
                                       data-parametro="<?php echo htmlspecialchars($param['nombre']); ?>"
                                       >
                                <div class="parametro-label">Límite superior</div>
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
</body>
<script>
        document.addEventListener('DOMContentLoaded', function() {
            // Referencias a elementos del DOM
            const tipoEquipoSelector = document.getElementById('tipo_equipo');
            const equipoSelector = document.getElementById('id_equipo');
            const clienteSelector = document.getElementById('id_cliente');
            const seccionAlveografo = document.getElementById('seccion-alveografo');
            const seccionFarinografo = document.getElementById('seccion-farinografo');
            const loadingMessage = document.getElementById('loading-message');

            const form = document.getElementById('analisisForm');
            const tipoEquipoSelector = document.getElementById('tipo_equipo');
            const equipoSelector = document.getElementById('id_equipo');
            const clienteSelector = document.getElementById('id_cliente');
            const seccionAlveografo = document.getElementById('seccion-alveografo');
            const seccionFarinografo = document.getElementById('seccion-farinografo');
            const loadingMessage = document.getElementById('loading-message');
            const switchOptions = document.querySelectorAll('.switch-option');
            const loteFinal = document.getElementById('lote_final');
            const loteNuevo = document.getElementById('lote_nuevo');
            const loteExistente = document.getElementById('lote_existente');
            

            
            // Función para mostrar/ocultar secciones según tipo de equipo
            function actualizarSecciones() {
                const tipoSeleccionado = tipoEquipoSelector.value;
                
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
                
                // Actualizar la lista de equipos según el tipo seleccionado
                const equipos = document.querySelectorAll('.equipo-option');
                
                equipos.forEach(function(equipo) {
                    if (tipoSeleccionado === '' || equipo.dataset.tipo === tipoSeleccionado) {
                        equipo.style.display = '';
                    } else {
                        equipo.style.display = 'none';
                    }
                });
            }
            
            // Función para cargar parámetros de referencia vía AJAX
            function cargarParametrosReferencia() {
                const idEquipo = equipoSelector.value;
                const idCliente = clienteSelector.value;
                
                if (!idEquipo || !idCliente) return;
                
                // Mostrar mensaje de carga
                loadingMessage.style.display = 'block';
                
                // Crear objeto FormData para enviar los datos
                const formData = new FormData();
                formData.append('id_equipo', idEquipo);
                formData.append('id_cliente', idCliente);
                
                // Realizar petición AJAX
                fetch('../config/obtener_parametros.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    // Ocultar mensaje de carga
                    loadingMessage.style.display = 'none';
                    
                    // Actualizar los valores de referencia en la interfaz
                    if (data.tipo_equipo === 'Alveógrafo') {
                        actualizarReferenciasAlveografo(data.parametros);
                    } else if (data.tipo_equipo === 'Farinógrafo') {
                        actualizarReferenciasFarinografo(data.parametros);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    loadingMessage.style.display = 'none';
                });
            }
            
            // Función para actualizar referencias de Alveógrafo en la UI
            function actualizarReferenciasAlveografo(parametros) {
                const campos = ['alveograma_p', 'alveograma_l', 'alveograma_w', 'alveograma_pl', 'alveograma_ie'];
                
                campos.forEach(campo => {
                    const spanRef = document.getElementById(`ref-${campo}`);
                    if (spanRef && parametros[campo]) {
                        spanRef.textContent = `Ref: ${parametros[campo].min} - ${parametros[campo].max}`;
                        
                        // Verificar si el valor actual está dentro del rango
                        const input = document.getElementsByName(campo)[0];
                        if (input && input.value) {
                            const valor = parseFloat(input.value);
                            const min = parseFloat(parametros[campo].min);
                            const max = parseFloat(parametros[campo].max);
                            
                            if (valor >= min && valor <= max) {
                                spanRef.classList.add('valor-dentro');
                                spanRef.classList.remove('valor-fuera');
                            } else {
                                spanRef.classList.add('valor-fuera');
                                spanRef.classList.remove('valor-dentro');
                            }
                        }
                    }
                });
            }
            
            // Función para actualizar referencias de Farinógrafo en la UI
            function actualizarReferenciasFarinografo(parametros) {
                const campos = ['absorcion_agua', 'tiempo_desarrollo', 'estabilidad'];
                
                campos.forEach(campo => {
                    const spanRef = document.getElementById(`ref-${campo}`);
                    if (spanRef && parametros[campo]) {
                        spanRef.textContent = `Ref: ${parametros[campo].min} - ${parametros[campo].max}`;
                        
                        // Verificar si el valor actual está dentro del rango
                        const input = document.getElementsByName(campo)[0];
                        if (input && input.value) {
                            const valor = parseFloat(input.value);
                            const min = parseFloat(parametros[campo].min);
                            const max = parseFloat(parametros[campo].max);
                            
                            if (valor >= min && valor <= max) {
                                spanRef.classList.add('valor-dentro');
                                spanRef.classList.remove('valor-fuera');
                            } else {
                                spanRef.classList.add('valor-fuera');
                                spanRef.classList.remove('valor-dentro');
                            }
                        }
                    }
                });
            }
            
            // Función para verificar valores mientras se ingresan
            function verificarValor(input, min, max) {
                const valor = parseFloat(input.value);
                const referencia = input.nextElementSibling;
                
                if (!isNaN(valor) && min && max) {
                    if (valor >= min && valor <= max) {
                        referencia.classList.add('valor-dentro');
                        referencia.classList.remove('valor-fuera');
                    } else {
                        referencia.classList.add('valor-fuera');
                        referencia.classList.remove('valor-dentro');
                    }
                }
            }
            
            // Asignar eventos a los selectores
            tipoEquipoSelector.addEventListener('change', actualizarSecciones);
            equipoSelector.addEventListener('change', cargarParametrosReferencia);
            clienteSelector.addEventListener('change', cargarParametrosReferencia);
            
            // Asignar eventos a los inputs de valores para verificar en tiempo real
            document.querySelectorAll('.parametro-input').forEach(input => {
                input.addEventListener('input', function() {
                    const refSpan = this.nextElementSibling;
                    const refText = refSpan.textContent;
                    const refMatch = refText.match(/Ref: (\d+\.?\d*) - (\d+\.?\d*)/);
                    
                    if (refMatch) {
                        const min = parseFloat(refMatch[1]);
                        const max = parseFloat(refMatch[2]);
                        verificarValor(this, min, max);
                    }
                });
            });
            
            // Inicializar la vista
            actualizarSecciones();
            
            // Si estamos editando, verificar valores iniciales
            if (document.querySelector('input[name="editando"]')) {
                document.querySelectorAll('.parametro-input').forEach(input => {
                    const refSpan = input.nextElementSibling;
                    const refText = refSpan.textContent;
                    const refMatch = refText.match(/Ref: (\d+\.?\d*) - (\d+\.?\d*)/);
                    
                    if (refMatch && input.value) {
                        const min = parseFloat(refMatch[1]);
                        const max = parseFloat(refMatch[2]);
                        verificarValor(input, min, max);
                    }
                });
            }
        });
    </script>
</html>