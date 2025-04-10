<?php
// Incluir el archivo de configuración de la base de datos (corregida la ruta)
require './conn.php';

// Verificar si es una solicitud de eliminación
if (isset($_GET['id']) && isset($_GET['accion']) && $_GET['accion'] == 'eliminar') {
    $id_equipo = $_GET['id'];
    
    try {
        // Iniciar transacción
        $pdo->beginTransaction();
        
        // Primero verificar si hay parámetros asociados a este equipo
        $sql_check_params = "SELECT COUNT(*) FROM Parametros WHERE id_equipo = :id_equipo";
        $stmt_check = $pdo->prepare($sql_check_params);
        $stmt_check->bindParam(':id_equipo', $id_equipo);
        $stmt_check->execute();
        $has_params = $stmt_check->fetchColumn() > 0;
        
        if ($has_params) {
            // Hay parámetros asociados, primero eliminarlos
            $sql_delete_params = "DELETE FROM Parametros WHERE id_equipo = :id_equipo";
            $stmt_delete_params = $pdo->prepare($sql_delete_params);
            $stmt_delete_params->bindParam(':id_equipo', $id_equipo);
            $stmt_delete_params->execute();
        }
        
        // Verificar si hay registros en Equipo_Inspeccion
        $sql_check_insp = "SELECT COUNT(*) FROM Equipo_Inspeccion WHERE id_equipo = :id_equipo";
        $stmt_check_insp = $pdo->prepare($sql_check_insp);
        $stmt_check_insp->bindParam(':id_equipo', $id_equipo);
        $stmt_check_insp->execute();
        $has_inspections = $stmt_check_insp->fetchColumn() > 0;
        
        if ($has_inspections) {
            // Eliminar registros en la tabla intermedia
            $sql_delete_insp = "DELETE FROM Equipo_Inspeccion WHERE id_equipo = :id_equipo";
            $stmt_delete_insp = $pdo->prepare($sql_delete_insp);
            $stmt_delete_insp->bindParam(':id_equipo', $id_equipo);
            $stmt_delete_insp->execute();
        }
        
        // Finalmente eliminar el equipo
        $sql_delete = "DELETE FROM Equipos_Laboratorio WHERE id_equipo = :id_equipo";
        $stmt_delete = $pdo->prepare($sql_delete);
        $stmt_delete->bindParam(':id_equipo', $id_equipo);
        $stmt_delete->execute();
        
        // Confirmar la transacción
        $pdo->commit();
        
        // Redirigir a la lista de equipos con mensaje de éxito
        header("Location: ../modulos/laboratorios.php?success=1&action=delete");
        exit;
        
    } catch (PDOException $e) {
        // Revertir la transacción en caso de error
        $pdo->rollBack();
        
        // Redirigir con mensaje de error
        header("Location: ../modulos/laboratorios.php?error=1&message=" . urlencode($e->getMessage()));
        exit;
    }
}

