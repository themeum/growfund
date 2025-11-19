<?php

namespace Growfund\Core;

defined( 'ABSPATH' ) || exit;

use Growfund\Contracts\Shortcode as ShortcodeContract;
use Exception;

abstract class Shortcode implements ShortcodeContract
{
    /**
     * Shortcode name.
     * @var string
     */
    protected $name;

    /**
     * Get the shortcode name.
     *
     * @return void
     * @throws Exception
     */
    public function get_name()
    {
        if (empty($this->name)) {
            throw new Exception(esc_html__('Shortcode name is required', 'growfund'));
        }

        return $this->name;
    }

    /**
     * The shortcode callback function.
     *
     * @param array $attributes
     * @param string $content
     * @param string $shortcode_tag
     * @return string
     */
    abstract public function callback($attributes, string $content = '', string $shortcode_tag = '');
}
