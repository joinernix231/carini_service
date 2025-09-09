<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nuevo Mantenimiento Asignado</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f8fafc;
        }

        .email-container {
            max-width: 650px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
        }

        .header {
            background-color: #0077b6;
            padding: 40px 30px;
            text-align: center;
            color: white;
        }

        .logo-image {
            max-height: 200px;
            max-width: 200px;
            margin-bottom: 15px;
            filter: brightness(0) invert(1); /* Hace el logo blanco si es necesario */
        }

        .logo {
            font-size: 100px;
            font-weight: 700;
            margin-bottom: 10px;
            text-transform: uppercase;
            letter-spacing: 2px;
        }

        .header-subtitle {
            font-size: 18px;
            opacity: 0.95;
            font-weight: 500;
            margin-top: 5px;
        }

        .content {
            padding: 40px 30px;
        }

        .greeting {
            font-size: 18px;
            font-weight: 600;
            color: #2d3748;
            margin-bottom: 20px;
        }

        .intro-text {
            font-size: 16px;
            color: #4a5568;
            margin-bottom: 30px;
            line-height: 1.7;
        }

        .maintenance-card {
            background: #f7fafc;
            border-left: 4px solid #00b4d8;
            border-radius: 8px;
            padding: 25px;
            margin: 25px 0;
        }

        .maintenance-title {
            font-size: 20px;
            font-weight: 700;
            color: #2d3748;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .status-badge {
            background: #fef5e7;
            color: #d69e2e;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-top: 25px;
        }

        .info-item {
            background: white;
            padding: 20px;
            border-radius: 8px;
            border: 1px solid #e2e8f0;
        }

        .info-label {
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            color: #a0aec0;
            margin-bottom: 5px;
            letter-spacing: 0.5px;
        }

        .info-value {
            font-size: 15px;
            font-weight: 500;
            color: #2d3748;
        }

        .description-section {
            background: white;
            padding: 20px;
            border-radius: 8px;
            border: 1px solid #e2e8f0;
            margin-top: 20px;
        }

        .cta-section {
            text-align: center;
            margin: 35px 0;
        }

        .cta-button {
            display: inline-block;
            background-color: #00b4d8;
            color: white;
            text-decoration: none;
            padding: 16px 32px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 16px;
            transition: all 0.2s ease;
            box-shadow: 0 4px 15px rgba(0, 180, 216, 0.3);
        }

        .cta-button:hover {
            background-color: #0077b6;
            transform: translateY(-2px);
        }

        .footer {
            background: #2d3748;
            padding: 30px;
            text-align: center;
            color: #a0aec0;
        }

        .footer-text {
            font-size: 14px;
            margin-bottom: 10px;
        }

        .footer-brand {
            font-size: 16px;
            font-weight: 600;
            color: #00b4d8;
        }

        .priority-high {
            background: #fed7d7;
            color: #c53030;
        }

        .priority-medium {
            background: #fef5e7;
            color: #d69e2e;
        }

        .priority-low {
            background: #f0fff4;
            color: #38a169;
        }

        /* Estilos adicionales para estados */
        .status-pending {
            background: #e6f3ff;
            color: #0077b6;
        }

        .status-in-progress {
            background: #fff2e6;
            color: #d69e2e;
        }

        .status-completed {
            background: #f0fff4;
            color: #38a169;
        }

        @media (max-width: 600px) {
            .email-container {
                margin: 10px;
                border-radius: 8px;
            }

            .content {
                padding: 25px 20px;
            }

            .info-grid {
                grid-template-columns: 1fr;
            }

            .maintenance-title {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }
        }
    </style>
</head>
<body>
<div class="email-container">
    <!-- Header -->
    <div class="header">
        <img src="https://joinerdavila.s3.us-east-1.amazonaws.com/logo-c.png" alt="CARINI Logo" class="logo-image">
        <div class="header-subtitle">Sistema de Gestión de Mantenimientos</div>
    </div>

    <!-- Content -->
    <div class="content">
        <div class="greeting">
            ¡Hola {{ $coordinator->name ?? 'Coordinador' }}! 👋
        </div>

        <div class="intro-text">
            Se ha creado un nuevo mantenimiento que requiere tu atención. A continuación encontrarás todos los detalles importantes:
        </div>

        <!-- Maintenance Card -->
        <div class="maintenance-card">
            <div class="maintenance-title">
                🔧 Mantenimiento #{{ $maintenance->id }}
                <span class="status-badge status-{{ strtolower($maintenance->status ?? 'pending') }}">
                        {{ ucfirst($maintenance->status ?? 'Pendiente') }}
                    </span>
            </div>

            <div class="info-grid">
                <!-- Cliente Info -->
                <div class="info-item">
                    <div class="info-label">Cliente</div>
                    <div class="info-value">{{ $client->name ?? 'N/A' }}</div>
                </div>

                <!-- Dispositivo Info -->
                <div class="info-item">
                    <div class="info-label">Dispositivo</div>
                    <div class="info-value">{{ $device->model ?? 'N/A' }}</div>
                </div>

                <!-- Serial -->
                <div class="info-item">
                    <div class="info-label">Serial del Equipo</div>
                    <div class="info-value">{{ $clientDevice->serial ?? 'N/A' }}</div>
                </div>

                <!-- Dirección -->
                <div class="info-item">
                    <div class="info-label">Dirección</div>
                    <div class="info-value">{{ $clientDevice->address ?? 'N/A' }}</div>
                </div>
            </div>

            <!-- Descripción -->
            @if($maintenance->description)
                <div class="description-section">
                    <div class="info-label">Descripción del Mantenimiento</div>
                    <div class="info-value">{{ $maintenance->description }}</div>
                </div>
            @endif
        </div>
        <div class="intro-text" style="margin-top: 30px;">
            Por favor, revisa los detalles y programa el mantenimiento según la disponibilidad del cliente y el técnico asignado.
        </div>
    </div>

    <!-- Footer -->
    <div class="footer">
        <div class="footer-text">
            Este correo fue generado automáticamente por el sistema.
        </div>
        <div class="footer-text">
            Si tienes alguna pregunta, contacta al administrador del sistema.
        </div>
        <div class="footer-brand">CARINI - Gestión Profesional</div>
    </div>
</div>
</body>
</html>
