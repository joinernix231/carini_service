<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Orden de Servicio #{{ $maintenance->id }} - Dispositivo {{ $device->brand ?? '' }} {{ $device->model ?? '' }}</title>

    <style>
        @page { margin: 1cm; size: letter; }

        body {
            font-family: 'Arial', sans-serif;
            font-size: 10px;
            color: #000;
            margin: 0;
            line-height: 1.2;
        }

        .header {
            display: table;
            width: 100%;
            border: 1px solid #000;
            margin-bottom: 4px;
            table-layout: fixed;
        }

        .header-logo {
            display: table-cell;
            width: 35%;
            border-right: 1px solid #000;
            text-align: center;
            padding: 6px;
            vertical-align: middle;
        }

        .header-logo img {
            width: 160px;
            height: auto;
        }

        .header-dept {
            display: table-cell;
            width: 65%;
            vertical-align: middle;
            text-align: center;
            font-weight: bold;
            font-size: 14px;
            padding: 6px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 4px;
        }

        th, td {
            border: 1px solid #000;
            padding: 3px 4px;
            font-size: 10px;
        }

        th {
            background-color: #d3d3d3;
            font-weight: bold;
            text-align: center;
        }

        .label-cell {
            width: 75px;
            font-weight: bold;
        }

        .value-cell {
            text-align: center;
        }

        .page-break {
            page-break-before: always;
        }

        .photo-card {
            border: 1px solid #000;
            padding: 6px;
            text-align: center;
            vertical-align: top;
            background-color: #f9f9f9;
        }

        .photo-card img {
            max-width: 100%;
            max-height: 200px;
            width: auto;
            height: auto;
            display: block;
            margin: 0 auto 6px;
            border: 1px solid #ccc;
        }

        .photo-info {
            font-size: 9px;
            margin-top: 4px;
            line-height: 1.3;
        }

        .photo-type {
            font-weight: bold;
            color: #333;
            text-transform: uppercase;
            margin-bottom: 2px;
        }

        .photo-date {
            color: #666;
            font-size: 8px;
        }

        .photo-number {
            font-weight: bold;
            color: #000;
            margin-bottom: 4px;
        }
    </style>
</head>

<body>

<!-- Traducción TIPO DE MANTENIMIENTO -->
@php
    $type = strtolower($maintenance->type ?? '');
    $typeTranslated = $type === 'preventive' ? 'Preventivo'
                    : ($type === 'correctivo' ? 'corrective'
                    : ucfirst($type));
@endphp

    <!-- CABECERA -->
<div class="header">
    <div class="header-logo">
        @if(!empty($logoBase64))
            <img src="{{ $logoBase64 }}" alt="Carini Logo">
        @else
            <div style="color: #999;">Logo no disponible</div>
        @endif
    </div>

    <div class="header-dept">
        Departamento de Mantenimiento
    </div>
</div>

<!-- ORDEN DE SERVICIO -->
<table>
    <tr>
        <th colspan="6">ORDEN DE SERVICIO</th>
    </tr>

    <!-- FILA 1 -->
    <tr>
        <td class="label-cell">FECHA</td>
        <td class="value-cell">
            {{ $maintenance->date_maintenance ? $maintenance->date_maintenance->format('d/m/Y') : now()->format('d/m/Y') }}
        </td>

        <td class="label-cell">VERSIÓN</td>
        <td class="value-cell">{{ $serviceReport->version ?? '1.0' }}</td>

        <td class="label-cell">CLIENTE</td>
        <td class="value-cell">{{ $client ? $client->name : '' }}</td>
    </tr>

    <!-- FILA 2 -->
    <tr>
        <td class="label-cell">ORDEN #</td>
        <td class="value-cell">{{ $maintenance->id }}</td>

        <td class="label-cell">DISPOSITIVO</td>
        <td class="value-cell">{{ ($device->brand ?? '') . ' ' . ($device->model ?? '') }}</td>

        <td class="label-cell">SERIAL</td>
        <td class="value-cell">{{ $clientDevice->serial ?? '' }}</td>
    </tr>

    <!-- FILA 3 - TIPO DE MANTENIMIENTO -->
    <tr>
        <td class="label-cell">TIPO DE MANTENIMIENTO</td>

        <td class="value-cell">{{ $typeTranslated }}</td>

        <td class="label-cell">UBICACIÓN</td>
        <td class="value-cell">{{$clientDevice->address}}</td>

        <td class="label-cell">MODELO</td>
        <td class="value-cell">{{$device->model}}</td>
    </tr>
