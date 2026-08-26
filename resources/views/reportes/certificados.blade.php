<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Certificados</title>
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

        .txt_center {
            font-weight: bold;
            text-align: center;
        }


        .gray {
            background: rgb(202, 202, 202);
        }

        .bg-principal {
            background: #153f59;
            color: white;
        }

        .img_celda img {
            width: 45px;
        }

        .derecha {
            text-align: right;
        }

        .lista {
            border: solid 1px;
            padding-left: 4px;
            margin-left: 0px;
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
        <h4 class="texto">CERTIFICADOS EMITIDOS</h4>
        <h4 class="fecha">Expedido: {{ date('d-m-Y') }}</h4>
    </div>
    <table border="1">
        <thead class="bg-principal">
            <tr>
                <th width="5%">N°</th>
                <th>NRO. C.I.</th>
                <th>NOMBRE</th>
                <th>AP. PATERNO</th>
                <th>AP. MATERNO</th>
                <th width="6%">EDAD</th>
                <th width="6%">CATEGORÍA</th>
                <th>MÉDICO</th>
                <th>FECHA Y HORA INICIO</th>
                <th>FECHA Y HORA FIN</th>
            </tr>
        </thead>
        <tbody>
            @php
                $cont = 1;
            @endphp
            @foreach ($certificado_detalles as $item)
                <tr>
                    <td class="centreado">{{ $cont++ }}</td>
                    <td>{{ $item->certificado->cliente->full_ci }}</td>
                    <td>{{ $item->certificado->cliente->nombre }}</td>
                    <td>{{ $item->certificado->cliente->paterno }}</td>
                    <td>{{ $item->certificado->cliente->materno }}</td>
                    <td class="centreado">{{ $item->certificado->cliente->edad }}</td>
                    <td class="centreado">{{ $item->categoria }}</td>
                    <td>{{ $item->user->full_name }}</td>
                    <td>{{ $item->fecha_inicio_t }} {{ $item->hora_inicio }}</td>
                    <td>{{ $item->fecha_fin_t }} {{ $item->hora_fin }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>

</html>
