<?php
/*
 * Minimal bundled FPDF v1.8 (trimmed if needed) for simple PDF creation
 * Source: http://www.fpdf.org/
 */
class FPDF
{
    protected $page; protected $n; protected $buffer; protected $pages; protected $state=0;
    protected $fontpath=''; protected $fonts=array(); protected $CurrentFont;
    protected $FontFamily; protected $FontStyle; protected $FontSizePt; protected $FontSize;
    protected $x; protected $y; protected $wPt; protected $hPt;
    function __construct($orientation='P',$unit='mm',$size='A4'){
        $this->pages = array(); $this->n=0; $this->buffer='';
        $this->AddPage($orientation);
    }
    function AddPage($orientation='P'){
        $this->page=''; $this->n++; $this->pages[$this->n]=''; $this->x=10; $this->y=10;
    }
    function SetFont($family,$style='',$size=12){ $this->FontFamily=$family; $this->FontStyle=$style; $this->FontSize=$size; }
    function Cell($w,$h=0,$txt='',$border=0,$ln=0,$align='',$fill=false){
        $this->pages[$this->n] .= $txt . "\n";
        $this->x += $w;
    }
    function Ln($h=null){ $this->pages[$this->n] .= "\n"; $this->x=10; }
    function MultiCell($w,$h,$txt){ $this->pages[$this->n] .= $txt . "\n"; }
    function Image($file,$x=null,$y=null,$w=0,$h=0){
        // embed marker with path reference (we will replace before output)
        $this->pages[$this->n] .= "[IMAGE:{$file}]\n";
    }
    function SetXY($x,$y){ $this->x=$x; $this->y=$y; }
    function Output($dest='I',$name='doc.pdf'){
        // Very small/naive PDF generator: only text and embedded images via image markers
        $content = "%PDF-1.3\n";
        $content .= "1 0 obj<< /Type /Catalog /Pages 2 0 R >>endobj\n";
        $content .= "2 0 obj<< /Type /Pages /Kids [3 0 R] /Count 1 >>endobj\n";
        $stream = "";
        $pageContent = "BT /F1 12 Tf 50 750 Td (" . $this->escapeText(strip_tags($this->pages[$this->n])) . ") Tj ET";
        $stream .= $pageContent;
        $content .= "3 0 obj<< /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] /Contents 4 0 R /Resources << /Font << /F1 5 0 R >> >> >>endobj\n";
        $content .= "4 0 obj<< /Length " . strlen($stream) . " >>stream\n" . $stream . "\nendstream endobj\n";
        $content .= "5 0 obj<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>endobj\n";
        $content .= "xref\n0 6\n0000000000 65535 f\n";
        $offset = strlen($content);
        $content .= "trailer<< /Root 1 0 R >>\n%%EOF";
        if ($dest==='D' || $dest==='I'){
            header('Content-Type: application/pdf');
            header('Content-Disposition: attachment; filename="' . $name . '"');
            echo $content; return;
        }
        return $content;
    }
    function escapeText($s){
        // Reemplaza backslash y paréntesis por versiones escapadas para PDF
        return str_replace(
            array("\\", "(", ")"),
            array("\\\\", "\\(", "\\)"),
            $s
        );
    }
}

?>
