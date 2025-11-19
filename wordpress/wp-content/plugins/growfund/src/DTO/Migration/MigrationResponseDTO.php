<?php

namespace Growfund\DTO\Migration;

defined( 'ABSPATH' ) || exit;

use Growfund\DTO\DTO;

class MigrationResponseDTO extends DTO
{
    /** @var string */
    public $step;

    /** @var int */
    public $total;

    /** @var int */
    public $completed;
}
