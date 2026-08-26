<?php

namespace App\Http\Controllers;

use App\Models\Certificado;
use App\Models\CertificadoDetalle;
use App\Models\Cliente;
use App\Models\Configuracion;
use App\Models\HistorialAccion;
use App\Models\Inscripcion;
use App\Models\Pago;
use App\Models\TipoCertificado;
use App\Models\User;
use App\Services\PagoService;
use App\Services\ReporteService;
use App\Services\ReporteServiceTcpdf;
use App\Services\TipoPagoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use PDF;
use Carbon\Carbon;
use FPDF;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;

class ReporteController extends Controller
{
    public $titulo = [
        'font' => [
            'bold' => true,
            'size' => 12,
            'family' => 'Times New Roman'
        ],
        'borders' => [
            'allBorders' => [
                'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_NONE,
            ],
        ],
    ];

    public $textoBold = [
        'font' => [
            'bold' => true,
            'size' => 10,
        ],
    ];

    public $headerTabla = [
        'font' => [
            'bold' => true,
            'size' => 10,
            'color' => ['argb' => 'ffffff'],
        ],
        'alignment' => [
            'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
        ],
        'borders' => [
            'allBorders' => [
                'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
            ],
        ],
        'fill' => [
            'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
            'color' => ['rgb' => '203764']
        ],
    ];

    public $headerTablaRed = [
        'font' => [
            'bold' => true,
            'size' => 10,
            'color' => ['argb' => 'ffffff'],
        ],
        'alignment' => [
            'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
        ],
        'borders' => [
            'allBorders' => [
                'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
            ],
        ],
        'fill' => [
            'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
            'color' => ['rgb' => 'f02222']
        ],
    ];

    public $headerTabla2 = [
        'font' => [
            'bold' => true,
            'size' => 10,
            'color' => ['argb' => '000000'],
        ],
        'alignment' => [
            'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
        ],
        'borders' => [
            'allBorders' => [
                'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
            ],
        ],
    ];

    public $bodyTabla = [
        'font' => [
            'size' => 10,
        ],
        'alignment' => [
            'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
            // 'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
        ],
        'borders' => [
            'allBorders' => [
                'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
            ],
        ],
    ];

    public $textLeft = [
        'alignment' => [
            'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT,
        ],
    ];

    public $textRight = [
        'alignment' => [
            'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT,
        ],
    ];

    public $textCenter = [
        'alignment' => [
            'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
        ],
    ];

    public $bg0 = [
        'fill' => [
            'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
            'color' => ['rgb' => 'cff3f3']
        ],
    ];

    public $bg1 = [
        'fill' => [
            'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
            'color' => ['rgb' => 'ffe9ff']
        ],
    ];

    public $bg2 = [
        'fill' => [
            'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
            'color' => ['rgb' => 'f7ffe0']
        ],
    ];

    public $bg3 = [
        'fill' => [
            'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
            'color' => ['rgb' => 'ecfcdd']
        ],
    ];

    public $bg4 = [
        'fill' => [
            'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
            'color' => ['rgb' => 'faeee4']
        ],
    ];

    private $configuracion = null;
    public function __construct(private PagoService $pagoService, private TipoPagoService $tipo_pago_service)
    {
        $this->configuracion = Configuracion::first();
        if (!$this->configuracion) {
            $this->configuracion = new Configuracion([
                "nombre_sistema" => "MEDINTER S.A.",
                "alias" => "MD",
                "logo" => "logo.png",
                "fono" => "2222222",
                "dir" => "LOS OLIVOS",
            ]);
        }
    }

    public function usuarios()
    {
        return Inertia::render("Admin/Reportes/Usuarios");
    }

    public function r_usuarios(Request $request)
    {
        ini_set('memory_limit', '1024M');
        set_time_limit(-1);
        $tipo =  $request->tipo;
        $formato =  $request->formato;
        $usuarios = User::select("users.*")
            ->where('id', '!=', 1);

        if ($tipo != 'todos') {
            $request->validate([
                'tipo' => 'required',
            ]);
            $usuarios->where('tipo', $tipo);
        }

        $usuarios = $usuarios->get();

        if ($formato == 'pdf') {
            $pdf = PDF::loadView('reportes.usuarios', compact('usuarios'))->setPaper('legal', 'landscape');

            // ENUMERAR LAS PÁGINAS USANDO CANVAS
            $pdf->output();
            $dom_pdf = $pdf->getDomPDF();
            $canvas = $dom_pdf->get_canvas();
            $alto = $canvas->get_height();
            $ancho = $canvas->get_width();
            $canvas->page_text($ancho - 90, $alto - 25, "Página {PAGE_NUM} de {PAGE_COUNT}", null, 9, array(0, 0, 0));

            return $pdf->stream('usuarios.pdf');
        } else {
            $spreadsheet = new Spreadsheet();
            $spreadsheet->getProperties()
                ->setCreator("ADMIN")
                ->setLastModifiedBy('Administración')
                ->setTitle('Registros')
                ->setSubject('Registros')
                ->setDescription('Registros')
                ->setKeywords('PHPSpreadsheet')
                ->setCategory('Listado');

            $sheet = $spreadsheet->getActiveSheet();

            $spreadsheet->getDefaultStyle()->getFont()->setName('Arial');

            $fila = 1;
            if (file_exists(public_path() . '/imgs/' . $this->configuracion->logo)) {
                $drawing = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
                $drawing->setName('logo');
                $drawing->setDescription('logo');
                $drawing->setPath(public_path() . '/imgs/' . $this->configuracion->logo); // put your path and image here
                $drawing->setCoordinates('A' . $fila);
                $drawing->setOffsetX(5);
                $drawing->setOffsetY(0);
                $drawing->setHeight(70);
                $drawing->setWorksheet($sheet);
            }

            $fila = 2;
            $sheet->setCellValue('A' . $fila, $this->configuracion->nombre_sistema);
            $sheet->mergeCells("A" . $fila . ":K" . $fila);  //COMBINAR CELDAS
            $sheet->getStyle('A' . $fila . ':K' . $fila)->getAlignment()->setHorizontal('center');
            $sheet->getStyle('A' . $fila . ':K' . $fila)->applyFromArray($this->titulo);
            $fila++;
            $sheet->setCellValue('A' . $fila, "LISTA DE USUARIOS");
            $sheet->mergeCells("A" . $fila . ":K" . $fila);  //COMBINAR CELDAS
            $sheet->getStyle('A' . $fila . ':K' . $fila)->getAlignment()->setHorizontal('center');
            $sheet->getStyle('A' . $fila . ':K' . $fila)->applyFromArray($this->titulo);
            $fila++;
            $fila++;
            $sheet->setCellValue('A' . $fila, 'N°');
            $sheet->setCellValue('B' . $fila, 'USUARIO');
            $sheet->setCellValue('C' . $fila, 'PATERNO');
            $sheet->setCellValue('D' . $fila, 'MATERNO');
            $sheet->setCellValue('E' . $fila, 'NOMBRE(S)');
            $sheet->setCellValue('F' . $fila, 'C.I.');
            $sheet->setCellValue('G' . $fila, 'DIRECCIÓN');
            $sheet->setCellValue('H' . $fila, 'CORREO');
            $sheet->setCellValue('I' . $fila, 'TELÉFONO/CELULAR');
            $sheet->setCellValue('J' . $fila, 'TIPO');
            $sheet->setCellValue('K' . $fila, 'ACCESO');
            $sheet->getStyle('A' . $fila . ':K' . $fila)->applyFromArray($this->headerTabla);
            $fila++;

            foreach ($usuarios as $key => $item) {
                $sheet->setCellValue('A' . $fila, $key + 1);
                $sheet->setCellValue('B' . $fila, $item->usuario);
                $sheet->setCellValue('C' . $fila, $item->paterno);
                $sheet->setCellValue('D' . $fila, $item->materno);
                $sheet->setCellValue('E' . $fila, $item->nombre);
                $sheet->setCellValue('F' . $fila, $item->full_ci);
                $sheet->setCellValue('G' . $fila, $item->dir);
                $sheet->setCellValue('H' . $fila, $item->correo);
                $sheet->setCellValue('I' . $fila, $item->fono);
                $sheet->setCellValue('J' . $fila, $item->tipo);
                $sheet->setCellValue('K' . $fila, $item->acceso == 1 ? 'HABILITADO' : 'DENEGADO');
                $sheet->getStyle('A' . $fila . ':K' . $fila)->applyFromArray($this->bodyTabla);
                $fila++;
            }

            $sheet->getColumnDimension('A')->setWidth(6);
            $sheet->getColumnDimension('B')->setWidth(20);
            $sheet->getColumnDimension('C')->setWidth(15);
            $sheet->getColumnDimension('D')->setWidth(10);
            $sheet->getColumnDimension('E')->setWidth(15);
            $sheet->getColumnDimension('F')->setWidth(20);
            $sheet->getColumnDimension('G')->setWidth(15);
            $sheet->getColumnDimension('H')->setWidth(15);
            $sheet->getColumnDimension('I')->setWidth(15);
            $sheet->getColumnDimension('J')->setWidth(15);
            $sheet->getColumnDimension('K')->setWidth(15);

            foreach (range('A', 'K') as $columnID) {
                $sheet->getStyle($columnID)->getAlignment()->setWrapText(true);
            }

            $sheet->getPageSetup()->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE);
            $sheet->getPageMargins()->setTop(0.5);
            $sheet->getPageMargins()->setRight(0.1);
            $sheet->getPageMargins()->setLeft(0.1);
            $sheet->getPageMargins()->setBottom(0.1);
            $sheet->getPageSetup()->setPrintArea('A:K');
            $sheet->getPageSetup()->setFitToWidth(1);
            $sheet->getPageSetup()->setFitToHeight(0);

            return response()->streamDownload(
                function () use ($spreadsheet) {
                    $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
                    $writer->save('php://output');
                },
                'usuarios_' . time() . '.xlsx',
                [
                    'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                ]
            );
        }
    }

