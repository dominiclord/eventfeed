<?php

namespace Utils;

/**
* Class GenerateRandomColor
* @package Utils
*
* Solution taken from here:
* http://stackoverflow.com/a/43235
*/
class GenerateRandomColor
{
    protected $base_r;
    protected $base_g;
    protected $base_b;
    protected $variance;

    /**
    * @param string $color Hex color code
    */
    public function __construct($color, $variance = 10)
    {

        $this->variance = $variance;

        $rgb = $this->hex2rgb($color);

        $this->base_r = $rgb[0];
        $this->base_g = $rgb[1];
        $this->base_b = $rgb[2];
    }

    /**
    * Convert a hexadecimal color to RGB
    * @param string $hex Hex color code
    * @see http://bavotasan.com/2011/convert-hex-color-to-rgb-using-php/
    */
    public function hex2rgb($hex) {
        $hex = str_replace('#', '', $hex);

        if (strlen($hex) === 3) {
            $r = hexdec(substr($hex,0,1).substr($hex,0,1));
            $g = hexdec(substr($hex,1,1).substr($hex,1,1));
            $b = hexdec(substr($hex,2,1).substr($hex,2,1));
        } else {
            $r = hexdec(substr($hex,0,2));
            $g = hexdec(substr($hex,2,2));
            $b = hexdec(substr($hex,4,2));
        }

        return [$r, $g, $b];
    }

    /**
    * Convert an RGB color to hexadecimal
    * @param string $rgb RGB color code
    * @see http://bavotasan.com/2011/convert-hex-color-to-rgb-using-php/
    */
    public function rgb2hex($rgb)
    {
        $hex = "#";
        $hex .= str_pad(dechex($rgb[0]), 2, "0", STR_PAD_LEFT);
        $hex .= str_pad(dechex($rgb[1]), 2, "0", STR_PAD_LEFT);
        $hex .= str_pad(dechex($rgb[2]), 2, "0", STR_PAD_LEFT);

        return $hex;
    }

    /**
    * @param int $length
    * @return string
    */
    public function generate()
    {
        $color  = [];
        $variance = $this->variance;

        /*
        for ($c=0; $c<3; $c++) {
            $color[$c] = rand(0 + $variance, 255 - $variance);
        }
        */

        $r = rand($this->base_r-$variance, $this->base_r+$variance);
        $g = rand($this->base_g-$variance, $this->base_g+$variance);
        $b = rand($this->base_b-$variance, $this->base_b+$variance);

        return $this->rgb2hex([$r, $g, $b]);
    }
}