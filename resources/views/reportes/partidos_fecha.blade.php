<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <style>
        @page {
            margin: 30px 25px 45px 25px;
        }

        body {
            font-family: sans-serif;
            font-size: 11px;
        }

        h2,
        h3 {
            text-align: center;
            margin: 5px 0 12px;
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
            text-align: center;
            font-size: 14pt;
        }

        .texto {
            width: 250px;
            text-align: center;
            margin: auto;
            font-weight: bold;
            font-size: 1.1em;
        }

        .fecha {
            width: 250px;
            text-align: center;
            margin: auto;
            font-weight: normal;
            font-size: 0.85em;
        }

        .fecha_table {
            margin-top: 18px;
            margin-bottom: 8px;
            padding: 7px;
            background-color: #e8e8e8;
            font-weight: bold;
            font-size: 13px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            padding: 6px;
        }

        th {
            background-color: #f2f2f2;
        }

        .centro {
            text-align: center;
        }

        .equipo {
            width: 35%;
        }

        .marcador {
            width: 10%;
            text-align: center;
        }

        .bloque-fecha {
            page-break-inside: avoid;
        }
    </style>
</head>

<body>
    @inject('configuracion', 'App\Models\Configuracion')
    <div class="encabezado">
        <div class="logo">
            <img src="{{ $configuracion->first()->logo_b64 }}">
        </div>
        <h2 class="titulo">
            {{ $configuracion->first()->razon_social }}
        </h2>
        <h3 class="texto">CALENDARIO DE PARTIDOS</h3>
        <h4 class="texto">{{ $campeonato->full_name }}</h4>
        <h4 class="fecha">Expedido: {{ date('d-m-Y') }}</h4>
    </div>
    @foreach ($fechas as $nroFecha => $partidosFecha)
        <div class="bloque-fecha">
            <div class="fecha_table">
                FECHA {{ $nroFecha }}
            </div>
            <table border="1">
                <thead>
                    <tr>
                        <th class="centro" width="4%">N°</th>
                        <th class="equipo">Local</th>
                        <th class="marcador">Resultado</th>
                        <th class="equipo">Visitante</th>
                        <th>Fecha y Hora</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach ($partidosFecha as $index => $partido)
                        <tr>
                            <td class="centro">
                                {{ $index + 1 }}
                            </td>
                            <td style="vertical-align: middle; text-align: center; height: 40px;">
                                <table style="margin: 0 auto; border-collapse: collapse;" border="0">
                                    <tr>
                                        <td style="vertical-align: middle; padding: 0 5px 0 0;" width="25%">
                                            <img src="{{ $partido->ci_local->carrera->logo_b64 }}"
                                                style="width: 30px; height: 30px;" alt="Logo">
                                        </td>
                                        <td style="vertical-align: middle; padding: 0;">
                                            {{ $partido->ci_local->carrera->nombre }}
                                        </td>
                                    </tr>
                                </table>
                            </td>
                            <td class="marcador">
                                {{ $partido->goles_local ?? '-' }}
                                -
                                {{ $partido->goles_visitante ?? '-' }}
                            </td>
                            <td style="vertical-align: middle; text-align: center; height: 40px;">
                                <table style="margin: 0 auto; border-collapse: collapse;" border="0">
                                    <tr>
                                        <td style="vertical-align: middle; padding: 0 5px 0 0;" width="25%">
                                            <img src="{{ $partido->ci_visitante->carrera->logo_b64 }}"
                                                style="width: 30px; height: 30px;" alt="Logo">
                                        </td>
                                        <td style="vertical-align: middle; padding: 0;">
                                            {{ $partido->ci_visitante->carrera->nombre }}
                                        </td>
                                    </tr>
                                </table>
                            </td>
                            <td>
                                {{ $partido->fecha && $partido->hora ? date('d/m/Y H:i', strtotime($partido->fecha . ' ' . $partido->hora)) : '-' }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endforeach

</body>

</html>