</table>

<!-- DESCRIPCIÓN DE ACTIVIDADES -->
<table>
    <tr><th>DESCRIPCIÓN DE ACTIVIDADES REALIZADAS</th></tr>
    <tr>
        <td style="height: 50px; vertical-align: top;">
            {{ $serviceReport->activities_description ?? $maintenance->description ?? '' }}
        </td>
    </tr>
</table>

<!-- REPUESTOS UTILIZADOS -->
<table>
    <tr>
        <th style="width: 85%;">REPUESTOS UTILIZADOS</th>
        <th style="width: 15%;">CANTIDAD</th>
    </tr>

    @php $usedSpareParts = $serviceReport->used_spare_parts ?? []; @endphp

    @forelse($usedSpareParts as $sparePart)
        <tr>
            <td>{{ $sparePart['description'] ?? '' }}</td>
            <td style="text-align: center;">{{ $sparePart['quantity'] ?? 0 }}</td>
        </tr>
    @empty
        <tr>
            <td colspan="2" style="text-align: center; height: 38px;">No se utilizaron repuestos</td>
        </tr>
    @endforelse
</table>

<!-- ESTADO DEL EQUIPO -->
<table>
    <tr><th>ESTADO DEL EQUIPO</th></tr>
    <tr>
        <td>
            FUNCIONANDO [{{ ($serviceReport && $serviceReport->equipment_status === 'FUNCIONANDO') ? 'X' : ' ' }}]
            &nbsp;&nbsp;
            EN REPARACIÓN [{{ ($serviceReport && $serviceReport->equipment_status === 'EN_REPARACION') ? 'X' : ' ' }}]
            &nbsp;&nbsp;
            FUERA DE SERVICIO [{{ ($serviceReport && $serviceReport->equipment_status === 'FUERA_DE_SERVICIO') ? 'X' : ' ' }}]
            &nbsp;&nbsp;
            OTRO [{{ ($serviceReport && $serviceReport->equipment_status === 'OTRO') ? 'X' : ' ' }}]
        </td>
    </tr>
</table>

<!-- FACTURACIÓN -->
<table>
    <tr>
        <td style="width: 50%;">
            FACTURAR EL SERVICIO:
            NO [{{ ($serviceReport && !$serviceReport->bill_service) ? 'X' : ' ' }}]
            SI [{{ ($serviceReport && $serviceReport->bill_service) ? 'X' : ' ' }}]
            ${{ $serviceReport && $serviceReport->service_amount ? number_format($serviceReport->service_amount, fmod($serviceReport->service_amount,1)==0?0:2, ',', '.') : '' }}
        </td>

        <td style="width: 50%;">
            FACTURAR VIÁTICOS:
            NO [{{ ($serviceReport && !$serviceReport->bill_expenses) ? 'X' : ' ' }}]
            SI [{{ ($serviceReport && $serviceReport->bill_expenses) ? 'X' : ' ' }}]
            ${{ $serviceReport && $serviceReport->expenses_amount ? number_format($serviceReport->expenses_amount, fmod($serviceReport->expenses_amount,1)==0?0:2, ',', '.') : '' }}
        </td>
    </tr>

    <tr>
        <td>
            VIÁTICOS RECIBIDOS:
            NO [{{ ($serviceReport && !$serviceReport->expenses_received) ? 'X' : ' ' }}]
            SI [{{ ($serviceReport && $serviceReport->expenses_received) ? 'X' : ' ' }}]
            ${{ $serviceReport && $serviceReport->expenses_amount_received ? number_format($serviceReport->expenses_amount_received, fmod($serviceReport->expenses_amount_received,1)==0?0:2, ',', '.') : '' }}
        </td>

        <td>
            ALIMENTACIÓN:
            NO [{{ ($serviceReport && !$serviceReport->food) ? 'X' : ' ' }}]
            SI [{{ ($serviceReport && $serviceReport->food) ? 'X' : ' ' }}]
            &nbsp;&nbsp;

            HOSPEDAJE:
            NO [{{ ($serviceReport && !$serviceReport->lodging) ? 'X' : ' ' }}]
            SI [{{ ($serviceReport && $serviceReport->lodging) ? 'X' : ' ' }}]
            &nbsp;&nbsp;

            TRANSPORTE:
            NO [{{ ($serviceReport && !$serviceReport->transport) ? 'X' : ' ' }}]
            SI [{{ ($serviceReport && $serviceReport->transport) ? 'X' : ' ' }}]
        </td>
    </tr>

