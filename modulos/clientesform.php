<?php require '../config/validar_permisos.php';
session_start();
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
    <title>FHE | Clientes</title>
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

        /* Estilos para el título de datos de contacto */
        h1 {
            color: var(--colorSecundario);
            font-family: var(--fuenteHeading);
            font-size: 24px;
            margin: 2rem 0 1.5rem;
            border-bottom: 3px solid var(--colorPrimario);
            text-align: left;
        }

        .parametro-group {
            background-color: #f9f9f9;
            padding: 1.5rem;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
        }
    </style>
    <main class="contenedor hoja">
        <?php include '../includes/header.php' ?>

        <div class="contenedor__modulo">
            <a href="clientes.php" class="atras">Ir atrás</a>
            <h2 class="heading">Agregar Cliente</h2>
            <form action="clientes/agregar_cliente.php" class="formulario" method="post">
                <div class="formulario__campo">
                    <label for="nombre" class="formulario__label">Nombre de la empresa</label>
                    <input type="text" name="nombre" class="formulario__input" placeholder="Nombre de la empresa">
                </div>

                <div class="formulario__campo">
                    <label for="certificado" class="formulario__label"> Requiere certificado </label>
                    <select name="certificado" id="certificado" class="formulario__select" required>
                        <option value="1"> Si </option>
                        <option value="0"> No </option>
                    </select>
                </div>
                
                <div class="formulario__campo">
                    <label for="rfc" class="formulario__label"> RFC </label>
                    <input type="text" name="rfc" class="formulario__input" placeholder="RFC" required>
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
                                <input type="number" step="0.01" class="parametro-input min-input" 
                                       name="alveografo[<?php echo $param['id_parametro']; ?>][min]" 
                                       value="<?php echo htmlspecialchars($param['lim_Inferior']); ?>" 
                                       placeholder="Mínimo"
                                       data-parametro="<?php echo htmlspecialchars($param['nombre']); ?>">
                                <div class="parametro-label">Límite inferior</div>
                            </div>
                            <div>
                                <input type="number" step="0.01" class="parametro-input max-input" 
                                       name="alveografo[<?php echo $param['id_parametro']; ?>][max]" 
                                       value="<?php echo htmlspecialchars($param['lim_Superior']); ?>" 
                                       placeholder="Máximo"
                                       data-parametro="<?php echo htmlspecialchars($param['nombre']); ?>">
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
                        <div class="parametro-nombre"><?php echo htmlspecialchars($param['nombre']); ?></div>
                        <div class="parametro-inputs">
                            <div>
                                <input type="number" step="0.01" class="parametro-input min-input" 
                                       name="farinografo[<?php echo $param['id_parametro']; ?>][min]"  
                                       placeholder="Mínimo"
                                       data-parametro="<?php echo htmlspecialchars($param['nombre']); ?>">
                                <div class="parametro-label">Límite inferior</div>
                            </div>
                            <div>
                                <input type="number" step="0.01" class="parametro-input max-input" 
                                       name="farinografo[<?php echo $param['id_parametro']; ?>][max]" 
                                       placeholder="Máximo"
                                       data-parametro="<?php echo htmlspecialchars($param['nombre']); ?>">
                                <div class="parametro-label">Límite superior</div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>  

                <div class="formulario__campo">
                    <h1>Datos de contacto</h1> 
                </div> <br>

                <div class="formulario__campo">
                    <label for="puesto_nombre" class="formulario__label"> Nombre </label>
                    <input type="text" name="puesto_nombre" class="formulario__input" placeholder="Nombre" required>
                </div>

                <div class="formulario__campo">
                    <label for="puesto" class="formulario__label"> Puesto </label>
                    <input type="text" name="puesto" class="formulario__input" placeholder="Puesto" required>
                </div>
                
                <div class="formulario__campo">
                    <label for="email" class="formulario__label">Correo electrónico</label>
                    <input type="email" name="email" class="formulario__input" placeholder="Correo electrónico" required>
                </div>

                <div class="formulario__campo">
                    <label for="numero-telefonico" class="formulario__label"> Numero telefónico </label>
                    <input type="text" name="numero-telefonico" class="formulario__input" placeholder="Numero telefonico" required>
                </div>

                <div class="formulario__campo">
                    <label for="direccion-fiscal" class="formulario__label"> Direccion fiscal </label>
                    <input type="text" name="direccion-fiscal" class="formulario__input" placeholder="Direccion fiscal" required>
                </div>

                <input type="submit" class="formulario__submit" value="Agregar cliente">
            </form>
        </div>
        <?php include '../includes/footer.php' ?>
    </main>

    <script>
        // Obtener elementos del DOM
        const tipoEquipoSelector = document.getElementById('tipo_equipo');
        const parametrosSelector = document.getElementById('parametros');
        const seccionAlveografo = document.getElementById('parametros-alveografo');
        const seccionFarinografo = document.getElementById('parametros-farinografo');

        // Función para actualizar la visibilidad de las secciones
        function actualizarSecciones() {
            const tipoSeleccionado = tipoEquipoSelector.value;
            const parametrosSeleccionados = parametrosSelector.value;
            
            // Ocultar ambas secciones si "Internacionales" está seleccionado
            if (parametrosSeleccionados === 'Internacionales') {
                seccionAlveografo.style.display = 'none';
                if (seccionFarinografo) {
                    seccionFarinografo.style.display = 'none';
                }
                return;
            }
            
            // Mostrar/ocultar secciones según el tipo de equipo cuando "Personalizados" está seleccionado
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

        // Agregar event listeners para ambos cambios de selección
        tipoEquipoSelector.addEventListener('change', actualizarSecciones);
        parametrosSelector.addEventListener('change', actualizarSecciones);

        // Ejecutar al cargar la página para establecer el estado inicial
        actualizarSecciones();

        // Función para validar los límites
        function validarLimites(minInput, maxInput) {
            const min = parseFloat(minInput.value);
            const max = parseFloat(maxInput.value);
            const parametro = minInput.dataset.parametro;

            if (!isNaN(min) && !isNaN(max) && min > max) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error de validación',
                    text: `El límite inferior de "${parametro}" no puede ser mayor al límite superior.`,
                    confirmButtonColor: '#4c3325'
                });
                maxInput.value = '';
                return false;
            }
            return true;
        }

        // Agregar event listeners a todos los inputs
        document.querySelectorAll('.parametro-input').forEach(input => {
            input.addEventListener('input', function() {
                const row = this.closest('.parametro-row');
                const minInput = row.querySelector('.min-input');
                const maxInput = row.querySelector('.max-input');
                validarLimites(minInput, maxInput);
            });
        });

        // Validar antes de enviar el formulario
        document.querySelector('form').addEventListener('submit', function(e) {
            let isValid = true;
            document.querySelectorAll('.parametro-row').forEach(row => {
                const minInput = row.querySelector('.min-input');
                const maxInput = row.querySelector('.max-input');
                if (!validarLimites(minInput, maxInput)) {
                    isValid = false;
                }
            });

            if (!isValid) {
                e.preventDefault();
            }
        });
    </script>
</body>

</html>