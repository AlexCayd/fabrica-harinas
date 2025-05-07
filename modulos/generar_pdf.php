    <?php
    include_once '../includes/config.php';
    include '../fpdf186/fpdf.php';
    session_start();
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);

    // Determinamos el numero parametros aprobados y desaprobados
    $aprobados = 0;
    $desaprobados = 0;

    // Obtenemos el id de la inspección
    $id_inspeccion = $_GET['id'];

    // Preparamos y ejecutamos la consulta
    $stmt = $pdo->prepare("SELECT DISTINCT 
        i.lote, 
        i.clave,
        i.id_equipo,
        i.id_inspeccion, 
        c.nombre, 
        ce.cantidad_solicitada, 
        ce.cantidad_recibida,
        ce.fecha_caducidad,
        ce.numero_factura,
        ce.numero_orden_compra,
        ce.fecha_envio,
        ri.aprobado,
        i.fecha_inspeccion,
        c.tipo_equipo,
        c.parametros,
        c.id_cliente
    FROM Inspeccion i
    INNER JOIN Clientes c ON i.id_cliente = c.id_cliente
    INNER JOIN Resultado_Inspeccion ri ON ri.id_inspeccion = i.id_inspeccion
    INNER JOIN Certificados ce ON ce.id_inspeccion = i.id_inspeccion
    WHERE i.id_inspeccion = ?");
    $stmt->execute([$id_inspeccion]);

    $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
    // Obtenemos el id del cliente de la consulta hecha
    $id_cliente = $resultado['id_cliente'];

    // Paso previo: obtener id_equipo
    $stmtEquipo = $pdo->prepare("SELECT id_equipo FROM Inspeccion WHERE id_inspeccion = ?");
    $stmtEquipo->execute([$id_inspeccion]);
    $id_equipo = $stmtEquipo->fetchColumn();

    $parametros = $resultado['parametros'];
    $tipo_equipo = $resultado['tipo_equipo'];

    if ($parametros == 'Personalizados') {

        $sql_personalizados = "SELECT      
        i.id_cliente,     
        ri.nombre_parametro,     
        ri.valor_obtenido,     
        ri.aprobado,
        p.lim_Superior,     
        p.lim_Inferior FROM Inspeccion i 
        JOIN Resultado_Inspeccion ri ON ri.id_inspeccion = i.id_inspeccion 
        JOIN Parametros p ON p.id_cliente = i.id_cliente AND p.nombre_parametro = ri.nombre_parametro 
        WHERE i.id_inspeccion = ?";

        $stmt_personalizados = $pdo->prepare($sql_personalizados);
        $stmt_personalizados->execute([$id_inspeccion]);
        $resultados_inspeccion_personalizados = $stmt_personalizados->fetchAll(PDO::FETCH_ASSOC);

        foreach ($resultados_inspeccion_personalizados as $row) {
            $row['aprobado'] == '1' ? $aprobados++ : $desaprobados++;
        }
    } else if ($parametros == 'Internacionales') {

        $sql_tipo_equipo = "";
        if ($tipo_equipo == 'Alveógrafo') {

            $sql_tipo_equipo = "SELECT p.*
                FROM Parametros p
                JOIN Equipos_Laboratorio e ON p.id_equipo = e.id_equipo
                WHERE e.clave = 'ALV-INT' AND p.id_cliente IS NULL;";
        } else {

            $sql_tipo_equipo = "SELECT p.*
                FROM Parametros p
                JOIN Equipos_Laboratorio e ON p.id_equipo = e.id_equipo
                WHERE e.clave = 'FAR-INT' AND p.id_cliente IS NULL;";
        }

        // Obtenemos los parámetros obtenidos en la inspección
        $sql_parametros_obtenidos = "SELECT ri.nombre_parametro, ri.valor_obtenido, ri.aprobado 
        FROM Resultado_Inspeccion ri 
        WHERE id_inspeccion = ?";
        $stmt_parametros_obtenidos = $pdo->prepare($sql_parametros_obtenidos);
        $stmt_parametros_obtenidos->execute([$id_inspeccion]);
        $resultados_obtenidos = $stmt_parametros_obtenidos->fetchAll(PDO::FETCH_ASSOC);

        // Convertimos a formato clave-valor para fácil acceso
        $parametros_obtenidos = [];
        foreach ($resultados_obtenidos as $result) {
            $parametros_obtenidos[$result['nombre_parametro']] = [
                'valor_obtenido' => $result['valor_obtenido'],
                'aprobado' => $result['aprobado']
            ];
        }

        // Obtenemos los parámetros internacionales
        $stmt_tipo_equipo = $pdo->prepare($sql_tipo_equipo);
        $stmt_tipo_equipo->execute();
        $parametros_internacionales = $stmt_tipo_equipo->fetchAll(PDO::FETCH_ASSOC);

        // Combinamos los arrays
        $resultado_final = [];
        foreach ($parametros_internacionales as $parametro) {
            $nombre_param = $parametro['nombre_parametro'];

            $registro_combinado = $parametro; // Copiamos todos los datos del parámetro

            // Añadimos los valores obtenidos si existen
            if (isset($parametros_obtenidos[$nombre_param])) {
                $registro_combinado['valor_obtenido'] = $parametros_obtenidos[$nombre_param]['valor_obtenido'];
                $registro_combinado['aprobado'] = $parametros_obtenidos[$nombre_param]['aprobado'];
            } else {
                $registro_combinado['valor_obtenido'] = null;
                $registro_combinado['aprobado'] = null;
            }

            $resultado_final[] = $registro_combinado;
        }

        foreach ($resultado_final as $row) {
            $row['aprobado'] == '1' ? $aprobados++ : $desaprobados++;
        }
    }

    // Verificamos que haya resultados
    if (count($resultado) > 0) {

        class PDF extends FPDF
        {
            // Colores
            private $primaryColor = array(235, 222, 208); // #EBDED0
            private $secondaryColor = array(76, 51, 37);  // #4c3325
            private $whiteColor = array(255, 255, 255);   // #FFFFFF


            function __construct()
            {
                parent::__construct();
                // Establecemos la codificación UTF-8
                $this->SetAutoPageBreak(true, 20);
                $this->SetFont('Arial', '', 12);
            }

            // Encabezado
            function Header()
            {
                // Logo
                $this->Image('../img/harinas.png', 10, 10, 30, 30, 'PNG');

                // Establecemos los colores
                $this->SetFillColor($this->primaryColor[0], $this->primaryColor[1], $this->primaryColor[2]);
                $this->SetTextColor($this->secondaryColor[0], $this->secondaryColor[1], $this->secondaryColor[2]);

                // Titulo
                $this->SetFont('Arial', 'B', 20);
                $this->Cell(0, 15, mb_convert_encoding('Certificado de Inspección', 'ISO-8859-1', 'UTF-8'), 0, 1, 'C');

                // Linea
                $this->SetDrawColor($this->secondaryColor[0], $this->secondaryColor[1], $this->secondaryColor[2]);
                $this->SetLineWidth(0.5);
                $this->Line(10, 45, 200, 45);

                // espacio despues del encabezado
                $this->Ln(10);
            }

            // Pie de página
            function Footer()
            {
                // Posicion a 1.5 cm desde el fondo
                $this->SetY(-15);

                // Establecemos los colores
                $this->SetTextColor($this->secondaryColor[0], $this->secondaryColor[1], $this->secondaryColor[2]);

                // Establecemos la fuente
                $this->SetFont('Arial', 'I', 8);

                // Numero de pagina
                $this->Cell(0, 10, mb_convert_encoding('Página ' . $this->PageNo() . '/{nb}', 'ISO-8859-1', 'UTF-8'), 0, 0, 'C');

                // Linea
                $this->SetDrawColor($this->secondaryColor[0], $this->secondaryColor[1], $this->secondaryColor[2]);
                $this->SetLineWidth(0.5);
                $this->Line(10, 280, 200, 280);
            }

            // Metodo para agregar contenido con un estilo consistente
            function AddContent($text, $fontSize = 12, $align = 'L')
            {
                $this->SetFont('Arial', '', $fontSize);
                $this->SetTextColor($this->secondaryColor[0], $this->secondaryColor[1], $this->secondaryColor[2]);
                $this->MultiCell(0, 10, mb_convert_encoding($text, 'ISO-8859-1', 'UTF-8'), 0, $align);
            }
        }

        function StyledRow($pdf, $label, $value)
        {
            $pdf->SetFillColor(255, 255, 255); // Fondo blanco para el valor
            $pdf->SetTextColor(76, 51, 37);    // Texto oscuro
            $pdf->SetFont('Arial', 'B', 11);
            $pdf->Cell(60, 10, mb_convert_encoding($label, 'ISO-8859-1', 'UTF-8'), 0, 0, 'L', true);

            $pdf->SetFont('Arial', '', 11);
            $pdf->SetFillColor(255, 255, 255); // Fondo blanco para el valor
            $pdf->Cell(0, 10, mb_convert_encoding($value, 'ISO-8859-1', 'UTF-8'), 0, 1, 'L', true);
        }

        function StyledDoubleRow($pdf, $label1, $value1, $label2, $value2)
        {
            // Estilos para las etiquetas
            $pdf->SetFillColor(255, 255, 255); // Fondo blanco para el valor
            $pdf->SetTextColor(76, 51, 37);    // Texto oscuro
            $pdf->SetFont('Arial', 'B', 11);
            $pdf->Cell(45, 10, mb_convert_encoding($label1, 'ISO-8859-1', 'UTF-8'), 0, 0, 'L', true);

            // Valor 1
            $pdf->SetFillColor(255, 255, 255); // Fondo blanco
            $pdf->SetTextColor(0, 0, 0); // Texto negro
            $pdf->SetFont('Arial', '', 11);
            $pdf->Cell(45, 10, mb_convert_encoding($value1, 'ISO-8859-1', 'UTF-8'), 0, 0, 'L', true);

            // Etiqueta 2
            $pdf->SetFillColor(255, 255, 255); // Fondo blanco para el valor
            $pdf->SetTextColor(76, 51, 37);    // Texto oscuro
            $pdf->SetFont('Arial', 'B', 11);
            $pdf->Cell(45, 10, mb_convert_encoding($label2, 'ISO-8859-1', 'UTF-8'), 0, 0, 'L', true);

            // Valor 2
            $pdf->SetFillColor(255, 255, 255);
            $pdf->SetTextColor(0, 0, 0);
            $pdf->SetFont('Arial', '', 11);
            $pdf->Cell(0, 10, mb_convert_encoding($value2, 'ISO-8859-1', 'UTF-8'), 0, 1, 'L', true);
        }


        // Inicializamos FPDF
        $pdf = new PDF();
        $pdf->AliasNbPages(); // For page numbers
        $pdf->AddPage();

        // Title and basic info
        $pdf->SetFont('Arial', 'B', 16);
        $pdf->Cell(0, 10, mb_convert_encoding('Información del Certificado', 'ISO-8859-1', 'UTF-8'), 0, 1, 'C');
        $pdf->Ln(5);

        StyledRow($pdf, 'Lote de producción:', $resultado['lote']);
        StyledRow($pdf, 'Cliente:', $resultado['nombre']);
        StyledRow($pdf, 'Clave de inspección:', $resultado['clave']);
        StyledRow($pdf, 'Número de orden de compra:', $resultado['numero_orden_compra']);
        StyledRow($pdf, 'Número de factura:', $resultado['numero_factura']);

        StyledDoubleRow($pdf, 'Cantidad solicitada:', $resultado['cantidad_solicitada'] . ' kg', 'Cantidad recibida:', $resultado['cantidad_recibida'] . ' kg');
        StyledDoubleRow($pdf, 'Fecha de envío:', $resultado['fecha_envio'], 'Fecha de caducidad:', $resultado['fecha_caducidad']);


        if ($resultado['parametros'] == 'Personalizados') {
            foreach ($resultados_inspeccion_personalizados as $resultado_inspeccion) {
                // Fuente para el nombre del parámetro
                $pdf->SetFont('Arial', 'B', 12);
                $pdf->SetTextColor(0, 0, 0); // Negro
                $pdf->Cell(80, 10, mb_convert_encoding($resultado_inspeccion['nombre_parametro'], 'ISO-8859-1', 'UTF-8'), 0, 0);

                // Cambiamos color según resultado
                if ($resultado_inspeccion['aprobado'] == 1) {
                    $pdf->SetTextColor(0, 128, 0); // Verde
                } else {
                    $pdf->SetTextColor(255, 0, 0); // Rojo
                }

                // Valor obtenido
                $pdf->SetFont('Arial', '', 12);
                $valor = floatval($resultado_inspeccion['valor_obtenido']);
                $pdf->Cell(40, 10, $valor, 0, 0);

                // Referencia (gris, más pequeño)
                $pdf->SetTextColor(100, 100, 100);
                $pdf->SetFont('Arial', 'I', 10);
                $lim_inf = floatval($resultado_inspeccion['lim_Inferior']);
                $lim_sup = floatval($resultado_inspeccion['lim_Superior']);
                $referencia = 'Referencia: ' . $lim_inf . ' - ' . $lim_sup;

                // Agregar desviación si no está aprobado
                if ($resultado_inspeccion['aprobado'] != 1) {
                    if ($valor < $lim_inf) {
                        $desviacion = $lim_inf - $valor;
                    } elseif ($valor > $lim_sup) {
                        $desviacion = $valor - $lim_sup;
                    } else {
                        $desviacion = 0; // No debería suceder si está reprobado, pero por seguridad
                    }
                    $referencia .= ' | Desviación: ' . number_format($desviacion, 2);
                }

                $pdf->Cell(0, 10, mb_convert_encoding($referencia, 'ISO-8859-1', 'UTF-8'), 0, 1);
            }
        } else {
            foreach ($resultado_final as $parametro) {
                // Fuente para el nombre del parámetro
                $pdf->SetFont('Arial', 'B', 12);
                $pdf->SetTextColor(0, 0, 0); // Negro
                $pdf->Cell(80, 10, mb_convert_encoding($parametro['nombre_parametro'], 'ISO-8859-1', 'UTF-8'), 0, 0);

                // Cambiamos color según resultado
                if ($parametro['aprobado'] == 1) {
                    $pdf->SetTextColor(0, 128, 0); // Verde
                } else {
                    $pdf->SetTextColor(255, 0, 0); // Rojo
                }

                // Valor obtenido
                $pdf->SetFont('Arial', '', 12);
                $valor = floatval($parametro['valor_obtenido']);
                $pdf->Cell(40, 10, $valor, 0, 0);

                // Referencia (gris, más pequeño)
                $pdf->SetTextColor(100, 100, 100);
                $pdf->SetFont('Arial', 'I', 10);
                $lim_inf = floatval($parametro['lim_Inferior']);
                $lim_sup = floatval($parametro['lim_Superior']);
                $referencia = 'Referencia: ' . $lim_inf . ' - ' . $lim_sup;

                // Agregar desviación si no está aprobado
                if ($parametro['aprobado'] != 1) {
                    if ($valor < $lim_inf) {
                        $desviacion = $lim_inf - $valor;
                    } elseif ($valor > $lim_sup) {
                        $desviacion = $valor - $lim_sup;
                    } else {
                        $desviacion = 0; // Seguridad
                    }
                    $referencia .= ' | Desviación: ' . number_format($desviacion, 2);
                }

                $pdf->Cell(0, 10, mb_convert_encoding($referencia, 'ISO-8859-1', 'UTF-8'), 0, 1);
            }
        }
        $pdf->SetTextColor(0, 0, 0); // Reset to default color

        // Rsultados de la prueba
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(60, 10, mb_convert_encoding('Resultado de la prueba:', 'ISO-8859-1', 'UTF-8'), 0, 0);
        $pdf->SetFont('Arial', '', 12);
        $pdf->Cell(0, 10, $desaprobados > 0 ? mb_convert_encoding('Desaprobado', 'ISO-8859-1', 'UTF-8') : mb_convert_encoding('Aprobado', 'ISO-8859-1', 'UTF-8'), 0, 1);

        $pdf->Ln(5);

        // Informacion adicional
        $pdf->AddContent('Este certificado es un documento oficial que acredita los resultados de la inspección realizada.', 10, 'C');
        $pdf->AddContent('Fecha de inspección: ' . $resultado['fecha_inspeccion'], 10, 'R');


        // $pdf->Output('F', $ruta_guardado);

        // $pdf->Output('D', 'Certificado_de_inspeccion_' . mb_convert_encoding($resultado['nombre'], 'ISO-8859-1', 'UTF-8') . '.pdf');

        // Proceso para guardar el pdf en el servidor
        $pdf_string = $pdf->Output('S'); // 'S' => return as string

        // Guardar el pdf en el servidor
        $ruta_guardado = '../certificados/Certificado_de_inspeccion_' .
            mb_convert_encoding($resultado['nombre'], 'ISO-8859-1', 'UTF-8') .
            '.pdf';
        file_put_contents($ruta_guardado, $pdf_string);

        // Forzar descarga en navegador
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="Certificado_de_inspeccion_' .
            mb_convert_encoding($resultado['nombre'], 'ISO-8859-1', 'UTF-8') .
            '.pdf"');
        echo $pdf_string;
        exit;
    } else {
        echo "No se encontró la inspección con ID $id_inspeccion.";
    }
