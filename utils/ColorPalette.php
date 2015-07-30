<?php

namespace Utils;

/**
* Class ColorPalette
* @package Utils
* @see http://stackoverflow.com/a/43235
* @see http://bavotasan.com/2011/convert-hex-color-to-rgb-using-php/
*/
class ColorPalette
{
    protected $base_r;
    protected $base_g;
    protected $base_b;

    protected $catalogue = [];
    protected $modifier;
    protected $options;

    /**
    * Stock the base color and options
    * @param   mixed   $color     Base color can be a string (hex) or an array (rgb)
    * @param   array   $options   General options for the generator (optional)
    */
    public function __construct($color, $options = [])
    {

        // Default options
        $this->options = [
            'avoid_proximity' => true,
            'return_format'   => 'hex',
            'variance'        => 25
        ];

        if (is_array($options) && !empty($options)) {
            $this->options = array_merge_recursive($this->options, $options);
        }

        // Check what type of color we're working with
        // Some checks on each array index for ints would be good too
        if (is_array($color) && !empty($color) && count($color) === 3) {
            $rgb = $color;

        // We need better checks for hex
        } elseif (is_string($color) && !empty($color)) {
            $rgb = $this->hex2rgb($color);

        // Default color will be grey, but we should really return an error
        } else {
            $rgb = [127,127,127];
        }

        $this->base_r = $rgb[0];
        $this->base_g = $rgb[1];
        $this->base_b = $rgb[2];
    }

    /**
    * Convert a hexadecimal color to RGB
    * @param    string   $hex Hex color code
    * @return   array
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
    * @param    array   $rgb   An array with rgb data
    * @return   string
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
    * Proximity needs to be checked, refers to catalogue for validation.
    * A variance of 50 is best for this feature.
    * @param    array     $rgb RGB values
    * @return   booleam
    */
    private function check_proximity($rgb)
    {
        $proximity = false;

        // We only check the most recently generated colors as a catalogue could get quite large
        // @TODO Add history count to options
        $colors_to_check = array_slice($this->catalogue, -5);

        $r = $rgb[0];
        $g = $rgb[1];
        $b = $rgb[2];

        foreach ($colors_to_check as $color) {
            // We can validate with $r on its own since all channels were modified with the same variance
            // Colors will be much closer the lower you go
            // @TODO Add proximity tolerance to options
            if (abs( $r - $color[0]) < 10) {
                $proximity = true;
            }
        }

        return $proximity;
    }

    /**
    * Generate a random variance for each color channel
    * @return   mixed
    */
    private function generate_colors()
    {

        $variance = rand(0, $this->options['variance']);

        // Switch operator on the variance
        if ($this->options['avoid_proximity']) {
            $this->modifier++;
            $variance = $variance * ( ( $this->modifier % 2 ) ? 1 : -1 );
        }

        $r = max(0, min(255, $this->base_r + $variance));
        $g = max(0, min(255, $this->base_g + $variance));
        $b = max(0, min(255, $this->base_b + $variance));

        return [$r, $g, $b];
    }

    /**
    * Render a new color according to a degree of variance and proximity avoidance
    * @return   mixed
    */
    public function render()
    {
        if ($this->options['avoid_proximity']) {

            // Reset modifier
            $this->modifier = 0;

            $counter = 0;
            $safety = 20;

            while ($safety > $counter) {

                $rgb = $this->generate_colors();

                if (!$this->check_proximity($rgb)) {
                    break;
                }

                $counter ++;
            }

        } else {
            $rgb = $this->generate_colors();
        }


        // After proximity testing, we can now save our color in the catalogue
        $this->catalogue[] = $rgb;

        return ($this->options['return_format'] === 'hex') ? $this->rgb2hex($rgb) : $rgb;
    }
}