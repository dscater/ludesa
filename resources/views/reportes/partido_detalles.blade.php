td
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>PartidoDetalles</title>
    <style type="text/css">
        * {
            font-family: sans-serif;
        }

        @page {
            margin-top: 1.5cm;
            margin-bottom: 1.5cm;
            margin-left: 2cm;
            margin-right: 1.5cm;
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

        .nueva_pagina {
            page-break-before: always;
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
        <h4 class="texto">DETALLE DE PARTIDO</h4>
        <h4 class="texto">{{ $campeonato->full_name }}</h4>
        <h4 class="fecha">Expedido: {{ date('d-m-Y') }}</h4>
    </div>
    <table border="1">
        <thead class="bg-principal">
            <tr>
                <th>Local: {{ $partido->ci_local->carrera->nombre }}</th>
            </tr>
            <tr>
                <th>Goles: {{ $partido->goles_local }}</th>
            </tr>
        </thead>
    </table>
    <table border="1" style="margin-top: 0;">
        <thead class="bg-principal">
            <tr>
                <th width="3%">N°</th>
                <th width="10%">Foto</th>
                <th width="47%">Jugador</th>
                <th width="10%">Titular</th>
                <th width="10%">Goles</th>
                <th width="10%">Amarillas</th>
                <th width="10%">Rojas</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($local_detalles as $item)
                <tr>
                    <td>{{ $item->carrera_jugador->nro }}</td>
                    <td class="centreado">
                        <img src="{{ $item->carrera_jugador->jugador->foto_b64 }}" width="30px" alt="">
                    </td>
                    <td>{{ $item->carrera_jugador->jugador->nombres }} {{ $item->carrera_jugador->jugador->apes }}</td>
                    <td class="centreado">{{ $item->titular ? 'SI' : 'NO' }}</td>
                    <td class="centreado">{{ $item->goles }}</td>
                    <td class="centreado">{{ $item->amarillas }}</td>
                    <td class="centreado">{{ $item->rojas }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table border="1">
        <thead class="bg-principal">
            <tr>
                <th>Visitante: {{ $partido->ci_visitante->carrera->nombre }}</th>
            </tr>
            <tr>
                <th>Goles: {{ $partido->goles_visitante }}</th>
            </tr>
        </thead>
    </table>
    <table border="1" style="margin-top: 0;">
        <thead class="bg-principal">
            <tr>
                <th width="3%">N°</th>
                <th width="10%">Foto</th>
                <th width="47%">Jugador</th>
                <th width="10%">Titular</th>
                <th width="10%">Goles</th>
                <th width="10%">Amarillas</th>
                <th width="10%">Rojas</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($visitante_detalles as $item)
                <tr>
                    <td>{{ $item->carrera_jugador->nro }}</td>
                    <td class="centreado">
                        <img src="{{ $item->carrera_jugador->jugador->foto_b64 }}" width="30px" alt="">
                    </td>
                    <td>{{ $item->carrera_jugador->jugador->nombres }} {{ $item->carrera_jugador->jugador->apes }}
                    </td>
                    <td class="centreado">{{ $item->titular ? 'SI' : 'NO' }}</td>
                    <td class="centreado">{{ $item->goles }}</td>
                    <td class="centreado">{{ $item->amarillas }}</td>
                    <td class="centreado">{{ $item->rojas }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>

</html>
