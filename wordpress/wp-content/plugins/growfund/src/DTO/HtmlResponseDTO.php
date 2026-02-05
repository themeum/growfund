<?php

namespace Growfund\DTO;

defined( 'ABSPATH' ) || exit;

class HtmlResponseDTO extends DTO
{
    /** @var string */
    public $html;

    /** @var array */
    public $data;
}