// Verificar si el formulario ha sido enviado (código existente)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Determinar si estamos editando o creando
    $editando = isset($_POST['editando']) && $_POST['editando'] == '1';
    
    // Recoger los datos del formulario
    $id_equipo = $editando ? $_POST['id_equipo'] : null;
    $clave = $_POST['clave'];
    $tipo_equipo = $_POST['tipo_equipo'];
    $marca = $_POST['marca'];
    $modelo = $_POST['modelo'];
    $serie = $_POST['serie'];
    $desc_larga = $_POST['desc_larga'];
    $desc_corta = $_POST['desc_corta'];
    $proveedor = $_POST['proveedor'];
    $fecha_adquisicion = !empty($_POST['fecha_adquisicion']) ? $_POST['fecha_adquisicion'] : null;
    $garantia = $_POST['garantia'];
    $vencimiento_garantia = !empty($_POST['vencimiento_garantia']) ? $_POST['vencimiento_garantia'] : null;
    $ubicacion = $_POST['ubicacion'];
    $estado = $_POST['estado'] ?? 'Activo';
    $id_responsable = $_POST['id_responsable'];
    
    // Recoger parámetros según el tipo de equipo
    $parametros = [];
    if ($tipo_equipo == 'Alveógrafo' && isset($_POST['alveografo'])) {
        $parametros = $_POST['alveografo'];
    } elseif ($tipo_equipo == 'Farinógrafo' && isset($_POST['farinografo'])) {
        $parametros = $_POST['farinografo'];
    }
    
    try {
        // Iniciar transacción
        $pdo->beginTransaction();
        
        if ($editando) {
            // Actualizar equipo existente
            $sql = "UPDATE Equipos_Laboratorio SET 
                    clave = :clave,
                    tipo_equipo = :tipo_equipo,
                    marca = :marca,
                    modelo = :modelo,
                    serie = :serie,
                    desc_larga = :desc_larga,
                    desc_corta = :desc_corta,
                    proveedor = :proveedor,
                    fecha_adquisicion = :fecha_adquisicion,
                    garantia = :garantia,
                    vencimiento_garantia = :vencimiento_garantia,
                    ubicacion = :ubicacion,
                    estado = :estado,
                    id_responsable = :id_responsable
                    WHERE id_equipo = :id_equipo";
                    
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':id_equipo', $id_equipo);
        } else {
            // Insertar nuevo equipo
            $sql = "INSERT INTO Equipos_Laboratorio (
                    clave, tipo_equipo, marca, modelo, serie, desc_larga, desc_corta, 
                    proveedor, fecha_adquisicion, garantia, vencimiento_garantia, 
                    ubicacion, estado, id_responsable
                    ) VALUES (
                    :clave, :tipo_equipo, :marca, :modelo, :serie, :desc_larga, :desc_corta, 
                    :proveedor, :fecha_adquisicion, :garantia, :vencimiento_garantia, 
                    :ubicacion, :estado, :id_responsable
                    )";
                    
            $stmt = $pdo->prepare($sql);
        }
        
        // Vincular parámetros
        $stmt->bindParam(':clave', $clave);
        $stmt->bindParam(':tipo_equipo', $tipo_equipo);
        $stmt->bindParam(':marca', $marca);
        $stmt->bindParam(':modelo', $modelo);
        $stmt->bindParam(':serie', $serie);
        $stmt->bindParam(':desc_larga', $desc_larga);
        $stmt->bindParam(':desc_corta', $desc_corta);
        $stmt->bindParam(':proveedor', $proveedor);
        $stmt->bindParam(':fecha_adquisicion', $fecha_adquisicion);
        $stmt->bindParam(':garantia', $garantia);
        $stmt->bindParam(':vencimiento_garantia', $vencimiento_garantia);
        $stmt->bindParam(':ubicacion', $ubicacion);
        $stmt->bindParam(':estado', $estado);
        $stmt->bindParam(':id_responsable', $id_responsable);
        
        // Ejecutar la consulta
        $stmt->execute();
        
        // Si es un nuevo equipo, obtener el ID insertado
        if (!$editando) {
            $id_equipo = $pdo->lastInsertId();
        }
        
        // Eliminar los parámetros existentes para este equipo (si estamos editando)
        if ($editando) {
            $sql_delete = "DELETE FROM Parametros WHERE id_equipo = :id_equipo AND tipo = 'Internacional'";
            $stmt_delete = $pdo->prepare($sql_delete);
            $stmt_delete->bindParam(':id_equipo', $id_equipo);
            $stmt_delete->execute();
        }
        
        // Insertar los nuevos parámetros
        if (!empty($parametros)) {
            $sql_insert_param = "INSERT INTO Parametros (id_equipo, nombre_parametro, tipo, lim_Inferior, lim_Superior) 
                                VALUES (:id_equipo, :nombre_parametro, 'Internacional', :lim_inferior, :lim_superior)";
            $stmt_param = $pdo->prepare($sql_insert_param);
            
            foreach ($parametros as $nombre_parametro => $valores) {
                if (!empty($valores['min']) || !empty($valores['max'])) {
                    $stmt_param->bindParam(':id_equipo', $id_equipo);
                    $stmt_param->bindParam(':nombre_parametro', $nombre_parametro);
                    $stmt_param->bindParam(':lim_inferior', $valores['min']);
                    $stmt_param->bindParam(':lim_superior', $valores['max']);
                    $stmt_param->execute();
                }
            }
        }
        
        // Confirmar la transacción
        $pdo->commit();
        
        // Redirigir a la lista de equipos con mensaje de éxito
        header("Location: ../modulos/laboratorios.php?success=1&action=" . ($editando ? 'update' : 'insert'));
        exit;
        
    } catch (PDOException $e) {
        // Revertir la transacción en caso de error
        $pdo->rollBack();
        
        // Redirigir con mensaje de error
        header("Location: ../modulos/laboratorios.php?error=1&message=" . urlencode($e->getMessage()));
        exit;
    }
} else {
    // Si no es una petición POST ni una solicitud de eliminación, redirigir
    if (!isset($_GET['id']) || !isset($_GET['accion'])) {
        header("Location: ../modulos/laboratorios.php");
        exit;
    }
}