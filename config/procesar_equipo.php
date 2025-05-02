<?php
// Incluir el archivo de configuración de la base de datos
require './conn.php';

// Iniciar sesión para poder almacenar datos temporales
session_start();

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
        
        // Corregi
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

// Verificar si el formulario ha sido enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Determinar si estamos editando o creando
    $editando = isset($_POST['editando']) && $_POST['editando'] == '1';
    
    // Recoger los datos del formulario
    $id_equipo = $editando ? $_POST['id_equipo'] : null;
    $clave = $_POST['clave'];
    $tipo_equipo = $_POST['tipo_equipo'];
    
    // Procesar campos que pueden venir de selects o inputs personalizados
    $marca = $_POST['marca'];
    $proveedor = $_POST['proveedor'];
    $ubicacion = $_POST['ubicacion'];
    
    $modelo = $_POST['modelo'];
    $serie = $_POST['serie'];
    $desc_larga = $_POST['desc_larga'];
    $desc_corta = $_POST['desc_corta'];
    $fecha_adquisicion = !empty($_POST['fecha_adquisicion']) ? $_POST['fecha_adquisicion'] : null;
    $garantia = $_POST['garantia'];
    $vencimiento_garantia = !empty($_POST['vencimiento_garantia']) ? $_POST['vencimiento_garantia'] : null;
    $estado = $_POST['estado'] ?? 'Activo';
    $id_responsable = $_POST['id_responsable'];
    
    $parametros_personalizados = [];
    if ($tipo_equipo == 'Alveógrafo' && isset($_POST['alveografo'])) {
        $parametros_personalizados = $_POST['alveografo'];
    } else if ($tipo_equipo == 'Farinógrafo' && isset($_POST['farinografo'])) {
        $parametros_personalizados = $_POST['farinografo'];
    }


    try {
        // Iniciar transacción
        $pdo->beginTransaction();
        
        // Validar campos únicos antes de procesar
        $errores = [];
        


        // Verificar clave
        $sql_check_clave = "SELECT COUNT(*) as total FROM Equipos_Laboratorio WHERE clave = :clave";
        $params_clave = [':clave' => $clave];
        
        if ($editando) {
            $sql_check_clave .= " AND id_equipo != :id_equipo";
            $params_clave[':id_equipo'] = $id_equipo;
        }
        
        $stmt_clave = $pdo->prepare($sql_check_clave);
        $stmt_clave->execute($params_clave);
        $existe_clave = $stmt_clave->fetch(PDO::FETCH_ASSOC)['total'] > 0;
        
        if ($existe_clave) {
            $errores['clave'] = "La clave de equipo '$clave' ya existe. Por favor, utilice otra.";
        }
        
        // Verificar serie
        $sql_check_serie = "SELECT COUNT(*) as total FROM Equipos_Laboratorio WHERE serie = :serie";
        $params_serie = [':serie' => $serie];
        
        if ($editando) {
            $sql_check_serie .= " AND id_equipo != :id_equipo";
            $params_serie[':id_equipo'] = $id_equipo;
        }
        
        $stmt_serie = $pdo->prepare($sql_check_serie);
        $stmt_serie->execute($params_serie);
        $existe_serie = $stmt_serie->fetch(PDO::FETCH_ASSOC)['total'] > 0;
        
        if ($existe_serie) {
            $errores['serie'] = "El número de serie '$serie' ya está registrado para otro equipo.";
        }
        
        // Verificar garantía
        $sql_check_garantia = "SELECT COUNT(*) as total FROM Equipos_Laboratorio WHERE garantia = :garantia";
        $params_garantia = [':garantia' => $garantia];
        
        if ($editando) {
            $sql_check_garantia .= " AND id_equipo != :id_equipo";
            $params_garantia[':id_equipo'] = $id_equipo;
        }
        
        $stmt_garantia = $pdo->prepare($sql_check_garantia);
        $stmt_garantia->execute($params_garantia);
        $existe_garantia = $stmt_garantia->fetch(PDO::FETCH_ASSOC)['total'] > 0;
        
        if ($existe_garantia) {
            $errores['garantia'] = "El número de garantía '$garantia' ya está registrado para otro equipo.";
        }
        
        // Verificar que la fecha de adquisición sea anterior a la fecha de vencimiento de la garantía
        if (!empty($fecha_adquisicion) && !empty($vencimiento_garantia)) {
            $fecha_adq = new DateTime($fecha_adquisicion);
            $fecha_venc = new DateTime($vencimiento_garantia);
            
            if ($fecha_adq > $fecha_venc) {
                $errores['fecha_adquisicion'] = "La fecha de adquisición no puede ser posterior a la fecha de vencimiento de la garantía.";
            }
        }
        
        // Si hay errores, abortar y redirigir de vuelta al formulario
        if (!empty($errores)) {
            $pdo->rollBack();
            
            // Guardar los datos del formulario y los errores en la sesión
            $_SESSION['form_data'] = $_POST;
            $_SESSION['form_errors'] = $errores;
            
            // Redirigir de vuelta al formulario con los errores
            if ($editando) {
                header("Location: ../modulos/laboratoriosform.php?id=" . $id_equipo);
            } else {
                header("Location: ../modulos/laboratoriosform.php");
            }
            exit;
        }
        
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
            $sql_delete = "DELETE FROM Parametros WHERE id_equipo = :id_equipo";
            $stmt_delete = $pdo->prepare($sql_delete);
            $stmt_delete->bindParam(':id_equipo', $id_equipo);
            $stmt_delete->execute();
        }
        
        
        if (!empty($parametros_personalizados)) {
            $sql_insert_param = "INSERT INTO Parametros (id_equipo, nombre_parametro, lim_Inferior, lim_Superior) 
                                VALUES (:id_equipo, :nombre_parametro, :lim_inferior, :lim_superior)";
            $stmt_param = $pdo->prepare($sql_insert_param);
            
            foreach ($parametros_personalizados as $nombre_parametro => $valores) {
                // Validar que existen los valores mínimo y máximo
                if (isset($valores['min']) && isset($valores['max'])) {
                    $lim_inferior = floatval($valores['min']);
                    $lim_superior = floatval($valores['max']);
                    
                    // Solo insertar si ambos límites tienen valores
                    if ($lim_inferior !== '' && $lim_superior !== '') {
                        $stmt_param->bindParam(':id_equipo', $id_equipo);
                        $stmt_param->bindParam(':nombre_parametro', $nombre_parametro);
                        $stmt_param->bindParam(':lim_inferior', $lim_inferior);
                        $stmt_param->bindParam(':lim_superior', $lim_superior);
                        $stmt_param->execute();
                    }
                }
            }
        } 


        
        // Confirmar la transacción
        $pdo->commit();
        
        // Limpiar datos de sesión si existen
        if (isset($_SESSION['form_data'])) {
            unset($_SESSION['form_data']);
        }
        if (isset($_SESSION['form_errors'])) {
            unset($_SESSION['form_errors']);
        }
        
        // Redirigir a la lista de equipos con mensaje de éxito
        header("Location: ../modulos/laboratorios.php?success=1&action=" . ($editando ? 'update' : 'insert'));
        exit;
        
    } catch (PDOException $e) {
        // Revertir la transacción en caso de error
        $pdo->rollBack();
        
        // Guardar los datos del formulario en la sesión
        $_SESSION['form_data'] = $_POST;
        $_SESSION['form_errors'] = ['general' => $e->getMessage()];
        
        // Redirigir de vuelta al formulario con error general
        if ($editando) {
            header("Location: ../modulos/laboratoriosform.php?id=" . $id_equipo);
        } else {
            header("Location: ../modulos/laboratoriosform.php");
        }
        exit;
    }
} else {
    // Si no es una petición POST ni una solicitud de eliminación, redirigir
    if (!isset($_GET['id']) || !isset($_GET['accion'])) {
        header("Location: ../modulos/laboratorios.php");
        exit;
    }
}