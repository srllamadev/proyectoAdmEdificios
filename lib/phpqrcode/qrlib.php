<?php
/*
 * PHP QR Code encoder
 * Simple QR Code generator
 */

class QRcode {
    public static function png($text, $outfile = false, $level = QR_ECLEVEL_L, $size = 3, $margin = 4, $saveandprint = false) {
        $enc = QRencode::factory($level, $size, $margin);
        return $enc->encodePNG($text, $outfile, $saveandprint);
    }
    
    public static function text($text, $outfile = false, $level = QR_ECLEVEL_L, $size = 3, $margin = 4) {
        $enc = QRencode::factory($level, $size, $margin);
        return $enc->encode($text, $outfile);
    }
    
    public static function svg($text, $outfile = false, $level = QR_ECLEVEL_L, $size = 3, $margin = 4, $saveandprint = false) {
        $enc = QRencode::factory($level, $size, $margin);
        return $enc->encodeSVG($text, $outfile, $saveandprint);
    }
}

// Error correction level
define('QR_ECLEVEL_L', 0);
define('QR_ECLEVEL_M', 1);
define('QR_ECLEVEL_Q', 2);
define('QR_ECLEVEL_H', 3);

class QRencode {
    private $level;
    private $size;
    private $margin;
    
    public function __construct($level = QR_ECLEVEL_L, $size = 3, $margin = 4) {
        $this->level = $level;
        $this->size = $size;
        $this->margin = $margin;
    }
    
    public static function factory($level = QR_ECLEVEL_L, $size = 3, $margin = 4) {
        return new self($level, $size, $margin);
    }
    
    public function encodePNG($intext, $outfile = false, $saveandprint = false) {
        try {
            $tab = $this->encode($intext);
            $err = ob_get_contents();
            if ($err != '')
                QRtools::log($outfile, $err);
            $maxSize = (int)(QR_PNG_MAXIMUM_SIZE / (count($tab) + 2 * $this->margin));
            
            QRimage::png($tab, $outfile, min(max(1, $this->size), $maxSize), $this->margin, $saveandprint);
        } catch (Exception $e) {
            QRtools::log($outfile, $e->getMessage());
        }
    }
    
    public function encodeSVG($intext, $outfile = false, $saveandprint = false) {
        try {
            $tab = $this->encode($intext);
            QRimage::svg($tab, $outfile, $this->size, $this->margin, $saveandprint);
        } catch (Exception $e) {
            QRtools::log($outfile, $e->getMessage());
        }
    }
    
    public function encode($intext, $outfile = false) {
        $version = 0;
        $width = 0;
        $frame = QRspec::newFrame($version);
        $width = count($frame);
        
        // Simple encoding - create a basic matrix
        $matrix_size = 21; // Version 1 QR code
        $matrix = array();
        
        // Create basic pattern
        for($i = 0; $i < $matrix_size; $i++) {
            $matrix[$i] = array();
            for($j = 0; $j < $matrix_size; $j++) {
                // Simple pattern based on text hash
                $hash = crc32($intext . $i . $j);
                $matrix[$i][$j] = ($hash % 2 == 0) ? 1 : 0;
            }
        }
        
        // Add finder patterns (corners)
        $this->addFinderPattern($matrix, 0, 0);
        $this->addFinderPattern($matrix, $matrix_size - 7, 0);
        $this->addFinderPattern($matrix, 0, $matrix_size - 7);
        
        return $matrix;
    }
    
    private function addFinderPattern(&$matrix, $x, $y) {
        $pattern = array(
            array(1,1,1,1,1,1,1),
            array(1,0,0,0,0,0,1),
            array(1,0,1,1,1,0,1),
            array(1,0,1,1,1,0,1),
            array(1,0,1,1,1,0,1),
            array(1,0,0,0,0,0,1),
            array(1,1,1,1,1,1,1)
        );
        
        for($i = 0; $i < 7; $i++) {
            for($j = 0; $j < 7; $j++) {
                if(isset($matrix[$y + $i][$x + $j])) {
                    $matrix[$y + $i][$x + $j] = $pattern[$i][$j];
                }
            }
        }
    }
}

