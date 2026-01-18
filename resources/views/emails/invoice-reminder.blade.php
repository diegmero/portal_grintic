<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recordatorio de Factura</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, sans-serif;
            background-color: #f5f5f5;
            color: #1f2937;
            line-height: 1.6;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
        }
        .header {
            background-color: #1f2937;
            padding: 32px;
            text-align: center;
        }
        .header img {
            max-height: 40px;
        }
        .header h1 {
            color: #ffffff;
            font-size: 20px;
            font-weight: 600;
            margin-top: 16px;
        }
        .content {
            padding: 40px 32px;
        }
        .greeting {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 16px;
            color: #1f2937;
        }
        .message {
            color: #4b5563;
            margin-bottom: 32px;
        }
        .invoice-card {
            background-color: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            padding: 24px;
            margin-bottom: 32px;
        }
        .invoice-row {
            display: flex;
            justify-content: space-between;
            padding: 12px 0;
            border-bottom: 1px solid #e5e7eb;
        }
        .invoice-row:last-child {
            border-bottom: none;
        }
        .invoice-label {
            color: #6b7280;
            font-size: 14px;
        }
        .invoice-value {
            font-weight: 600;
            color: #1f2937;
            text-align: right;
        }
        .status-badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 600;
        }
        .status-normal {
            background-color: #dbeafe;
            color: #1d4ed8;
        }
        .status-warning {
            background-color: #fef3c7;
            color: #d97706;
        }
        .status-urgent {
            background-color: #fee2e2;
            color: #dc2626;
        }
        .status-overdue {
            background-color: #dc2626;
            color: #ffffff;
        }
        .cta-button {
            display: inline-block;
            background-color: #1f2937;
            color: #ffffff !important;
            text-decoration: none;
            padding: 14px 32px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 16px;
            text-align: center;
        }
        .cta-container {
            text-align: center;
            margin: 32px 0;
        }
        .footer-note {
            color: #9ca3af;
            font-size: 13px;
            text-align: center;
            margin-top: 24px;
        }
        .footer {
            background-color: #f9fafb;
            padding: 24px 32px;
            text-align: center;
            border-top: 1px solid #e5e7eb;
        }
        .footer p {
            color: #9ca3af;
            font-size: 12px;
        }
        .amount-highlight {
            font-size: 24px;
            font-weight: 700;
            color: #1f2937;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <img src="https://res.cloudinary.com/dspoaxmvn/image/upload/v1768692272/gr_elohvv.png" alt="{{ config('app.name') }}">
            <h1>Recordatorio de Factura</h1>
        </div>

        <!-- Content -->
        <div class="content">
            <p class="greeting">Hola {{ $contact->name }},</p>
            
            <p class="message">
                Le recordamos que tiene una factura pendiente de pago. A continuación encontrará los detalles:
            </p>

            <!-- Invoice Card -->
            <div class="invoice-card">
                <div class="invoice-row">
                    <span class="invoice-label">Número de Factura</span>
                    <span class="invoice-value">{{ $invoice->number }}</span>
                </div>
                <div class="invoice-row">
                    <span class="invoice-label">Cliente</span>
                    <span class="invoice-value">{{ $company->name }}</span>
                </div>
                <div class="invoice-row">
                    <span class="invoice-label">Monto Pendiente</span>
                    <span class="invoice-value amount-highlight">${{ number_format($invoice->balance_due, 2) }} {{ $invoice->currency }}</span>
                </div>
                <div class="invoice-row">
                    <span class="invoice-label">Fecha de Vencimiento</span>
                    <span class="invoice-value">{{ \Carbon\Carbon::parse($invoice->due_date)->format('d M Y') }}</span>
                </div>
                <div class="invoice-row">
                    <span class="invoice-label">Estado</span>
                    <span class="invoice-value">
                        @if($urgencyLevel === 'overdue')
                            <span class="status-badge status-overdue">⚠️ Vencida hace {{ abs($daysUntilDue) }} días</span>
                        @elseif($urgencyLevel === 'urgent')
                            <span class="status-badge status-urgent">🔴 Vence en {{ $daysUntilDue }} días</span>
                        @elseif($urgencyLevel === 'warning')
                            <span class="status-badge status-warning">⏳ Vence en {{ $daysUntilDue }} días</span>
                        @else
                            <span class="status-badge status-normal">📅 Vence en {{ $daysUntilDue }} días</span>
                        @endif
                    </span>
                </div>
            </div>

            <!-- CTA Button -->
            <div class="cta-container">
                <a href="{{ $portalUrl }}" class="cta-button">Ver Factura en el Portal</a>
            </div>

            <p class="footer-note">
                Si ya realizó el pago, por favor ignore este mensaje. El pago puede tardar hasta 24 horas en reflejarse en nuestro sistema.
            </p>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p>Este es un correo automático enviado por {{ config('app.name') }}</p>
            <p>© {{ date('Y') }} {{ config('app.name') }}. Todos los derechos reservados.</p>
        </div>
    </div>
</body>
</html>
