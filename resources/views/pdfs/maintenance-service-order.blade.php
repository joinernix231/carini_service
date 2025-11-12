<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Orden de Servicio #{{ $maintenance->id }}</title>
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
            <td class="value-cell" colspan="3">{{ $maintenance->id }}</td>
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

    <!-- REPUESTOS -->
    <table>
        <tr>
            <th style="width: 85%;">REPUESTOS UTILIZADOS</th>
            <th style="width: 15%;">CANTIDAD</th>
        </tr>
        <tr>
            <td style="height: 38px; vertical-align: top;">{{ $serviceReport->spare_parts_used ?? '' }}</td>
            <td style="vertical-align: top; text-align: center;">{{ $serviceReport->spare_parts_quantity ?? 0 }}</td>
        </tr>
    </table>

    <!-- ESTADO DEL EQUIPO -->
    <table>
        <tr>
            <th>ESTADO DEL EQUIPO</th>
        </tr>
        <tr>
            <td style="padding: 3px 4px;">
                FUNCIONANDO {{ ($serviceReport && $serviceReport->equipment_status === 'FUNCIONANDO') ? '☑' : '☐' }}&nbsp;&nbsp;
                EN REPARACIÓN {{ ($serviceReport && $serviceReport->equipment_status === 'EN_REPARACION') ? '☑' : '☐' }}&nbsp;&nbsp;
                FUERA DE SERVICIO {{ ($serviceReport && $serviceReport->equipment_status === 'FUERA_DE_SERVICIO') ? '☑' : '☐' }}&nbsp;&nbsp;
                OTRO {{ ($serviceReport && $serviceReport->equipment_status === 'OTRO') ? '☑' : '☐' }}
            </td>
        </tr>
    </table>

    <!-- FACTURACIÓN -->
    <table>
        <tr>
            <td style="width: 50%; padding: 3px 4px;">
                FACTURAR EL SERVICIO: 
                NO {{ ($serviceReport && !$serviceReport->bill_service) ? '☑' : '☐' }} 
                SI {{ ($serviceReport && $serviceReport->bill_service) ? '☑' : '☐' }} 
                ${{ $serviceReport && $serviceReport->service_amount ? number_format($serviceReport->service_amount, 2, ',', '.') : '' }}
            </td>
            <td style="width: 50%; padding: 3px 4px;">
                FACTURAR VIATICOS: 
                NO {{ ($serviceReport && !$serviceReport->bill_expenses) ? '☑' : '☐' }} 
                SI {{ ($serviceReport && $serviceReport->bill_expenses) ? '☑' : '☐' }} 
                ${{ $serviceReport && $serviceReport->expenses_amount ? number_format($serviceReport->expenses_amount, 2, ',', '.') : '' }}
            </td>
        </tr>
        <tr>
            <td style="padding: 3px 4px;">
                VIATICOS RECIBIDOS: 
                NO {{ ($serviceReport && !$serviceReport->expenses_received) ? '☑' : '☐' }} 
                SI {{ ($serviceReport && $serviceReport->expenses_received) ? '☑' : '☐' }} 
                ${{ $serviceReport && $serviceReport->expenses_amount_received ? number_format($serviceReport->expenses_amount_received, 2, ',', '.') : '' }}
            </td>
            <td style="padding: 3px 4px;">
                ALIMENTACIÓN: NO {{ ($serviceReport && !$serviceReport->food) ? '☑' : '☐' }} SI {{ ($serviceReport && $serviceReport->food) ? '☑' : '☐' }}&nbsp;&nbsp;
                HOSPEDAJE: NO {{ ($serviceReport && !$serviceReport->lodging) ? '☑' : '☐' }} SI {{ ($serviceReport && $serviceReport->lodging) ? '☑' : '☐' }}&nbsp;&nbsp;
                TRANSPORTE: NO {{ ($serviceReport && !$serviceReport->transport) ? '☑' : '☐' }} SI {{ ($serviceReport && $serviceReport->transport) ? '☑' : '☐' }}
            </td>
        </tr>
    </table>

    <!-- RECOMENDACIONES -->
    <table>
        <tr>
            <th>RECOMENDACIONES / SERVICIOS PENDIENTES</th>
        </tr>
        <tr>
            <td style="height: 38px; vertical-align: top;">{{ $maintenance->recommendations ?? '' }}</td>
        </tr>
    </table>

    <!-- CLIENTE -->
    <table>
        <tr>
            <th colspan="2">PARA SER DILIGENCIADO POR EL CLIENTE</th>
        </tr>
        <tr>
            <td style="width: 50%; padding: 3px 4px;">REPRESENTANTE:</td>
            <td style="width: 50%; padding: 3px 4px;">FECHA Y HORA DE LLEGADA:<br>FECHA Y HORA DE SALIDA:</td>
        </tr>
        <tr>
            <td style="padding: 3px 4px;">RECIBÍ A SATISFACCIÓN &nbsp; SI ☐ NO ☐</td>
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
            <td style="width: 35%; padding: 3px 4px;">TECNICO ASIGNADO</td>
            <td style="height: 35px;"></td>
            <td style="width: 20%; padding: 3px 4px;">FECHA</td>
        </tr>
        <tr>
            <td style="padding: 3px 4px;">AUTORIZADO POR</td>
            <td style="height: 35px;"></td>
            <td style="padding: 3px 4px;">FECHA</td>
        </tr>
    </table>
</body>
</html>