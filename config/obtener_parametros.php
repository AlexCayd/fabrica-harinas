<?php
/**
 * obtener_parametros.php
 * 
 * Este archivo realiza consultas a la base de datos para obtener los parámetros
 * asociados a un cliente o equipo específico, determinando automáticamente
 * el tipo de equipo (Alveógrafo o Farinógrafo).
 */
require './conn.php';
// Iniciar sesión para almacenar los resultados
session_start();

// Capturar información de selección de lote si está presente
if (isset($_GET['lote_option']) && isset($_GET['lote_value'])) {
    $_SESSION['lote_selection'] = [
        'option' => $_GET['lote_option'],
        'value' => $_GET['lote_value']
    ];
}

// Verificar que se reciben los datos necesarios
if (!isset($_GET['origen_parametros']) || 
   ($_GET['origen_parametros'] === 'cliente' && !isset($_GET['id_cliente'])) || 
   ($_GET['origen_parametros'] === 'equipo' && !isset($_GET['id_equipo']))) {
    
    $_SESSION['error'] = "Faltan datos necesarios para obtener los parámetros.";
    header('Location: ../modulos/analisiscalidadform.php' . (isset($_GET['id_inspeccion']) ? '?id=' . $_GET['id_inspeccion'] : ''));
    exit;
}

// Obtener el origen de los parámetros
$origen_parametros = $_GET['origen_parametros'];

// Variable para almacenar el tipo de equipo
$tipo_equipo = null;
$id_objetivo = null;
$nombre_objetivo = ""; // Para mostrar información al usuario
$parametros = [];

