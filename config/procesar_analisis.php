<?php
require './conn.php';
require './validar_permisos.php'; // Esta línea ya incluye toda la validación necesaria

// Verificar que es una petición POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error'] = "Método no permitido";
    header('Location: ../modulos/analisiscalidadform.php');
    exit;
}


// Reemplazar la verificación de permisos que quitaste con esta versión compatible:
    if (isset($_SESSION['rol'])) {
        $location = '/fabrica-harinas/modulos/procesar_analisis.php'; // Ruta actual
        $rol = $_SESSION['rol'];
        $path = '/fabrica-harinas/modulos/';
        
        $permisos_laboratorio = [ 
            $path.'analisiscalidad.php', $path.'analisiscalidadform.php', $path.'certificadosform.php',
            $path.'clientes_editar.php', $path.'clientes.php', $path.'clientesform.php', $path.'estadisticos.php',
            $path.'historico.php', $path.'laboratorios.php', $path.'laboratoriosform.php', $path.'parametros.php', 
            $path.'parametrosform.php'
        ];
        
        // Solo los roles con permisos para procesar análisis
        $roles_permitidos = ['TI', 'Laboratorio', 'Gerencia de Control de Calidad', 'Gerencia de Aseguramiento de Calidad'];
        
        if (!in_array($_SESSION['rol'], $roles_permitidos)) {
            $_SESSION['error'] = "No tienes permisos para realizar esta acción";
            header('Location: ../modulos/analisiscalidad.php');
            exit;
        }
        
        // Verificación adicional para asegurar que el rol tenga acceso a esta acción
        if ($_SESSION['rol'] === 'Laboratorio' && !in_array($location, $permisos_laboratorio)) {
            $_SESSION['error'] = "No tienes permisos para realizar esta acción";
            header('Location: ../modulos/analisiscalidad.php');
            exit;
        }
    } else {
        $_SESSION['error'] = "No has iniciado sesión";
        header('Location: /fabrica-harinas/index.php');
        exit;
    }

// Obtener datos del formulario
$editando = isset($_POST['editando']) && $_POST['editando'] == '1';
$id_inspeccion = $editando ? $_POST['id_inspeccion'] : null;
$id_cliente = $_POST['id_cliente'] ?? null;
$id_equipo = $_POST['id_equipo'] ?? null;
$tipo_equipo = $_POST['tipo_equipo'] ?? null;

// Determinar el lote a usar
$usando_lote_nuevo = isset($_POST['lote_nuevo']) && !empty($_POST['lote_nuevo']);
$lote = $usando_lote_nuevo ? $_POST['lote_nuevo'] : ($_POST['lote_existente'] ?? null);

// Validaciones básicas
if (empty($lote)) {
    $_SESSION['error'] = "Debe especificar un lote";
    header('Location: ../modulos/analisiscalidadform.php' . ($id_inspeccion ? "?id=$id_inspeccion" : ''));
    exit;
}

if (empty($tipo_equipo) || !in_array($tipo_equipo, ['Alveógrafo', 'Farinógrafo'])) {
    $_SESSION['error'] = "Tipo de equipo no válido";
    header('Location: ../modulos/analisiscalidadform.php' . ($id_inspeccion ? "?id=$id_inspeccion" : ''));
    exit;
}

// Obtener parámetros según el tipo de equipo
$parametros_form = $tipo_equipo === 'Alveógrafo' ? ($_POST['alveografo'] ?? []) : ($_POST['farinografo'] ?? []);

if (empty($parametros_form)) {
    $_SESSION['error'] = "No se recibieron parámetros para el análisis";
    header('Location: ../modulos/analisiscalidadform.php' . ($id_inspeccion ? "?id=$id_inspeccion" : ''));
    exit;
}

