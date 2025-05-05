<?php

include '../config/conn.php';
include '../fpdf186/fpdf.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Obtenemos el id de la inspección
$id_inspeccion = $_GET['id'];

// Preparamos y ejecutamos la consulta
$stmt = $pdo->prepare("SELECT DISTINCT 
    i.lote, 
    i.clave,
    i.id_inspeccion, 
    c.nombre, 
    ce.cantidad_solicitada, 
    ce.cantidad_recibida,
    ri.aprobado,
    i.fecha_inspeccion
FROM Inspeccion i
INNER JOIN Clientes c ON i.id_cliente = c.id_cliente
INNER JOIN Resultado_Inspeccion ri ON ri.id_inspeccion = i.id_inspeccion
INNER JOIN Certificados ce ON ce.id_inspeccion = i.id_inspeccion
WHERE i.id_inspeccion = ?");
$stmt->execute([$id_inspeccion]);

$resultado = $stmt->fetch(PDO::FETCH_ASSOC);

// Recueperamos los resultados de la inspeccion realizada.
$stmt2 = $pdo->prepare("SELECT * FROM Resultado_Inspeccion WHERE id_inspeccion = ?");
$stmt2->execute([$id_inspeccion]);
$resultados_inspeccion = $stmt2->fetchAll(PDO::FETCH_ASSOC);

$desaprobados = 0;
$aprobados = 0;

// Determinamos el numero parametros aprobados y desaprobados
$aprobados = 0;
$desaprobados = 0;
foreach ($resultados_inspeccion as $row) {
    $row['aprobado'] == '1' ? $aprobados++ : $desaprobados++;
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
            $this->Cell(0, 15, utf8_decode('Certificado de Inspección'), 0, 1, 'C');

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
            $this->Cell(0, 10, utf8_decode('Página ') . $this->PageNo() . '/{nb}', 0, 0, 'C');

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
            $this->MultiCell(0, 10, utf8_decode($text), 0, $align);
        }
    }

    // Inicializamos FPDF
    $pdf = new PDF();
    $pdf->AliasNbPages(); // For page numbers
    $pdf->AddPage();

    // Title and basic info
    $pdf->SetFont('Arial', 'B', 16);
    $pdf->Cell(0, 10, utf8_decode('Información del Certificado'), 0, 1, 'C');
    $pdf->Ln(5);

    // Client and Production Information
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(60, 10, utf8_decode('Lote de producción:'), 0, 0);
    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(0, 10, utf8_decode($resultado['lote']), 0, 1);

    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(60, 10, utf8_decode('Cliente:'), 0, 0);
    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(0, 10, utf8_decode($resultado['nombre']), 0, 1);

    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(60, 10, utf8_decode('Clave de inspección:'), 0, 0);
    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(0, 10, utf8_decode($resultado['clave']), 0, 1);

    // Quantity Information
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(60, 10, utf8_decode('Cantidad solicitada:'), 0, 0);
    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(0, 10, utf8_decode($resultado['cantidad_solicitada']) . ' kg', 0, 1);

    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(60, 10, utf8_decode('Cantidad recibida:'), 0, 0);
    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(0, 10, utf8_decode($resultado['cantidad_recibida']) . ' kg', 0, 1);

    // Agregamos todos los parametros de la inspeccion
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(60, 10, utf8_decode('Fecha de inspección:' . $resultado['fecha_inspeccion']), 0, 0);
    $pdf->Ln(10);
    foreach ($resultados_inspeccion as $resultado_inspeccion) {
        $pdf->SetFont('Arial', '', 12);

        if($resultado_inspeccion['aprobado'] == 1){
            $pdf->SetTextColor(0, 128, 0); // Green
        } else {
            $pdf->SetTextColor(255, 0, 0); // Red
        }

        // Nombre del parámetro (alineado a la izquierda, 80mm de ancho)
        $pdf->Cell(80, 10, utf8_decode($resultado_inspeccion['nombre_parametro']), 0, 0);

        // Valor obtenido (alineado a la derecha, 40mm de ancho)
        $pdf->Cell(40, 10, $resultado_inspeccion['valor_obtenido'], 0, 1);
    }
    $pdf->SetTextColor(0, 0, 0); // Reset to default color

    // Rsultados de la prueba
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(60, 10, utf8_decode('Resultado de la prueba:'), 0, 0);
    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(0, 10, $desaprobados > 0 ? utf8_decode('Desaprobado') : utf8_decode('Aprobado'), 0, 1);

    $pdf->Ln(10);

    // Additional Information
    $pdf->AddContent('Este certificado es un documento oficial que acredita los resultados de la inspección realizada.', 10, 'C');
    $pdf->AddContent('Fecha de emisión: ' . date('d/m/Y'), 10, 'R');

    $pdf->Output('D', 'Certificado_de_inspeccion_'.utf8_decode($resultado['nombre']).'.pdf');
} else {
    echo "No se encontró la inspección con ID $id_inspeccion.";
}
