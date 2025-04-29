<?php
require '../config/validar_permisos.php';
require '../config/conn.php';

$editando = false;
$equipo = null;
$errores = [];

if (isset($_SESSION['form_errors'])) {
    $errores = $_SESSION['form_errors'];
    unset($_SESSION['form_errors']);
}

$datos_form = [];
if (isset($_SESSION['form_data'])) {
    $datos_form = $_SESSION['form_data'];
    unset($_SESSION['form_data']);
}

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
}

// Consulta para obtener los responsables disponibles
$sql_responsables = "SELECT id_usuario, nombre FROM Usuarios 
                    WHERE rol IN ('Gerencia de Control de Calidad', 'Laboratorio')
                    ORDER BY nombre";
$responsables = $pdo->query($sql_responsables)->fetchAll(PDO::FETCH_ASSOC);

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

// Lista de marcas para el select
$marcas = [
    'Chopin',
    'Brabender',
    'Perten',
    'Foss',
    'Stable Micro Systems',
    'PerkinElmer',
    'Bühler',
    'Bruker',
    'Agilent',
    'Shimadzu'
];

// Lista de proveedores para el select
$proveedores = [
    'Chopin Instruments',
    'Brabender GmbH',
    'Perten Instruments',
    'Foss Analytics',
    'Stable Micro Systems',
    'PerkinElmer',
    'Bühler Group',
    'Bruck Laboratories',
    'FOSS Iberia',
    'Agromatic AG'
];

// Lista de ubicaciones para el select
$ubicaciones = [
    'Laboratorio Central',
    'Laboratorio de Control de Calidad',
    'Planta Principal',
    'Planta Norte',
    'Planta Sur',
    'Área de Producción',
    'Área de Almacén',
    'Área de Molienda',
    'Área de Empaque',
    'Oficina Técnica',
    'Bodega de Insumos',
    'Bodega de Mantenimiento',
    'Cuarto de Máquinas'
];

// Determinar si estamos usando valores personalizados o predefinidos en edición
$marca_personalizada = false;
$proveedor_personalizado = false;
$ubicacion_personalizada = false;

if ($editando) {

    $sql_parametros = "SELECT nombre_parametro, lim_Inferior, lim_Superior 
                       FROM Parametros 
                       WHERE id_equipo = :id_equipo";
    $stmt_parametros = $pdo->prepare($sql_parametros);
    $stmt_parametros->bindParam(':id_equipo', $id_equipo);
    $stmt_parametros->execute();
    
    $parametros_equipo = $stmt_parametros->fetchAll(PDO::FETCH_ASSOC);

     // Asignar los valores a los arrays de parámetros correspondientes
     foreach ($parametros_equipo as $param) {
        // Para parámetros de Alveógrafo
        foreach ($parametros_alveografo as $key => $alv_param) {
            if ($param['nombre_parametro'] == $alv_param['id_parametro']) {
                $parametros_alveografo[$key]['lim_Inferior'] = $param['lim_Inferior'];
                $parametros_alveografo[$key]['lim_Superior'] = $param['lim_Superior'];
            }
        }
        
        // Para parámetros de Farinógrafo
        foreach ($parametros_farinografo as $key => $far_param) {
            if ($param['nombre_parametro'] == $far_param['id_parametro']) {
                $parametros_farinografo[$key]['lim_Inferior'] = $param['lim_Inferior'];
                $parametros_farinografo[$key]['lim_Superior'] = $param['lim_Superior'];
            }
        }
    }


    $marca_personalizada = !in_array($equipo['marca'], $marcas);
    $proveedor_personalizado = !in_array($equipo['proveedor'], $proveedores);
    $ubicacion_personalizada = !in_array($equipo['ubicacion'], $ubicaciones);
}

