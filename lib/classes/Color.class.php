<?php
/**
 * lib/classes/Color.class.php - class to mix colors and convert them between different types
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Rasmus Fuhse <fuhse@data-quest.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 */

/**
 * class to mix colors and convert them between different types
 *
 * @since       2.1
 */
class Color {
    /**
     * HTML-4-Standard color-names plus a few additions
     * May be expanded in a later version to allow
     *  X11 color names as described in
     *  http://en.wikipedia.org/wiki/Web_colors
     * 
     * @var array
     */
    static $colorstrings = [
        'aqua'           => [0, 255, 255, 1],
        'azure'          => [240, 255, 255, 1],
        'blue'           => [0, 0, 255, 1],
        'cyan'           => [0, 255, 255, 1],
        'darkblue'       => [0, 0, 139, 1],
        'darkred'        => [139, 0, 0, 1],
        'gold'           => [255, 215, 0, 1],
        'gray'           => [128, 128, 128, 1],
        'indigo'         => [75, 0, 130, 1],
        'lightsteelblue' => [176, 196, 222, 1],
        'lightyellow'    => [255, 255, 224, 1],
        'lime'           => [0, 255, 0, 1],
        'magenta'        => [255, 0, 255, 1],
        'navy'           => [0, 0, 128, 1],
        'olive'          => [128, 128, 0, 1],
        'orange'         => [255, 165, 0, 1],
        'pink'           => [255, 192, 203, 1],
        'red'            => [255, 0, 0, 1],
        'purple'         => [128, 0, 128, 1],
        'violet'         => [238, 130, 238, 1],
        'yellow'         => [255, 255, 0, 1],
        'black'          => [0, 0, 0, 1],
        'white'          => [255, 255, 255, 1]
    ];

    /**
     * converts a css-hex-color into a rgba-quadruple
     *
     * @param  string  $color  the color to be converted
     * @return array   colors as rgba-quadruple
     */
    static function hex2array($color) {
        $color = str_replace('#','',$color);
        $arr[0] = hexdec(mb_substr($color,0,2));
        $arr[1] = hexdec(mb_substr($color,2,2));
        $arr[2] = hexdec(mb_substr($color,4,2));
        $arr[3] = 1.0;
        return $arr;
    }

    /**
     * converts a css-rgba-color into a rgba-quadruple
     *
     * @param  string  $color  the color to be converted
     * @return array   colors as rgba-quadruple
     */
    static function rgba2array($color) {
        preg_match("/rgba\(\s*(\d+\%?),\s*(\d+\%?),\s*(\d+\%?),\s*(\d*\.?\d*)\s*\)/", $color, $matches);
        array_shift($matches);
        $matches[3] = floatval($matches[3]);
        return $matches;
    }

    /**
     * converts a css-rgb-color into a rgba-quadruple
     *
     * @param  string  $color  the color to be converted
     * @return array   colors as rgba-quadruple
     */
    static function rgb2array($color) {
        preg_match("/rgb\(\s*(\d+\%?),\s*(\d+\%?),\s*(\d+\%?)\s*\)/", $color, $matches);
        array_shift($matches);
        $matches[3] = 1.0;
        return $matches;
    }

    /**
     * converts a css-hsl-color into a rgba-quadruple
     *
     * @param  string  $color  the color to be converted
     * @return array   colors as rgba-quadruple
     */
    static function hsl2array($color) {
        preg_match("/hsl\(\s*(\d+),\s*(\d+)\%,\s*(\d+)\%\s*\)/", $color, $matches);
        array_shift($matches);
        $matches[0] %= 360;
        $matches[3] = 1.0;
        $h = $matches[0];
        $s = $matches[1];
        $l = $matches[2];
        $m2 = ($l <= 0.5) ? $l * ($s + 1) : $l + $s - $l*$s;
        $m1 = $l * 2 - $m2;
        return [self::_color_hue2rgb($m1, $m2, $h + 0.33333),
            self::_color_hue2rgb($m1, $m2, $h),
            self::_color_hue2rgb($m1, $m2, $h - 0.33333),
            1.0
        ];
    }

