<?php

namespace HomeDocs;

class Mock
{
    public const int DEFAULT_MAX_DOCUMENT_UPDATES = 3;
    public const int DEFAULT_MAX_ATTACHMENTS = 4;
    public const int DEFAULT_MAX_NOTES = 2;

    private ?string $userId = null;
    private int $maxDocumentUpdatesCount = 0;
    private int $maxDocumentAttachmentsCount = 0;
    private int $maxDocumentNotes = 0;

    private array $documentTemplates = [
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

    public function __construct(string $userId, int $maxDocumentUpdatesCount = self::DEFAULT_MAX_DOCUMENT_UPDATES, int $maxDocumentAttachmentsCount = self::DEFAULT_MAX_ATTACHMENTS, int $maxDocumentNotes = self::DEFAULT_MAX_NOTES)
    {
        $this->userId = $userId;
        $this->maxDocumentUpdatesCount = $maxDocumentUpdatesCount;
        $this->maxDocumentAttachmentsCount = $maxDocumentAttachmentsCount;
        $this->maxDocumentNotes = $maxDocumentNotes;
    }

    private function extractKeywords(string $text)
    {
        $text = mb_strtolower($text);
        $text = preg_replace('/[^a-záéíóúö0-9\s]/', '', $text);
        $words = explode(' ', $text);
        $stopWords = ['de', 'el', 'la', 'los', 'las', 'en', 'con', 'por', 'a', 'para'];
        $keywords = array_diff($words, $stopWords);
        return $keywords;
    }

    private function generateTagsFromDocument(string $title, string $description)
    {
        $tags = [];

        $titleKeywords = $this->extractKeywords($title);
        $descriptionKeywords = $this->extractKeywords($description);

        $keywords = array_merge($titleKeywords, $descriptionKeywords);

        $tags[] = mb_strpos($title, 'Factura') !== false ? 'factura' : '';
        $tags[] = mb_strpos($title, 'Recibo') !== false ? 'recibo' : '';
        $tags[] = mb_strpos($title, 'Póliza') !== false ? 'seguro' : '';
        $tags[] = mb_strpos($title, 'Contrato') !== false ? 'contrato' : '';
        $tags[] = mb_strpos($title, 'Informe') !== false ? 'informe' : '';

        $tags = array_merge($tags, $keywords);

        $tags = array_filter($tags);
        $tags = array_unique($tags);

        return $tags;
    }

    private function generateNoteBody(string $title)
    {
        $notes = [
            'Factura' => 'Verificación del pago de la factura. Comprobado que el monto es correcto.',
            'Recibo' => 'Revisar las tarifas del recibo. Comprobado que son correctas.',
            'Póliza' => 'Confirmación de emisión de la póliza de seguro.',
            'Contrato' => 'Contrato firmado por ambas partes. Verificado que todos los términos están claros.',
            'Informe' => 'Informe revisado y aprobado. Sin cambios adicionales.',
            'Comprobante' => 'Verificación del pago realizado. Todos los detalles coinciden con la transacción.',
            'Notificación' => 'Se notificó al cliente sobre la factura pendiente de pago.',
            'Acta' => 'Acta firmada por los asistentes, con todos los puntos acordados.',
            'Devolución' => 'Verificación de devolución procesada. Confirmado que el producto ha sido devuelto correctamente.',
            'Solicitud' => 'Solicitud revisada y aprobada, pendiente de confirmación final.',
            'Presupuesto' => 'Presupuesto aprobado, proceder con la compra.',
            'Declaración' => 'Declaración fiscal presentada y aprobada por el departamento contable.',
        ];

        foreach ($notes as $key => $note) {
            if (strpos($title, $key) !== false) {
                return $note;
            }
        }
        return 'Nota generada automáticamente. Revisar el documento.';
    }

    /**
     * @return array<string>
     */
    public function getQueries(): array
    {
        $queries = [];

        $documentId = \HomeDocs\Utils::uuidv4();

        $creationTimestamp = mt_rand(
            strtotime('01-01-2000'),
            strtotime('31-10-2025')
        );

        $title = array_rand($this->documentTemplates);

        $queries[] = sprintf(
            'INSERT INTO DOCUMENT (id, title, description) VALUES("%s", "%s", "%s");',
            $documentId,
            $title,
            sprintf($this->documentTemplates[$title], date('d-m-Y', $creationTimestamp))
        );

        $queries[] = sprintf('INSERT INTO DOCUMENT_HISTORY (document_id, created_on_timestamp, operation_type, created_by_user_id) VALUES("%s", %d, 1, "%s");', $documentId, $creationTimestamp * 1000, $this->userId);

        $documentUpdatesCount = mt_rand(0, $this->maxDocumentUpdatesCount);
        for ($j = 0; $j < $documentUpdatesCount; $j++) {
            $queries[] = sprintf(
                'INSERT INTO DOCUMENT_HISTORY (document_id, created_on_timestamp, operation_type, created_by_user_id) VALUES("%s", %d, 2, "%s");',
                $documentId,
                mt_rand($creationTimestamp + (10 * 60), $creationTimestamp + (5 * 24 * 60 * 60)) * 1000,
                $this->userId
            );
        }

        $queries[] = sprintf('INSERT INTO DOCUMENT_TAG (document_id, tag) VALUES("%s", "%s");', $documentId, date('Y', $creationTimestamp));

        $tags = $this->generateTagsFromDocument($title, $this->documentTemplates[$title]);
        foreach ($tags as $tag) {
            if (strlen($tag) > 3) {
                $queries[] = sprintf(
                    'INSERT INTO DOCUMENT_TAG (document_id, tag) VALUES("%s", "%s");',
                    $documentId,
                    $tag
                );
            }
        }

        $queries[] = sprintf(
            'INSERT INTO DOCUMENT_NOTE VALUES("%s", "%s", %d, "%s", "%s");',
            \HomeDocs\Utils::uuidv4(),
            $documentId,
            ($creationTimestamp * 1000) + mt_rand(0, 86400000),
            $this->userId,
            $this->generateNoteBody($title)
        );

        $attachments = ["report.pdf", "factura.pdf", "ticket.doc", "informe.docx"];
        $documentAttachmentsCount = mt_rand(0, count($attachments));
        for ($j = 0; $j < $documentAttachmentsCount; $j++) {
            $attachmentId = \HomeDocs\Utils::uuidv4();
            $queries[] = sprintf(
                'INSERT INTO ATTACHMENT (id, sha1_hash, name, size, created_by_user_id, created_on_timestamp) VALUES("%s", "%s", "%s", %d, "%s", %d);',
                $attachmentId,
                sha1(\HomeDocs\Utils::uuidv4()),
                $attachments[$j],
                mt_rand(1024, 10485760),
                $this->userId,
                ($creationTimestamp * 1000) + mt_rand(0, 86400000)
            );
            $queries[] = sprintf(
                'INSERT INTO DOCUMENT_ATTACHMENT (document_id, attachment_id) VALUES ("%s", "%s");',
                $documentId,
                $attachmentId
            );
        }
        return $queries;
    }
}
