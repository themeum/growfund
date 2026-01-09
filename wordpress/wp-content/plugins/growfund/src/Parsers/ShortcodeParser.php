<?php

namespace Growfund\Parsers;

defined( 'ABSPATH' ) || exit;

use Growfund\Contracts\Parser;
use InvalidArgumentException;

class ShortcodeParser implements Parser
{
    protected $variables = [];
    protected $pattern = '/\{([a-zA-Z0-9_]+)\}/';

    /**
     * Attach the variables to parser
     *
     * @param array $variables
     * @return self
     */
    public function with(array $variables)
    {
        $this->variables = $variables;

        return $this;
    }

    /**
     * Explicitly set the pattern
     *
     * @param string $pattern
     * @return self
     */
    public function pattern(string $pattern)
    {
        $this->pattern = $pattern;

        return $this;
    }

    /**
     * Parse the content
     *
     * @param string $content
     * @return string
     */
    public function parse(string $content)
    {
        if (empty($this->pattern)) {
            throw new InvalidArgumentException(esc_html__('The parser pattern is required.', 'growfund'));
        }

        if (empty($this->variables)) {
            return $content;
        }

        return preg_replace_callback($this->pattern, function ($matches) {
            $tag = $matches[1];

            if (isset($this->variables[$tag])) {
                return $this->variables[$tag];
            }

            return $matches[0];
        }, $content);
    }
}