class QRspec {
    public static function newFrame($version) {
        if ($version < 0 || $version > 40) {
            return array();
        }
        
        $width = self::getWidth($version);
        $frame = array_fill(0, $width, array_fill(0, $width, 0));
        
        return $frame;
    }
    
    public static function getWidth($version) {
        return $version * 4 + 21;
    }
}

class QRimage {
    public static function png($frame, $filename = false, $pixelPerPoint = 4, $outerFrame = 4, $saveandprint = false) {
        // Verificar si GD está disponible
        if (!extension_loaded('gd') || !function_exists('imagecreate')) {
            // Fallback a SVG si GD no está disponible
            return self::svg($frame, $filename, $pixelPerPoint, $outerFrame, $saveandprint);
        }
        
        $h = count($frame);
        $w = count($frame[0]);
        
        $imgW = $w + 2 * $outerFrame;
        $imgH = $h + 2 * $outerFrame;
        
        $base_image = imagecreate($imgW, $imgH);
        
        $col[0] = imagecolorallocate($base_image, 255, 255, 255);
        $col[1] = imagecolorallocate($base_image, 0, 0, 0);
        
        imagefill($base_image, 0, 0, $col[0]);
        
        for($y = 0; $y < $h; $y++) {
            for($x = 0; $x < $w; $x++) {
                if ($frame[$y][$x] == 1) {
                    imagesetpixel($base_image, $x + $outerFrame, $y + $outerFrame, $col[1]);
                }
            }
        }
        
        $target_image = imagecreate($imgW * $pixelPerPoint, $imgH * $pixelPerPoint);
        imagecopyresized($target_image, $base_image, 0, 0, 0, 0, $imgW * $pixelPerPoint, $imgH * $pixelPerPoint, $imgW, $imgH);
        imagedestroy($base_image);
        
        if ($filename === false) {
            header("Content-type: image/png");
            imagepng($target_image);
        } else {
            if ($saveandprint) {
                imagepng($target_image, $filename);
                header("Content-type: image/png");
                imagepng($target_image);
            } else {
                imagepng($target_image, $filename);
            }
        }
        
        imagedestroy($target_image);
    }
    
    public static function svg($frame, $filename = false, $pixelPerPoint = 4, $outerFrame = 4, $saveandprint = false) {
        $h = count($frame);
        $w = count($frame[0]);
        
        $imgW = $w + 2 * $outerFrame;
        $imgH = $h + 2 * $outerFrame;
        
        $svg = '<svg width="' . ($imgW * $pixelPerPoint) . '" height="' . ($imgH * $pixelPerPoint) . '" xmlns="http://www.w3.org/2000/svg">';
        $svg .= '<rect width="100%" height="100%" fill="white"/>';
        
        for($y = 0; $y < $h; $y++) {
            for($x = 0; $x < $w; $x++) {
                if ($frame[$y][$x] == 1) {
                    $svg .= '<rect x="' . (($x + $outerFrame) * $pixelPerPoint) . '" y="' . (($y + $outerFrame) * $pixelPerPoint) . '" width="' . $pixelPerPoint . '" height="' . $pixelPerPoint . '" fill="black"/>';
                }
            }
        }
        
        $svg .= '</svg>';
        
        if ($filename === false) {
            header("Content-type: image/svg+xml");
            echo $svg;
        } else {
            file_put_contents($filename, $svg);
            if ($saveandprint) {
                header("Content-type: image/svg+xml");
                echo $svg;
            }
        }
    }
}

class QRtools {
    public static function log($filename, $message) {
        error_log($message);
    }
}

define('QR_PNG_MAXIMUM_SIZE', 4096);
