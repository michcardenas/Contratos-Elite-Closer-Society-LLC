<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\ContractTemplate;

class ContractTemplatesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $dynamicFields = json_encode(['dia', 'mes', 'anio', 'nombre', 'dni', 'forma_de_pago', 'imagen_firma', 'aclaracion']);

        // Template 1: 2 Cuotas
        ContractTemplate::create([
            'name' => '2 Cuotas',
            'html_content' => $this->get2CuotasTemplate(),
            'dynamic_fields' => $dynamicFields,
        ]);

        // Template 2: 3 Cuotas
        ContractTemplate::create([
            'name' => '3 Cuotas',
            'html_content' => $this->get3CuotasTemplate(),
            'dynamic_fields' => $dynamicFields,
        ]);

        // Template 3: Beca
        ContractTemplate::create([
            'name' => 'Beca',
            'html_content' => $this->getBecaTemplate(),
            'dynamic_fields' => $dynamicFields,
        ]);

        // Template 4: Low Ticket
        ContractTemplate::create([
            'name' => 'Low Ticket',
            'html_content' => $this->getLowTicketTemplate(),
            'dynamic_fields' => $dynamicFields,
        ]);

        // Template 5: Full Pago
        ContractTemplate::create([
            'name' => 'Full Pago',
            'html_content' => $this->getFullPagoTemplate(),
            'dynamic_fields' => $dynamicFields,
        ]);
    }

    private function get2CuotasTemplate()
    {
        return '<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Acuerdo de Formación Personalizada - 2 Cuotas</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.4; margin: 30px; font-size: 11pt; }
        h1 { text-align: center; font-size: 14pt; margin-bottom: 20px; }
        h2 { font-size: 12pt; margin-top: 15px; }
        p { text-align: justify; margin-bottom: 10px; }
    </style>
</head>
<body>
    <h1>Acuerdo de Formación Personalizada</h1>

    <p><strong>PARTES:</strong></p>
    <p>El presente Acuerdo de Formación (en adelante: "Acuerdo") se celebra entre Elite Closer Society LLC, una sociedad constituida y existente bajo las leyes Argentinas (en lo sucesivo: "Coach") y <strong>{nombre}</strong>, DNI <strong>{dni}</strong> (en lo sucesivo: "Aprendiz"). Ambos acuerdan que los siguientes términos y condiciones se aplicarán a los servicios prestados en virtud del presente Contrato.</p>

    <h2>TÉRMINOS Y CONDICIONES Artículo 1. Definiciones</h2>
    <p><strong>1.1</strong> "Servicio de formación" o "Formación" o "Coaching" se refiere al servicio de la empresa de "Closer de ventas/Cerrador de ventas" que proporciona el uso y acceso al material de formación en línea, que se pone a disposición del Aprendiz en base a un plazo de uso.</p>

    <p><strong>1.2</strong> "Material de formación" es el material de formación en línea, que se utiliza para formar al Aprendiz. Debido a su naturaleza inmediatamente consumible y transferible, no se concederán reembolsos una vez que se haya concedido el acceso al material de formación en línea. El precio del material de formación en línea se indica en el artículo 4.1.</p>

    <h2>Artículo 2. Servicio de formación</h2>
    <p><strong>2.1</strong> Por la presente, Elite Closer Society LLC concede al Aprendiz, durante el Periodo de Vigencia, una licencia no exclusiva, intransferible (a menos que se acuerde lo contrario por escrito) que le otorga el derecho a acceder y utilizar el material del curso en línea, sujeto a los términos de este Acuerdo y únicamente para fines internos dentro de la propia empresa del Aprendiz. Durante la vigencia, El Aprendiz también podrá hacer uso del Servicio de Coaching.</p>

    <p><strong>2.2</strong> El plazo de acceso al contenido del curso, a los materiales de formación y a las sesiones de preguntas y respuestas en directo será de <strong>180 días</strong>.</p>

    <h2>Artículo 3. La Certificación</h2>
    <p><strong>3.1</strong> La obtención de la Certificación y el convertirse en un Closer de ventas certificado depende totalmente del dominio y finalización del material del curso. Para obtener la Certificación el Aprendiz deberá aprobar las <strong>cuatro instancias</strong> de evaluación, con cinco intentos. Si el Aprendiz desaprueba no obtendrá la Certificación.</p>

    <h2>Artículo 4. Garantía</h2>
    <p><strong>4.1</strong> A través de la formación personalizada se garantiza que el alumno obtenga, dentro de los primeros seis meses, una ganancia que tiene como base un importe de <strong>$2.000 (USD)</strong>. La garantía solo tiene validez si el alumno se logra certificar en un lapso menor a 121 días, una vez realizado el primer pago del programa.</p>

    <h2>Artículo 5. Honorarios y gastos. Reembolso</h2>
    <p><strong>5.1</strong> La tarifa total es <strong>$2.000 (USD)</strong>. El método de pago se describirá en el artículo 6.1.</p>

    <h2>Artículo 6. Forma de pago</h2>
    <p><strong>6.1</strong> La tasa se abonará según indica el ANEXO 1 del contrato, mediante pago en efectivo, criptomonedas, débito, crédito, Paypal, transferencia en pesos o en dólares.</p>

    <p style="margin-top: 30px;">FIRMA: _____________________</p>
    <p>ACLARACIÓN: _____________________</p>
    <p>DNI: _____________________</p>

    <div style="page-break-before: always;"></div>

    <h1>ANEXO 1. Forma de pago</h1>

    <p>Elite Closer Society LLC, sociedad constituida y existente bajo las leyes Argentinas (en lo sucesivo: "Coach") y <strong>{nombre}</strong>, DNI <strong>{dni}</strong> (en lo sucesivo: "Aprendiz") acuerdan lo siguiente:</p>

    <p>Las partes declaran que han suscripto con anterioridad el "Acuerdo de Formación Personalizada" que se encuentra plenamente vigente.</p>

    <p>El siguiente anexo detalla la forma de pago contemplada en el Artículo 6.1 del contrato.</p>

    <p>{forma_de_pago}</p>

    <p style="margin-top: 30px;">FIRMA: _____________________</p>
    <p>ACLARACIÓN: _____________________</p>
    <p>DNI: _____________________</p>
</body>
</html>';
    }

    private function get3CuotasTemplate()
    {
        return '<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Acuerdo de Formación Personalizada - 3 Cuotas</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.4; margin: 30px; font-size: 11pt; }
        h1 { text-align: center; font-size: 14pt; margin-bottom: 20px; }
        h2 { font-size: 12pt; margin-top: 15px; }
        p { text-align: justify; margin-bottom: 10px; }
    </style>
</head>
<body>
    <h1>Acuerdo de Formación Personalizada</h1>

    <p><strong>PARTES:</strong></p>
    <p>El presente Acuerdo de Formación (en adelante: "Acuerdo") se celebra entre Elite Closer Society LLC, una sociedad constituida y existente bajo las leyes Argentinas (en lo sucesivo: "Coach") y <strong>{nombre}</strong>, DNI <strong>{dni}</strong> (en lo sucesivo: "Aprendiz"). Ambos acuerdan que los siguientes términos y condiciones se aplicarán a los servicios prestados en virtud del presente Contrato.</p>

    <h2>TÉRMINOS Y CONDICIONES Artículo 1. Definiciones</h2>
    <p><strong>1.1</strong> "Servicio de formación" o "Formación" o "Coaching" se refiere al servicio de la empresa de "Closer de ventas/Cerrador de ventas" que proporciona el uso y acceso al material de formación en línea, que se pone a disposición del Aprendiz en base a un plazo de uso.</p>

    <p><strong>1.2</strong> "Material de formación" es el material de formación en línea, que se utiliza para formar al Aprendiz. Debido a su naturaleza inmediatamente consumible y transferible, no se concederán reembolsos una vez que se haya concedido el acceso al material de formación en línea. El precio del material de formación en línea se indica en el artículo 4.1.</p>

    <h2>Artículo 2. Servicio de formación</h2>
    <p><strong>2.1</strong> Por la presente, Elite Closer Society LLC concede al Aprendiz, durante el Periodo de Vigencia, una licencia no exclusiva, intransferible (a menos que se acuerde lo contrario por escrito) que le otorga el derecho a acceder y utilizar el material del curso en línea, sujeto a los términos de este Acuerdo y únicamente para fines internos dentro de la propia empresa del Aprendiz. Durante la vigencia, El Aprendiz también podrá hacer uso del Servicio de Coaching.</p>

    <p><strong>2.2</strong> El plazo de acceso al contenido del curso, a los materiales de formación y a las sesiones de preguntas y respuestas en directo será de <strong>180 días</strong>.</p>

    <h2>Artículo 3. La Certificación</h2>
    <p><strong>3.1</strong> La obtención de la Certificación y el convertirse en un Closer de ventas certificado depende totalmente del dominio y finalización del material del curso. Para obtener la Certificación el Aprendiz deberá aprobar las <strong>cuatro instancias</strong> de evaluación, con cinco intentos. Si el Aprendiz desaprueba no obtendrá la Certificación.</p>

    <h2>Artículo 4. Garantía</h2>
    <p><strong>4.1</strong> A través de la formación personalizada se garantiza que el alumno obtenga, dentro de los primeros seis meses, una ganancia que tiene como base un importe de <strong>$2.000 (USD)</strong>. La garantía solo tiene validez si el alumno se logra certificar en un lapso menor a 121 días, una vez realizado el primer pago del programa.</p>

    <h2>Artículo 5. Honorarios y gastos. Reembolso</h2>
    <p><strong>5.1</strong> La tarifa total es <strong>$2.000 (USD)</strong>. El método de pago se describirá en el artículo 6.1.</p>

    <h2>Artículo 6. Forma de pago</h2>
    <p><strong>6.1</strong> La tasa se abonará según indica el ANEXO 1 del contrato, mediante pago en efectivo, criptomonedas, débito, crédito, Paypal, transferencia en pesos o en dólares.</p>

    <p style="margin-top: 30px;">FIRMA: _____________________</p>
    <p>ACLARACIÓN: _____________________</p>
    <p>DNI: _____________________</p>

    <div style="page-break-before: always;"></div>

    <h1>ANEXO 1. Forma de pago</h1>

    <p>Elite Closer Society LLC, sociedad constituida y existente bajo las leyes Argentinas (en lo sucesivo: "Coach") y <strong>{nombre}</strong>, DNI <strong>{dni}</strong> (en lo sucesivo: "Aprendiz") acuerdan lo siguiente:</p>

    <p>Las partes declaran que han suscripto con anterioridad el "Acuerdo de Formación Personalizada" que se encuentra plenamente vigente.</p>

    <p>El siguiente anexo detalla la forma de pago contemplada en el Artículo 6.1 del contrato.</p>

    <p>{forma_de_pago}</p>

    <p style="margin-top: 30px;">FIRMA: _____________________</p>
    <p>ACLARACIÓN: _____________________</p>
    <p>DNI: _____________________</p>
</body>
</html>';
    }

    private function getBecaTemplate()
    {
        return '<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Acuerdo de Formación Personalizada - Beca</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.4; margin: 30px; font-size: 11pt; }
        h1 { text-align: center; font-size: 14pt; margin-bottom: 20px; }
        h2 { font-size: 12pt; margin-top: 15px; }
        p { text-align: justify; margin-bottom: 10px; }
    </style>
</head>
<body>
    <h1>Acuerdo de Formación Personalizada</h1>

    <p><strong>PARTES:</strong></p>
    <p>El presente Acuerdo de Formación (en adelante: "Acuerdo") se celebra entre Elite Closer Society LLC, una sociedad constituida y existente bajo las leyes Argentinas (en lo sucesivo: "Coach") y <strong>{nombre}</strong>, DNI <strong>{dni}</strong> (en lo sucesivo: "Aprendiz"). Ambos acuerdan que los siguientes términos y condiciones se aplicarán a los servicios prestados en virtud del presente Contrato.</p>

    <h2>TÉRMINOS Y CONDICIONES Artículo 1. Definiciones</h2>
    <p><strong>1.1</strong> "Servicio de formación" o "Formación" o "Coaching" se refiere al servicio de la empresa de "Closer de ventas/Cerrador de ventas" que proporciona el uso y acceso al material de formación en línea, que se pone a disposición del Aprendiz en base a un plazo de uso.</p>

    <p><strong>1.2</strong> "Material de formación" es el material de formación en línea, que se utiliza para formar al Aprendiz. Debido a su naturaleza inmediatamente consumible y transferible, no se concederán reembolsos una vez que se haya concedido el acceso al material de formación en línea.</p>

    <h2>Artículo 2. Servicio de formación</h2>
    <p><strong>2.1</strong> Por la presente, Elite Closer Society LLC concede al Aprendiz, durante el Periodo de Vigencia, una licencia no exclusiva, intransferible (a menos que se acuerde lo contrario por escrito) que le otorga el derecho a acceder y utilizar el material del curso en línea, sujeto a los términos de este Acuerdo y únicamente para fines internos dentro de la propia empresa del Aprendiz. Durante la vigencia, El Aprendiz también podrá hacer uso del Servicio de Coaching.</p>

    <p><strong>2.2</strong> El plazo de acceso al contenido del curso, a los materiales de formación y a las sesiones de preguntas y respuestas en directo será de <strong>180 días</strong>.</p>

    <h2>Artículo 3. La Certificación</h2>
    <p><strong>3.1</strong> La obtención de la Certificación y el convertirse en un Closer de ventas certificado depende totalmente del dominio y finalización del material del curso. Para obtener la Certificación el Aprendiz deberá aprobar las <strong>cuatro instancias</strong> de evaluación, con cinco intentos. Si el Aprendiz desaprueba no obtendrá la Certificación.</p>

    <h2>Artículo 4. Honorarios y gastos. Reembolso</h2>
    <p><strong>4.1</strong> La tarifa total es <strong>$2.000 (USD)</strong>. El método de pago se describirá en el artículo 5.1.</p>

    <h2>Artículo 5. Forma de pago</h2>
    <p><strong>5.1</strong> La tasa se abonará según indica el ANEXO 1 del contrato, mediante pago en efectivo, criptomonedas, débito, crédito, Paypal, transferencia en pesos o en dólares.</p>

    <p style="margin-top: 30px;">FIRMA: _____________________</p>
    <p>ACLARACIÓN: _____________________</p>
    <p>DNI: _____________________</p>

    <div style="page-break-before: always;"></div>

    <h1>ANEXO 1. Forma de pago</h1>

    <p>Elite Closer Society LLC, sociedad constituida y existente bajo las leyes Argentinas (en lo sucesivo: "Coach") y <strong>{nombre}</strong>, DNI <strong>{dni}</strong> (en lo sucesivo: "Aprendiz") acuerdan lo siguiente:</p>

    <p>Las partes declaran que han suscripto con anterioridad el "Acuerdo de Formación Personalizada" que se encuentra plenamente vigente.</p>

    <p>El siguiente anexo detalla la forma de pago contemplada en el Artículo 5.1 del contrato.</p>

    <p>El pago de la formación se realizará a través de un Sistema de Becas de la siguiente manera:</p>

    <p>{forma_de_pago}</p>

    <p style="margin-top: 30px;">FIRMA: _____________________</p>
    <p>ACLARACIÓN: _____________________</p>
    <p>DNI: _____________________</p>
</body>
</html>';
    }

    private function getLowTicketTemplate()
    {
        return '<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Acuerdo de Formación Personalizada - Low Ticket</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.4; margin: 30px; font-size: 11pt; }
        h1 { text-align: center; font-size: 14pt; margin-bottom: 20px; }
        h2 { font-size: 12pt; margin-top: 15px; }
        p { text-align: justify; margin-bottom: 10px; }
    </style>
</head>
<body>
    <h1>Acuerdo de Formación Personalizada</h1>

    <p><strong>PARTES:</strong></p>
    <p>El presente Acuerdo de Formación (en adelante: "Acuerdo") se celebra entre Elite Closer Society LLC, una sociedad constituida y existente bajo las leyes Argentinas (en lo sucesivo: "Coach") y <strong>{nombre}</strong>, DNI <strong>{dni}</strong> (en lo sucesivo: "Aprendiz"). Ambos acuerdan que los siguientes términos y condiciones se aplicarán a los servicios prestados en virtud del presente Contrato.</p>

    <h2>TÉRMINOS Y CONDICIONES Artículo 1. Definiciones</h2>
    <p><strong>1.1</strong> "Servicio de formación" o "Formación" o "Coaching" se refiere al servicio de la empresa de "Closer de ventas/Cerrador de ventas" que proporciona el uso y acceso al material de formación en línea, que se pone a disposición del Aprendiz en base a un plazo de uso.</p>

    <p><strong>1.2</strong> "Material de formación" es el material de formación en línea, que se utiliza para formar al Aprendiz. Debido a su naturaleza inmediatamente consumible y transferible, no se concederán reembolsos una vez que se haya concedido el acceso al material de formación en línea.</p>

    <h2>Artículo 2. Servicio de formación</h2>
    <p><strong>2.1</strong> Por la presente, Elite Closer Society LLC concede al Aprendiz, durante el Periodo de Vigencia, una licencia no exclusiva, intransferible (a menos que se acuerde lo contrario por escrito) que le otorga el derecho a acceder y utilizar el material del curso en línea, sujeto a los términos de este Acuerdo y únicamente para fines internos dentro de la propia empresa del Aprendiz. Durante la vigencia, El Aprendiz también podrá hacer uso del Servicio de Coaching.</p>

    <p><strong>2.2</strong> El plazo de acceso al contenido del curso, a los materiales de formación y a las sesiones de preguntas y respuestas en directo será de <strong>180 días</strong>.</p>

    <h2>Artículo 3. Honorarios y gastos. Reembolso</h2>
    <p><strong>3.1</strong> La tarifa total es <strong>$500 (USD)</strong>. El método de pago se describirá en el artículo 4.1.</p>

    <h2>Artículo 4. Forma de pago</h2>
    <p><strong>4.1</strong> La tasa se abonará según indica el ANEXO 1 del contrato, mediante pago en efectivo, criptomonedas, débito, crédito, Paypal, transferencia en pesos o en dólares.</p>

    <p style="margin-top: 30px;">FIRMA: _____________________</p>
    <p>ACLARACIÓN: _____________________</p>
    <p>DNI: _____________________</p>

    <div style="page-break-before: always;"></div>

    <h1>ANEXO 1. Forma de pago</h1>

    <p>Elite Closer Society LLC, sociedad constituida y existente bajo las leyes Argentinas (en lo sucesivo: "Coach") y <strong>{nombre}</strong>, DNI <strong>{dni}</strong> (en lo sucesivo: "Aprendiz") acuerdan lo siguiente:</p>

    <p>Las partes declaran que han suscripto con anterioridad el "Acuerdo de Formación Personalizada" que se encuentra plenamente vigente.</p>

    <p>El siguiente anexo detalla la forma de pago contemplada en el Artículo 4.1 del contrato.</p>

    <p>Las partes acuerdan que el pago de la formación, cuyo monto total asciende a <strong>$500 (USD)</strong>, se realizará de la siguiente manera:</p>

    <p>{forma_de_pago}</p>

    <p style="margin-top: 30px;">FIRMA: _____________________</p>
    <p>ACLARACIÓN: _____________________</p>
    <p>DNI: _____________________</p>
</body>
</html>';
    }

    private function getFullPagoTemplate()
    {
        return '<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Acuerdo de Formación Personalizada - Full Pago</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.4; margin: 30px; font-size: 11pt; }
        h1 { text-align: center; font-size: 14pt; margin-bottom: 20px; }
        h2 { font-size: 12pt; margin-top: 15px; }
        p { text-align: justify; margin-bottom: 10px; }
    </style>
</head>
<body>
    <h1>Acuerdo de Formación Personalizada</h1>

    <p><strong>PARTES:</strong></p>
    <p>El presente Acuerdo de Formación (en adelante: "Acuerdo") se celebra entre Elite Closer Society LLC, una sociedad constituida y existente bajo las leyes Argentinas (en lo sucesivo: "Coach") y <strong>{nombre}</strong>, DNI <strong>{dni}</strong> (en lo sucesivo: "Aprendiz"). Ambos acuerdan que los siguientes términos y condiciones se aplicarán a los servicios prestados en virtud del presente Contrato.</p>

    <h2>TÉRMINOS Y CONDICIONES Artículo 1. Definiciones</h2>
    <p><strong>1.1</strong> "Servicio de formación" o "Formación" o "Coaching" se refiere al servicio de la empresa de "Closer de ventas/Cerrador de ventas" que proporciona el uso y acceso al material de formación en línea, que se pone a disposición del Aprendiz en base a un plazo de uso.</p>

    <p><strong>1.2</strong> "Material de formación" es el material de formación en línea, que se utiliza para formar al Aprendiz. Debido a su naturaleza inmediatamente consumible y transferible, no se concederán reembolsos una vez que se haya concedido el acceso al material de formación en línea. El precio del material de formación en línea se indica en el artículo 4.1.</p>

    <h2>Artículo 2. Servicio de formación</h2>
    <p><strong>2.1</strong> Por la presente, Elite Closer Society LLC concede al Aprendiz, durante el Periodo de Vigencia, una licencia no exclusiva, intransferible (a menos que se acuerde lo contrario por escrito) que le otorga el derecho a acceder y utilizar el material del curso en línea, sujeto a los términos de este Acuerdo y únicamente para fines internos dentro de la propia empresa del Aprendiz. Durante la vigencia, El Aprendiz también podrá hacer uso del Servicio de Coaching.</p>

    <p><strong>2.2</strong> El plazo de acceso al contenido del curso, a los materiales de formación y a las sesiones de preguntas y respuestas en directo será de <strong>180 días</strong>.</p>

    <h2>Artículo 3. La Certificación</h2>
    <p><strong>3.1</strong> La obtención de la Certificación y el convertirse en un Closer de ventas certificado depende totalmente del dominio y finalización del material del curso. Para obtener la Certificación el Aprendiz deberá aprobar las <strong>cuatro instancias</strong> de evaluación, con cinco intentos. Si el Aprendiz desaprueba no obtendrá la Certificación.</p>

    <h2>Artículo 4. Garantía</h2>
    <p><strong>4.1</strong> A través de la formación personalizada se garantiza que el alumno obtenga, dentro de los primeros seis meses, una ganancia que tiene como base un importe de <strong>$2.000 (USD)</strong>. La garantía solo tiene validez si el alumno se logra certificar en un lapso menor a 121 días, una vez realizado el primer pago del programa.</p>

    <h2>Artículo 5. Honorarios y gastos. Reembolso</h2>
    <p><strong>5.1</strong> La tarifa total es <strong>$2.000 (USD)</strong>. El método de pago se describirá en el artículo 6.1.</p>

    <h2>Artículo 6. Forma de pago</h2>
    <p><strong>6.1</strong> La tasa se abonará según indica el ANEXO 1 del contrato, mediante pago en efectivo, criptomonedas, débito, crédito, Paypal, transferencia en pesos o en dólares.</p>

    <p style="margin-top: 30px;">FIRMA: _____________________</p>
    <p>ACLARACIÓN: _____________________</p>
    <p>DNI: _____________________</p>

    <div style="page-break-before: always;"></div>

    <h1>ANEXO 1. Forma de pago</h1>

    <p>Elite Closer Society LLC, sociedad constituida y existente bajo las leyes Argentinas (en lo sucesivo: "Coach") y <strong>{nombre}</strong>, DNI <strong>{dni}</strong> (en lo sucesivo: "Aprendiz") acuerdan lo siguiente:</p>

    <p>Las partes declaran que han suscripto con anterioridad el "Acuerdo de Formación Personalizada" que se encuentra plenamente vigente.</p>

    <p>El siguiente anexo detalla la forma de pago contemplada en el Artículo 6.1 del contrato.</p>

    <p>Las partes acuerdan que el pago de la formación, cuyo monto total asciende a <strong>$2.000 (USD)</strong>, se realizará de la siguiente manera:</p>

    <p>{forma_de_pago}</p>

    <p style="margin-top: 30px;">FIRMA: _____________________</p>
    <p>ACLARACIÓN: _____________________</p>
    <p>DNI: _____________________</p>
</body>
</html>';
    }
}
