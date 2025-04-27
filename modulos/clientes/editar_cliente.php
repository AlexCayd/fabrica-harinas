<?php
session_start();
include '../../config/conn.php';
include '../../config/functions.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    //Obtener el id del cliente con ayuda de la URL
    $id = $_GET['id'];
    $certificado = test_data($_POST['certificado']);
    $nombre = test_data($_POST['nombre']);
    $correo = test_data($_POST['email']);
    $rfc = test_data($_POST['rfc']);
    $puesto = test_data($_POST['puesto']);
    $nombre_contacto = test_data($_POST['puesto_nombre']);
    $telefono = test_data($_POST['numero-telefonico']);
    $direccion_fiscal = test_data($_POST['direccion-fiscal']);
    $estado = test_data($_POST['estado']);

     // Agregamos validaciones a los datos

    // Verificamos si los campos estan llenos
    if(empty($certificado) || empty($nombre) || empty($rfc) || empty($nombre_contacto) || empty($puesto) || empty($correo) || empty($telefono) || empty($direccion_fiscal) 
    || empty($estado)) {
        $_SESSION['error'] = 'Debes de llenar todos los campos.';
        header("Location: /fabrica-harinas/modulos/clientes_editar.php?id=$id");
        exit;
    }

    // Verificamos si el telefono tiene 10 digitos
    if(strlen($telefono) != 10) {
        $_SESSION['error'] = 'El telefono debe de tener 10 digitos.';
        header("Location: /fabrica-harinas/modulos/clientes_editar.php?id=$id");
        exit;
    }

    // Verificamos si el RFC tiene 13 digitos
    if(strlen($rfc) != 13) {
        $_SESSION['error'] = 'El RFC debe de tener 13 digitos.';
        header("Location: /fabrica-harinas/modulos/clientes_editar.php?id=$id");
        exit;
    }

    // Actualizar los datos del cliente
    $sql = "UPDATE Clientes SET req_certificado = $certificado, nombre = '$nombre', rfc = '$rfc', nombre_contacto = '$nombre_contacto', 
    puesto_contacto = '$puesto', correo_contacto = '$correo', telefono_contacto = '$telefono', 
    direccion_fiscal = '$direccion_fiscal', estado = '$estado' WHERE id_cliente = $id";

    $stmt = $pdo->prepare($sql);

    if ($stmt->execute()) {
        $_SESSION['exito'] = 'Cliente actualizado correctamente.';
    } else {
        $_SESSION['error'] = 'No se pudo actualizar al cliente.';
    }

    // Actualizar los datos de los parametros
    if ($parametros == 'Personalizados') {
        // Primero eliminar los parámetros existentes para este cliente
        $sql_delete = "DELETE FROM Parametros WHERE id_cliente = $id";
        $stmt_delete = $pdo->prepare($sql_delete);
        $stmt_delete->execute();

        if ($tipo == 'Alveógrafo') {
            // Insertar parámetros de Alveógrafo
            foreach ($_POST['alveografo'] as $parametro_id => $valores) {
                $min = test_data($valores['min']);
                $max = test_data($valores['max']);

                $sql_parametros = "INSERT INTO Parametros (id_cliente, nombre_parametro, tipo, lim_Inferior, lim_Superior) 
                                 VALUES ($id, :parametro, 'Personalizado', :min, :max)";

                $stmt_parametros = $pdo->prepare($sql_parametros);
                $stmt_parametros->bindParam(':parametro', $parametro_id);
                $stmt_parametros->bindParam(':min', $min);
                $stmt_parametros->bindParam(':max', $max);

                if ($stmt_parametros->execute()) {
                    $_SESSION['mensaje'] = 'exito';
                    $_SESSION['texto'] = 'Parámetros actualizados correctamente.';

                } else {
                    $_SESSION['mensaje'] = 'error';
                    $_SESSION['texto'] = 'No se pudo actualizar los parámetros.';

                }
            }
        } else if ($tipo == 'Farinógrafo') {
            // Insertar parámetros de Farinógrafo
            foreach ($_POST['farinografo'] as $parametro_id => $valores) {
                $min = test_data($valores['min']);
                $max = test_data($valores['max']);

                $sql_parametros = "INSERT INTO Parametros (id_cliente, nombre_parametro, tipo, lim_Inferior, lim_Superior) 
                                 VALUES ($id, :parametro, 'Personalizado', :min, :max)";

                $stmt_parametros = $pdo->prepare($sql_parametros);
                $stmt_parametros->bindParam(':parametro', $parametro_id);
                $stmt_parametros->bindParam(':min', $min);
                $stmt_parametros->bindParam(':max', $max);

                if ($stmt_parametros->execute()) {
                    $_SESSION['mensaje'] = 'exito';
                    $_SESSION['texto'] = 'Parámetros actualizados correctamente.';
                } else {
                    $_SESSION['mensaje'] = 'error';
                    $_SESSION['texto'] = 'No se pudo actualizar los parámetros.';
                }
            }
        }
    }


    header("Location: /fabrica-harinas/modulos/clientes.php");
    exit;
} else {
    header("Location: /fabrica-harinas/menu.php");
    exit;
}
