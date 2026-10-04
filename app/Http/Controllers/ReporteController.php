<?php

namespace App\Http\Controllers;

use App\Models\Campeonato;
use App\Models\CampeonatoInscripcion;
use App\Models\Carrera;
use App\Models\CarreraJugador;
use App\Models\Configuracion;
use App\Models\HistorialAccion;
use App\Models\Partido;
use App\Models\PartidoDetalle;
use App\Models\User;
use App\Services\CampeonatoInscripcionService;
use App\Services\ReporteService;
use App\Services\ReporteServiceTcpdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use PDF;
use Carbon\Carbon;
use FPDF;
use Illuminate\Support\Facades\DB;

class ReporteController extends Controller
{

    private $configuracion = null;
    public function __construct(private CampeonatoInscripcionService $campeonatoInscripcionService, private ReporteService $reporteService, private ReporteServiceTcpdf $reporteServiceTcpdf)
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

        $pdf = PDF::loadView('reportes.usuarios', compact('usuarios'))->setPaper('legal', 'landscape');

        // ENUMERAR LAS PÁGINAS USANDO CANVAS
        $pdf->output();
        $dom_pdf = $pdf->getDomPDF();
        $canvas = $dom_pdf->get_canvas();
        $alto = $canvas->get_height();
        $ancho = $canvas->get_width();
        $canvas->page_text($ancho - 90, $alto - 25, "Página {PAGE_NUM} de {PAGE_COUNT}", null, 9, array(0, 0, 0));