try {
    $pdo->beginTransaction();

    // 1. Insertar o actualizar la inspección
    if ($editando) {
        // Actualizar inspección existente
        $sql_inspeccion = "UPDATE Inspeccion SET 
                           id_cliente = :id_cliente, 
                           lote = :lote,
                           fecha_inspeccion = NOW()
                           WHERE id_inspeccion = :id_inspeccion";
        
        $stmt_inspeccion = $pdo->prepare($sql_inspeccion);
        $stmt_inspeccion->bindParam(':id_cliente', $id_cliente, PDO::PARAM_INT);
        $stmt_inspeccion->bindParam(':lote', $lote, PDO::PARAM_STR);
        $stmt_inspeccion->bindParam(':id_inspeccion', $id_inspeccion, PDO::PARAM_INT);
        $stmt_inspeccion->execute();
        
        // Eliminar resultados anteriores
        $sql_delete_resultados = "DELETE FROM Resultado_Inspeccion WHERE id_inspeccion = :id_inspeccion";
        $stmt_delete = $pdo->prepare($sql_delete_resultados);
        $stmt_delete->bindParam(':id_inspeccion', $id_inspeccion, PDO::PARAM_INT);
        $stmt_delete->execute();
        
        // Eliminar relación con equipos anteriores
        $sql_delete_equipos = "DELETE FROM Equipo_Inspeccion WHERE id_inspeccion = :id_inspeccion";
        $stmt_delete_equipos = $pdo->prepare($sql_delete_equipos);
        $stmt_delete_equipos->bindParam(':id_inspeccion', $id_inspeccion, PDO::PARAM_INT);
        $stmt_delete_equipos->execute();
    } else {
        // Generar secuencia (A, B, ..., Z, AA, AB, ..., ZZ, AAA, etc.)
        $sql_secuencia = "SELECT MAX(secuencia) as max_sec FROM Inspeccion WHERE lote = :lote";
        $stmt_secuencia = $pdo->prepare($sql_secuencia);
        $stmt_secuencia->bindParam(':lote', $lote, PDO::PARAM_STR);
        $stmt_secuencia->execute();
        $result = $stmt_secuencia->fetch(PDO::FETCH_ASSOC);
        
        $secuencia = generarSiguienteSecuencia($result['max_sec'] ?? null);
        
        // Generar clave única (LOTE-00N)
        $sql_max_lote = "SELECT MAX(CAST(SUBSTRING(clave, 6) AS UNSIGNED)) as max_num FROM Inspeccion WHERE clave LIKE 'LOTE-%'";
        $stmt_max_lote = $pdo->query($sql_max_lote);
        $result = $stmt_max_lote->fetch(PDO::FETCH_ASSOC);
        $num_lote = ($result['max_num'] ?? 0) + 1;
        $clave = 'LOTE-' . str_pad($num_lote, 3, '0', STR_PAD_LEFT);
        
        // Insertar nueva inspección
        $sql_inspeccion = "INSERT INTO Inspeccion 
                          (id_cliente, lote, secuencia, clave, fecha_inspeccion) 
                          VALUES 
                          (:id_cliente, :lote, :secuencia, :clave, NOW())";
        
        $stmt_inspeccion = $pdo->prepare($sql_inspeccion);
        $stmt_inspeccion->bindParam(':id_cliente', $id_cliente, PDO::PARAM_INT);
        $stmt_inspeccion->bindParam(':lote', $lote, PDO::PARAM_STR);
        $stmt_inspeccion->bindParam(':secuencia', $secuencia, PDO::PARAM_STR);
        $stmt_inspeccion->bindParam(':clave', $clave, PDO::PARAM_STR);
        $stmt_inspeccion->execute();
        
        $id_inspeccion = $pdo->lastInsertId();
    }

    // 2. Insertar relación con equipo(s)
    if ($id_equipo) {
        $sql_equipo_inspeccion = "INSERT INTO Equipo_Inspeccion (id_equipo, id_inspeccion) VALUES (:id_equipo, :id_inspeccion)";
        $stmt_equipo = $pdo->prepare($sql_equipo_inspeccion);
        $stmt_equipo->bindParam(':id_equipo', $id_equipo, PDO::PARAM_INT);
        $stmt_equipo->bindParam(':id_inspeccion', $id_inspeccion, PDO::PARAM_INT);
        $stmt_equipo->execute();
    }

    // 3. Insertar resultados con validación de parámetros
    // Obtener límites de referencia para los parámetros
    $limites_parametros = obtenerLimitesParametros($pdo, $id_cliente, $id_equipo, $tipo_equipo);

    foreach ($parametros_form as $nombre_parametro => $datos) {
        $valor_obtenido = $datos['valor'] ?? null;
        
        if ($valor_obtenido === null || $valor_obtenido === '') {
            continue; // Saltar parámetros sin valor
        }
        
        // Determinar si el valor está dentro de los límites
        $aprobado = true;
        if (isset($limites_parametros[$nombre_parametro])) {
            $lim_inf = $limites_parametros[$nombre_parametro]['lim_Inferior'];
            $lim_sup = $limites_parametros[$nombre_parametro]['lim_Superior'];
            
            $aprobado = ($valor_obtenido >= $lim_inf && $valor_obtenido <= $lim_sup);
        }
        
        // Insertar resultado
        $sql_resultado = "INSERT INTO Resultado_Inspeccion 
                         (id_inspeccion, nombre_parametro, valor_obtenido, aprobado) 
                         VALUES 
                         (:id_inspeccion, :nombre_parametro, :valor_obtenido, :aprobado)";
        
        $stmt_resultado = $pdo->prepare($sql_resultado);
        $stmt_resultado->bindParam(':id_inspeccion', $id_inspeccion, PDO::PARAM_INT);
        $stmt_resultado->bindParam(':nombre_parametro', $nombre_parametro, PDO::PARAM_STR);
        $stmt_resultado->bindParam(':valor_obtenido', $valor_obtenido);
        $stmt_resultado->bindParam(':aprobado', $aprobado, PDO::PARAM_BOOL);
        $stmt_resultado->execute();
    }

    $pdo->commit();
    
    // Limpiar parámetros de sesión si existen
    if (isset($_SESSION['parametros_consulta'])) {
        unset($_SESSION['parametros_consulta']);
    }
    
    $_SESSION['exito'] = $editando ? "Análisis actualizado correctamente" : "Análisis registrado correctamente";
    header('Location: ../modulos/analisiscalidad.php');
    exit;

} catch (PDOException $e) {
    $pdo->rollBack();
    $_SESSION['error'] = "Error al procesar el análisis: " . $e->getMessage();
    header('Location: ../modulos/analisiscalidadform.php' . ($id_inspeccion ? "?id=$id_inspeccion" : ''));
    exit;
} catch (Exception $e) {
    $pdo->rollBack();
    $_SESSION['error'] = $e->getMessage();
    header('Location: ../modulos/analisiscalidadform.php' . ($id_inspeccion ? "?id=$id_inspeccion" : ''));
    exit;
}

