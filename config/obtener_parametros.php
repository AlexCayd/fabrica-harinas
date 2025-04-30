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


// Verificar que se reciben los datos necesarios
if (!isset($_POST['origen_parametros']) || 
   ($_POST['origen_parametros'] === 'cliente' && !isset($_POST['id_cliente'])) || 
   ($_POST['origen_parametros'] === 'equipo' && !isset($_POST['id_equipo']))) {
    
    $_SESSION['error'] = "Faltan datos necesarios para obtener los parámetros.";
    header('Location: analisiscalidadform.php');
    exit;
}

// Obtener el origen de los parámetros
$origen_parametros = $_POST['origen_parametros'];

// Variable para almacenar el tipo de equipo
$tipo_equipo = null;
$id_objetivo = null;
$nombre_objetivo = ""; // Para mostrar información al usuario
$parametros = [];

try {
    // Caso 1: Origen de parámetros es CLIENTE
    if ($origen_parametros === 'cliente') {
        $id_cliente = $_POST['id_cliente'];
        $id_objetivo = $id_cliente;
        
        // 1. Obtener información del cliente (incluyendo tipo de parámetros)
        $sql_cliente = "SELECT nombre, parametros FROM Clientes WHERE id_cliente = :id_cliente";
        $stmt_cliente = $pdo->prepare($sql_cliente);
        $stmt_cliente->bindParam(':id_cliente', $id_cliente, PDO::PARAM_INT);
        $stmt_cliente->execute();
        
        $cliente = $stmt_cliente->fetch(PDO::FETCH_ASSOC);
        
        if (!$cliente) {
            throw new Exception("No se encontró el cliente especificado.");
        }
        
        $nombre_objetivo = $cliente['nombre'];
        $tipo_parametros = $cliente['parametros']; // 'Internacionales' o 'Personalizados'
        
        // 2. Obtener los parámetros según el tipo
        if ($tipo_parametros === 'Internacionales') {
            // Si son parámetros internacionales, 
            // primero necesitamos determinar el tipo de equipo seleccionado
            if (isset($_POST['tipo_equipo']) && !empty($_POST['tipo_equipo'])) {
                $tipo_equipo = $_POST['tipo_equipo'];
            } else {
                throw new Exception("Para parámetros internacionales, debe seleccionar un tipo de equipo.");
            }
            
            // Usar cliente ID 1 como fuente de parámetros internacionales, filtrando por tipo de equipo
            $prefijo_parametro = ($tipo_equipo === 'Alveógrafo') ? 'Alveograma_' : 'Farinograma_'; // Si no es Alveógrafo, asumimos que es Farinógrafo osea Farinograma_
            
            $sql_parametros = "SELECT nombre_parametro, lim_Inferior, lim_Superior 
                              FROM Parametros 
                              WHERE id_cliente = 1 
                              AND (nombre_parametro LIKE :prefijo OR 
                                   nombre_parametro IN ('Humedad', 'Cenizas', 'Gluten_Humedo', 'Gluten_Seco', 'Indice_Gluten', 'Indice_Caida'))";
            
            $stmt_parametros = $pdo->prepare($sql_parametros);
            $stmt_parametros->bindValue(':prefijo', $prefijo_parametro . '%', PDO::PARAM_STR);
            
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
                if (isset($_POST['tipo_equipo']) && !empty($_POST['tipo_equipo'])) {
                    $tipo_equipo = $_POST['tipo_equipo'];
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
        
    } 
    // Caso 2: Origen de parámetros es EQUIPO
    else if ($origen_parametros === 'equipo') {
        $id_equipo = $_POST['id_equipo'];
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
        
    // Si hay un ID de inspección en el formulario original, preservarlo
    if (isset($_POST['id_inspeccion'])) {
        header("Location: analisiscalidadform.php?id=" . $_POST['id_inspeccion']);
    } else {
        header("Location: analisiscalidadform.php");
    }
    exit;
    
} catch (Exception $e) {
    $_SESSION['error'] = "Error al obtener parámetros: " . $e->getMessage();
    
    // Si hay un ID de inspección en el formulario original, preservarlo
    if (isset($_POST['id_inspeccion'])) {
        header("Location: analisiscalidadform.php?id=" . $_POST['id_inspeccion']);
    } else {
        header("Location: analisiscalidadform.php");
    }
    exit;
}