    public function exportarCaja(Request $request)
    {
        ini_set('memory_limit', '1024M');
        set_time_limit(-1);
        $fecha_ini = $request->fecha_ini;
        $fecha_fin = $request->fecha_fin;
        $sucursal_id = $request->sucursal_id;
        $medico_id = $request->medico_id;
        $user_id = $request->user_id;
        $formato = $request->formato;

        $array_res = $this->pagoService->reporteArqueo($fecha_ini, $fecha_fin, $sucursal_id, $medico_id, $user_id);

        $pagos = $array_res[0];
        $suma_tipos = $array_res[1];
        $pagos_sin_verificar = $array_res[2];
        $suma_tipos_sin_verificar = $array_res[3];
        $suma_total_tipos = $array_res[4];

        $tipo_pagos = $this->tipo_pago_service->listado();

        if ($formato == 'pdf') {
            $pdf = new ReporteServiceTcpdf();
            $pdf->SetTitle('Caja');
            $pdf->setMargins(10, 5, 5);
            $pdf->AddPage();
            $pdf->setPrintHeader(false);
            $pdf->setY(13);
            $pdf->SetFont('helvetica', 'B', 12);
            $pdf->Cell(0, 6, "ARQUEO DE CAJA", 0, 1, 'C', 0, '', 0, false);
            $pdf->SetFont('helvetica', 'B', 10);
            $pdf->Cell(0, 5, "Expedido: " . date("d/m/Y"), 0, 1, 'C', 0, '', 0, false);
            $pdf->SetFont('helvetica', 'B', 8);
            $ancho = 20;
            $font_size = 8;
            $font_size2 = 9;

            // Encabezado de tabla
            $pdf->Ln();
            $pdf->SetFont('helvetica', 'B', $font_size2);
            $pdf->SetX(9);
            $pdf->Cell(23, 5, "Elaborado por: ", 0, 0, 'L');

            $pdf->SetFont('helvetica', '', $font_size2);
            $pdf->Cell(40, 5,  Auth::user()->full_name, 0, 0);
            $pdf->Cell(133, 5, "Del " . date('d/m/Y', strtotime($fecha_ini)) . " al " . date('d/m/Y', strtotime($fecha_fin)), 0, 1, 'R');
            if (Auth::user()->tipo == 'MÉDICO' || Auth::user()->tipo == 'SECRETARIA') {
                $user_id = Auth::user()->id;
            }
            if ($user_id) {
                $pdf->SetFont('helvetica', 'B', $font_size2);
                $pdf->SetX(9);
                $pdf->Cell(41, 5, "Pagos recepcionados por: ", 0, 0, 'L');
                $user = User::find($user_id);
                $pdf->SetFont('helvetica', '', $font_size2);
                $pdf->Cell(40, 5, $user->full_name, 0, 1);
            }

            $pdf->SetFont('helvetica', 'B', $font_size);

            $thTipoPagos = '';
            foreach ($tipo_pagos as $item) {
                $thTipoPagos .= '<th style="font-weight:bold;background-color:#153f59;color:white; text-align:right;">' . $item['value'] . ' Bs.</th>';
            }

            $html = '   <table border="1" cellpadding="3">
            <thead class="bg-red" width="100%">
                <tr>
                    <th style="font-weight:bold;background-color:#153f59;color:white;" width="18">N°</th>
                    <th style="font-weight:bold;background-color:#153f59;color:white;" width="50">FECHA</th>
                    <th style="font-weight:bold;background-color:#153f59;color:white;" width="70">SUCURSAL</th>
                    <th style="font-weight:bold;background-color:#153f59;color:white;" width="90">PACIENTE</th>
                    <th style="font-weight:bold;background-color:#153f59;color:white;" width="120">DESCRIPCIÓN</th>
                    <th style="font-weight:bold;background-color:#153f59;color:white;">MÉDICO</th>
                    ' . $thTipoPagos . '
                </tr>
            </thead>
            <tbody>';

            $cont = 1;
            $pdf->SetFont('helvetica', 'N', $font_size);
            foreach ($pagos as $item) {
                $html .= '<tr>';
                $html .= '<td  width="18">' . $cont++ . '</td>';
                $html .= '<td  width="50">' . $item->fecha_verificado_t . '<br/>' . $item->hora_verificado . '</td>';
                $html .= '<td  width="70">' . $item->sucursal->nombre . '</td>';
                $html .= '<td width="90">' . $item->cliente->nombre . ' ' . $item->cliente->paterno . ' ' . $item->cliente->materno . '<br/>' . $item->cliente->full_ci . '</td>';
                $html .= '<td width="120">' . $item->certificado_detalle->tipo_certificado->nombre . '<br/><small style="font-size:6pt">(' . $item->certificado_detalle->certificado->tipo . ($item->certificado_detalle->certificado->tramitador ? ' - ' . $item->certificado_detalle->certificado->tramitador->nombre : '') . '</small>)</td>';
                $html .= '<td>' . ($item->medico ? $item->medico->nombre . ' ' . $item->medico->paterno . ' ' . $item->medico->materno : '') . '</td>';

                $tdTipoPagos = '';
                foreach ($tipo_pagos as $tipo_pago) {
                    $tdTipoPagos .= '<td style="text-align:right;">' . ($item->tipo_pago == $tipo_pago['value'] ? $item->monto : '') . '</td>';
                }

                $html .= $tdTipoPagos . '</tr>';
            }
            $suma_total = 0;
            $html .= '<tr>';
            $html .= '<td style="text-align:right;font-weight:bold;" colspan="6">TOTAL BS.</td>';
            foreach ($tipo_pagos as $tipo_pago) {
                $suma_total += (float) $suma_tipos[$tipo_pago['value']];
                $html .= '<td style="text-align:right;font-weight:bold;">' . number_format($suma_tipos[$tipo_pago["value"]], 2, '.', '') . '</td>';
            }

            $html .= '</tr>';
            $html .= '<tr>';
            $html .= '<td style="text-align:right;font-weight:bold;" colspan="6">TOTAL FINAL BS.</td>';
            $html .= '<td style="text-align:center;font-weight:bold;" colspan="2">' . number_format($suma_total, 2, '.', '') . '</td>';
            $html .= '</tr>';
            $html .= '</tbody></table>';
            $pdf->writeHTML($html, true, false, true, false, '');

            // SIN VERIFICAR
            if (count($pagos_sin_verificar) > 0) {
                $pdf->Ln();
                $pdf->SetFont('helvetica', 'B', 12);
                $pdf->Cell(0, 6, "PAGOS SIN RECEPCIÓN", 0, 1, 'C', 0, '', 0, false);
                $html = '   <table border="1" cellpadding="3">
                <thead class="bg-red" width="100%">
                    <tr>
                        <th style="font-weight:bold;background-color:#f02222;color:white;" width="18">N°</th>
                        <th style="font-weight:bold;background-color:#f02222;color:white;" width="50">FECHA</th>
                        <th style="font-weight:bold;background-color:#f02222;color:white;" width="70">SUCURSAL</th>
                        <th style="font-weight:bold;background-color:#f02222;color:white;">PACIENTE</th>
                        <th style="font-weight:bold;background-color:#f02222;color:white;" width="120">DESCRIPCIÓN</th>
                        <th style="font-weight:bold;background-color:#f02222;color:white;">MÉDICO</th>
                        ' . $thTipoPagos . '
                    </tr>
                </thead>
                <tbody>';

                $cont = 1;
                $suma_total = 0;
                $pdf->SetFont('helvetica', 'N', $font_size);
                foreach ($pagos_sin_verificar as $item) {
                    $html .= '<tr>';
                    $html .= '<td  width="18">' . $cont++ . '</td>';
                    $html .= '<td  width="50">' . $item->fecha_verificado_t . '<br/>' . $item->hora_verificado . '</td>';
                    $html .= '<td  width="70">' . $item->sucursal->nombre . '</td>';
                    $html .= '<td>' . $item->cliente->nombre . ' ' . $item->cliente->paterno . ' ' . $item->cliente->materno . '<br/>' . $item->cliente->full_ci . '</td>';
                    $html .= '<td width="120">' . $item->certificado_detalle->tipo_certificado->nombre . '<br/><small style="font-size:6pt">(' . $item->certificado_detalle->certificado->tipo . ($item->certificado_detalle->certificado->tramitador ? ' - ' . $item->certificado_detalle->certificado->tramitador->nombre : '') . '</small>)</td>';
                    $html .= '<td>' . ($item->medico ? $item->medico->nombre . ' ' . $item->medico->paterno . ' ' . $item->medico->materno : '') . '</td>';

                    $tdTipoPagos = '';
                    foreach ($tipo_pagos as $tipo_pago) {
                        $tdTipoPagos .= '<td style="text-align:right;">' . ($item->tipo_pago == $tipo_pago['value'] ? $item->monto : '') . '</td>';
                    }

                    $html .= $tdTipoPagos . '</tr>';
                }
                $suma_total = 0;
                $html .= '<tr>';
                $html .= '<td style="text-align:right;font-weight:bold;" colspan="6">TOTAL BS.</td>';
                foreach ($tipo_pagos as $tipo_pago) {
                    $suma_total += (float) $suma_tipos_sin_verificar[$tipo_pago['value']];
                    $html .= '<td style="text-align:right;font-weight:bold;">' . number_format($suma_tipos_sin_verificar[$tipo_pago["value"]], 2, '.', '') . '</td>';
                }

                $html .= '</tr>';
                $html .= '<tr>';
                $html .= '<td style="text-align:right;font-weight:bold;" colspan="6">TOTAL FINAL BS.</td>';
                $html .= '<td style="text-align:center;font-weight:bold;" colspan="2">' . number_format($suma_total, 2, '.', '') . '</td>';
                $html .= '</tr>';
                $html .= '</tbody></table>';
                $pdf->writeHTML($html, true, false, true, false, '');

                // resumen
                $pdf->Ln();
                $pdf->SetFont('helvetica', 'B', 12);
                $pdf->Cell(0, 6, "RESUMEN GENERAL", 0, 1, 'C', 0, '', 0, false);

                $html = '<table border="1" cellpadding="3">';
                $html .= '</thead>
                    <tr>
                    <th>TIPO DE PAGO</th>
                    <th>MONTO BS.</th>
                    </tr>
                </thead><tbody>';
                $total_final = 0;

                foreach ($tipo_pagos as $tipo_pago) {
                    $html .= '<tr>';
                    $html .= '<td style="font-weight:normal;">' . $tipo_pago["value"] . '</td>';
                    $html .= '<td style="font-weight:normal;">' . number_format($suma_total_tipos[$tipo_pago['value']], 2, '.', '') . '</td>';
                    $html .= '</tr>';
                    $total_final += (float) $suma_total_tipos[$tipo_pago['value']];
                }
                $html .= '<tr>';
                $html .= '<td>TOTAL GENERAL</td>';
                $html .= '<td>' . number_format($total_final, 2, '.', '') . '</td>';
                $html .= '</tr>';

                $html .= '</tbody></table>';
                $pdf->writeHTML($html, true, false, true, false, '');
            }



            // Guardar PDF o forzar descarga
            return response($pdf->Output('S'), 200)
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'inline; filename="caja.pdf"');
        } else {
            // EXCEL

            $spreadsheet = new Spreadsheet();

            $spreadsheet->getProperties()
                ->setCreator("ADMIN")
                ->setLastModifiedBy('Administración')
                ->setTitle('Registros')
                ->setSubject('Registros')
                ->setDescription('Registros')
                ->setKeywords('PHPSpreadsheet')
                ->setCategory('Listado');

            $sheet = $spreadsheet->getActiveSheet();
            $spreadsheet->getDefaultStyle()->getFont()->setName('Arial');

            $fila = 1;

            /* ===================== LOGO ===================== */
            if (file_exists(public_path() . '/imgs/' . $this->configuracion->logo)) {
                $drawing = new Drawing();
                $drawing->setName('logo');
                $drawing->setDescription('logo');
                $drawing->setPath(public_path() . '/imgs/' . $this->configuracion->logo);
                $drawing->setCoordinates('A' . $fila);
                $drawing->setHeight(70);
                $drawing->setWorksheet($sheet);
            }

            /* ===================== TITULOS ===================== */
            $totalColumnas = 6 + count($tipo_pagos);
            $lastColumn = Coordinate::stringFromColumnIndex($totalColumnas);

            $fila = 2;

            $sheet->setCellValue('A' . $fila, $this->configuracion->nombre_sistema);
            $sheet->mergeCells("A{$fila}:{$lastColumn}{$fila}");
            $sheet->getStyle("A{$fila}:{$lastColumn}{$fila}")
                ->getAlignment()->setHorizontal('center');
            $sheet->getStyle("A{$fila}:{$lastColumn}{$fila}")
                ->applyFromArray($this->titulo);

            $fila++;

            $sheet->setCellValue('A' . $fila, "ARQUEO DE CAJA");
            $sheet->mergeCells("A{$fila}:{$lastColumn}{$fila}");
            $sheet->getStyle("A{$fila}:{$lastColumn}{$fila}")
                ->getAlignment()->setHorizontal('center');
            $sheet->getStyle("A{$fila}:{$lastColumn}{$fila}")
                ->applyFromArray($this->titulo);

            $fila += 2;
            /* ===================== ELABORADO ===================== */
            $sheet->setCellValue('A' . $fila, 'Elaborado por:');
            $sheet->mergeCells("A{$fila}:B{$fila}");
            $sheet->getStyle("A{$fila}:B{$fila}")->applyFromArray($this->textRight);
            $sheet->setCellValue('C' . $fila, Auth::user()->nombre . ' ' . AUth::user()->paterno . ' ' . Auth::user()->materno);
            $sheet->mergeCells("C{$fila}:{$lastColumn}{$fila}");
            $fila++;

            if (Auth::user()->tipo == 'MÉDICO' || Auth::user()->tipo == 'SECRETARIA') {
                $user_id = Auth::user()->id;
            }
            if ($user_id) {
                /* ===================== USUARIO RECEPCION ===================== */
                $sheet->setCellValue('A' . $fila, 'Pagos recepcionados por:');
                $sheet->mergeCells("A{$fila}:B{$fila}");
                $sheet->getStyle("A{$fila}:B{$fila}")->applyFromArray($this->textRight);
                $user = User::find($user_id);
                $sheet->setCellValue('C' . $fila, $user->full_name);
                $sheet->mergeCells("C{$fila}:{$lastColumn}{$fila}");
                $fila++;
            }


            /* ===================== FECHA ===================== */
            $sheet->setCellValue('A' . $fila, 'Fecha:');
            $sheet->mergeCells("A{$fila}:B{$fila}");
            $sheet->getStyle("A{$fila}:B{$fila}")->applyFromArray($this->textRight);

            $sheet->setCellValue('C' . $fila, 'Del ' . date("d/m/Y", strtotime($fecha_ini)) . ' al ' . date("d/m/Y", strtotime($fecha_fin)));
            $sheet->mergeCells("C{$fila}:{$lastColumn}{$fila}");

            $fila++;
            /* ===================== HEADERS ===================== */
            $colIndex = 1;

            $sheet->setCellValue(Coordinate::stringFromColumnIndex($colIndex++) . $fila, 'N°');
            $sheet->setCellValue(Coordinate::stringFromColumnIndex($colIndex++) . $fila, 'FECHA');
            $sheet->setCellValue(Coordinate::stringFromColumnIndex($colIndex++) . $fila, 'SUCURSAL');
            $sheet->setCellValue(Coordinate::stringFromColumnIndex($colIndex++) . $fila, 'PACIENTE');
            $sheet->setCellValue(Coordinate::stringFromColumnIndex($colIndex++) . $fila, 'DESCRIPCIÓN');
            $sheet->setCellValue(Coordinate::stringFromColumnIndex($colIndex++) . $fila, 'MÉDICO');

            /* columnas dinámicas */
            foreach ($tipo_pagos as $tipo) {
                $sheet->setCellValue(
                    Coordinate::stringFromColumnIndex($colIndex++) . $fila,
                    $tipo['value'] . ' Bs.'
                );
            }

            $totalColumnas = $colIndex - 1;
            $lastColumn = Coordinate::stringFromColumnIndex($totalColumnas);

            $sheet->getStyle("A{$fila}:{$lastColumn}{$fila}")
                ->applyFromArray($this->headerTabla);

            $fila++;

            /* ===================== DATA ===================== */
            foreach ($pagos as $key => $item) {
                $colIndex = 1;

                $sheet->setCellValue(Coordinate::stringFromColumnIndex($colIndex++) . $fila, $key + 1);

                $sheet->setCellValue(
                    Coordinate::stringFromColumnIndex($colIndex++) . $fila,
                    $item->fecha_verificado_t . "\n" . $item->hora_verificado
                );

                $sheet->setCellValue(
                    Coordinate::stringFromColumnIndex($colIndex++) . $fila,
                    $item->sucursal->nombre
                );

                $sheet->setCellValue(
                    Coordinate::stringFromColumnIndex($colIndex++) . $fila,
                    $item->cliente->nombre . ' ' .
                        $item->cliente->paterno . ' ' .
                        $item->cliente->materno
                );

                $tramitador_txt = "";
                if ($item->certificado_detalle->certificado->tramitador) {
                    $tramitador_txt = " - " . $item->certificado_detalle->certificado->tramitador->nombre;
                }

                $sheet->setCellValue(
                    Coordinate::stringFromColumnIndex($colIndex++) . $fila,
                    $item->certificado_detalle->tipo_certificado->nombre . "\n(" .
                        $item->certificado_detalle->certificado->tipo . $tramitador_txt . ")"
                );

                if ($item->medico) {
                    $sheet->setCellValue(
                        Coordinate::stringFromColumnIndex($colIndex++) . $fila,
                        $item->medico->nombre . ' ' . $item->medico->paterno
                    );
                } else {
                    $sheet->setCellValue(
                        Coordinate::stringFromColumnIndex($colIndex++) . $fila,
                        ''
                    );
                }

                foreach ($tipo_pagos as $tipo) {
                    $value = ($item->tipo_pago == $tipo['value'])
                        ? number_format($item->monto, 2, ".", "")
                        : '';

                    $sheet->setCellValue(
                        Coordinate::stringFromColumnIndex($colIndex++) . $fila,
                        $value
                    );
                }

                $sheet->getStyle("A{$fila}:{$lastColumn}{$fila}")
                    ->applyFromArray($this->bodyTabla);

                $fila++;
            }

            /* ===================== TOTALES ===================== */
            $sheet->setCellValue("A{$fila}", "TOTAL BS.");
            $sheet->mergeCells("A{$fila}:" . Coordinate::stringFromColumnIndex(6) . "{$fila}");

            $colIndex = 7;
            $suma_total = 0;

            foreach ($tipo_pagos as $tipo) {
                $total = $suma_tipos[$tipo['value']] ?? 0;

                $sheet->setCellValue(
                    Coordinate::stringFromColumnIndex($colIndex++) . $fila,
                    number_format($total, 2, ".", "")
                );

                $suma_total += $total;
            }

            $sheet->getStyle("A{$fila}:{$lastColumn}{$fila}")
                ->applyFromArray($this->headerTabla);

            $fila++;

            /* ===================== TOTAL FINAL ===================== */
            $sheet->setCellValue("A{$fila}", "TOTAL FINAL BS.");
            $sheet->mergeCells("A{$fila}:" . Coordinate::stringFromColumnIndex(6) . "{$fila}");

            $sheet->setCellValue(
                Coordinate::stringFromColumnIndex(7) . $fila,
                number_format($suma_total, 2, ".", "")
            );
            $sheet->getStyle("A{$fila}:{$lastColumn}{$fila}")
                ->applyFromArray($this->headerTabla);

            /*******************************
             *  PAGO SIN VERIFICAR
             ****************************** */
            if (count($pagos_sin_verificar) > 0) {
                $fila++;
                $fila++;
                $sheet->setCellValue('A' . $fila, "PAGOS SIN RECEPCIÓN");
                $sheet->mergeCells("A{$fila}:{$lastColumn}{$fila}");
                $sheet->getStyle("A{$fila}:{$lastColumn}{$fila}")
                    ->getAlignment()->setHorizontal('center');
                $sheet->getStyle("A{$fila}:{$lastColumn}{$fila}")
                    ->applyFromArray($this->titulo);

                $fila++;
                /* ===================== HEADERS ===================== */
                $colIndex = 1;

                $sheet->setCellValue(Coordinate::stringFromColumnIndex($colIndex++) . $fila, 'N°');
                $sheet->setCellValue(Coordinate::stringFromColumnIndex($colIndex++) . $fila, 'FECHA');
                $sheet->setCellValue(Coordinate::stringFromColumnIndex($colIndex++) . $fila, 'SUCURSAL');
                $sheet->setCellValue(Coordinate::stringFromColumnIndex($colIndex++) . $fila, 'PACIENTE');
                $sheet->setCellValue(Coordinate::stringFromColumnIndex($colIndex++) . $fila, 'DESCRIPCIÓN');
                $sheet->setCellValue(Coordinate::stringFromColumnIndex($colIndex++) . $fila, 'MÉDICO');

                /* columnas dinámicas */
                foreach ($tipo_pagos as $tipo) {
                    $sheet->setCellValue(
                        Coordinate::stringFromColumnIndex($colIndex++) . $fila,
                        $tipo['value'] . ' Bs.'
                    );
                }

                $totalColumnas = $colIndex - 1;
                $lastColumn = Coordinate::stringFromColumnIndex($totalColumnas);

                $sheet->getStyle("A{$fila}:{$lastColumn}{$fila}")
                    ->applyFromArray($this->headerTablaRed);

                $fila++;

                /* ===================== DATA ===================== */
                foreach ($pagos_sin_verificar as $key => $item) {
                    $colIndex = 1;

                    $sheet->setCellValue(Coordinate::stringFromColumnIndex($colIndex++) . $fila, $key + 1);

                    $sheet->setCellValue(
                        Coordinate::stringFromColumnIndex($colIndex++) . $fila,
                        $item->fecha_verificado_t . "\n" . $item->hora_verificado
                    );

                    $sheet->setCellValue(
                        Coordinate::stringFromColumnIndex($colIndex++) . $fila,
                        $item->sucursal->nombre
                    );

                    $sheet->setCellValue(
                        Coordinate::stringFromColumnIndex($colIndex++) . $fila,
                        $item->cliente->nombre . ' ' .
                            $item->cliente->paterno . ' ' .
                            $item->cliente->materno
                    );

                    $tramitador_txt = "";
                    if ($item->certificado_detalle->certificado->tramitador) {
                        $tramitador_txt = " - " . $item->certificado_detalle->certificado->tramitador->nombre;
                    }

                    $sheet->setCellValue(
                        Coordinate::stringFromColumnIndex($colIndex++) . $fila,
                        $item->certificado_detalle->tipo_certificado->nombre . "\n(" .
                            $item->certificado_detalle->certificado->tipo . $tramitador_txt . ")"
                    );

                    if ($item->medico) {
                        $sheet->setCellValue(
                            Coordinate::stringFromColumnIndex($colIndex++) . $fila,
                            $item->medico->nombre . ' ' . $item->medico->paterno
                        );
                    } else {
                        $sheet->setCellValue(
                            Coordinate::stringFromColumnIndex($colIndex++) . $fila,
                            ''
                        );
                    }

                    foreach ($tipo_pagos as $tipo) {
                        $value = ($item->tipo_pago == $tipo['value'])
                            ? number_format($item->monto, 2, ".", "")
                            : '';

                        $sheet->setCellValue(
                            Coordinate::stringFromColumnIndex($colIndex++) . $fila,
                            $value
                        );
                    }

                    $sheet->getStyle("A{$fila}:{$lastColumn}{$fila}")
                        ->applyFromArray($this->bodyTabla);

                    $fila++;
                }

                /* ===================== TOTALES ===================== */
                $sheet->setCellValue("A{$fila}", "TOTAL BS.");
                $sheet->mergeCells("A{$fila}:" . Coordinate::stringFromColumnIndex(6) . "{$fila}");

                $colIndex = 7;
                $suma_total = 0;

                foreach ($tipo_pagos as $tipo) {
                    $total = $suma_tipos_sin_verificar[$tipo['value']] ?? 0;

                    $sheet->setCellValue(
                        Coordinate::stringFromColumnIndex($colIndex++) . $fila,
                        number_format($total, 2, ".", "")
                    );

                    $suma_total += $total;
                }

                $sheet->getStyle("A{$fila}:{$lastColumn}{$fila}")
                    ->applyFromArray($this->headerTablaRed);

                $fila++;

                /* ===================== TOTAL FINAL ===================== */
                $sheet->setCellValue("A{$fila}", "TOTAL FINAL BS.");
                $sheet->mergeCells("A{$fila}:" . Coordinate::stringFromColumnIndex(6) . "{$fila}");

                $sheet->setCellValue(
                    Coordinate::stringFromColumnIndex(7) . $fila,
                    number_format($suma_total, 2, ".", "")
                );
                $sheet->getStyle("A{$fila}:{$lastColumn}{$fila}")
                    ->applyFromArray($this->headerTablaRed);

                $fila++;
                $fila++;
                // RESUMEN
                $sheet->setCellValue("A{$fila}", "RESUMEN GENERAL");
                $sheet->mergeCells("A{$fila}:{$lastColumn}{$fila}");
                $sheet->getStyle("A{$fila}:{$lastColumn}{$fila}")
                    ->getAlignment()->setHorizontal('center');
                $fila++;
                $sheet->setCellValue("D{$fila}", "TIPO DE PAGO");
                $sheet->setCellValue("E{$fila}", "MONTO");
                $sheet->getStyle("D{$fila}:E{$fila}")
                    ->applyFromArray($this->headerTabla);
                $total_final = 0;
                $fila++;
                foreach ($tipo_pagos as $tipo) {
                    $total = $suma_tipos[$tipo['value']] ?? 0;
                    $total_sin_verificar = $suma_tipos_sin_verificar[$tipo['value']] ?? 0;
                    $total_final_tipo = $total + $total_sin_verificar;
                    $sheet->setCellValue(
                        "D{$fila}",
                        $tipo['value']
                    );
                    $sheet->setCellValue(
                        "E{$fila}",
                        number_format($total_final_tipo, 2, ".", "")
                    );
                    $total_final += (float)$total_final_tipo;
                    $fila++;
                }

                // TOTAL FINAL GENERAL
                $sheet->setCellValue("D{$fila}", "TOTAL GENERAL");
                $sheet->getStyle("D{$fila}:E{$fila}")
                    ->applyFromArray($this->titulo);
                $sheet->setCellValue(
                    "E{$fila}",
                    number_format($total_final, 2, ".", "")
                );
                $sheet->getStyle("D{$fila}:E{$fila}")
                    ->applyFromArray($this->headerTabla);
            }

            /* ===================== COLUMN WIDTH ===================== */
            $sheet->getColumnDimension('A')->setWidth(6);
            $sheet->getColumnDimension('B')->setWidth(18);
            $sheet->getColumnDimension('C')->setWidth(18);
            $sheet->getColumnDimension('D')->setWidth(30);
            $sheet->getColumnDimension('E')->setWidth(30);

            for ($i = 6; $i <= $totalColumnas; $i++) {
                $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($i))->setWidth(15);
            }