// Función para generar la siguiente secuencia (A, B, ..., Z, AA, AB, ..., ZZ, AAA, etc.)
function generarSiguienteSecuencia($secuencia_actual) {
    if (empty($secuencia_actual)) {
        return 'A';
    }
    
    $letras = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $pos = strpos($letras, $secuencia_actual);
    
    if ($pos !== false && $pos < strlen($letras) - 1) {
        return $letras[$pos + 1];
    } else {
        $longitud = strlen($secuencia_actual);
        $todas_A = str_repeat('A', $longitud);
        
        if ($secuencia_actual === $todas_A) {
            return str_repeat('A', $longitud + 1);
        } else {
            // Lógica para incrementar secuencias como AB, AC, etc.
            $ultima_letra = substr($secuencia_actual, -1);
            $resto = substr($secuencia_actual, 0, -1);
            
            if ($ultima_letra === 'Z') {
                return generarSiguienteSecuencia($resto) . 'A';
            } else {
                $pos = strpos($letras, $ultima_letra);
                return $resto . $letras[$pos + 1];
            }
        }
    }
}

// Función para obtener los límites de los parámetros
function obtenerLimitesParametros($pdo, $id_cliente, $id_equipo, $tipo_equipo) {
    $limites = [];
    
    // Si hay cliente, primero verificar si usa parámetros internacionales
    if ($id_cliente) {
        $sql_tipo_parametros = "SELECT parametros FROM Clientes WHERE id_cliente = :id_cliente";
        $stmt_tipo = $pdo->prepare($sql_tipo_parametros);
        $stmt_tipo->bindParam(':id_cliente', $id_cliente, PDO::PARAM_INT);
        $stmt_tipo->execute();
        
        $tipo_parametros = $stmt_tipo->fetchColumn();
        
        if ($tipo_parametros === 'Internacionales') {
            // Caso especial: cliente con parámetros internacionales
            // Usamos los parámetros del cliente ID 1 (internacionales) filtrados por tipo de equipo
            
            $prefijo_parametro = ($tipo_equipo === 'Alveógrafo') ? 'Alveograma_' : 'Farinograma_';
            
            $sql = "SELECT nombre_parametro, lim_Inferior, lim_Superior 
                   FROM Parametros 
                   WHERE id_cliente = 1 
                   AND (nombre_parametro LIKE :prefijo OR 
                        nombre_parametro IN ('Humedad', 'Cenizas', 'Gluten_Humedo', 'Gluten_Seco', 'Indice_Gluten', 'Indice_Caida'))";
            
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':prefijo', $prefijo_parametro . '%', PDO::PARAM_STR);
            $stmt->execute();
            
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $limites[$row['nombre_parametro']] = [
                    'lim_Inferior' => $row['lim_Inferior'],
                    'lim_Superior' => $row['lim_Superior']
                ];
            }
            
            return $limites; // Retornamos directamente sin consultar equipos
        } else {
            // Cliente con parámetros personalizados - obtener todos sus parámetros
            $sql = "SELECT nombre_parametro, lim_Inferior, lim_Superior 
                   FROM Parametros 
                   WHERE id_cliente = :id_cliente";
            
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':id_cliente', $id_cliente, PDO::PARAM_INT);
            $stmt->execute();
            
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $limites[$row['nombre_parametro']] = [
                    'lim_Inferior' => $row['lim_Inferior'],
                    'lim_Superior' => $row['lim_Superior']
                ];
            }
        }
    }
    
    // Si no hay suficientes parámetros del cliente (o no hay cliente), obtener del equipo
    if ($id_equipo && (empty($limites) || count($limites) < 5)) {
        $sql = "SELECT nombre_parametro, lim_Inferior, lim_Superior 
               FROM Parametros 
               WHERE id_equipo = :id_equipo";
        
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':id_equipo', $id_equipo, PDO::PARAM_INT);
        $stmt->execute();
        
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            // Solo agregar si no existe ya del cliente
            if (!isset($limites[$row['nombre_parametro']])) {
                $limites[$row['nombre_parametro']] = [
                    'lim_Inferior' => $row['lim_Inferior'],
                    'lim_Superior' => $row['lim_Superior']
                ];
            }
        }
    }
    
    return $limites;
}