    static private function _color_hue2rgb($m1, $m2, $h) {
        $h = ($h < 0) ? $h + 1 : (($h > 1) ? $h - 1 : $h);
        if ($h * 6 < 1) return $m1 + ($m2 - $m1) * $h * 6;
        if ($h * 2 < 1) return $m2;
        if ($h * 3 < 2) return $m1 + ($m2 - $m1) * (0.66666 - $h) * 6;
        return $m1;
    }

    /**
     * Mixes two colors by preferring the first one with $percentOfColor1 percent.
     * If color2 is the only color with alpha-chanel, it will return a color
     * in the format of color2 else (most of the time) in the format of color1.
     * @return string: "#ffffff", "rgb(...)", "rgba(...)" depending on color1 and alpha-chanel
     */
    static function mix($color1, $color2, $percentOfColor1 = 50, $f = null) {
        $percentOfColor1 = $percentOfColor1 > 100
                            ? 100
                            : ($percentOfColor1 < 0 ? 0 : $percentOfColor1);
        list($color1, $format1) = self::_normalize($color1);
        list($color2, $format2) = self::_normalize($color2);
        $color_new[0] = floor(($color1[0] * $percentOfColor1
                            + $color2[0] * (100 - $percentOfColor1)) / 100);
        $color_new[1] = floor(($color1[1] * $percentOfColor1
                            + $color2[1] * (100 - $percentOfColor1)) / 100);
        $color_new[2] = floor(($color1[2] * $percentOfColor1
                            + $color2[2] * (100 - $percentOfColor1)) / 100);
        $color_new[3] = ($color1[3] * $percentOfColor1
                            + $color2[3] * (100 - $percentOfColor1)) / 100;
        $format = ((mb_strpos($format2, "a") !== false) && (mb_strpos($fotmat1, "a") === false))
                    ? $format2
                    : $format1;
        $func = "_array2" . $format;
        return self::$func($color_new);
    }

    /**
     * Sets the opacity for a color. Hue and saturation is kept, but opacity is changed.
     * Returns a format with alpha-chanel (rgba or hsla) depending on the given format.
     */
    static function opacity($color, $opacity) {
        list($color, $format) = self::_normalize($color);
        if (in_array($format, ["hex", "rgb"])) {
            $format = "rgba";
        } elseif ($format = "hsl") {
            $format = "hsla";
        }
        $color[3] = floatval($opacity);
        $color[3] = $color[3] < 0 ? 0 : ($color[3] > 1 ? 1 : $color[3]);
        $func = "_array2" . $format;
        return self::$func($color);
    }

    /**
     * Make the passed color brighter
     *
     * @author Till Glöggler <tgloeggl@uos.de>
     *
     * @param  string  $color  any type of css-valid color
     * @param  int  $factor  percentage of brightning, 100 for white and 0 for no changes to color
     * @return string  brightened color in same format as the passed one
     */
    static function brighten($color, $factor = 35) {
        // convert to color to rgba
        list($color, $format) = self::_normalize($color);
        if ($factor > 100) {
            $factor = 100;
        } elseif($factor < 0) {
            $factor = 0;
        }

        // return the color itself, if the conversion failed
        if (!$format) return $color;

        // brighten the color
        $color[0] = floor(($color[0] * (100 - $factor) + 255 * $factor) / 100);
        $color[1] = floor(($color[1] * (100 - $factor) + 255 * $factor) / 100);
        $color[2] = floor(($color[2] * (100 - $factor) + 255 * $factor) / 100);
        
        // convert the color back (if possible)
        $func = "_array2" . $format;
        return self::$func($color);
    }

    /**
     * converts any css-color into a rgba-quadruple
     *
     * @param  string  $color  the color to be converted
     * @return array   colors as rgba-quadruple
     */
    private static function _normalize($color) {
        if ($color[0] === "#") {
            $format = "hex";
            $arr = self::hex2array($color);
        } elseif (preg_match("/\(.*\)/", $color)) {
            $format = mb_substr($color, 0, mb_strpos($color, "("));
            $func = $format."2array";
            $arr = self::$func($color);
        } elseif (self::$colorstrings[mb_strtolower($color)]) {
            $format = "rgb"; //we don't want colors as strings like "red"
            $arr = self::$colorstrings[mb_strtolower($color)];
        }
        return [$arr, $format];
    }

    /**
     * converts a css-rgba-color into a rgba-quadruple
     *
     * @param  string  $color  the color to be converted
     * @return array   colors as rgba-quadruple
     */
    static function rgba($color) {
        list($arr, $format) = self::_normalize($color);
        return self::_array2rgba($arr);
    }

