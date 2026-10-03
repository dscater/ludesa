<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>PagosPendientes</title>
    <style type="text/css">
        * {
            font-family: sans-serif;
        }

        @page {
            margin-top: 1.5cm;
            margin-bottom: 0.3cm;
            margin-left: 0.3cm;
            margin-right: 0.3cm;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            margin-top: 20px;
            page-break-before: avoid;
        }

        table thead tr th,
        tbody tr td {
            padding: 3px;
            word-wrap: break-word;
        }

        table thead tr th {
            font-size: 9pt;
        }

        table tbody tr td {
            font-size: 8pt;
        }


        .encabezado {
            width: 100%;
        }

        .logo img {
            position: absolute;
            height: 90px;
            top: -20px;
            left: 0px;
        }

        h2.titulo {
            width: 450px;
            margin: auto;
            margin-top: 0PX;
            margin-bottom: 15px;
            text-align: center;
            font-size: 14pt;
        }

        .texto {
            width: 250px;
            text-align: center;
            margin: auto;
            margin-top: 15px;
            font-weight: bold;
            font-size: 1.1em;
        }

        .fecha {
            width: 250px;
            text-align: center;
            margin: auto;
            margin-top: 15px;
            font-weight: normal;
            font-size: 0.85em;
        }

        .total {
            text-align: right;
            padding-right: 15px;
            font-weight: bold;
        }

        table {
            width: 100%;
        }

        table thead {
            background: rgb(236, 236, 236)
        }

        tr {
            page-break-inside: avoid !important;
        }

        .centreado {
            padding-left: 0px;
            text-align: center;
        }

        .datos {
            margin-left: 15px;
            border-top: solid 1px;
            border-collapse: collapse;
            width: 250px;
        }

        .txt {
            font-weight: bold;
            text-align: right;
            padding-right: 5px;
        }

        .txt_center {
            font-weight: bold;
            text-align: center;
        }

        .b_top {
            border-top: solid 1px black;
        }

        .gray {
            background: rgb(202, 202, 202);
        }

        .bg-principal {
            background: #153f59;
            color: white;
        }

        .bg-ganador {
            background: #a8ffae;
        }

        .img_celda img {
            width: 45px;
        }

        .bold {
            font-weight: bold;
        }

        .derecha {
            text-align: right;
        }

        .nueva_pagina {
            page-break-before: always;
        }
    </style>
</head>

<body>
    @inject('configuracion', 'App\Models\Configuracion')
    @php
        $cont = 0;
    @endphp
    @foreach ($campeonatos as $campeonato)
        <div class="encabezado">
            <div class="logo">
                <img src="{{ $configuracion->first()->logo_b64 }}">
            </div>
            <h2 class="titulo">
                {{ $configuracion->first()->razon_social }}
            </h2>
            <h4 class="texto">PAGOS PENDIENTES</h4>
            <h4 class="texto">{{ $campeonato->full_name }}</h4>
            <h4 class="fecha">Expedido: {{ date('d-m-Y') }}</h4>
        </div>
        <table border="1">
            <thead class="bg-principal">
                <tr>
                    <th>FECHA Y HORA</th>
                    <th>CARRERA</th>
                    <th>INSCRIPCIÓN</th>
                    <th>DERECHO DE CANCHA</th>
                    <th>AMARILLAS</th>
                    <th>ROJAS</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $total_inscripcions = 0;
                    $total_derechos = 0;
                    $total_amarillas = 0;
                    $total_rojas = 0;
                @endphp
                @foreach ($campeonato->campeonato_inscripcion_pendientes as $item)
                    @if ($item->por_inscripcion)
                        <tr>
                            <td>{{ $item->por_inscripcion->fecha_hora_t }}</td>
                            <td>{{ $item->por_inscripcion->carrera->nombre }}</td>
                            <td class="derecha">{{ $item->por_inscripcion->total_inscripcion }}</td>
                            <td class="gray"></td>
                            <td class="gray"></td>
                            <td class="gray"></td>
                        </tr>
                        @php
                            $total_inscripcions += (float) $item->total_inscripcion;
                        @endphp
                    @endif

                    @foreach ($item->partidos_local_pendientes as $partido)
                        <tr>
                            <td>{{ $partido->fecha_hora_t }}</td>
                            <td>{{ $item->carrera->nombre }}</td>
                            <td class="gray"></td>
                            <td class="derecha">{{ $partido->total_local }}</td>
                            <td class="gray"></td>
                            <td class="gray"></td>
                        </tr>

                        @php
                            $total_derechos += (float) $partido->total_local;
                        @endphp
                    @endforeach

                    @foreach ($item->partidos_visitante_pendientes as $partido)
                        <tr>
                            <td>{{ $partido->fecha_hora_t }}</td>
                            <td>{{ $item->carrera->nombre }}</td>
                            <td class="gray"></td>
                            <td class="derecha">{{ $partido->total_visitante }}</td>
                            <td class="gray"></td>
                            <td class="gray"></td>
                        </tr>

                        @php
                            $total_derechos += (float) $partido->total_local;
                        @endphp
                    @endforeach

                    @foreach ($item->partido_detalles_pendientes as $detalle)
                        @if ($detalle->pagado_amarillas == 0 || $detalle->pagado_rojas == 0)
                            <tr>
                                <td>{{ $detalle->partido->fecha_hora_t }}</td>
                                <td>{{ $item->carrera->nombre }}</td>
                                <td class="gray"></td>
                                <td class="gray"></td>
                                <td class="derecha">{{ $detalle->total_amarillas }}</td>
                                <td class="derecha">{{ $detalle->total_rojas }}</td>
                            </tr>

                            @php
                                $total_amarillas += (float) $detalle->total_amarillas;
                                $total_rojas += (float) $detalle->total_rojas;
                            @endphp
                        @endif
                    @endforeach
                @endforeach
                <tr>
                    <td colspan="2" class="total bold">TOTAL</td>
                    <td class="bold derecha">{{ number_format($total_inscripcions, 2, '.', '') }}</td>
                    <td class="bold derecha">{{ number_format($total_derechos, 2, '.', '') }}</td>
                    <td class="bold derecha">{{ number_format($total_amarillas, 2, '.', '') }}</td>
                    <td class="bold derecha">{{ number_format($total_rojas, 2, '.', '') }}</td>
                </tr>
            </tbody>
        </table>

        @php
            $cont++;
        @endphp
        @if ($cont < count($campeonatos))
            <div class="nueva_pagina"></div>
        @endif
    @endforeach
</body>

</html>
