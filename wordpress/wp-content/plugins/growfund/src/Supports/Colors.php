<?php

namespace Growfund\Supports;

defined( 'ABSPATH' ) || exit;

use InvalidArgumentException;

class Colors
{
    protected $hex;
    protected $hsl;
    protected $lightness;

    public function __construct(string $hex)
    {
        $this->hex = $hex;
        $this->hsl = static::hex_to_hsl($hex);
    }

    public static function from(string $hex)
    {
        return new static($hex);
    }

    public function make_lighter($lightness = 95)
    {
        $this->hsl['l'] = $lightness;
        return $this;
    }

    public function make_darker($lightness = 40)
    {
        $this->hsl['l'] = $lightness;
        return $this;
    }

    public function get_aa_compliant_colors()
    {
        $original_hsl = $this->hsl;
        $target_contrast = 4.5;

        $background_lightness = $this->calculate_background_lightness($original_hsl);
        $foreground_lightness = $this->calculate_foreground_lightness($original_hsl, $background_lightness, $target_contrast);

        $background_hsl = array_merge($original_hsl, ['l' => $background_lightness]);
        $foreground_hsl = array_merge($original_hsl, ['l' => $foreground_lightness]);

        $background_hex = $this->hsl_to_hex($background_hsl);
        $foreground_hex = $this->hsl_to_hex($foreground_hsl);

        $contrast_ratio = $this->calculate_contrast_ratio($background_hex, $foreground_hex);

        return [
            'background' => $this->hsl_to_string($background_hsl),
            'foreground' => $this->hsl_to_string($foreground_hsl),
            'contrast_ratio' => $contrast_ratio
        ];
    }

    protected function calculate_background_lightness($hsl)
    {
        $original_lightness = $hsl['l'];

        if ($original_lightness >= 85) {
            return min($original_lightness, 95);
        }

        return 90;
    }

    protected function calculate_foreground_lightness($hsl, $background_lightness, $target_contrast)
    {
        $background_hsl = array_merge($hsl, ['l' => $background_lightness]);
        $background_hex = $this->hsl_to_hex($background_hsl);

        $min_lightness = 5;
        $max_lightness = 50;

        for ($foreground_lightness = $max_lightness; $foreground_lightness >= $min_lightness; $foreground_lightness -= 0.5) {
            $foreground_hsl = array_merge($hsl, ['l' => $foreground_lightness]);
            $foreground_hex = $this->hsl_to_hex($foreground_hsl);
            $contrast_ratio = $this->calculate_contrast_ratio($background_hex, $foreground_hex);

            if ($contrast_ratio >= $target_contrast) {
                return $foreground_lightness;
            }
        }

        return $min_lightness;
    }

    public function get_aa_compliant_background()
    {
        $colors = $this->get_aa_compliant_colors();
        return $colors['background'];
    }

    public function get_aa_compliant_foreground()
    {
        $colors = $this->get_aa_compliant_colors();
        return $colors['foreground'];
    }

    public function calculate_contrast_ratio($color1, $color2)
    {
        $luminance1 = $this->get_relative_luminance($color1);
        $luminance2 = $this->get_relative_luminance($color2);

        $lighter = max($luminance1, $luminance2);
        $darker = min($luminance1, $luminance2);

        return ($lighter + 0.05) / ($darker + 0.05);
    }

    protected function get_relative_luminance($hex)
    {
        $hex = ltrim($hex, '#');
        $r = hexdec(substr($hex, 0, 2)) / 255;
        $g = hexdec(substr($hex, 2, 2)) / 255;
        $b = hexdec(substr($hex, 4, 2)) / 255;

        $r = $r <= 0.03928 ? $r / 12.92 : pow(($r + 0.055) / 1.055, 2.4);
        $g = $g <= 0.03928 ? $g / 12.92 : pow(($g + 0.055) / 1.055, 2.4);
        $b = $b <= 0.03928 ? $b / 12.92 : pow(($b + 0.055) / 1.055, 2.4);

        return 0.2126 * $r + 0.7152 * $g + 0.0722 * $b;
    }

    public function hsl_to_hex($hsl)
    {
        $h = $hsl['h'] / 360;
        $s = $hsl['s'] / 100;
        $l = $hsl['l'] / 100;

        /* phpcs:disable */
        if ($s == 0) {
            $r = $g = $b = $l;
        } else {
            $hue2rgb = function ($p, $q, $t) {
                if ($t < 0) $t += 1;
                if ($t > 1) $t -= 1;
                if ($t < 1 / 6) return $p + ($q - $p) * 6 * $t;
                if ($t < 1 / 2) return $q;
                if ($t < 2 / 3) return $p + ($q - $p) * (2 / 3 - $t) * 6;
                return $p;
            };

            $q = $l < 0.5 ? $l * (1 + $s) : $l + $s - $l * $s;
            $p = 2 * $l - $q;
            $r = $hue2rgb($p, $q, $h + 1 / 3);
            $g = $hue2rgb($p, $q, $h);
            $b = $hue2rgb($p, $q, $h - 1 / 3);
        }
        /* phpcs:enable */

        $r = round($r * 255);
        $g = round($g * 255);
        $b = round($b * 255);

        return sprintf('#%02x%02x%02x', $r, $g, $b);
    }

    /**
     * Convert hex color to HSL
     *
     * @param string $hex Hex color code (with or without #)
     * @return array HSL values as associative array with keys: h, s, l
     */
    public static function hex_to_hsl(string $hex)
    {
        $hex = ltrim($hex, '#');

        if (!preg_match('/^[0-9A-Fa-f]{6}$/', $hex)) {
            throw new InvalidArgumentException('Invalid hex color format');
        }

        $r = hexdec(substr($hex, 0, 2)) / 255;
        $g = hexdec(substr($hex, 2, 2)) / 255;
        $b = hexdec(substr($hex, 4, 2)) / 255;

        $max = max($r, $g, $b);
        $min = min($r, $g, $b);
        $delta = $max - $min;

        $l = ($max + $min) / 2;

        if ($delta == 0) { // phpcs:ignore
            $h = 0;
            $s = 0;
        } else {
            $s = $l > 0.5 ? $delta / (2 - $max - $min) : $delta / ($max + $min);

            switch ($max) {
                case $r:
                    $h = (($g - $b) / $delta) + ($g < $b ? 6 : 0);
                    break;
                case $g:
                    $h = (($b - $r) / $delta) + 2;
                    break;
                case $b:
                    $h = (($r - $g) / $delta) + 4;
                    break;
            }
            $h /= 6;
        }

        return [
            'h' => round($h * 360, 2),
            's' => round($s * 100, 2),
            'l' => round($l * 100, 2),
        ];
    }

    /**
     * Make the string representation of the HSL color.
     *
     * @param string $hex
     * @return string
     */
    public function to_hsl_string()
    {
        $hsl = $this->hsl;
        return sprintf('%s %s%% %s%%', $hsl['h'], $hsl['s'], $hsl['l']);
    }

    protected function hsl_to_string($hsl)
    {
        return sprintf('%s %s%% %s%%', $hsl['h'], $hsl['s'], $hsl['l']);
    }
}
