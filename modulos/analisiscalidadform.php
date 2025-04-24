<?php
require '../config/conn.php';

// Variable para determinar si estamos editando un análisis existente
$editando = false;
$equipo = null;

$equipos_seleccionados = [];




// Si se recibe un ID para editar por medio de GET entonces...
if (isset($_GET['id']) && !empty($_GET['id'])) {
    $id_inspeccion = $_GET['id'];
    $editando = true;
    
    // Consulta para encontrar que inspección se está editando

    $sql_inspeccion = "SELECT * FROM Inspeccion WHERE id_inspeccion = :id_inspeccion";
    $stmt_inspeccion = $pdo->prepare($sql_inspeccion);
    $stmt_inspeccion->bindParam(':id_inspeccion', $id_inspeccion);
    $stmt_inspeccion->execute();
    
    $inspeccion = $stmt_inspeccion->fetch(PDO::FETCH_ASSOC);
    
    // Si no se encuentra la inspección, redirigir a la lista
    // Plantear si podría marcar con algun error
    if (!$inspeccion) {
        header('Location: analisiscalidad.php');
        exit;
    }
    
    // Consultar los equipos utilizados en esta inspección
    $sql_equipos_inspeccion = "SELECT id_equipo FROM Equipo_Inspeccion WHERE id_inspeccion = :id_inspeccion";
    $stmt_equipos = $pdo->prepare($sql_equipos_inspeccion);
    $stmt_equipos->bindParam(':id_inspeccion', $id_inspeccion);
    $stmt_equipos->execute();
    
    while ($row = $stmt_equipos->fetch(PDO::FETCH_ASSOC)) {
        $equipos_seleccionados[] = $row['id_equipo'];
    }
}

// Consulta para obtener todos los equipos activos
$sql_equipos = "SELECT id_equipo, clave, marca, modelo, tipo_equipo FROM Equipos_Laboratorio WHERE estado = 'Activo' ORDER BY tipo_equipo, clave";
$stmt_equipos = $pdo->query($sql_equipos);
$equipos = $stmt_equipos->fetchAll(PDO::FETCH_ASSOC);

// Organizar equipos por tipo para mostrarlos agrupados
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

// Función para obtener los parámetros de referencia según tipo de equipo y cliente
function obtenerParametrosReferencia($pdo, $id_equipo, $id_cliente = null) {
    $parametros = [];
    
    // Primero obtenemos el tipo de cliente (si usa parámetros internacionales o personalizados)
    if ($id_cliente) {
        $sql_cliente = "SELECT parametros FROM Clientes WHERE id_cliente = :id_cliente";
        $stmt_cliente = $pdo->prepare($sql_cliente);
        $stmt_cliente->bindParam(':id_cliente', $id_cliente);
        $stmt_cliente->execute();
        $cliente = $stmt_cliente->fetch(PDO::FETCH_ASSOC);
        $tipo_parametros = $cliente['parametros'] ?? 'Internacionales';
    } else {
        $tipo_parametros = 'Internacionales';
    }
    
    // Consulta base para obtener parámetros
    $sql_base = "SELECT nombre_parametro, lim_Inferior, lim_Superior FROM Parametros WHERE id_equipo = :id_equipo";
    
    if ($tipo_parametros == 'Personalizados' && $id_cliente) {
        // Buscar parámetros personalizados del cliente
        $sql = $sql_base . " AND id_cliente = :id_cliente";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':id_equipo', $id_equipo);
        $stmt->bindParam(':id_cliente', $id_cliente);
        $stmt->execute();
        $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Si no hay parámetros personalizados, usar los internacionales
        if (empty($resultados)) {
            $sql = $sql_base . " AND tipo = 'Internacional'";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':id_equipo', $id_equipo);
            $stmt->execute();
            $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
    } else {
        // Usar parámetros internacionales
        $sql = $sql_base . " AND tipo = 'Internacional'";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':id_equipo', $id_equipo);
        $stmt->execute();
        $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Convertir resultados a un array asociativo por nombre de parámetro
    foreach ($resultados as $param) {
        $parametros[$param['nombre_parametro']] = [
            'min' => $param['lim_Inferior'],
            'max' => $param['lim_Superior']
        ];
    }
    
    return $parametros;
}

// Inicializar arrays para guardar los parámetros de referencia
$parametros_alveografo_refs = [];
$parametros_farinografo_refs = [];

