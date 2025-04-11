<?php
// Incluir el archivo de configuración de la base de datos
require './conn.php';

// Verificar si es una solicitud de eliminación
if (isset($_GET['id']) && isset($_GET['accion']) && $_GET['accion'] == 'eliminar') {
    $id_inspeccion = $_GET['id'];
    
    try {
        // Iniciar transacción
        $pdo->beginTransaction();
        
        // Primero eliminar los registros de la tabla Equipo_Inspeccion
        $sql_delete_equipos = "DELETE FROM Equipo_Inspeccion WHERE id_inspeccion = :id_inspeccion";
        $stmt_delete_equipos = $pdo->prepare($sql_delete_equipos);
        $stmt_delete_equipos->bindParam(':id_inspeccion', $id_inspeccion);
        $stmt_delete_equipos->execute();
        
        // Verificar si hay certificados relacionados
        $sql_check_certificados = "SELECT COUNT(*) FROM Certificados WHERE id_inspeccion = :id_inspeccion";
        $stmt_check = $pdo->prepare($sql_check_certificados);
        $stmt_check->bindParam(':id_inspeccion', $id_inspeccion);
        $stmt_check->execute();
        $has_certificados = $stmt_check->fetchColumn() > 0;
        
        if ($has_certificados) {
            // Si hay certificados, necesitamos eliminar los registros históricos primero
            $sql_get_certificados = "SELECT id_certificado FROM Certificados WHERE id_inspeccion = :id_inspeccion";
            $stmt_get = $pdo->prepare($sql_get_certificados);
            $stmt_get->bindParam(':id_inspeccion', $id_inspeccion);
            $stmt_get->execute();
            
            while ($row = $stmt_get->fetch(PDO::FETCH_ASSOC)) {
                $id_certificado = $row['id_certificado'];
                
                // Eliminar registros del historial de certificados
                $sql_delete_hist = "DELETE FROM Hist_Certificados WHERE id_certificado = :id_certificado";
                $stmt_delete_hist = $pdo->prepare($sql_delete_hist);
                $stmt_delete_hist->bindParam(':id_certificado', $id_certificado);
                $stmt_delete_hist->execute();
            }
            
            // Ahora eliminar los certificados
            $sql_delete_cert = "DELETE FROM Certificados WHERE id_inspeccion = :id_inspeccion";
            $stmt_delete_cert = $pdo->prepare($sql_delete_cert);
            $stmt_delete_cert->bindParam(':id_inspeccion', $id_inspeccion);
            $stmt_delete_cert->execute();
        }
        
        // Finalmente eliminar la inspección
        $sql_delete = "DELETE FROM Inspeccion WHERE id_inspeccion = :id_inspeccion";
        $stmt_delete = $pdo->prepare($sql_delete);
        $stmt_delete->bindParam(':id_inspeccion', $id_inspeccion);
        $stmt_delete->execute();
        
        // Confirmar la transacción
        $pdo->commit();
        
        // Redirigir a la lista de análisis con mensaje de éxito
        header("Location: ../modulos/analisiscalidad.php?success=1&action=delete");
        exit;
        
    } catch (PDOException $e) {
        // Revertir la transacción en caso de error
        $pdo->rollBack();
        
        // Redirigir con mensaje de error
        header("Location: ../modulos/analisiscalidad.php?error=1&message=" . urlencode($e->getMessage()));
        exit;
    }
}

