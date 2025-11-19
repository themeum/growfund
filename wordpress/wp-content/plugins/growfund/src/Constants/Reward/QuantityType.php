<?php

namespace Growfund\Constants\Reward;

defined( 'ABSPATH' ) || exit;

use Growfund\Traits\HasConstants;

class QuantityType
{
    use HasConstants;

    const UNLIMITED = 'unlimited';
    const LIMITED = 'limited';
}
