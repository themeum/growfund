<?php

namespace Growfund\Constants\Contributor;

defined( 'ABSPATH' ) || exit;

use Growfund\Traits\HasConstants;

class DisplayOptionOrderBy
{
    use HasConstants;

    const TOP_AND_RECENT = 'top-and-recent';
    const TOP_ONLY = 'top-only';
    const RECENT_ONLY = 'recent-only';
}