// Solo cargar parámetros si estamos editando o si ya hay equipo y cliente seleccionados
if ($editando && !empty($equipos_seleccionados) && !empty($inspeccion['id_cliente'])) {
    $id_equipo = $equipos_seleccionados[0];
    $id_cliente = $inspeccion['id_cliente'];
    
    // Obtener el tipo de equipo
    $sql_tipo = "SELECT tipo_equipo FROM Equipos_Laboratorio WHERE id_equipo = :id_equipo";
    $stmt_tipo = $pdo->prepare($sql_tipo);
    $stmt_tipo->bindParam(':id_equipo', $id_equipo);
    $stmt_tipo->execute();
    $tipo_equipo = $stmt_tipo->fetchColumn();
    
    // Cargar parámetros según el tipo
    if ($tipo_equipo == 'Alveógrafo') {
        $parametros_alveografo_refs = obtenerParametrosReferencia($pdo, $id_equipo, $id_cliente);
    } else if ($tipo_equipo == 'Farinógrafo') {
        $parametros_farinografo_refs = obtenerParametrosReferencia($pdo, $id_equipo, $id_cliente);
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
    </style>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Referencias a elementos del DOM
            const tipoEquipoSelector = document.getElementById('tipo_equipo');
            const equipoSelector = document.getElementById('id_equipo');
            const clienteSelector = document.getElementById('id_cliente');
            const seccionAlveografo = document.getElementById('seccion-alveografo');
            const seccionFarinografo = document.getElementById('seccion-farinografo');
            const loadingMessage = document.getElementById('loading-message');
            
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
</head>
<body>
    <main class="contenedor hoja">
        <header class="header">
            <h2 class="header__logo">
                F.H. Elizondo
            </h2>

            <nav class="header__nav">
                <a href="../menu.php" class="header__btn">
                    <img class="header__icono" src="../img/home.svg" alt="Home">
                    <p class="header__textoicono">Home</p>
                </a>

                <a href="../index.php" class="header__btn">
                    <img class="header__icono" src="../img/exit.svg" alt="Home">
                    <p class="header__textoicono">Salir</p>
                </a>
            </nav>
        </header>

        <div class="contenedor__modulo">
            <a href="analisiscalidad.php" class="atras">Ir atrás</a>
            <h2 class="heading"><?php echo $editando ? 'Editar' : 'Agregar'; ?> Análisis de Calidad</h2>
            
            <form action="../config/procesar_analisis.php" class="formulario" method="POST">
                <!-- Campo oculto para identificar si estamos editando -->
                <?php if ($editando): ?>
                <input type="hidden" name="editando" value="1">
                <input type="hidden" name="id_inspeccion" value="<?php echo htmlspecialchars($inspeccion['id_inspeccion']); ?>">
                <?php endif; ?>

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

                <!-- Sección para Alveógrafos -->
                <div id="seccion-alveografo" style="display: none;">
                    <h3 class="parametros-title">Parámetros Alveógrafo</h3>
                    
                    <div class="parametro-group">
                        <div class="parametro-row">
                            <label for="alveograma_p" class="parametro-label">Valor P (mm H₂O)</label>
                            <input type="number" step="0.01" class="parametro-input" placeholder="Ej. 70.50" name="alveograma_p" 
                                   value="<?php echo $editando ? htmlspecialchars($inspeccion['alveograma_p']) : ''; ?>">
                            <span id="ref-alveograma_p" class="parametro-referencia">
                                <?php if (!empty($parametros_alveografo_refs['alveograma_p'])): ?>
                                Ref: <?php echo $parametros_alveografo_refs['alveograma_p']['min']; ?> - <?php echo $parametros_alveografo_refs['alveograma_p']['max']; ?>
                                <?php else: ?>
                                Ref: Pendiente
                                <?php endif; ?>
                            </span>
                        </div>
                        
                        <div class="parametro-row">
                            <label for="alveograma_l" class="parametro-label">Valor L (mm)</label>
                            <input type="number" step="0.01" class="parametro-input" placeholder="Ej. 80.75" name="alveograma_l" 
                                   value="<?php echo $editando ? htmlspecialchars($inspeccion['alveograma_l']) : ''; ?>">
                            <span id="ref-alveograma_l" class="parametro-referencia">
                                <?php if (!empty($parametros_alveografo_refs['alveograma_l'])): ?>
                                Ref: <?php echo $parametros_alveografo_refs['alveograma_l']['min']; ?> - <?php echo $parametros_alveografo_refs['alveograma_l']['max']; ?>
                                <?php else: ?>
                                Ref: Pendiente
                                <?php endif; ?>
                            </span>
                        </div>
                        
                        <div class="parametro-row">
                            <label for="alveograma_w" class="parametro-label">Valor W (10⁻⁴ J)</label>
                            <input type="number" step="0.01" class="parametro-input" placeholder="Ej. 250.50" name="alveograma_w" 
                                   value="<?php echo $editando ? htmlspecialchars($inspeccion['alveograma_w']) : ''; ?>">
                            <span id="ref-alveograma_w" class="parametro-referencia">
                                <?php if (!empty($parametros_alveografo_refs['alveograma_w'])): ?>
                                Ref: <?php echo $parametros_alveografo_refs['alveograma_w']['min']; ?> - <?php echo $parametros_alveografo_refs['alveograma_w']['max']; ?>
                                <?php else: ?>
                                Ref: Pendiente
                                <?php endif; ?>
                            </span>
                        </div>
                        
                        <div class="parametro-row">
                            <label for="alveograma_pl" class="parametro-label">Relación P/L</label>
                            <input type="number" step="0.01" class="parametro-input" placeholder="Ej. 0.87" name="alveograma_pl" 
                                   value="<?php echo $editando ? htmlspecialchars($inspeccion['alveograma_pl']) : ''; ?>">
                            <span id="ref-alveograma_pl" class="parametro-referencia">
                                <?php if (!empty($parametros_alveografo_refs['alveograma_pl'])): ?>
                                Ref: <?php echo $parametros_alveografo_refs['alveograma_pl']['min']; ?> - <?php echo $parametros_alveografo_refs['alveograma_pl']['max']; ?>
                                <?php else: ?>
                                Ref: Pendiente
                                <?php endif; ?>
                            </span>
                        </div>
                        
                        <div class="parametro-row">
                            <label for="alveograma_ie" class="parametro-label">Índice de elasticidad (Ie)</label>
                            <input type="number" step="0.01" class="parametro-input" placeholder="Ej. 55.30" name="alveograma_ie" 
                                   value="<?php echo $editando ? htmlspecialchars($inspeccion['alveograma_ie']) : ''; ?>">
                            <span id="ref-alveograma_ie" class="parametro-referencia">
                                <?php if (!empty($parametros_alveografo_refs['alveograma_ie'])): ?>
                                Ref: <?php echo $parametros_alveografo_refs['alveograma_ie']['min']; ?> - <?php echo $parametros_alveografo_refs['alveograma_ie']['max']; ?>
                                <?php else: ?>
                                Ref: Pendiente
                                <?php endif; ?>
                            </span>
                        </div>
                    </div>
                </div>
                
                <!-- Sección para Farinógrafos -->
                <div id="seccion-farinografo" style="display: none;">
                    <h3 class="parametros-title">Parámetros Farinógrafo</h3>
                    
                    <div class="parametro-group">
                        <div class="parametro-row">
                            <label for="absorcion_agua" class="parametro-label">Absorción de agua (%)</label>
                            <input type="number" step="0.01" class="parametro-input" placeholder="Ej. 60.50" name="absorcion_agua" 
                                   value="<?php echo $editando && isset($inspeccion['absorcion_agua']) ? htmlspecialchars($inspeccion['absorcion_agua']) : ''; ?>">
                            <span id="ref-absorcion_agua" class="parametro-referencia">
                                <?php if (!empty($parametros_farinografo_refs['absorcion_agua'])): ?>
                                Ref: <?php echo $parametros_farinografo_refs['absorcion_agua']['min']; ?> - <?php echo $parametros_farinografo_refs['absorcion_agua']['max']; ?>
                                <?php else: ?>
                                Ref: Pendiente
                                <?php endif; ?>
                            </span>
                        </div>
                        
                        <div class="parametro-row">
                            <label for="tiempo_desarrollo" class="parametro-label">Tiempo de desarrollo (min)</label>
                            <input type="number" step="0.01" class="parametro-input" placeholder="Ej. 5.75" name="tiempo_desarrollo" 
                                   value="<?php echo $editando && isset($inspeccion['tiempo_desarrollo']) ? htmlspecialchars($inspeccion['tiempo_desarrollo']) : ''; ?>">
                            <span id="ref-tiempo_desarrollo" class="parametro-referencia">
                                <?php if (!empty($parametros_farinografo_refs['tiempo_desarrollo'])): ?>
                                Ref: <?php echo $parametros_farinografo_refs['tiempo_desarrollo']['min']; ?> - <?php echo $parametros_farinografo_refs['tiempo_desarrollo']['max']; ?>
                                <?php else: ?>
                                Ref: Pendiente
                                <?php endif; ?>
                            </span>
                        </div>
                        
                        <div class="parametro-row">
                            <label for="estabilidad" class="parametro-label">Estabilidad (min)</label>
                            <input type="number" step="0.01" class="parametro-input" placeholder="Ej. 8.25" name="estabilidad" 
                                   value="<?php echo $editando && isset($inspeccion['estabilidad']) ? htmlspecialchars($inspeccion['estabilidad']) : ''; ?>">
                            <span id="ref-estabilidad" class="parametro-referencia">
                                <?php if (!empty($parametros_farinografo_refs['estabilidad'])): ?>
                                Ref: <?php echo $parametros_farinografo_refs['estabilidad']['min']; ?> - <?php echo $parametros_farinografo_refs['estabilidad']['max']; ?>
                                <?php else: ?>
                                Ref: Pendiente
                                <?php endif; ?>
                            </span>
                        </div>
                    </div>
                </div>

                <input type="submit" class="formulario__submit" value="<?php echo $editando ? 'Guardar cambios' : 'Registrar análisis'; ?>">
            </form>
        </div>
    </main>
</body>
</html>