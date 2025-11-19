<?php

namespace Growfund\Payments\DTO;

defined( 'ABSPATH' ) || exit;

use Growfund\DTO\DTO;

class PaymentMethodDTO extends DTO
{
    /** @var string */
    public $name;

    /** @var string */
    public $label;

    /** @var string|null */
    public $logo;

    /** @var string */
    public $type;

    /** @var string|null */
    public $instruction;
}