</table>

<!-- RECOMENDACIONES -->
<table>
    <tr><th>RECOMENDACIONES / SERVICIOS PENDIENTES</th></tr>
    <tr>
        <td style="height: 25px; vertical-align: top;">
            {{ $maintenance->observations ?? '' }}
        </td>
    </tr>
</table>

<!-- CLIENTE -->
<table>
    <tr>
        <th colspan="2">PARA SER DILIGENCIADO POR EL CLIENTE</th>
    </tr>

    <tr>
        <td style="width: 50%;">
            REPRESENTANTE: {{ $client ? $client->name : '' }}
        </td>
        <td style="width: 50%;">
            FECHA Y HORA DE LLEGADA: {{ $arrivalLog ? $arrivalLog->timestamp->format('d/m/Y H:i') : '' }}<br>
        </td>
    </tr>

    <tr>
        <td>
            RECIBÍ A SATISFACCIÓN &nbsp; SI [X] &nbsp; NO [ ]
        </td>
        <td>
            FECHA Y HORA DE SALIDA: {{ $endLog ? $endLog->timestamp->format('d/m/Y H:i') : '' }}
        </td>
    </tr>

    <tr>
        <td colspan="2" style="height: 30px; vertical-align: top;">
            OBSERVACIONES: {{ $serviceReport->client_observations ?? '' }}
        </td>
    </tr>
</table>


<!-- FIRMAS -->
<table>
    <tr><th colspan="3">FIRMA CLIENTE</th></tr>

    <tr>
        <td colspan="3" style="height: 25px; text-align: center;">
            @if(!empty($signatureBase64))
                <img src="{{ $signatureBase64 }}" style="max-height: 155px; max-width: 150px;">
            @endif
        </td>
    </tr>

    <tr>
        <td style="width: 35%;">TÉCNICO ASIGNADO:</td>
        <td>{{ $technician && $technician->user ? $technician->user->name : '' }}</td>
        <td style="width: 20%;">FECHA: {{ $serviceReport ? $serviceReport->created_at->format('d/m/Y') : '' }}</td>
    </tr>

    <tr>
        <td>AUTORIZADO POR:</td>
        <td>{{ $client ? $client->name : '' }}</td>
        <td>FECHA: {{ $serviceReport ? $serviceReport->created_at->format('d/m/Y') : '' }}</td>
    </tr>
</table>

<!-- SEGUNDA PÁGINA: FOTOS DEL MANTENIMIENTO -->
@if(!empty($photosBase64) && count($photosBase64) > 0)
<div class="page-break"></div>

<!-- CABECERA SEGUNDA PÁGINA -->
<div class="header">
    <div class="header-logo">
        @if(!empty($logoBase64))
            <img src="{{ $logoBase64 }}" alt="Carini Logo">
        @else
            <div style="color: #999;">Logo no disponible</div>
        @endif
    </div>

    <div class="header-dept">
        Departamento de Mantenimiento
    </div>
