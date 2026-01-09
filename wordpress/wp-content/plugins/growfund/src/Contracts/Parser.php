<?php

namespace Growfund\Contracts;

defined( 'ABSPATH' ) || exit;

interface Parser
{
    /**
     * Parse the content.
     *
     * @param string $content
     * @return string
     */
    public function parse(string $content);
}
