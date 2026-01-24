<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Recordatorio de Pago</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background-color: #ffc107;
            color: #333;
            padding: 20px;
            text-align: center;
            border-radius: 5px 5px 0 0;
        }
        .content {
            background-color: #fff;
            padding: 30px;
            border: 1px solid #ddd;
            border-top: none;
        }
        .info-box {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 5px;
            margin: 20px 0;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            margin: 10px 0;
            padding: 5px 0;
            border-bottom: 1px solid #eee;
        }
        .info-label {
            font-weight: bold;
            color: #666;
        }
        .info-value {
            color: #333;
        }
        .highlight {
            color: #ffc107;
            font-weight: bold;
        }
        .footer {
            background-color: #f8f9fa;
            padding: 20px;
            text-align: center;
            font-size: 12px;
            color: #666;
            border-radius: 0 0 5px 5px;
        }
        .btn {
            display: inline-block;
            padding: 12px 30px;
            background-color: #007bff;
            color: #fff;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Recordatorio de Pago</h1>
    </div>

    <div class="content">
        <p>Estimado/a <strong>{{ $installment->sale->nombre_cliente }} {{ $installment->sale->apellido_cliente }}</strong>,</p>

        <p>Le recordamos que tiene una cuota <span class="highlight">proxima a vencer</span>. A continuacion los detalles:</p>

        <div class="info-box">
            <div class="info-row">
                <span class="info-label">Numero de Cuota:</span>
                <span class="info-value">{{ $installment->numero_cuota }} de {{ $installment->sale->numero_cuotas }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Monto a Pagar:</span>
                <span class="info-value">${{ number_format($installment->monto, 2) }} {{ $installment->sale->moneda }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Fecha de Vencimiento:</span>
                <span class="info-value">{{ $installment->fecha_vencimiento->format('d/m/Y') }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Dias Restantes:</span>
                <span class="info-value">{{ now()->diffInDays($installment->fecha_vencimiento) }} dias</span>
            </div>
        </div>

        <p>Para evitar recargos o inconvenientes, le solicitamos realizar el pago antes de la fecha indicada.</p>

        <h3>Metodos de Pago Disponibles:</h3>
        <ul>
            <li>Transferencia USD</li>
            <li>Transferencia Pesos</li>
            <li>PayPal</li>
            <li>Criptomonedas</li>
            <li>D Local Go</li>
            <li>Whop</li>
        </ul>

        <p>Una vez realizado el pago, por favor envie el comprobante a este correo para registrar su pago.</p>

        <p>Si tiene alguna consulta, no dude en contactarnos.</p>

        <p>Saludos cordiales,<br>
        <strong>{{ config('app.name') }}</strong></p>
    </div>

    <div class="footer">
        <p>Este es un correo automatico de recordatorio.</p>
        <p>&copy; {{ date('Y') }} {{ config('app.name') }}. Todos los derechos reservados.</p>
    </div>
</body>
</html>
