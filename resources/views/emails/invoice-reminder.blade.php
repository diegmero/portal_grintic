<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="x-apple-disable-message-reformatting">
    <meta name="format-detection" content="telephone=no, address=no, email=no, date=no">
    <title>Recordatorio de Factura</title>
    <!--[if mso]>
    <style type="text/css">
        body, table, td {font-family: Arial, Helvetica, sans-serif !important;}
    </style>
    <![endif]-->
    <style>
        /* Reset for Gmail */
        u + .body .gmail-fix { display: none !important; }
        
        /* Mobile styles */
        @media only screen and (max-width: 620px) {
            .email-container {
                width: 100% !important;
                max-width: 100% !important;
            }
            .content-padding {
                padding: 24px 16px !important;
            }
            .header-padding {
                padding: 24px 16px !important;
            }
            .card-padding {
                padding: 16px !important;
            }
            .footer-padding {
                padding: 20px 16px !important;
            }
            .mobile-text-sm {
                font-size: 12px !important;
            }
            .mobile-hide {
                display: none !important;
            }
        }
    </style>
</head>
<body class="body" style="margin: 0; padding: 0; width: 100%; background-color: #f5f5f5; -webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%;">
    
    <!-- Preheader (hidden preview text) -->
    <div style="display: none; max-height: 0; overflow: hidden; mso-hide: all;">
        Factura {{ $invoice->number }} - Monto pendiente: ${{ number_format($invoice->balance_due, 2) }} {{ $invoice->currency }}
    </div>
    
    <!-- Main Wrapper -->
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color: #f5f5f5;">
        <tr>
            <td align="center" style="padding: 20px 10px;">
                
                <!-- Email Container -->
                <table role="presentation" class="email-container" width="100%" cellpadding="0" cellspacing="0" border="0" style="max-width: 580px; background-color: #ffffff; border-radius: 8px;">
                    
                    <!-- Header -->
                    <tr>
                        <td class="header-padding" style="background-color: #1f2937; padding: 28px 24px; text-align: center; border-radius: 8px 8px 0 0;">
                            <img src="https://res.cloudinary.com/dspoaxmvn/image/upload/v1768692272/gr_elohvv.png" alt="{{ config('app.name') }}" width="120" style="max-width: 120px; height: auto; display: block; margin: 0 auto 10px auto;">
                            <p style="color: #ffffff; font-size: 16px; font-weight: 600; margin: 0; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;">Recordatorio de Factura</p>
                        </td>
                    </tr>

                    <!-- Content -->
                    <tr>
                        <td class="content-padding" style="padding: 32px 24px; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;">
                            
                            <!-- Greeting -->
                            <p style="font-size: 15px; font-weight: 600; margin: 0 0 12px 0; color: #1f2937;">
                                Hola {{ $contact->name }},
                            </p>
                            
                            <p style="color: #4b5563; margin: 0 0 24px 0; font-size: 14px; line-height: 1.5;">
                                Le recordamos que tiene una factura pendiente de pago:
                            </p>

                            <!-- Invoice Info Card -->
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color: #f9fafb; border: 1px solid #e5e7eb; border-radius: 6px; margin-bottom: 20px;">
                                <tr>
                                    <td class="card-padding" style="padding: 16px;">
                                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                                            <tr>
                                                <td style="padding: 6px 0; font-size: 13px; color: #6b7280; border-bottom: 1px solid #e5e7eb;">Factura</td>
                                                <td style="padding: 6px 0; font-size: 13px; font-weight: 600; color: #1f2937; text-align: right; border-bottom: 1px solid #e5e7eb;">{{ $invoice->number }}</td>
                                            </tr>
                                            <tr>
                                                <td style="padding: 6px 0; font-size: 13px; color: #6b7280; border-bottom: 1px solid #e5e7eb;">Cliente</td>
                                                <td style="padding: 6px 0; font-size: 13px; font-weight: 600; color: #1f2937; text-align: right; border-bottom: 1px solid #e5e7eb;">{{ $company->name }}</td>
                                            </tr>
                                            <tr>
                                                <td style="padding: 6px 0; font-size: 13px; color: #6b7280; border-bottom: 1px solid #e5e7eb;">Vencimiento</td>
                                                <td style="padding: 6px 0; font-size: 13px; font-weight: 600; color: #1f2937; text-align: right; border-bottom: 1px solid #e5e7eb;">{{ \Carbon\Carbon::parse($invoice->due_date)->format('d M Y') }}</td>
                                            </tr>
                                            <tr>
                                                <td style="padding: 6px 0; font-size: 13px; color: #6b7280; border-bottom: 1px solid #e5e7eb;">Estado</td>
                                                <td style="padding: 6px 0; font-size: 13px; text-align: right; border-bottom: 1px solid #e5e7eb;">
                                                    @if($urgencyLevel === 'overdue')
                                                        <span style="display: inline-block; padding: 3px 8px; border-radius: 10px; font-size: 11px; font-weight: 600; background-color: #dc2626; color: #ffffff;">Vencida</span>
                                                    @elseif($urgencyLevel === 'urgent')
                                                        <span style="display: inline-block; padding: 3px 8px; border-radius: 10px; font-size: 11px; font-weight: 600; background-color: #fee2e2; color: #dc2626;">{{ $daysUntilDue }}d</span>
                                                    @elseif($urgencyLevel === 'warning')
                                                        <span style="display: inline-block; padding: 3px 8px; border-radius: 10px; font-size: 11px; font-weight: 600; background-color: #fef3c7; color: #d97706;">{{ $daysUntilDue }}d</span>
                                                    @else
                                                        <span style="display: inline-block; padding: 3px 8px; border-radius: 10px; font-size: 11px; font-weight: 600; background-color: #dbeafe; color: #1d4ed8;">{{ $daysUntilDue }}d</span>
                                                    @endif
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="padding: 10px 0 0 0; font-size: 14px; font-weight: 600; color: #1f2937;">Total</td>
                                                <td style="padding: 10px 0 0 0; font-size: 16px; font-weight: 700; color: #1f2937; text-align: right;">${{ number_format($invoice->balance_due, 2) }}</td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>

                            <!-- Items Section -->
                            @if($items && count($items) > 0)
                            <p style="font-size: 11px; font-weight: 600; color: #9ca3af; text-transform: uppercase; margin: 0 0 8px 0; letter-spacing: 0.5px;">Conceptos</p>
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="border: 1px solid #e5e7eb; border-radius: 6px; margin-bottom: 24px;">
                                @foreach($items as $index => $item)
                                <tr>
                                    <td class="mobile-text-sm" style="padding: 8px 10px; font-size: 12px; color: #374151; {{ $index < count($items) - 1 ? 'border-bottom: 1px solid #e5e7eb;' : '' }}">
                                        {{ Str::limit($item->description, 40) }}
                                    </td>
                                    <td style="padding: 8px 10px; font-size: 12px; font-weight: 600; color: #1f2937; text-align: right; white-space: nowrap; {{ $index < count($items) - 1 ? 'border-bottom: 1px solid #e5e7eb;' : '' }}">
                                        ${{ number_format($item->total, 2) }}
                                    </td>
                                </tr>
                                @endforeach
                            </table>
                            @endif

                            <!-- CTA Button -->
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                                <tr>
                                    <td align="center" style="padding: 8px 0 24px 0;">
                                        <a href="{{ $portalUrl }}" style="display: inline-block; background-color: #1f2937; color: #ffffff; text-decoration: none; padding: 12px 28px; border-radius: 6px; font-weight: 600; font-size: 14px; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;">
                                            Ver Factura
                                        </a>
                                    </td>
                                </tr>
                            </table>

                            <!-- Footer Note -->
                            <p style="color: #9ca3af; font-size: 11px; text-align: center; margin: 0; line-height: 1.5;">
                                Si ya realizó el pago, ignore este mensaje.
                            </p>

                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td class="footer-padding" style="background-color: #f9fafb; padding: 20px 24px; text-align: center; border-top: 1px solid #e5e7eb; border-radius: 0 0 8px 8px;">
                            <p style="color: #9ca3af; font-size: 11px; margin: 0; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;">
                                © {{ date('Y') }} {{ config('app.name') }}
                            </p>
                        </td>
                    </tr>

                </table>
                
            </td>
        </tr>
    </table>

</body>
</html>
