td
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Fixture</title>
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
        <h4 class="texto">FIXTURE DE PARTIDOS</h4>
        <h4 class="texto">{{ $campeonato->full_name }}</h4>
        <h4 class="fecha">Expedido: {{ date('d-m-Y') }}</h4>
    </div>
    <table border="1">
        <thead class="bg-principal">
            <tr>
                <th width="10%">FECHA Y HORA</th>
                <th>LOCAL</th>
                <th>VISITANTE</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($partidos as $item)
                <tr>
                    <td>{{ $item->fecha_hora_t }}</td>
                    <td style="vertical-align: middle; text-align: center; height: 40px;">
                        <table style="margin: 0 auto; border-collapse: collapse;" border="0">
                            <tr>
                                <td style="vertical-align: middle; padding: 0 5px 0 0;" width="25%">
                                    <img src="{{ $item->ci_local->carrera->logo_b64 }}"
                                        style="width: 30px; height: 30px;" alt="Logo">
                                </td>
                                <td style="vertical-align: middle; padding: 0;">
                                    {{ $item->ci_local->carrera->nombre }}
                                </td>
                            </tr>
                        </table>
                    </td>

                    <td style="vertical-align: middle; text-align: center; height: 40px;">
                        <table style="margin: 0 auto; border-collapse: collapse;" border="0">
                            <tr>
                                <td style="vertical-align: middle; padding: 0 5px 0 0;" width="25%">
                                    <img src="{{ $item->ci_visitante->carrera->logo_b64 }}"
                                        style="width: 30px; height: 30px;" alt="Logo">
                                </td>
                                <td style="vertical-align: middle; padding: 0;">
                                    {{ $item->ci_visitante->carrera->nombre }}
                                </td>
                            </tr>
                        </table>
                    </td>

                </tr>
            @endforeach
        </tbody>
    </table>
</body>

</html>
