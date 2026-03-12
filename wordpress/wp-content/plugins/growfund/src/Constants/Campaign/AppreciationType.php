<?php

namespace Growfund\Constants\Campaign;

defined( 'ABSPATH' ) || exit;

use Growfund\Traits\HasConstants;

class AppreciationType
{
    use HasConstants;

    /** @var string */
    const GOODIES = 'goodies';

    /** @var string */
    const GIVING_THANKS = 'giving-thanks';
}
