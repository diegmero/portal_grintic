{{--
    Base Email Layout - Compatible with all email clients
    Usage: @extends('emails.layouts.base')
    Sections: @section('content'), @section('preheader')
--}}
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="x-apple-disable-message-reformatting">
    <meta name="format-detection" content="telephone=no, address=no, email=no, date=no">
    <title>{{ $subject ?? config('app.name') }}</title>
    <!--[if mso]>
    <style type="text/css">
        body, table, td {font-family: Arial, Helvetica, sans-serif !important;}
    </style>
    <![endif]-->
    <style>
        /* Gmail fix */
        u + .body .gmail-fix { display: none !important; }
        
        /* Mobile responsive */
        @media only screen and (max-width: 620px) {
            .email-container { width: 100% !important; max-width: 100% !important; }
            .content-padding { padding: 24px 16px !important; }
            .header-padding { padding: 24px 16px !important; }
            .footer-padding { padding: 20px 16px !important; }
        }
    </style>
</head>
<body class="body" style="margin: 0; padding: 0; width: 100%; background-color: #f5f5f5; -webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%;">
    
    {{-- Preheader (preview text in inbox) --}}
    <div style="display: none; max-height: 0; overflow: hidden; mso-hide: all;">
        @yield('preheader', '')
    </div>
    
    {{-- Main Wrapper --}}
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color: #f5f5f5;">
        <tr>
            <td align="center" style="padding: 20px 10px;">
                
                {{-- Email Container --}}
                <table role="presentation" class="email-container" width="100%" cellpadding="0" cellspacing="0" border="0" style="max-width: 580px; background-color: #ffffff; border-radius: 8px;">
                    
                    {{-- Header --}}
                    <tr>
                        <td class="header-padding" style="background-color: #1f2937; padding: 28px 24px; text-align: center; border-radius: 8px 8px 0 0;">
                            <img src="https://res.cloudinary.com/dspoaxmvn/image/upload/v1768692272/gr_elohvv.png" alt="{{ config('app.name') }}" width="120" style="max-width: 120px; height: auto; display: block; margin: 0 auto;">
                        </td>
                    </tr>

                    {{-- Content --}}
                    <tr>
                        <td class="content-padding" style="padding: 32px 24px; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;">
                            @yield('content')
                        </td>
                    </tr>

                    {{-- Footer --}}
                    <tr>
                        <td class="footer-padding" style="background-color: #f9fafb; padding: 20px 24px; text-align: center; border-top: 1px solid #e5e7eb; border-radius: 0 0 8px 8px;">
                            <p style="color: #9ca3af; font-size: 11px; margin: 0; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;">
                                © {{ date('Y') }} {{ config('app.name') }} · Todos los derechos reservados
                            </p>
                        </td>
                    </tr>

                </table>
                
            </td>
        </tr>
    </table>

</body>
</html>
