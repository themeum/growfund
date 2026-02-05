<?php

namespace Growfund\Constants\Campaign;

defined( 'ABSPATH' ) || exit;

use Growfund\Traits\HasConstants;

class TributeRequirement
{
    use HasConstants;

    const OPTIONAL = "optional";
    const REQUIRED = "required";
}
