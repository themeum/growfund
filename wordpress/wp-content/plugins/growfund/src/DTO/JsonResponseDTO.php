<?php

namespace Growfund\DTO;

defined( 'ABSPATH' ) || exit;

class JsonResponseDTO extends DTO
{
    /** @var string */
    public $html;

    /** @var array */
    public $data;
}
