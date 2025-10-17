<?php

require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

$document_templates = [
    // Home
    'Factura instalación gas' => 'Proveedor GasCo, fecha %s',
    'Recibo electricidad hogar' => 'Compañía ElecPower, fecha %s',
    'Póliza seguro hogar' => 'Compañía Seguros del Hogar, emitido el %s',
    'Factura reparación electrodoméstico' => 'Servicio HogarFix, fecha %s',
    'Comprobante de pago internet' => 'Proveedor NetCom, pagado el %s',
    'Recibo agua hogar' => 'Compañía AquaPlus, fecha %s',
    'Contrato servicio limpieza' => 'Empresa Limpio Hogar, firmado el %s',
    'Recibo gas hogar' => 'Compañía GasCo, fecha %s',
    'Factura electricidad comercial' => 'Proveedor Electra, emitida el %s',
    'Factura alquiler vivienda' => 'Arrendador Juan Pérez, fecha %s',

    // Company
    'Factura compra de equipo' => 'Proveedor TechSupplies, emitida el %s',
    'Recibo alquiler oficina' => 'Inmobiliaria Alfa, fecha de pago %s',
    'Contrato de trabajo administrativo' => 'Empresa Soluciones S.A., firmado el %s',
    'Póliza seguro de empresa' => 'Compañía Aseguradora Global, contratada el %s',
    'Comprobante de pago proveedor' => 'Proveedor FastTech, realizado el %s',
    'Declaración de impuestos anual' => 'Empresa XYZ, presentada el %s',
    'Informe financiero empresa' => 'Generado por el Departamento Contable, mes de %s',
    'Acta de constitución empresa' => 'Firmada el %s, registro oficial',
    'Recibo pago empleados' => 'Pagado el %s a todos los empleados',
    'Factura proveedor servicios IT' => 'Proveedor SoftWareTech, emitida el %s',
    'Contrato de arrendamiento oficina' => 'Firmado el %s con Inmobiliaria Vega',
    'Informe de auditoría financiera' => 'Realizado por Auditores Corp, fecha %s',
    'Recibo anual de servicios empresariales' => 'Emitido el %s por TechSolution S.A.',
    'Acta de reunión empresarial' => 'Celebrada el %s con la junta directiva',
    'Certificación fiscal de empresa' => 'Emitida por la Agencia Tributaria, %s',
    'Evaluación de desempeño empleados' => 'Realizada el %s, por recursos humanos',
    'Comprobante de pago impuesto sobre la renta' => 'Pago realizado el %s, empresa ABC S.A.',
    'Informe anual de ventas' => 'Generado por el departamento comercial, fin de año %s'
];

for ($i = 0; $i < 250; $i++) {
    $document_id = \HomeDocs\Utils::uuidv4();
    $title = array_rand($document_templates);
    echo sprintf(
        'INSERT INTO DOCUMENT (id, title, description") VALUES("%s", "%s", "%s")',
        $document_id,
        $title,
        sprintf(
            $document_templates[$title],
            date(
                'd-m-Y',
                mt_rand(
                    strtotime('01-01-2000'),
                    strtotime('31-12-2023')
                )
            )
        )
    ) . PHP_EOL;
}