// Comprobar si se han enviado datos del formulario
if (isset($datos_form)) {
    if (isset($datos_form['marca_select']) && $datos_form['marca_select'] == 'Otra') {
        $marca_personalizada = true;
    }
    if (isset($datos_form['proveedor_select']) && $datos_form['proveedor_select'] == 'Otro') {
        $proveedor_personalizado = true;
    }
    if (isset($datos_form['ubicacion_select']) && $datos_form['ubicacion_select'] == 'Otra') {
        $ubicacion_personalizada = true;
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
    
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.3/dist/sweetalert2.min.css">
    <style>
        .campo-error {
            color: red;
            font-size: 0.8rem;
            margin-top: 5px;
        }  
        .input-error {
            border-color: red !important;
        }        
        .campo-personalizado {
            margin-top: 10px;
            display: none;
        }
        
        .campo-personalizado.visible {
            display: block;
        }
        
        .parametros-section {
            margin-top: 20px;
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            background-color: #f9f9f9;
            grid-column: 1 / 3;
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
            gap: 20px;
            justify-content: space-between;
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
            width: 100px;
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 4px;
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 15px;
            font-family: inherit;
        }
        
        .parametro-label {
            font-size: 12px;
            color: #666;
        }
      
        .parametro-group {
            background-color: #f9f9f9;
            padding: 1.5rem;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
        }
    </style>
    <!-- SweetAlert2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.3/dist/sweetalert2.all.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {            
            const elements = {
                tipoEquipo: document.querySelector('select[name="tipo_equipo"]'),
                form: document.querySelector('form.formulario'),
                secciones: {
                    alveografo: document.getElementById('parametros-alveografo'),
                    farinografo: document.getElementById('parametros-farinografo')
                },
                campos: {
                    marca: {
                        select: document.getElementById('marca_select'),
                        personalizado: document.getElementById('marca_personalizada')
                    },
                    proveedor: {
                        select: document.getElementById('proveedor_select'),
                        personalizado: document.getElementById('proveedor_personalizado')
                    },
                    ubicacion: {
                        select: document.getElementById('ubicacion_select'),
                        personalizado: document.getElementById('ubicacion_personalizada')
                    }
                },
                fechas: {
                    adquisicion: document.getElementById('fecha_adquisicion'),
                    vencimiento: document.getElementById('vencimiento_garantia')
                }
            };

            // Funciones de utilidad para manejar la lógica del formulario
            const utils = {            
                actualizarSecciones() {
                    const tipoSeleccionado = elements.tipoEquipo.value;
                    
                    // Ocultar ambas secciones por defecto y quitar required
                    elements.secciones.alveografo.style.display = 'none';
                    elements.secciones.farinografo.style.display = 'none';
                    
                    // Quitar el atributo required de todos los inputs de parámetros
                    document.querySelectorAll('#parametros-alveografo input, #parametros-farinografo input').forEach(input => {
                        input.required = false;
                    });
                    
                    // Mostrar sección según el tipo seleccionado
                    if (tipoSeleccionado === 'Alveógrafo') {
                        elements.secciones.alveografo.style.display = 'block';
                        // Añadir required solo a los inputs visibles
                        document.querySelectorAll('#parametros-alveografo input').forEach(input => {
                            input.required = true;
                        });
                    } else if (tipoSeleccionado === 'Farinógrafo') {
                        elements.secciones.farinografo.style.display = 'block';
                        // Añadir required solo a los inputs visibles
                        document.querySelectorAll('#parametros-farinografo input').forEach(input => {
                            input.required = true;
                        });
                    }
                },
                
                // Función para validar que los límites inferiores no sean mayores que los superiores
                validarLimites(minInput, maxInput) {
                    const min = parseFloat(minInput.value);
                    const max = parseFloat(maxInput.value);
                    
                    if (!isNaN(min) && !isNaN(max) && min > max) {
                        utils.mostrarError(`El límite inferior de "${minInput.dataset.parametro}" no puede ser mayor al límite superior.`);
                        maxInput.value = '';
                        return false;
                    }
                    return true;
                },
                
                // Función para manejar campos personalizados (marca, proveedor, ubicación)
                toggleCampoPersonalizado(select, campoPersonalizado) {
                    if (!select || !campoPersonalizado) return;
                    
                    const esVisible = select.value === 'Otra' || select.value === 'Otro';
                    campoPersonalizado.parentElement.classList.toggle('visible', esVisible);
                    campoPersonalizado.required = esVisible;
                    
                    if (!esVisible) {
                        campoPersonalizado.value = '';
                    }
                },
                
                // Función para validar que la fecha de adquisición no sea posterior a la de vencimiento
                validarFechas() {
                    const adquisicion = elements.fechas.adquisicion;
                    const vencimiento = elements.fechas.vencimiento;
                    
                    if (adquisicion.value && vencimiento.value) {
                        const fechaAdq = new Date(adquisicion.value);
                        const fechaVenc = new Date(vencimiento.value);
                        
                        if (fechaAdq > fechaVenc) {
                            utils.mostrarError('La fecha de adquisición no puede ser posterior a la fecha de vencimiento de la garantía.');
                            return false;
                        }
                    }
                    return true;
                },
                
                // Función para transferir valores de campos personalizados a campos ocultos
                transferirValoresCampos() {
                    const campos = ['marca', 'proveedor', 'ubicacion'];
                    
                    campos.forEach(campo => {
                        const select = elements.campos[campo].select;
                        const personalizado = elements.campos[campo].personalizado;
                        const hidden = document.getElementById(campo);
                        
                        if (select && hidden) {
                            const esPersonalizado = select.value === 'Otra' || select.value === 'Otro';
                            hidden.value = esPersonalizado ? personalizado.value : select.value;
                        }
                    });
                },
                
                // Función para validar que los campos de parámetros tienen valores
                validarParametros() {
                    const tipoSeleccionado = elements.tipoEquipo.value;
                    let camposIncompletos = false;
                    
                    if (tipoSeleccionado === 'Alveógrafo') {
                        document.querySelectorAll('#parametros-alveografo input').forEach(input => {
                            if (!input.value) {
                                camposIncompletos = true;
                                input.classList.add('input-error');
                            } else {
                                input.classList.remove('input-error');
                            }
                        });
                    } else if (tipoSeleccionado === 'Farinógrafo') {
                        document.querySelectorAll('#parametros-farinografo input').forEach(input => {
                            if (!input.value) {
                                camposIncompletos = true;
                                input.classList.add('input-error');
                            } else {
                                input.classList.remove('input-error');
                            }
                        });
                    }
                    
                    if (camposIncompletos) {
                        utils.mostrarError('Por favor, complete todos los campos de parámetros.');
                        return false;
                    }
                    
                    return true;
                },
                
                // Función para mostrar mensajes de error con SweetAlert
                mostrarError(mensaje) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error de validación',
                        text: mensaje,
                        confirmButtonColor: '#4c3325',
                        confirmButtonText: 'Entendido'
                    });
                }
            };

            // Inicializar event listeners
            function inicializarEventListeners() {
                // Cambio de tipo de equipo
                elements.tipoEquipo.addEventListener('change', utils.actualizarSecciones);
                
                // Campos personalizados (marca, proveedor, ubicación)
                Object.entries(elements.campos).forEach(([key, campo]) => {
                    if (campo.select) {
                        campo.select.addEventListener('change', () => 
                            utils.toggleCampoPersonalizado(campo.select, campo.personalizado)
                        );
                        utils.toggleCampoPersonalizado(campo.select, campo.personalizado);
                    }
                });
                
                // Validación del formulario antes de enviar
                elements.form.addEventListener('submit', function(event) {
                    // Validar tipo de equipo
                    if (!elements.tipoEquipo.value) {
                        event.preventDefault();
                        utils.mostrarError('Por favor, seleccione un tipo de equipo.');
                        return false;
                    }
                    
                    // Validar que los parámetros estén completos
                    if (!utils.validarParametros()) {
                        event.preventDefault();
                        return false;
                    }
                    
                    // Validar fechas
                    if (!utils.validarFechas()) {
                        event.preventDefault();
                        return false;
                    }
                    
                    // Transferir valores de campos personalizados
                    utils.transferirValoresCampos();
                });
                
                // Validación de límites en los campos de parámetros
                document.querySelectorAll('.min-input').forEach(minInput => {
                    const maxInput = minInput.closest('.parametro-inputs').querySelector('.max-input');
                    minInput.addEventListener('change', () => utils.validarLimites(minInput, maxInput));
                    maxInput.addEventListener('change', () => utils.validarLimites(minInput, maxInput));
                });
            }
            
            // Inicializar
            inicializarEventListeners();
            utils.actualizarSecciones();
            
            // Mostrar errores iniciales si existen
            <?php if (!empty($errores)): ?>
            Swal.fire({
                title: 'Error al procesar el formulario',
                html: `
                    <div class="error-list">
                        <strong>Por favor corrija los siguientes errores:</strong>
                        <ul>
                            <?php foreach ($errores as $error): ?>
                            <li><?php echo $error; ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                `,
                icon: 'error',
                confirmButtonText: 'Entendido'
            });
            <?php endif; ?>
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
                    <input type="text" class="formulario__input <?php echo isset($errores['clave']) ? 'input-error' : ''; ?>" 
                           placeholder="Clave del equipo" 
                           name="clave" 
                           value="<?php echo isset($datos_form['clave']) ? htmlspecialchars($datos_form['clave']) : ($editando ? htmlspecialchars($equipo['clave']) : ''); ?>" 
                           required>
                    <?php if (isset($errores['clave'])): ?>
                    <div class="campo-error"><?php echo $errores['clave']; ?></div>
                    <?php endif; ?>
                </div>
                
                <div class="formulario__campo">
                    <label for="tipo_equipo" class="formulario__label">Tipo de equipo</label>
                    <select class="formulario__input" name="tipo_equipo" required>
                        <option value="" disabled <?php echo (!$editando && !isset($datos_form['tipo_equipo'])) ? 'selected' : ''; ?>>-- Seleccione un tipo --</option>
                        <option value="Alveógrafo" <?php echo (isset($datos_form['tipo_equipo']) && $datos_form['tipo_equipo'] == 'Alveógrafo') || ($editando && $equipo['tipo_equipo'] == 'Alveógrafo') ? 'selected' : ''; ?>>Alveógrafo</option>
                        <option value="Farinógrafo" <?php echo (isset($datos_form['tipo_equipo']) && $datos_form['tipo_equipo'] == 'Farinógrafo') || ($editando && $equipo['tipo_equipo'] == 'Farinógrafo') ? 'selected' : ''; ?>>Farinógrafo</option>
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
                
                
                <!-- Campo oculto para marca (valor final) -->
                <input type="hidden" id="marca" name="marca" value="<?php 
                    echo isset($datos_form['marca']) ? htmlspecialchars($datos_form['marca']) : 
                        ($editando ? htmlspecialchars($equipo['marca']) : ''); 
                ?>">
                
                <div class="formulario__campo">
                    <label for="marca_select" class="formulario__label">Marca</label>
                    <select class="formulario__input" id="marca_select" name="marca_select" required>
                        <option value="" disabled <?php echo (!$editando && !isset($datos_form['marca_select'])) ? 'selected' : ''; ?>>-- Seleccione una marca --</option>
                        <?php foreach($marcas as $marca): ?>
                            <option value="<?php echo htmlspecialchars($marca); ?>" 
                                <?php echo (isset($datos_form['marca_select']) && $datos_form['marca_select'] == $marca) || 
                                    ($editando && !$marca_personalizada && $equipo['marca'] == $marca) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($marca); ?>
                            </option>
                        <?php endforeach; ?>
                        <option value="Otra" <?php echo (isset($datos_form['marca_select']) && $datos_form['marca_select'] == 'Otra') || 
                            ($editando && $marca_personalizada) ? 'selected' : ''; ?>>Otra</option>
                    </select>
                    
                    <div class="campo-personalizado <?php echo $marca_personalizada ? 'visible' : ''; ?>">
                        <label for="marca_personalizada" class="formulario__label">Especifique la marca:</label>
                        <input type="text" class="formulario__input" 
                               id="marca_personalizada" 
                               name="marca_personalizada" 
                               placeholder="Escriba la marca" 
                               value="<?php echo (isset($datos_form['marca_personalizada']) ? htmlspecialchars($datos_form['marca_personalizada']) : 
                                   ($editando && $marca_personalizada ? htmlspecialchars($equipo['marca']) : '')); ?>" 
                               <?php echo $marca_personalizada ? 'required' : ''; ?>>
                    </div>
                </div>

                <div class="formulario__campo">
                    <label for="modelo" class="formulario__label">Modelo</label>
                    <input type="text" class="formulario__input" 
                           placeholder="Modelo" 
                           name="modelo" 
                           value="<?php echo isset($datos_form['modelo']) ? htmlspecialchars($datos_form['modelo']) : ($editando ? htmlspecialchars($equipo['modelo']) : ''); ?>" 
                           required>
                </div>

                <div class="formulario__campo">
                    <label for="serie" class="formulario__label">Serie</label>
                    <input type="text" class="formulario__input <?php echo isset($errores['serie']) ? 'input-error' : ''; ?>" 
                           placeholder="Serie" 
                           name="serie" 
                           value="<?php echo isset($datos_form['serie']) ? htmlspecialchars($datos_form['serie']) : ($editando ? htmlspecialchars($equipo['serie']) : ''); ?>" 
                           required>
                    <?php if (isset($errores['serie'])): ?>
                    <div class="campo-error"><?php echo $errores['serie']; ?></div>
                    <?php endif; ?>
                </div>
                
                <!-- Campo oculto para proveedor (valor final) -->
                <input type="hidden" id="proveedor" name="proveedor" value="<?php 
                    echo isset($datos_form['proveedor']) ? htmlspecialchars($datos_form['proveedor']) : 
                        ($editando ? htmlspecialchars($equipo['proveedor']) : ''); 
                ?>">
                
                <div class="formulario__campo">
                    <label for="proveedor_select" class="formulario__label">Proveedor</label>
                    <select class="formulario__input" id="proveedor_select" name="proveedor_select" required>
                        <option value="" disabled <?php echo (!$editando && !isset($datos_form['proveedor_select'])) ? 'selected' : ''; ?>>-- Seleccione un proveedor --</option>
                        <?php foreach($proveedores as $prov): ?>
                            <option value="<?php echo htmlspecialchars($prov); ?>" 
                                <?php echo (isset($datos_form['proveedor_select']) && $datos_form['proveedor_select'] == $prov) || 
                                    ($editando && !$proveedor_personalizado && $equipo['proveedor'] == $prov) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($prov); ?>
                            </option>
                        <?php endforeach; ?>
                        <option value="Otro" <?php echo (isset($datos_form['proveedor_select']) && $datos_form['proveedor_select'] == 'Otro') || 
                            ($editando && $proveedor_personalizado) ? 'selected' : ''; ?>>Otro</option>
                    </select>
                    
                    <div class="campo-personalizado <?php echo $proveedor_personalizado ? 'visible' : ''; ?>">
                        <label for="proveedor_personalizado" class="formulario__label">Especifique el proveedor:</label>
                        <input type="text" class="formulario__input" 
                               id="proveedor_personalizado" 
                               name="proveedor_personalizado" 
                               placeholder="Escriba el proveedor" 
                               value="<?php echo (isset($datos_form['proveedor_personalizado']) ? htmlspecialchars($datos_form['proveedor_personalizada']) : 
                                   ($editando && $proveedor_personalizado ? htmlspecialchars($equipo['proveedor']) : '')); ?>" 
                               <?php echo $proveedor_personalizado ? 'required' : ''; ?>>
                    </div>
                </div>

                <div class="formulario__campo">
                    <label for="descripcion_larga" class="formulario__label">Descripción larga</label>
                    <textarea name="desc_larga" id="descripcion_larga" class="formulario__input" required><?php echo isset($datos_form['desc_larga']) ? htmlspecialchars($datos_form['desc_larga']) : ($editando ? htmlspecialchars($equipo['desc_larga']) : ''); ?></textarea>
                </div>

                <div class="formulario__campo">
                    <label for="descripcion_corta" class="formulario__label">Descripción corta</label>
                    <input type="text" class="formulario__input" 
                           placeholder="Descripción corta" 
                           name="desc_corta" 
                           value="<?php echo isset($datos_form['desc_corta']) ? htmlspecialchars($datos_form['desc_corta']) : ($editando ? htmlspecialchars($equipo['desc_corta']) : ''); ?>" 
                           required>
                </div>

                <div class="formulario__campo">
                    <label for="garantia" class="formulario__label">Garantía</label>
                    <input type="text" class="formulario__input <?php echo isset($errores['garantia']) ? 'input-error' : ''; ?>" 
                           placeholder="Garantía" 
                           name="garantia" 
                           value="<?php echo isset($datos_form['garantia']) ? htmlspecialchars($datos_form['garantia']) : ($editando ? htmlspecialchars($equipo['garantia']) : ''); ?>" 
                           required>
                    <?php if (isset($errores['garantia'])): ?>
                    <div class="campo-error"><?php echo $errores['garantia']; ?></div>
                    <?php endif; ?>
                </div>

                <div class="formulario__campo">
                    <label for="encargado" class="formulario__label">Encargado del equipo</label>
                    <select class="formulario__input" name="id_responsable" required>
                        <option value="" disabled <?php echo (!$editando && !isset($datos_form['id_responsable'])) ? 'selected' : ''; ?>>-- Seleccione un Responsable --</option>
                        <?php foreach($responsables as $resp): ?>
                        <option value="<?= htmlspecialchars($resp['id_usuario']) ?>"
                            <?php echo (isset($datos_form['id_responsable']) && $datos_form['id_responsable'] == $resp['id_usuario']) || ($editando && $equipo['id_responsable'] == $resp['id_usuario']) ? 'selected' : ''; ?>>
                            <?= htmlspecialchars($resp['nombre']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Campo oculto para ubicación (valor final) -->
                <input type="hidden" id="ubicacion" name="ubicacion" value="<?php 
                    echo isset($datos_form['ubicacion']) ? htmlspecialchars($datos_form['ubicacion']) : 
                        ($editando ? htmlspecialchars($equipo['ubicacion']) : ''); 
                ?>">
                
                <div class="formulario__campo">
                    <label for="ubicacion_select" class="formulario__label">Ubicación del equipo</label>
                    <select class="formulario__input" id="ubicacion_select" name="ubicacion_select" required>
                        <option value="" disabled <?php echo (!$editando && !isset($datos_form['ubicacion_select'])) ? 'selected' : ''; ?>>-- Seleccione una ubicación --</option>
                        <?php foreach($ubicaciones as $ubic): ?>
                            <option value="<?php echo htmlspecialchars($ubic); ?>" 
                                <?php echo (isset($datos_form['ubicacion_select']) && $datos_form['ubicacion_select'] == $ubic) || 
                                    ($editando && !$ubicacion_personalizada && $equipo['ubicacion'] == $ubic) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($ubic); ?>
                            </option>
                        <?php endforeach; ?>
                        <option value="Otra" <?php echo (isset($datos_form['ubicacion_select']) && $datos_form['ubicacion_select'] == 'Otra') || 
                            ($editando && $ubicacion_personalizada) ? 'selected' : ''; ?>>Otra</option>
                    </select>
                    
                    <div class="campo-personalizado <?php echo $ubicacion_personalizada ? 'visible' : ''; ?>">
                        <label for="ubicacion_personalizada" class="formulario__label">Especifique la ubicación:</label>
                        <input type="text" class="formulario__input" 
                               id="ubicacion_personalizada" 
                               name="ubicacion_personalizada" 
                               placeholder="Escriba la ubicación" 
                               value="<?php echo (isset($datos_form['ubicacion_personalizada']) ? htmlspecialchars($datos_form['ubicacion_personalizada']) : 
                                   ($editando && $ubicacion_personalizada ? htmlspecialchars($equipo['ubicacion']) : '')); ?>" 
                               <?php echo $ubicacion_personalizada ? 'required' : ''; ?>>
                    </div>
                </div>

                <div class="formulario__campo">
                    <label for="estado" class="formulario__label">Estado</label>
                    <select class="formulario__input" name="estado" required>
                        <option value="Activo" <?php echo (isset($datos_form['estado']) && $datos_form['estado'] == 'Activo') || ($editando && $equipo['estado'] == 'Activo') ? 'selected' : ''; ?>>Activo</option>
                        <option value="Inactivo" <?php echo (isset($datos_form['estado']) && $datos_form['estado'] == 'Inactivo') || ($editando && $equipo['estado'] == 'Inactivo') ? 'selected' : ''; ?>>Inactivo</option>
                        <option value="Baja" <?php echo (isset($datos_form['estado']) && $datos_form['estado'] == 'Baja') || ($editando && $equipo['estado'] == 'Baja') ? 'selected' : ''; ?>>Baja</option>
                    </select>
                </div>
                
                <div class="formulario__campo">
                    <label for="fecha_adquisicion" class="formulario__label">Fecha de adquisición</label>
                    <input type="date" class="formulario__input" 
                           id="fecha_adquisicion"
                           name="fecha_adquisicion" 
                           value="<?php echo isset($datos_form['fecha_adquisicion']) ? htmlspecialchars($datos_form['fecha_adquisicion']) : ($editando && $equipo['fecha_adquisicion'] ? htmlspecialchars($equipo['fecha_adquisicion']) : ''); ?>" 
                           required>
                </div>

                <div class="formulario__campo">
                    <label for="vencimiento_garantia" class="formulario__label">Vigencia de la garantía</label>
                    <input type="date" class="formulario__input" 
                           id="vencimiento_garantia"
                           name="vencimiento_garantia" 
                           value="<?php echo isset($datos_form['vencimiento_garantia']) ? htmlspecialchars($datos_form['vencimiento_garantia']) : ($editando && $equipo['vencimiento_garantia'] ? htmlspecialchars($equipo['vencimiento_garantia']) : ''); ?>" 
                           required>
                </div>

                
                <input type="submit" class="formulario__submit" value="<?php echo $editando ? 'Guardar cambios' : 'Agregar equipo'; ?>">
            </form>
        </div>
        <?php include '../includes/footer.php'; ?>
    </main>
</body>
</html>