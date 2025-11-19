<?php

namespace Growfund\Constants\Status;

defined( 'ABSPATH' ) || exit;

use Growfund\Traits\HasConstants;

class FundStatus
{
    use HasConstants;

    const PUBLISHED = 'published';
    const TRASHED = 'trashed';
}
