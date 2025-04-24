<?php
// Incluir el archivo de configuración de la base de datos
require '../config/conn.php';

// Iniciar sesión para recuperar datos y errores
session_start();

// Variable para determinar si estamos editando un equipo existente
$editando = false;
$equipo = null;
$errores = [];

// Recuperar errores de la sesión si existen
if (isset($_SESSION['form_errors'])) {
    $errores = $_SESSION['form_errors'];
    unset($_SESSION['form_errors']);
}

// Recuperar datos del formulario de la sesión si existen
$datos_form = [];
if (isset($_SESSION['form_data'])) {
    $datos_form = $_SESSION['form_data'];
    unset($_SESSION['form_data']);
}

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
}

// Consulta para obtener los responsables disponibles
$sql_responsables = "SELECT id_usuario, nombre FROM Usuarios 
                    WHERE rol IN ('Gerencia de Control de Calidad', 'Laboratorio')
                    ORDER BY nombre";
$responsables = $pdo->query($sql_responsables)->fetchAll(PDO::FETCH_ASSOC);

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
    $marca_personalizada = !in_array($equipo['marca'], $marcas);
    $proveedor_personalizado = !in_array($equipo['proveedor'], $proveedores);
    $ubicacion_personalizada = !in_array($equipo['ubicacion'], $ubicaciones);
}

// También comprobar si hay datos del formulario previo con valores personalizados
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
    <style>
        .campo-error {
            color: red;
            font-size: 0.8rem;
            margin-top: 5px;
        }
        
        .input-error {
            border-color: red !important;
        }
        
        .mensaje-error {
            background-color: #ffebee;
            border-left: 5px solid #f44336;
            padding: 10px 15px;
            margin-bottom: 20px;
            color: #b71c1c;
            border-radius: 4px;
        }
        
        .campo-personalizado {
            margin-top: 10px;
            display: none;
        }
        
        .campo-personalizado.visible {
            display: block;
        }
        
    </style>
    // Reemplaza todo el bloque del script en laboratoriosform.php con este código:
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Referencias a elementos
            const tipoEquipoSelect = document.querySelector('select[name="tipo_equipo"]');
            const formElement = document.querySelector('form.formulario');
            
            // Referencias a selects y campos personalizados
            const marcaSelect = document.getElementById('marca_select');
            const marcaPersonalizada = document.getElementById('marca_personalizada');
            const proveedorSelect = document.getElementById('proveedor_select');
            const proveedorPersonalizado = document.getElementById('proveedor_personalizado');
            const ubicacionSelect = document.getElementById('ubicacion_select');
            const ubicacionPersonalizada = document.getElementById('ubicacion_personalizada');
            
            // Fechas
            const fechaAdquisicion = document.getElementById('fecha_adquisicion');
            const fechaVencimiento = document.getElementById('vencimiento_garantia');
            
            // Función para manejar campos personalizados
            function toggleCampoPersonalizado(select, campoPersonalizado) {
                if (select && campoPersonalizado) {
                    if (select.value === 'Otra' || select.value === 'Otro') {
                        campoPersonalizado.parentElement.classList.add('visible');
                        campoPersonalizado.required = true;
                    } else {
                        campoPersonalizado.parentElement.classList.remove('visible');
                        campoPersonalizado.required = false;
                        campoPersonalizado.value = '';
                    }
                }
            }
            
            // Función para validar fechas
            function validarFechas() {
                if (fechaAdquisicion && fechaVencimiento && 
                    fechaAdquisicion.value && fechaVencimiento.value) {
                    
                    const fechaAdq = new Date(fechaAdquisicion.value);
                    const fechaVenc = new Date(fechaVencimiento.value);
                    
                    if (fechaAdq > fechaVenc) {
                        alert("Error: La fecha de adquisición no puede ser posterior a la fecha de vencimiento de la garantía.");
                        return false;
                    }
                }
                return true;
            }
            
            // Asignar eventos a los selects para mostrar/ocultar campos personalizados
            if (marcaSelect) {
                marcaSelect.addEventListener('change', function() {
                    toggleCampoPersonalizado(marcaSelect, marcaPersonalizada);
                });
                // Inicializar estado
                toggleCampoPersonalizado(marcaSelect, marcaPersonalizada);
            }
            
            if (proveedorSelect) {
                proveedorSelect.addEventListener('change', function() {
                    toggleCampoPersonalizado(proveedorSelect, proveedorPersonalizado);
                });
                // Inicializar estado
                toggleCampoPersonalizado(proveedorSelect, proveedorPersonalizado);
            }
            
            if (ubicacionSelect) {
                ubicacionSelect.addEventListener('change', function() {
                    toggleCampoPersonalizado(ubicacionSelect, ubicacionPersonalizada);
                });
                // Inicializar estado
                toggleCampoPersonalizado(ubicacionSelect, ubicacionPersonalizada);
            }
                
            // También validar el formulario antes de enviar
            formElement.addEventListener('submit', function(event) {
                // Validar que se haya seleccionado un tipo de equipo
                const tipoSeleccionado = tipoEquipoSelect.value;
                
                if (!tipoSeleccionado) {
                    event.preventDefault();
                    alert('Por favor, seleccione un tipo de equipo.');
                    return false;
                }
                
                // Validar las fechas
                if (!validarFechas()) {
                    event.preventDefault();
                    return false;
                }
                
                // Transferir valores personalizados a los campos ocultos antes de enviar
                if (marcaSelect && marcaSelect.value === 'Otra' && marcaPersonalizada) {
                    document.getElementById('marca').value = marcaPersonalizada.value;
                } else if (marcaSelect) {
                    document.getElementById('marca').value = marcaSelect.value;
                }
                
                if (proveedorSelect && proveedorSelect.value === 'Otro' && proveedorPersonalizado) {
                    document.getElementById('proveedor').value = proveedorPersonalizado.value;
                } else if (proveedorSelect) {
                    document.getElementById('proveedor').value = proveedorSelect.value;
                }
                
                if (ubicacionSelect && ubicacionSelect.value === 'Otra' && ubicacionPersonalizada) {
                    document.getElementById('ubicacion').value = ubicacionPersonalizada.value;
                } else if (ubicacionSelect) {
                    document.getElementById('ubicacion').value = ubicacionSelect.value;
                }
            });
            
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
            <a href="laboratorios.php" class="atras">Ir atrás</a>
            <h2 class="heading"><?php echo $editando ? 'Editar' : 'Agregar'; ?> Equipo de Laboratorio</h2>
            
            <?php if (!empty($errores)): ?>
            <div class="mensaje-error">
                <strong>Por favor corrija los siguientes errores:</strong>
                <ul>
                    <?php foreach ($errores as $error): ?>
                    <li><?php echo $error; ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php endif; ?>
            
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

                <!-- Botón para enviar el formulario -->
                <input type="submit" class="formulario__submit" value="<?php echo $editando ? 'Guardar cambios' : 'Agregar equipo'; ?>">
            </form>
        </div>
    </main>
</body>
</html>