</div>

<!-- INFORMACIÓN DEL MANTENIMIENTO -->
<table>
    <tr>
        <th colspan="6">REGISTRO FOTOGRÁFICO - ORDEN DE SERVICIO #{{ $maintenance->id }}</th>
    </tr>
    <tr>
        <td class="label-cell">CLIENTE</td>
        <td class="value-cell" style="text-align: left;">{{ $client ? $client->name : '' }}</td>
        <td class="label-cell">TÉCNICO</td>
        <td class="value-cell" style="text-align: left;">{{ $technician && $technician->user ? $technician->user->name : '' }}</td>
        <td class="label-cell">FECHA</td>
        <td class="value-cell">{{ $maintenance->date_maintenance ? $maintenance->date_maintenance->format('d/m/Y') : now()->format('d/m/Y') }}</td>
    </tr>
    <tr>
        <td class="label-cell">DISPOSITIVO</td>
        <td class="value-cell" style="text-align: left;">{{ ($device->brand ?? '') . ' ' . ($device->model ?? '') }}</td>
        <td class="label-cell">SERIAL</td>
        <td class="value-cell" style="text-align: left;">{{ $clientDevice->serial ?? 'N/A' }}</td>
        <td class="label-cell">UBICACIÓN</td>
        <td class="value-cell" style="text-align: left;">{{ $clientDevice->address ?? '' }}</td>
    </tr>
    <tr>
        <td class="label-cell">TIPO</td>
        <td class="value-cell" style="text-align: left;">{{ $typeTranslated }}</td>
        <td class="label-cell">TOTAL FOTOS</td>
        <td class="value-cell" colspan="3" style="text-align: left;">{{ count($photosBase64) }} fotografía(s)</td>
    </tr>
</table>

<!-- FOTOS ORGANIZADAS -->
@php
    $typeLabels = [
        'initial' => 'FOTO INICIAL',
        'final' => 'FOTO FINAL',
        'part' => 'REPUESTO',
        'other' => 'FOTO TECNICO'
    ];
    $photosChunks = array_chunk($photosBase64, 2);
@endphp

@foreach($photosChunks as $chunkIndex => $chunk)
<table style="margin-top: 6px;">
    <tr>
        @foreach($chunk as $photoIndex => $photo)
            <td class="photo-card" style="width: 50%;">
                <div class="photo-number">
                    FOTO {{ ($chunkIndex * 2) + $photoIndex + 1 }} de {{ count($photosBase64) }}
                </div>
                <img src="{{ $photo['base64'] }}" alt="Foto del mantenimiento">
                <div class="photo-info">
                    <div class="photo-type">
                        {{ $typeLabels[$photo['type']] ?? 'OTRA' }}
                    </div>
                    @if($photo['created_at'])
                        <div class="photo-date">
                            Fecha: {{ $photo['created_at']->format('d/m/Y H:i') }}
                        </div>
                    @endif
                </div>
            </td>
        @endforeach
        @if(count($chunk) < 2)
            <td class="photo-card" style="width: 50%;"></td>
        @endif
    </tr>
</table>
@endforeach

<!-- RESUMEN POR TIPO -->
@php
    $photosByType = [];
    foreach ($photosBase64 as $photo) {
        $type = $photo['type'] ?? 'other';
        if (!isset($photosByType[$type])) {
            $photosByType[$type] = 0;
        }
        $photosByType[$type]++;
    }
@endphp

@if(count($photosByType) > 0)
<table style="margin-top: 6px;">
    <tr>
        <th colspan="4">RESUMEN DE FOTOGRAFÍAS POR TIPO</th>
    </tr>
    <tr>
        @foreach($typeLabels as $typeKey => $typeLabel)
            <td style="width: 25%; text-align: center;">
                <strong>{{ $typeLabel }}:</strong> {{ $photosByType[$typeKey] ?? 0 }}
            </td>
        @endforeach
    </tr>
</table>
@endif

@endif

</body>
</html>
