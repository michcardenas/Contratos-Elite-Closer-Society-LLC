<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Cuota Vencida</title>
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
            background-color: #dc3545;
            color: #fff;
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
            background-color: #f8d7da;
            padding: 20px;
            border-radius: 5px;
            margin: 20px 0;
            border: 1px solid #f5c6cb;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            margin: 10px 0;
            padding: 5px 0;
            border-bottom: 1px solid #f5c6cb;
        }
        .info-label {
            font-weight: bold;
            color: #721c24;
        }
        .info-value {
            color: #721c24;
        }
        .highlight {
            color: #dc3545;
            font-weight: bold;
        }
        .urgent {
            background-color: #dc3545;
            color: #fff;
            padding: 15px;
            border-radius: 5px;
            text-align: center;
            margin: 20px 0;
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
            background-color: #dc3545;
            color: #fff;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Cuota Vencida</h1>
    </div>

    <div class="content">
        <p>Estimado/a <strong>{{ $installment->sale->nombre_cliente }} {{ $installment->sale->apellido_cliente }}</strong>,</p>

        <div class="urgent">
            <strong>ATENCION: Su cuota ha vencido y requiere accion inmediata.</strong>
        </div>

        <p>Le informamos que la siguiente cuota se encuentra <span class="highlight">VENCIDA</span>:</p>

        <div class="info-box">
            <div class="info-row">
                <span class="info-label">Numero de Cuota:</span>
                <span class="info-value">{{ $installment->numero_cuota }} de {{ $installment->sale->numero_cuotas }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Monto Adeudado:</span>
                <span class="info-value">${{ number_format($installment->getMontoPendiente(), 2) }} {{ $installment->sale->moneda }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Fecha de Vencimiento:</span>
                <span class="info-value">{{ $installment->fecha_vencimiento->format('d/m/Y') }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Dias de Atraso:</span>
                <span class="info-value">{{ $installment->getDaysOverdue() }} dias</span>
            </div>
        </div>

        <p>Es importante que regularice esta situacion a la brevedad para evitar:</p>
        <ul>
            <li>Interrupcion de servicios</li>
            <li>Posibles recargos por mora</li>
            <li>Afectacion a su historial</li>
        </ul>

        <h3>Como Realizar el Pago:</h3>
        <p>Puede realizar el pago mediante cualquiera de los siguientes metodos:</p>
        <ul>
            <li>Transferencia USD</li>
            <li>Transferencia Pesos</li>
            <li>PayPal</li>
            <li>Criptomonedas</li>
            <li>D Local Go</li>
            <li>Whop</li>
        </ul>

        <p>Una vez realizado el pago, por favor envie el comprobante a este correo para registrar su pago inmediatamente.</p>

        <p>Si esta experimentando dificultades para realizar el pago, por favor contactenos para encontrar una solucion juntos.</p>

        <p>Quedamos atentos a su respuesta.</p>

        <p>Saludos cordiales,<br>
        <strong>{{ config('app.name') }}</strong></p>
    </div>

    <div class="footer">
        <p>Este es un correo automatico de notificacion de cuota vencida.</p>
        <p>&copy; {{ date('Y') }} {{ config('app.name') }}. Todos los derechos reservados.</p>
    </div>
</body>
</html>
