<?php require '../config/validar_permisos.php';

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

$editando = '';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FHE | Clientes</title>
    <link rel="stylesheet" href="../styles.css">
    <link rel="stylesheet" href="../css/menu.css">
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
            <h2 class="heading">Agregar Cliente</h2>
            <form action="clientes/agregar_cliente.php" class="formulario" method="post">
                <div class="formulario__campo">
                    <label for="nombre" class="formulario__label">Nombre</label>
                    <input type="text" name="nombre" class="formulario__input" placeholder="Nombre">
                </div>

                <div class="formulario__campo">
                    <label for="certificado" class="formulario__label"> Requiere certificado </label>
                    <select name="certificado" id="certificado" class="formulario__select" required>
                        <option value="1"> Si </option>
                        <option value="0"> No </option>
                    </select>
                </div>

                <div class="formulario__campo">
                    <label for="email" class="formulario__label">Correo electrónico</label>
                    <input type="email" name="email" class="formulario__input" placeholder="Correo electrónico" required>
                </div>

                <div class="formulario__campo">
                    <label for="rfc" class="formulario__label"> RFC </label>
                    <input type="text" name="rfc" class="formulario__input" placeholder="RFC" required>
                </div>

                <div class="formulario__campo">
                    <label for="puesto" class="formulario__label"> Puesto </label>
                    <input type="text" name="puesto" class="formulario__input" placeholder="Puesto" required>
                </div>

                <div class="formulario__campo">
                    <label for="numero-telefonico" class="formulario__label"> Numero telefónico </label>
                    <input type="text" name="numero-telefonico" class="formulario__input" placeholder="Numero telefonico" required>
                </div>

                <div class="formulario__campo">
                    <label for="direccion-fiscal" class="formulario__label"> Direccion fiscal </label>
                    <input type="text" name="direccion-fiscal" class="formulario__input" placeholder="Direccion fiscal" required>
                </div>

                <div class="formulario__campo">
                    <label for="rol" class="formulario__label">Estado</label>
                    <select name="categoria" id="categoria" class="formulario__select" required>
                        <option value="activo">Activo</option>
                        <option value="inactivo">Inactivo</option>
                    </select>
                </div>

                <div class="formulario__campo">
                    <label for="parametros" class="formulario__label"> Parametros </label>
                    <select name="parametros" id="parametros" class="formulario__select" required>
                        <option value="Internacionales"> Internacionales </option>
                        <option value="Personalizados"> Personalizados </option>
                    </select>
                </div>

                <div class="formulario__campo">
                    <label for="tipo_equipo" class="formulario__label">Tipo de Equipo</label>
                    <select class="formulario__input" id="tipo_equipo" name="tipo_equipo" required>
                        <option value="" disabled <?php echo !$editando ? 'selected' : ''; ?>>-- Seleccione tipo de equipo --</option>
                        <option value="Alveografo" >Alveógrafo</option>
                        <option value="Farinografo">Farinógrafo</option>
                    </select>
                </div>

                <!-- Mensaje de carga para los parámetros -->
                <div id="loading-message" class="loading-message" style="display: none;">
                    Cargando valores de referencia...
                </div>

                <!-- Sección para Alveógrafos -->
                <div id="parametros-alveografo" class="parametros-section" style="display: none;">
                    <div class="parametros-title">Valores de referencia internacionales - Alveógrafo</div>
                    
                    <?php foreach ($parametros_alveografo as $param): ?>
                    <div class="parametro-row">
                        <div class="parametro-nombre"><?php echo htmlspecialchars($param['nombre']); ?></div>
                        <div class="parametro-inputs">
                            <div>
                                <input type="number" step="0.01" class="parametro-input" 
                                       name="alveografo[<?php echo $param['id_parametro']; ?>][min]"  
                                       placeholder="Mínimo">
                                <div class="parametro-label">Límite inferior</div>
                            </div>
                            <div>
                                <input type="number" step="0.01" class="parametro-input" 
                                       name="alveografo[<?php echo $param['id_parametro']; ?>][max]"  
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

                <input type="submit" class="formulario__submit" value="Agregar cliente">
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
            if (tipoSeleccionado === 'Alveografo') {
                seccionAlveografo.style.display = 'block';
                if (seccionFarinografo) {
                    seccionFarinografo.style.display = 'none';
                }
            } else if (tipoSeleccionado === 'Farinografo') {
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