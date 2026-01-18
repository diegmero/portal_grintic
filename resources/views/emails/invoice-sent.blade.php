@extends('emails.layouts.base')

@section('preheader')
Nueva factura {{ $invoice->number }} por ${{ number_format($invoice->total, 2) }} {{ $invoice->currency }}
@endsection

@section('content')
    {{-- Greeting --}}
    <p style="font-size: 15px; font-weight: 600; margin: 0 0 12px 0; color: #1f2937;">
        Hola {{ $contact->name }},
    </p>
    
    <p style="color: #4b5563; margin: 0 0 24px 0; font-size: 14px; line-height: 1.5;">
        Le informamos que se ha generado una nueva factura a su nombre. A continuación encontrará los detalles:
    </p>

    {{-- Invoice Info Card --}}
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color: #f9fafb; border: 1px solid #e5e7eb; border-radius: 6px; margin-bottom: 20px;">
        <tr>
            <td style="padding: 16px;">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                    <tr>
                        <td style="padding: 6px 0; font-size: 13px; color: #6b7280; border-bottom: 1px solid #e5e7eb;">Factura</td>
                        <td style="padding: 6px 0; font-size: 13px; font-weight: 600; color: #1f2937; text-align: right; border-bottom: 1px solid #e5e7eb;">{{ $invoice->number }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 6px 0; font-size: 13px; color: #6b7280; border-bottom: 1px solid #e5e7eb;">Fecha de Emisión</td>
                        <td style="padding: 6px 0; font-size: 13px; font-weight: 600; color: #1f2937; text-align: right; border-bottom: 1px solid #e5e7eb;">{{ \Carbon\Carbon::parse($invoice->date)->format('d M Y') }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 6px 0; font-size: 13px; color: #6b7280; border-bottom: 1px solid #e5e7eb;">Vencimiento</td>
                        <td style="padding: 6px 0; font-size: 13px; font-weight: 600; color: #1f2937; text-align: right; border-bottom: 1px solid #e5e7eb;">{{ \Carbon\Carbon::parse($invoice->due_date)->format('d M Y') }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 10px 0 0 0; font-size: 14px; font-weight: 600; color: #1f2937;">Total a Pagar</td>
                        <td style="padding: 10px 0 0 0; font-size: 18px; font-weight: 700; color: #1f2937; text-align: right;">${{ number_format($invoice->total, 2) }} {{ $invoice->currency }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    {{-- Items Section --}}
    @if($items && count($items) > 0)
    <p style="font-size: 11px; font-weight: 600; color: #9ca3af; text-transform: uppercase; margin: 0 0 8px 0; letter-spacing: 0.5px;">Conceptos</p>
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="border: 1px solid #e5e7eb; border-radius: 6px; margin-bottom: 24px;">
        @foreach($items as $index => $item)
        <tr>
            <td style="padding: 8px 10px; font-size: 12px; color: #374151; {{ $index < count($items) - 1 ? 'border-bottom: 1px solid #e5e7eb;' : '' }}">
                {{ Str::limit($item->description, 50) }}
            </td>
            <td style="padding: 8px 10px; font-size: 12px; font-weight: 600; color: #1f2937; text-align: right; white-space: nowrap; {{ $index < count($items) - 1 ? 'border-bottom: 1px solid #e5e7eb;' : '' }}">
                ${{ number_format($item->total, 2) }}
            </td>
        </tr>
        @endforeach
    </table>
    @endif

    {{-- CTA Button --}}
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
        <tr>
            <td align="center" style="padding: 8px 0 24px 0;">
                <a href="{{ $portalUrl }}" style="display: inline-block; background-color: #1f2937; color: #ffffff; text-decoration: none; padding: 12px 28px; border-radius: 6px; font-weight: 600; font-size: 14px; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;">
                    Ver Factura en el Portal
                </a>
            </td>
        </tr>
    </table>

    {{-- Footer Note --}}
    <p style="color: #9ca3af; font-size: 12px; text-align: center; margin: 0; line-height: 1.5;">
        Si tiene alguna pregunta sobre esta factura, no dude en contactarnos.
    </p>
@endsection
