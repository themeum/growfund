<?php

namespace Growfund\DTO;

defined( 'ABSPATH' ) || exit;


class EmailContentDTO extends DTO
{
    /** @var string */
    public $subject;

    /** @var string */
    public $heading;

    /** @var string */
    public $message;
}
