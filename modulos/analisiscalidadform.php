<?php
require '../config/validar_permisos.php';
require '../config/conn.php';

// Variable para determinar si estamos editando un análisis existente
$editando = false;
$analisis = null;
$equipos_seleccionados = [];
$parametros_cargados = false;

// Verificar si hay parámetros cargados en sesión
$parametros_sesion = isset($_SESSION['parametros_consulta']) ? $_SESSION['parametros_consulta'] : null;

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

// INSPECCIONES para el alveógrafo y farinógrafo con valores iniciales vacíos
$parametros_alveografo = [
    ['nombre' => 'Humedad', 'id_parametro' => 'Humedad', 'valor_obtenido' => '', 'lim_Inferior' => '', 'lim_Superior' => ''],
    ['nombre' => 'Cenizas', 'id_parametro' => 'Cenizas', 'valor_obtenido' => '', 'lim_Inferior' => '', 'lim_Superior' => ''],
    ['nombre' => 'Gluten Humedo', 'id_parametro' => 'Gluten_Humedo', 'valor_obtenido' => '', 'lim_Inferior' => '', 'lim_Superior' => ''],
    ['nombre' => 'Gluten Seco', 'id_parametro' => 'Gluten_Seco', 'valor_obtenido' => '', 'lim_Inferior' => '', 'lim_Superior' => ''],
    ['nombre' => 'Indice de gluten', 'id_parametro' => 'Indice_Gluten', 'valor_obtenido' => '', 'lim_Inferior' => '', 'lim_Superior' => ''],
    ['nombre' => 'Indice de caída', 'id_parametro' => 'Indice_Caida', 'valor_obtenido' => '', 'lim_Inferior' => '', 'lim_Superior' => ''],
    ['nombre' => 'Valor P (mm H₂O)', 'id_parametro' => 'Alveograma_P', 'valor_obtenido' => '', 'lim_Inferior' => '', 'lim_Superior' => ''],
    ['nombre' => 'Valor L (mm)', 'id_parametro' => 'Alveograma_L', 'valor_obtenido' => '', 'lim_Inferior' => '', 'lim_Superior' => ''],
    ['nombre' => 'Valor W (10⁻⁴ J)', 'id_parametro' => 'Alveograma_W', 'valor_obtenido' => '', 'lim_Inferior' => '', 'lim_Superior' => ''],
    ['nombre' => 'Relación P/L', 'id_parametro' => 'Alveograma_PL', 'valor_obtenido' => '', 'lim_Inferior' => '', 'lim_Superior' => ''],
    ['nombre' => 'Índice de elasticidad (Ie)', 'id_parametro' => 'Alveograma_IE', 'valor_obtenido' => '', 'lim_Inferior' => '', 'lim_Superior' => '']
];

$parametros_farinografo = [
    ['nombre' => 'Humedad', 'id_parametro' => 'Humedad', 'valor_obtenido' => '', 'lim_Inferior' => '', 'lim_Superior' => ''],
    ['nombre' => 'Cenizas', 'id_parametro' => 'Cenizas', 'valor_obtenido' => '', 'lim_Inferior' => '', 'lim_Superior' => ''],
    ['nombre' => 'Gluten Humedo', 'id_parametro' => 'Gluten_Humedo', 'valor_obtenido' => '', 'lim_Inferior' => '', 'lim_Superior' => ''],
    ['nombre' => 'Gluten Seco', 'id_parametro' => 'Gluten_Seco', 'valor_obtenido' => '', 'lim_Inferior' => '', 'lim_Superior' => ''],
    ['nombre' => 'Indice de gluten', 'id_parametro' => 'Indice_Gluten', 'valor_obtenido' => '', 'lim_Inferior' => '', 'lim_Superior' => ''],
    ['nombre' => 'Indice de caída', 'id_parametro' => 'Indice_Caida', 'valor_obtenido' => '', 'lim_Inferior' => '', 'lim_Superior' => ''],
    ['nombre' => 'Absorción de agua (%)', 'id_parametro' => 'Farinograma_Absorcion_Agua', 'valor_obtenido' => '', 'lim_Inferior' => '', 'lim_Superior' => ''],
    ['nombre' => 'Tiempo de desarrollo (min)', 'id_parametro' => 'Farinograma_Tiempo_Desarrollo', 'valor_obtenido' => '', 'lim_Inferior' => '', 'lim_Superior' => ''],
    ['nombre' => 'Estabilidad (min)', 'id_parametro' => 'Farinograma_Estabilidad', 'valor_obtenido' => '', 'lim_Inferior' => '', 'lim_Superior' => ''],
    ['nombre' => 'Grado Decaimiento', 'id_parametro' => 'Farinograma_Grado_Decaimiento', 'valor_obtenido' => '', 'lim_Inferior' => '', 'lim_Superior' => '']
];

