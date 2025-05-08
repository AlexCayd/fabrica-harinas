<?php
include_once '../../includes/config.php';
include '../../config/conn.php';
include '../../config/functions.php';
// session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
$datos_validados = [];


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
    if (
        empty($certificado) || empty($nombre) || empty($rfc) || empty($nombre_contacto) || empty($puesto) || empty($correo) || empty($telefono) || empty($direccion_fiscal) ||
        empty($estado) || empty($parametros) || empty($tipo)
    ) {
        $_SESSION['error'] = 'Debes llenar todos los campos.';
        $pdo->rollBack();
        header("Location: " . BASE_URL . "modulos/clientesform.php");
        exit();
    }

    if (strlen($telefono) != 10) {
        $_SESSION['error'] = 'El teléfono debe tener 10 dígitos.';
        $pdo->rollBack();
        header("Location: " . BASE_URL . "modulos/clientesform.php");
        exit();
    }

    if (strlen($rfc) != 13) {
        $_SESSION['error'] = 'El RFC debe tener 13 dígitos.';
        $pdo->rollBack();
        header("Location: " . BASE_URL . "modulos/clientesform.php");
        exit();
    }

    // Insertar parámetros personalizados si existen
    if ($parametros == 'Personalizados') {
        if ($tipo == 'Alveógrafo' && isset($_POST['alveografo'])) {
            $sql_tipo_equipo = "SELECT p.* 
            FROM Parametros p
            JOIN Equipos_Laboratorio e ON p.id_equipo = e.id_equipo
            WHERE e.clave = 'ALV-INT' AND p.id_cliente IS NULL;";

            // Obtener los parametros internacionales
            $stmt_parametros_int = $pdo->prepare($sql_tipo_equipo);
            $stmt_parametros_int->execute();
            $parametros_int = $stmt_parametros_int->fetchAll(PDO::FETCH_ASSOC);

            $parametros_referencia = [];
            foreach ($parametros_int as $p) {
                $parametros_referencia[$p['nombre_parametro']] = [
                    'min' => $p['lim_Inferior'],
                    'max' => $p['lim_Superior']
                ];
            }

            foreach ($_POST['alveografo'] as $parametro_id => $valores) {
                $min_raw = test_data($valores['min']);
                $max_raw = test_data($valores['max']);

                if (!is_numeric($min_raw) || !is_numeric($max_raw)) {
                    $_SESSION['error'] = 'Los valores de límite para el parámetro "' . $parametro_id . '" deben ser numéricos.';
                    header("Location: " . BASE_URL . "modulos/clientesform.php?error_parametro=" . $parametro_id);
                    exit();
                }

                $min = floatval($min_raw);
                $max = floatval($max_raw);

                if ($min > $max) {
                    $_SESSION['error'] = 'El límite inferior de "' . $parametro_id . '" no puede ser mayor al límite superior.';
                    header("Location: " . BASE_URL . "modulos/clientesform.php?error_parametro=" . $parametro_id);
                    exit();
                } else if (isset($parametros_referencia[$parametro_id])) {
                    $ref = $parametros_referencia[$parametro_id];
                    if ($min < $ref['min'] || $max > $ref['max']) {
                        $_SESSION['error'] = 'El parámetro "' . $parametro_id . '" debe estar dentro del rango internacional (' . $ref['min'] . ' - ' . $ref['max'] . ').';
                        header("Location: " . BASE_URL . "modulos/clientesform.php?error_parametro=" . $parametro_id);
                        exit;
                    } else {
                        // Guardamos el dato validado en caso de que esté dentro de los límites. 
                        $datos_validados[$parametro_id] = [
                            'min' => $min,
                            'max' => $max
                        ];
                    }
                }
            }
        } else if ($tipo == 'Farinógrafo' && isset($_POST['farinografo'])) {

            $sql_tipo_equipo = "SELECT p.* 
            FROM Parametros p
            JOIN Equipos_Laboratorio e ON p.id_equipo = e.id_equipo
            WHERE e.clave = 'FAR-INT' AND p.id_cliente IS NULL;";

            $stmt_parametros_int = $pdo->prepare($sql_tipo_equipo);
            $stmt_parametros_int->execute();
            $parametros_int = $stmt_parametros_int->fetchAll(PDO::FETCH_ASSOC);

            $parametros_referencia = [];
            foreach ($parametros_int as $p) {
                $parametros_referencia[$p['nombre_parametro']] = [
                    'min' => $p['lim_Inferior'],
                    'max' => $p['lim_Superior']
                ];
            }

            foreach ($_POST['farinografo'] as $parametro_id => $valores) {
                $min_raw = test_data($valores['min']);
                $max_raw = test_data($valores['max']);

                if (!is_numeric($min_raw) || !is_numeric($max_raw)) {
                    $_SESSION['error'] = 'Los valores de límite para el parámetro "' . $parametro_id . '" deben ser numéricos.';
                    header("Location: " . BASE_URL . "modulos/clientesform.php?error_parametro=" . $parametro_id);
                }

                $min = floatval($min_raw);
                $max = floatval($max_raw);

                if ($min > $max) {
                    $_SESSION['error'] = 'El límite inferior de "' . $parametro_id . '" no puede ser mayor al límite superior.';
                    header("Location: " . BASE_URL . "modulos/clientesform.php?error_parametro=" . $parametro_id);

                } else if (isset($parametros_referencia[$parametro_id])) {
                    $ref = $parametros_referencia[$parametro_id];
                    if ($min < $ref['min'] || $max > $ref['max']) {

                        $_SESSION['error'] = 'El parámetro "' . $parametro_id . '" debe estar dentro del rango internacional (' . $ref['min'] . ' - ' . $ref['max'] . ').';
                        header("Location: " . BASE_URL . "modulos/clientesform.php?error_parametro=" . $parametro_id);
                        exit;
                    }
                } else {
                    // Guardamos el dato validado en caso de que esté dentro de los límites. 
                    $datos_validados[$parametro_id] = [
                        'min' => $min,
                        'max' => $max
                    ];
                }
            }
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
            header("Location: " . BASE_URL . "modulos/clientesform.php");
            exit;
        }

        // Recuperar el id del ultimo cliente 
        $ultimo_id = $pdo->lastInsertId();

        // Insertamos los datos una vez que los validamos
        foreach ($datos_validados as $parametro_id => $dato) {
            $sql_parametros = "INSERT INTO Parametros (id_equipo, id_cliente, nombre_parametro, lim_Superior, lim_Inferior) 
            VALUES (NULL, :id_cliente, :parametro, :max, :min)";

            $stmt_parametros = $pdo->prepare($sql_parametros);

            if (!$stmt_parametros->execute([
                ':id_cliente' => $ultimo_id,
                ':parametro' => $parametro_id,
                ':max' => $dato['max'],
                ':min' => $dato['min']
            ])) {
                $_SESSION['error'] = 'Error al insertar parámetros.';
                $pdo->rollBack();
                header("Location: " . BASE_URL . "modulos/clientesform.php");
                exit;
            }
        }
        // Parametros internacionales
    } else {

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
            header("Location: " . BASE_URL . "modulos/clientesform.php");
            exit();
        }
    }

    // Todo salió bien
    $pdo->commit();
    $_SESSION['exito'] = 'Cliente agregado correctamente.';
    header("Location: " . BASE_URL . "modulos/clientes.php?success");
    exit();
} else {
    header("Location: " . BASE_URL . "menu.php");
    exit();
}
