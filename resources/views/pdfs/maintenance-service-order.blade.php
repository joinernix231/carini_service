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
            padding: 3px 4px;
        }

        .label-cell {
            width: 80px;
            font-weight: bold;
        }

        .value-cell {
            text-align: center;
        }
    </style>
</head>
<body>
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
            <th colspan="4">ORDEN DE SERVICIO</th>
        </tr>
        <tr>
            <td class="label-cell">FECHA</td>
            <td class="value-cell">
                {{ $maintenance->date_maintenance ? $maintenance->date_maintenance->format('d/m/Y') : now()->format('d/m/Y') }}
            </td>
            <td class="label-cell">VERSIÓN</td>
            <td class="value-cell">{{ $serviceReport->version ?? '1.0' }}</td>
        </tr>
        <tr>
            <td class="label-cell">ORDEN #</td>
            <td class="value-cell">{{ $maintenance->id }}</td>
            <td class="label-cell">DISPOSITIVO</td>
            <td class="value-cell">{{ ($device->brand ?? '') . ' ' . ($device->model ?? '') . ' - ' . ($clientDevice->serial ?? '') }}</td>
        </tr>
    </table>

    <!-- DESCRIPCIÓN DE ACTIVIDADES -->
    <table>
        <tr>
            <th>DESCRIPCIÓN DE ACTIVIDADES REALIZADAS</th>
        </tr>
        <tr>
            <td style="height: 50px; vertical-align: top;">{{ $serviceReport->activities_description ?? $maintenance->description ?? '' }}</td>
        </tr>
    </table>

    <!-- REPUESTOS UTILIZADOS -->
    <table>
        <tr>
            <th style="width: 85%;">REPUESTOS UTILIZADOS</th>
            <th style="width: 15%;">CANTIDAD</th>
        </tr>
        @php
            $usedSpareParts = $serviceReport->used_spare_parts ?? [];
        @endphp
        @forelse($usedSpareParts as $sparePart)
            <tr>
                <td style="vertical-align: top; padding: 3px 4px;">{{ $sparePart['description'] ?? '' }}</td>
                <td style="vertical-align: top; text-align: center; padding: 3px 4px;">{{ $sparePart['quantity'] ?? 0 }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="2" style="text-align: center; padding: 3px 4px; height: 38px;">No se utilizaron repuestos</td>
            </tr>
        @endforelse
    </table>

   <!-- ESTADO DEL EQUIPO -->
<table>
    <tr>
        <th>ESTADO DEL EQUIPO</th>
    </tr>
        <tr>
            <td style="padding: 3px 4px;">
                FUNCIONANDO [{{ ($serviceReport && $serviceReport->equipment_status === 'FUNCIONANDO') ? 'X' : ' ' }}]&nbsp;&nbsp;
                EN REPARACIÓN [{{ ($serviceReport && $serviceReport->equipment_status === 'EN_REPARACION') ? 'X' : ' ' }}]&nbsp;&nbsp;
                FUERA DE SERVICIO [{{ ($serviceReport && $serviceReport->equipment_status === 'FUERA_DE_SERVICIO') ? 'X' : ' ' }}]&nbsp;&nbsp;
                OTRO [{{ ($serviceReport && $serviceReport->equipment_status === 'OTRO') ? 'X' : ' ' }}]
            </td>
        </tr>
</table>

    <!-- FACTURACIÓN -->
    <table>
        <tr>
            <td style="width: 50%; padding: 3px 4px;">
                FACTURAR EL SERVICIO: 
                NO [{{ ($serviceReport && !$serviceReport->bill_service) ? 'X' : ' ' }}] 
                SI [{{ ($serviceReport && $serviceReport->bill_service) ? 'X' : ' ' }}] 
                ${{ $serviceReport && $serviceReport->service_amount ? (fmod($serviceReport->service_amount, 1) == 0 ? number_format($serviceReport->service_amount, 0, ',', '.') : number_format($serviceReport->service_amount, 2, ',', '.')) : '' }}
            </td>
            <td style="width: 50%; padding: 3px 4px;">
                FACTURAR VIATICOS: 
                NO [{{ ($serviceReport && !$serviceReport->bill_expenses) ? 'X' : ' ' }}] 
                SI [{{ ($serviceReport && $serviceReport->bill_expenses) ? 'X' : ' ' }}] 
                ${{ $serviceReport && $serviceReport->expenses_amount ? (fmod($serviceReport->expenses_amount, 1) == 0 ? number_format($serviceReport->expenses_amount, 0, ',', '.') : number_format($serviceReport->expenses_amount, 2, ',', '.')) : '' }}
            </td>
        </tr>
        <tr>
            <td style="padding: 3px 4px;">
                VIATICOS RECIBIDOS: 
                NO [{{ ($serviceReport && !$serviceReport->expenses_received) ? 'X' : ' ' }}] 
                SI [{{ ($serviceReport && $serviceReport->expenses_received) ? 'X' : ' ' }}] 
                ${{ $serviceReport && $serviceReport->expenses_amount_received ? (fmod($serviceReport->expenses_amount_received, 1) == 0 ? number_format($serviceReport->expenses_amount_received, 0, ',', '.') : number_format($serviceReport->expenses_amount_received, 2, ',', '.')) : '' }}
            </td>
            <td style="padding: 3px 4px;">
                ALIMENTACIÓN: NO [{{ ($serviceReport && !$serviceReport->food) ? 'X' : ' ' }}] SI [{{ ($serviceReport && $serviceReport->food) ? 'X' : ' ' }}]&nbsp;&nbsp;
                HOSPEDAJE: NO [{{ ($serviceReport && !$serviceReport->lodging) ? 'X' : ' ' }}] SI [{{ ($serviceReport && $serviceReport->lodging) ? 'X' : ' ' }}]&nbsp;&nbsp;
                TRANSPORTE: NO [{{ ($serviceReport && !$serviceReport->transport) ? 'X' : ' ' }}] SI [{{ ($serviceReport && $serviceReport->transport) ? 'X' : ' ' }}]
            </td>
        </tr>
    </table>

    <!-- RECOMENDACIONES -->
    <table>
        <tr>
            <th>RECOMENDACIONES / SERVICIOS PENDIENTES</th>
        </tr>
        <tr>
            <td style="height: 38px; vertical-align: top;">{{ $maintenance->observations ?? '' }}</td>
        </tr>
    </table>

    <!-- CLIENTE -->
    <table>
        <tr>
            <th colspan="2">PARA SER DILIGENCIADO POR EL CLIENTE</th>
        </tr>
        <tr>
            <td style="width: 50%; padding: 3px 4px;">REPRESENTANTE: {{ $client ? $client->name : '' }}</td>
            <td style="width: 50%; padding: 3px 4px;">
                FECHA Y HORA DE LLEGADA: {{ $arrivalLog ? $arrivalLog->timestamp->format('d/m/Y H:i') : '' }}<br>
                FECHA Y HORA DE SALIDA: {{ $endLog ? $endLog->timestamp->format('d/m/Y H:i') : '' }}
            </td>
        </tr>
        <tr>
            <td style="padding: 3px 4px;">RECIBÍ A SATISFACCIÓN &nbsp; SI [X] NO [ ]</td>
            <td rowspan="2" style="height: 55px; vertical-align: top;"></td>
        </tr>
        <tr>
            <td style="vertical-align: top; padding: 3px 4px; height: 55px;">OBSERVACIONES: {{ $serviceReport->client_observations ?? '' }}</td>
        </tr>
    </table>

   <!-- FIRMAS -->
   <table>
        <tr>
            <th colspan="3">FIRMA CLIENTE</th>
        </tr>
        <tr>
            <td colspan="3" style="height: 55px; text-align: center; vertical-align: middle; padding: 5px;">
                @if(!empty($signatureBase64))
                    <img src="{{ $signatureBase64 }}" style="max-height: 200px; max-width: 150px; display: inline-block;">
                @else
                    &nbsp;
                @endif
            </td>
        </tr>
        <tr>
            <td style="width: 35%; padding: 3px 4px;">TECNICO ASIGNADO:</td>
            <td style="height: 35px;"> {{ $technician && $technician->user ? $technician->user->name : '' }}</td>
            <td style="width: 20%; padding: 3px 4px;">FECHA: {{ $serviceReport ? $serviceReport->created_at->format('d/m/Y') : ($maintenance->updated_at ? $maintenance->updated_at->format('d/m/Y') : '') }}</td>
        </tr>
        <tr>
            <td style="padding: 3px 4px;">AUTORIZADO POR:</td>
            <td style="height: 35px;">{{ $client ? $client->name : '' }}</td>
            <td style="padding: 3px 4px;">FECHA: {{ $serviceReport ? $serviceReport->created_at->format('d/m/Y') : ($maintenance->updated_at ? $maintenance->updated_at->format('d/m/Y') : '') }}</td>
        </tr>
    </table>
</body>
</html>