    /**
     * converts a css-rgb-color into a rgba-quadruple
     *
     * @param  string  $color  the color to be converted
     * @return array   colors as rgba-quadruple
     */
    static function rgb($color) {
        list($arr, $format) = self::_normalize($color);
        return self::_array2rgb($arr);
    }

    /**
     * converts a css-hex-color into a rgba-quadruple
     *
     * @param  string  $color  the color to be converted
     * @return array   colors as rgba-quadruple
     */
    static function hex($color) {
        list($arr, $format) = self::_normalize($color);
        return self::_array2hex($arr);
    }

    /**
     * converts a css-hsl-color into a rgba-quadruple
     *
     * @param  string  $color  the color to be converted
     * @return array   colors as rgba-quadruple
     */
    static function hsl($color) {
        list($arr, $format) = self::_normalize($color);
        return self::_array2hsl($arr);
    }

    /**
     * converts a css-hsla-color into a rgba-quadruple
     *
     * @param  string  $color  the color to be converted
     * @return array   colors as rgba-quadruple
     */
    static function hsla($color) {
        list($arr, $format) = self::_normalize($color);
        return self::_array2hsla($arr);
    }


    /**
     * converts a rgba-quadruple into a css-hex-color
     *
     * @param  array  $arr  the rgba-quadruple to be converted
     * @return array  colors as css-hex
     */
    static private function _array2hex($arr) {
        return "#" .
            ($arr[0] < 16 ? "0" : "").dechex($arr[0]) .
            ($arr[1] < 16 ? "0" : "").dechex($arr[1]) .
            ($arr[2] < 16 ? "0" : "").dechex($arr[2]);
    }

    /**
     * converts a rgba-quadruple into a css-rgb-color
     *
     * @param  array  $arr  the rgba-quadruple to be converted
     * @return array  colors as css-rgb
     */
    static private function _array2rgb($arr) {
        array_pop($arr);
        return "rgb(".implode(", ", $arr).")";
    }

    /**
     * converts a rgba-quadruple into a css-rgba-color
     *
     * @param  array  $arr  the rgba-quadruple to be converted
     * @return array  colors as css-rgba
     */
    static private function _array2rgba($arr) {
        return "rgba(".implode(", ", $arr).")";
    }

    /**
     * converts a rgba-quadruple into a css-hsl-color
     *
     * @param  array  $arr  the rgba-quadruple to be converted
     * @return array  colors as css-hsl
     */
    static private function _array2hsl($arr) {
        $arr = self::_calculate_hsl($arr);
        return "hsl(".$arr[0].", ".$arr[1]."%, ".$arr[2]."%)";
    }

    /**
     * converts a rgba-quadruple into a css-hsla-color
     *
     * @param  array  $arr  the rgba-quadruple to be converted
     * @return array  colors as css-hsla
     */
    static private function _array2hsla($arr) {
        $hsl = self::_calculate_hsl($arr);
        return "hsl(".$hsl[0].", ".$hsl[1]."%, ".$hsl[2]."%, ".$arr[3].")";
    }

    static private function _calculate_hsl($arr) {
        $clrR = ($arr[0]);
        $clrG = ($arr[1]);
        $clrB = ($arr[2]);

        $clrMin = min($clrR, $clrG, $clrB);
        $clrMax = max($clrR, $clrG, $clrB);
        $deltaMax = $clrMax - $clrMin;

        $L = ($clrMax + $clrMin) / 510;

        if (0 == $deltaMax) {
            $H = 0;
            $S = 0;
        } else {
            if (0.5 > $L) {
                $S = $deltaMax / ($clrMax + $clrMin);
            } else {
                $S = $deltaMax / (510 - $clrMax - $clrMin);
            }

            if ($clrMax == $clrR) {
                $H = ($clrG - $clrB) / (6.0 * $deltaMax);
            } else if ($clrMax == $clrG) {
                $H = 1/3 + ($clrB - $clrR) / (6.0 * $deltaMax);
            } else {
                $H = 2 / 3 + ($clrR - $clrG) / (6.0 * $deltaMax);
            }

            if (0 > $H) $H += 1;
            if (1 < $H) $H -= 1;
        }
        return [$H, $S, $L];
    }

}