            /* ===================== WRAP TEXT ===================== */
            for ($i = 1; $i <= $totalColumnas; $i++) {
                $sheet->getStyle(Coordinate::stringFromColumnIndex($i))
                    ->getAlignment()
                    ->setWrapText(true);
            }

            /* ===================== PAGE SETUP ===================== */
            $sheet->getPageSetup()->setOrientation(PageSetup::ORIENTATION_LANDSCAPE);
            $sheet->getPageMargins()->setTop(0.5);
            $sheet->getPageMargins()->setRight(0.1);
            $sheet->getPageMargins()->setLeft(0.1);
            $sheet->getPageMargins()->setBottom(0.1);

            $sheet->getPageSetup()->setPrintArea("A1:{$lastColumn}{$fila}");
            $sheet->getPageSetup()->setFitToWidth(1);
            $sheet->getPageSetup()->setFitToHeight(0);

            /* ===================== EXPORT ===================== */
            return response()->streamDownload(
                function () use ($spreadsheet) {
                    $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
                    $writer->save('php://output');
                },
                'caja_' . time() . '.xlsx',
                [
                    'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                ]
            );
        }
    }


    public function clientes()
    {
        return Inertia::render("Admin/Reportes/Clientes");
    }

    public function r_clientes(Request $request)
    {
        ini_set('memory_limit', '1024M');
        set_time_limit(-1);
        $fecha_ini =  $request->fecha_ini;
        $fecha_fin =  $request->fecha_fin;
        $formato =  $request->formato;
        $clientes = Cliente::select("clientes.*")
            ->where("status", 1);

        if ($fecha_ini && $fecha_fin) {
            $clientes->whereBetween('fecha_registro', [$fecha_ini, $fecha_fin]);
        }

        $clientes = $clientes->get();
        if ($formato == 'pdf') {
            $pdf = PDF::loadView('reportes.clientes', compact('clientes'))->setPaper('letter', 'portrait');

            // ENUMERAR LAS PÁGINAS USANDO CANVAS
            $pdf->output();
            $dom_pdf = $pdf->getDomPDF();
            $canvas = $dom_pdf->get_canvas();
            $alto = $canvas->get_height();
            $ancho = $canvas->get_width();
            $canvas->page_text($ancho - 90, $alto - 25, "Página {PAGE_NUM} de {PAGE_COUNT}", null, 9, array(0, 0, 0));

            return $pdf->stream('clientes.pdf');
        } else {
            $spreadsheet = new Spreadsheet();
            $spreadsheet->getProperties()
                ->setCreator("ADMIN")
                ->setLastModifiedBy('Administración')
                ->setTitle('Registros')
                ->setSubject('Registros')
                ->setDescription('Registros')
                ->setKeywords('PHPSpreadsheet')
                ->setCategory('Listado');

            $sheet = $spreadsheet->getActiveSheet();

            $spreadsheet->getDefaultStyle()->getFont()->setName('Arial');

            $fila = 1;
            if (file_exists(public_path() . '/imgs/' . $this->configuracion->logo)) {
                $drawing = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
                $drawing->setName('logo');
                $drawing->setDescription('logo');
                $drawing->setPath(public_path() . '/imgs/' . $this->configuracion->logo); // put your path and image here
                $drawing->setCoordinates('A' . $fila);
                $drawing->setOffsetX(5);
                $drawing->setOffsetY(0);
                $drawing->setHeight(70);
                $drawing->setWorksheet($sheet);
            }

            $fila = 2;
            $sheet->setCellValue('A' . $fila, $this->configuracion->nombre_sistema);
            $sheet->mergeCells("A" . $fila . ":H" . $fila);  //COMBINAR CELDAS
            $sheet->getStyle('A' . $fila . ':H' . $fila)->getAlignment()->setHorizontal('center');
            $sheet->getStyle('A' . $fila . ':H' . $fila)->applyFromArray($this->titulo);
            $fila++;
            $sheet->setCellValue('A' . $fila, "LISTA DE CLIENTES");
            $sheet->mergeCells("A" . $fila . ":H" . $fila);  //COMBINAR CELDAS
            $sheet->getStyle('A' . $fila . ':H' . $fila)->getAlignment()->setHorizontal('center');
            $sheet->getStyle('A' . $fila . ':H' . $fila)->applyFromArray($this->titulo);
            $fila++;
            $fila++;
            $sheet->setCellValue('A' . $fila, 'N°');
            $sheet->setCellValue('B' . $fila, 'PATERNO');
            $sheet->setCellValue('C' . $fila, 'MATERNO');
            $sheet->setCellValue('D' . $fila, 'NOMBRE(S)');
            $sheet->setCellValue('E' . $fila, 'C.I.');
            $sheet->setCellValue('F' . $fila, 'FECHA NACIMIENTO');
            $sheet->setCellValue('G' . $fila, 'CELULAR');
            $sheet->setCellValue('H' . $fila, 'FECHA REGISTRO');
            $sheet->getStyle('A' . $fila . ':H' . $fila)->applyFromArray($this->headerTabla);
            $fila++;

            foreach ($clientes as $key => $item) {
                $sheet->setCellValue('A' . $fila, $key + 1);
                $sheet->setCellValue('B' . $fila, $item->paterno);
                $sheet->setCellValue('C' . $fila, $item->materno);
                $sheet->setCellValue('D' . $fila, $item->nombre);
                $sheet->setCellValue('E' . $fila, $item->full_ci);
                $sheet->setCellValue('F' . $fila, $item->fecha_nact_t);
                $sheet->setCellValue('G' . $fila, $item->cel);
                $sheet->setCellValue('H' . $fila, $item->fecha_registro_t);
                $sheet->getStyle('A' . $fila . ':H' . $fila)->applyFromArray($this->bodyTabla);
                $fila++;
            }

            $sheet->getColumnDimension('A')->setWidth(6);
            $sheet->getColumnDimension('B')->setWidth(20);
            $sheet->getColumnDimension('C')->setWidth(15);
            $sheet->getColumnDimension('D')->setWidth(10);
            $sheet->getColumnDimension('E')->setWidth(15);
            $sheet->getColumnDimension('F')->setWidth(20);
            $sheet->getColumnDimension('G')->setWidth(15);
            $sheet->getColumnDimension('H')->setWidth(15);

            foreach (range('A', 'H') as $columnID) {
                $sheet->getStyle($columnID)->getAlignment()->setWrapText(true);
            }

            $sheet->getPageSetup()->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE);
            $sheet->getPageMargins()->setTop(0.5);
            $sheet->getPageMargins()->setRight(0.1);
            $sheet->getPageMargins()->setLeft(0.1);
            $sheet->getPageMargins()->setBottom(0.1);
            $sheet->getPageSetup()->setPrintArea('A:H');
            $sheet->getPageSetup()->setFitToWidth(1);
            $sheet->getPageSetup()->setFitToHeight(0);

            return response()->streamDownload(
                function () use ($spreadsheet) {
                    $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
                    $writer->save('php://output');
                },
                'clientes_' . time() . '.xlsx',
                [
                    'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                ]
            );
        }
    }

    public function certificados()
    {
        return Inertia::render("Admin/Reportes/Certificados");
    }

    public function r_certificados(Request $request)
    {
        ini_set('memory_limit', '1024M');
        set_time_limit(-1);
        $cliente_id =  $request->cliente_id;
        $sucursal_id =  $request->sucursal_id;
        $user_id =  $request->user_id;
        $tipo_certificado_id =  $request->tipo_certificado_id;
        $fecha_ini =  $request->fecha_ini;
        $fecha_fin =  $request->fecha_fin;
        $formato =  $request->formato;
        $certificado_detalles = CertificadoDetalle::select("certificado_detalles.*");

        if ($cliente_id != 'todos') {
            $certificado_detalles->whereHas('certificado', function ($q) use ($cliente_id) {
                $q->where("cliente_id", $cliente_id);
            });
        }

        if ($sucursal_id != 'todos') {
            // $certificado_detalles->whereHas('certificado', function ($q) use ($sucursal_id) {
            //     $q->where("sucursal_id", $sucursal_id);
            // });
            $certificado_detalles->where("sucursal_id", $sucursal_id);
        }

        if ($user_id != 'todos') {
            // $certificado_detalles->whereHas('certificado', function ($q) use ($user_id) {
            //     $q->where("user_id", $user_id);
            // });
            $certificado_detalles->where("user_id", $user_id);
        }

        if ($tipo_certificado_id != 'todos') {
            $certificado_detalles->where("tipo_certificado_id", $tipo_certificado_id);
        }

        if ($fecha_ini && $fecha_fin) {
            // $certificado_detalles->whereHas('certificado', function ($q) use ($fecha_ini, $fecha_fin) {
            //     $q->whereBetween('fecha_inicio', [$fecha_ini, $fecha_fin]);
            // });
            $certificado_detalles->whereBetween('fecha_inicio', [$fecha_ini, $fecha_fin]);
        }

        $certificado_detalles->whereHas('certificado', function ($q) use ($sucursal_id) {
            // $q->where("estado", 1);
            $q->where("status", 1);
        });

        $certificado_detalles->where("estado", 1);

        $certificado_detalles = $certificado_detalles->get();

        if ($formato == 'pdf') {
            $pdf = PDF::loadView('reportes.certificados', compact('certificado_detalles'))->setPaper('letter', 'portrait');

            // ENUMERAR LAS PÁGINAS USANDO CANVAS
            $pdf->output();
            $dom_pdf = $pdf->getDomPDF();
            $canvas = $dom_pdf->get_canvas();
            $alto = $canvas->get_height();
            $ancho = $canvas->get_width();
            $canvas->page_text($ancho - 90, $alto - 25, "Página {PAGE_NUM} de {PAGE_COUNT}", null, 9, array(0, 0, 0));

            return $pdf->stream('certificados.pdf');
        } else {
            $spreadsheet = new Spreadsheet();
            $spreadsheet->getProperties()
                ->setCreator("ADMIN")
                ->setLastModifiedBy('Administración')
                ->setTitle('Registros')
                ->setSubject('Registros')
                ->setDescription('Registros')
                ->setKeywords('PHPSpreadsheet')
                ->setCategory('Listado');

            $sheet = $spreadsheet->getActiveSheet();

            $spreadsheet->getDefaultStyle()->getFont()->setName('Arial');

            $fila = 1;
            if (file_exists(public_path() . '/imgs/' . $this->configuracion->logo)) {
                $drawing = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
                $drawing->setName('logo');
                $drawing->setDescription('logo');
                $drawing->setPath(public_path() . '/imgs/' . $this->configuracion->logo); // put your path and image here
                $drawing->setCoordinates('A' . $fila);
                $drawing->setOffsetX(5);
                $drawing->setOffsetY(0);
                $drawing->setHeight(70);
                $drawing->setWorksheet($sheet);
            }

            $fila = 2;
            $sheet->setCellValue('A' . $fila, $this->configuracion->nombre_sistema);
            $sheet->mergeCells("A" . $fila . ":J" . $fila);  //COMBINAR CELDAS
            $sheet->getStyle('A' . $fila . ':J' . $fila)->getAlignment()->setHorizontal('center');
            $sheet->getStyle('A' . $fila . ':J' . $fila)->applyFromArray($this->titulo);
            $fila++;
            $sheet->setCellValue('A' . $fila, "CERTIFICADOS EMITIDOS");
            $sheet->mergeCells("A" . $fila . ":J" . $fila);  //COMBINAR CELDAS
            $sheet->getStyle('A' . $fila . ':J' . $fila)->getAlignment()->setHorizontal('center');
            $sheet->getStyle('A' . $fila . ':J' . $fila)->applyFromArray($this->titulo);
            $fila++;
            $fila++;
            $sheet->setCellValue('A' . $fila, 'N°');
            $sheet->setCellValue('B' . $fila, 'NRO. C.I.');
            $sheet->setCellValue('C' . $fila, 'NOMBRE');
            $sheet->setCellValue('D' . $fila, 'AP. PATERNO');
            $sheet->setCellValue('E' . $fila, 'AP. MATERNO');
            $sheet->setCellValue('F' . $fila, 'EDAD');
            $sheet->setCellValue('G' . $fila, 'CATEGORÍA');
            $sheet->setCellValue('H' . $fila, 'MÉDICO');
            $sheet->setCellValue('I' . $fila, 'FECHA Y HORA INICIO');
            $sheet->setCellValue('J' . $fila, 'FECHA Y HORA FIN');
            $sheet->getStyle('A' . $fila . ':J' . $fila)->applyFromArray($this->headerTabla);
            $fila++;

            foreach ($certificado_detalles as $key => $item) {
                $sheet->setCellValue('A' . $fila, $key + 1);
                $sheet->setCellValue('B' . $fila, $item->certificado->cliente->full_ci);
                $sheet->setCellValue('C' . $fila, $item->certificado->cliente->nombre);
                $sheet->setCellValue('D' . $fila, $item->certificado->cliente->paterno);
                $sheet->setCellValue('E' . $fila, $item->certificado->cliente->materno);
                $sheet->setCellValue('F' . $fila, $item->certificado->cliente->edad);
                $sheet->setCellValue('G' . $fila, $item->categoria);
                $sheet->setCellValue('H' . $fila, $item->user->full_name);
                $sheet->setCellValue('I' . $fila, $item->certificado->fecha_inicio_t . ' ' . $item->certificado->hora_inicio);
                $sheet->setCellValue('J' . $fila, $item->certificado->fecha_fin_t . ' ' . $item->certificado->hora_fin);
                $sheet->getStyle('A' . $fila . ':J' . $fila)->applyFromArray($this->bodyTabla);
                $fila++;
            }

            $sheet->getColumnDimension('A')->setWidth(6);
            $sheet->getColumnDimension('B')->setWidth(15);
            $sheet->getColumnDimension('C')->setWidth(15);
            $sheet->getColumnDimension('D')->setWidth(15);
            $sheet->getColumnDimension('E')->setWidth(15);
            $sheet->getColumnDimension('F')->setWidth(12);
            $sheet->getColumnDimension('G')->setWidth(10);
            $sheet->getColumnDimension('H')->setWidth(20);
            $sheet->getColumnDimension('I')->setWidth(20);
            $sheet->getColumnDimension('J')->setWidth(20);

            foreach (range('A', 'J') as $columnID) {
                $sheet->getStyle($columnID)->getAlignment()->setWrapText(true);
            }

            $sheet->getPageSetup()->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE);
            $sheet->getPageMargins()->setTop(0.5);
            $sheet->getPageMargins()->setRight(0.1);
            $sheet->getPageMargins()->setLeft(0.1);
            $sheet->getPageMargins()->setBottom(0.1);
            $sheet->getPageSetup()->setPrintArea('A:J');
            $sheet->getPageSetup()->setFitToWidth(1);
            $sheet->getPageSetup()->setFitToHeight(0);

            return response()->streamDownload(
                function () use ($spreadsheet) {
                    $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
                    $writer->save('php://output');
                },
                'certificados_' . time() . '.xlsx',
                [
                    'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                ]
            );
        }
    }

    public function certificados_interno()
    {
        return Inertia::render("Admin/Reportes/CertificadosInterno");
    }

    public function r_certificados_interno(Request $request)
    {
        ini_set('memory_limit', '1024M');
        set_time_limit(-1);
        $cliente_id =  $request->cliente_id;
        $sucursal_id =  $request->sucursal_id;
        $user_id =  $request->user_id;
        $tipo_certificado_id =  $request->tipo_certificado_id;
        $fecha_ini =  $request->fecha_ini;
        $fecha_fin =  $request->fecha_fin;
        $formato =  $request->formato;
        $certificado_detalles = CertificadoDetalle::select("certificado_detalles.*");

        if ($cliente_id != 'todos') {
            $certificado_detalles->whereHas('certificado', function ($q) use ($cliente_id) {
                $q->where("cliente_id", $cliente_id);
            });
        }

        if ($sucursal_id != 'todos') {
            // $certificado_detalles->whereHas('certificado', function ($q) use ($sucursal_id) {
            //     $q->where("sucursal_id", $sucursal_id);
            // });
            $certificado_detalles->where("sucursal_id", $sucursal_id);
        }

        if ($user_id != 'todos') {
            // $certificado_detalles->whereHas('certificado', function ($q) use ($user_id) {
            //     $q->where("user_id", $user_id);
            // });
            $certificado_detalles->where("user_id", $user_id);
        }

        if ($tipo_certificado_id != 'todos') {
            $certificado_detalles->where("tipo_certificado_id", $tipo_certificado_id);
        }

        if ($fecha_ini && $fecha_fin) {
            // $certificado_detalles->whereHas('certificado', function ($q) use ($fecha_ini, $fecha_fin) {
            //     $q->whereBetween('fecha_inicio', [$fecha_ini, $fecha_fin]);
            // });
            $certificado_detalles->whereBetween('fecha_inicio', [$fecha_ini, $fecha_fin]);
        }

        $certificado_detalles->whereHas('certificado', function ($q) use ($sucursal_id) {
            // $q->where("estado", 1);
            $q->where("status", 1);
        });

        $certificado_detalles->where("estado", 1);

        $certificado_detalles = $certificado_detalles->get();

        if ($formato == 'pdf') {
            $pdf = PDF::loadView('reportes.certificados_interno', compact('certificado_detalles'))->setPaper('letter', 'portrait');

            // ENUMERAR LAS PÁGINAS USANDO CANVAS
            $pdf->output();
            $dom_pdf = $pdf->getDomPDF();
            $canvas = $dom_pdf->get_canvas();
            $alto = $canvas->get_height();
            $ancho = $canvas->get_width();
            $canvas->page_text($ancho - 90, $alto - 25, "Página {PAGE_NUM} de {PAGE_COUNT}", null, 9, array(0, 0, 0));

            return $pdf->stream('certificados_interno.pdf');
        } else {
            $spreadsheet = new Spreadsheet();
            $spreadsheet->getProperties()
                ->setCreator("ADMIN")
                ->setLastModifiedBy('Administración')
                ->setTitle('Registros')
                ->setSubject('Registros')
                ->setDescription('Registros')
                ->setKeywords('PHPSpreadsheet')
                ->setCategory('Listado');

            $sheet = $spreadsheet->getActiveSheet();

            $spreadsheet->getDefaultStyle()->getFont()->setName('Arial');

            $fila = 1;
            if (file_exists(public_path() . '/imgs/' . $this->configuracion->logo)) {
                $drawing = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
                $drawing->setName('logo');
                $drawing->setDescription('logo');
                $drawing->setPath(public_path() . '/imgs/' . $this->configuracion->logo); // put your path and image here
                $drawing->setCoordinates('A' . $fila);
                $drawing->setOffsetX(5);
                $drawing->setOffsetY(0);
                $drawing->setHeight(70);
                $drawing->setWorksheet($sheet);
            }

            $fila = 2;
            $sheet->setCellValue('A' . $fila, $this->configuracion->nombre_sistema);
            $sheet->mergeCells("A" . $fila . ":I" . $fila);  //COMBINAR CELDAS
            $sheet->getStyle('A' . $fila . ':I' . $fila)->getAlignment()->setHorizontal('center');
            $sheet->getStyle('A' . $fila . ':I' . $fila)->applyFromArray($this->titulo);
            $fila++;
            $sheet->setCellValue('A' . $fila, "CERTIFICADOS EMITIDOS INTERNO");
            $sheet->mergeCells("A" . $fila . ":I" . $fila);  //COMBINAR CELDAS
            $sheet->getStyle('A' . $fila . ':I' . $fila)->getAlignment()->setHorizontal('center');
            $sheet->getStyle('A' . $fila . ':I' . $fila)->applyFromArray($this->titulo);
            $fila++;
            $fila++;
            $sheet->setCellValue('A' . $fila, 'N°');
            $sheet->setCellValue('B' . $fila, 'NRO. C.I.');
            $sheet->setCellValue('C' . $fila, 'NOMBRE');
            $sheet->setCellValue('D' . $fila, 'AP. PATERNO');
            $sheet->setCellValue('E' . $fila, 'AP. MATERNO');
            $sheet->setCellValue('F' . $fila, 'TELÉFONO');
            $sheet->setCellValue('G' . $fila, 'EDAD');
            $sheet->setCellValue('H' . $fila, 'CATEGORÍA');
            $sheet->setCellValue('I' . $fila, 'MÉDICO');
            $sheet->getStyle('A' . $fila . ':I' . $fila)->applyFromArray($this->headerTabla);
            $fila++;

            foreach ($certificado_detalles as $key => $item) {
                $sheet->setCellValue('A' . $fila, $key + 1);
                $sheet->setCellValue('B' . $fila, $item->certificado->cliente->full_ci);
                $sheet->setCellValue('C' . $fila, $item->certificado->cliente->nombre);
                $sheet->setCellValue('D' . $fila, $item->certificado->cliente->paterno);
                $sheet->setCellValue('E' . $fila, $item->certificado->cliente->materno);
                $sheet->setCellValue('F' . $fila, $item->certificado->cliente->cel);
                $sheet->setCellValue('G' . $fila, $item->certificado->cliente->edad);
                $sheet->setCellValue('H' . $fila, $item->categoria);
                $sheet->setCellValue('I' . $fila, $item->user->full_name);
                $sheet->getStyle('A' . $fila . ':I' . $fila)->applyFromArray($this->bodyTabla);
                $fila++;
            }

            $sheet->getColumnDimension('A')->setWidth(6);
            $sheet->getColumnDimension('B')->setWidth(15);
            $sheet->getColumnDimension('C')->setWidth(15);
            $sheet->getColumnDimension('D')->setWidth(15);
            $sheet->getColumnDimension('E')->setWidth(15);
            $sheet->getColumnDimension('F')->setWidth(12);
            $sheet->getColumnDimension('G')->setWidth(10);
            $sheet->getColumnDimension('H')->setWidth(20);
            $sheet->getColumnDimension('I')->setWidth(20);

            foreach (range('A', 'I') as $columnID) {
                $sheet->getStyle($columnID)->getAlignment()->setWrapText(true);
            }

            $sheet->getPageSetup()->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE);
            $sheet->getPageMargins()->setTop(0.5);
            $sheet->getPageMargins()->setRight(0.1);
            $sheet->getPageMargins()->setLeft(0.1);
            $sheet->getPageMargins()->setBottom(0.1);
            $sheet->getPageSetup()->setPrintArea('A:I');
            $sheet->getPageSetup()->setFitToWidth(1);
            $sheet->getPageSetup()->setFitToHeight(0);

            return response()->streamDownload(
                function () use ($spreadsheet) {
                    $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
                    $writer->save('php://output');
                },
                'certificados_interno_' . time() . '.xlsx',
                [
                    'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                ]
            );
        }
    }

    public function r_certificados_diario(Request $request)
    {
        ini_set('memory_limit', '1024M');
        set_time_limit(-1);
        $sucursal_id =  $request->sucursal_id;
        $user_id =  $request->user_id;
        $tipo =  $request->tipo;
        $fecha_ini =  $request->fecha_ini;
        $fecha_fin =  $request->fecha_fin;
        // $tipo_certificado_id =  $request->tipo_certificado_id;
        $formato =  $request->formato;
        $certificado_detalles = CertificadoDetalle::select("certificado_detalles.*");

        if ($sucursal_id != 'todos') {
            $certificado_detalles->whereHas('certificado', function ($q) use ($sucursal_id) {
                $q->where("sucursal_id", $sucursal_id);
            });
        }

        if (Auth::user()->tipo == 'MÉDICO') {
            $certificado_detalles->where("user_id", Auth::user()->id);
        } else {
            if ($user_id != 'todos') {
                $certificado_detalles->where("user_id", $user_id);
            }
        }

        if ($tipo != 'todos') {
            $certificado_detalles->whereHas('certificado', function ($q) use ($tipo) {
                $q->where("tipo", $tipo);
            });
        }

        if ($fecha_ini && $fecha_fin) {
            $certificado_detalles->whereBetween('fecha_inicio', [$fecha_ini, $fecha_fin]);
        }

        $certificado_detalles->whereHas('certificado', function ($q) {
            $q->where("estado", 1);
            $q->where("status", 1);
        });

        $certificado_detalles = $certificado_detalles->get();

        $suma_cantidad = [];
        $tipo_certificados = TipoCertificado::all();
        foreach ($tipo_certificados as $tipo) {
            $suma = $certificado_detalles->where('tipo_certificado_id', $tipo->id)->count();
            $suma_cantidad[$tipo->id] = $suma;
        }

        if ($formato == 'pdf') {
            $pdf = PDF::loadView('reportes.certificados_diario', compact('certificado_detalles', 'suma_cantidad', 'tipo_certificados'))->setPaper('letter', 'portrait');

            // ENUMERAR LAS PÁGINAS USANDO CANVAS
            $pdf->output();
            $dom_pdf = $pdf->getDomPDF();
            $canvas = $dom_pdf->get_canvas();
            $alto = $canvas->get_height();
            $ancho = $canvas->get_width();
            $canvas->page_text($ancho - 90, $alto - 25, "Página {PAGE_NUM} de {PAGE_COUNT}", null, 9, array(0, 0, 0));

            return $pdf->stream('certificados.pdf');
        } else {
            $spreadsheet = new Spreadsheet();
            $spreadsheet->getProperties()
                ->setCreator("ADMIN")
                ->setLastModifiedBy('Administración')
                ->setTitle('Registros')
                ->setSubject('Registros')
                ->setDescription('Registros')
                ->setKeywords('PHPSpreadsheet')
                ->setCategory('Listado');

            $sheet = $spreadsheet->getActiveSheet();

            $spreadsheet->getDefaultStyle()->getFont()->setName('Arial');

            $fila = 1;
            if (file_exists(public_path() . '/imgs/' . $this->configuracion->logo)) {
                $drawing = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
                $drawing->setName('logo');
                $drawing->setDescription('logo');
                $drawing->setPath(public_path() . '/imgs/' . $this->configuracion->logo); // put your path and image here
                $drawing->setCoordinates('A' . $fila);
                $drawing->setOffsetX(5);
                $drawing->setOffsetY(0);
                $drawing->setHeight(70);
                $drawing->setWorksheet($sheet);
            }

            $fila = 2;
            $sheet->setCellValue('A' . $fila, $this->configuracion->nombre_sistema);
            $sheet->mergeCells("A" . $fila . ":J" . $fila);  //COMBINAR CELDAS
            $sheet->getStyle('A' . $fila . ':J' . $fila)->getAlignment()->setHorizontal('center');
            $sheet->getStyle('A' . $fila . ':J' . $fila)->applyFromArray($this->titulo);
            $fila++;
            $sheet->setCellValue('A' . $fila, "DIARIO DE CERTIFICADOS EMITIDOS");
            $sheet->mergeCells("A" . $fila . ":J" . $fila);  //COMBINAR CELDAS
            $sheet->getStyle('A' . $fila . ':J' . $fila)->getAlignment()->setHorizontal('center');
            $sheet->getStyle('A' . $fila . ':J' . $fila)->applyFromArray($this->titulo);
            $fila++;
            $fila++;
            $sheet->setCellValue('A' . $fila, 'N°');
            $sheet->setCellValue('B' . $fila, 'PACIENTE');
            $sheet->setCellValue('C' . $fila, 'C.I.');
            $sheet->setCellValue('D' . $fila, 'CATEGORÍA');
            $sheet->setCellValue('E' . $fila, 'TIPO');
            $sheet->setCellValue('F' . $fila, 'TIPO CERTIFICADO');
            $sheet->setCellValue('G' . $fila, 'MÉDICO');
            $sheet->setCellValue('H' . $fila, 'SUCURSAL');
            $sheet->setCellValue('I' . $fila, 'FECHA Y HORA INICIO');
            $sheet->setCellValue('J' . $fila, 'FECHA Y HORA FIN');
            $sheet->getStyle('A' . $fila . ':J' . $fila)->applyFromArray($this->headerTabla);
            $fila++;

            foreach ($certificado_detalles as $key => $item) {
                $sheet->setCellValue('A' . $fila, $key + 1);
                $sheet->setCellValue('B' . $fila, $item->certificado->cliente->full_name);
                $sheet->setCellValue('C' . $fila, $item->certificado->cliente->full_ci);
                $sheet->setCellValue('D' . $fila, $item->categoria);
                $sheet->setCellValue('E' . $fila, $item->certificado->tipo);
                $sheet->setCellValue('F' . $fila, $item->tipo_certificado->nombre);
                $sheet->setCellValue('G' . $fila, $item->user->full_name);
                $sheet->setCellValue('H' . $fila, $item->sucursal->nombre);
                $sheet->setCellValue('I' . $fila, $item->certificado->fecha_inicio_t . ' ' . $item->certificado->hora_inicio);
                $sheet->setCellValue('J' . $fila, $item->certificado->fecha_fin_t . ' ' . $item->certificado->hora_fin);
                $sheet->getStyle('A' . $fila . ':J' . $fila)->applyFromArray($this->bodyTabla);
                $fila++;
            }
            $fila++;
            $sheet->setCellValue('D' . $fila, 'TIPO DE CERTIFICADO');
            $sheet->mergeCells("D" . $fila . ":F" . $fila);  //COMBINAR CELDAS
            $sheet->setCellValue('G' . $fila, 'TOTAL');
            $sheet->getStyle('D' . $fila . ':G' . $fila)->applyFromArray($this->headerTabla);
            foreach ($tipo_certificados as $tipo) {
                $sheet->setCellValue('D' . ($fila + 1), $tipo->nombre);
                $sheet->mergeCells("D" . ($fila + 1) . ":F" . ($fila + 1));  //COMBINAR CELDAS
                $sheet->setCellValue('G' . ($fila + 1), $suma_cantidad[$tipo->id] ?? 0);
                $sheet->getStyle('D' . ($fila + 1) . ':G' . ($fila + 1))->applyFromArray($this->bodyTabla);
                $fila++;
            }

            $sheet->getColumnDimension('A')->setWidth(6);
            $sheet->getColumnDimension('B')->setWidth(20);
            $sheet->getColumnDimension('C')->setWidth(15);
            $sheet->getColumnDimension('D')->setWidth(10);
            $sheet->getColumnDimension('E')->setWidth(10);
            $sheet->getColumnDimension('F')->setWidth(20);
            $sheet->getColumnDimension('G')->setWidth(20);
            $sheet->getColumnDimension('H')->setWidth(20);
            $sheet->getColumnDimension('I')->setWidth(20);
            $sheet->getColumnDimension('J')->setWidth(20);

            foreach (range('A', 'J') as $columnID) {
                $sheet->getStyle($columnID)->getAlignment()->setWrapText(true);
            }

            $sheet->getPageSetup()->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE);
            $sheet->getPageMargins()->setTop(0.5);
            $sheet->getPageMargins()->setRight(0.1);
            $sheet->getPageMargins()->setLeft(0.1);
            $sheet->getPageMargins()->setBottom(0.1);
            $sheet->getPageSetup()->setPrintArea('A:J');
            $sheet->getPageSetup()->setFitToWidth(1);
            $sheet->getPageSetup()->setFitToHeight(0);

            return response()->streamDownload(
                function () use ($spreadsheet) {
                    $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
                    $writer->save('php://output');
                },
                'certificados_diario_' . time() . '.xlsx',
                [
                    'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                ]
            );
        }
    }

    public function historial_accions()
    {
        return Inertia::render("Admin/Reportes/HistorialAccions");
    }

    public function r_historial_accions(Request $request)
    {
        ini_set('memory_limit', '1024M');
        set_time_limit(-1);
        $user_id =  $request->user_id;
        $modulo =  $request->modulo;
        $fecha_ini =  $request->fecha_ini;
        $fecha_fin =  $request->fecha_fin;
        $formato =  $request->formato;
        $historial_accions = HistorialAccion::select("historial_accions.*");

        if ($user_id != 'todos') {
            $historial_accions->where('user_id', $user_id);
        }
        if ($modulo != 'todos') {
            $historial_accions->where('modulo', $modulo);
        }

        if ($fecha_ini && $fecha_fin) {
            $historial_accions->whereBetween('fecha', [$fecha_ini, $fecha_fin]);
        }

        $historial_accions = $historial_accions->get();

        if ($formato == 'pdf') {
            $pdf = PDF::loadView('reportes.historial_accions', compact('historial_accions'))->setPaper('letter', 'portrait');

            // ENUMERAR LAS PÁGINAS USANDO CANVAS
            $pdf->output();
            $dom_pdf = $pdf->getDomPDF();
            $canvas = $dom_pdf->get_canvas();
            $alto = $canvas->get_height();
            $ancho = $canvas->get_width();
            $canvas->page_text($ancho - 90, $alto - 25, "Página {PAGE_NUM} de {PAGE_COUNT}", null, 9, array(0, 0, 0));

            return $pdf->stream('historial_accions.pdf');
        } else {
            $spreadsheet = new Spreadsheet();
            $spreadsheet->getProperties()
                ->setCreator("ADMIN")
                ->setLastModifiedBy('Administración')
                ->setTitle('Registros')
                ->setSubject('Registros')
                ->setDescription('Registros')
                ->setKeywords('PHPSpreadsheet')
                ->setCategory('Listado');

            $sheet = $spreadsheet->getActiveSheet();

            $spreadsheet->getDefaultStyle()->getFont()->setName('Arial');

            $fila = 1;
            if (file_exists(public_path() . '/imgs/' . $this->configuracion->logo)) {
                $drawing = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
                $drawing->setName('logo');
                $drawing->setDescription('logo');
                $drawing->setPath(public_path() . '/imgs/' . $this->configuracion->logo); // put your path and image here
                $drawing->setCoordinates('A' . $fila);
                $drawing->setOffsetX(5);
                $drawing->setOffsetY(0);
                $drawing->setHeight(70);
                $drawing->setWorksheet($sheet);
            }

            $fila = 2;
            $sheet->setCellValue('A' . $fila, $this->configuracion->nombre_sistema);
            $sheet->mergeCells("A" . $fila . ":F" . $fila);  //COMBINAR CELDAS
            $sheet->getStyle('A' . $fila . ':F' . $fila)->getAlignment()->setHorizontal('center');
            $sheet->getStyle('A' . $fila . ':F' . $fila)->applyFromArray($this->titulo);
            $fila++;
            $sheet->setCellValue('A' . $fila, "LOG DE USUARIOS");
            $sheet->mergeCells("A" . $fila . ":F" . $fila);  //COMBINAR CELDAS
            $sheet->getStyle('A' . $fila . ':F' . $fila)->getAlignment()->setHorizontal('center');
            $sheet->getStyle('A' . $fila . ':F' . $fila)->applyFromArray($this->titulo);
            $fila++;
            $fila++;
            $sheet->setCellValue('A' . $fila, 'N°');
            $sheet->setCellValue('B' . $fila, 'FECHA Y HORA');
            $sheet->setCellValue('C' . $fila, 'USUARIO');
            $sheet->setCellValue('D' . $fila, 'DESCRIPCIÓN');
            $sheet->setCellValue('E' . $fila, 'MÓDULO');
            $sheet->setCellValue('F' . $fila, 'ACCIÓN');
            $sheet->getStyle('A' . $fila . ':F' . $fila)->applyFromArray($this->headerTabla);
            $fila++;

            foreach ($historial_accions as $key => $item) {
                $sheet->setCellValue('A' . $fila, $key + 1);
                $sheet->setCellValue('B' . $fila, $item->fecha_t . ' ' . $item->hora);
                $sheet->setCellValue('C' . $fila, $item->user->full_name);
                $sheet->setCellValue('D' . $fila, $item->descripcion);
                $sheet->setCellValue('E' . $fila, $item->modulo);
                $sheet->setCellValue('F' . $fila, $item->accion);
                $sheet->getStyle('A' . $fila . ':F' . $fila)->applyFromArray($this->bodyTabla);
                $fila++;
            }

            $sheet->getColumnDimension('A')->setWidth(6);
            $sheet->getColumnDimension('B')->setWidth(20);
            $sheet->getColumnDimension('C')->setWidth(15);
            $sheet->getColumnDimension('D')->setWidth(25);
            $sheet->getColumnDimension('E')->setWidth(18);
            $sheet->getColumnDimension('F')->setWidth(15);

            foreach (range('A', 'F') as $columnID) {
                $sheet->getStyle($columnID)->getAlignment()->setWrapText(true);
            }

            $sheet->getPageSetup()->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE);
            $sheet->getPageMargins()->setTop(0.5);
            $sheet->getPageMargins()->setRight(0.1);
            $sheet->getPageMargins()->setLeft(0.1);
            $sheet->getPageMargins()->setBottom(0.1);
            $sheet->getPageSetup()->setPrintArea('A:F');
            $sheet->getPageSetup()->setFitToWidth(1);
            $sheet->getPageSetup()->setFitToHeight(0);

            return response()->streamDownload(
                function () use ($spreadsheet) {
                    $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
                    $writer->save('php://output');
                },
                'historial_accions_' . time() . '.xlsx',
                [
                    'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                ]
            );
        }
    }

    public function gcemitidos()
    {
        return Inertia::render("Admin/Reportes/Gcemitidos");
    }
    public function r_gcemitidos(Request $request)
    {
        $cliente_id =  $request->cliente_id;
        $sucursal_id =  $request->sucursal_id;
        $user_id =  $request->user_id;
        $tipo_certificado_id =  $request->tipo_certificado_id;
        $fecha_ini = $request->fecha_ini;
        $fecha_fin = $request->fecha_fin;

        $users = User::select("users.*")
            ->where("tipo", "MÉDICO");
        if ($user_id != 'todos') {
            $users->where("id", $user_id);
        }

        $users = $users->get();
        $categorias = $users->map(function ($user) {
            return $user->nombre . ' ' . $user->paterno . ' ' . $user->materno;
        })->toArray();

        $tipo_certificados = TipoCertificado::select("tipo_certificados.*");

        if ($tipo_certificado_id != 'todos') {
            $tipo_certificados->where("id", $tipo_certificado_id);
        }

        $tipo_certificados = $tipo_certificados->get();


        $data = [];
        $total_final = 0;
        foreach ($tipo_certificados as $tipo_certificado) {
            $cantidad_por_tipo = [];
            foreach ($users as $key => $user) {
                $total = CertificadoDetalle::whereHas("certificado", function ($q) use ($user, $fecha_ini, $fecha_fin, $sucursal_id, $cliente_id) {
                    // $q->where("estado", 1);
                    $q->where("status", 1);
                    // $q->where("user_id", $user->id);
                    if ($fecha_ini && $fecha_fin) {
                        $q->whereBetween("fecha_registro", [$fecha_ini, $fecha_fin]);
                    }
                    if ($sucursal_id != 'todos') {
                        $q->where("sucursal_id", $sucursal_id);
                    }
                    if ($cliente_id != 'todos') {
                        $q->where("cliente_id", $cliente_id);
                    }
                })->where("tipo_certificado_id", $tipo_certificado->id);
                $total->where("user_id", $user->id);
                $total->where("estado", 1);
                $total = $total->count();
                $cantidad_por_tipo[] = $total;
                $total_final += (int)$total;
            }
            $data[] = [
                'name' => $tipo_certificado->nombre,
                'data' => $cantidad_por_tipo,
            ];
        }

        return response()->JSON([
            "categories" => $categorias,
            "data" => $data,
            "total_final" => $total_final
        ]);
    }

    public function gmemitidos()
    {
        return Inertia::render("Admin/Reportes/Gmemitidos");
    }
    public function r_gmemitidos(Request $request)
    {
        $cliente_id =  $request->cliente_id;
        $sucursal_id =  $request->sucursal_id;
        $user_id =  $request->user_id;
        $tipo_pago =  $request->tipo_pago;
        $fecha_ini = $request->fecha_ini;
        $fecha_fin = $request->fecha_fin;

        // Si no vienen fechas
        if (!$request->fecha_ini || !$request->fecha_fin) {

            $primerPago = Pago::orderBy('fecha_verificado', 'asc')
                ->where("status", 1)->first();

            $fecha_ini = $primerPago
                ? Carbon::parse($primerPago->fecha_verificado)->startOfDay()
                : Carbon::now()->startOfDay();

            $fecha_fin = Carbon::now()->endOfDay();
        } else {
            $fecha_ini = Carbon::parse($request->fecha_ini)->startOfDay();
            $fecha_fin = Carbon::parse($request->fecha_fin)->endOfDay();
        }

        $categories = [];
        $data = [];

        $periodo = Carbon::parse($fecha_ini);
        $total_final = 0;
        while ($periodo <= $fecha_fin) {
            $fecha = $periodo->format('Y-m-d');
            $categories[] = date("d/m/Y", strtotime($fecha));

            $total_fecha = Pago::select("id,monto,fecha_verificado,verificado");
            $total_fecha->when($sucursal_id != 'todos', fn($q) => $q->where("sucursal_id", $sucursal_id));
            $total_fecha->when($user_id != 'todos', fn($q) => $q->where("user_id", $user_id));
            $total_fecha->when($tipo_pago != 'todos', fn($q) => $q->where("tipo_pago", $tipo_pago));
            $total_fecha->where("verificado", 1);
            $total_fecha->where("status", 1);
            $total_fecha->where("fecha_verificado", $fecha);
            $total_fecha = $total_fecha->sum("monto");
            $data[] = (float)$total_fecha;
            $total_final += (float)$total_fecha;
            $periodo->addDay();
        }

        $total_final = array_sum($data);

        return response()->JSON([
            "categories" => $categories,
            "data" => $data,
            "total_final" => number_format($total_final, 2, ".", ",")
        ]);
    }
}