try {
    // Caso 1: Origen de parámetros es CLIENTE
    if ($origen_parametros === 'cliente') {
        $id_cliente = $_GET['id_cliente'];
        $id_objetivo = $id_cliente;
        
        // 1. Obtener información del cliente (incluyendo tipo de parámetros)
        $sql_cliente = "SELECT nombre, parametros, tipo_equipo FROM Clientes WHERE id_cliente = :id_cliente";
        $stmt_cliente = $pdo->prepare($sql_cliente);
        $stmt_cliente->bindParam(':id_cliente', $id_cliente, PDO::PARAM_INT);
        $stmt_cliente->execute();
        
        $cliente = $stmt_cliente->fetch(PDO::FETCH_ASSOC);
        
        if (!$cliente) {
            throw new Exception("No se encontró el cliente especificado.");
        }
        
        $nombre_objetivo = $cliente['nombre'];
        $tipo_parametros = $cliente['parametros']; // 'Internacionales' o 'Personalizados'
        
        // Si el cliente tiene tipo de equipo definido, usarlo preferentemente
        if (!empty($cliente['tipo_equipo'])) {
            $tipo_equipo = $cliente['tipo_equipo'];
        } 
        // Si no, usar el tipo de equipo seleccionado en el formulario
        else if (isset($_GET['tipo_equipo']) && !empty($_GET['tipo_equipo'])) {
            $tipo_equipo = $_GET['tipo_equipo'];
        }
        
        // 2. Obtener los parámetros según el tipo
        if ($tipo_parametros === 'Internacionales') {
            // Si son parámetros internacionales, 
            // necesitamos asegurarnos de tener un tipo de equipo seleccionado
            if (empty($tipo_equipo)) {
                throw new Exception("Para parámetros internacionales, debe seleccionar un tipo de equipo.");
            }

            // Determinar qué equipo de referencia usar según el tipo
            $id_equipo_referencia = ($tipo_equipo === 'Alveógrafo') ? 1 : 2;
            
            // Consultar parámetros directamente del equipo de referencia
            $sql_parametros = "SELECT nombre_parametro, lim_Inferior, lim_Superior 
                              FROM Parametros 
                              WHERE id_equipo = :id_equipo_referencia";
            
            $stmt_parametros = $pdo->prepare($sql_parametros);
            $stmt_parametros->bindParam(':id_equipo_referencia', $id_equipo_referencia, PDO::PARAM_INT);
            
        } else { // Personalizados
            // Para parámetros personalizados, obtener directamente los del cliente
            
            // Primero intentamos determinar el tipo de equipo a partir de los parámetros
            $sql_tipo = "SELECT nombre_parametro FROM Parametros WHERE id_cliente = :id_cliente";
            $stmt_tipo = $pdo->prepare($sql_tipo);
            $stmt_tipo->bindParam(':id_cliente', $id_cliente, PDO::PARAM_INT);
            $stmt_tipo->execute();
            
            $parametros_nombres = $stmt_tipo->fetchAll(PDO::FETCH_COLUMN);
            
            // Determinar tipo de equipo basado en los parámetros existentes
            if (in_array('Alveograma_P', $parametros_nombres) || 
                in_array('Alveograma_L', $parametros_nombres) || 
                in_array('Alveograma_W', $parametros_nombres)) {
                $tipo_equipo = 'Alveógrafo';
            } elseif (in_array('Farinograma_Absorcion_Agua', $parametros_nombres) || 
                      in_array('Farinograma_Tiempo_Desarrollo', $parametros_nombres) || 
                      in_array('Farinograma_Estabilidad', $parametros_nombres)) {
                $tipo_equipo = 'Farinógrafo';
            } else {
                // Si no podemos determinar el tipo, usamos el tipo seleccionado por el usuario
                if (isset($_GET['tipo_equipo']) && !empty($_GET['tipo_equipo'])) {
                    $tipo_equipo = $_GET['tipo_equipo'];
                } else {
                    throw new Exception("No se pudo determinar el tipo de equipo para este cliente.");
                }
            }
            
            // Obtener todos los parámetros del cliente
            $sql_parametros = "SELECT nombre_parametro, lim_Inferior, lim_Superior 
                              FROM Parametros 
                              WHERE id_cliente = :id_cliente";
            
            $stmt_parametros = $pdo->prepare($sql_parametros);
            $stmt_parametros->bindParam(':id_cliente', $id_cliente, PDO::PARAM_INT);
        }
        
    // Caso 2: Origen de parámetros es EQUIPO
    } else if ($origen_parametros === 'equipo') {
        $id_equipo = $_GET['id_equipo'];
        $id_objetivo = $id_equipo;
            
        // 1. Obtener información del equipo, incluyendo el tipo
        $sql_equipo = "SELECT clave, tipo_equipo FROM Equipos_Laboratorio WHERE id_equipo = :id_equipo";
        $stmt_equipo = $pdo->prepare($sql_equipo);
        $stmt_equipo->bindParam(':id_equipo', $id_equipo, PDO::PARAM_INT);
        $stmt_equipo->execute();
        
        $equipo = $stmt_equipo->fetch(PDO::FETCH_ASSOC);
        
        if (!$equipo) {
            throw new Exception("No se encontró el equipo especificado.");
        }
        
        $nombre_objetivo = $equipo['clave']; // Usar clave como nombre para mostrar
        $tipo_equipo = $equipo['tipo_equipo'];
        
        // 2. Obtener los parámetros del equipo
        $sql_parametros = "SELECT nombre_parametro, lim_Inferior, lim_Superior 
                          FROM Parametros 
                          WHERE id_equipo = :id_equipo";
        
        $stmt_parametros = $pdo->prepare($sql_parametros);
        $stmt_parametros->bindParam(':id_equipo', $id_equipo, PDO::PARAM_INT);
    } else {
        throw new Exception("Origen de parámetros no válido.");
    }
    
    // Ejecutar la consulta de parámetros
    $stmt_parametros->execute();
    $parametros = $stmt_parametros->fetchAll(PDO::FETCH_ASSOC);
    
    // Verificar que se obtuvieron parámetros
    if (empty($parametros)) {
        throw new Exception("No se encontraron parámetros para " . 
            ($origen_parametros === 'cliente' ? "el cliente" : "el equipo") . 
            " seleccionado.");
    }
    
    // Guardar los resultados en la sesión
    $_SESSION['parametros_consulta'] = [
        'origen' => $origen_parametros,
        'id_objetivo' => $id_objetivo,
        'nombre_objetivo' => $nombre_objetivo,
        'tipo_equipo' => $tipo_equipo,
        'parametros' => $parametros,
        'timestamp' => time() // Para saber cuándo se realizó la consulta
    ];
    
    $_SESSION['exito'] = "Parámetros cargados correctamente para " . 
        ($origen_parametros === 'cliente' ? "cliente" : "equipo") . 
        ": $nombre_objetivo";
        
    // Generar la URL de redirección, preservando el id_inspeccion si existe
    $redirect_url = "../modulos/analisiscalidadform.php";
    $params = [];
    
    if (isset($_GET['id_inspeccion'])) {
        $params[] = "id=" . $_GET['id_inspeccion'];
    }
    
    // Construir URL completa con parámetros
    if (!empty($params)) {
        $redirect_url .= '?' . implode('&', $params);
    }
    
    header("Location: $redirect_url");
    exit;
    
} catch (Exception $e) {
    $_SESSION['error'] = "Error al obtener parámetros: " . $e->getMessage();    
    
    // Generar la URL de redirección para caso de error, preservando el id_inspeccion si existe
    $redirect_url = "../modulos/analisiscalidadform.php";
    $params = [];
    
    if (isset($_GET['id_inspeccion'])) {
        $params[] = "id=" . $_GET['id_inspeccion'];
    }
    
    // Construir URL completa con parámetros
    if (!empty($params)) {
        $redirect_url .= '?' . implode('&', $params);
    }
    
    header("Location: $redirect_url");
    exit;
}