        return $pdf->stream('usuarios.pdf');
    }

    public function carreras()
    {
        return Inertia::render("Admin/Reportes/Carreras");
    }

    public function r_carreras(Request $request)
    {
        ini_set('memory_limit', '1024M');
        set_time_limit(-1);
        $carreras = Carrera::select("carreras.*");

        $carreras = $carreras->get();

        $pdf = PDF::loadView('reportes.carreras', compact('carreras'))->setPaper('letter', 'portrait');

        // ENUMERAR LAS PÁGINAS USANDO CANVAS
        $pdf->output();
        $dom_pdf = $pdf->getDomPDF();
        $canvas = $dom_pdf->get_canvas();
        $alto = $canvas->get_height();
        $ancho = $canvas->get_width();
        $canvas->page_text($ancho - 90, $alto - 25, "Página {PAGE_NUM} de {PAGE_COUNT}", null, 9, array(0, 0, 0));

        return $pdf->stream('carreras.pdf');
    }

    public function carrera_jugadors()
    {
        return Inertia::render("Admin/Reportes/CarreraJugadors");
    }

    public function r_carrera_jugadors(Request $request)
    {
        ini_set('memory_limit', '1024M');
        set_time_limit(-1);

        $fecha_ini = $request->fecha_ini;
        $fecha_fin = $request->fecha_fin;
        $campeonato_id = $request->campeonato_id;
        $carrera_id = $request->carrera_id;

        $carrera_jugadors = CarreraJugador::select("carrera_jugadors.*");

        if ($fecha_ini && $fecha_fin) {
            $carrera_jugadors->whereBetween('fecha_registro', [$fecha_ini, $fecha_fin]);
        }

        if ($campeonato_id && $campeonato_id != "todos") {
            $carrera_jugadors->where('campeonato_id', $campeonato_id);
        }

        if ($carrera_id && $carrera_id != "todos") {
            $carrera_jugadors->where('carrera_id', $carrera_id);
        }

        $carrera_jugadors = $carrera_jugadors->get();
        $pdf = PDF::loadView('reportes.carrera_jugadors', compact('carrera_jugadors'))->setPaper('letter', 'portrait');

        // ENUMERAR LAS PÁGINAS USANDO CANVAS
        $pdf->output();
        $dom_pdf = $pdf->getDomPDF();
        $canvas = $dom_pdf->get_canvas();
        $alto = $canvas->get_height();
        $ancho = $canvas->get_width();
        $canvas->page_text($ancho - 90, $alto - 25, "Página {PAGE_NUM} de {PAGE_COUNT}", null, 9, array(0, 0, 0));

        return $pdf->stream('carrera_jugadors.pdf');
    }

    public function posicions()
    {
        return Inertia::render("Admin/Reportes/Posicions");
    }

    public function r_posicions(Request $request)
    {
        ini_set('memory_limit', '1024M');
        set_time_limit(-1);

        $campeonato_id = $request->campeonato_id;
        $campeonato_inscripcions = $this->campeonatoInscripcionService->listadoPosicions($campeonato_id);

        $campeonato = Campeonato::findOrFail($campeonato_id);
        $pdf = PDF::loadView('reportes.posicions', compact('campeonato_inscripcions', 'campeonato'))->setPaper('letter', 'portrait');

        // ENUMERAR LAS PÁGINAS USANDO CANVAS
        $pdf->output();
        $dom_pdf = $pdf->getDomPDF();
        $canvas = $dom_pdf->get_canvas();
        $alto = $canvas->get_height();
        $ancho = $canvas->get_width();
        $canvas->page_text($ancho - 90, $alto - 25, "Página {PAGE_NUM} de {PAGE_COUNT}", null, 9, array(0, 0, 0));

        return $pdf->stream('posicions.pdf');
    }

    public function resultado_partidos()
    {
        return Inertia::render("Admin/Reportes/ResultadoPartidos");
    }

    public function r_resultado_partidos(Request $request)
    {
        ini_set('memory_limit', '1024M');
        set_time_limit(-1);

        $fecha_ini = $request->fecha_ini;
        $fecha_fin = $request->fecha_fin;
        $campeonato_id = $request->campeonato_id;
        $carrera_id = $request->carrera_id;

        $campeonatos = Campeonato::select("campeonatos.*")
            ->when($campeonato_id && $campeonato_id != "todos", function ($query) use ($campeonato_id) {
                return $query->where('id', $campeonato_id);
            })
            ->orderBy("periodo", "desc")
            ->orderBy("gestion", "desc")
            ->get()->map(function ($campeonato) use ($fecha_ini, $fecha_fin, $carrera_id) {
                $partidos = Partido::with(['ci_local', 'ci_visitante', 'campeonato']);

                if ($fecha_ini && $fecha_fin) {
                    $partidos->whereBetween('fecha', [$fecha_ini, $fecha_fin]);
                }

                if ($carrera_id && $carrera_id != "todos") {
                    $partidos->where(function ($q) use ($carrera_id) {
                        $q->where("local_id", $carrera_id)
                            ->orWhere("visitante_id", $carrera_id);
                    });
                }

                $partidos = $partidos
                    ->where("campeonato_id", $campeonato->id)
                    ->where("estado", "FINALIZADO")
                    ->get();

                $campeonato->partidos = $partidos;
                return $campeonato;
            });


        $pdf = PDF::loadView('reportes.resultado_partidos', compact('campeonatos'))->setPaper('letter', 'portrait');

        // ENUMERAR LAS PÁGINAS USANDO CANVAS
        $pdf->output();
        $dom_pdf = $pdf->getDomPDF();
        $canvas = $dom_pdf->get_canvas();
        $alto = $canvas->get_height();
        $ancho = $canvas->get_width();
        $canvas->page_text($ancho - 90, $alto - 25, "Página {PAGE_NUM} de {PAGE_COUNT}", null, 9, array(0, 0, 0));

        return $pdf->stream('resultado_partidos.pdf');
    }

    public function r_fixture(Request $request)
    {

        ini_set('memory_limit', '1024M');
        set_time_limit(-1);

        $fecha_ini = $request->fecha_ini;
        $fecha_fin = $request->fecha_fin;
        $campeonato_id = $request->campeonato_id;


        $partidos = Partido::where("campeonato_id", $campeonato_id)
            ->where("estado", "PENDIENTE");

        if ($fecha_ini && $fecha_fin) {
            $partidos->whereBetween("fecha", [$fecha_ini, $fecha_fin]);
        }

        $partidos = $partidos->get();

        $campeonato = Campeonato::findOrFail($campeonato_id);
        $pdf = PDF::loadView('reportes.fixture', compact('partidos', 'campeonato'))->setPaper('letter', 'portrait');

        // ENUMERAR LAS PÁGINAS USANDO CANVAS
        $pdf->output();
        $dom_pdf = $pdf->getDomPDF();
        $canvas = $dom_pdf->get_canvas();
        $alto = $canvas->get_height();
        $ancho = $canvas->get_width();
        $canvas->page_text($ancho - 90, $alto - 25, "Página {PAGE_NUM} de {PAGE_COUNT}", null, 9, array(0, 0, 0));

        return $pdf->stream('fixture.pdf');
    }

    public function r_partido_detalles(Request $request)
    {

        ini_set('memory_limit', '1024M');
        set_time_limit(-1);

        $partido_id = $request->partido_id;

        $partido = Partido::findOrFail($partido_id);

        $local_detalles = PartidoDetalle::with(["carrera_jugador.jugador"])
            ->where("partido_id", $partido->id)
            ->where("campeonato_inscripcion_id", $partido->ci_local_id)
            ->get();

        $visitante_detalles = PartidoDetalle::with(["carrera_jugador.jugador"])
            ->where("partido_id", $partido->id)
            ->where("campeonato_inscripcion_id", $partido->ci_visitante_id)
            ->get();

        $campeonato = Campeonato::findOrFail($partido->campeonato_id);
        $pdf = PDF::loadView('reportes.partido_detalles', compact('partido', 'campeonato', 'local_detalles', 'visitante_detalles'))->setPaper('letter', 'portrait');

        // ENUMERAR LAS PÁGINAS USANDO CANVAS
        $pdf->output();
        $dom_pdf = $pdf->getDomPDF();
        $canvas = $dom_pdf->get_canvas();
        $alto = $canvas->get_height();
        $ancho = $canvas->get_width();
        $canvas->page_text($ancho - 90, $alto - 25, "Página {PAGE_NUM} de {PAGE_COUNT}", null, 9, array(0, 0, 0));

        return $pdf->stream('partido_detalles.pdf');
    }

    public function goleadores()
    {
        return Inertia::render("Admin/Reportes/Goleadores");
    }

    public function r_goleadores(Request $request)
    {
        ini_set('memory_limit', '1024M');
        set_time_limit(-1);

        $campeonato_id = $request->campeonato_id;
        $carrera_jugadors = $this->campeonatoInscripcionService->listadoGoleadores($campeonato_id);

        $campeonato = Campeonato::findOrFail($campeonato_id);
        $pdf = PDF::loadView('reportes.goleadores', compact('carrera_jugadors', 'campeonato'))->setPaper('letter', 'portrait');

        // ENUMERAR LAS PÁGINAS USANDO CANVAS
        $pdf->output();
        $dom_pdf = $pdf->getDomPDF();
        $canvas = $dom_pdf->get_canvas();
        $alto = $canvas->get_height();
        $ancho = $canvas->get_width();
        $canvas->page_text($ancho - 90, $alto - 25, "Página {PAGE_NUM} de {PAGE_COUNT}", null, 9, array(0, 0, 0));

        return $pdf->stream('goleadores.pdf');
    }

    public function porteros()
    {
        return Inertia::render("Admin/Reportes/Porteros");
    }

    public function r_porteros(Request $request)
    {
        ini_set('memory_limit', '1024M');
        set_time_limit(-1);

        $campeonato_id = $request->campeonato_id;
        $carrera_jugadors = $this->campeonatoInscripcionService->listadoPorteros($campeonato_id);

        $campeonato = Campeonato::findOrFail($campeonato_id);
        $pdf = PDF::loadView('reportes.porteros', compact('carrera_jugadors', 'campeonato'))->setPaper('letter', 'portrait');

        // ENUMERAR LAS PÁGINAS USANDO CANVAS
        $pdf->output();
        $dom_pdf = $pdf->getDomPDF();
        $canvas = $dom_pdf->get_canvas();
        $alto = $canvas->get_height();
        $ancho = $canvas->get_width();
        $canvas->page_text($ancho - 90, $alto - 25, "Página {PAGE_NUM} de {PAGE_COUNT}", null, 9, array(0, 0, 0));

        return $pdf->stream('porteros.pdf');
    }

    public function pagos_pendientes()
    {
        return Inertia::render("Admin/Reportes/PagosPendientes");
    }

    public function r_pagos_pendientes(Request $request)
    {
        ini_set('memory_limit', '1024M');
        set_time_limit(-1);

        $fecha_ini = $request->fecha_ini;
        $fecha_fin = $request->fecha_fin;
        $campeonato_id = $request->campeonato_id;
        $carrera_id = $request->carrera_id;

        $campeonatos = Campeonato::select("campeonatos.*")
            ->when($campeonato_id && $campeonato_id != "todos", function ($query) use ($campeonato_id) {
                return $query->where('id', $campeonato_id);
            })
            ->orderBy("periodo", "desc")
            ->orderBy("gestion", "desc")
            ->get()->map(function ($campeonato) use ($fecha_ini, $fecha_fin, $carrera_id) {

                $campeonato_inscripcions = CampeonatoInscripcion::with(['carrera'])
                    ->where("campeonato_id", $campeonato->id);

                $campeonato_inscripcions->when($carrera_id && $carrera_id != "todos", function ($q) use ($carrera_id) {
                    $q->where("carrera_id", $carrera_id);
                });

                // VERIFICAR DEUDAS
                $campeonato_inscripcions = $campeonato_inscripcions->get()
                    ->map(function ($campeonato_inscripcion) use ($fecha_ini, $fecha_fin) {

                        // POR INSCRIPCION
                        $campeonato_inscripcion->por_inscripcion = CampeonatoInscripcion::where("id", $campeonato_inscripcion->id)
                            ->where("pago_inscripcion", 0)
                            ->when($fecha_ini && $fecha_fin, function ($q) use ($fecha_ini, $fecha_fin) {
                                $q->whereBetween('fecha', [$fecha_ini, $fecha_fin]);
                            })
                            ->get()->first();

                        // DERECHO DE CANCHA LOCAL
                        $campeonato_inscripcion->partidos_local_pendientes = Partido::where("ci_local_id", $campeonato_inscripcion->id)
                            ->where("total_local", ">", 0)
                            ->where("pago_local", 0)
                            ->when($fecha_ini && $fecha_fin, function ($q) use ($fecha_ini, $fecha_fin) {
                                $q->whereBetween('fecha', [$fecha_ini, $fecha_fin]);
                            })
                            ->get();

                        // DERECHO DE CANCHA VISITANTE
                        $campeonato_inscripcion->partidos_visitante_pendientes = Partido::where("ci_visitante_id", $campeonato_inscripcion->id)
                            ->where("total_visitante", ">", 0)
                            ->where("pago_visitante", 0)
                            ->when($fecha_ini && $fecha_fin, function ($q) use ($fecha_ini, $fecha_fin) {
                                $q->whereBetween('fecha', [$fecha_ini, $fecha_fin]);
                            })
                            ->get();


                        $campeonato_inscripcion->partido_detalles_pendientes = PartidoDetalle::where("campeonato_inscripcion_id", $campeonato_inscripcion->id)
                            ->when($fecha_ini && $fecha_fin, function ($q) use ($fecha_ini, $fecha_fin) {
                                $q->whereHas("partido", function ($q2) use ($fecha_ini, $fecha_fin) {
                                    $q2->whereBetween('fecha', [$fecha_ini, $fecha_fin]);
                                });
                            })
                            ->where(function ($q) {
                                $q->where(function ($q2) {
                                    $q2->where("pagado_amarillas", 0)
                                        ->where("total_amarillas", ">", 0);
                                })
                                    ->orWhere(function ($q2) {
                                        $q2->where("pagado_rojas", 0)
                                            ->where("total_rojas", ">", 0);
                                    });
                            })
                            ->get();

                        return $campeonato_inscripcion;
                    });
                $campeonato->campeonato_inscripcion_pendientes = $campeonato_inscripcions;

                return $campeonato;
            });


        $pdf = PDF::loadView('reportes.pagos_pendientes', compact('campeonatos'))->setPaper('letter', 'portrait');

        // ENUMERAR LAS PÁGINAS USANDO CANVAS
        $pdf->output();
        $dom_pdf = $pdf->getDomPDF();
        $canvas = $dom_pdf->get_canvas();
        $alto = $canvas->get_height();
        $ancho = $canvas->get_width();
        $canvas->page_text($ancho - 90, $alto - 25, "Página {PAGE_NUM} de {PAGE_COUNT}", null, 9, array(0, 0, 0));

        return $pdf->stream('pagos_pendientes.pdf');
    }
}
