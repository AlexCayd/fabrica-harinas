<?php
// Incluir el archivo de configuración de la base de datos
require './conn.php';

// Inicializar respuesta
$response = [
    'success' => false,
    'tipo_equipo' => '',
    'parametros' => []
];

// Verificar que se reciben los datos necesarios
if (isset($_POST['id_equipo']) && isset($_POST['id_cliente'])) {
    $id_equipo = $_POST['id_equipo'];
    $id_cliente = $_POST['id_cliente'];
    
    try {
        // Obtener el tipo de equipo
        $sql_tipo = "SELECT tipo_equipo FROM Equipos_Laboratorio WHERE id_equipo = :id_equipo";
        $stmt_tipo = $pdo->prepare($sql_tipo);
        $stmt_tipo->bindParam(':id_equipo', $id_equipo);
        $stmt_tipo->execute();
        $tipo_equipo = $stmt_tipo->fetchColumn();
        
        if ($tipo_equipo) {
            $response['tipo_equipo'] = $tipo_equipo;
            
            // Obtener el tipo de parámetros que usa el cliente (Internacional o Personalizado)
            $sql_cliente = "SELECT parametros FROM Clientes WHERE id_cliente = :id_cliente";
            $stmt_cliente = $pdo->prepare($sql_cliente);
            $stmt_cliente->bindParam(':id_cliente', $id_cliente);
            $stmt_cliente->execute();
            $tipo_parametros = $stmt_cliente->fetchColumn() ?: 'Internacionales';
            
            // Consulta base para obtener parámetros
            $sql_base = "SELECT nombre_parametro, lim_Inferior, lim_Superior FROM Parametros WHERE id_equipo = :id_equipo";
            
            if ($tipo_parametros == 'Personalizados') {
                // Buscar parámetros personalizados del cliente
                $sql = $sql_base . " AND id_cliente = :id_cliente";
                $stmt = $pdo->prepare($sql);
                $stmt->bindParam(':id_equipo', $id_equipo);
                $stmt->bindParam(':id_cliente', $id_cliente);
                $stmt->execute();
                $parametros = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                // Si no hay parámetros personalizados, usar los internacionales
                if (empty($parametros)) {
                    $sql = $sql_base . " AND tipo = 'Internacional' AND id_cliente IS NULL";
                    $stmt = $pdo->prepare($sql);
                    $stmt->bindParam(':id_equipo', $id_equipo);
                    $stmt->execute();
                    $parametros = $stmt->fetchAll(PDO::FETCH_ASSOC);
                }
            } else {
                // Usar parámetros internacionales
                $sql = $sql_base . " AND tipo = 'Internacional' AND id_cliente IS NULL";
                $stmt = $pdo->prepare($sql);
                $stmt->bindParam(':id_equipo', $id_equipo);
                $stmt->execute();
                $parametros = $stmt->fetchAll(PDO::FETCH_ASSOC);
            }
            
            // Procesar resultados
            foreach ($parametros as $param) {
                $response['parametros'][$param['nombre_parametro']] = [
                    'min' => $param['lim_Inferior'],
                    'max' => $param['lim_Superior']
                ];
            }
            
            $response['success'] = true;
        }
    } catch (PDOException $e) {
        $response['error'] = $e->getMessage();
    }
}

// Devolver respuesta en formato JSON
header('Content-Type: application/json');
echo json_encode($response);