// Cargar valores existentes si estamos editando o desde la sesión
if ($editando && $parametros_cargados) {
    // Cargar de la edición
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

// Si hay resultados en la sesión, actualizar los límites de referencia
if ($parametros_sesion !== null) {
    $tipo_equipo_sesion = $parametros_sesion['tipo_equipo'];
    $params_db = $parametros_sesion['parametros'];
    
    // Actualizar los límites para cada parámetro
    foreach ($params_db as $param_db) {
        $nombre_param = $param_db['nombre_parametro'];
        
        // Actualizar en alveográfo
        foreach ($parametros_alveografo as &$param) {
            if ($param['id_parametro'] === $nombre_param) {
                $param['lim_Inferior'] = $param_db['lim_Inferior'];
                $param['lim_Superior'] = $param_db['lim_Superior'];
            }
        }
        
        // Actualizar en farinógrafo
        foreach ($parametros_farinografo as &$param) {
            if ($param['id_parametro'] === $nombre_param) {
                $param['lim_Inferior'] = $param_db['lim_Inferior'];
                $param['lim_Superior'] = $param_db['lim_Superior'];
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
            flex: 2;
            display: flex;
            gap: 10px;
        }
        
        .parametro-input {
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-family: inherit;
            flex: 1;
        }
        
        .parametro-label {
            font-size: 12px;
            color: #666;
            text-align: center;
        }

        .parametros-consulta {
            margin-top: 20px;
            padding: 10px;
            background-color: #f0f8ff;
            border: 1px solid #b8d0e8;
            border-radius: 5px;
            margin-bottom: 20px;
        }

        .parametros-consulta p {
            margin: 5px 0;
        }

        .boton-verificar {
            background-color: #4c3325;
            
            padding: 10px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
            margin-top: 10px;
            width: auto;
        }

        .boton-verificar:hover {
            background-color: #6a4535;
        }
    </style>
</head>
<body>
    <main class="contenedor hoja">
    <?php include '../includes/header.php'; ?>

        <div class="contenedor__modulo">
            <a href="analisiscalidad.php" class="atras">Ir atrás</a>
            <h2 class="heading"><?php echo $editando ? 'Editar' : 'Agregar'; ?> Análisis de Calidad</h2>
            
            <?php if ($parametros_sesion): ?>
            <div class="parametros-consulta">
                <h3>Parámetros Consultados</h3>
                <p><strong>Origen:</strong> <?php echo $parametros_sesion['origen'] === 'cliente' ? 'Cliente' : 'Equipo'; ?></p>
                <p><strong>Nombre:</strong> <?php echo htmlspecialchars($parametros_sesion['nombre_objetivo']); ?></p>
                <p><strong>Tipo de Equipo:</strong> <?php echo htmlspecialchars($parametros_sesion['tipo_equipo']); ?></p>
                <p><small>Parámetros cargados: <?php echo count($parametros_sesion['parametros']); ?></small></p>
            </div>
            <?php endif; ?>
            
            <!-- Formulario principal para análisis de calidad -->
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
                    <select class="formulario__input" id="tipo_equipo" name="tipo_equipo" <?php echo isset($parametros_sesion) ? 'readonly disabled' : ''; ?>>
                        <option value="" disabled <?php echo (!isset($parametros_sesion) && !$editando) ? 'selected' : ''; ?>>-- Seleccione tipo de equipo --</option>
                        <option value="Alveógrafo" <?php echo (isset($parametros_sesion) && $parametros_sesion['tipo_equipo'] == 'Alveógrafo') || ($editando && isset($equipos_seleccionados[0]) && $equipos_seleccionados[0]['tipo_equipo'] == 'Alveógrafo') ? 'selected' : ''; ?>>Alveógrafo</option>
                        <option value="Farinógrafo" <?php echo (isset($parametros_sesion) && $parametros_sesion['tipo_equipo'] == 'Farinógrafo') || ($editando && isset($equipos_seleccionados[0]) && $equipos_seleccionados[0]['tipo_equipo'] == 'Farinógrafo') ? 'selected' : ''; ?>>Farinógrafo</option>
                    </select>
                    <?php if (isset($parametros_sesion)): ?>
                    <!-- Campo oculto para mantener el valor si el select está deshabilitado -->
                    <input type="hidden" name="tipo_equipo" value="<?php echo htmlspecialchars($parametros_sesion['tipo_equipo']); ?>">
                    <?php endif; ?>
                </div>
                
                <!-- Campo oculto para almacenar el lote final -->
                <input type="hidden" name="lote" id="lote_final" value="<?php echo $editando ? htmlspecialchars($analisis['lote']) : $siguiente_lote; ?>">
                
                <!-- Formulario para verificar parámetros - se envía a obtener_parametros.php -->
                <div action="../config/obtener_parametros.php" method="POST" id="verificarParametrosForm">
                    <?php if ($editando): ?>
                    <input type="hidden" name="id_inspeccion" value="<?php echo htmlspecialchars($analisis['id_inspeccion']); ?>">
                    <?php endif; ?>
                    
                    <!-- Sección Origen de Parámetros -->
                    <h3 class="section-title">Origen de Parámetros</h3>
                    
                    <div class="switch-group">
                        <div class="switch-option <?php echo !isset($parametros_sesion) || $parametros_sesion['origen'] === 'cliente' ? 'active' : ''; ?>" data-target="cliente-group">Parámetros por Cliente</div>
                        <div class="switch-option <?php echo isset($parametros_sesion) && $parametros_sesion['origen'] === 'equipo' ? 'active' : ''; ?>" data-target="equipo-group">Parámetros por Equipo</div>
                    </div>
                    
                    <div class="option-group <?php echo !isset($parametros_sesion) || $parametros_sesion['origen'] === 'cliente' ? 'active' : ''; ?>" id="cliente-group">
                        <div class="formulario__campo">
                            <label for="id_cliente" class="formulario__label">Cliente</label>
                            <select class="formulario__input" id="id_cliente" name="id_cliente" <?php echo isset($parametros_sesion) && $parametros_sesion['origen'] === 'cliente' ? 'readonly disabled' : ''; ?>>
                                <option value="" disabled <?php echo (!isset($parametros_sesion) && !$editando) ? 'selected' : ''; ?>>-- Seleccione un cliente --</option>
                                <?php foreach ($clientes as $cliente): ?>
                                <option value="<?php echo $cliente['id_cliente']; ?>" 
                                        data-parametros="<?php echo htmlspecialchars($cliente['parametros']); ?>"
                                        <?php echo (isset($parametros_sesion) && $parametros_sesion['origen'] === 'cliente' && $parametros_sesion['id_objetivo'] == $cliente['id_cliente']) || ($editando && isset($analisis['id_cliente']) && $analisis['id_cliente'] == $cliente['id_cliente']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($cliente['nombre']); ?> (<?php echo htmlspecialchars($cliente['rfc']); ?>)
                                </option>
                                <?php endforeach; ?>
                            </select>
                            <?php if (isset($parametros_sesion) && $parametros_sesion['origen'] === 'cliente'): ?>
                            <!-- Campo oculto para mantener el valor si el select está deshabilitado -->
                            <input type="hidden" name="id_cliente" value="<?php echo htmlspecialchars($parametros_sesion['id_objetivo']); ?>">
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="option-group <?php echo isset($parametros_sesion) && $parametros_sesion['origen'] === 'equipo' ? 'active' : ''; ?>" id="equipo-group">                              
                        <div class="formulario__campo">
                            <label for="id_equipo" class="formulario__label">Equipo de Laboratorio</label>
                            <select class="formulario__input" id="id_equipo" name="id_equipo" <?php echo isset($parametros_sesion) && $parametros_sesion['origen'] === 'equipo' ? 'readonly disabled' : ''; ?>>
                                <option value="" disabled <?php echo (!isset($parametros_sesion) && !$editando) ? 'selected' : ''; ?>>-- Seleccione un equipo --</option>
                                <?php foreach ($equipos as $equipo): ?>
                                <option class="equipo-option" 
                                        data-tipo="<?php echo htmlspecialchars($equipo['tipo_equipo']); ?>" 
                                        value="<?php echo $equipo['id_equipo']; ?>" 
                                        <?php echo (isset($parametros_sesion) && $parametros_sesion['origen'] === 'equipo' && $parametros_sesion['id_objetivo'] == $equipo['id_equipo']) || ($editando && isset($equipos_seleccionados[0]) && $equipos_seleccionados[0]['id_equipo'] == $equipo['id_equipo']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($equipo['clave']); ?> - <?php echo htmlspecialchars($equipo['marca']); ?> <?php echo htmlspecialchars($equipo['modelo']); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                            <?php if (isset($parametros_sesion) && $parametros_sesion['origen'] === 'equipo'): ?>
                            <!-- Campo oculto para mantener el valor si el select está deshabilitado -->
                            <input type="hidden" name="id_equipo" value="<?php echo htmlspecialchars($parametros_sesion['id_objetivo']); ?>">
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Botón para verificar parámetros -->
                    <input type="hidden" id="origen_parametros" value="<?php echo isset($parametros_sesion) ? $parametros_sesion['origen'] : 'cliente'; ?>">
                    <div style="display: flex; gap: 10px;">
                        <button type="button" id="btnVerificarParametros" class="formulario__submit boton-verificar" <?php echo isset($parametros_sesion) ? 'disabled' : ''; ?>>
                            <?php echo isset($parametros_sesion) ? 'Parámetros Verificados ✓' : 'Verificar Parámetros Asociados'; ?>
                        </button>
                        
                        <?php if (isset($parametros_sesion)): ?>
                        <button type="button" id="btnCancelarVerificacion" class="formulario__submit boton-verificar" style="background-color: #d33;">
                            Cancelar Verificación
                        </button>
                        <?php endif; ?>
                    </div>                        
                </form>

                <!-- Sección Parámetros de Medición -->
                <h3 class="section-title">Parámetros de Medición</h3>
                
                <div id="seccion-alveografo" class="parametros-section" style="display: <?php echo (isset($parametros_sesion) && $parametros_sesion['tipo_equipo'] == 'Alveógrafo') ? 'block' : 'none'; ?>;">
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
                                <div class="parametro-label">Valor</div>
                            </div>
                            <?php if (!empty($param['lim_Inferior']) && !empty($param['lim_Superior'])): ?>
                            <div class="parametro-referencia">
                                <span>Referencia: <?php echo $param['lim_Inferior']; ?> - <?php echo $param['lim_Superior']; ?></span>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <!-- Sección para Farinografo -->
                <div id="seccion-farinografo" class="parametros-section" style="display: <?php echo (isset($parametros_sesion) && $parametros_sesion['tipo_equipo'] == 'Farinógrafo') ? 'block' : 'none'; ?>;">
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
                                <div class="parametro-label">Valor</div>
                            </div>
                            <?php if (!empty($param['lim_Inferior']) && !empty($param['lim_Superior'])): ?>
                            <div class="parametro-referencia">
                                <span>Referencia: <?php echo $param['lim_Inferior']; ?> - <?php echo $param['lim_Superior']; ?></span>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <input type="submit" class="formulario__submit" value="<?php echo $editando ? 'Guardar cambios' : 'Registrar análisis'; ?>">
                </div>
        </div>
        <?php include '../includes/footer.php'; ?>
    </main>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Referencias a elementos del DOM
            const form = document.getElementById('analisisForm');
            const verificarForm = document.getElementById('verificarParametrosForm');
            const tipoEquipoSelector = document.getElementById('tipo_equipo');
            const equipoSelector = document.getElementById('id_equipo');
            const clienteSelector = document.getElementById('id_cliente');
            const seccionAlveografo = document.getElementById('seccion-alveografo');
            const seccionFarinografo = document.getElementById('seccion-farinografo');
            const switchOptions = document.querySelectorAll('.switch-option');
            const loteFinal = document.getElementById('lote_final');
            const loteNuevo = document.getElementById('lote_nuevo');
            const loteExistente = document.getElementById('lote_existente');
            const btnVerificarParametros = document.getElementById('btnVerificarParametros');
            const origenParametrosInput = document.getElementById('origen_parametros');
            
            // Variable para rastrear el origen actual de parámetros (cliente o equipo)
            let parametrosPorCliente = <?php echo (!isset($parametros_sesion) || $parametros_sesion['origen'] === 'cliente') ? 'true' : 'false'; ?>;
            
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
                        
                        // Actualizar variable de seguimiento y campo oculto
                        parametrosPorCliente = targetId === 'cliente-group';
                        origenParametrosInput.value = parametrosPorCliente ? 'cliente' : 'equipo';
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
            
            // Función para cargar información de un lote existente
            function cargarInfoLote() {
                const lote = loteExistente.value;
                if (!lote) return;
                
                // Actualizar valor final del lote
                loteFinal.value = lote;
            }
            
            // Función para verificar parámetros (redirección manual en lugar de envío de formulario)
            function verificarParametros() {
                // Validaciones previas a redirigir
                let isValid = true;
                let errorMessage = '';
                
                // 1. Verificar que hay un tipo de equipo seleccionado
                if (!tipoEquipoSelector.value) {
                    isValid = false;
                    errorMessage = 'Por favor seleccione un tipo de equipo.';
                    tipoEquipoSelector.classList.add('input-error');
                } else {
                    tipoEquipoSelector.classList.remove('input-error');
                }
                
                // 2. Verificar el origen de parámetros
                parametrosPorCliente = document.querySelector('.switch-option.active[data-target="cliente-group"]') !== null;
                const origen = parametrosPorCliente ? 'cliente' : 'equipo';
                
                // 3. Verificar selección según origen
                let idObjetivo = '';
                if (parametrosPorCliente) {
                    // Si usamos parámetros por cliente, verificar que hay un cliente seleccionado
                    if (!clienteSelector.value) {
                        isValid = false;
                        errorMessage = errorMessage || 'Por favor seleccione un cliente.';
                        clienteSelector.classList.add('input-error');
                    } else {
                        clienteSelector.classList.remove('input-error');
                        idObjetivo = clienteSelector.value;
                    }
                } else {
                    // Si usamos parámetros por equipo, verificar que hay un equipo seleccionado
                    if (!equipoSelector.value) {
                        isValid = false;
                        errorMessage = errorMessage || 'Por favor seleccione un equipo de laboratorio.';
                        equipoSelector.classList.add('input-error');
                    } else {
                        equipoSelector.classList.remove('input-error');
                        idObjetivo = equipoSelector.value;
                    }
                }
                
                // 4. Si hay errores, mostrarlos
                if (!isValid) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error de validación',
                        text: errorMessage
                    });
                    return;
                }
                
                // 5. Construir y navegar a la URL
                let url = '../config/obtener_parametros.php';
                let params = new URLSearchParams();
                params.append('origen_parametros', origen);
                params.append('tipo_equipo', tipoEquipoSelector.value);
                
                if (parametrosPorCliente) {
                    params.append('id_cliente', idObjetivo);
                } else {
                    params.append('id_equipo', idObjetivo);
                }
                
                // Agregar id_inspeccion si estamos editando
                const idInspeccion = document.querySelector('input[name="id_inspeccion"]');
                if (idInspeccion) {
                    params.append('id_inspeccion', idInspeccion.value);
                }
                
                // Redirigir
                window.location.href = url + '?' + params.toString();
            }
            
            // Validación del formulario principal antes de enviar
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

                // 2. Verificar que hay parámetros cargados
                <?php if (!isset($parametros_sesion)): ?>
                isValid = false;
                errorMessage = errorMessage || 'Por favor verifique los parámetros asociados antes de registrar el análisis.';
                btnVerificarParametros.classList.add('input-error');
                <?php endif; ?>

                // 3. Verificar que hay parámetros visibles y que tienen valores
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
                    errorMessage = errorMessage || 'No hay sección de parámetros visible. Por favor, verifique los parámetros asociados.';
                } else if (parametrosIncompletos) {
                    isValid = false;
                    errorMessage = errorMessage || 'Por favor, complete todos los valores de parámetros.';
                }

                // 4. Mostrar errores o continuar con el envío
                if (!isValid) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error de validación',
                        text: errorMessage
                    });
                    return;
                }

                // 5. Confirmación antes de enviar
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

            // Función para cancelar la verificación
            function cancelarVerificacion() {
                Swal.fire({
                    title: '¿Cancelar verificación?',
                    text: 'Esto permitirá cambiar el cliente/equipo y tipo de equipo seleccionados.',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Sí, cancelar',
                    cancelButtonText: 'No, mantener'
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Redirigir a un archivo que limpie la sesión
                        window.location.href = '../config/limpiar_parametros_sesion.php';
                    }
                });
            }

            // Añade este evento con el resto de eventos
            if (document.getElementById('btnCancelarVerificacion')) {
                document.getElementById('btnCancelarVerificacion').addEventListener('click', cancelarVerificacion);
            }
            
            // Configurar eventos
            tipoEquipoSelector.addEventListener('change', actualizarSecciones);
            loteExistente.addEventListener('change', cargarInfoLote);
            btnVerificarParametros.addEventListener('click', verificarParametros);
            
            // Inicializar la vista
            actualizarSecciones();
            
            // Si estamos editando y ya hay tipo de equipo, actualizar secciones al cargar
            if (tipoEquipoSelector.value) {
                actualizarSecciones();
            }
        });
    </script>
</body>
</html>