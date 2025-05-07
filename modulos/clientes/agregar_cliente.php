<?php

include '../../config/conn.php';
include '../../config/functions.php';
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $pdo->beginTransaction(); // Iniciamos la transacción

    $certificado = test_data($_POST['certificado']);
    $nombre = test_data($_POST['nombre']);
    $correo = test_data($_POST['email']);
    $rfc = test_data($_POST['rfc']);
    $puesto = test_data($_POST['puesto']);
    $nombre_contacto = test_data($_POST['puesto_nombre']);
    $telefono = test_data($_POST['numero-telefonico']);
    $direccion_fiscal = test_data($_POST['direccion-fiscal']);
    $parametros = test_data($_POST['parametros']);
    $estado = test_data($_POST['categoria']);
    $tipo = test_data($_POST['tipo_equipo']);

    // Validaciones iniciales
    if(empty($certificado) || empty($nombre) || empty($rfc) || empty($nombre_contacto) || empty($puesto) || empty($correo) || empty($telefono) || empty($direccion_fiscal) || 
    empty($estado) || empty($parametros) || empty($tipo)) {
        $_SESSION['error'] = 'Debes llenar todos los campos.';
        $pdo->rollBack();
        header("Location: /fabrica-harinas/modulos/clientesform.php");
        exit;
    }

    if(strlen($telefono) != 10) {
        $_SESSION['error'] = 'El teléfono debe tener 10 dígitos.';
        $pdo->rollBack();
        header("Location: /fabrica-harinas/modulos/clientesform.php");
        exit;
    }

    if(strlen($rfc) != 13) {
        $_SESSION['error'] = 'El RFC debe tener 13 dígitos.';
        $pdo->rollBack();
        header("Location: /fabrica-harinas/modulos/clientesform.php");
        exit;
    }

    // Insertar cliente
    $sql = "INSERT INTO Clientes (req_certificado, nombre, rfc, nombre_contacto, puesto_contacto, correo_contacto, telefono_contacto, direccion_fiscal, estado, tipo_equipo, parametros) 
            VALUES (:certificado, :nombre, :rfc, :nombre_contacto, :puesto, :correo, :telefono, :direccion_fiscal, :estado, :tipo, :parametros)";

    $stmt = $pdo->prepare($sql);

    if (!$stmt->execute([
        ':certificado' => $certificado,
        ':nombre' => $nombre,
        ':rfc' => $rfc,
        ':nombre_contacto' => $nombre_contacto,
        ':puesto' => $puesto,
        ':correo' => $correo,
        ':telefono' => $telefono,
        ':direccion_fiscal' => $direccion_fiscal,
        ':estado' => $estado,
        ':tipo' => $tipo,
        ':parametros' => $parametros
    ])) {
        $_SESSION['error'] = 'Error al insertar el cliente.';
        $pdo->rollBack();
        header("Location: /fabrica-harinas/modulos/clientesform.php");
        exit;
    }

    $ultimo_id = $pdo->lastInsertId();

    // Insertar parámetros personalizados si existen
    if ($parametros == 'Personalizados') {
        if ($tipo == 'Alveografo' && isset($_POST['alveografo'])) {
            foreach ($_POST['alveografo'] as $parametro_id => $valores) {
                $min = floatval(test_data($valores['min']));
                $max = floatval(test_data($valores['max']));

                if($min > $max) {
                    $_SESSION['error'] = 'El límite inferior de "'.$parametro_id.'" no puede ser mayor al límite superior.';
                    $pdo->rollBack();
                    header("Location: /fabrica-harinas/modulos/clientesform.php?error_parametro=".$parametro_id);
                    exit;
                }

                $sql_parametros = "INSERT INTO Parametros (id_equipo, id_cliente, nombre_parametro, lim_Superior, lim_Inferior) 
                                   VALUES (NULL, :id_cliente, :parametro, :max, :min)";

                $stmt_parametros = $pdo->prepare($sql_parametros);

                if (!$stmt_parametros->execute([
                    ':id_cliente' => $ultimo_id,
                    ':parametro' => $parametro_id,
                    ':max' => $max,
                    ':min' => $min
                ])) {
                    $_SESSION['error'] = 'Error al insertar parámetros.';
                    $pdo->rollBack();
                    header("Location: /fabrica-harinas/modulos/clientesform.php");
                    exit;
                }
            }
        } else if ($tipo == 'Farinografo' && isset($_POST['farinografo'])) {
            foreach ($_POST['farinografo'] as $parametro_id => $valores) {
                $min = test_data($valores['min']);
                $max = test_data($valores['max']);

                if($min > $max) {
                    $_SESSION['error'] = 'El límite inferior de "'.$parametro_id.'" no puede ser mayor al límite superior.';
                    $pdo->rollBack();
                    header("Location: /fabrica-harinas/modulos/clientesform.php?error_parametro=".$parametro_id);
                    exit;
                }

                $sql_parametros = "INSERT INTO Parametros (id_equipo, id_cliente, nombre_parametro, lim_Superior, lim_Inferior) 
                                   VALUES (NULL, :id_cliente, :parametro, :max, :min)";

                $stmt_parametros = $pdo->prepare($sql_parametros);

                if (!$stmt_parametros->execute([
                    ':id_cliente' => $ultimo_id,
                    ':parametro' => $parametro_id,
                    ':max' => $max,
                    ':min' => $min
                ])) {
                    $_SESSION['error'] = 'Error al insertar parámetros.';
                    $pdo->rollBack();
                    header("Location: /fabrica-harinas/modulos/clientesform.php");
                    exit;
                }
            }
        }
    }

    // Todo salió bien
    $pdo->commit();
    $_SESSION['exito'] = 'Cliente agregado correctamente.';
    header("Location: /fabrica-harinas/modulos/clientes.php?success");
    exit();

} else {
    header("Location: /fabrica-harinas/menu.php");
    exit();
}
?>
