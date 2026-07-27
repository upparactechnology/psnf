<?php
declare(strict_types=1);

namespace App\Services;

class PdfService
{
    public function jpgToPdf(string $jpgPath, string $pdfPath): void
    {
        if (!is_file($jpgPath)) {
            throw new \RuntimeException('JPG file not found: ' . $jpgPath);
        }

        $imageInfo = getimagesize($jpgPath);
        if ($imageInfo === false || ($imageInfo[2] ?? null) !== IMAGETYPE_JPEG) {
            throw new \RuntimeException('Only JPG input is supported for PDF conversion.');
        }

        [$imageWidth, $imageHeight] = $imageInfo;
        $jpegBinary = file_get_contents($jpgPath);
        if ($jpegBinary === false) {
            throw new \RuntimeException('Unable to read JPG file for PDF conversion.');
        }

        $pageWidth = 842.0;
        $pageHeight = 595.0;

        $scale = min($pageWidth / $imageWidth, $pageHeight / $imageHeight);
        $drawWidth = $imageWidth * $scale;
        $drawHeight = $imageHeight * $scale;

        $x = ($pageWidth - $drawWidth) / 2.0;
        $y = ($pageHeight - $drawHeight) / 2.0;

        $contentStream = sprintf(
            "q %.2F 0 0 %.2F %.2F %.2F cm /Im0 Do Q",
            $drawWidth,
            $drawHeight,
            $x,
            $y
        );

        $objects = [];
        $objects[] = '<< /Type /Catalog /Pages 2 0 R >>';
        $objects[] = '<< /Type /Pages /Count 1 /Kids [3 0 R] >>';
        $objects[] = sprintf(
            '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 %.2F %.2F] /Resources << /XObject << /Im0 4 0 R >> /ProcSet [/PDF /ImageC] >> /Contents 5 0 R >>',
            $pageWidth,
            $pageHeight
        );
        $objects[] = '<< /Type /XObject /Subtype /Image /Width ' . (int) $imageWidth . ' /Height ' . (int) $imageHeight . ' /ColorSpace /DeviceRGB /BitsPerComponent 8 /Filter /DCTDecode /Length ' . strlen($jpegBinary) . " >>\nstream\n" . $jpegBinary . "\nendstream";
        $objects[] = '<< /Length ' . strlen($contentStream) . " >>\nstream\n" . $contentStream . "\nendstream";

        $pdf = "%PDF-1.4\n";
        $offsets = [];

        $objectCount = count($objects);
        for ($i = 0; $i < $objectCount; $i++) {
            $objectNumber = $i + 1;
            $offsets[$objectNumber] = strlen($pdf);
            $pdf .= $objectNumber . " 0 obj\n" . $objects[$i] . "\nendobj\n";
        }

        $xrefPosition = strlen($pdf);
        $pdf .= "xref\n0 " . ($objectCount + 1) . "\n";
        $pdf .= "0000000000 65535 f \n";

        for ($i = 1; $i <= $objectCount; $i++) {
            $pdf .= sprintf('%010d 00000 n ', $offsets[$i]) . "\n";
        }

        $pdf .= "trailer\n<< /Size " . ($objectCount + 1) . " /Root 1 0 R >>\n";
        $pdf .= "startxref\n" . $xrefPosition . "\n%%EOF";

        file_put_contents($pdfPath, $pdf);
    }
}
