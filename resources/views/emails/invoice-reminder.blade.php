<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recordatorio de Factura</title>
</head>
<body style="margin: 0; padding: 0; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background-color: #f5f5f5; color: #1f2937; line-height: 1.6;">
    
    <!-- Main Container -->
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color: #f5f5f5; padding: 40px 20px;">
        <tr>
            <td align="center">
                <table role="presentation" width="600" cellpadding="0" cellspacing="0" style="background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.05);">
                    
                    <!-- Header -->
                    <tr>
                        <td style="background-color: #1f2937; padding: 32px; text-align: center;">
                            <img src="https://res.cloudinary.com/dspoaxmvn/image/upload/v1768692272/gr_elohvv.png" alt="{{ config('app.name') }}" style="max-height: 40px; margin-bottom: 12px;">
                            <h1 style="color: #ffffff; font-size: 18px; font-weight: 600; margin: 0;">Recordatorio de Factura</h1>
                        </td>
                    </tr>

                    <!-- Content -->
                    <tr>
                        <td style="padding: 40px 32px;">
                            
                            <!-- Greeting -->
                            <p style="font-size: 16px; font-weight: 600; margin: 0 0 16px 0; color: #1f2937;">
                                Hola {{ $contact->name }},
                            </p>
                            
                            <p style="color: #4b5563; margin: 0 0 32px 0; font-size: 14px;">
                                Le recordamos que tiene una factura pendiente de pago. A continuación encontrará los detalles:
                            </p>

                            <!-- Invoice Info Card -->
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color: #f9fafb; border: 1px solid #e5e7eb; border-radius: 8px; margin-bottom: 24px;">
                                <tr>
                                    <td style="padding: 20px;">
                                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                                            <!-- Invoice Number -->
                                            <tr>
                                                <td style="padding: 8px 0; border-bottom: 1px solid #e5e7eb; font-size: 13px; color: #6b7280;">Factura</td>
                                                <td style="padding: 8px 0; border-bottom: 1px solid #e5e7eb; font-size: 13px; font-weight: 600; color: #1f2937; text-align: right;">{{ $invoice->number }}</td>
                                            </tr>
                                            <!-- Client -->
                                            <tr>
                                                <td style="padding: 8px 0; border-bottom: 1px solid #e5e7eb; font-size: 13px; color: #6b7280;">Cliente</td>
                                                <td style="padding: 8px 0; border-bottom: 1px solid #e5e7eb; font-size: 13px; font-weight: 600; color: #1f2937; text-align: right;">{{ $company->name }}</td>
                                            </tr>
                                            <!-- Due Date -->
                                            <tr>
                                                <td style="padding: 8px 0; border-bottom: 1px solid #e5e7eb; font-size: 13px; color: #6b7280;">Vencimiento</td>
                                                <td style="padding: 8px 0; border-bottom: 1px solid #e5e7eb; font-size: 13px; font-weight: 600; color: #1f2937; text-align: right;">
                                                    {{ \Carbon\Carbon::parse($invoice->due_date)->format('d M Y') }}
                                                </td>
                                            </tr>
                                            <!-- Status -->
                                            <tr>
                                                <td style="padding: 8px 0; border-bottom: 1px solid #e5e7eb; font-size: 13px; color: #6b7280;">Estado</td>
                                                <td style="padding: 8px 0; border-bottom: 1px solid #e5e7eb; font-size: 13px; text-align: right;">
                                                    @if($urgencyLevel === 'overdue')
                                                        <span style="display: inline-block; padding: 4px 10px; border-radius: 12px; font-size: 11px; font-weight: 600; background-color: #dc2626; color: #ffffff;">Vencida hace {{ abs($daysUntilDue) }} días</span>
                                                    @elseif($urgencyLevel === 'urgent')
                                                        <span style="display: inline-block; padding: 4px 10px; border-radius: 12px; font-size: 11px; font-weight: 600; background-color: #fee2e2; color: #dc2626;">Vence en {{ $daysUntilDue }} días</span>
                                                    @elseif($urgencyLevel === 'warning')
                                                        <span style="display: inline-block; padding: 4px 10px; border-radius: 12px; font-size: 11px; font-weight: 600; background-color: #fef3c7; color: #d97706;">Vence en {{ $daysUntilDue }} días</span>
                                                    @else
                                                        <span style="display: inline-block; padding: 4px 10px; border-radius: 12px; font-size: 11px; font-weight: 600; background-color: #dbeafe; color: #1d4ed8;">Vence en {{ $daysUntilDue }} días</span>
                                                    @endif
                                                </td>
                                            </tr>
                                            <!-- Amount Due (highlighted) -->
                                            <tr>
                                                <td style="padding: 12px 0 0 0; font-size: 14px; font-weight: 600; color: #1f2937;">Monto Pendiente</td>
                                                <td style="padding: 12px 0 0 0; font-size: 18px; font-weight: 700; color: #1f2937; text-align: right;">${{ number_format($invoice->balance_due, 2) }} {{ $invoice->currency }}</td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>

                            <!-- Items Section -->
                            @if($items && count($items) > 0)
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin-bottom: 24px;">
                                <tr>
                                    <td style="padding-bottom: 8px;">
                                        <p style="font-size: 12px; font-weight: 600; color: #6b7280; text-transform: uppercase; margin: 0;">Conceptos Facturados</p>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border: 1px solid #e5e7eb; border-radius: 6px;">
                                            @foreach($items as $index => $item)
                                            <tr>
                                                <td style="padding: 10px 12px; font-size: 13px; color: #374151; {{ $index < count($items) - 1 ? 'border-bottom: 1px solid #e5e7eb;' : '' }}">
                                                    {{ $item->description }}
                                                </td>
                                                <td style="padding: 10px 12px; font-size: 13px; font-weight: 600; color: #1f2937; text-align: right; white-space: nowrap; {{ $index < count($items) - 1 ? 'border-bottom: 1px solid #e5e7eb;' : '' }}">
                                                    ${{ number_format($item->total, 2) }}
                                                </td>
                                            </tr>
                                            @endforeach
                                        </table>
                                    </td>
                                </tr>
                            </table>
                            @endif

                            <!-- CTA Button -->
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin: 32px 0;">
                                <tr>
                                    <td align="center">
                                        <a href="{{ $portalUrl }}" style="display: inline-block; background-color: #1f2937; color: #ffffff; text-decoration: none; padding: 14px 32px; border-radius: 6px; font-weight: 600; font-size: 14px;">
                                            Ver Factura en el Portal
                                        </a>
                                    </td>
                                </tr>
                            </table>

                            <!-- Footer Note -->
                            <p style="color: #9ca3af; font-size: 12px; text-align: center; margin: 0;">
                                Si ya realizó el pago, por favor ignore este mensaje.<br>
                                El pago puede tardar hasta 24 horas en reflejarse en nuestro sistema.
                            </p>

                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="background-color: #f9fafb; padding: 24px 32px; text-align: center; border-top: 1px solid #e5e7eb;">
                            <p style="color: #9ca3af; font-size: 11px; margin: 0 0 4px 0;">
                                Este es un correo automático enviado por {{ config('app.name') }}
                            </p>
                            <p style="color: #9ca3af; font-size: 11px; margin: 0;">
                                © {{ date('Y') }} {{ config('app.name') }}. Todos los derechos reservados.
                            </p>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>

</body>
</html>
