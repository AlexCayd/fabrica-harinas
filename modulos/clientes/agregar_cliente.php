<?php

include '../../config/conn.php';
include '../../config/functions.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $certificado = test_data($_POST['certificado']);
    $nombre = test_data($_POST['nombre']);
    $correo = test_data($_POST['email']);
    $rfc = test_data($_POST['rfc']);
    $puesto = test_data($_POST['puesto']);
    $telefono = test_data($_POST['numero-telefonico']);
    $direccion_fiscal = test_data($_POST['direccion-fiscal']);
    $parametros = test_data($_POST['parametros']);
    $estado = test_data($_POST['categoria']);
    $tipo = test_data($_POST['tipo_equipo']);

    $sql = "INSERT INTO Clientes (req_certificado, nombre, rfc, nombre_contacto, puesto_contacto, correo_contacto, telefono_contacto,
    direccion_fiscal, estado, parametros) VALUES ($certificado, '$nombre', '$rfc', '$nombre', '$puesto', 
    '$correo', '$telefono', '$direccion_fiscal', '$estado', '$parametros')";

    $stmt = $pdo->prepare($sql);
    $stmt->execute();

    // Obtener el ID del último registro insertado
    $ultimo_id = $pdo->lastInsertId();

    if ($parametros == 'Personalizados') {
        echo "Entra";
        echo $tipo;
        if ($tipo == 'Alveografo') {
            echo "Entra alveografo";
            foreach ($_POST['alveografo'] as $parametro_id => $valores) {
                $min = test_data($valores['min']);
                $max = test_data($valores['max']);

                // Insertar cada parámetro en la tabla Parametros
                $sql_parametros = "INSERT INTO Parametros (id_equipo, id_cliente, nombre_parametro, tipo, lim_Superior, lim_Inferior) 
                             VALUES (NULL, :id_cliente, :parametro, 'Personalizado',  :max, :min)";

                $stmt_parametros = $pdo->prepare($sql_parametros);
                $stmt_parametros->bindParam(':id_cliente', $ultimo_id);
                $stmt_parametros->bindParam(':parametro', $parametro_id);
                $stmt_parametros->bindParam(':min', $min);
                $stmt_parametros->bindParam(':max', $max);
                $stmt_parametros->execute();
                echo "Entra ";
                header("Location: /fabrica-harinas/modulos/clientes.php?success");

            }
        } else if ($tipo == 'Farinografo') {
            foreach ($_POST['farinografo'] as $parametro_id => $valores) {
                $min = test_data($valores['min']);
                $max = test_data($valores['max']);

                // Insertar cada parámetro en la tabla Parametros
                $sql_parametros = "INSERT INTO Parametros (id_equipo, id_cliente, nombre_parametro, tipo, lim_Superior, lim_Inferior) 
                             VALUES (NULL, :id_cliente, :parametro, 'Personalizado',  :max, :min)";

                $stmt_parametros = $pdo->prepare($sql_parametros);
                $stmt_parametros->bindParam(':id_cliente', $ultimo_id);
                $stmt_parametros->bindParam(':parametro', $parametro_id);
                $stmt_parametros->bindParam(':min', $min);
                $stmt_parametros->bindParam(':max', $max);
                $stmt_parametros->execute();
                header("Location: /fabrica-harinas/modulos/clientes.php?success");

            }
        }
    }

    // header("Location: /fabrica-harinas/modulos/clientes.php?success");
    exit;
} else {

    header("Location: /fabrica-harinas/menu.php");
    exit;
}