// Verificar si el formulario ha sido enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Determinar si estamos editando o creando
    $editando = isset($_POST['editando']) && $_POST['editando'] == '1';
    
    // Recoger los datos del formulario
    $id_inspeccion = $editando ? $_POST['id_inspeccion'] : null;
    $id_cliente = $_POST['id_cliente'];
    $lote = $_POST['lote'];
    $secuencia = $_POST['secuencia'];
    $id_equipo = $_POST['id_equipo'];
    $tipo_equipo = $_POST['tipo_equipo'];
    
    // Generar clave compuesta para la inspección
    $clave = $lote . '-' . $secuencia;
    
    // Obtener el tipo de equipo para determinar qué parámetros procesar
    try {
        // Iniciar transacción
        $pdo->beginTransaction();
        
        // Preparar los datos según el tipo de equipo
        $campos_alveografo = ['alveograma_p', 'alveograma_l', 'alveograma_w', 'alveograma_pl', 'alveograma_ie'];
        $campos_farinografo = ['absorcion_agua', 'tiempo_desarrollo', 'estabilidad'];
        
        $columnas = ['id_cliente', 'lote', 'secuencia', 'clave', 'fecha_inspeccion'];
        $valores = [':id_cliente', ':lote', ':secuencia', ':clave', 'NOW()'];
        $actualizaciones = ['id_cliente = :id_cliente', 'lote = :lote', 'secuencia = :secuencia', 'clave = :clave', 'fecha_inspeccion = NOW()'];
        
        // Añadir columnas según el tipo de equipo
        if ($tipo_equipo == 'Alveógrafo') {
            foreach ($campos_alveografo as $campo) {
                if (isset($_POST[$campo]) && $_POST[$campo] !== '') {
                    $columnas[] = $campo;
                    $valores[] = ':' . $campo;
                    $actualizaciones[] = $campo . ' = :' . $campo;
                }
            }
        } elseif ($tipo_equipo == 'Farinógrafo') {
            foreach ($campos_farinografo as $campo) {
                if (isset($_POST[$campo]) && $_POST[$campo] !== '') {
                    $columnas[] = $campo;
                    $valores[] = ':' . $campo;
                    $actualizaciones[] = $campo . ' = :' . $campo;
                }
            }
        }
        
        // Construir la consulta SQL
        if ($editando) {
            $sql = "UPDATE Inspeccion SET " . implode(', ', $actualizaciones) . " WHERE id_inspeccion = :id_inspeccion";
        } else {
            $sql = "INSERT INTO Inspeccion (" . implode(', ', $columnas) . ") VALUES (" . implode(', ', $valores) . ")";
        }
        
        $stmt = $pdo->prepare($sql);
        
        // Vincular parámetros básicos
        $stmt->bindParam(':id_cliente', $id_cliente);
        $stmt->bindParam(':lote', $lote);
        $stmt->bindParam(':secuencia', $secuencia);
        $stmt->bindParam(':clave', $clave);
        
        if ($editando) {
            $stmt->bindParam(':id_inspeccion', $id_inspeccion);
        }
        
        // Vincular parámetros específicos según el tipo de equipo
        if ($tipo_equipo == 'Alveógrafo') {
            foreach ($campos_alveografo as $campo) {
                if (isset($_POST[$campo]) && $_POST[$campo] !== '') {
                    $valor = $_POST[$campo];
                    $stmt->bindParam(':' . $campo, $valor);
                }
            }
        } elseif ($tipo_equipo == 'Farinógrafo') {
            foreach ($campos_farinografo as $campo) {
                if (isset($_POST[$campo]) && $_POST[$campo] !== '') {
                    $valor = $_POST[$campo];
                    $stmt->bindParam(':' . $campo, $valor);
                }
            }
        }
        
        // Ejecutar la consulta
        $stmt->execute();
        
        // Si es una nueva inspección, obtener el ID insertado
        if (!$editando) {
            $id_inspeccion = $pdo->lastInsertId();
        }
        
        // Actualizar la relación con el equipo
        if ($editando) {
            // Primero eliminar las relaciones existentes
            $sql_delete_rel = "DELETE FROM Equipo_Inspeccion WHERE id_inspeccion = :id_inspeccion";
            $stmt_delete_rel = $pdo->prepare($sql_delete_rel);
            $stmt_delete_rel->bindParam(':id_inspeccion', $id_inspeccion);
            $stmt_delete_rel->execute();
        }
        
        // Insertar la nueva relación con el equipo
        $sql_equipo = "INSERT INTO Equipo_Inspeccion (id_equipo, id_inspeccion) VALUES (:id_equipo, :id_inspeccion)";
        $stmt_equipo = $pdo->prepare($sql_equipo);
        $stmt_equipo->bindParam(':id_equipo', $id_equipo);
        $stmt_equipo->bindParam(':id_inspeccion', $id_inspeccion);
        $stmt_equipo->execute();
        
        // Confirmar la transacción
        $pdo->commit();
        
        // Redirigir a la lista de análisis con mensaje de éxito
        header("Location: ../modulos/analisiscalidad.php?success=1&action=" . ($editando ? 'update' : 'insert'));
        exit;
        
    } catch (PDOException $e) {
        // Revertir la transacción en caso de error
        $pdo->rollBack();
        
        // Redirigir con mensaje de error
        header("Location: ../modulos/analisiscalidad.php?error=1&message=" . urlencode($e->getMessage()));
        exit;
    }
} else {
    // Si no es una petición POST ni una solicitud de eliminación, redirigir
    if (!isset($_GET['id']) || !isset($_GET['accion'])) {
        header("Location: ../modulos/analisiscalidad.php");
        exit;
    }
}